<?php

ini_set('display_errors', '1');
error_reporting(E_ALL);

require_once('../../inc/config.inc.php');

require_once(BASE_PATH . 'inc/database.inc.php');
require_once(BASE_PATH . 'inc/ocr.inc.php');
require_once(BASE_PATH . 'inc/sync.inc.php');
require_once(BASE_PATH . 'inc/users.inc.php');

abstract class API {
	/**
	 * Property: method
	 * The HTTP method this request was made in, either GET, POST, PUT or DELETE
	 */
	 
	protected $method = '';
	
	/**
	 * Property: endpoint
	 * The Model requested in the URI. eg: /files
	 */
	 
	protected $endpoint = '';
	
	/**
	 * Property: verb
	 * An optional additional descriptor about the endpoint, used for things that can
	 * not be handled by the basic methods. eg: /files/process
	 */
	 
	protected $verb = '';
	
	/**
	 * Property: args
	 * Any additional URI components after the endpoint and verb have been removed, in our
	 * case, an integer ID for the resource. eg: /<endpoint>/<verb>/<arg0>/<arg1>
	 * or /<endpoint>/<arg0>
	 */
	 
	protected $args = Array();
	
	/**
	 * Property: file
	 * Stores the input of the PUT request
	 */
	 
	 protected $file = Null;

	/**
	 * Constructor: __construct
	 * Allow for CORS, assemble and pre-process the data
	 */
	 
	public function __construct($request) {
		header("Access-Control-Allow-Orgin: *");
		header("Access-Control-Allow-Methods: *");
		header("Content-Type: application/json");

		$this->args = explode('/', rtrim($request, '/'));
		$this->endpoint = array_shift($this->args);
		
		if (array_key_exists(0, $this->args) && !is_numeric($this->args[0])) {
			$this->verb = array_shift($this->args);
		}

		$this->method = $_SERVER['REQUEST_METHOD'];
		if ($this->method == 'POST' && array_key_exists('HTTP_X_HTTP_METHOD', $_SERVER)) {
			if ($_SERVER['HTTP_X_HTTP_METHOD'] == 'DELETE') {
				$this->method = 'DELETE';
			} else if ($_SERVER['HTTP_X_HTTP_METHOD'] == 'PUT') {
				$this->method = 'PUT';
			} else {
				throw new Exception("Unexpected Header");
			}
		}

		switch($this->method) {
		case 'DELETE':
		case 'POST':
			$this->request = $this->_cleanInputs($_POST);
			break;
		case 'GET':
			$this->request = $this->_cleanInputs($_GET);
			break;
		case 'PUT':
			$this->request = $this->_cleanInputs($_GET);
			$this->file = file_get_contents("php://input");
			break;
		default:
			$this->_response('Invalid Method', 405);
			break;
		}
	}
	
	public function processAPI() {
		if ((int)method_exists($this, $this->endpoint) > 0) {
			return $this->_response($this->{$this->endpoint}($this->args));
		}
		
		return $this->_response("No Endpoint: $this->endpoint", 404);
	}
	
	private function _response($data, $status = 200) {
		header("HTTP/1.1 " . $status . " " . $this->_requestStatus($status));
		
		return json_encode($data);
	}
	
	private function _cleanInputs($data) {
		$clean_input = Array();
		
		if (is_array($data)) {
			foreach ($data as $k => $v) {
				$clean_input[$k] = $this->_cleanInputs($v);
			}
		} else {
			$clean_input = trim(strip_tags($data));
		}
		
		return $clean_input;
	}
	
	private function _requestStatus($code) {
		$status = array(  
			200 => 'OK',
			404 => 'Not Found',   
			405 => 'Method Not Allowed',
			500 => 'Internal Server Error',
		); 
		
		return ($status[$code])?$status[$code]:$status[500]; 
	}
}

class MyAPI extends API {
	protected $userId;

	public function __construct($request, $origin) {
		parent::__construct($request);
		
		global $db_conn;
		
		if (array_key_exists('username', $this->request)) {
			
		}

		// Abstracted out for example
		/*$APIKey = new Models\APIKey();
		$User = new Models\User();

		if (!array_key_exists('apiKey', $this->request)) {
			throw new Exception('No API Key provided');
		} else if (!$APIKey->verifyKey($this->request['apiKey'], $origin)) {
			throw new Exception('Invalid API Key');
		} else if (array_key_exists('token', $this->request) &&
			 !$User->get('token', $this->request['token'])) {

			throw new Exception('Invalid User Token');
		}

		$this->User = $User;*/
	}
	
	protected function login() {
		if ($this->method == 'GET') {
			if (!array_key_exists('username', $this->request) || !array_key_exists('password', $this->request))
				return array('error' => 'Missing required fields.');
			
			if (Users\login($this->request['username'], $this->request['password'], $accessKey))
				return array('result' => true, 'key' => $accessKey);
			
			return array('result' => false);
		} else
			return 'This method only accepts GET requests.';
	}
	
	protected function register() {
		if ($this->method == 'POST') {
			if (!array_key_exists('username', $this->request) ||
				!array_key_exists('password', $this->request) ||
				!array_key_exists('email', $this->request))
				return array('error' => 'Missing required fields.');
			
			if (Users\createAccount($this->request['username'], $this->request['password'], $this->request['email'], $error))
				return array('result' => true);
			
			return array('result' => false, 'error' => $error);
		} else
			return 'This method only accepts POST requests.';
	}
	
	protected function createRequestOCR() {
		if ($this->method == 'POST') {
			
		} else
			return 'This method only accepts POST requests.';
	}
	
	protected function retrieveResultOCR() {
		if ($this->method == 'GET') {
			
		} else
			return 'This method only accepts POST requests.';
	}
	
	protected function deleteResultOCR() {
		if ($this->method == 'DELETE') {
			
		} else
			return 'This method only accepts DELETE requests.';
	}
	
	protected function loadData() {
		if ($this->method == 'GET') {
			if (!array_key_exists('key', $this->request))
				return array('error' => 'Missing required fields.');
			
			return Sync\load($userId);
		} else
			return 'This method only accepts GET requests.';
	}
	
	protected function storeData() {
		if ($this->method == 'PUT') {
			Sync\store($userId, 'blob');
		} else
			return 'This method only accepts PUT requests.';
	}
	
	protected function getGameResults() {
		//	Query Santa Casa API
		
		if ($this->method == 'GET') {
			
		} else
			return 'This method only accepts GET requests.';
	}
}

if (!array_key_exists('HTTP_ORIGIN', $_SERVER))
	$_SERVER['HTTP_ORIGIN'] = $_SERVER['SERVER_NAME'];

try {
	$API = new MyAPI($_REQUEST['request'], $_SERVER['HTTP_ORIGIN']);
	
	echo $API->processAPI();
} catch (Exception $e) {
	echo json_encode(Array('error' => $e->getMessage()));
}

?>
