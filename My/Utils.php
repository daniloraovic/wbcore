<?php

/**
 * Utility functions
 * 
 * @author МREŽNI_SISTEMI 2009
 *
 */
class My_Utils 
{
	
	/**
	 * Calls action controller and returns view response
	 * 
	 * @param string $action - action name
	 * @param string $controller - controller name
	 * @param string $module - module name 
	 * @param array $params - additional request params
	 * @return view 
	 */
	public static function getResponseRequest($action = null, $controller = null, $module = null, array $params = array(), $setCookie = false)
	{
		Zend_Registry::set('setCookie', $setCookie);
		
		Zend_Registry::set('route', 'internal');
		$params['route'] = 'internal';

		 // clone the view object to prevent over-writing of view variables
		$viewRendererObj = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer');
        Zend_Controller_Action_HelperBroker::addHelper(clone $viewRendererObj);
		
		$request = new Zend_Controller_Request_Simple($action, $controller, $module, $params);
		
		$response = new Zend_Controller_Response_Cli(); 
		$front_controller = Zend_Controller_Front::getInstance(); 
		
		$plugins = $front_controller->getPlugins();
		$method = 'routeShutdown'; 
		foreach ($plugins as $plugin) {
            if($plugin instanceof My_PageController) continue; // Internal request ne treba da prolazi kroz sistem strana
			if (method_exists($plugin, $method)) { 
		    	call_user_func_array(array($plugin, $method), array($request)); 
			} 
		} 

		$front_controller->getDispatcher()->dispatch($request, $response);

		// reset the viewRenderer object to it's original state
		Zend_Controller_Action_HelperBroker::addHelper($viewRendererObj);

		Zend_Registry::set('route', 'external');
		
		return $response->getBody();
	}


	/**
	 * ISTO KAO GORE SAMO UMESTO getBody vracamo ceo response objekat
	 *
	 * @param string $action - action name
	 * @param string $controller - controller name
	 * @param string $module - module name
	 * @param array $params - additional request params
	 * @return view
	 */
	public static function getResponse($action = null, $controller = null, $module = null, array $params = array(), $setCookie = false)
	{
		Zend_Registry::set('setCookie', $setCookie);

//		var_dump($action);
//		var_dump($controller);
//		var_dump($module);

		Zend_Registry::set('route', 'internal');
		$params['route'] = 'internal';

		 // clone the view object to prevent over-writing of view variables
		$viewRendererObj = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer');
        Zend_Controller_Action_HelperBroker::addHelper(clone $viewRendererObj);

		$request = new Zend_Controller_Request_Simple($action, $controller, $module, $params);

		$response = new Zend_Controller_Response_Cli();
		$front_controller = Zend_Controller_Front::getInstance();

		$plugins = $front_controller->getPlugins();
		$method = 'routeShutdown';
		foreach ($plugins as $plugin) {
			if (method_exists($plugin, $method)) {
		    	call_user_func_array(array($plugin, $method), array($request));
			}
		}

		$front_controller->getDispatcher()->dispatch($request, $response);

		// reset the viewRenderer object to it's original state
		Zend_Controller_Action_HelperBroker::addHelper($viewRendererObj);

		Zend_Registry::set('route', 'external');

		return $response;
	}


	/**
	 * Creates full image path
	 *  
	 * @param string $size - image size -> folder name
	 * @param string $imageName - image relative path from DB
	 * @return string - full image path
	 */
	public static function getImagePath($size, $imageName, $type = 'thumbnail')
	{
		if(empty($imageName)) {
			$currentLanguage = Zend_Registry::get('language');
			$imageName = '/no-img/no/img/' . $type . '-noimg.png';
		}
		if($size != 'original') {
			$letter = $size[strlen($size)-1];
			if(!in_array($letter, array('A', 'B', 'C', 'D'))) $size .= 'B';
		}
		return '/data/img/' . $size . $imageName;
	}

        public static function getSubfolderParts($id)
        {
            $subfolderPart = md5($id);
            return array(
                'level0' => substr($subfolderPart, -4, 2),
                'level1' => substr($subfolderPart, -2, 2)
            );
        }

