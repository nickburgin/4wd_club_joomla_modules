<?php

/**
 * @version    5.1.0
 * @package    Com_Gatripsys
 * @author     Glenn Arkell <glenn@glennarkell.com.au>
 * @copyright  2016 Glenn Arkell
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace GlennArkell\Component\Gatripsys\Site\Model;

// No direct access.
defined('_JEXEC') or die;

use \Joomla\CMS\Factory;
use \Joomla\Utilities\ArrayHelper;
use \Joomla\CMS\MVC\Model\ItemModel;
use \Joomla\CMS\Table\Table;
use \Joomla\CMS\Language\Text;
use \GlennArkell\Component\Gatripsys\Administrator\Helper\GatripsysHelper;

/**
 * Item model.
 * @since  1.6
 */
class IncidentModel extends ItemModel
{
    public $_item;

	/**
	 * Method to auto-populate the model state.
	 * Note. Calling getState in this method will result in recursion.
	 * @return void
	 * @since    1.6
	 */
	protected function populateState()
	{
		$app = Factory::getApplication('com_gatripsys');

		// Load state from the request userState on edit or from the passed variable on default
		if (Factory::getApplication()->input->get('layout') == 'edit') {
			$id = Factory::getApplication()->getUserState('com_gatripsys.edit.incident.id');
			$trip_id = Factory::getApplication()->getUserState('com_gatripsys.edit.trip.id');
		} else {
			$id = Factory::getApplication()->input->get('id');
			Factory::getApplication()->setUserState('com_gatripsys.edit.incident.id', $id);
			$trip_id = Factory::getApplication()->input->get('trip_id');
			Factory::getApplication()->setUserState('com_gatripsys.edit.trip.id', $trip_id);
		}

		$this->setState('incident.id', $id);
		$this->setState('trip.id', $trip_id);

		// Load the parameters.
		$params       = $app->getParams();
		$params_array = $params->toArray();

		if (isset($params_array['item_id'])) {
			$this->setState('incident.id', $params_array['item_id']);
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
		if ($this->_item === null) {
			$this->_item = false;

			if (empty($id)) {
				$id = $this->getState('incident.id');
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

				// Convert the Table to a clean Object.
				$properties  = $table->getProperties(1);
				$this->_item = ArrayHelper::toObject($properties, 'stdClass');
			}
		}

		if (isset($this->_item->created_by) ) {
			$this->_item->created_by_name = GatripsysHelper::getSpecificUser($this->_item->created_by)->name;
		}
		if (isset($this->_item->modified_by) ) {
			$this->_item->modified_by_name = GatripsysHelper::getSpecificUser($this->_item->modified_by)->name;
		}
		$this->_item->user_id_name = GatripsysHelper::getSpecificUser($this->_item->user_id)->name;

		if (!isset($this->_item->trip_id)) {
            $this->_item->trip_id = $this->getState('trip.id');
		}
		$this->_item->trip = GatripsysHelper::getTripInformation($this->_item->trip_id);

		return $this->_item;
	}

	/**
	 * Get an instance of Table class
	 * @param   string  $type    Name of the Table class to get an instance of.
	 * @param   string  $prefix  Prefix for the table class name. Optional.
	 * @param   array   $config  Array of configuration values for the Table object. Optional.
	 * @return  Table|bool Table if success, false on failure.
	 */
	public function getTable($type = 'Incident', $prefix = 'Administrator', $config = array())
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
		$table = $this->getTable();

		$table->load(array('alias' => $alias));

		return $table->id;
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
		$id = (!empty($id)) ? $id : (int) $this->getState('trip.id');

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
		$id = (!empty($id)) ? $id : (int) $this->getState('incident.id');

		if ($id) {
			// Initialise the table
			$table = $this->getTable();

			// Get the current user object.
			$user = GatripsysHelper::getSpecificUser();

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
		$table = $this->getTable();
		$table->load($id);
		$table->state = $state;

		return $table->store();
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
		try {
			$table->store();
			return $table->trip_id;
	    } catch (RuntimeException $e) {
	        Factory::getApplication()->enqueueMessage($e->getMessage(),'danger');
		}	
		return false;
	}

	
}
