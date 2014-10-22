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
class Front extends \JFusion\Plugin\Front
{
	/**
	 * @var $helper Helper
	 */
	var $helper;

    /**
     * Get registration url
     *
     * @return string url
     */
    function getRegistrationURL()
    {
        return 'secure/Signup!default.jspa';
    }

    /**
     * Get lost password url
     *
     * @return string url
     */
    function getLostPasswordURL()
    {
        return 'secure/ForgotLoginDetails.jspa';
    }

    /**
     * Get url for lost user name
     *
     * @return string url
     */
    function getLostUsernameURL()
    {
        return 'secure/ForgotLoginDetails.jspa';
    }
}