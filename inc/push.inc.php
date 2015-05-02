<?php

//	Cross-Platform Settings

define('SANDBOX_SERVERS', false);

//	iOS Settings

define('IOS_SANDBOX_CERTIFICATE_PATH', '');
define('IOS_PRODUCTION_CERTIFICATE_PATH', '');
define('IOS_CERTIFICATE_PASSPHRASE', '');

define('IOS_ROOT_CAFILE_PATH', '');

//	Android Settings

define('ANDROID_API_ACCESS_KEY', '');

class Push {
	public $message;
	public $devices = array();
	
	public $sound = true;
	
	function __construct($message) {
		$this->message = $message;
	}
	
	function addDevice($deviceId) {
		$devices[] = $deviceId;
	}
}

class AndroidPush extends Push {
	public $vibrate = true;
	
	public $title = 'Euro Millions';
	
	public $largeIcon = '';
	public $smallIcon = '';
	
	function send() {
		$msg = array 
		(
			'message' 	=> $this->message,
			'title'		=> $this->title,
			'vibrate'	=> (int) $this->vibrate,
			'sound'		=> (int) $this->sound,
			'largeIcon'	=> $this->largeIcon,
			'smallIcon'	=> $this->smallIcon
			
			//	'subtitle'	=> 'This is a subtitle. subtitle',
			//	'tickerText'	=> 'Ticker text here...Ticker text here...Ticker text here',
		);
		
		$fields = array
		(
			'registration_ids' 	=> $this->devices,
			'data'				=> $msg
		);
		
		$headers = array
		(
			'Authorization: key=' . ANDROID_API_ACCESS_KEY,
			'Content-Type: application/json'
		);
		
		$ch = curl_init();
		
		{
			curl_setopt($ch, CURLOPT_URL, 'https://android.googleapis.com/gcm/send');
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
		}
		
		$result = curl_exec($ch);
		
		curl_close($ch);
	}
}

class iOSPush extends Push {
	public $soundFile = 'default';
	public $contentAvailable = true;
	
	function send() {
		foreach ($devices as $d)
			_send($d);
	}
	
	function _send($device) {
		$ctx = stream_context_create();
		
		stream_context_set_option($ctx, 'ssl', 'local_cert', 
			(SANDBOX_SERVERS ? IOS_SANDBOX_CERTIFICATE_PATH : IOS_PRODUCTION_CERTIFICATE_PATH));
		stream_context_set_option($ctx, 'ssl', 'passphrase', IOS_CERTIFICATE_PASSPHRASE);
		stream_context_set_option($ctx, 'ssl', 'cafile', IOS_ROOT_CERTIFICATE_PATH);
		
		$fp = stream_socket_client(
			(SANDBOX_SERVERS ? 'ssl://gateway.sandbox.push.apple.com:2195' : 'ssl://gateway.push.apple.com:2195'), $err,
			$errstr, 60, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $ctx);
		
		if (!$fp)
			throw new Exception("Failed to Connect to APNS: $err $errstr");
		
		$body['aps'] = array(
			'alert' => $this->message,
			'sound' => $this->soundFile,
			'content-available' => (int) $this->contentAvailable
		);
		
		$payload = json_encode($body);
		
		$msg = chr(0) . pack('n', 32) . pack('H*', $deviceToken) . pack('n', strlen($payload)) . $payload;
		
		$result = fwrite($fp, $msg, strlen($msg));
		
		fclose($fp);
	}
}

//
// class WindowsPhonePush extends Push {
//     function send() {
//         
//     }
// }
//

?>