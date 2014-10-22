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


use JFusion\User\Groups;
use Joomla\Language\Text;
use RuntimeException;

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
class Admin extends \JFusion\Plugin\Admin
{
	/**
	 * @var $helper Helper
	 */
	var $helper;

	/**
	 * check config
	 *
	 * @throws RuntimeException
	 * @return array status message
	 */
    function checkConfig()
    {
	    $result = $this->helper->ping();
        if (!$result) {
	        throw new RuntimeException(Text::_('Unable to connect to jira: ') . $this->helper->getErrorMessage());
        }
	    return true;
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
	    $usergroup = Groups::get($this->getJname(), true);
	    return $usergroup;
    }

	/**
	 * function  return if user can register or not
	 *
	 * @return boolean true can register
	 */
	function allowRegistration()
	{
		$registration = $this->params->get('registration', true);
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
