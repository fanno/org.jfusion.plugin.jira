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
class Auth extends \JFusion\Plugin\Auth
{
	/**
	 * @var $helper Helper
	 */
	var $helper;

    /**
     * Generate a encrypted password from clean password
     *
     * @param object $userinfo holds the user data
     *
     * @return string
     */
    function generateEncryptedPassword($userinfo)
    {
	    return $userinfo->password;
    }

	/**
	 * used by framework to ensure a password test
	 *
	 * @param object $userinfo userdata object containing the userdata
	 *
	 * @return boolean
	 */
	function checkPassword($userinfo) {
		return $this->helper->checkPassword($userinfo->username, $userinfo->password_clear);
	}
}
