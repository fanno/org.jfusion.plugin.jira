<?php
defined('_JEXEC') or die('Restricted access');

class JFusionHelper_jira
{
	private $curl;
	private $url;
	private $response = "";
	private $headers = array();

	private $method = "GET";
	private $params = null;
	private $contentType = null;
	private $file = null;

	/**
	 * Returns the name for this plugin
	 *
	 * @return string
	 */
	function getJname() {
		return 'jira';
	}

	/**
	 * Private Constructor, sets default options
	 */
	function __construct() {
		$params = JFusionFactory::getParams($this->getJname());

		$this->curl = curl_init();
		curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($this->curl, CURLOPT_AUTOREFERER, true); // This make sure will follow redirects
		curl_setopt($this->curl, CURLOPT_FOLLOWLOCATION, true); // This too
		curl_setopt($this->curl, CURLOPT_HEADER, true); // THis verbose option for extracting the headers



		$this->setUrl($params->get('source_url'));
		$this->setCredentials($params->get('rest_username'), $params->get('rest_password'));

		$this->setContentType('application/json');
	}

	/**
	 * get user
	 *
	 * @param string $username holds the new user data
	 *
	 * @access public
	 * @return object
	 */
	public function getUser($username) {
		$user = null;
		$this->get('user', '?username=' . $username. '&expand=groups');
		if ($this->getResponseCode() == 200) {
			$user = $this->getResponse();
		}
		return $user;
	}

	/**
	 * create user
	 *
	 * @param stdclass $userinfo
	 *
	 * @access   public
	 * @return object
	 */
	public function createUser($userinfo) {
		$success = false;
		$data = new stdclass();

		$data->name = $userinfo->username;
		if (isset($userinfo->password_clear)) {
			$data->password = $userinfo->password_clear;
		}
		$data->emailAddress = $userinfo->email;
		$data->displayName = $userinfo->name;

		$data = json_encode($data);

		$this->post('user', $data);
		if ($this->getResponseCode() == 201) {
			$success = true;
		}
		return $success;
	}

	/**
	 * update password
	 *
	 * @param stdclass $userinfo
	 *
	 * @access   public
	 * @return object
	 */
	public function updateEmail($userinfo) {
		$success = false;
		$data = new stdclass();

		$data->emailAddress = $userinfo->email;

		$data = json_encode($data);

		$this->put('user?username=' . $userinfo->username, $data);

		if ($this->getResponse() && $this->getResponseCode() == 200) {
			$success = true;
		}
		return $success;
	}

	/**
	 * update password
	 *
	 * @param stdclass $userinfo
	 *
	 * @access   public
	 * @return object
	 */
	public function updatePassword($userinfo) {
		$success = false;
		$data = new stdclass();

		$data->password = $userinfo->password_clear;

		$data = json_encode($data);

		$this->put('user?username=' . $userinfo->username, $data);

		if ($this->getResponse() && $this->getResponseCode() == 200) {
			$success = true;
		}
		return $success;
	}


	/**
	 * update block
	 *
	 * @param stdclass $userinfo
	 *
	 * @access   public
	 * @return object
	 */
	public function updateBlock($userinfo) {
		$success = false;
		$data = new stdclass();

		if ($userinfo->block) {
			$data->active = 0;
		} else {
			$data->active = 1;
		}

		$data = json_encode($data);

		$this->put('user?username=' . $userinfo->username, $data);

		if ($this->getResponse() && $this->getResponseCode() == 200) {
			$success = true;
		}
		return $success;
	}

	/**
	 * delete user
	 *
	 * @param string $username holds the new user data
	 *
	 * @access public
	 * @return boolean
	 */
	public function deleteUser($username) {
		$success = false;
		$this->delete('user', '?username=' . $username);
		if ($this->getResponseCode() == 204) {
			$success = true;
		}
		return $success;
	}

	/**
	 * getUserList
	 *
	 * @access public
	 *
	 * @param int $limitstart
	 * @param int $limit
	 *
	 * @return array
	 */
	public function getUserList($limitstart = 0, $limit = 0) {
		$users = array();

		if ($limit == 0) {
			$limit = $this->getUserCount();
		}
		$total = $limitstart + $limit;
		while (true) {
			$this->get('group', '?groupname=jira-users&expand=users[' . $limitstart . ':' . $total . ']');
			if ($this->getResponseCode() == 200) {
				$responce = $this->getResponse();
				foreach($responce->users->items as $item) {
					$user = new stdClass();

					$user->userid = $item->name;
					$user->username = $item->name;
					$user->email = $item->emailAddress;

					$users[] = $user;
					$limitstart++;
				}
				if (count($responce->users->items) != 50 || count($users) >= $limit) {
					break;
				}
			} else {
				break;
			}
		}
		return $users;
	}

