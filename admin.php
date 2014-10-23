<?php

/**
 * file containing administrator function for the jfusion plugin
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
 * JFusion Admin Class for JIRA
 * For detailed descriptions on these functions please check the model.abstractadmin.php
 *
 * @category   JFusion
 * @package    JFusionPlugins
 * @subpackage JIRA
 * @author     JFusion Team <webmaster@jfusion.org>
 * @copyright  2008 JFusion. All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.jfusion.org
 */

class JFusionAdmin_jira extends JFusionAdmin
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
     * check config
     *
     * @return array status message
     */
    function checkConfig()
    {
        $status = array();

	    $result = $this->helper->ping();
        if ($result) {
            $status['config'] = 1;
            $status['message'] = JText::_('GOOD_CONFIG');
        } else {
            $status['config'] = 0;
            $status['message'] = JText::_('Unable to connect to jira: ') . $this->helper->getErrorMessage();
        }
	    return $status;
    }

	/**
	 * Returns the a list of users of the integrated software
	 *
	 * @param int $limitstart start at
	 * @param int $limit number of results
	 *
	 * @return array
	 */
	function getUserList($limitstart = 0, $limit = 0)
	{
	    return $this->helper->getUserList($limitstart, $limit);
    }

    /**
     * returns user count
     *
     * @return int user count
     */
    function getUserCount()
    {
	    return $this->helper->getUserCount();
    }

	/**
	 * get usergroup list
	 *
	 * @return array
	 */
    function getUsergroupList()
    {
	    $usergrouplist = $this->helper->getGroups();
        return $usergrouplist;
    }

    /**
     * get default user group
     *
     * @return string|array with default user group
     */
    function getDefaultUsergroup()
    {
	    $usergroup = JFusionFunction::getUserGroups($this->getJname(), true);
	    return $usergroup;
    }

	/**
	 * function  return if user can register or not
	 *
	 * @return boolean true can register
	 */
	function allowRegistration()
	{
		$params = JFusionFactory::getParams($this->getJname());
		$registration = $params->get('registration', true);
		if ($registration) {
			$registration = true;
		} else {
			$registration = false;
		}
		return $registration;
	}

	/**
	 * do plugin support multi usergroups
	 */
	function isMultiGroup()
	{
		return true;
	}    
}
