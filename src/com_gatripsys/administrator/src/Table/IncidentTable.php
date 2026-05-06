<?php

/**
 * @version     5.3.0
 * @package     com_gatripsys
 * @copyright   Copyright (C) 2014. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Glenn Arkell <glenn@glennarkell.com.au> - http://www.glennarkell.com.au
 */

namespace GlennArkell\Component\Gatripsys\Administrator\Table;

// No direct access
defined('_JEXEC') or die;

use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Factory;
use Joomla\Registry\Registry;
use Joomla\CMS\Access\Access;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Table\Table as Table;
use Joomla\CMS\Versioning\VersionableTableInterface;
use Joomla\Database\DatabaseDriver;
use Joomla\CMS\Filter\OutputFilter;
use Joomla\Filesystem\File;
use \GlennArkell\Component\Gatripsys\Administrator\Helper\GatripsysHelper;

/**
 * member Table class
 */
class IncidentTable extends Table implements VersionableTableInterface
{
    /**
     * Constructor
     * @param JDatabase A database connector object
     */
	public function __construct(DatabaseDriver $db)
	{
		$this->typeAlias = 'com_gatripsys.incident';
		parent::__construct('#__gatripsys_incidents', 'id', $db);
        $this->setColumnAlias('published', 'state');
    }

    /**
	 * Get the type alias for the history table
	 * @return  string  The alias as described above
	 * @since   5.3.0
	 */
	public function getTypeAlias()
	{
		return 'com_gatripsys.incident';
	}

    /**
     * Overloaded bind function to pre-process the params.
     *
     * @param	array		Named array
     * @return	null|string	null is operation was satisfactory, otherwise returns an error
     * @see		Table:bind
     * @since	1.5
     */
    public function bind($array, $ignore = '') 
	{

		$date = Factory::getDate();
		$user = GatripsysHelper::getSpecificUser();
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
				$array['update_date'] = $date->toSql();
				$array['modified_by'] = $user->id;
			}
		}

		// Support for empty date field: tran_date
		if (!isset($array['incid_date']) || empty($array['incid_date'])) {
			$array['incid_date'] = null;
		}


        if (isset($array['params']) && is_array($array['params'])) {
            $registry = new Registry();
            $registry->loadArray($array['params']);
            $array['params'] = (string) $registry;
        }

        if (isset($array['metadata']) && is_array($array['metadata'])) {
            $registry = new Registry();
            $registry->loadArray($array['metadata']);
            $array['metadata'] = (string) $registry;
        }

		if (!GatripsysHelper::getSpecificUser()->authorise('core.admin', 'com_gatripsys.incident.' . $array['id'])) {
			$actions = Access::getActionsFromFile( JPATH_ADMINISTRATOR . '/components/com_gatripsys/access.xml', "/access/section[@name='incident']/" );
			$default_actions = Access::getAssetRules('com_gatripsys.incident.' . $array['id'])->getData();
			$array_Access   = array();

			foreach ($actions as $action) {
				$array_Access[$action->name] = $default_actions[$action->name];
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

		return 'com_gatripsys.incident.' . (int) $this->$k;
	}

    /**
     * This function convert an array of AccessRule objects into an rules array.
     * @param type $Accessrules an arrao of AccessRule objects.
     */
    private function JAccessRulestoArray($Accessrules) {
        $rules = array();
        foreach ($Accessrules as $action => $Access) {
            $actions = array();
            foreach ($Access->getData() as $group => $allow) {
                $actions[$group] = ((bool) $allow);
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
		$assetParent->loadByName('com_gatripsys');

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
