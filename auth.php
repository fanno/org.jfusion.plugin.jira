<?php

/**
 * file containing auth function for the jfusion plugin
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
 * JFusion auth plugin class
 *
 * @category   JFusion
 * @package    JFusionPlugins
 * @subpackage JIRA
 * @author     JFusion Team <webmaster@jfusion.org>
 * @copyright  2008 JFusion. All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.jfusion.org
 */

class JFusionAuth_jira extends JFusionAuth
{
	/**
	 * @var $helper JFusionHelper_jira
	 */
	var $helper;

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
