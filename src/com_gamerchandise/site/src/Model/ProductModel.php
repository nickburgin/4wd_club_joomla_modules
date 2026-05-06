<?php

/**
 * @version    4.0.7
 * @package    Com_Gamerchandise
 * @author     Glenn Arkell <glenn@glennarkell.com.au>
 * @copyright  2016 Glenn Arkell
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace GlennArkell\Component\Gamerchandise\Site\Model;

// No direct access.
\defined('_JEXEC') or die;

use \Joomla\CMS\Factory;
use \Joomla\Utilities\ArrayHelper;
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\Table\Table;
use \Joomla\CMS\MVC\Model\ItemModel;
use \Joomla\CMS\Helper\TagsHelper;
use \GlennArkell\Component\Gamerchandise\Administrator\Helper\GamerchandiseHelper;

/**
 * Item model.
 * @since  1.6
 */
class ProductModel extends ItemModel
{
	/**
	 * Method to auto-populate the model state.
0	 * Note. Calling getState in this method will result in recursion.
	 * @return void
	 * @since    1.6
	 *
	 */
	protected function populateState()
	{
		$app = Factory::getApplication('com_gamerchandise');

		// Load state from the request userState on edit or from the passed variable on default
		if (Factory::getApplication()->input->get('layout') == 'edit') {
			$id = Factory::getApplication()->getUserState('com_gamerchandise.edit.product.id');
		} else {
			$id = Factory::getApplication()->input->get('id');
			Factory::getApplication()->setUserState('com_gamerchandise.edit.product.id', $id);
		}

		$this->setState('product.id', $id);

		// Load the parameters.
		$params       = $app->getParams();
		$params_array = $params->toArray();

		if (isset($params_array['item_id'])) {
			$this->setState('product.id', $params_array['item_id']);
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
				$id = $this->getState('product.id');
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

            if (empty($this->_item)) {
				throw new \Exception(Text::_('COM_GAMERCHANDISE_ITEM_NOT_LOADED'), 404);
			}
		}

		if (isset($this->_item->created_by) ) {
			$this->_item->created_by_name = GamerchandiseHelper::getSpecificUser($this->_item->created_by)->name;
		}
		if (isset($this->_item->modified_by) ) {
			$this->_item->modified_by_name = GamerchandiseHelper::getSpecificUser($this->_item->modified_by)->name;
		}

		$this->_item->cat_id_name = $this->getCategoryName($this->_item->cat_id)->title;
		$this->_item->sales = GamerchandiseHelper::getSaleItems($this->_item->id);

		return $this->_item;
	}

	/**
	 * Get an instance of Table class
	 * @param   string  $type    Name of the Table class to get an instance of.
	 * @param   string  $prefix  Prefix for the table class name. Optional.
	 * @param   array   $config  Array of configuration values for the Table object. Optional.
	 * @return  Table|bool Table if success, false on failure.
	 */
	public function getTable($type = 'Product', $prefix = 'Administrator', $config = array())
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
            $aliasKey   = $this->getAliasFieldNameByView('product');
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
		$id = (!empty($id)) ? $id : (int) $this->getState('product.id');

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
		$id = (!empty($id)) ? $id : (int) $this->getState('product.id');

		if ($id) {
			// Initialise the table
			$table = $this->getTable();

			// Get the current user object.
			$user = GamerchandiseHelper::getSpecificUser();

			// Attempt to check the row out.
			if (method_exists($table, 'checkout')) {
				if (!$table->checkout($user->id, $id)) {
					return false;
				}
			}
		}

		return true;
	}

	/**
	 * Get the name of a category by id
	 * @param   int  $id  Category id
	 * @return  Object|null	Object if success, null in case of failure
	 */
	public function getCategoryName($id)
	{
		$db    = Factory::getContainer()->get('DatabaseDriver');
		$query = $db->getQuery(true);
		$query
			->select('title')
			->from('#__categories')
			->where('id = ' . $id);
		$db->setQuery($query);

		return $db->loadObject();
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

		return $table->store();
	}

}
