<?php
/**
 * @version    3.0.0
 * @package    Com_Gacalevents
 * @author     Glenn Arkell <glenn@glennarkell.com.au>
 * @copyright  2021 Glenn Arkell
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace GlennArkell\Component\Gacalevents\Administrator\Table;

// No direct access
defined('_JEXEC') or die;

use \Joomla\Utilities\ArrayHelper;
use \Joomla\CMS\Factory;
use \Joomla\Registry\Registry;
use \Joomla\CMS\Access\Access;
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\Table\Table as Table;
use \Joomla\CMS\Versioning\VersionableTableInterface;
use \Joomla\Database\DatabaseDriver;
use \Joomla\CMS\Filter\OutputFilter;
use \Joomla\Filesystem\File;
use \GlennArkell\Component\Gacalevents\Administrator\Helper\GacaleventsHelper;

/**
 * Table
 * @since  1.5
 */
class EventTable extends Table implements VersionableTableInterface
{
	/**
	 * Constructor
	 * @param   JDatabase  &$db  A database connector object
	 */
	public function __construct(DatabaseDriver $db)
	{
		$this->typeAlias = 'com_gacalevents.event';
		parent::__construct('#__gacalevents_events', 'id', $db);
        $this->setColumnAlias('published', 'state');
    }

    /**
	 * Get the type alias for the history table
	 * @return  string  The alias as described above
	 * @since   4.0.0
	 */
	public function getTypeAlias()
	{
		return 'com_gacalevents.event';
	}

	/**
	 * Overloaded bind function to pre-process the params.
	 * @param   array  $array   Named array
	 * @param   mixed  $ignore  Optional array or list of parameters to ignore
	 * @return  null|string  null is operation was satisfactory, otherwise returns an error
	 * @see     Table:bind
	 * @since   1.5
     * @throws Exception
	 */
	public function bind($array, $ignore = '')
	{
	    $user = GacaleventsHelper::getSpecificUser();
	    $date = Factory::getDate();
		$task = Factory::getApplication()->input->get('task');
	    
		if ($array['id'] == 0 && empty($array['created_by'])) {
			$array['created_by'] = $user->id;
		}

		if ($array['id'] == 0 && empty($array['modified_by'])) {
			$array['modified_by'] = $user->id;
		}

		if ($task == 'apply' || $task == 'save') {
			$array['modified_by'] = $user->id;
		}

		if ($array['id'] == 0) {
			$array['created_date'] = $date->toSql();
		}

		if ($task == 'apply' || $task == 'save') {
			$array['modified_date'] = $date->toSql();
		}

		if (empty($array['depart_date'])) {
			$array['depart_date'] = $date->toSql();
		}
		if (empty($array['return_date'])) {
			$array['return_date'] = $array['depart_date'];
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

		if (!$user->authorise('core.admin', 'com_gacalevents.event.' . $array['id'])) {
			$actions         = Access::getActionsFromFile(
				JPATH_ADMINISTRATOR . '/components/com_gacalevents/access.xml',
				"/access/section[@name='event']/"
			);
			$default_actions = Access::getAssetRules('com_gacalevents.event.' . $array['id'])->getData();
			$array_Access   = array();

			foreach ($actions as $action) {
                if (key_exists($action->name, $default_actions)) {
                    $array_Access[$action->name] = $default_actions[$action->name];
                }
			}

			$array['rules'] = $this->JAccessRulestoArray($array_Access);
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
	private function JAccessRulestoArray($Accessrules)
	{
		$rules = array();

		foreach ($Accessrules as $action => $Access) {
			$actions = array();

			if ($Access) {
				foreach ($Access->getData() as $group => $allow) {
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
		if (property_exists($this, 'ordering') && $this->id == 0)
		{
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

		return 'com_gacalevents.event.' . (int) $this->$k;
	}

	/**
	 * Returns the parent asset's id. If you have a tree structure, retrieve the parent's id using the external key field
	 * @param   Table   $table  Table name
	 * @param   integer  $id     Id
	 * @see Table::_getAssetParentId
	 * @return mixed The id on success, false on failure.
	 */
	protected function _getAssetParentId($table = null, $id = null)
	{
		// We will retrieve the parent-asset from the Asset-table
		$assetParent = Table::getInstance('Asset');

		// Default: if no asset-parent can be found we take the global asset
		$assetParentId = $assetParent->getRootId();

		// The item has the component as asset-parent
		$assetParent->loadByName('com_gacalevents');

		// Return the found asset-parent-id
		if ($assetParent->id) {
			$assetParentId = $assetParent->id;
		}

		return $assetParentId;
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
