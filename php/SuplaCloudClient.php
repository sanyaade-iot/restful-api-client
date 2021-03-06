<?php
/*
 This program is free software; you can redistribute it and/or
 modify it under the terms of the GNU General Public License
 as published by the Free Software Foundation; either version 2
 of the License, or (at your option) any later version.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with this program; if not, write to the Free Software
 Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */



class SuplaCloudClient
{
	protected $server;
	protected $clientId;
	protected $secret;
	protected $username;
	protected $password;
	
	protected $debug;
	protected $token;
	protected $auto_logout;
	protected $last_error;
	
	public function __construct($server_params, $auto_logout = true, $debug = false)
	{
		$this->server = $server_params['server'];
		$this->clientId = $server_params['clientId'];
		$this->secret = $server_params['secret'];
		$this->username = $server_params['username'];
		$this->password = $server_params['password'];
		
		$this->debug = $debug;
		$this->token = null;
		$this->auto_logout = $auto_logout;
	}
	
	private function setLastError($error) {
		$this->last_error = $error;
	}
	
	private function remoteRequest($data, $path, $method = 'POST', $bearer = false ) {
	
		$data_string = '';
		$result = FALSE;
		$access_token = null;
		
		if ( $bearer
			 && ($access_token = $this->getAccessToken()) == '' ) {
			return false;
		}
		
		if ( $method == 'GET' ) {
			$data_string = @http_build_query($data);
			
			if ( $data_string !== false ) {
				$path .= '/' . $data_string;
				$data_string = null;
			}
			
		} else {
			$data_string = json_encode($data);
		}
			
		$ch = curl_init('https://'.$this->server.$path);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
		
		if ( $method != 'GET' ) {
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
		}
		
		if ( $bearer ) {
			curl_setopt($ch, CURLOPT_HTTPHEADER, array( 'Authorization: Bearer ' . $access_token ) );
		} else {

			if ( strlen(@$data_string) > 0 ) {
				curl_setopt($ch, CURLOPT_HTTPHEADER, array(
						'Content-Type: application/json',
						'Content-Length: ' . strlen($data_string))
						);
			}
			
		}
		
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		$cresult = curl_exec($ch);
		$result = false;
	
		$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	
		if ( curl_errno($ch) == 0 ) {
			
			if ( $code === 200 ) {
				$result = json_decode($cresult);
			} else {
				$this->setLastError("HTTP: ". $code);
			}
			
		} else {
			
			$this->setLastError("CURL ERR: ". curl_error($ch));
			
		}

		if ( $this->debug ) {
			var_dump($cresult);
			var_dump($result);
		}
	
		curl_close( $ch );	
		return $result;
	}
		
	private function tokenRequest() {
		
		$params = array("client_id" => $this->clientId,
				"client_secret" => $this->secret,
				"grant_type" => 'password',
				"username" => $this->username,
				"password" => $this->password);
		
		$result = $this->remoteRequest($params, '/oauth/v2/token');
		
		if ( $result !== FALSE
			 && @$result->token_type == 'bearer' ) {
		
			$result->expires_in = time() + intval(@$result->expires_in);
			$this->token = $result;
			
			return true;
			
		} else if ( $result === FALSE
				 && $this->last_error === null ) {
				
				$this->setLastError('Unknown error');
			
		}

		
		$this->token = null;
		return false;
	}
	
	private function accessTokenExists() {
		return $this->token !== null && $this->token->expires_in > time()+5 && $this->token->access_token != '';
	}
	
	private function getAccessToken() {
		
		if ( $this->accessTokenExists() == false ) {
			$this->tokenRequest();
		}
	
		return @$this->token->access_token;
	
	}
	
	private function autoLogout() {
		if ( $this->auto_logout === true ) {
			$this->logout();
		}
	}
	
	private function apiGET($path, $data = null) {
	
		if ( is_array($data) )
			foreach($data as $value) {
				$path .= '/' . urlencode($value);
			}
	
		$result = $this->remoteRequest(null, $path, 'GET', true);
		
		if ( $result !== false ) {
			return $result;
		}
			
		
		return false;
	}
	
	private function apiP($path, $method, $data = null) {
	
		$result = $this->remoteRequest($data, $path, $method, true);
	
		if ( $result !== false ) {
			return @$result->data;
		}
			
		return false;
	}
	
	private function apiPOST($path, $data = null) {
			
		return $this->apiP($path, 'POST', $data);
	}
	
	private function apiPUT($path, $data = null) {
	
		return $this->apiP($path, 'PUT', $data);
	}
	
