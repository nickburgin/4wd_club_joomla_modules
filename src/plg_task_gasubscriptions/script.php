<?php
/**
 * @version     5.3
 * @package     pkg_gausers
 * @subpackage  plg_task_gasubscriptions
 * @copyright   Copyright (C) 2011. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Created by Glenn Arkell - http://www.glennarkell.com.au
 */

// No direct access
defined('_JEXEC') or die();

define('MODIFIED', 1);
define('NOT_MODIFIED', 2);

use \Joomla\CMS\Factory;
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\Installer\InstallerScript;

/**
 * Updates the database structure of the component
 *
 * @version  Release: 0.2b
 * @author   Component Creator <support@component-creator.com>
 * @since    0.1b
 */
class plg_task_gasubscriptionsInstallerScript extends InstallerScript
{
	/**
	 * Load the language file on instantiation.
	 * @var    boolean
	 */
    protected $autoloadLanguage = true;

	/**
	 * @param   string $type   type
	 * @param   string $parent parent
	 * @return boolean
	 */
	public function postflight($type, $parent)
	{
		// $parent is the class calling this method
		echo '<p>' . Text::_('PLG_TASK_GASUBSCRIPTIONS_POSTFLIGHT_TEXT') . '</p>';

        $params = '{
            "individual_log":false,"log_file":"","notifications":{"success_mail":"0","failure_mail":"1","fatal_failure_mail":"1","orphan_mail":"1"},
            "run_frequency":"1","freq_type":"DAYS"
            }';

		if (STRTOUPPER($type) == 'UPDATE')
        {
            $exists = $this->checkTask('renewal_task_id');
            
            if (!$exists) { $this->createNewTask($params); }
            
		}

		if (STRTOUPPER($type) == 'INSTALL')
        {
			// do something
            $this->createNewTask($params);
		}

		return true;
	}

	/**
	 * *********************  Extra installation stuff  *******************************
	 */

	/**
	 * Insert new task record
	 * @param   string $params   Parameters as set
	 * @return boolean
	 */
	function createNewTask($params)
	{
		// Create and populate an object.
		$rec = new \stdClass();
		$rec->id = 0;
		$rec->state = 0;
		$rec->last_exit_code = 0;
		$rec->last_execution = NULL;
		$rec->next_execution = Factory::getDate()->toSQL;
		$rec->title = 'Subscription Renewals Task';
		$rec->type = 'renewal_task_id';
		$rec->execution_rules = '{"rule-type":"interval-days","interval-days":"1","exec-day":"20","exec-time":"06:14"}';
		$rec->params = $params;
		$rec->created_by = $user->id;

	    try {
			$result = Factory::getContainer()->get('DatabaseDriver')->insertObject('#__scheduler_tasks', $rec);
	    } catch (RuntimeException $e) {
	        Factory::getApplication()->enqueueMessage($e->getMessage().'New Task Failed ', 'danger');
	        return false;
	    }

		return $result;
	}

    /**
    *   Method to get the required record
    *   @param int $id id reference
    *   @return object record data
    */
	public static function checkTask($task)
	{
        $db		= Factory::getContainer()->get('DatabaseDriver');
		$query	= $db->getQuery(true);
        $query->clear();
		$query->select(' count(*) ');
		$query->from(' #__scheduler_tasks ');
		$query->where(' type = ' . $db->quote('renewal_task_id') );
		$db->setQuery((string)$query);

	    try {
	        return $db->loadResult();
	    } catch (RuntimeException $e) {
	        Factory::getApplication()->enqueueMessage($e->getMessage().' Membership Type');
	        return false;
	    }

	}

}