	/**
	 * Creates full flash HTML
	 *  
	 * @param string $size - image size -> folder name
	 * @param string $imageName - image relative path from DB
	 * @return string - full image path
	 */
	public static function getFlashObject($flashName)
	{
		if(empty($flashName)) {
			$currentLanguage = Zend_Registry::get('language');
			$flashName = '/language/' . $currentLanguage->url_part . '/' . $type . '-noimg.png';
		}
		
		$width = '550'; $height = '400';
		
    	$info = getimagesize($_SERVER['DOCUMENT_ROOT'] . '/data/flash/original' . $flashName); 
	    $width = $info[0];
    	$height = $info[1];
		
		$object = '
		<object width="' . $width . '" height="' . $height . '">
			<param name="movie" value="/data/flash/original' . $flashName . '" />
			<embed src="/data/flash/original' . $flashName . '" width="' . $width . '" height="' . $height . '">
			</embed>
		</object>';
		
		return $object;
	}
	
	/**
	 * Creates offset for DB query LIMIT
	 * 
	 * @param int $page - page number
	 * @param int $perPage - per page number
	 * @return int
	 */
	public static function getOffset($page, $perPage)
	{
		return ($perPage * ($page - 1));
	}
	
	/**
	 * Checks if variable is empty
	 * problem when "0" is not intended to be empty 
	 * false and white spaces are optional
	 * 
	 * @param string $var - variable to be checked
	 * @param bool $allowFalse - if variable is set to false it will return true
	 * @param $allowWs - for variable with white spaces it will return true
	 * @return bool
	 */
	public static function is_empty($var, $allowFalse = false, $allowWs = false) 
	{
	    if ((is_array($var) && empty($var))||  !isset($var) || is_null($var) || (is_string($var) && $allowWs == false && trim($var) == "" && !is_bool($var)) || ($allowFalse === false && is_bool($var) && $var === false)) {    
	        return true;
	    } else {
	        return false;
	    }
	}
	
	/**
	 * Returns posted value or value from database
	 * 
	 * @param array $params
	 * @return string
	 */
	public static function formFieldValue($params)
	{
		if ($params['edit']) {
			if (!empty($params['post'])) {
				$return = $params['post'];
			} elseif (!empty($params['edit'])) {
				$return = $params['read'];
			} else {
				$return = '';
			}
		} else {
			if (!empty($params['post'])) {
				$return = $params['post'];
			} else {
				$return = '';
			}
		}
		return $return;
	}
	
	/**
	 * Creates date in proper format Y-m-g H:i:s for(entity_sort_date, entity_view_date...)
	 * 
	 * @param array $names post params property key (sort, view...)
	 * @param array $params post params
	 * @return array
	 */
	public static function formatDate($names, $params) 
	{
		foreach($names as $name) {
		
			$params[$name . '_hour'] = empty($params[$name . '_hour']) ? '00' : (int)$params[$name . '_hour'];
			$params[$name . '_min'] = empty($params[$name . '_min']) ? '00' : (int)$params[$name . '_min'];
			
			$params[$name . '_hour'] = str_pad($params[$name . '_hour'], 2, '0', STR_PAD_LEFT);
			$params[$name . '_min'] = str_pad($params[$name . '_min'], 2, '0', STR_PAD_LEFT);
			
			$params['entity_' . $name . '_date'] .= ' ' . $params[$name . '_hour'] . ':' . $params[$name . '_min'] . ':00';
		}
		
		return $params;
	}
	
	/**
	 * Converts date string to proper format Y-m-g H:i:s - general use
	 * 
	 * @param string $date
	 * @return $date
	 */
	
