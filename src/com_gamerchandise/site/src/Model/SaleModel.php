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
use \Joomla\CMS\Component\ComponentHelper;
use \GlennArkell\Component\Gamerchandise\Administrator\Helper\GamerchandiseHelper;

/**
 * Item model.
 * @since  1.6
 */
class SaleModel extends ItemModel
{
	/**
	 * Method to auto-populate the model state.
	 * Note. Calling getState in this method will result in recursion.
	 * @return void
	 * @since    1.6
	 */
	protected function populateState()
	{
		$app = Factory::getApplication('com_gamerchandise');

		// Load state from the request userState on edit or from the passed variable on default
		if (Factory::getApplication()->input->get('layout') == 'edit') {
			$id = Factory::getApplication()->getUserState('com_gamerchandise.edit.sale.id');
		} else {
			$id = Factory::getApplication()->input->get('id');
			Factory::getApplication()->setUserState('com_gamerchandise.edit.sale.id', $id);
		}

		$this->setState('sale.id', $id);

		// Load the parameters.
		$params       = $app->getParams();
		$params_array = $params->toArray();

		if (isset($params_array['item_id'])) {
			$this->setState('sale.id', $params_array['item_id']);
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
				$id = $this->getState('sale.id');
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
		$this->_item->product = GamerchandiseHelper::getProduct($this->_item->prod_id);
		$this->_item->user_id_name = GamerchandiseHelper::getSpecificUser($this->_item->user_id)->name;

		return $this->_item;
	}

	/**
	 * Get an instance of Table class
	 * @param   string  $type    Name of the Table class to get an instance of.
	 * @param   string  $prefix  Prefix for the table class name. Optional.
	 * @param   array   $config  Array of configuration values for the Table object. Optional.
	 * @return  Table|bool Table if success, false on failure.
	 */
	public function getTable($type = 'Sale', $prefix = 'Administrator', $config = array())
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
            $aliasKey   = $this->getAliasFieldNameByView('sale');
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
		$id = (!empty($id)) ? $id : (int) $this->getState('sale.id');

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
		$id = (!empty($id)) ? $id : (int) $this->getState('sale.id');

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

		$deleteOK = $table->store();

		if ($deleteOK) {
			return $table->prod_id;
		} else {
			return false;
		}
	}

	/**
	 * Method to mark an item as paid
	 * @param   int  $id  Element id
	 * @return  bool
	 */
	public function markPaid($id)
	{
		// get the current date-time based on timezone
		$today = Factory::getDate()->toSql();
        $params  = ComponentHelper::getParams('com_gamerchandise');
        $incl_finance  = $params->get('incl_finance',0);
        $fin_cat  = $params->get('fin_cat',0);

		$table = $this->getTable();
		$table->load($id);
		$table->paid_date = $today;
		$table->ord_paid = 1;
		$table->state = 3;

		try {
			$table->store();
			if ($incl_finance) {
				GamerchandiseHelper::createFinanceTrans($id, 'I', $fin_cat);
			}
			return true;
		} catch (RuntimeException $e) {
			Factory::getApplication()->enqueueMessage($e->getMessage(), 'danger');
			return  false;
		}

	}

	/**
	 * Method to mark an item as ordered
	 * @param   int  $id  Element id
	 * @return  bool
	 */
	public function markOrdered($id)
	{
        $params  = ComponentHelper::getParams('com_gamerchandise');
        $incl_finance  = $params->get('incl_finance',0);
        $fin_cat  = $params->get('fin_cat',0);

		$table = $this->getTable();
		$table->load($id);
		$table->state = 4;

		try {
			$table->store();
			if ($incl_finance) {
				GamerchandiseHelper::createFinanceTrans($id, 'E', $fin_cat);
			}
			return true;
		} catch (RuntimeException $e) {
			Factory::getApplication()->enqueueMessage($e->getMessage(), 'danger');
			return  false;
		}

	}

	/**
	 * Method to mark an item as ordered
	 * @param   int  $id  Element id
	 * @return  bool
	 */
	public function markDelivered($id)
	{
		// get the current date-time based on timezone
		$today = Factory::getDate()->toSql();

		$table = $this->getTable();
		$table->load($id);
		$table->date_delivered = $today;
		$table->state = 2;

		return $table->store();

	}

	/**
	 * Method to action the items purchased
	 * @param  integer id reference of a record to mark as ordered.
	 * @return  boolean true on success, false on failure.
	 */
	public function commitPurchases($id = 0)
	{
		$user = GamerchandiseHelper::getSpecificUser();
		// get the sales records for this user
        $db		= Factory::getContainer()->get('DatabaseDriver');
		$query	= $db->getQuery(true);
        $query->clear();
		$query->select(' a.*, p.prod_name, p.prod_desc, c.title AS prod_colour, s.title AS prod_size ');
		$query->from(' #__gamerchandise_sales AS a');
		$query->join('LEFT',' #__gamerchandise_products AS p ON p.id = a.prod_id');
		$query->join('LEFT',' #__categories AS c ON c.id = a.cat_colour_id');
		$query->join('LEFT',' #__categories AS s ON s.id = a.cat_size_id');
		if ($id) {
			$query->where(' a.id = '.(int) $id );
		} else {
			$query->where(' a.user_id = '.(int) $user->id );
		}
		$query->where(' a.state = 0 ' );
		$db->setQuery((string)$query);

	    try {
	        $rows = $db->loadObjectList();
	    } catch (\RuntimeException $e) {
	        Factory::getApplication()->enqueueMessage($e->getMessage(),'danger');
	        return false;
	    }

		if ($id) {
			$order_by = $rows[0]->user_id;
		} else {
			$order_by = $user->id;
		}

		$attachment = GamerchandiseHelper::generatePDF($rows);
        $subject = "Merchandise Order";
        $pdfok = GamerchandiseHelper::sendEmail($attachment, $subject);
        $order_ref = Factory::getApplication()->getUserState('com_gamerchandise.po.data');
        Factory::getApplication()->setUserState('com_gamerchandise.po.data', null);

        if ($pdfok) {
			$db		= Factory::getContainer()->get('DatabaseDriver');
			$query	= $db->getQuery(true);
	        $query->clear();
			$query = ' UPDATE #__gamerchandise_sales SET state = 5, order_ref = '.(int) $order_ref.' WHERE user_id = '.(int) $order_by.' AND state = 0 ';
			$db->setQuery((string)$query);

		    try {
		        $result = $db->execute();
		        return true;
		    } catch (\RuntimeException $e) {
		        Factory::getApplication()->enqueueMessage($e->getMessage(),'danger');
		        return false;
		    }
		} else {
			return false;
		}

	}

}
