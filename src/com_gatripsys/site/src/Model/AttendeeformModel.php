<?php
/**
 * @version    5.1.0
 * @package    com_gatripsys
 * @author     Glenn Arkell <glenn@glennarkell.com.au>
 * @copyright  2021 Glenn Arkell
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace GlennArkell\Component\Gatripsys\Site\Model;

// No direct access.
defined('_JEXEC') or die;

use \Joomla\CMS\Factory;
use \Joomla\Utilities\ArrayHelper;
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\Table\Table;
use \Joomla\CMS\MVC\Model\FormModel;
use \Joomla\CMS\Component\ComponentHelper;
use \Joomla\CMS\Date\Date;
use \Joomla\Filesystem\File;
use \Joomla\Filesystem\Folder;
use \Joomla\Filesystem\Path;
use \GlennArkell\Component\Gatripsys\Administrator\Helper\GatripsysHelper;
use \GlennArkell\Component\Gatripsys\Administrator\Helper\GanotificationsHelper;
use \GlennArkell\Component\Gatripsys\Administrator\Helper\GainvoiceHelper;
use \GlennArkell\Component\Gatripsys\Administrator\Helper\GaemailHelper;

/**
 * Form model.
 * @since  1.6
 */
class AttendeeformModel extends FormModel
{
    private $item = null;