	public static function reformat_date($date)
	{
		// dd/mm/yyyy.
		if(preg_match('/^(\d{1,2})\/\s?(\d{1,2})\/\s?(\d{2,4})\/?(.*)$/', trim($date), $match)) {	
			if ($match[3] < 100) $yyyy = 2000 + $match[3];
			elseif ($match[3] < 1000) $yyyy = 1000 + $match[3];
			else $yyyy = $match[3];
			$dd = sprintf("%02d", $match[1]); 
			$mm = sprintf("%02d", $match[2]);
			return "$yyyy-$mm-$dd" . $match[4]; // maybe had a time part, keep intact
		} else {			
			return $date;
		}
	}
	
	public static function globalsStripSlashes() 
	{
		if (get_magic_quotes_gpc()) {
			if ($_POST)		$_POST		= My_Utils::object_walk($_POST, 'stripslashes');
			if ($_GET)		$_GET 		= My_Utils::object_walk($_GET, 'stripslashes');
			if ($_COOKIE)	$_COOKIE	= My_Utils::object_walk($_COOKIE, 'stripslashes');
		}	
	}
	
	
	/**
	 * Apply callback function recursively to all object's properties / all array members
	 *  
	 * @param $data
	 * @param $function
	 * @return unknown_type
	 */
	public static function object_walk($data, $function) {
		$is_o = is_object($data);
		if (is_array($data) || ($is_o)) {
			if ($is_o) $data = (array)$data;
			foreach ($data as $key => $value) {
				if (is_array($value) || is_object($value)) $data[$key] = self::object_walk($value, $function);
				else $data[$key] = call_user_func($function, $value);
			}
			if ($is_o) $data = (object)$data;
			return $data;
		} else {
			return call_user_func($function, $data);
		}
	}
	
	/**
	 * Strip slashes and encode html special chars recursive
	 * 
	 * @param array $value
	 * @return array
	 */
	public static function stripslashesHtmlspecialRecursive($value) {
		$value = is_array($value) ?
	                array_map(array('My_Utils', 'stripslashesHtmlspecialRecursive'), $value) :
	                htmlspecialchars(stripslashes($value));
	
	    return $value;
	}
	
	/**
	 * 
	 * Gets net number for sequnece.
	 * 
	 * @param $tableName
	 * @param $where
	 * @param $sequenceName
	 * @return unknown_type
	 */
	public static function getSequence($tableName, $where, $sequenceName = 'sequence')
	{
		
		$db = Zend_Registry::get('db');
    	
        $select = $db->select();
        $select->from($tableName, $sequenceName);
        if(!empty($where)) $select->where($where);
        $select->order($sequenceName . ' DESC');
        
        $stmt = $db->query($select);
		$row = $stmt->fetch();
		
		return ($row[$sequenceName]+1);
        
	}
	
	public static function cutString($string, $length, $append = '')
	{
		$utf8 = new My_Utf8();
		if($utf8->utf8_strlen($string) > $length){
			$string = $utf8->utf8_substr($string, 0, $length);
			$string = preg_replace('/\s\S*$/u', '', $string);
			
			return $string . (!empty($append) ? ' ' . $append : '');
		}else return $string;
	}
	
	public static function encodeParams()
	{
		
	}
	
	public static function decodeParams()
	{
		
	}
	
	public static function createUrl($language, $identificator, $keyword = 'index', $params = array(), $what = '')
	{
		$urlWords = ZendRegistry::get('fromUrlWords');
		$urlWords = ZendRegistry::get('toUrlWords');
	}
	
	/**
	 * Creates url from page conf - use in menus
	 * 
	 * @param array $page - page conf
	 * @param obj $currentLanguage - current language
	 * @return strint - page url
	 */
	public static function pageUrl($page, $currentLanguage)
	{
		if(empty($page) || empty($currentLanguage)) return '';
		
		$pageParams = array();
		//$page['settings'] = unserialize(htmlspecialchars_decode($page['settings']));
		//$subtypeId = current($page['settings']['subtype']);
		
		$pageParams['language'] = $currentLanguage->url_part;
		$pageParams['ident'] = $page['unique_identifier'];
		//if(!empty($page['settings']['keyword'][$subtypeId]) || !empty($page['settings']['keyword']['default']))
			//$pageParams['keyword'] = !empty($page['settings']['keyword'][$subtypeId]) ? $page['settings']['keyword'][$subtypeId] : $page['settings']['keyword']['default'];
		//if(!empty($page['settings']['what'][$subtypeId]) || !empty($page['settings']['what']['default']))
			//$pageParams['w'] = !empty($page['settings']['what'][$subtypeId]) ? $page['settings']['what'][$subtypeId] : $page['settings']['what']['default'];
		
		$helper = new Zend_View_Helper_Url();
			
		return $helper->url($pageParams, 'default', true);
		
	}
	
