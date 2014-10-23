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
 * Load the JFusion framework
 */
require_once JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_jfusion' . DS . 'models' . DS . 'model.jfusion.php';
require_once JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_jfusion' . DS . 'models' . DS . 'model.abstractadmin.php';

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
	    $params = JFusionFactory::getParams($this->getJname());
	    /**
	     * @ignore
	     * @var $helper JFusionHelper_jira
	     */
	    $helper = JFusionFactory::getHelper($this->getJname());

	    $result = $helper->ping();
        if ($result) {
            $status['config'] = 1;
            $status['message'] = JText::_('GOOD_CONFIG');
            return $status;
        } else {
            $status['config'] = 0;
            $status['message'] = JText::_('Unable to connect to jira: '). $helper->getErrorMessage();
            return $status;
        }

    }

    /**
     * setup plugin from path
     *
     * @param string $forumPath Source path user to find config files
     *
     * @return mixed return false on failor and array if sucess
     */
    function setupFromPath($forumPath)
    {
        //check for trailing slash and generate file path
        if (substr($forumPath, -1) == DS) {
            $myfile = $forumPath . 'Settings.php';
        } else {
            $myfile = $forumPath . DS . 'Settings.php';
        }
        //try to open the file
        if (($file_handle = @fopen($myfile, 'r')) === false) {
            JError::raiseWarning(500, JText::_('WIZARD_FAILURE') . ": $myfile " . JText::_('WIZARD_MANUAL'));
            //get the default parameters object
            $params = JFusionFactory::getParams($this->getJname());
            return $params;
        } else {
            //parse the file line by line to get only the config variables
            $file_handle = fopen($myfile, 'r');
            while (!feof($file_handle)) {
                $line = fgets($file_handle);
                if (strpos($line, '$') === 0) {
                    $vars = explode("'", $line);
                    if (isset($vars[1]) && isset($vars[0])) {
                        $name = trim($vars[0], ' $=');
                        $value = trim($vars[1], ' $=');
                        $config[$name] = $value;
                    }
                }
            }
            fclose($file_handle);
            //Save the parameters into the standard JFusion params format
            $params = array();
            $params['database_host'] = $config['db_server'];
            $params['database_type'] = 'mysql';
            $params['database_name'] = $config['db_name'];
            $params['database_user'] = $config['db_user'];
            $params['database_password'] = $config['db_passwd'];
            $params['database_prefix'] = $config['db_prefix'];
            $params['source_url'] = $config['boardurl'];
            $params['cookie_name'] = $config['cookiename'];
            $params['source_path'] = $forumPath;
            return $params;
        }
    }

    /**
     * Get a list of users
     *
     * @return object with list of users
     */
    function getUserList($limitstart = 0, $limit = 0)
    {
	    /**
	     * @ignore
	     * @var $helper JFusionHelper_jira
	     */
	    $helper = JFusionFactory::getHelper($this->getJname());

	    return $helper->getUserList($limitstart, $limit);
    }

    /**
     * returns user count
     *
     * @return int user count
     */
    function getUserCount()
    {
	    /**
	     * @ignore
	     * @var $helper JFusionHelper_jira
	     */
	    $helper = JFusionFactory::getHelper($this->getJname());

	    return $helper->getUserCount();
    }

    /**
     * get default user group list
     *
     * @return object with default user group list
     */
    function getUsergroupList()
    {
	    /**
	     * @ignore
	     * @var $helper JFusionHelper_jira
	     */
	    $helper = JFusionFactory::getHelper($this->getJname());

	    $result = $helper->getGroups();

	    $usergrouplist = array();

	    if ($result->groups) {
			foreach($result->groups as $group) {
				$g = new stdClass;
				$g->id = $group->name;
				$g->name = $group->html;
				$usergrouplist[] = $g;
			}
	    }

        //append the default usergroup

        return $usergrouplist;
    }

    /**
     * get default user group
     *
     * @return object with default user group
     */
    function getDefaultUsergroup()
    {
        $params = JFusionFactory::getParams($this->getJname());
        $usergroup_id = $params->get('usergroup');
        if ($usergroup_id == 0) {
            return "User";
        }
        //we want to output the usergroup name
        $db = JFusionFactory::getDatabase($this->getJname());
        $query = 'SELECT groupName FROM #__membergroups WHERE ID_GROUP = ' . (int)$usergroup_id;
        $db->setQuery($query);
        return $db->loadResult();
    }

    /**
     * function  return if user can register or not
     *
     * @return boolean true can register
     */
    function allowRegistration()
    {
    	return true;
    }

	/*
	 * do plugin support multi usergroups
	 */
	function isMultiGroup()
	{
		return true;
	}    
}
