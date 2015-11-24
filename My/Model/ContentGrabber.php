<?php

class My_Model_ContentGrabber
{
	public $header, $rawdata, $data, $info, $verbose, $cache_lifetime, $user_agent;
	public $curl_options, $content_retrieved_from_cache, $post_fields = '';
	
	/**
	 * 
	 * @var cache_simple_file
	 */
	public $cache_obj;

	private static $_path = APPLICATION_PATH;
	private static $_cache_root_storage = "/data/grabber/tmp/";
	
	public function __construct($verbose = false, $cache_lifetime = 0) {
		self::$_cache_root_storage = self::$_path . self::$_cache_root_storage;
		$this->verbose = $verbose;
// 		if ($this->cache_lifetime = $cache_lifetime) {
// 			$this->cache_obj = new My_Cache_Simple_File(array(
// 				'root_dir'	=> self::$_cache_root_storage . 'remotedata/'
// 			));
// 		}
	}
	
	public function check_header($content_type = 'text/xml') {
		return $this->info['http_code'] == 200 && preg_match("~$content_type~", $this->info['content_type']);
	}

	public function clean() {
		unset($this->rawdata, $this->header, $this->data, $this->info);
	}
	
	private function cache_get($key) {
		if ($this->cache_lifetime) {
			if ($this->content_retrieved_from_cache = $this->cache_obj->check($key)) {
				if ($this->verbose & 2) echo "... from cache ";
				list($this->header, $this->rawdata, $this->info, $this->user_agent) = $this->cache_obj->get($key);
				return true;
			} else {
				if ($this->verbose & 2) echo "... fetching ";
				$this->cache_obj->lock_obtain($key);
				return false;
			}
		} else {
			return false;
		}
	}
	
	private function cache_put($key) {
		if ($this->cache_lifetime && ($this->info['http_code'] == 200)) {
			$this->cache_obj->put($key, array($this->header, $this->rawdata, $this->info, $this->user_agent), $this->cache_lifetime);
		}
	}
	
	/**
	 * Dovlaci zadati url, uz kontrolu da to nemamo vec kesirano
	 * 
	 * @param string $url
	 * @param string $referer
	 * @param array $post_fields
	 * @param string $user_agent
	 * @param array $curl_req_options
	 */
	public function get_content($url, $referer = 'auto', $post_fields = false, $user_agent = 'web', $curl_req_options = array()) {
		$key = md5($url);
		if (!$this->cache_get($key)) {
			$this->__get_content($url, $referer, $post_fields, $user_agent, $curl_req_options);
			$this->cache_put($key);
		} 
	}
		