	public static function isEmail($string)
	{
// 		return eregi('^[a-z0-9\.\_\%\+\-]+@([A-Z0-9-]+\.)+[a-z]{2,6}$', $string) ? true : false;
		return preg_match("/^[_\.0-9a-zA-Z-]+@([0-9a-zA-Z][0-9a-zA-Z-]+\.)+[a-zA-Z]{2,6}$/i", $string)? true : false;
	}
	
	public static function runQuery($query)
	{
		$db = Zend_Registry::get('db');
		return $db->query($query);
	}
	
	/*
	 * Extract domain name from url
	 * 
	 * @param string $url - url
	 * @return string - domain name
	 */
	public static function getDomainFromURL($url)
	{
		if(empty($url)) return null;
	
		preg_match('@^(?:http://)?([^/]+)@i', $url, $matches);
		$host = $matches[1];
		preg_match('/[^.]+\.[^.]+$/', $host, $matches);
		return empty($matches) ? null : $matches[0];
	}

	/**
	 * Returns easy understandable time difference like "2 days ago"
	 * 
	 * @param string|Zend_Date|integer|array $_dateFrom
	 * @return string - xx minutes|hours|days|years ago
	 */
	public static function timeSince($_dateFrom) {
	    static $_time_formats = array(
	        array(90, '1 minute'),                  // 60*1.5
	        array(3600, 'minutes', 60),             // 60*60, 60
	        array(5400, '1 hour'),                  // 60*60*1.5
	        array(86400, 'hours', 3600),            // 60*60*24, 60*60
	        array(129600, '1 day'),                 // 60*60*24*1.5
	        array(518400, 'days', 86400),           // 60*60*24*6, 60*60*24	, bilo je: 60*60*24*7 = 604800
	        array(907200, '1 week'),                // 60*60*24*7*1.5
	        array(2628000, 'weeks', 604800),        // 60*60*24*(365/12), 60*60*24*7
	        array(3942000, '1 month'),              // 60*60*24*(365/12)*1.5
	        array(31536000, 'months', 2628000),     // 60*60*24*365, 60*60*24*(365/12)
	        array(47304000, '1 year'),              // 60*60*24*365*1.5
	        array(3153600000, 'years', 31536000),   // 60*60*24*365*100, 60*60*24*365
	    );	

		$dateTo = new Zend_Date(null, Zend_Date::ISO_8601);

        if (!($_dateFrom instanceof Zend_Date)) {
            $_dateFrom = new Zend_Date($dateFrom, Zend_Date::ISO_8601);
        }

		$dateTo = $dateTo->getTimestamp();   // UnixTimestamp
		$dateFrom = $_dateFrom->getTimestamp(); // UnixTimestamp
		$difference = $dateTo - $dateFrom;
		$message = '';

		if ($dateFrom <= 0) {
			$message = 'a long time ago';
		} else {
			foreach ($_time_formats as $format) {
				if ($difference < $format[0]) {
					if (count($format) == 2) {
						$message = $format[1];
						break;
					} else {
						$message = ceil($difference / $format[2]) . ' ' . $format[1];
						break;
					}
				}
			}
		}

		return $message;	    
	}
	/**
	 * 
	 * Returns user age in years avoiding unix timestamp - handy for users born before 1970 ...
	 * @param $userDOB
	 */
	
