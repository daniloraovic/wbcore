<?php
class My_Model_Domain_User extends My_Model_Domain
{
	public function __construct($id = null) {
		$this->_client_id_unset_from_data = false;
		parent::__construct($id);
	}
	
	public function login( $username, $password, $dbName = 'bitgear_authentication' )
	{
		$auth = Zend_Auth::getInstance();
		$authAdapter = new My_Auth_Adapter_Mongo( Zend_Registry::get('dbConfiguration')->mongo->authentication, $dbName, 'user', 'username', 'password', 'user_id' );
		
		$authAdapter->setIdentity( $username );
		$authAdapter->setCredential( $password );
		$authAdapter->setCredentialTreatment(array('func(?)' => 'md5'));
		
		$result = $auth->authenticate($authAdapter);

// 		var_dump($result);
// 		Zend_Auth::getInstance()->clearIdentity();
// 		die;
		
		if($result->isValid()) {
			$storage = $auth->getStorage();
			$storageData = $authAdapter->getResultRowObject( array('_id' => 'user_id', 'username', 'client_id', 'timezone', 'language', 'last_update') );

			$mongoMapper = new My_Model_Mapper_Mongo();
			$collection = $mongoMapper->_dbAuthentcation()->client;
			$client = iterator_to_array($collection->find(array('client_id' => $storageData->client_id)), false);

			$mongoMapper = new My_Model_Mapper_Mongo();
			$collection = $mongoMapper->_dbClientData($storageData->client_id)->user_user_group;
			$resultUserUserGroup = iterator_to_array($collection->find(array('user_id' => $storageData->user_id)), false);
			
			// ukoliko se obustavi ucitavanje podataka o korisnickoj grupi i dozvoljenim modulima
			// ovo je mesto da se $storageData upise u $storage
			//$storage->write($storageData);
			if ( !isset($resultUserUserGroup[0]) ) {
				
				Zend_Auth::getInstance()->clearIdentity();
				throw new Exception();
				
			} else {
				
				$mongoMapper = new My_Model_Mapper_Mongo();
				
				$collection = $mongoMapper->_dbClientData($storageData->client_id)->user_group;
			
				$resultUserGroup = iterator_to_array($collection->find(array('group_id' => $resultUserUserGroup[0]['group_id'])), false);
		
				if (empty($resultUserGroup)) {
					
					Zend_Auth::getInstance()->clearIdentity();
					throw new Exception();
					
				} else {
					
					$userGroup = $resultUserGroup[0];
					$userGroup['acn_details'] = '0';
					
					// check client that user can access [superuser && warehouse]
					if( !empty( $client ) && in_array($client[0]['superclient'], array("1", "2")) ) {
						
						if ( !empty( $client[0]['client_ids'] ) ){

							$client_ids = $userGroup['client_ids'];
							$userGroup['client_ids'] = array();

							foreach ( $client_ids as $key => $value ){
								
								if( in_array( $value, $client[0]['client_ids'] ) ){
									
									$userGroup['client_ids'][$key] =  (int) $value; 
								}
							}
							
						} else {
							
							Zend_Auth::getInstance()->clearIdentity();
						
							throw new Exception();					
						}
					}
					
					
					$mongoMapper = new My_Model_Mapper_Mongo_Module();
					
					$identity = $mongoMapper->getIdentity();
					
					$identity->field('visible_to_client')->eq( (string)$client[0]['superclient'] );
					
					$resultModules = $mongoMapper->findAll( $identity );
					
					
					$moduleArr = $this->return_modules($resultModules->getRawData(), $userGroup['module_id'], $userGroup['role_id']);
	
					$userGroup['module'] = $this->set_order($moduleArr);
					
					if(empty($userGroup['module'])) {
						Zend_Auth::getInstance()->clearIdentity();
						throw new Exception();
					}
				}
				
				$storageData->vin = isset($resultUserUserGroup[0]['vin']) ? $resultUserUserGroup[0]['vin'] : '0';
				
				$storageData->user_group = $userGroup;
				
				$storageData->super_client = !empty( $client ) ? $client[0]['superclient'] : 0;
				
				$mongoMapper = new My_Model_Mapper_Mongo();
				
				$collection = $mongoMapper->_dbClientData($storageData->client_id)->settings;
				
				$settingsCollection = iterator_to_array($collection->find());
				
				$numberOfMonth = 1;
				
				foreach ( $settingsCollection as $row ) {
					
					foreach ($row as $key => $value ){
						
						if( $key == "password_month_range" && isset($value) && $value > 0 ) $numberOfMonth = $value;
						
						if( $key == "lat" && isset($value) ) $storageData->lat = $value;
						
						if( $key == "lng" && isset($value) ) $storageData->lng = $value;
					}
				}
				
				if ( !empty($storageData->last_update) && ( ( $storageData->last_update + ($numberOfMonth * date('t', $storageData->last_update)  * 24 * 60 * 60 ) ) <= time() ) ) {
					
					$storageData->password_expired = 1;
				} else {
					
					$storageData->password_expired = 0;
				}
				$dateLocal = new DateTime('now');
				$storageData->localeTimezone = $dateLocal->getOffset();
				$logic = $storageData->user_group;
// 				var_dump("Ovo je sredjeno: ", $logic['module']);
							
// 				Zend_Auth::getInstance()->clearIdentity();
// 				die;
				
				$storage->write($storageData);
			}
		} else {
			
			throw new Exception(current($result->getMessages()), $result->getCode());
		}
		
		return $result;
	}
	
