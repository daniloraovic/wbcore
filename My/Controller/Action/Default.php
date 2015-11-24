<?php 
class My_Controller_Action_Default extends Zend_Controller_Action
{
	
	protected $_htmlToPdfFilename;
	protected $_url;
	protected $_header;
	protected $_footer;
	
	public function init()
	{
		$this->view->request = $this->getRequest();

		try {
			
			//$this->_authorize();
			
		}
		catch(Exception $e) {
			
			$logger = new My_Controller_Logger();
			$logger->setClientId(Zend_Auth::getInstance()->getIdentity()->client_id)
				   ->log($e, true, true);
			
		}
		
	}
	
	public function postDispatch()
	{
		// TODO: ispraviti ovo - dva dispatch loop-a i u jednom se resetuje renderer pronaci zasto - poziv ka API metodi
		$this->_helper->viewRenderer;
		$this->_helper->layout->enableLayout();
		
	}

	public function postXml()
	{
		$this->_helper->layout->disableLayout();

	}	
	
	public function postHtmlToPdf()
	{
		$this->_helper->layout->disableLayout();		

		$tempnam = tempnam('/tmp', 'BG'); 	
					
//		if(file_put_contents("$tempnam.html", $this->getResponse()->getBody())) {
			
	            $bin    = Zend_Registry::get('configuration')->wkhtmltopdfpath;
//	            $html   = "$tempnam.html";
	            $pdf    = "$tempnam.pdf";
	            $params = array(
	                '--encoding utf-8',
	                '--page-size A4'
	            );

//	            if (is_array($this->_header)) {
//	            	$params[] = "--header-html \"http://72.167.142.229/img/logo/logo73x30.png\"";
//	            	$params[] = "--header-spacing 5";
//                	$params[] = "--margin-top 30";
//	            }
	            
				if (is_array($this->_footer)) {	            
	                $params[] = "--footer-left \"" . (isset($this->_footer['left']) ? $this->_footer['left'] : '') . "\"";
	                $params[] = "--footer-center \"" . (isset($this->_footer['center']) ? $this->_footer['center'] : '') . " \"";
	                $params[] = "--footer-right \"" . (isset($this->_footer['right']) ? $this->_footer['right'] : '') . "\"";
	                $params[] = "--footer-spacing 3";
	                $params[] = "--margin-bottom 20";
				}
	            
	            // Execute command, set response, delete tmp files
	            $command = implode(" ", array($bin, implode(" ", $params), $this->_url, $pdf));
	            $output = system($command, $return_var);

	            $this->getResponse()->setHeader('Content-Disposition', "attachment;filename={$this->_htmlToPdfFilename}.pdf");
	            $this->getResponse()->setHeader('Content-Length', $filesize = filesize("$tempnam.pdf"));
	            ini_set('memory_limit', ceil(16 + 2 * $filesize / 1024 / 1024) . 'M'); 
	            $this->getResponse()->setBody(file_get_contents("$tempnam.pdf"));
	            unlink($tempnam);
//	            unlink("$tempnam.html");
	            unlink("$tempnam.pdf");
//		}
		
	}

	protected function _getClientIds( $params = array() )
	{
		
		if ( isset(Zend_Auth::getInstance()->getIdentity()->super_client) && (int) Zend_Auth::getInstance()->getIdentity()->super_client == 1 ){
			
			$clientIds = Zend_Auth::getInstance()->getIdentity()->user_group['client_ids'];
			
			if ( isset( $params['client-id'] ) ){
				
				if ( in_array( $params['client-id'], $clientIds ) ) {
					
					$clientIds = array( $params['client-id'] );
					
				} else {
					
					$clientIds = array();
				}
			}
			
		} else {
			
			$clientIds = array( (int) Zend_Auth::getInstance()->getIdentity()->client_id );
		}
		
		return $clientIds;
	}
	
	protected function _getDeviceId($params) {
		$api = new Api_Model_Vehicle();
	
		$apiParams = array(
				"client_id" => (int) Zend_Auth::getInstance()->getIdentity()->client_id,
				"vin" => $params['vin']
		);
	
		$data = $api->find($apiParams);
	
		if ( $data->valid() ){
				
			$vehicle = $data->next();
			$params['device_id'] = $vehicle->getTrueDeviceId();
		}
	
		return $params;
	}
	
