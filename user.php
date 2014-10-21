<?php

/**
 * file containing user function for the jfusion plugin
 *
 * PHP version 5
 *
 * @category   JFusion
 * @package    JFusionPlugins
 * @subpackage JIRA
 * @author     JFusion Team <webmaster@jfusion.org>
 * @copyright  2008 JFusion. All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.jfusion.org
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

/**
 * Load the JFusion framework
 */
require_once JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_jfusion' . DS . 'models' . DS . 'model.jfusion.php';
require_once JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_jfusion' . DS . 'models' . DS . 'model.abstractuser.php';
require_once JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_jfusion' . DS . 'models' . DS . 'model.jplugin.php';
/**
 * JFusion User Class for JIRA
 * For detailed descriptions on these functions please check the model.abstractuser.php
 *
 * @category   JFusion
 * @package    JFusionPlugins
 * @subpackage JIRA
 * @author     JFusion Team <webmaster@jfusion.org>
 * @copyright  2008 JFusion. All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.jfusion.org
 */

/**
 * Class JFusionUser_jira
 */
class JFusionUser_jira extends JFusionUser
{
    /**
     * returns the name of this JFusion plugin
     *
     * @return string name of current JFusion plugin
     */
    function getJname()
    {
        return 'jira';
    }	
	
    /**
     * get user
     *
     * @param object $userinfo holds the new user data
     *
     * @access public
     * @return object
     */
    function &getUser($userinfo)
    {
        //get the identifier
        list($identifier_type, $identifier) = $this->getUserIdentifier($userinfo, 'a.memberName', 'a.emailAddress');
        // initialise some objects

	    $params = JFusionFactory::getParams($this->getJname());
	    /**
	     * @ignore
	     * @var $helper JFusionHelper_jira
	     */
	    $helper = JFusionFactory::getHelper($this->getJname());

	    $result = $helper->getUser($userinfo->username);

        if ($result) {
        		$user = new stdClass();
	        	$user->username = $result->name;
	        	$user->name = $result->displayName;
	        	$user->email = $result->emailAddress;
	        	$user->original = $result;
	            if ($result->active) {
		            $user->block = 0;
		            $user->activation = 0;
	            } else {
		            $user->block = 1;
		            //user not active generate a random code
		            jimport('joomla.user.helper');
		            $result->activation = JUserHelper::genRandomPassword(13);
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

	        	if (isset($userinfo->password_clear)) {
		            if ($helper->checkPassword($userinfo->username, $userinfo->password_clear)) {
						$user->password = 'valid';
		            } else {
						$user->password = 'invalid';
		            }
	        	} else {
			        $user->password = 'unknown';
		        }
        } else {
			$user = null;
        }
        return $user;
    }

    /**
     * delete user
     *
     * @param object $userinfo holds the new user data
     *
     * @access public
     * @return array
     */
    function deleteUser($userinfo)
    {
        //setup status array to hold debug info and errors
        $status = array();
        $status['debug'] = array();
        $status['error'] = array();

	    /**
	     * @ignore
	     * @var $helper JFusionHelper_jira
	     */
	    $helper = JFusionFactory::getHelper($this->getJname());

        if ($helper->deleteUser($userinfo->username)) {
	        $status['debug'][] = JText::_('USER_DELETION') . ' ' . $userinfo->username;
        } else {
	        $status['error'][] = JText::_('USER_DELETION_ERROR') . ' : ' . $userinfo->username . ' : ' . $helper->getErrorMessage();
        }
        return $status;
    }

	/**
	 * @param object $userinfo
	 * @param object $existinguser
	 * @param array $status
	 *
	 * @return void

	function blockUser($userinfo, &$existinguser, &$status) {
	    //@ignore
	    //@var $helper JFusionHelper_jira
	    $helper = JFusionFactory::getHelper($this->getJname());

	    if ($helper->updateBlock($userinfo)) {
		    $status['debug'][] = JText::_('BLOCK_UPDATE') . ': ' . $existinguser->block . ' -> ' . $userinfo->block;
	    } else {
		    $status['error'][] = JText::_('BLOCK_UPDATE_ERROR') . $helper->getErrorMessage();
	    }
	}
*/

	/**
	 * @param object $userinfo
	 * @param object $existinguser
	 * @param array $status
	 *
	 * @return void

	function unblockUser($userinfo, &$existinguser, &$status) {
		//@ignore
		//@var $helper JFusionHelper_jira
		$helper = JFusionFactory::getHelper($this->getJname());

		if ($helper->updateBlock($userinfo)) {
			$status['debug'][] = JText::_('BLOCK_UPDATE') . ': ' . $existinguser->block . ' -> ' . $userinfo->block;
		} else {
			$status['error'][] = JText::_('BLOCK_UPDATE_ERROR') . $helper->getErrorMessage();
		}
	}
*/

    /**
     * destroy session
     *
     * @param object $userinfo holds the new user data
     * @param array  $options  Status array
     *
     * @access public
     * @return array
     */
    function destroySession($userinfo, $options)
    {
	    $status = array();
	    $status['error'] = array();
	    $status['debug'] = array();
	    return $status;
    }

    /**
     * create session
     *
     * @param object $userinfo holds the new user data
     * @param array  $options  options
     *
     * @access public
     * @return array
     */
    function createSession($userinfo, $options)
    {
        $status = array();
        $status['error'] = array();
        $status['debug'] = array();
        return $status;
    }

    /**
     * filterUsername
     *
     * @param string $username holds the new user data
     *
     * @access public
     * @return string
     */
    function filterUsername($username)
    {
        //no username filtering implemented yet
        return $username;
    }

    /**
     * updatePassword
     *
     * @param object $userinfo      holds the new user data
     * @param object &$existinguser holds the exsisting user data
     * @param array  &$status       Status array
     *
     * @access public
     * @return void
     */
    function updatePassword($userinfo, &$existinguser, &$status)
    {
	    /**
	     * @ignore
	     * @var $helper JFusionHelper_jira
	     */
	    $helper = JFusionFactory::getHelper($this->getJname());

	    if ($helper->updatePassword($userinfo)) {
		    $status['debug'][] = JText::_('PASSWORD_UPDATE') . ' ' . substr($existinguser->password, 0, 6) . '********';
	    } else {
		    $status['error'][] = JText::_('PASSWORD_UPDATE_ERROR') . ' ' . $helper->getErrorMessage();
	    }
    }

    /**
     * updateUsername
     *
     * @param object $userinfo      holds the new user data
     * @param object &$existinguser holds the exsisting user data
     * @param array  &$status       Status array
     *
     * @access public
     * @return void
     */
    function updateUsername($userinfo, &$existinguser, &$status)
    {
    }

    /**
     * updateEmail
     *
     * @param stdclass $userinfo      holds the new user data
     * @param stdclass &$existinguser holds the exsisting user data
     * @param array  &$status       Status array
     *
     * @access public
     * @return void
     */
    function updateEmail($userinfo, &$existinguser, &$status)
    {
	    /**
	     * @ignore
	     * @var $helper JFusionHelper_jira
	     */
	    $helper = JFusionFactory::getHelper($this->getJname());

        if ($helper->updateEmail($userinfo)) {
	        $status['debug'][] = JText::_('EMAIL_UPDATE') . ': ' . $existinguser->email . ' -> ' . $userinfo->email;
        } else {
	        $status['error'][] = JText::_('EMAIL_UPDATE_ERROR') . ' ' . $helper->getErrorMessage();
        }
    }

    /**
     * updateUsergroup
     *
     * @param object $userinfo      holds the new user data
     * @param object &$existinguser holds the exsisting user data
     * @param array  &$status       Status array
     *
     * @access public
     * @return void
     */
    function updateUsergroup($userinfo, &$existinguser, &$status)
    {
        $params = JFusionFactory::getParams($this->getJname());
        //get the usergroup and determine if working in advanced or simple mode

        $groups = JFusionFunction::getCorrectUserGroups($this->getJname(),$userinfo);
	    if (!isset($groups[0])) {
		    $status['error'][] = JText::_('GROUP_UPDATE_ERROR');
	    } else {
		    /**
		     * @ignore
		     * @var $helper JFusionHelper_jira
		     */
		    $helper = JFusionFactory::getHelper($this->getJname());

		    foreach($groups as $group) {
			    if (!in_array($group, $existinguser->groups)) {
				    $helper->addGroup($existinguser->username, $group);
			    }
		    }

			foreach($existinguser->groups as $group) {
				if (!in_array($group, $groups)) {
					$helper->removeGroup($existinguser->username, $group);
				}
			}
		    $status['debug'][] = JText::_('GROUP_UPDATE') . ': ' . implode (' , ', $existinguser->groups) . ' -> ' . implode (' , ', $groups);
	    }
    }

    /**
     * Creates a new user
     *
     * @param object $userinfo holds the new user data
     * @param array  &$status  Status array
     *
     * @access public
     * @return mixed null on fail or array with status
     */
    function createUser($userinfo, &$status)
    {
        //we need to create a new SMF user
        $params = JFusionFactory::getParams($this->getJname());
        $source_path = $params->get('source_path');
        
        $groups = JFusionFunction::getCorrectUserGroups($this->getJname(), $userinfo);
		
        if (!isset($groups[0])) {
            //TODO: change error message
            $status['error'][] = JText::_('GROUP_UPDATE_ERROR') . ": " . JText::_('ADVANCED_GROUPMODE_MASTER_NOT_HAVE_GROUPID');
        } else {
			/**
			 * @ignore
			 * @var $helper JFusionHelper_jira
			 */
			$helper = JFusionFactory::getHelper($this->getJname());

			if ($helper->createUser($userinfo)) {
				foreach($groups as $group) {
					$helper->addGroup($userinfo->username, $group);
				}
				//return the good news
				$status['debug'][] = JText::_('USER_CREATION');
				$status['userinfo'] = $this->getUser($userinfo);
			} else {
				//return the error
				$status['error'][] = JText::_('USER_CREATION_ERROR') . ' : ' . $helper->getErrorMessage();
			}
        }
        return $status;
    }
}
