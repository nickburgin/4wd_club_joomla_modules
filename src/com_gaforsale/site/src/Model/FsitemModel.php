<?php

/**
 * @version    4.2.2
 * @package    Com_Gaforsale
 * @author     Glenn Arkell <glenn@glennarkell.com.au>
 * @copyright  Copyright (C) 2013. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace GlennArkell\Component\Gaforsale\Site\Model;

// No direct access.
\defined('_JEXEC') or die;

use \Joomla\CMS\Factory;
use \Joomla\Utilities\ArrayHelper;
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\Table\Table;
use \Joomla\Filesystem\File;
use \Joomla\Filesystem\Folder;
use \Joomla\Filesystem\Path;
use \Joomla\CMS\MVC\Model\ItemModel;
use \Joomla\CMS\Helper\TagsHelper;
use \Joomla\CMS\Component\ComponentHelper;
use \GlennArkell\Component\Gaforsale\Administrator\Helper\GaforsaleHelper;

/**
 * Item model.
 * @since  1.6
 */
class FsitemModel extends ItemModel
{
	/**
	 * Method to auto-populate the model state.
	 * Note. Calling getState in this method will result in recursion.
	 * @return void
	 * @since    1.6
	 */
	protected function populateState()
	{
		$app = Factory::getApplication('com_gaforsale');

		// Load state from the request userState on edit or from the passed variable on default
		if ($app->input->get('layout') == 'edit') {
			$id = $app->getUserState('com_gaforsale.edit.fsitem.id');
		} else {
			$id = $app->input->get('id');
			$app->setUserState('com_gaforsale.edit.fsitem.id', $id);
		}

		$this->setState('fsitem.id', $id);

		// Load the parameters.
		$params       = $app->getParams();
		$params_array = $params->toArray();

		if (isset($params_array['item_id'])) {
			$this->setState('fsitem.id', $params_array['item_id']);
		}

		$this->setState('params', $params);
	}

	/**
	 * Method to get an object.
	 * @param   integer  $id  The id of the object to get.
	 * @return  mixed    Object on success, false on failure.
	 */
	public function getItem($id = null)
	{
		if ($this->_item === null)
		{
			$this->_item = false;

			if (empty($id)) {
				$id = $this->getState('fsitem.id');
			}

			// Get a level row instance.
			$table = $this->getTable();

			// Attempt to load the row.
			if ($table->load($id)) {
				// Check published state.
				if ($published = $this->getState('filter.published')) {
					if ($table->state != $published) {
						return $this->_item;
					}
				}

				// Convert the Table to a clean JObject.
				$properties  = $table->getProperties(1);
				$this->_item = ArrayHelper::toObject($properties, 'JObject');
			}
		}

		if (isset($this->_item->created_by) ) {
			$this->_item->created_by_name = Factory::getUser($this->_item->created_by)->name;
		}
		if (isset($this->_item->modified_by) ) {
			$this->_item->modified_by_name = Factory::getUser($this->_item->modified_by)->name;
		}
		if (isset($this->_item->user_id) ) {
			$this->_item->user_id_name = Factory::getUser($this->_item->user_id)->name;
		}

		return $this->_item;
	}

	/**
	 * Get an instance of Table class
	 * @param   string  $type    Name of the Table class to get an instance of.
	 * @param   string  $prefix  Prefix for the table class name. Optional.
	 * @param   array   $config  Array of configuration values for the Table object. Optional.
	 * @return  Table|bool Table if success, false on failure.
	 */
	public function getTable($type = 'Fsitem', $prefix = 'Administrator', $config = array())
	{
		return parent::getTable($type, $prefix, $config);
	}

	/**
	 * Get the id of an item by alias
	 * @param   string  $alias  Item alias
	 * @return  mixed
	 */
	public function getItemIdByAlias($alias)
	{
		$table      = $this->getTable();
		$properties = $table->getProperties();
		$result     = null;
		$aliasKey   = null;
		if (method_exists($this, 'getAliasFieldNameByView')) {
			$aliasKey   = $this->getAliasFieldNameByView('fsitem');
		}

		if (key_exists('alias', $properties)) {
			$table->load(array('alias' => $alias));
			$result = $table->id;
		} elseif (isset($aliasKey) && key_exists($aliasKey, $properties)) {
			$table->load(array($aliasKey => $alias));
			$result = $table->id;
		}

		return $result;
	}

	/**
	 * Method to check in an item.
	 * @param   integer  $id  The id of the row to check out.
	 * @return  boolean True on success, false on failure.
	 * @since    1.6
	 */
	public function checkin($id = null)
	{
		// Get the id.
		$id = (!empty($id)) ? $id : (int) $this->getState('fsitem.id');

		if ($id) {
			// Initialise the table
			$table = $this->getTable();

			// Attempt to check the row in.
			if (method_exists($table, 'checkin')) {
				if (!$table->checkin($id)) {
					return false;
				}
			}
		}

		return true;
	}

	/**
	 * Method to check out an item for editing.
	 * @param   integer  $id  The id of the row to check out.
	 * @return  boolean True on success, false on failure.
	 * @since    1.6
	 */
	public function checkout($id = null)
	{
		// Get the user id.
		$id = (!empty($id)) ? $id : (int) $this->getState('fsitem.id');

		if ($id) {
			// Initialise the table
			$table = $this->getTable();

			// Get the current user object.
			$user = GaforsaleHelper::getSpecificUser();

			// Attempt to check the row out.
			if (method_exists($table, 'checkout')) {
				if (!$table->checkout($user->get('id'), $id)) {
					return false;
				}
			}
		}

		return true;
	}

	/**
	 * Publish the element
	 * @param   int  $id     Item id
	 * @param   int  $state  Publish state
	 * @return  boolean
	 */
	public function publish($id, $state)
	{
		$notify  = ComponentHelper::getParams('com_gaforsale')->get('notif_mbrs');
        $table = $this->getTable();
		$table->load($id);
		$table->state = $state;

		if ($table->store() === true) {
			if ($state == 1 && $notify) {
                GaforsaleHelper::notifyForsale($id, 'fstombrs');
            }
            return $id;
		} else {
			return false;
		}
	}

	/**
	 * Method to delete an item
	 * @param   int  $id  Element id
	 * @return  bool
	 */
	public function delete($id)
	{
		$table = $this->getTable();
		$table->load($id);
		$table->state = -2;

		return $table->store();
	}

    public function marksold($id)
	{
		$table = $this->getTable();
		$table->load($id);

		$table->state = 4;

		return $table->store();
    }

	/**
	 * Method to delete an item
	 * @param   int $id Element id
	 * @return  bool
	 */
	public function deletefile($id)
	{
		$table = $this->getTable();
		$table->load($id);

		// delete the physical file
		$tran_file = Path::clean( JPATH_SITE . '/' . $table->item_image );
		
		if (file_exists($item_image)) {
			$delete_OK = File::delete($item_image);
		}

		if ($delete_OK) {
			$table->item_image = '';
			$result = $table->store();
			Factory::getApplication()->enqueueMessage(Text::_('COM_GAFORSALE_FILE_DELETED_SUCCESSFULLY'), 'message');
		} else {
			$table->item_image = '';
			$result = $table->store();
			Factory::getApplication()->enqueueMessage(Text::_('COM_GAFORSALE_FILE_DIDNT_EXIST'), 'message');
		}

        return $result;
                
	}

    public function sendReminder($id)
	{
		GaforsaleHelper::notifyForsale($id, 'fsremind');

		return true;
    }

}