	protected function _doAuditLog( $operation )
	{
		
		$auditData['username'] = Zend_Auth::getInstance()->getIdentity()->username;
		$auditData['user_id'] = Zend_Auth::getInstance()->getIdentity()->user_id;
		$auditData['operation'] = $operation;
		$auditData['date'] = time();
 		$auditData['client_id'] = (int) Zend_Auth::getInstance()->getIdentity()->client_id;
		$auditData['role_id'] = (int) Zend_Auth::getInstance()->getIdentity()->user_group['role_id'];
		$auditData['ip_address'] = $this->getIpAddress();
		
		$obj = new My_Model_Domain_AuditingLog();
		
		$obj->setData( $auditData )
			->setMappers(array('My_Model_Mapper_Mongo_AuditingLog'))
			->markNew();
		
		My_Model_Watcher::commit();
	}
	
	protected function _authorize() {

		$status = true;
		
		//is my user group allowed to do this?
		$userIdentity = Zend_Auth::getInstance()->getIdentity();

		$userRoleId = $userIdentity->user_group["role_id"];

		//collection of groups to which the user belongs
		$userGroupCollection = My_Model_Mapper_Mongo_UserGroup::findById(
										$userIdentity->client_id,
										$userIdentity->user_group["group_id"]
									);

		//collection of user group objects each containing list of assigned module ids
		$parentUserGroupCollection = My_Model_Mapper_Mongo_UserGroup::findById(
										$userIdentity->client_id,
										$userIdentity->user_group["group_parent_id"]
									);

		//one group (by design) for each user
		if(1 !== $userGroupCollection->getTotal()) {
			
			//dispatch an email and log the event of multiple groups
			$logger = new My_Controller_Logger();
			$logger->setClientId(Zend_Auth::getInstance()->getIdentity()->client_id)
				   ->log($userGroupCollection, true, true);
			
			//re-throw exception
			throw new Exception("There can be only one user group with particular id"
					. "(client id: " . Zend_Auth::getInstance()->getIdentity()->client_id
					. ", group id: " . $userIdentity->user_group["group_id"] . ", user id: "
					. $userIdentity->user_id . ")");
			
		}

		//one parent group at most for any particular group
		if(!in_array($parentUserGroupCollection->getTotal(), array(0,1))) {
			
			//dispatch an email and log the event of multiple parent groups
			$logger = new My_Controller_Logger();
			$logger->setClientId(Zend_Auth::getInstance()->getIdentity()->client_id)
				   ->log($parentUserGroupCollection, true, true);
				
			//re-throw exception
			throw new Exception("There can be only one parent user group for particular user"
					. "(client id: " . Zend_Auth::getInstance()->getIdentity()->client_id
					. ", parent group id: " . $userIdentity->user_group["group_parent_id"]
					. ", group id: " . $userIdentity->user_group["group_id"] . ", user id: "
					. $userIdentity->user_id . ")");
			
		}

		//get all module access control list rules which apply to the request
		$maclCollection = My_Model_Mapper_Mongo_ModuleAccessControlList::findModuleIdsByMCA(
				$this->view->request->module,
				$this->view->request->controller,
				$this->view->request->action
		);

		
		//is there any acl which grants the request?
		foreach($maclCollection as $macl) {
			if(is_array($macl->getUserModuleIdList())
					&& count(array_intersect($macl->getUserModuleIdList(), $userGroupCollection->current()->getModuleId())) > 0) {
					
				$status = false;
				
				$logger = new My_Controller_Logger();
				$logger->setClientId(Zend_Auth::getInstance()->getIdentity()->client_id)
					   ->log($macl->getUserModuleIdList(), true, true)
				 	   ->log($userGroupCollection->current()->getModuleId(), true, true);
		
			}
		}
		
		//if granted to particular group, is it granted to its parent group (if it has one)?
		if($status && 1 === $parentUserGroupCollection->getTotal())
			foreach($maclCollection as $macl) {
				if(!is_array($macl->getUserModuleIdList())
					|| count(array_intersect($macl->getUserModuleIdList(), $parentUserGroupCollection->current()->getModuleId())) < 1) {

					$status = false;
					
					$logger = new My_Controller_Logger();
					$logger->setClientId(Zend_Auth::getInstance()->getIdentity()->client_id)
						   ->log($macl->getUserModuleIdList(), true, true)
						   ->log($parentUserGroupCollection->current()->getModuleId(), true, true);
					
				}
			}
			
		if(!$status) {
			
			throw new Exception("Request authorization failed.");
			
		}		
		
	}
	
	protected function getIpAddress() {
		 
		foreach (array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR') as $key) {
			 
			if (array_key_exists($key, $_SERVER) === true) {
				 
				foreach (explode(',', $_SERVER[$key]) as $ip) {
					 
					if (filter_var($ip, FILTER_VALIDATE_IP) !== false) {
						 
						return $ip;
					}
				}
			}
		}
	}
	
}