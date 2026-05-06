<?php

/**
 * @version     5.1.0
 * @package     com_gatripsys
 * @author     Glenn Arkell <glenn@glennarkell.com.au>
 * @copyright  2019 Glenn Arkell
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
use \Joomla\CMS\Component\ComponentHelper;
use \GlennArkell\Component\Gatripsys\Administrator\Helper\GatripsysHelper;
use \GlennArkell\Component\Gatripsys\Administrator\Helper\GainvoiceHelper;
use \GlennArkell\Component\Gatripsys\Administrator\Helper\GanotificationsHelper;

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
	 */
	protected function populateState()
	{
		$app  = Factory::getApplication('com_gatripsys');
		$user = GatripsysHelper::getSpecificUser();

		// Check published state
		if ((!$user->authorise('core.edit.state', 'com_gatripsys')) && (!$user->authorise('core.edit', 'com_gatripsys')))
		{
			$this->setState('filter.published', 1);
			$this->setState('filter.archived', 2);
		}

		// Load state from the request userState on edit or from the passed variable on default
		if (Factory::getApplication()->input->get('layout') == 'edit')
		{
			$id = Factory::getApplication()->getUserState('com_gatripsys.edit.invoice.id');
		} else {
			$id = Factory::getApplication()->input->get('id');
			Factory::getApplication()->setUserState('com_gatripsys.edit.invoice.id', $id);
		}

		$this->setState('invoice.id', $id);

		// Load the parameters.
		$params       = $app->getParams();
		$params_array = $params->toArray();

		if (isset($params_array['item_id'])) {
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
                            throw new \Exception(Text::_('COM_GABROADCAST_ITEM_NOT_LOADED'), 403);
                        }
                    }

                    // Convert the Table to a clean Object.
                    $properties  = $table->getProperties(1);
                    $this->_item = ArrayHelper::toObject($properties, 'stdClass');

                }
            }

			if (isset($this->_item->created_by)) {
				$this->_item->created_by_name = GatripsysHelper::getSpecificUser($this->_item->created_by)->name;
			}
			if (isset($this->_item->user_id)) {
				$this->_item->user_id_name = GatripsysHelper::getSpecificUser($this->_item->user_id)->name;
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
	 * Method to mark an item as paid
	 * @param   int $id Element id
	 * @return  bool
	 */
	public function markPaid($data)
	{
		$table = $this->getTable();
		$table->load($data['id']);
		$table->state = 2;
		$table->invoice_amt = $data['invoice_amt'];
		$table->paid_date = $data['paid_date'];

		return $table->store();

	}

	/**
	 * Method to resend an invoice to member
	 * @param   int $id Element id
	 * @return  bool
	 */
	public function resendInv($data)
	{
		$sentOK = GainvoiceHelper::resendInv($data);

		return $sentOK;

	}

	/**
	 * Method to resend an invoice to member
	 * @param   int $id Element id
	 * @return  bool
	 */
	public function generateInv()
	{
		$compname = 'com_gatripsys';
		$lang = Factory::getApplication()->getLanguage();
		$lang->load($compname, JPATH_ADMINISTRATOR);
		$params = ComponentHelper::getParams($compname);
		$members = GatripsysHelper::getMembersDetails($params, 1);
		$cutoff_date  = $params->get('cutoff_date');
		$exclude_email_pref  = $params->get('exclude_email_pref');
		$preLen = strlen($exclude_email_pref);
		$sitename = Factory::getConfig()->get('sitename');

		// cycle through the member records to generate the invoice and send
		if (is_array($members)) {
			foreach ($members AS $m) {
				$inv = GainvoiceHelper::createInvoice($m);
				
				// test for email address and if genuine, send invoice email
				if (substr($m->email,0,$preLen) != $exclude_email_pref) {
					if (is_file($inv)) {
						$body = '<p>Dear '.$m->name.',</p><p>Find attached your invoice.</p><p> </p><p>'.$sitename.'</p>';
						GanotificationsHelper::sendEmail(array($m->email), $body, 'Event/Trip Invoice', $inv);
					} elseif ($inv) {
						$body = '<p>Dear '.$m->name.',</p>';
						$body .= '<p>'.Text::sprintf('COM_GATRIPSYS_CUTOFF_MESSAGE',substr($m->registerDate,0,10),substr($cutoff_date,0,10)).'</p>';
						$body .= '<p> </p><p>'.$sitename.'</p>';
						GanotificationsHelper::sendEmail(array($m->email), $body, 'Event/Trip Invoice', $inv);
					}
				}
			}
		} else {
			// no members found
			Factory::getApplication()->enqueueMessage('No members found, please check settings', 'danger');
		}
		GatripsysHelper::updateExtensionParams($compname);

		return true;

	}

}
