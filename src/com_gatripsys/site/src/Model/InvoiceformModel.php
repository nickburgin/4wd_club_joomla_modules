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
use \Joomla\CMS\MVC\Model\FormModel;
use \Joomla\CMS\Table\Table;
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\Component\ComponentHelper;
use \Joomla\Filesystem\File;
use \Joomla\Filesystem\Folder;
use \Joomla\Filesystem\Path;
use \Joomla\CMS\User\User;
use \GlennArkell\Component\Gatripsys\Administrator\Helper\GaemailHelper;
use \GlennArkell\Component\Gatripsys\Administrator\Helper\GatripsysHelper;
use \GlennArkell\Component\Gatripsys\Administrator\Helper\GainvoiceHelper;
use \GlennArkell\Component\Gatripsys\Administrator\Helper\GanotificationsHelper;

/**
 * Form model.
 * @since  1.6
 */
class InvoiceformModel extends FormModel
{
    private $item = null;

    /**
     * Method to auto-populate the model state.
     * Note. Calling getState in this method will result in recursion.
     * @return void
     * @since  1.6
     */
    protected function populateState()
    {
        $app = Factory::getApplication('com_gatripsys');

        // Load state from the request userState on edit or from the passed variable on default
        if (Factory::getApplication()->input->get('layout') == 'edit') {
            $id = $app->getUserState('com_gatripsys.edit.invoice.id');
        } else {
            $id = Factory::getApplication()->input->get('id');
            $app->setUserState('com_gatripsys.edit.invoice.id', $id);
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
     * Method to get an ojbect.
     * @param   integer $id The id of the object to get.
     * @return Object|boolean Object on success, false on failure.
     * @throws Exception
     */
    public function getItem($id = null)
    {
        if ($this->item === null) {
            $this->item = false;

            if (empty($id)) {
                    $id = $this->getState('invoice.id');
            }

            // Get a level row instance.
            $table = $this->getTable();

            if ($table !== false && $table->load($id)) {
                $user = GatripsysHelper::getSpecificUser();
                $id   = $table->id;

                $canEdit = $user->authorise('core.edit', 'com_gatripsys') || $user->authorise('core.create', 'com_gatripsys');

                if (!$canEdit && $user->authorise('core.edit.own', 'com_gatripsys')) {
                        $canEdit = $user->id == $table->created_by;
                }

                if (!$canEdit) {
                        throw new \Exception(Text::_('JERROR_ALERTNOAUTHOR'), 403);
                }

                // Check published state.
                if ($published = $this->getState('filter.published')) {
                        if (isset($table->state) && $table->state != $published) {
                                return $this->item;
                        }
                }

                // Convert the Table to a clean Object.
                $properties = $table->getProperties(1);
                $this->item = ArrayHelper::toObject($properties, 'stdClass');
                
            }
        }

        return $this->item;
    }

    /**
     * Method to get the table
     * @param   string $type   Name of the Table class
     * @param   string $prefix Optional prefix for the table class name
     * @param   array  $config Optional configuration array for Table object
     * @return  Table|boolean Table if found, boolean false on failure
     */
	public function getTable($type = 'Invoice', $prefix = 'Administrator', $config = array())
	{
        return parent::getTable($type, $prefix, $config);
    }

    /**
     * Get an item by alias
     * @param   string $alias Alias string
     * @return int Element id
     */
    public function getItemIdByAlias($alias)
    {
        $table      = $this->getTable();
        $properties = $table->getProperties();

        if (!in_array('alias', $properties)) {
                return null;
        }

        $table->load(array('alias' => $alias));

        return $table->id;

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
     * Method to get the profile form.
     * The base form is loaded from XML
     * @param   array   $data     An optional array of data for the form to interogate.
     * @param   boolean $loadData True if the form is to load its own data (default case), false if not.
     * @return    JForm    A JForm object on success, false on failure
     * @since    1.6
     */
    public function getForm($data = array(), $loadData = true)
    {
        // Get the form.
        $form = $this->loadForm('com_gatripsys.invoice', 'invoiceform', array(
                        'control'   => 'jform',
                        'load_data' => $loadData
                )
        );

        if (empty($form)) {
                return false;
        }

        return $form;
    }

    /**
     * Method to get the data that should be injected in the form.
     * @return    mixed    The data for the form.
     * @since    1.6
     */
    protected function loadFormData()
    {
        $data = Factory::getApplication()->getUserState('com_gatripsys.edit.invoice.data', array());

        if (empty($data)) {
            $data = $this->getItem();
        }
        

        return $data;
    }

    /**
     * Method to save the form data.
     * @param   array $data The form data
     * @return bool
     * @throws Exception
     * @since 1.6
     */
    public function save($data)
    {
		// get the current date-time based on timezone
		$date = new DateTime();
		$config = Factory::getConfig();
		$date->setTimezone(new DateTimeZone($config->get('offset')));
		$today = date_format($date,'Y-m-d H:i:s');

        $id    = (!empty($data['id'])) ? $data['id'] : (int) $this->getState('invoice.id');
        $data['state'] = (!empty($data['state'])) ? $data['state'] : 1;
        $user  = GatripsysHelper::getSpecificUser();
        $data['created_by'] = (!empty($data['created_by'])) ? $data['created_by'] : $user->id;
        $data['created_date'] = (!empty($data['created_date'])) ? $data['created_date'] : $today;
		$params = ComponentHelper::getParams('com_gatripsys');

        if ($id) {
            // Check the user can edit this item
            $authorised = $user->authorise('core.edit', 'com_gatripsys') || $authorised = $user->authorise('core.edit.own', 'com_gatripsys');
        } else {
            // Check the user can create new items in this section
            $authorised = $user->authorise('core.create', 'com_gatripsys');
        }

        if ($authorised !== true) {
            throw new \Exception(Text::_('JERROR_ALERTNOAUTHOR'), 403);
        }

        $table = $this->getTable();

        if ($table->save($data) === true) {
			/* ---------------------------------------------------------------- */
			// trigger transaction log if required
			$act_log = $params->get('act_log', 0);
			if ($act_log && $table->id) {
				// gather information and log in a new action log record
				GatripsysHelper::recordActionLog($user, $table->id, $data['id']);
			}
			/* ---------------------------------------------------------------- */
			$data['id'] = $table->id;
            $nextinv  = str_pad($table->id, 6, '0', STR_PAD_LEFT);
            Factory::getApplication()->setUserState('com_gatripsys.nextinv.data', $nextinv);
            $data['nextinv'] = $nextinv;

	        // do a dummy setup of test user so only a single record actioned
			$params->set('set_test',1);
	        $params->set('user_id',$data['user_id']);
	        $members = GatripsysHelper::getMembersDetails($params, 0);

            foreach ($members as $u ) {
				Factory::getApplication()->setUserState('com_gatripsys.user.data', $u);
				$attachfile = GainvoiceHelper::createAdHocPDF($data);
				$recipients = array();
				$recipients[] = $u->email;
				$subject = 'Membership Invoice';
				$body = '<p>'.Text::_('COM_GATRIPSYS_MSHIP_INV_BODY').'</p>';
				$sentOK = GanotificationsHelper::sendEmail($recipients, $body, $subject, $attachfile);
     		}

	        $table->load($id);
	        $amt = Factory::getApplication()->getUserState('com_gatripsys.invoice_amt.data');
	        $table->invoice_amt = $amt;
	        $table->store();
	        Factory::getApplication()->setUserState('com_gatripsys.invoice_amt.data',null);

            return $table->id;
        } else {
            return false;
        }
        
    }

    /**
     * Method to delete data
     * @param   int $pk Item primary key
     * @return  int  The id of the deleted item
     * @throws Exception
     * @since 1.6
     */
    public function delete($pk)
    {
        $user = GatripsysHelper::getSpecificUser();

        if (empty($pk)) {
            $pk = (int) $this->getState('invoice.id');
        }

        if ($pk == 0 || $this->getItem($pk) == null) {
            throw new \Exception(Text::_('COM_GATRIPSYS_ITEM_DOESNT_EXIST'), 404);
        }

        if ($user->authorise('core.delete', 'com_gatripsys') !== true) {
            throw new \Exception(Text::_('JERROR_ALERTNOAUTHOR'), 403);
        }

        $table = $this->getTable();
        $table->load($pk);
        $table->state = -2;

        if ($table->store($pk) !== true) {
            throw new \Exception(Text::_('JERROR_FAILED'), 501);
        }

        return $pk;
        
    }

    /**
     * Check if data can be saved
     * @return bool
     */
    public function getCanSave()
    {
        $table = $this->getTable();

        return $table !== false;
    }
    
	/**
	 * Method to mark an item as paid
	 * @param   int $id Element id
	 * @return  bool
	 */
	public function markAsPaid($data)
	{
		$app = Factory::getApplication();
        $params = ComponentHelper::getParams('com_gatripsys');
		$user = GatripsysHelper::getSpecificUser();
		$today = GatripsysHelper::getTodaysDate();

		$data['paid_date'] = !empty($data['paid_date']) ? $data['paid_date'] : $today;

		$table = $this->getTable();
		$table->load($data['id']);
		$table->state = 2;
        if ($data['invoice_amt'] == $table->invoice_amt) {
            $app->enqueueMessage(Text::_("COM_GATRIPSYS_MARKED_AS_PAID"), "message");
        } else {
            $table->invoice_amt = $data['invoice_amt'];
            $app->enqueueMessage(Text::_("COM_GATRIPSYS_MARKED_AS_PAID_CHG"), "notice");
        }
		$table->paid_date = $data['paid_date'];
		$table->modified_by = $user->id;
		$table->modified_date = $today;

		try {
			$table->store();
	    } catch (RuntimeException $e) {
	        $app->enqueueMessage($e->getMessage(),'danger');
		}	
        //if ($table->store() !== true) {
        //    throw new \Exception(Text::_('JERROR_FAILED'), 501);
        //}

		$trip = GatripsysHelper::getTripFromAttend($table->att_id);
		/* ---------------------------------------------------------------- */
		// trigger transaction log if required
		if ($params->get('log_actions', 0)) {
			// gather information and log in a new action log record
			GatripsysHelper::recordActionLog($user, $table->user_id, $table->att_id);
		}
		/* ---------------------------------------------------------------- */
		// trigger transaction creation in finance if necessary
		if ($params->get('incl_finance', 0)) {
			$data['trip_title'] = $trip->title;
			GatripsysHelper::createFinanceTrans($data);
		}
		/* ---------------------------------------------------------------- */
		if ($params->get('mship_single', 0)) {
	        $u = GainvoiceHelper::breakdownNamesFromUserID($table->user_id);
	        $uname = GainvoiceHelper::combineNames($u);
		} else {
	        $u = GatripsysHelper::getSpecificUser($table->user_id);
	        $uname = $u->name;
		}
		// trigger acknowledgement email if necessary
		if ($params->get('email_acknow', 0)) {
			$acknow_txt  = $params->get('pay_ack_text');
			$exclude_bcast  = $params->get('exclude_email', 'noemail');
			$sitename = $app->get('fromname');
	        if (substr($u->email,0,7) != $exclude_bcast) {
	            $recipients = array($u->email);
	            $subject = 'Payment Received - Thank You';
	            $body = '<p>Dear '.$uname.', </p>';
	            $body .= '<p>'.TEXT::sprintf($acknow_txt, $trip->title, $data['invoice_amt']);
	            $body .= '</p><p> </p><p>'.$sitename .'</p>';
	            $attachfile = null;
	            $paid = GaemailHelper::sendEmail($recipients, $body, $subject, $attachfile);
	            $app->enqueueMessage(Text::_("COM_GATRIPSYS_ACKNOWLEDGE_SENT"), "message");
	        } else {
				$app->enqueueMessage(Text::_('No email set for '.$u->name.'.'), 'notice');
    		}
		}
		/* ---------------------------------------------------------------- */
		// check if approvals upon payment is set to yes
		$paid_apprv = $params->get('paid_apprv', 0);
		if ($paid_apprv) {
			// using the invoice id, update the attendance record to be approved
			GatripsysHelper::approveAttendee($table->id);
		}
		/* ---------------------------------------------------------------- */

		return $table->id;

	}

}
