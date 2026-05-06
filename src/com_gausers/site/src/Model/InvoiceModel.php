<?php

/**
 * @version     5.1.6
 * @package     com_gausers
 * @author     Glenn Arkell <glenn@glennarkell.com.au>
 * @copyright  2019 Glenn Arkell
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace GlennArkell\Component\Gausers\Site\Model;

// No direct access.
defined('_JEXEC') or die;

use \Joomla\CMS\Factory;
use \Joomla\Utilities\ArrayHelper;
use \Joomla\CMS\MVC\Model\ItemModel;
use \Joomla\CMS\Table\Table;
use \Joomla\CMS\Date\Date;
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\Component\ComponentHelper;
use \GlennArkell\Component\Gausers\Administrator\Helper\GausersHelper;
use \GlennArkell\Component\Gausers\Administrator\Helper\GainvoiceHelper;
use \GlennArkell\Component\Gausers\Administrator\Helper\GaemailHelper;
use \GlennArkell\Component\Gausers\Administrator\Helper\GafamilyHelper;

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
		$app  = Factory::getApplication('com_gausers');
		$user = GausersHelper::getSpecificUser();

		// Check published state
		if ((!$user->authorise('core.edit.state', 'com_gausers')) && (!$user->authorise('core.edit', 'com_gausers')))
		{
			$this->setState('filter.published', 1);
			$this->setState('filter.archived', 2);
		}

		// Load state from the request userState on edit or from the passed variable on default
		if (Factory::getApplication()->input->get('layout') == 'edit')
		{
			$id = Factory::getApplication()->getUserState('com_gausers.edit.invoice.id');
		} else {
			$id = Factory::getApplication()->input->get('id');
			Factory::getApplication()->setUserState('com_gausers.edit.invoice.id', $id);
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
				$this->_item->created_by_name = GausersHelper::getSpecificUser($this->_item->created_by)->name;
			}
			if (isset($this->_item->user_id)) {
				$this->_item->user_id_name = GausersHelper::getSpecificUser($this->_item->user_id)->name;
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
			$user = GausersHelper::getSpecificUser();

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
	 * Publish the element
	 * @param   int $id    Item id
	 * @param   date $enddate
	 * @return  boolean
	 */
	public function extendExpiry($id, $enddate)
	{
		$table = $this->getTable();
                
		$table->load($id);
		$table->end_date = $enddate;

		return $table->store();
                
	}

	/**
	 * Method to create all renewal invoices
	 * @return  bool
	 */
	public function generateInv()
	{
		$financialOnly = 1;
		$compname = 'com_gausers';

		$lang = Factory::getLanguage();
		$lang->load($compname, JPATH_ADMINISTRATOR);
		$sitename = Factory::getConfig()->get('sitename');

		$params = ComponentHelper::getParams($compname);

		$cutoff_date  = substr($params->get('cutoff_date'),0,10);
		$exclude_email_pref  = $params->get('exclude_email_pref');
		$mship_exempt  = $params->get('mship_exempt');
		$mship_extend  = $params->get('mship_extend');
		$group_exempt  = $params->get('group_exempt');
		$preLen = strlen($exclude_email_pref);
		$default_mship  = $params->get('default_mship', 1);
		$useCutoff  = $params->get('use_cutoff');
		$exclude_member  = $params->get('exclude_member');
		$prof_pref  = $params->get('profile_suffix', 'b4wdc');

		// set up if family groups are being used
        $allowFamily  = $params->get('allow_family', 0);
        $primeMbr  = $params->get('primary_indic', 0);
		$prof_pref  = $params->get('profile_suffix', 'b4wdc');
        $link_family  = $params->get('link_family', 'mship_no');
        $linkFamily  = $prof_pref.'.'.$link_family;
        $mship_family  = $params->get('mship_family', array());

		$members = GausersHelper::getMembersDetails($params, $financialOnly);
		// cycle through the member records to generate the invoice and send
		if (is_array($members)) {
			foreach ($members AS $m) {

                // get last mship record
                $mship = GainvoiceHelper::getLastInvoiceMship($m->id);

                $groups = GausersHelper::getSpecificUser($m->id)->get('groups');

                if (empty($mship)) {
                    // if no past mship invoice, set default details
                    $mship = GausersHelper::getMshiptypeID($default_mship);
                }

                // if to be excluded don't do anymore go to next record
                if (in_array($m->id, $exclude_member)) {
                    Factory::getApplication()->enqueueMessage(Text::sprintf('COM_GAUSERS_USER_TOBE_EXCLUDED', $m->name));
                    continue;
                }

                // check for action record switching to life member
                if (strtolower($mship->title) == 'life') { continue; }

                // check if an action identifying a change to life member to add zero mship record
                $acts = GausersHelper::getRecordList('#__gausers_actions', 'a.user_id', $m->id, 'a.created_date', 'DESC');
                if (is_array($acts)) {
                    $moveToNextMbr = false;
                    foreach ($acts AS $act) {
                        $msTitle = strtolower($act->act_name);
                        if ($msTitle == 'life') {
                            $msLife = GausersHelper::getRecord('#__gausers_mshiptypes', 'title', $msTitle);
                            $msLife->mship_id = $msLife->id;
                            // Create a new mship record for new life membership
                            $lastInvDate = new Date(strtotime($params->get('invoice_date') ?? ''));
                            $eDate = $lastInvDate->modify('+2 YEAR');
                            $expDate = date_format($eDate,'Y-m-d H:i:s');
                            GainvoiceHelper::createNewInvoiceRec($m->id, 0.00, $msLife, $expDate);
                            $moveToNextMbr = true;
                        }
                    }
                    if ($moveToNextMbr) {
                        continue;
                    }
                }

                $m->mship = $mship;

                // if exempt don't do anymore go to next record
                if (in_array($m->mship->mship_id, $mship_exempt)) {
                    Factory::getApplication()->enqueueMessage(Text::sprintf('COM_GAUSERS_USER_MSHIP_EXEMPT', $m->name));

                    // check for life member to update expiry date
                    if (in_array($m->mship->mship_id, $mship_extend)) {
                        // setup new expiry date
                        $new_expDate = new Date($m->mship->end_date);
                        $new_expDate = $new_expDate->modify('+1 years');
                        $new_expDate = $new_expDate->format('Y-m-d');
                        self::extendExpiry($mship->id, $new_expDate);
                    }
                    continue;
                }

                // check if member belongs to the group to be ignored
                $ignoreMember = false;
                foreach ($groups as $g) {
                    if (in_array($g, $group_exempt)) { 
                        $ignoreMember = true;
                    }
                }
                if ($ignoreMember) {
                    continue;
                }

                // if registered after cut-off date skip to next record
                $registerDate = substr($m->registerDate,0,10);
                if ($useCutoff && $registerDate >= $cutoff_date) {
                    Factory::getApplication()->enqueueMessage(Text::sprintf('COM_GAUSERS_CUTOFF_MESSAGE', $m->name.' - '.$registerDate, $cutoff_date), 'warning');
                    continue;
                }

        		// set up if family groups are being used
                if ($allowFamily && in_array($m->mship->mship_id, $mship_family)) {
                    // get family member records
                    $family_mbrs = GafamilyHelper::getFamilyMembers($m->id, $linkFamily);
                    if ($family_mbrs === false) {
                        Factory::getApplication()->enqueueMessage(Text::_('Family member of Primary - '.$m->name), 'warning');
                        continue;
                    } else {
                        // load returned array of family members (id,mship_no)
                        $m->family_mbrs = $family_mbrs;
                    }
                } else {
                    $m->family_mbrs = 0;
                }

                // now generate the invoice pdf and db record
                $inv = GainvoiceHelper::mainInvoiceCreation($m->id, $m, $params);

                // if no invoice generated then skip to next record
                if (!is_file($inv) && $inv) { 
                    Factory::getApplication()->enqueueMessage(Text::_('Invoice zero - '.$m->name), 'warning');
                    continue;
                }

                // test for email address and if genuine, send invoice email
				if (substr($m->email,0,$preLen) != $exclude_email_pref) {
					$body = Text::sprintf('COM_GAUSERS_INVOICE_EMAIL_SALUTATION',$m->name);
					if (is_file($inv)) {
						$body .= Text::_('COM_GAUSERS_INVOICE_EMAIL_BODY');
					} elseif ($inv) {
						$body .= Text::sprintf('COM_GAUSERS_CUTOFF_MESSAGE', $registerDate, $cutoff_date);
					}
					$body .= Text::sprintf('COM_GAUSERS_INVOICE_EMAIL_SIGNOFF',$sitename);
					$subject = Text::_('COM_GAUSERS_INVOICE_EMAIL_SUBJECT');
					GaemailHelper::sendEmail(array($m->email), $body, $subject, $inv);
				}
			}
		} else {
			// no members found
			Factory::getApplication()->enqueueMessage(Text::_('COM_GAUSERS_NO_MEMBERS_FOUND'), 'danger');
		}
		GausersHelper::updateExtensionParams($compname);

		return true;

	}

}
