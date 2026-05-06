<?php

/**
 * @version     6.0.0
 * @package     com_gausers
 * @copyright   Copyright (C) 2012. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Created by Glenn Arkell - http://www.glennarkell.com
 */

namespace GlennArkell\Component\Gausers\Administrator\Table;

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
use \GlennArkell\Component\Gausers\Administrator\Helper\GausersHelper;

/**
 * invoice Table class
 */
class InvoiceTable extends Table implements VersionableTableInterface
{
    /**
     * Constructor
     *
     * @param JDatabase A database connector object
     */
	public function __construct(DatabaseDriver $db)
	{
		$this->typeAlias = 'com_gausers.invoice';
		parent::__construct('#__gausers_invoices', 'id', $db);
		$this->setColumnAlias('published', 'state');
	}

    /**
	 * Get the type alias for the history table
	 * @return  string  The alias as described above
	 * @since   4.0.2
	 */
	public function getTypeAlias()
	{
		return 'com_gausers.invoice';
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

		// Support for empty date field: tran_date
		if (!isset($array['paid_date']) || empty($array['paid_date'])) {
			$array['paid_date'] = null;
		}

		// Support for empty checked_out fields:
		if (!isset($array['checked_out']) || empty($array['checked_out']) || $array['checked_out'] == 0) {
			$array['checked_out'] = null;
		}
		if (!isset($array['checked_out_time']) || empty($array['checked_out_time']) || $array['checked_out_time'] == '0000-00-00 00:00:00') {
			$array['checked_out_time'] = null;
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

		if (!$user->authorise('core.admin', 'com_gausers.invoice.' . $array['id'])) {
			$actions         = Access::getActionsFromFile(
				JPATH_ADMINISTRATOR . '/components/com_gausers/access.xml',
				"/access/section[@name='invoice']/"
			);
			$default_actions = Access::getAssetRules('com_gausers.invoice.' . $array['id'])->getData();
			$array_jaccess   = array();

			foreach ($actions as $action) {
				$array_jaccess[$action->name] = $default_actions[$action->name];
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
     * @param type $jaccessrules an arrao of JAccessRule objects.
     */
    private function JAccessRulestoArray($jaccessrules){
        $rules = array();
        foreach($jaccessrules as $action => $jaccess){
            $actions = array();
            foreach($jaccess->getData() as $group => $allow){
                $actions[$group] = ((bool)$allow);
            }
            $rules[$action] = $actions;
        }
        return $rules;
    }

    /**
     * Overloaded check function
     */
    public function check() {

        //If there is an ordering column and this is a new row then get the next ordering value
        if (property_exists($this, 'ordering') && $this->id == 0) {
            $this->ordering = self::getNextOrder();
        }

        return parent::check();
    }

    /**
      * Define a namespaced asset name for inclusion in the #__assets table
      * @return string The asset name 
      *
      * @see Table::_getAssetName 
    */
    protected function _getAssetName() {
        $k = $this->_tbl_key;
        return 'com_gausers.invoice.' . (int) $this->$k;
    }
 
    /**
      * Returns the parent asset's id. If you have a tree structure, retrieve the parent's id using the external key field
      *
      * @see Table::_getAssetParentId 
    */
    protected function _getAssetParentId($table = null, $id = null){
        // We will retrieve the parent-asset from the Asset-table
        $assetParent = Table::getInstance('Asset');
        // Default: if no asset-parent can be found we take the global asset
        $assetParentId = $assetParent->getRootId();
        // The item has the component as asset-parent
        $assetParent->loadByName('com_gausers');
        // Return the found asset-parent-id
        if ($assetParent->id){
            $assetParentId=$assetParent->id;
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
