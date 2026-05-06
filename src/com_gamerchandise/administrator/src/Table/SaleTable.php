<?php

/**
 * @version    4.0.7
 * @package    Com_Gamerchandise
 * @author     Glenn Arkell <glenn@glennarkell.com.au>
 * @copyright  2016 Glenn Arkell
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace GlennArkell\Component\Gamerchandise\Administrator\Table;

// No direct access
\defined('_JEXEC') or die;

use \Joomla\Utilities\ArrayHelper;
use \Joomla\CMS\Factory;
use \Joomla\Registry\Registry;
use \Joomla\CMS\Access\Access;
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\Table\Table as Table;
use \Joomla\CMS\Versioning\VersionableTableInterface;
use \Joomla\Database\DatabaseDriver;
use \Joomla\CMS\Filter\OutputFilter;
use \Joomla\CMS\Filesystem\File;
use \GlennArkell\Component\Gamerchandise\Administrator\Helper\GamerchandiseHelper;

/**
 * Table
 * @since  1.5
 */
class SaleTable extends Table implements VersionableTableInterface
{
	
	/**
	 * Constructor
	 * @param   JDatabase  &$db  A database connector object
	 */
	public function __construct(DatabaseDriver $db)
	{
		$this->typeAlias = 'com_gamerchandise.sale';
		parent::__construct('#__gamerchandise_sales', 'id', $db);
        $this->setColumnAlias('published', 'state');
    }

    /**
	 * Get the type alias for the history table
	 * @return  string  The alias as described above
	 * @since   4.0.7
	 */
	public function getTypeAlias()
	{
		return 'com_gamerchandise.sale';
	}

	/**
	 * Overloaded bind function to pre-process the params.
	 * @param   array  $array   Named array
	 * @param   mixed  $ignore  Optional array or list of parameters to ignore
	 * @return  null|string  null is operation was satisfactory, otherwise returns an error
	 * @see     JTable:bind
	 * @since   1.5
     * @throws Exception
	 */
	public function bind($array, $ignore = '')
	{
		$user = GamerchandiseHelper::getSpecificUser();
	    $date = Factory::getDate();
		$input = Factory::getApplication()->input;
		$task = $input->getString('task', '');

		if ($array['id'] == 0) {
			$array['created_by'] = $user->id;
			$array['created_date'] = $date->toSql();
		}

		if ($task == 'apply' || $task == 'save') {
			$array['modified_by'] = $user->id;
			$array['modified_date'] = $date->toSql();
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

		if (!$user->authorise('core.admin', 'com_gamerchandise.sale.' . $array['id'])) {
			$actions         = Access::getActionsFromFile(
				JPATH_ADMINISTRATOR . '/components/com_gamerchandise/access.xml',
				"/access/section[@name='sale']/"
			);
			$default_actions = Access::getAssetRules('com_gamerchandise.sale.' . $array['id'])->getData();
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
	 * Define a namespaced asset name for inclusion in the #__assets table
	 * @return string The asset name
	 * @see Table::_getAssetName
	 */
	protected function _getAssetName()
	{
		$k = $this->_tbl_key;

		return 'com_gamerchandise.sale.' . (int) $this->$k;
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
	 * Returns the parent asset's id. If you have a tree structure, retrieve the parent's id using the external key field
	 * @param   JTable   $table  Table name
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
		$assetParent->loadByName('com_gamerchandise');

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