	/**
	 * getUserCount
	 *
	 * @access public
	 * @return int
	 */
	public function getUserCount() {
		$count = 0;

		$this->get('group', '?groupname=jira-users');
		if ($this->getResponseCode() == 200) {
			$responce = $this->getResponse();
			$count = $responce->users->size;
		}
		return $count;
	}

	/**
	 * check password
	 *
	 * @access   public
	 *
	 * @param string $user
	 * @param string $pass
	 *
	 * @return object
	 */
	public function checkPassword($user, $pass) {
		$client = new self();
		$client->setCredentials($user, $pass);
		return $client->ping();
	}

	/**
	 * get groups
	 *
	 * @access   public
	 * @return object
	 */
	public function getGroups() {
		$this->get('groups/picker');

		return $this->getResponse();
	}

	/**
	 * add group
	 *
	 * @access   public
	 *
	 * @param string $username
	 * @param string $group
	 *
	 * @return object
	 */
	public function addGroup($username, $group) {
		$data = new stdclass();

		$data->name = $username;

		$data = json_encode($data);

		$this->post('group/user?groupname=' . $group , $data);
	}

	/**
	 * remove group
	 *
	 * @access   public
	 *
	 * @param $username
	 * @param $group
	 *
	 * @return object
	 */
	public function removeGroup($username, $group) {
		$this->delete('group/user', '?groupname=' . $group . '&username=' . $username);
	}


	/**
	 * @return bool
	 */
	public function ping() {
		$result = false;

		$this->get('');

		if (isset($this->headers['x-arequestid']) && isset($this->headers['x-asessionid'])  && isset($this->headers['x-seraph-loginreason'])) {
			if ($this->headers['x-seraph-loginreason'] == 'OK') {
				$result = true;
			}
		}
		return $result;

	}

	/**
	 * @return string
	 */
	public function getErrorMessage() {
		$result = 'Unknown';
		if (isset($this->headers['x-seraph-loginreason']) && $this->headers['x-seraph-loginreason'] != 'OK') {
			$result = $this->headers['x-seraph-loginreason'];
		} else {
			$responce = $this->getResponse();
			if ($responce && isset($responce->errorMessages) && !empty($responce->errorMessages)) {
				$result = implode(' , ', $responce->errorMessages);
			} else if (isset($this->headers['message']) ) {
				$result = $this->headers['message'];
			}
		}
		return $result;
	}