    /**
     * Method to auto-populate the model state.
     * Note. Calling getState in this method will result in recursion.
     * @return void
     * @since  1.6
     * @throws Exception
     */
    protected function populateState()
    {
        $app = Factory::getApplication('com_gatripsys');

        // Load state from the request userState on edit or from the passed variable on default
        if ($app->input->get('layout') == 'edit') {
            $id = $app->getUserState('com_gatripsys.edit.attendee.id');
			$trip_id = $app->getUserState('com_gatripsys.edit.trip.id', 0);
        } else {
            $id = $app->input->get('id');
            $app->setUserState('com_gatripsys.edit.attendee.id', $id);
			$trip_id = $app->input->get('trip_id');
			$app->setUserState('com_gatripsys.edit.trip.id', $trip_id);
        }

        $this->setState('attendee.id', $id);
		$this->setState('trip.id', $trip_id);

        // Load the parameters.
        $params       = $app->getParams();
        $params_array = $params->toArray();

        if (isset($params_array['item_id'])) {
                $this->setState('attendee.id', $params_array['item_id']);
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
                $id = $this->getState('attendee.id');
                $trip_id = $this->getState('trip.id');
            }

            // Get a level row instance.
            $table = $this->getTable();

            if ($table !== false && $table->load($id) && !empty($table->id)) {
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

			if (isset($this->item->trip_id)) {
				$this->item->leader = GatripsysHelper::getTripLeader($this->item->trip_id);
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
    public function getTable($type = 'Attendee', $prefix = 'Administrator', $config = array())
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
        $id = $table->id;

        return $id;
        
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
        $id = (!empty($id)) ? $id : (int) $this->getState('attendee.id');
        
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
        $id = (!empty($id)) ? $id : (int) $this->getState('attendee.id');
        
        if ($id) {
            // Initialise the table
            $table = $this->getTable();

            // Get the current user object.
            $user = GatripsysHelper::getSpecificUser();

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
     * Method to get the form.
     * The base form is loaded from XML
     * @param   array   $data     An optional array of data for the form to interogate.
     * @param   boolean $loadData True if the form is to load its own data (default case), false if not.
     * @return  Form    A Form object on success, false on failure
     * @since   1.6
     */
    public function getForm($data = array(), $loadData = true)
    {
        // Get the form.
        $form = $this->loadForm('com_gatripsys.attendee', 'attendeeform', array(
                    'control'   => 'jform',
                    'load_data' => $loadData ) );

        if (empty($form)) {
                return false;
        }

        return $form;
    }

    /**
     * Method to get the data that should be injected in the form.
     * @return    array  The default data is an empty array.
     * @since    1.6
     */
    protected function loadFormData()
    {
        $data = Factory::getApplication()->getUserState('com_gatripsys.edit.attendee.data', array());

        if (empty($data)) {
            $data = $this->getItem();
        }

        if ($data) {
            return $data;
        }

        return array();
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
        $id    = (!empty($data['id'])) ? $data['id'] : (int) $this->getState('attendee.id');
        $user  = GatripsysHelper::getSpecificUser();
        $today = GatripsysHelper::getTodaysDate();

        if ($id) {
            // Check the user can edit this item
            $authorised = $user->authorise('core.edit', 'com_gatripsys') || $authorised = $user->authorise('core.edit.own', 'com_gatripsys');
			$data['modified_date'] = $today;
			$data['modified_by'] = $user->id;
        } else {
            // Check the user can create new items in this section
            $authorised = $user->authorise('core.create', 'com_gatripsys');
			$data['created_by'] = $user->id;
			$data['created_date'] = $today;
        }

        if ($authorised !== true) {
            throw new \Exception(Text::_('JERROR_ALERTNOAUTHOR'), 403);
        }

		$params = ComponentHelper::getParams('com_gatripsys');

		if ($params->get('auto_book_apprv', 0)) {
			$data['state'] = 1;   // approved status
			$data['approved_by'] = $user->id;
			$tmpl = 'bookaprv';
		} else {
			$data['state'] = 0;
			$tmpl = 'booknew';
		}

        $table = $this->getTable();

        if ($table->save($data) === true) {
            
            $data['new_booking'] = !$id ? 1 : 0;
            $data['id'] = $table->id;
            $data['booked_by'] = $user->id;
            $data['booking_status'] = $data['state'] == 0 ? 'Pending' : 'Approved';
            //$data['user_id'] = $user->id;

			// check if invoice required and create it if necessary
			$invOK = GainvoiceHelper::checkInvoiceReq($user, $data, $table->id, $params);

			// test for sending notification to leader
			if ($params->get('notif_leader', 1)) {
				// notify leader of booking
                if ($data['new_booking']) {
    				GanotificationsHelper::notifyTripLeader($data, $tmpl, $id);
				}
			}
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
        $today = GatripsysHelper::getTodaysDate();

        if (empty($pk)) {
            $pk = (int) $this->getState('attendee.id');
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
		$table->modified_by = $user->id;
		$table->modified_date = $today;

		Factory::getApplication()->setUserState('com_gatripsys.tripid.data',$table->trip_id);

		if ($table->store() === true) {
			return $pk;
		} else {
			return false;
		}

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
	 * Method to send all attendees an email
	 * @param   integer  $trip_id  Id of trip
	 * @return bool true success, if not false
	 * @throws Exception
	 */
	public function sendEmail($data)
	{
        $user  = GatripsysHelper::getSpecificUser();

        if ($data['trip_id']) {
            $authorised = $user->authorise('core.edit', 'com_gatripsys') || $authorised = $user->authorise('core.edit.own', 'com_gatripsys');
        } else {
            throw new \Exception(Text::_('JERROR_ALERTNOAUTHOR'), 403);
        }

        $recipients = array();
		$params = ComponentHelper::getParams('com_gatripsys');
		$reply_sender  = $params->get( 'reply_sender', 0 );
		$exclude_email  = $params->get( 'exclude_email', 0 );
		$email_admin  = $params->get( 'email_admin', 0 );
		$send_admin  = $params->get( 'send_admin', 0 );
		$exLen = strlen($exclude_email);
		if ($email_admin && $send_admin > '') {
			$recipients[] = $send_admin;
		}

        // get all attendees for the trip then cycle through them to create the recipients list
        $attendees = Factory::getApplication()->getUserState('com_gatripsys.trip.attendees');
        Factory::getApplication()->setUserState('com_gatripsys.trip.attendees', null);

        foreach ($attendees AS $att) {
			if ($att->state) {
				if (substr($att->attend_email,0,$exLen) != $exclude_email) {
					$recipients[] = $att->attend_email;
				}
				if ($att->inc_altemail && str_replace('"','',$att->altemail) > '' &&  substr($att->altemail,0,$exLen) != $exclude_email) {
					$recipients[] = $att->altemail;
				}
			}
		}

		$sendOK = GaemailHelper::sendEmail($recipients, $data['news_detail'], $data['news_subject'], 0, 1, $reply_sender);

        if ($sendOK) {
            return true;
        } else {
            return false;
        }
	}
}