	public static function currentAge($userDOB) {
		if (empty($userDOB)) return false;
					
		//Age difference
        $endDateArray = explode('/', date('d/m/Y'));
        $startDateArray = explode('/', $userDOB);        					 	        
        
        $startDate = gregoriantojd((int)$startDateArray[1], $startDateArray[0], $startDateArray[2]);
   		$endDate = gregoriantojd($endDateArray[1], $endDateArray[0], $endDateArray[2]);  						
   		
    	$userAge = round(($endDate - $startDate)/365);
		
    	return $userAge;
	}
	
/**
 * 
 * Function to check if input array has a http:// prefix and if not to add it ...
 * @param $strings = array
 * 
 * @return Array
 */
	public static function httpFormat(array $strings = null) {
		
		$result = array();
		
		if (empty($strings)) return false;
		
		foreach ($strings as $key=>$value) 
		{
			$position = strpos($value, 'http://');
			
			if ($position === false) $result[$key] = 'http://' . $value;
			else $result[$key] = $value;
		}
		return $result;
	}
	
	public static function getResponseFromUrl($url = null) {
		
		if (empty($url)) return false;
			
			$str = '';

			$sock = @fsockopen($url, 80);

			if (!is_resource($sock)) 
			{ 
				return false; 
				
			} else {

				$req ="GET / HTTP/1.1\r\n" . "Host: " . $url . "\r\n" . "Connection: close\r\n" . "\r\n"; 
				fwrite($sock, $req);
				$str = fread($sock, 1024);
				return true;
			}
			
			if (!empty($str)) 
			{
				$response = Zend_Http_Response::fromString($str);
			}
			else return false; 
		
			if ($response->isError()) 
			{
				return false;
				
			} else {
				return true;
			}	
	}
	
	/**
	 * for frontend
	 * 
	 * @param $tree
	 * @param $i
	 * @param $parentPageId
	 * @param $thisView
	 */
	public static function printMenuTree($tree, $root, $thisView, $route = 'menu', $currentLevel = 0, $depth = null, $classLast = 'last', $classActive = 'active')
	{
		$children = $tree[$route][(int)$root]['children'];
		$ident = Zend_Controller_Front::getInstance()->getRequest()->getParam('ident');
		
		if(!empty($children)){
			$numChildren = count($children);
			$cnt = 0;
	        echo '<ul';
	        	echo $currentLevel ? ' class="hidden" ' : '';
	        echo '>';
	        	foreach($children as $chk => $childId){
	        		if (!$tree[$route][$childId]['active']) continue;
	        		
	        		$cnt++;
	        		$class = array();
	        		
	        		echo '<li';//li
	        			if($cnt == $numChildren && $currentLevel) 
	        				$class[] = $classLast; 
	        			if($ident == $tree[$route][$childId]['unique_identifier'])
	        				$class[] = $classActive; 
	        			if(!empty($class)) echo ' class="' . implode(" ", $class) . '" ';
	        		echo '>';//end li
	        		
	        		echo '<a ';//<a>
	        		if(isset($tree[$route][$childId]['settings'])) {
	        			$settings = $tree[$route][$childId]['settings']; // unserializovan
		        		if(($settings['subtype'][0] == SUBTYPE_SPECIAL_PAGE_REDIRECT) || ($settings['subtype'][0] == SUBTYPE_SPECIAL_PAGE_BLANK)) {
	            		 	echo 'href="'  . $tree[$route][$childId]['teaser'] . '" ';
							if($settings['subtype'][0] == SUBTYPE_SPECIAL_PAGE_BLANK) {
								echo 'onclick="window.open(\'' . $tree[$route][$childId]['teaser']  . ',\'_blank\'); return false;" ';
							}
		        		} else {	
		        			echo 'href="' . My_Utils::pageUrl($tree[$route][$childId], $thisView->currentLanguage) . '" ';
		        		}
	        		} else {
	        			echo 'href="' . My_Utils::pageUrl($tree[$route][$childId], $thisView->currentLanguage) . '" ';
	        		}
        			echo ' title="' . $tree[$route][$childId]['name'] . '">';
	        			echo $tree[$route][$childId]['name'];
        			echo '</a>';//end <a>

        			if(isset($tree[$route][$childId]['level']) && !empty($tree[$route][$childId]['level'])) {
        				if($tree[$route][$childId]['menu_item_entity_type'] == TYPE_PAGE) {
        					My_Utils::printMenuTree($tree, $tree[$route][$childId]['id_entity'], $thisView, 'page', $currentLevel+1, $tree[$route][$childId]['level']);
        				} else {
        					My_Utils::printMenuTree($tree, $tree[$route][$childId]['id_entity'], $thisView, 'category', $currentLevel+1, $tree[$route][$childId]['level']);
        				}
        			} else if(isset($tree[$route][$childId]['level']) && empty($tree[$route][$childId]['level'])) {
        				My_Utils::printMenuTree($tree, $childId, $thisView, 'menu', $currentLevel+1);
        			} else if(!isset($tree[$route][$childId]['level'])){
        				if(isset($depth) && !empty($depth)) {
	        				if($tree[$route][$childId]['id_type'] == TYPE_PAGE) {
	        					My_Utils::printMenuTree($tree, $tree[$route][$childId]['id'], $thisView, 'page', $currentLevel+1, $depth-1);
	        				} else {
	        					My_Utils::printMenuTree($tree, $tree[$route][$childId]['id'], $thisView, 'category', $currentLevel+1, $depth-1);
	        				}
        				}
        			}
	        				
	        		echo '</li>';
	        	}
	     	echo '</ul>'; 
		}
	}
	
