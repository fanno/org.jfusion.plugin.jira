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

// no direct access
use JFusion\User\Userinfo;
use Joomla\Language\Text;

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
class User extends \JFusion\Plugin\User
{
	/**
	 * @var $helper Helper
	 */
	var $helper;
	
    /**
     * get user
     *
     * @param Userinfo $userinfo holds the new user data
     *
     * @access public
     * @return object
     */
    function &getUser(Userinfo $userinfo)
    {
	    $result = $this->helper->getUser($userinfo->username);

        if ($result) {
	        $result->password = 'unknown';
        }
        return $result;
    }

    /**
     * delete user
     *
     * @param Userinfo $userinfo holds the new user data
     *
     * @access public
     * @return array
     */
    function deleteUser(Userinfo $userinfo)
    {
        //setup status array to hold debug info and errors
        $status = array();
        $status['debug'] = array();
        $status['error'] = array();

        if ($this->helper->deleteUser($userinfo->username)) {
	        $status['debug'][] = Text::_('USER_DELETION') . ' ' . $userinfo->username;
        } else {
	        $status['error'][] = Text::_('USER_DELETION_ERROR') . ' : ' . $userinfo->username. ' : ' . $this->helper->getErrorMessage();
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
	    if ($this->helper->updateBlock($userinfo)) {
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
		if ($this->helper->updateBlock($userinfo)) {
			$status['debug'][] = JText::_('BLOCK_UPDATE') . ': ' . $existinguser->block . ' -> ' . $userinfo->block;
		} else {
			$status['error'][] = JText::_('BLOCK_UPDATE_ERROR') . $helper->getErrorMessage();
		}
	}
*/

    /**
     * destroy session
     *
     * @param Userinfo $userinfo holds the new user data
     * @param array  $options  Status array
     *
     * @access public
     * @return array
     */
    function destroySession(Userinfo $userinfo, $options)
    {
	    $status = array();
	    $status['error'] = array();
	    $status['debug'] = array();
	    return $status;
    }

    /**
     * create session
     *
     * @param Userinfo $userinfo holds the new user data
     * @param array  $options  options
     *
     * @access public
     * @return array
     */
    function createSession(Userinfo $userinfo, $options)
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
     * @param Userinfo $userinfo      holds the new user data
     * @param Userinfo &$existinguser holds the exsisting user data
     * @param array  &$status       Status array
     *
     * @access public
     * @return void
     */
    function updatePassword(Userinfo $userinfo, Userinfo &$existinguser, &$status)
    {
	    if ($this->helper->updatePassword($userinfo)) {
		    $status['debug'][] = Text::_('PASSWORD_UPDATE') . ' ' . substr($existinguser->password, 0, 6) . '********';
	    } else {
		    $status['error'][] = Text::_('PASSWORD_UPDATE_ERROR') . ' ' . $this->helper->getErrorMessage();
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
     * @param Userinfo $userinfo      holds the new user data
     * @param Userinfo &$existinguser holds the exsisting user data
     * @param array  &$status       Status array
     *
     * @access public
     * @return void
     */
    function updateEmail(Userinfo $userinfo, Userinfo &$existinguser, &$status)
    {
        if ($this->helper->updateEmail($userinfo)) {
	        $status['debug'][] = Text::_('EMAIL_UPDATE') . ': ' . $existinguser->email . ' -> ' . $userinfo->email;
        } else {
	        $status['error'][] = Text::_('EMAIL_UPDATE_ERROR') . ' ' . $this->helper->getErrorMessage();
        }
    }

    /**
     * updateUsergroup
     *
     * @param Userinfo $userinfo      holds the new user data
     * @param Userinfo &$existinguser holds the exsisting user data
     * @param array  &$status       Status array
     *
     * @access public
     * @return void
     */
    function updateUsergroup(Userinfo $userinfo, Userinfo &$existinguser, &$status)
    {
        //get the usergroup and determine if working in advanced or simple mode

        $groups = $this->getCorrectUserGroups($this->getJname(),$userinfo);
	    if (!isset($groups[0])) {
		    $status['error'][] = Text::_('GROUP_UPDATE_ERROR');
	    } else {
		    foreach($groups as $group) {
			    if (!in_array($group, $existinguser->groups)) {
				    $this->helper->addGroup($existinguser->username, $group);
			    }
		    }

			foreach($existinguser->groups as $group) {
				if (!in_array($group, $groups)) {
					$this->helper->removeGroup($existinguser->username, $group);
				}
			}
		    $status['debug'][] = Text::_('GROUP_UPDATE') . ': ' . implode (' , ', $existinguser->groups) . ' -> ' . implode (' , ', $groups);
	    }
    }

    /**
     * Creates a new user
     *
     * @param Userinfo $userinfo holds the new user data
     * @param array  &$status  Status array
     *
     * @access public
     * @return mixed null on fail or array with status
     */
    function createUser(Userinfo $userinfo, &$status)
    {
        //we need to create a new SMF user
        $source_path = $this->params->get('source_path');
        
        $groups = $this->getCorrectUserGroups($this->getJname(), $userinfo);
		
        if (!isset($groups[0])) {
            //TODO: change error message
            $status['error'][] = Text::_('GROUP_UPDATE_ERROR') . ": " . Text::_('ADVANCED_GROUPMODE_MASTER_NOT_HAVE_GROUPID');
        } else {
			if ($this->helper->createUser($userinfo)) {
				foreach($groups as $group) {
					$this->helper->addGroup($userinfo->username, $group);
				}
				//return the good news
				$status['debug'][] = Text::_('USER_CREATION');
				$status['userinfo'] = $this->getUser($userinfo);
			} else {
				//return the error
				$status['error'][] = Text::_('USER_CREATION_ERROR') . ' : ' . $this->helper->getErrorMessage();
			}
        }
        return $status;
    }
}
