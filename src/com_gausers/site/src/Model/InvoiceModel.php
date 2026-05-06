<?php

/**
 * @version     6.0.0
 * @package     com_gausers
 * @author     Glenn Arkell <glenn@glennarkell.com.au>
 * @copyright  2019 Glenn Arkell
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace GlennArkell\Component\Gausers\Site\Model;

// No direct access.
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\MVC\Model\ItemModel;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\User\UserHelper;
use GlennArkell\Component\Gausers\Administrator\Helper\GanamesHelper;
use GlennArkell\Component\Gausers\Administrator\Helper\GausersHelper;
use GlennArkell\Component\Gausers\Administrator\Helper\GainvoiceHelper;
use GlennArkell\Component\Gausers\Administrator\Helper\GaemailHelper;
use GlennArkell\Component\Gausers\Administrator\Helper\GafamilyHelper;

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
		$user = Factory::getApplication()->getIdentity();

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
		$app = Factory::getApplication();
		$app->getLanguage()->load('com_gausers', JPATH_ADMINISTRATOR);
		$sitename = $app->get('sitename');

		$params = ComponentHelper::getParams('com_gausers');

		$useCutoff  = $params->get('use_cutoff');
		$cutoff_date  = $params->get('cutoff_date');
		$exclude_email_pref  = $params->get('exclude_email_pref');
		$mship_exempt  = $params->get('mship_exempt');
		$mship_extend  = $params->get('mship_extend');
		$group_exempt  = $params->get('group_exempt');
		$preLen = strlen($exclude_email_pref);
		$default_mship  = $params->get('default_mship', 1);
		$exclude_member  = $params->get('exclude_member');
		$prof_pref  = $params->get('profile_suffix', 'b4wdc');

		// set up if family groups are being used
        $allowFamily  = $params->get('allow_family', 0);
        $primeMbr  = $params->get('primary_indic', 0);
		$prof_pref  = $params->get('profile_suffix', 'b4wdc');
        $link_family  = $params->get('link_family', 'mship_no');
        $linkFamily  = $prof_pref.'.'.$link_family;
        $mship_family  = $params->get('mship_family', array());
        
		$defMship = GausersHelper::getMshiptypeID($default_mship);

        // get dates using the start date in params
        $oldExpDate = GainvoiceHelper::setupOldEndDate($params, $defMship);
        $newExpDate = GainvoiceHelper::setupNewEndDate($params, $defMship);

        // flash up a message to show dates
        $app->enqueueMessage($oldExpDate.' - '.$newExpDate, 'notice');

		// get all the members - currently financial only
        $members = GausersHelper::getMembersDetails($params, 1);

		// cycle through the member records to generate the invoice and send
		if (is_array($members)) {
			foreach ($members AS $m) {

                $m->oldExpDate = $oldExpDate;
                $m->newExpDate = $newExpDate;

                // get last mship record
                $lastInv = GainvoiceHelper::getLastInvoiceMship($m->id);

                $groups = GausersHelper::getSpecificUser($m->id)->get('groups');

                if (!$lastInv || empty($lastInv)) {
                    // if no past mship invoice, set default details
                    $lastInv = $defMship;
                }

                // if paid up for next year, skip
                if (isset($lastInv->end_date) && $lastInv->end_date > $newExpDate) {
                    $app->enqueueMessage(Text::sprintf('COM_GAUSERS_USER_PAID_INADVANCE', $m->name), 'notice');
                    continue;
                }

                // if to be excluded don't do anymore go to next record
                if (in_array($m->id, $exclude_member)) {
                    $app->enqueueMessage(Text::sprintf('COM_GAUSERS_USER_TOBE_EXCLUDED', $m->name), 'notice');
                    continue;
                }

                // check for membership record for life member
                if (strtolower($lastInv->title) == 'life') {
                    GainvoiceHelper::createNewInvoiceRec($m->id, 0.00, $m->mship, $newExpDate);
                    continue;
                }

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
                            GainvoiceHelper::createNewInvoiceRec($m->id, 0.00, $msLife, $newExpDate);
                            $moveToNextMbr = true;
                        }
                    }
                    if ($moveToNextMbr) {
                        continue;
                    }
                }

                $m->mship = $lastInv;

                // if exempt don't do anymore go to next record
                if (is_array($mship_exempt) && in_array($m->mship->mship_id, $mship_exempt)) {
                    $app->enqueueMessage(Text::sprintf('COM_GAUSERS_USER_MSHIP_EXEMPT', $m->name), 'notice');

                    // check for life member to update expiry date
                    if (in_array($m->mship->mship_id, $mship_extend)) {
                        // setup new expiry date
                        self::extendExpiry($lastInv->id, $newExpDate);
                    }
                    continue;
                }

                // check if member belongs to the group to be ignored
                $ignoreMember = false;
                foreach ($groups as $g) {
                    if (is_array($group_exempt) && in_array($g, $group_exempt)) {
                        $ignoreMember = true;
                    }
                }
                if ($ignoreMember) {
                    continue;
                }

                // if registered after cut-off date skip to next record
                //$registerDate = substr($m->registerDate,0,10);
                $registerDate = new Date(strtotime($m->registerDate));
                $regDate = date_format($registerDate,'Y-m-d');
                if ($useCutoff && $regDate >= $cutoff_date) {
                    $app->enqueueMessage(Text::sprintf('COM_GAUSERS_CUTOFF_MESSAGE', $m->name.' - '.$regDate, $cutoff_date), 'notice');
                    $body = Text::sprintf('COM_GAUSERS_INVOICE_EMAIL_SALUTATION',$m->name);
                    $body .= Text::sprintf('COM_GAUSERS_CUTOFF_MESSAGE', $regDate, $cutoff_date);
                    $subject = Text::_('COM_GAUSERS_INVOICE_EMAIL_SUBJECT');
                    GaemailHelper::sendEmail(array($m->email), $body, $subject, null);
                    self::extendExpiry($lastInv->id, $newExpDate);
                    continue;
                }

        		// set up if family groups are being used
                if ($allowFamily && is_array($mship_family) && in_array($m->mship->mship_id, $mship_family)) {
                    // get family member records
                    $family_mbrs = GafamilyHelper::getFamilyMembers($m->id, $linkFamily);
                    if ($family_mbrs === false) {
                        $app->enqueueMessage(Text::_('Family member of Primary - '.$m->name), 'notice');
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
                    $app->enqueueMessage(Text::_('Invoice zero - '.$m->name), 'warning');
                    continue;
                }

                // test for email address and if genuine, send invoice email
				if (substr($m->email,0,$preLen) != $exclude_email_pref) {
					$body = Text::sprintf('COM_GAUSERS_INVOICE_EMAIL_SALUTATION',$m->name);
					if (is_file($inv)) {
						$body .= Text::_('COM_GAUSERS_INVOICE_EMAIL_BODY');
					} elseif ($inv) {
						$body .= Text::sprintf('COM_GAUSERS_CUTOFF_MESSAGE', $regDate, $cutoff_date);
					}
					$body .= Text::sprintf('COM_GAUSERS_INVOICE_EMAIL_SIGNOFF',$sitename);
					$subject = Text::_('COM_GAUSERS_INVOICE_EMAIL_SUBJECT');
					GaemailHelper::sendEmail(array($m->email), $body, $subject, $inv);
				}
			}
    		GausersHelper::updateExtensionParams('com_gausers');
		} else {
			// no members found
			$app->enqueueMessage(Text::_('COM_GAUSERS_NO_MEMBERS_FOUND'), 'danger');
		}

		return true;

	}

	/**
	 * Method to create a PDF invoice
	 * @return  bool
	 */
	public function regenInvoice($id = 0)
	{
		$app = Factory::getApplication();
		$app->getLanguage()->load('com_gausers', JPATH_ADMINISTRATOR);

		$params = ComponentHelper::getParams('com_gausers');

		$invRec = GausersHelper::getRecord('#__gausers_invoices', 'id', $id);
		$user = GanamesHelper::breakdownNamesFromUserID($invRec->user_id, 'partner');
		$app->setUserState('com_gausers.user.data', $user);

		$mship = GausersHelper::getRecord('#__gausers_mshiptypes', 'id', $invRec->mship_id);
		$nextinv  = str_pad($id, 6, '0', STR_PAD_LEFT);
		$app->setUserState('com_gausers.nextinv.data', $nextinv);

        $data = array();
		$data['nextinv'] = $nextinv;
		$data['invRec'] = $invRec;
		$data['mship'] = $mship;
		
		$invFile = GainvoiceHelper::createAdHocPDF($data, $id, $params);
		if (\is_file($invFile) && $invFile) {
            $app->enqueueMessage(Text::_('COM_GAUSERS_NO_MEMBERS_FOUND'), 'danger');
            return $invFile;
        }
        
        return false;


	}
}
