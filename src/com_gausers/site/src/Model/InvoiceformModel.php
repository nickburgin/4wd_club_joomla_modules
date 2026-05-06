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
use Joomla\CMS\MVC\Model\FormModel;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\User\UserFactoryInterace;
use Joomla\Filesystem\File;
use Joomla\Filesystem\Folder;
use Joomla\Filesystem\Path;
use Joomla\CMS\User\User;
use \GlennArkell\Component\Gausers\Administrator\Helper\GausersHelper;
use \GlennArkell\Component\Gausers\Administrator\Helper\GainvoiceHelper;
use \GlennArkell\Component\Gausers\Administrator\Helper\GaemailHelper;


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
        $app = Factory::getApplication('com_gausers');

        // Load state from the request userState on edit or from the passed variable on default
        if (Factory::getApplication()->input->get('layout') == 'edit') {
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
                $user = Factory::getApplication()->getIdentity();
                $id   = $table->id;

                $canEdit = $user->authorise('core.edit', 'com_gausers') || $user->authorise('core.create', 'com_gausers');

                if (!$canEdit && $user->authorise('core.edit.own', 'com_gausers')) {
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
        $form = $this->loadForm('com_gausers.invoice', 'invoiceform', array(
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
        $data = Factory::getApplication()->getUserState('com_gausers.edit.invoice.data', array());

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
		//$date = Factory::getDate();
		//$today = date_format($date,'Y-m-d H:i:s');
		$today = Factory::getDate()->toSql;

        $id    = (!empty($data['id'])) ? $data['id'] : (int) $this->getState('invoice.id');
        $data['state'] = (!empty($data['state'])) ? $data['state'] : 1;
        $user  = Factory::getApplication()->getIdentity();
        $data['created_by'] = (!empty($data['created_by'])) ? $data['created_by'] : $user->id;
        $data['created_date'] = (!empty($data['created_date'])) ? $data['created_date'] : $today;
		$params = ComponentHelper::getParams('com_gausers');

        if ($id) {
            // Check the user can edit this item
            $authorised = $user->authorise('core.edit', 'com_gausers') || $authorised = $user->authorise('core.edit.own', 'com_gausers');
        } else {
            // Check the user can create new items in this section
            $authorised = $user->authorise('core.create', 'com_gausers');
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
				GausersHelper::recordActionLog($user, $table->id, $data['id'], $table->id);
			}
			/* ---------------------------------------------------------------- */
			$data['id'] = $table->id;
            $nextinv  = str_pad($table->id, 6, '0', STR_PAD_LEFT);
            Factory::getApplication()->setUserState('com_gausers.nextinv.data', $nextinv);
            $data['nextinv'] = $nextinv;

	        // do a dummy setup of test user so only a single record actioned
			$params->set('set_test',1);
	        $params->set('user_id',$data['user_id']);
	        $members = GausersHelper::getMembersDetails($params, 0);

            foreach ($members as $u ) {
				Factory::getApplication()->setUserState('com_gausers.user.data', $u);
				$attachfile = GainvoiceHelper::createAdHocPDF($data);
				if (!$attachfile) {
					continue;
				} else {
					$recipients = array();
					$recipients[] = $u->email;
					$subject = Text::_('COM_GAUSERS_INVOICE_EMAIL_SUBJECT');
					$body = Text::_('COM_GAUSERS_INVOICE_EMAIL_BODY');
					$sentOK = GaemailHelper::sendEmail($recipients, $body, $subject, $attachfile);
				}
     		}

	        $table->load($id);
	        $amt = Factory::getApplication()->getUserState('com_gausers.invoice_amt.data');
	        $table->invoice_amt = $amt;
	        if (!$attachfile) {
				$table->state = -2;
			}
	        $table->store();
	        Factory::getApplication()->setUserState('com_gausers.invoice_amt.data',null);

            return $attachfile;
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
        $user = Factory::getApplication()->getIdentity();

        
        if (empty($pk)) {
            $pk = (int) $this->getState('invoice.id');
        }

        if ($pk == 0 || $this->getItem($pk) == null) {
            throw new \Exception(Text::_('COM_GAUSERS_ITEM_DOESNT_EXIST'), 404);
        }

        if ($user->authorise('core.delete', 'com_gausers') !== true) {
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
		$params = ComponentHelper::getParams('com_gausers');
		$user = Factory::getApplication()->getIdentity();

		$today = Factory::getDate()->toSql();

		$data['paid_date'] = isset($data['paid_date']) && !empty($data['paid_date']) ? $data['paid_date'] : $today;

		$table = $this->getTable();
		$table->load($data['id']);
		$table->state = 2;
        if ($data['invoice_amt'] != $table->invoice_amt) {
            $table->invoice_amt = $data['invoice_amt'];
            Factory::getApplication()->enqueueMessage(Text::_("COM_GAUSERS_MARKED_AS_PAID_CHG"), "notice");
        }
		$table->paid_date = $data['paid_date'];

        if ($table->store() !== true) {
            throw new \Exception(Text::_('JERROR_FAILED'), 501);
        }

		/* ---------------------------------------------------------------- */
		// trigger transaction log if required
		$log_actions = $params->get('log_actions', 0);
		if ($log_actions) {
			// gather information and log in a new action log record
			GausersHelper::recordActionLog($user, $table->user_id, 'invoice', $data['id']);
		}
		/* ---------------------------------------------------------------- */
		// trigger transaction creation in finance if necessary
		$incl_finance = $params->get('incl_finance', 0);
		if ($incl_finance) {
			$data['mship_id'] = $table->mship_id; // identifies single/family etc
            GausersHelper::createFinanceTrans($data);
		}
		/* ---------------------------------------------------------------- */
        $temp_mship  = $params->get('temp_mship', 0);
		if ($table->mship_id == $temp_mship) {
            Factory::getApplication()->enqueueMessage(Text::_("COM_GAUSERS_TEMPMBR_BLOCK"), "message");
            return $table->id;
        } else {
            // get the user details for the email
            $u = GausersHelper::getSpecificUser($table->user_id);

    		if ($u->block && !$tempMbr) {
                // set user record to be unblocked
                $unblockedOK = GausersHelper::unblockUser($table->user_id, 0);
                if (!$unblockedOK) {
    				Factory::getApplication()->enqueueMessage(Text::_("COM_GAUSERS_UNBLOCK_FAILED"), "warning");
    			} else {
    				Factory::getApplication()->enqueueMessage(Text::_("COM_GAUSERS_UNBLOCK_SUCCESS"), "message");
    			}
            }
        }
		/* ---------------------------------------------------------------- */
		// trigger acknowledgement email if necessary
		$email_acknow = $params->get('email_acknow', 0);
		if ($email_acknow) {
			$acknow_txt  = $params->get('pay_ack_text');
			$exclude_bcast  = $params->get('exclude_email_pref');
			$sitename = Factory::getApplication()->get('fromname');
	        if (substr($u->email,0,7) != $exclude_bcast) {
	            $subject = Text::_('COM_GAUSERS_INVOICE_EMAIL_PAYSUBJECT');
	            $body = Text::sprintf('COM_GAUSERS_INVOICE_EMAIL_SALUTATION',$u->name);
	            $body .= Text::sprintf('COM_GAUSERS_INVOICE_EMAIL_PAYBODY', $acknow_txt);
	            $body .= Text::sprintf('COM_GAUSERS_INVOICE_EMAIL_SIGNOFF',$sitename);
	            $attachfile = null;
	            $paid = GaemailHelper::sendEmail(array($u->email), $body, $subject, $attachfile);
	            Factory::getApplication()->enqueueMessage(Text::_("COM_GAUSERS_ACKNOWLEDGE_SENT"), "message");
	        } else {
				Factory::getApplication()->enqueueMessage(Text::sprintf('COM_GAUSERS_ACKNOWLEDGE_NOTSENT',$u->name), 'notice');
    		}
		}
		/* ---------------------------------------------------------------- */

		return $table->id;

	}

}
