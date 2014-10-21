<?php

/**
 * file containing public function for the jfusion plugin
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
 * JFusion Public Class for JIRA
 * For detailed descriptions on these functions please check the model.abstractpublic.php
 *
 * @category   JFusion
 * @package    JFusionPlugins
 * @subpackage JIRA
 * @author     JFusion Team <webmaster@jfusion.org>
 * @copyright  2008 JFusion. All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.jfusion.org
 */
class JFusionPublic_jira extends JFusionPublic
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
     * Get registration url
     *
     * @return strung url
     */
    function getRegistrationURL()
    {
        return 'index.php?action=register';
    }

    /**
     * Get lost password url
     *
     * @return strung url
     */
    function getLostPasswordURL()
    {
        return 'index.php?action=reminder';
    }

    /**
     * Get url for lost user name
     *
     * @return strung url
     */
    function getLostUsernameURL()
    {
        return 'index.php?action=reminder';
    }
}