	/**
	 * for webadmin
	 * 
	 * @param $tree
	 * @param $i
	 * @param $parentPageId
	 * @param $thisView
	 */
	public static function printTree($tree, $i, $parentPageId = null, $thisView)
	{
		$result = array(
			'list' => '',
			'ids'  => ''
		);
	    if(!is_null($tree) && count($tree) > 0) {
	        echo '<ul>';
	    	foreach($tree as $node) {
	        	echo '<li>';
	        		echo '<span class="' . $node['type'] . ' pageItem page' . $node['id'] . '" title="page' . $node['id'] . '" rel="level-' . $i . '-' . $parentPageId . '">';
	            		echo $node['name'];
	            	echo (empty($node['lang_name']) ? ' ' : ' <strong>(' . $node['lang_name'] . ')</strong> ');
	            	echo (!isset($node['active']) ? '' : ($node['active'] ? '<span class="active"> ' . $thisView->translation('Aktivna') . '</span>'  : '<span class="inactive"> ' . $thisView->translation('Neaktivna') . '</span>')) . 
	            	'</span>';
	            	echo '</span>';
	            	//form general pages related stuff to writeout
	            	$result['list'] .= $node['id'] . '/' . $node['type'] . '/' . $i . '/' . $node['name'] . '/' . (empty($node['active']) ? '' : $node['active']) . '/' . $parentPageId . ';';
	        		$result['ids']  .= ', .page' . $node['id'];
	            	$res = My_Utils::printTree($node['children'], $i+1, $node['id'], $thisView);
	            	$result['list'] .= $res['list'];
	            	$result['ids']  .= $res['ids'];
	            	
	        	echo '</li>';
	        }
	     	 echo '</ul>';   
	    }
	    
	    return $result;
	}
	
	/**
	 * for webadmin
	 * 
	 * @param $tree
	 * @param $i
	 * @param $parentId
	 * @param $property_to_print
	 */
	function printTreeSelect($tree, $i, $parentId = null, $property_to_print = 'id') 
	{
	    if(!is_null($tree) && count($tree) > 0) {
	        foreach($tree as $node) {
	        	echo '<option value="' . $node[$property_to_print] . '" ';
	        	if($parentId && $parentId == $node['id']) {
	        		echo 'selected = "selected"';
	        	}
	        	echo '>';
	        	echo str_repeat("&nbsp;", $i+1);
	            echo !empty($node['name']) ? $node['name'] : '';
	        	echo '</option>';
	        	My_Utils::printTreeSelect($node['children'], $i+1, $parentId, $property_to_print);
	        }
	    }
	}
	