	/**
	 * @param string $path
	 */
	public function execute($path) {
		$url = $this->url;
		if($this->method === "POST") {
			$url .=  $path;

			curl_setopt($this->curl, CURLOPT_POST, true);
			curl_setopt($this->curl, CURLOPT_POSTFIELDS, $this->params);
		} else if($this->method == "GET") {
			$url .= $path. $this->treatURL();

			curl_setopt($this->curl, CURLOPT_HTTPGET, true);
		} else if($this->method === "PUT") {
			$url .= $path;

			curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, $this->method);
			curl_setopt($this->curl, CURLOPT_POSTFIELDS, $this->params);
		} else if($this->method === "DELETE") {
			$url .= $path . $this->treatURL();

			curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, $this->method);
		} else {
			$url .= $path;

			curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, $this->method);
		}
		if($this->contentType != null) {
			curl_setopt($this->curl, CURLOPT_HTTPHEADER, array("Content-Type: " . $this->contentType));
		}

		curl_setopt($this->curl, CURLOPT_URL, $url);
		$r = curl_exec($this->curl);
		if (curl_errno($this->curl) !== 0) {
			$this->headers['message'] = curl_error($this->curl);
		}
		$this->treatResponse($r); // Extract the headers and response
	}

	/**
	 * Treats URL
	 */
	private function treatURL() {
		$url = '';
		if(is_array($this->params) && count($this->params) >= 1) { // Transform parameters in key/value pars in URL
			$url = '?';
			$params = array();
			foreach($this->params as $k=>$v) {

				$params[] = urlencode($k)."=".urlencode($v);
			}
			$params = implode('&', $params);
		} else {
			$params = $this->params;
		}


		return $url . $params;
	}

	/**
	 * Treats the Response for extracting the Headers and Response
	 *
	 * @param string $r
	 */
	private function treatResponse($r) {
		if ($r && strlen($r) > 0) {
			$parts  = explode("\n\r",$r); // HTTP packets define that Headers end in a blank line (\n\r) where starts the body

			$test = $parts[0];

			foreach ($parts as $key => $part) {
				if(preg_match('#HTTP/1.[0-1] 100 Continue#', $part)) {
					unset($parts[$key]);
				}
			}

			$parts = array_values($parts);
			$headers = explode("\n", $parts[0]);

			$header = array();
			foreach ($headers as $part) {
				$part = trim($part);
				if (!empty($part)) {
					$header[] = trim($part);
				}
			}
			$body = null;
			if (isset($parts[1])) {
				$body = trim($parts[1]);
			}



			list($protocol, $code, $message) =  explode(" ", $header[0], 3);
			$this->headers['code'] = $code;
			$this->headers['message'] = $message;
			unset($header[0]);

			foreach ($header as $value) {
				list($key, $value)= explode(':', $value, 2);
				$this->headers[strtolower($key)] = trim($value);
			}

			$this->response = trim($body);

			$this->response = json_decode($this->response);
		}
	}

	/**
	 * @return array
	 */
	public function getHeaders() {
		return $this->headers;
	}

	/**
	 * @return string
	 */
	public function getResponse() {
		return $this->response ;
	}

	/**
	 * HTTP response code (404,401,200,etc)
	 *
	 * @return int
	 */
	public function getResponseCode() {
		return (int) $this->headers['code'];
	}

	/**
	  * HTTP response message (Not Found, Continue, etc )
	 *
	  * @return string
	  */
	public function getResponseMessage() {
		return $this->headers['message'];
	}

	/**
	  * Content-Type (text/plain, application/xml, etc)
	 *
	  * @return string
	  */
	public function getResponseContentType() {
		return $this->headers['content-type'];
	}

	/**
	 * This sets that will not follow redirects
	 *
	 */
	public function setNoFollow() {
		curl_setopt($this->curl, CURLOPT_AUTOREFERER, false);
		curl_setopt($this->curl, CURLOPT_FOLLOWLOCATION, false);
	}

	/**
	 * This closes the connection and release resources
	 *
	 */
	public function close() {
//		curl_close($this->curl);
//		$this->curl = null ;
		if($this->file !=null) {
			fclose($this->file);
		}
	}

	/**
	 * Sets the URL to be Called
	 *
	 * @param $url
	 *
	 */
	public function setUrl($url) {
		$this->url = $url . 'rest/api/2/';
	}

	/**
	 * Set the Content-Type of the request to be send
	 * Format like "application/xml" or "text/plain" or other
	 * @param string $contentType
	 */
	public function setContentType($contentType) {
		$this->contentType = $contentType;
	}

	/**
	 * Set the Credentials for BASIC Authentication
	 * @param string $user
	 * @param string $pass
	 */
	public function setCredentials($user, $pass) {
		if($user != null) {
			curl_setopt($this->curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
			curl_setopt($this->curl, CURLOPT_USERPWD, $user . ':' . $pass);
		}
	}

	/**
	 * Set the Request HTTP Method
	 * For now, only accepts GET and POST
	 *
	 * @param string $method
	 */
	public function setMethod($method) {
		$this->method = $method;
	}

	/**
	 * Set Parameters to be send on the request
	 * It can be both a key/value par array (as in array("key"=>"value"))
	 * or a string containing the body of the request, like a XML, JSON or other
	 * Proper content-type should be set for the body if not a array
	 * @param mixed $params
	 */
	public function setParameters($params) {
		$this->params = $params;
	}

	/**
	 * Convenience method wrapping a commom POST call
	 *
	 * @param string $url
	 * @param null   $params
	 * @param string $contentType ="multpary/form-data" [optional] commom post (multipart/form-data) as default
	 *
	 * @internal param string $user =null [optional]
	 * @internal param null $pwd
	 * @internal param \params $mixed
	 * @internal param string $password =null [optional]
	 */
	public function post($url, $params = null, $contentType = "multipart/form-data") {
		$this->call("POST", $url, $params, $contentType);
	}

	/**
	 * Convenience method wrapping a commom PUT call
	 *
	 * @param string $path
	 * @param string $body
	 * @param string $contentType =null [optional]
	 *
	 * @internal param string $user =null [optional]
	 * @internal param null $pwd
	 * @internal param string $url
	 * @internal param string $password =null [optional]
	 */
	public function put($path, $body, $contentType = null) {
		$this->call("PUT", $path, $body, $contentType);
	}

	/**
	 * Convenience method wrapping a commom GET call
	 *
	 * @param       $path
	 * @param array $params
	 *
	 * @internal param string $url
	 * @internal param string $user =null [optional]
	 * @internal param null $pwd
	 *
	 * @internal param \params $array
	 * @internal param string $password =null [optional]
	 */
	public function get($path, $params = null) {
		$this->call("GET", $path, $params);
	}

	/**
	 * Convenience method wrapping a commom delete call
	 *
	 * @param        $path
	 * @param array  $params
	 *
	 * @internal param string $user =null [optional]
	 * @internal param null $pwd
	 *
	 * @internal param string $url
	 * @internal param \params $array
	 * @internal param string $password =null [optional]
	 */
	public function delete($path, $params = null) {
		$this->call("DELETE", $path, $params);
	}

	/**
	 * Convenience method wrapping a commom custom call
	 *
	 * @param string $method
	 * @param        $path
	 * @param string $body
	 *
	 * @internal param string $url
	 * @internal param string $user =null [optional]
	 * @internal param null $pwd
	 * @internal param string $password =null [optional]
	 */
	public function call($method, $path ,$body) {
		$this->setMethod($method);
		$this->setParameters($body);
		$this->execute($path);
		$this->close();
	}
}