	private function __get_content($url, $referer = 'auto', $post_fields = false, $user_agent = 'web', $curl_req_options = array()) {
		// create a new curl resource
		$ch = curl_init();

		// set URL and other appropriate options
		$curl_opts = array();
		if (!is_array($curl_req_options)) $curl_req_options = array();
		$curl_default_opts = array(
			CURLOPT_URL => $url,
			CURLOPT_HEADER => 1,
			CURLINFO_HEADER_OUT => true,
			CURLOPT_TIMEOUT => 250,
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_SSL_VERIFYHOST => false,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_AUTOREFERER => true,
			CURLOPT_MAXREDIRS => 10,
		);
		if (!strcmp($user_agent, 'web')) {
			$buf = array_values(array_filter(file(self::$_path . "/data/grabber/user_agents_list_web.txt"), 'trim'));
			$user_agent = trim($buf[mt_rand(0, count($buf) - 1)]);
		} elseif (!strcmp($user_agent, 'mobile')) {
			$buf = array_values(array_filter(file(self::$_path . "/data/grabber/user_agents_list_mobile.txt"), 'trim'));
			$user_agent = trim($buf[mt_rand(0, count($buf) - 1)]);
		} 
		if ($user_agent) {
			$this->user_agent = $user_agent;
			$curl_default_opts[CURLOPT_USERAGENT] = $user_agent;
		}
		if (!strcmp($referer, 'auto')) {
			$referer = preg_replace('~(^[^/]+//[^/]+).*~', '$1', $url);
		}
		
		if ($referer) $curl_default_opts[CURLOPT_REFERER] = $referer;
		if ($post_fields) {
			if (is_array($post_fields)) {
				$post_fields = http_build_query($post_fields, '', '&');
			}
			$curl_default_opts[CURLOPT_POST] = 1;
			$curl_default_opts[CURLOPT_POSTFIELDS] = ($this->post_fields = $post_fields);
		}
		if (defined('CURLOPT_ENCODING')) {
			$curl_default_opts[CURLOPT_ENCODING] = 'gzip, deflate';
		}
		
		$curl_options = $curl_req_options + $curl_default_opts;
		$this->curl_options = $curl_options;
		
		if ($curl_options) {
			if (function_exists('curl_setopt_array')) {
				curl_setopt_array($ch, $curl_options);
			} else {
				foreach ($curl_options as $curl_key => $curl_value) {
					curl_setopt($ch, $curl_key, $curl_value);
				}
			}
		}
		
		// grab URL and save it
		$feed_data = curl_exec($ch);
		$this->info = curl_getinfo($ch);
		
		if ($this->curl_options[CURLOPT_HEADER]) {
			$this->header = trim(substr($feed_data, 0, $this->info['header_size']));
			$this->rawdata = trim(substr($feed_data, $this->info['header_size']));
		} else {
			$this->header = '';
			$this->rawdata = trim($feed_data);
		}
		
		if ($this->verbose & 1) {
			if (curl_errno($ch)) {
				echo "<br>\nUrl: $url " .
					 "\nReferer: $referer " .
					 "\nBrowser: $user_agent " .
					 "\ncurl error: " . curl_errno($ch) . " - " . curl_error($ch) . "\n";
			} else {
				echo "<br>\nUrl got: $url ";
			}
		}

		// close curl resource, and free up system resources
		curl_close($ch);
		$this->curl_options = $curl_options;
	}
	
	public function generate_xml($characterEncoding = 'utf-8', $content = null) {
		$content = $content ? $content : $this->rawdata;
		if (!$content) return false;

		$config1 = array(
            'show-body-only' => false,
            'quote-ampersand' => false,
            'quote-nbsp' => FALSE,
            'output-encoding' => 'UTF8',
            'quiet' => TRUE,
            'show-warnings' => FALSE,
            'tidy-mark' => FALSE,
            'indent' => 0,
            'wrap' => 0,
            'clean' => true,
            'bare' => true,
            'drop-font-tags' => false,
            'drop-proprietary-attributes' => false,
            'hide-comments' => TRUE,
            'numeric-entities' => FALSE,
            'write-back' => TRUE
		);
         
        $tidy = new tidy();

        $rawdata = isset($characterEncoding) && $characterEncoding != 'utf-8' ? 
        	iconv($characterEncoding, 'utf-8', $content) : 
        	$content;
        $tidy->parseString($rawdata, $config1, 'utf8');
        $tidy->cleanRepair();

        $config2 = array(
            'add-xml-decl' => TRUE,
            'bare' => true,
            'clean' => true,
        	'doctype' => 'omit',
            'drop-font-tags' => false,
            'drop-proprietary-attributes' => false,
            'force-output' => TRUE,
            'hide-comments' => TRUE,
            'indent' => 0,
            'numeric-entities' => FALSE,
            'output-xml' => TRUE,
            'output-encoding' => 'UTF8',
            'quiet' => TRUE,
            'quote-ampersand' => FALSE,
            'quote-nbsp' => FALSE,
            'show-warnings' => FALSE,
            'tidy-mark' => FALSE,
            'wrap' => 0,
            'write-back' => TRUE
        );

        $tidy2 = new tidy();
        $tidy2->parseString((string)$tidy, $config2, 'utf8');
        $tidy2->cleanRepair();
		return (string)$tidy2;
	}

}