	/**
	 * for webadmin
	 * 
	 * @param $tree
	 * @param $i
	 * $productId
	 */
	function printCategoryProducts($tree, $i, $productId) 
	{
	    if(!is_null($tree) && count($tree) > 0) {
	        foreach($tree as $node) {
	        	$product_ids = array();
		        if (isset($node['products'])) {
					foreach ($node['products'] as $pkey => $pvalue){
						$product_ids[] = $pkey;
					}
	
					if (in_array($productId, $product_ids)) {
						echo '<span class="hide" name="' . $node['name'] . '">';
						echo $node['name'];
						echo '</span>';
					}
				}
				
				if (isset($node['products']))
	        		My_Utils::printCategoryProducts($node['products'], $i+1, $productId);
	        }
	    }
	}
	
	/**
	 * Retrieves message from session
	 * 
	 * @return string  
	 */
	public static function getMessage()
	{
		$authNamespace = new Zend_Session_Namespace('Zend_Auth');
		$return = $authNamespace->message;
		unset($authNamespace->message);

		return $return;
	}
	
	/**
	 * Sets message in session.
	 * 
	 * @param string $message 
	 */
	public static function setMessage($message)
	{
		$authNamespace = new Zend_Session_Namespace('Zend_Auth');
		$authNamespace->message = $message;
	}
	
    private static $cyr2lat_trans_table = array (
        "А" => "A", "Б" => "B", "В" => "V", "Г" => "G", "Д" => "D", "Ђ" => "Đ", "Е" => "E", "Ж" => "Ž",
        "З" => "Z", "И" => "I", "Ј" => "J", "К" => "K", "Л" => "L", "Љ" => "Lj", "М" => "M", "Н" => "N",
        "Њ" => "Nj", "О" => "O", "П" => "P", "Р" => "R", "С" => "S", "Ш" => "Š", "Т" => "T", "Ћ" => "Ć",
        "У" => "U", "Ф" => "F", "Х" => "H", "Ц" => "C", "Ч" => "Č", "Џ" => "Dž", "Ш" => "Š",
        "а" => "a", "б" => "b", "в" => "v", "г" => "g", "д" => "d", "ђ" => "đ", "е" => "e", "ж" => "ž",
        "з" => "z", "и" => "i", "ј" => "j", "к" => "k", "л" => "l", "љ" => "lj", "м" => "m", "н" => "n",
        "њ" => "nj", "о" => "o", "п" => "p", "р" => "r", "с" => "s", "ш" => "š", "т" => "t", "ћ" => "ć",
        "у" => "u", "ф" => "f", "х" => "h", "ц" => "c", "ч" => "č", "џ" => "x", "ш" => "š",
    );
	
    /**
     * Transcribe cyrilic (html) text to latin equivalent. Simple strtr().
     * are in latin 
     * 
     * @param $str
     * @return unknown_type
     */
    public static function cyr2lat($str) {
        return strtr($str, self::$cyr2lat_trans_table); 
    }
	
    private static $lat2cyr_trans_table = array();
	
	/** 
	* Transcribe latin html text to cyrilic equivalent. A bit complicated one, it mustn't touch html tags, which 
	* are in latin 
	* 
	* @param string $str string / html code to translate 
	* @return string 
	*/ 
	public static function lat2cyr($str) {
		if (!self::$lat2cyr_trans_table) self::$lat2cyr_trans_table = array_flip(self::$cyr2lat_trans_table); 
		$splitted = preg_split('~(<[^>]+>|\&[a-z]+;|\&0x[0-9a-f]+;|\&\#[0-9]+;)~sSi', $str, -1, PREG_SPLIT_DELIM_CAPTURE); 
		// parni su sadrzaj, neparni su delimiteri 
		for ($i = 0, $l = count($splitted), $out = ''; $i < $l; $i++) { 
			if ($i % 2) $out .= $splitted[$i]; 
                else $out .= strtr($splitted[$i], self::$lat2cyr_trans_table); 
			}
		return $out; 
	}
	
}