	private function set_order($moduleArr){
		
		$module = array();
		
		$order = Zend_Registry::get('configuration')->orderModul->toArray();
		
		foreach ( $moduleArr as $value ) {
		
			if (!empty($value['children'])){
					
				$value['children'] = $this->set_order($value['children']);			
			}		
		
			$module[isset($order[$value['button_id']]) ? $order[$value['button_id']] : $value['module_id']] = $value;
		
			if ($value['name'] == 'Emergency') $userGroup['acn_details'] = '1';
		
			if ($value['name'] == 'Monitor') $userGroup['acn_details'] = '1';
				
		}
		
		return $module;
	}
	
	private function return_modules($modules_object_list, $modul_id_list, $role_id = 3) {
		
		foreach ($modules_object_list as $key => $module) {
			
			if($module['children']) {
				 
				$modules_object_list[$key]['children'] = array();
				
				$modules_object_list[$key]['children'] = $this->return_modules($module['children'], $modul_id_list, $role_id);
			}
			
			
			if(in_array($module['module_id'], $modul_id_list)) {
				if(in_array($role_id, $module['accessible'])) continue;
			}
				
			unset($modules_object_list[$key]);
			
		}
		
		return $modules_object_list;
		
	}
	
	public function requestResetPassword( $data )
	{
		$auditData = array(
			'status' => true
		);
		
		$mongoMapper = new My_Model_Mapper_Mongo();
		
		$collection = $mongoMapper->_dbAuthentcation()->user;
	
		if ( !filter_var($data, FILTER_VALIDATE_EMAIL) ) {
						
			$users = iterator_to_array($collection->find(array('username' => $data)), false);
		} else {
						
			$users = iterator_to_array($collection->find(array('email' => $data)), false);
		}
	
		if( isset($users) && !empty($users) ) {
		
			$cliens = $this->_getClientName();
			
			$messages = array();
			
			foreach ( $users as $user ) {
				
				$clientname = $cliens[$user['client_id']];
								
				if( isset($clientname) && isset( $user['email'] ) && filter_var($user['email'], FILTER_VALIDATE_EMAIL) ) {
					
					$resetUID = uniqid();
									
					$userData = array(
						'reset_uid' => md5( $resetUID ),
						'reset_uid_expired' => time() + (1*24*60*60)
					);
					
					$collection->update( array( 'user_id' => $user['user_id'] ), array( '$set'=> $userData ));
					
					$messages[] = My_Translation::publicTranslate("To change your password for user with email").": ".$user["email"].", ".My_Translation::publicTranslate("please click the link below").":\n\n".Zend_Registry::get('configuration')->globalHostIp."/user/password/id/".md5( $resetUID )."\n";
					
					$auditData[$user['user_id']]['username'] = $user['username'];
					$auditData[$user['user_id']]['user_id'] = $user['user_id'];
					$auditData[$user['user_id']]['operation'] = 3;
					$auditData[$user['user_id']]['date'] = time();
					$auditData[$user['user_id']]['client_id'] = $user['client_id'];
					
				} else {
									
				 	$auditData['status'] = false;
				}
			}
			
			if ( $auditData['status'] ) {
				
				$message = My_Translation::publicTranslate("Hi").",\n".My_Translation::publicTranslate("We recently received a request for a forgotten password.")."\n";
				
				foreach ( $messages as $txt ) {
	
					$message = $message."\n".$txt;
				}
				
				$message = $message."\n".My_Translation::publicTranslate("If you did not request this change, you do not need to do anything.")."\n".My_Translation::publicTranslate("This link will expire in one day.")."\n\n".My_Translation::publicTranslate("Thanks,")."\n".My_Translation::publicTranslate("Carsharing Support.");

				$subject = My_Translation::publicTranslate("Reset password");
				
				
				$mailcontent = array(
						"sender_email" => Zend_Registry::get('configuration')->simpleEmail->senderEmail ? Zend_Registry::get('configuration')->simpleEmail->senderEmail : 'branko.karaklajic@bitgear.rs',
						"sender_name"=> Zend_Registry::get('configuration')->simpleEmail->senderName ? Zend_Registry::get('configuration')->simpleEmail->senderName : My_Translation::publicTranslate("Carsharing Support."),
						"subject" => $subject,
						"message" => $message,
						"recipient_name" => $user['first_name']." ".$user['last_name'],
						"recipient_email" => $user['email'],
				);
				$mail = new My_Communication_SimpleEmail($mailcontent);
				$mail->send();				
				
			}	
		} else {
			
			 $auditData['status'] = false;
		}

		return $auditData;
		
	}
	
	public function logout()
	{
		if ( Zend_Auth::getInstance()->hasIdentity() )
		{
			Zend_Auth::getInstance()->clearIdentity();
			Zend_Session::destroy(true, true);
		}
	}
	
	private function _getClientName() {
		
		$mongoMapper = new My_Model_Mapper_Mongo();
		
		$collection = $mongoMapper->_dbAuthentcation()->client;
				
		$data = iterator_to_array($collection->find());
		
		$result = array();
		
		foreach ( $data as $key => $value ) {
			
			$result[$value['client_id']] = $value['name'];
		}
		
		return $result;
		
	}
	
	
}
