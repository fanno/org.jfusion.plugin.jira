<?php namespace JFusion\Plugins\jira;

/**
 * @category   Plugins
 * @package    JFusion\Plugins
 * @subpackage jira
 * @author     Morten Hundevad <fannoj@gmail.com>
 * @copyright  2008 JFusion. All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.jfusion.org
 */

use JFusion\Plugin\Plugin;
use JFusion\User\Userinfo;
use stdClass;

/**
 * JFusion user class for jira
 *
 * @category   Plugins
 * @package    JFusion\Plugins
 * @subpackage jira
 * @author     Morten Hundevad <fannoj@gmail.com>
 * @copyright  2008 JFusion. All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.jfusion.org
 */
class Helper extends Plugin
{
	private $curl;
	private $url;
	private $response = null;
	private $headers = array();
	private $cookies = array();

	private $method = 'GET';
	private $fields = null;
	private $contentType = null;

	/**
	 * @param string $instance instance name of this plugin
	 */
	function __construct($instance) {
		parent::__construct($instance);
		$this->curl = curl_init();

		curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($this->curl, CURLOPT_AUTOREFERER, true); // This make sure will follow redirects
		curl_setopt($this->curl, CURLOPT_FOLLOWLOCATION, true); // This too
		curl_setopt($this->curl, CURLOPT_HEADER, true); // THis verbose option for extracting the headers

		$this->setUrl($this->params->get('source_url'));
		$this->setCredentials($this->params->get('rest_username'), $this->params->get('rest_password'));
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
			$result = $this->getResponse();

			if ($result) {
				$user = new stdClass();
				$user->username = $result->name;
				$user->name = $result->displayName;
				$user->email = $result->emailAddress;
				if ($result->active) {
					$user->block = 0;
					$user->activation = 0;
				} else {
					$user->block = 1;
					//user not active generate a random code

					$result->activation = $this->genRandomPassword(13);
				}

				$user->groups = array();
				$user->groupnames = array();

				foreach($result->groups->items as $group) {
					if (!isset($user->group_id)) {
						$user->group_id = $group->name;
					}
					$user->groups[] = $group->name;
					$user->groupnames[] = $group->name;
				}
			}
		}
		return $user;
	}

	/**
	 * create user
	 *
	 * @param Userinfo $userinfo
	 *
	 * @access public
	 * @return boolean
	 */
	public function createUser(Userinfo $userinfo) {
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
	 * @param Userinfo $userinfo
	 *
	 * @access public
	 * @return boolean
	 */
	public function updateEmail(Userinfo $userinfo) {
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
	 * @param Userinfo $userinfo
	 *
	 * @access public
	 * @return boolean
	 */
	public function updatePassword(Userinfo $userinfo) {
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
	 * @param Userinfo $userinfo
	 *
	 * @access public
	 * @return boolean
	 */
	public function updateBlock(Userinfo $userinfo) {
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
	 * @return array
	 */
	public function getUserList() {
		$users = array();
		/*
				$letters = range('a', 'z');
				$letters = 'a e i o u y';
				$letters = explode(' ', $letters);

				foreach($letters as $letter) {
					$start = 1;
					while(true) {
						$this->get('user/search', '?username=' . $letter . '&startAt=' . $start . '&maxResults=1000&includeInactive=1');

						if ($this->getResponseCode() == 200) {
							$responce = $this->getResponse();
							if ($responce && is_array($responce)) {
								foreach($responce as $user) {
									if (!isset($users[$user->name])) {
										$u = new stdClass();
										$u->id = $user->key;
										$u->name = $user->name;

										$users[$user->name] = $u;
									}
								}
								if (count($responce) != 1000) {
									break;
								}
								$start += 1000;
							} else {
								break;
							}
						} else {
							break;
						}
					}
				}
				$users = array_values($users);
		//		$this->get('user/picker', '?query=e&maxResults=1000');
		var_dump($users);
		die();
		*/
		return $users;
	}

	/**
	 * getUserCount
	 *
	 * @access public
	 * @return int
	 */
	public function getUserCount() {
		$result = $this->getUserList();
		return count($result);
	}

	/**
	 * check password
	 *
	 * @param string $user
	 * @param string $pass
	 *
	 * @access public
	 * @return boolean
	 */
	public function checkPassword($user, $pass) {
		$client = new self($this->getJname());
		$client->setCredentials($user, $pass);
		return $client->ping();
	}

	/**
	 * get groups
	 *
	 * @access public
	 * @return object
	 *
	 * return array
	 */
	public function getGroups() {
		$this->get('groups/picker');

		$response = $this->getResponse();
		$usergrouplist = array();
		if ($response) {
			if ($this->getResponseCode() == 200) {
				if (isset($response->groups)) {
					foreach($response->groups as $group) {
						$g = new stdClass;
						$g->id = $group->name;
						$g->name = $group->html;
						$usergrouplist[] = $g;
					}
				}
			}
		}
		return $usergrouplist;
	}

	/**
	 * add group
	 *
	 * @param string $username
	 * @param string $group
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
	 * @param $username
	 * @param $group
	 */
	public function removeGroup($username, $group) {
		$this->delete('group/user', '?groupname=' . $group . '&username=' . $username);
	}


	/**
	 * @return boolean
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
		if($this->method === 'POST') {
			$url .=  $path;

			curl_setopt($this->curl, CURLOPT_POST, true);
			curl_setopt($this->curl, CURLOPT_POSTFIELDS, $this->fields);
		} else if($this->method == 'GET') {
			$url .= $path. $this->treatURL();

			curl_setopt($this->curl, CURLOPT_HTTPGET, true);
		} else if($this->method === 'PUT') {
			$url .= $path;

			curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, $this->method);
			curl_setopt($this->curl, CURLOPT_POSTFIELDS, $this->fields);
		} else if($this->method === 'DELETE') {
			$url .= $path . $this->treatURL();

			curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, $this->method);
		} else {
			$url .= $path;

			curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, $this->method);
		}
		if($this->contentType != null) {
			curl_setopt($this->curl, CURLOPT_HTTPHEADER, array('Content-Type: ' . $this->contentType));
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
		if(is_array($this->fields) && count($this->fields) >= 1) { // Transform parameters in key/value pars in URL
			$url = '?';
			$fields = array();
			foreach($this->fields as $k=>$v) {
				$fields[] = urlencode($k) . '=' . urlencode($v);
			}
			$fields = implode('&', $fields);
		} else {
			$fields = $this->fields;
		}
		return $url . $fields;
	}

	/**
	 * Treats the Response for extracting the Headers and Response
	 *
	 * @param string $responce
	 */
	private function treatResponse($responce) {
		$this->headers = array();
		$this->cookies = array();
		$this->response = null;
		if ($responce && strlen($responce) > 0) {
			$parts  = explode("\n\r", $responce); // HTTP packets define that Headers end in a blank line (\n\r) where starts the body

			$test = $parts[0];

			foreach ($parts as $key => $part) {
				if(preg_match('#HTTP/1.[0-1] 100 Continue#', $part)) {
					unset($parts[$key]);
				}
			}

			$parts = array_values($parts);
			$headers = explode("\n", $parts[0]);

			$header = array();
			$cookies = array();
			foreach ($headers as $part) {
				$part = trim($part);
				if (!empty($part)) {
					if (strpos($part, 'Set-Cookie') === 0) {
						list(, $part) = explode(':', $part, 2);

						$part = explode(';', $part);

						$cookie = new stdClass();

						list($cookie->name, $cookie->value) = explode('=', $part[0]);
						$cookie->name = trim($cookie->name);
						$cookie->value = trim($cookie->value);
						$cookie->domain = null;
						$cookie->path = null;
						$cookie->expires = null;
						$cookie->secure = false;
						$cookie->httpOnly = false;

						unset($part[0]);

						foreach($part as $value) {
							$value = trim($value);
							if (!empty($value)) {
								if ( strtolower($value) == 'secure' ) {
									$cookie->secure = true;
								} else if ( strtolower($value) == 'httponly' ) {
									$cookie->httpOnly = true;
								} else {
									list($type, $value) = explode('=', $value, 2);

									$type = strtolower($type);
									$cookie->$type = $value;
								}
							}
						}
						$this->cookies[] = $cookie;
					} else {
						$header[] = $part;
					}

				}
			}

			$body = null;
			if (isset($parts[1])) {
				$body = trim($parts[1]);
			}

			list($protocol, $code, $message) =  explode(' ', $header[0], 3);
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
	 * @return array
	 */
	public function getCookies() {
		return $this->cookies;
	}

	/**
	 * @return mixed
	 */
	public function getResponse() {
		return $this->response;
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
	 * Sets the URL to be Called
	 *
	 * @param $url
	 */
	public function setUrl($url) {
		$this->url = $url . 'rest/api/2/';
	}

	/**
	 * Set the Content-Type of the request to be send
	 * Format like "application/xml" or "text/plain" or other
	 *
	 * @param string $contentType
	 */
	public function setContentType($contentType) {
		$this->contentType = $contentType;
	}

	/**
	 * Set the Credentials for BASIC Authentication
	 *
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
	 *
	 * @param mixed $fields
	 */
	public function setParameters($fields) {
		$this->fields = $fields;
	}

	/**
	 * Convenience method wrapping a commom POST call
	 *
	 * @param string $url
	 * @param null   $fields
	 */
	public function post($url, $fields = null) {
		$this->call("POST", $url, $fields);
	}

	/**
	 * Convenience method wrapping a commom PUT call
	 *
	 * @param string $path
	 * @param string $body
	 */
	public function put($path, $body) {
		$this->call("PUT", $path, $body);
	}

	/**
	 * Convenience method wrapping a commom GET call
	 *
	 * @param string $path
	 * @param array  $fields
	 */
	public function get($path, $fields = null) {
		$this->call("GET", $path, $fields);
	}

	/**
	 * Convenience method wrapping a commom delete call
	 *
	 * @param string $path
	 * @param array  $fields
	 */
	public function delete($path, $fields = null) {
		$this->call("DELETE", $path, $fields);
	}

	/**
	 * Convenience method wrapping a commom custom call
	 *
	 * @param string $method
	 * @param string $path
	 * @param string $body
	 */
	private function call($method, $path ,$body) {
		$this->setMethod($method);
		$this->setParameters($body);
		$this->execute($path);
	}
}
