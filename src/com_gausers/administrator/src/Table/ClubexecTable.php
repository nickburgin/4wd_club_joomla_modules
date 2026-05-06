<?php

/**
 * @version     6.0.0
 * @package     com_gausers
 * @copyright   Copyright (C) 2014. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Glenn Arkell <glenn@glennarkell.com.au> - http://www.glennarkell.com.au
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
use Joomla\CMS\Tag\TaggableTableInterface;
use Joomla\CMS\Tag\TaggableTableTrait;
use Joomla\Database\DatabaseDriver;
use Joomla\CMS\Filter\OutputFilter;
use Joomla\Filesystem\File;
use Joomla\CMS\User\UserHelper;
use Joomla\CMS\Component\ComponentHelper;
use \GlennArkell\Component\Gausers\Administrator\Helper\GausersHelper;

/**
 * member Table class
 */
class ClubexecTable extends Table implements VersionableTableInterface, TaggableTableInterface
{
	use TaggableTableTrait;

	/**
     * Indicates that columns fully support the NULL value in the database
     * @var    boolean
     * @since  4.0.0
     */
    protected $_supportNullValue = true;

    /**
     * Constructor
     *
     * @param JDatabase A database connector object
     */
	public function __construct(DatabaseDriver $db)
	{
		$this->typeAlias = 'com_gausers.clubexec';
		parent::__construct('#__gausers_club_execs', 'id', $db);
		$this->setColumnAlias('published', 'state');
	}

    /**
	 * Get the type alias for the history table
	 * @return  string  The alias as described above
	 * @since   4.0.2
	 */
	public function getTypeAlias()
	{
		return $this->typeAlias;
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
        $params = ComponentHelper::getParams('com_gausers');
        $profile_suffix  = $params->get('profile_suffix');
        $lprof = 'profile'.$profile_suffix;

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

		// Support for empty checked_out fields:
		if (!isset($array['checked_out']) || empty($array['checked_out']) || $array['checked_out'] == 0) {
			$array['checked_out'] = null;
		}
		if (!isset($array['checked_out_time']) || empty($array['checked_out_time']) || $array['checked_out_time'] == '0000-00-00 00:00:00') {
			$array['checked_out_time'] = null;
		}

		// load names if left blank
		if (!empty($array['pres_id']) && $array['pres_name'] == '') {
			$p = GausersHelper::getSpecificUser($array['pres_id']);
			if ($array['pres_idp']) {
                $pp = UserHelper::getProfile($p->id);
				$pos_name = $pp->$lprof['partner'];
			} else { 
				$pos_name = $p->name;
			}
			$array['pres_name'] = $pos_name;
		}
		if (!empty($array['vpres_id']) && $array['vpres_name'] == '') {
			$vp = GausersHelper::getSpecificUser($array['vpres_id']);
			if ($array['vpres_idp']) {
                $vpp = UserHelper::getProfile($vp->id);
				$pos_name = $vpp->$lprof['partner'];
			} else { 
				$pos_name = $vp->name;
			}
			$array['vpres_name'] = $pos_name;
		}
		if (!empty($array['secr_id']) && $array['secr_name'] == '') {
			$s = GausersHelper::getSpecificUser($array['secr_id']);
			if ($array['secr_idp']) {
                $sp = UserHelper::getProfile($s->id);
				$pos_name = $sp->$lprof['partner'];
			} else { 
				$pos_name = $s->name;
			}
			$array['secr_name'] = $pos_name;
		}
		if (!empty($array['tres_id']) && $array['tres_name'] == '') {
			$t = GausersHelper::getSpecificUser($array['tres_id']);
			if ($array['tres_idp']) {
                $tp = UserHelper::getProfile($t->id);
				$pos_name = $tp->$lprof['partner'];
			} else { 
				$pos_name = $t->name;
			}
			$array['tres_name'] = $pos_name;
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

		if (!$user->authorise('core.admin', 'com_gausers.clubexec.' . $array['id'])) {
			$actions         = Access::getActionsFromFile(
				JPATH_ADMINISTRATOR . '/components/com_gausers/access.xml',
				"/access/section[@name='clubexec']/"
			);
			$default_actions = Access::getAssetRules('com_gausers.clubexec.' . $array['id'])->getData();
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
     * @param type $jaccessrules an array of JAccessRule objects.
	 * @return  array
     */
    private function JAccessRulestoArray($jaccessrules) 
	{
        $rules = array();
        foreach ($jaccessrules as $action => $jaccess) {
            $actions = array();
            foreach ($jaccess->getData() as $group => $allow) {
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

        //If there is an ordering column and this is a new row then get the next ordering value
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
        return $this->typeAlias . '.' . (int) $this->$k;
    }

	/**
	 * Returns the parent asset's id. If you have a tree structure, retrieve the parent's id using the external key field
	 * @param   Table   $table  Table name
	 * @param   integer  $id     Id
	 * @see Table::_getAssetParentId
	 * @return mixed The id on success, false on failure.
	 */
    protected function _getAssetParentId($table = null, $id = null) {
        // We will retrieve the parent-asset from the Asset-table
        $assetParent = Table::getInstance('Asset');
        // Default: if no asset-parent can be found we take the global asset
        $assetParentId = $assetParent->getRootId();
        // The item has the component as asset-parent
        $assetParent->loadByName('com_gausers');
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

	/**
	 * Method to store a row in the database from the Table instance properties.
	 * If a primary key value is set the row with that primary key value will be updated with the instance property values.
	 * If no primary key value is set a new row will be inserted into the database with the properties from the Table instance.
	 * @param   boolean  $updateNulls  True to update fields even if they are null.
	 * @return  boolean  True on success.
	 */
	public function store($updateNulls = true)
	{
		return parent::store($updateNulls);
	}

}
