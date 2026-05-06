<?php

/**
 * @version    4.3.3
 * @package    Com_Gabroadcast
 * @author     Glenn Arkell <glenn@glennarkell.com.au>
 * @copyright  2019 Glenn Arkell
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace GlennArkell\Component\Gabroadcast\Site\Model;

// No direct access.
\defined('_JEXEC') or die;

use \Joomla\CMS\Factory;
use \Joomla\Utilities\ArrayHelper;
use \Joomla\CMS\MVC\Model\ItemModel;
use \Joomla\CMS\Table\Table;
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\MVC\View\GenericDataException;
use \GlennArkell\Component\Gabroadcast\Administrator\Helper\GabroadcastHelper;

/**
 * Gabroadcast model.
 *
 * @since  1.6
 */
class UsernewModel extends ItemModel
{
    public $_item;

	/**
	 * Method to auto-populate the model state.
	 * Note. Calling getState in this method will result in recursion.
	 * @return void
	 * @since    1.6
	 *
	 */
	protected function populateState()
	{
		$app  = Factory::getApplication('com_gabroadcast');
		$user = $app->getIdentity();

		// Check published state
		if ((!$user->authorise('core.edit.state', 'com_gabroadcast')) && (!$user->authorise('core.edit', 'com_gabroadcast')))
		{
			$this->setState('filter.published', 1);
			$this->setState('filter.archived', 2);
		}

		// Load state from the request userState on edit or from the passed variable on default
		if ($app->input->get('layout') == 'edit')
		{
			$id = $app->getUserState('com_gabroadcast.edit.usernew.id');
		} else {
			$id = $app->input->get('id');
			$app->setUserState('com_gabroadcast.edit.usernew.id', $id);
		}

		$this->setState('usernew.id', $id);

		// Load the parameters.
		$params       = $app->getParams();
		$params_array = $params->toArray();

		if (isset($params_array['item_id'])) {
			$this->setState('usernew.id', $params_array['item_id']);
		}

		$this->setState('params', $params);
	}

	/**
	 * Method to get an object.
	 * @param   integer $id The id of the object to get.
	 * @return  mixed    Object on success, false on failure.
     * @throws Exception
	 */
	public function getItem($id = null)
	{
            if ($this->_item === null) {
                $this->_item = false;

                if (empty($id)) {
                    $id = $this->getState('usernew.id');
                }

                // Get a level row instance.
                $table = $this->getTable();

                // Attempt to load the row.
                if ($table->load($id)) {

                    // Check published state.
                    if ($published = $this->getState('filter.published')) {
                        if (isset($table->state) && $table->state != $published) {
                            throw new \Exception(Text::_('COM_GABROADCAST_ITEM_NOT_LOADED'), 403);
                        }
                    }

                    // Convert the Table to a clean Object.
                    $properties  = $table->getProperties(1);
                    $this->_item = ArrayHelper::toObject($properties, 'stdClass');

                }
            }

			if (isset($this->_item->created_by)) {
				$this->_item->created_by_name = GabroadcastHelper::getSpecificUser($this->_item->created_by)->name;
			}
	
			if (isset($this->_item->modified_by)) {
				$this->_item->modified_by_name = GabroadcastHelper::getSpecificUser($this->_item->modified_by)->name;
			}

			if (isset($this->_item->cat_id)) {
				$this->_item->cat_id_name = GabroadcastHelper::getCategoryNameByCategoryId($this->_item->cat_id);
			}

            return $this->_item;
        }

	/**
	 * Get an instance of Table class
	 * @param   string $type   Name of the Table class to get an instance of.
	 * @param   string $prefix Prefix for the table class name. Optional.
	 * @param   array  $config Array of configuration values for the Table object. Optional.
	 * @return  Table|bool Table if success, false on failure.
	 */
	public function getTable($type = 'Usernew', $prefix = 'Administrator', $config = array())
	{
		return parent::getTable($type, $prefix, $config);
	}

	/**
	 * Get the id of an item by alias
	 * @param   string $alias Item alias
	 * @return  mixed
	 */
	public function getItemIdByAlias($alias)
	{
        $table      = $this->getTable();
        $properties = $table->getProperties();
        $result     = null;

        if (key_exists('alias', $properties)) {
            $table->load(array('alias' => $alias));
            $result = $table->id;
        }
            
        return $result;
            
	}

	/**
	 * Method to check in an item.
	 * @param   integer $id The id of the row to check out.
	 * @return  boolean True on success, false on failure.
	 * @since    1.6
	 */
	public function checkin($id = null)
	{
		// Get the id.
		$id = (!empty($id)) ? $id : (int) $this->getState('usernew.id');
                
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
	 * @param   integer $id The id of the row to check out.
	 * @return  boolean True on success, false on failure.
	 * @since    1.6
	 */
	public function checkout($id = null)
	{
		// Get the user id.
		$id = (!empty($id)) ? $id : (int) $this->getState('usernew.id');

		if ($id) {
			// Initialise the table
			$table = $this->getTable();

			// Get the current user object.
			$user = Factory::getApplication()->getIdentity();

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
	 * @param   int $id    Item id
	 * @param   int $state Publish state
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
	 * @param   int $id Element id
	 * @return  bool
	 */
	public function delete($id)
	{
		$table = $this->getTable();
		$table->load($id);
		$table->state = -2;

		return $table->store();

	}

	/**
	 * Method to send a prending broadcast
	 * @param   int $id Element id
	 * @return  bool
	 */
	public function sendOut($id)
	{
		$sent = GabroadcastHelper::sendPending($id);

		if ($sent !== true) {
			return false;
		} else {
			$today = Factory::getDate()->toSql();
			$table = $this->getTable();
			$table->load($id);
			//$table->state = 6;
			$table->modified_date = $today;

			return $table->store();
		}

	}

}
