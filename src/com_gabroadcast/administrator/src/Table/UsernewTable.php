<?php

/**
 * @version    4.3.3
 * @package    Com_Gabroadcast
 * @author     Glenn Arkell <glenn@glennarkell.com.au>
 * @copyright  2019 Glenn Arkell
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace GlennArkell\Component\Gabroadcast\Administrator\Table;

// No direct access
defined('_JEXEC') or die;

use \Joomla\Utilities\ArrayHelper;
use \Joomla\CMS\Factory;
use \Joomla\CMS\Table\Table;
use \Joomla\CMS\Versioning\VersionableTableInterface;
use \Joomla\Database\DatabaseDriver;
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\Access\Access;
use \Joomla\Registry\Registry;
use \GlennArkell\Component\Gabroadcast\Administrator\Helper\GabroadcastHelper;

/**
 * Table class
 *
 * @since  1.6
 */
class UsernewTable extends Table implements VersionableTableInterface
{
	
	/**
	 * Constructor
	 *
	 * @param   JDatabase  &$db  A database connector object
	 */
	public function __construct(DatabaseDriver $db)
	{
		$this->typeAlias = 'com_gabroadcast.usernew';
		parent::__construct('#__gabroadcast_usernews', 'id', $db);
        $this->setColumnAlias('published', 'state');
    }

    /**
	 * Get the type alias for the history table
	 * @return  string  The alias as described above
	 * @since   4.3.3
	 */
	public function getTypeAlias()
	{
		return 'com_gabroadcast.usernew';
	}

	/**
	 * Overloaded bind function to pre-process the params.
	 * @param   array  $array   Named array
	 * @param   mixed  $ignore  Optional array or list of parameters to ignore
	 * @return  null|string  null is operation was satisfactory, otherwise returns an error
	 * @see     Table:bind
	 * @since   1.5
	 */
	public function bind($array, $ignore = '')
	{
	    $user = Factory::getApplication()->getIdentity();
	    $date = Factory::getDate();
		$task = Factory::getApplication()->input->get('task');

		if ($array['id'] == 0) {
			$array['created_date'] = $date->toSql();
			$array['modified_date'] = $date->toSql();
			if (empty($array['created_by'])) {
				$array['created_by'] = $user->id;
			}
			if (empty($array['modified_by'])) {
				$array['modified_by'] = $user->id;
			}
		} else {
			if ($task == 'apply' || $task == 'save') {
				$array['modified_date'] = $date->toSql();
				$array['modified_by'] = $user->id;
			}
		}

		if (isset($array['params']) && is_array($array['params'])) {
			$registry = new Registry;
			$registry->loadArray($array['params']);
			$array['params'] = (string) $registry;
		}

		if (isset($array['metadata']) && is_array($array['metadata'])) {
			$registry = new Registry;
			$registry->loadArray($array['metadata']);
			$array['metadata'] = (string) $registry;
		}

		if (!$user->authorise('core.admin', 'com_gabroadcast.usernew.' . $array['id'])) {
			$actions         = Access::getActionsFromFile(
				JPATH_ADMINISTRATOR . '/components/com_gabroadcast/access.xml',
				"/access/section[@name='usernew']/"
			);
			$default_actions = Access::getAssetRules('com_gabroadcast.usernew.' . $array['id'])->getData();
			$array_jaccess   = array();

			foreach ($actions as $action) {
                if (key_exists($action->name, $default_actions)) {
                    $array_jaccess[$action->name] = $default_actions[$action->name];
                }
			}

			$array['rules'] = $this->JAccessRulestoArray($array_jaccess);
		}

		// Bind the rules for ACL where supported.
		if (isset($array['rules']) && is_array($array['rules'])) {
			$this->setRules($array['rules']);
		}

		return parent::bind($array, $ignore);
	}

	/**
	 * This function convert an array of JAccessRule objects into an rules array.
	 * @param   array  $jaccessrules  An array of JAccessRule objects.
	 * @return  array
	 */
	private function JAccessRulestoArray($jaccessrules)
	{
		$rules = array();

		foreach ($jaccessrules as $action => $jaccess) {
			$actions = array();

			if ($jaccess) {
				foreach ($jaccess->getData() as $group => $allow) {
					$actions[$group] = ((bool)$allow);
				}
			}

			$rules[$action] = $actions;
		}

		return $rules;
	}

	/**
	 * Overloaded check function
	 * @return bool
	 */
	public function check()
	{
		// If there is an ordering column and this is a new row then get the next ordering value
		if (property_exists($this, 'ordering') && $this->id == 0) {
			$this->ordering = self::getNextOrder();
		}

		return parent::check();
	}

	/**
	 * Define a namespaced asset name for inclusion in the #__assets table
	 * @return string The asset name
	 * @see Table::_getAssetName
	 */
	protected function _getAssetName()
	{
		$k = $this->_tbl_key;

		return 'com_gabroadcast.usernew.' . (int) $this->$k;
	}

	/**
	 * Delete a record by id
	 * @param   mixed  $pk  Primary key value to delete. Optional
	 * @return bool
	 */
	public function delete($pk = null)
	{
		$this->load($pk);
		$result = parent::delete($pk);
		
		return $result;
	}
}
