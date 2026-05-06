<?php
/**
 * @version    5.2.3
 * @package    Com_Gafinance
 * @author     Glenn Arkell <glenn@glennarkell.com.au>
 * @copyright  2021 Glenn Arkell
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace GlennArkell\Component\Gafinance\Site\Model;
// No direct access.
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Table\Table;
use Joomla\CMS\MVC\Model\ItemModel;
use \GlennArkell\Component\Gafinance\Administrator\Helper\GafinanceHelper;
use \GlennArkell\Component\Gafinance\Administrator\Helper\GainvoiceHelper;

/**
 * Item model.
 * @since  1.6
 */
class InvoiceModel extends ItemModel
{
    public $_item;

	/**
	 * Method to auto-populate the model state.
	 * Note. Calling getState in this method will result in recursion.
	 * @return void
	 * @since    1.6
     * @throws Exception
	 */
	protected function populateState()
	{
		$app  = Factory::getApplication('com_gafinance');
		$user = GafinanceHelper::getSpecificUser();

		// Check published state
		if ((!$user->authorise('core.edit.state', 'com_gafinance')) && (!$user->authorise('core.edit', 'com_gafinance'))) {
			$this->setState('filter.published', 1);
			$this->setState('filter.archived', 2);
		}

		// Load state from the request userState on edit or from the passed variable on default
		if ($app->input->get('layout') == 'edit') {
			$id = $app->getUserState('com_gafinance.edit.invoice.id');
		} else {
			$id = $app->input->get('id');
			$app->setUserState('com_gafinance.edit.invoice.id', $id);
		}

		$this->setState('invoice.id', $id);

		// Load the parameters.
		$params       = $app->getParams();
		$params_array = $params->toArray();

		if (isset($params_array['item_id']))
		{
			$this->setState('invoice.id', $params_array['item_id']);
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
				$id = $this->getState('invoice.id');
			}
	
			// Get a level row instance.
			$table = $this->getTable();
	
			// Attempt to load the row.
			if ($table->load($id)) {
	
				// Check published state.
				if ($published = $this->getState('filter.published')) {
					if (isset($table->state) && $table->state != $published) {
						throw new \Exception(Text::_('COM_GAFINANCE_ITEM_NOT_LOADED'), 403);
					}
				}
		
				// Convert the Table to a clean Object.
				$properties  = $table->getProperties(1);
				$this->_item = ArrayHelper::toObject($properties, 'stdClass');
	
			}
			
			if (empty($this->_item)) {
				throw new \Exception(Text::_('COM_GAFINANCE_ITEM_NOT_LOADED'), 404);
			}
		}

		if (isset($this->_item->created_by)) {
			$this->_item->created_by_name = GafinanceHelper::getSpecificUser($this->_item->created_by)->name;
		}

		if (isset($this->_item->modified_by)) {
			$this->_item->modified_by_name = GafinanceHelper::getSpecificUser($this->_item->modified_by)->name;
		}

		if (isset($this->_item->client_id)) {
			$this->_item->client_id_name = GafinanceHelper::getRecordValue($this->_item->client_id, '#__gafinance_patrons', 'name');
		}

		if (isset($this->_item->invoice_type)) {
			$this->_item->invoice_type_name = GafinanceHelper::getRecordValue($this->_item->invoice_type, '#__gafinance_invtypes', 'invtype_name');
		}

		if (isset($this->_item->accnt_id)) {
			$this->_item->accnt_id_name = GafinanceHelper::getRecordValue($this->_item->accnt_id, '#__gafinance_accounts', 'accnt_name');
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
	public function getTable($type = 'Invoice', $prefix = 'Administrator', $config = array())
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
		$aliasKey   = null;
		if (method_exists($this, 'getAliasFieldNameByView')) {
			$aliasKey   = $this->getAliasFieldNameByView('invoice');
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
	 * @param   integer $id The id of the row to check out.
	 * @return  boolean True on success, false on failure.
	 * @since    1.6
	 */
	public function checkin($id = null)
	{
		// Get the id.
		$id = (!empty($id)) ? $id : (int) $this->getState('invoice.id');
                
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
		$id = (!empty($id)) ? $id : (int) $this->getState('invoice.id');

		if ($id) {
			// Initialise the table
			$table = $this->getTable();

			// Get the current user object.
			$user = GafinanceHelper::getSpecificUser();

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
	 * Go to the helper tyo generate the pdf
	 */
	public function generateInv($pk)
	{
		$pks = array();
		$pks[] = $pk;

        $client = GainvoiceHelper::createinv($pks);

        return $client;
	}

	public function sendInv($pk, $smail)
	{
		$pks = array();
		$pks[] = $pk;

        $client = GainvoiceHelper::sendInvoices($pks, $smail);

        return $client;
	}

	public function markPaid($pk)
	{
		$pks = array();
		$pks[] = $pk;
		// invoice record status will be 2 when paid
        $client = GainvoiceHelper::markInvoicesPaid($pks);

        return $client;
	}

	public function cancelInvoice($pk)
	{
		$table = $this->getTable();
		if ($table->load($pk)) {
			$table->state = 6;

	        if (!$table->store() === true) {
	            return false;
	        }
        }

        return GafinanceHelper::getRecordValue($table->client_id, '#__gafinance_patrons', 'name');
	}

	public function archiveInvoice($pk)
	{
		$table = $this->getTable();
		if ($table->load($pk)) {
			$table->state = 7;

	        if (!$table->store() === true) {
	            return false;
	        }
        }

        return GafinanceHelper::getRecordValue($table->client_id, '#__gafinance_patrons', 'name');
	}
}