	private function apiPATCH($path, $data = null) {
	
		return $this->apiP($path, 'PATCH', $data);
	}
	
	private function getResult($path) {
		
		$result = $this->apiGET('/api'.$path);
		$this->autoLogout();
		
		return $result;
	}
	
	private function post($path, $data = null) {
		
		$result = $this->apiPOST('/api'.$path, $data);
		$this->autoLogout();
		
		return $result;
	}
	
	private function put($path, $data = null) {
	
		$result = $this->apiPUT('/api'.$path, $data);
		$this->autoLogout();
	
		return $result;
	}
	
	private function patch($path, $data = null) {
	
		$result = $this->apiPATCH('/api'.$path, $data);
		$this->autoLogout();
	
		return $result;
	}
	
	public function getLastError() {
		return $this->last_error;
	}
	
	public function setDebug($debug) {
		$this->debug = $debug;
	}
	
	public function setAutoLogout($auto_logout) {
		$this->auto_logout = $auto_logout;
	}
	
	public function getToken() {
		$this->getAccessToken();
		return serialize($this->token);
	}
	
	public function setToken($token) {
		$this->token = unserialize($token);
	}
	
	public function logout() {
		
		if (  $this->accessTokenExists() ) {
			$this->apiGET('/api/logout', [@$this->token->refresh_token]);
		}
	
		$this->token = null;
	}
	
	public function getServerInfo() {
		
		return $this->getResult('/server-info');
	}
	
	public function locations() {
		
		return $this->getResult('/locations');
	}
	
	public function accessIDs() {
	
		return $this->getResult('/accessids');	
	}
	
	public function ioDevices() {
		
		return $this->getResult('/iodevices');
	}
		
	public function ioDevice($devid) {
	
		return $this->getResult('/iodevices/'.$devid);
	}
	
	public function temperatureLog_ItemCount($channelid) {
		
		return $this->getResult('/channels/'.$channelid.'/temperature-log-count');
	}
	
	public function temperatureLog_GetItems($channelid, $offset = 0, $limit = 0) {
		
		return $this->getResult('/channels/'.$channelid.'/temperature-log-items?offset='.$offset.'&limit='.$limit);
	}
	
	public function temperatureAndHumidityLog_ItemCount($channelid) {
	
		return $this->getResult('/channels/'.$channelid.'/temperature-and-humidity-count');
	}
	
	public function temperatureAndHumidityLog_GetItems($channelid, $offset = 0, $limit = 0) {
	
		return $this->getResult('/channels/'.$channelid.'/temperature-and-humidity-items?offset='.$offset.'&limit='.$limit);
	}
	
	public function channel($channelid) {
		
		return $this->getResult('/channels/'.$channelid);
	}
	
	public function channel_SetRGBW($channelid, $color, $color_brightness, $brightness) {
	
		$data = array('color' => $color, 
		              'color_brightness' => $color_brightness,
				      'brightness' => $brightness);
		
		return $this->put('/channels/'.$channelid, $data);
	}
	
	public function channel_SetRGB($channelid, $color, $color_brightness) {
	
		$data = array('color' => $color, 
		              'color_brightness' => $color_brightness);
	
		return $this->put('/channels/'.$channelid, $data);
	}
	
	public function channel_SetBrightness($channelid, $brightness) {
	
		$data = array('brightness' => $brightness);
	
		return $this->put('/channels/'.$channelid, $data);
	}
	
	public function channel_TurnOn($channelid) {
	
		return $this->patch('/channels/'.$channelid, array('action' => 'turn-on'));
	}
	
	public function channel_TurnOff($channelid) {
	
		return $this->patch('/channels/'.$channelid, array('action' => 'turn-off'));
	}
	
	public function channel_Open($channelid) {
	
		return $this->patch('/channels/'.$channelid, array('action' => 'open'));
	}
	
	public function channel_OpenClose($channelid) {
	
		return $this->patch('/channels/'.$channelid, array('action' => 'open-close'));
	}
	
	public function channel_Shut($channelid, $percent = 100) {
	
		return $this->patch('/channels/'.$channelid, array('action' => 'shut', 'percent' => $percent));
	}

	public function channel_Reveal($channelid, $percent = 100) {
	
		return $this->patch('/channels/'.$channelid, array('action' => 'reveal', 'percent' => $percent));
	}
	
	public function channel_Stop($channelid) {

		return $this->patch('/channels/'.$channelid, array('action' => 'stop'));
	}
	
};

