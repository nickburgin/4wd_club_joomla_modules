<?php
/**
 * @version    3.3.1
 * @package    Com_Gacalevents
 * @author     Glenn Arkell <glenn@glennarkell.com.au>
 * @copyright  2021 Glenn Arkell
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace GlennArkell\Component\Gacalevents\Site\Model;

// No direct access.
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Table\Table;
use Joomla\CMS\MVC\Model\ItemModel;
use Joomla\CMS\Helper\TagsHelper;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Component\ComponentHelper;
use \GlennArkell\Component\Gacalevents\Administrator\Helper\GacaleventsHelper;
use \GlennArkell\Component\Gacalevents\Administrator\Helper\GacommunicationsHelper;
use \GlennArkell\Component\Gacalevents\Administrator\Helper\GanamesHelper;

/**
 * Item model.
 * @since  1.6
 */
class EventModel extends ItemModel
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
		$app  = Factory::getApplication('com_gacalevents');
		$user = Factory::getApplication()->getIdentity();

		// Check published state
		if ((!$user->authorise('core.edit.state', 'com_gacalevents')) && (!$user->authorise('core.edit', 'com_gacalevents'))) {
			$this->setState('filter.published', 1);
			$this->setState('filter.archived', 2);
		}

		// Load state from the request userState on edit or from the passed variable on default
		if (Factory::getApplication()->input->get('layout') == 'edit') {
			$id = Factory::getApplication()->getUserState('com_gacalevents.edit.event.id');
		} else {
			$id = Factory::getApplication()->input->get('id');
			Factory::getApplication()->setUserState('com_gacalevents.edit.event.id', $id);
		}

		$this->setState('event.id', $id);

		// Load the parameters.
		$params       = $app->getParams();
		$params_array = $params->toArray();

		if (isset($params_array['item_id'])) {
			$this->setState('event.id', $params_array['item_id']);
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
                    $id = $this->getState('event.id');
                }

                // Get a level row instance.
                $table = $this->getTable();

                // Attempt to load the row.
                if ($table->load($id)) {

                    // Check published state.
                    if ($published = $this->getState('filter.published')) {
                        if (isset($table->state) && $table->state != $published) {
                            throw new \Exception(Text::_('COM_GACALEVENTS_ITEM_NOT_LOADED'), 403);
                        }
                    }

                    // Convert the Table to a clean Object.
                    $properties  = $table->getProperties(1);
                    $this->_item = ArrayHelper::toObject($properties, 'stdClass');

                }

                if (empty($this->_item)) {
					throw new \Exception(Text::_('COM_GACALEVENTS_ITEM_NOT_LOADED'), 404);
				}
            }

			if (isset($this->_item->created_by)) {
				$this->_item->created_by_name = GacaleventsHelper::getSpecificUser($this->_item->created_by)->name;
			}
	
			if (isset($this->_item->modified_by)) {
				$this->_item->modified_by_name = GacaleventsHelper::getSpecificUser($this->_item->modified_by)->name;
			}
	
			if (isset($this->_item->cat_id)) {
				$this->_item->cat_id_name = GacaleventsHelper::getCategoryName($this->_item->cat_id);
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
	public function getTable($type = 'Event', $prefix = 'Administrator', $config = array())
	{
		return parent::getTable($type, $prefix, $config);
	}

	public function getAttTable($type = 'Attendee', $prefix = 'Administrator', $config = array())
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
            $aliasKey   = $this->getAliasFieldNameByView('event');
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
		$id = (!empty($id)) ? $id : (int) $this->getState('event.id');
                
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
		$id = (!empty($id)) ? $id : (int) $this->getState('event.id');

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
	 * Method to repeat an item
	 * @param   int $id Element id
	 * @return  bool
	 */
	public function repeatEvent($id)
	{
		//$table = $this->getTable();
        $params = ComponentHelper::getParams('com_gacalevents');
        $repeatEvent = $params->get('repeat_event', 0);
        $repeatQty = $params->get('repeat_qty', 1);
        $repeatNumber = $params->get('repeat_setting', 0);
        $repeatType = $params->get('repeat_type', 'DAYS');

        $record_id = $id;
        for ($x = 1; $x <= $repeatQty; $x++) {
            $table = $this->getTable();
            $table->load($record_id);
			$fdate = new \DateTime($table->depart_date);
			$tdate = new \DateTime($table->return_date);
            $table->depart_date = \date_format($fdate->modify('+'.$repeatNumber.' '.$repeatType),'Y-m-d H:i:s');
            $table->return_date = \date_format($tdate->modify('+'.$repeatNumber.' '.$repeatType),'Y-m-d H:i:s');
            $table->id = 0;
            $table->store();
            $record_id = $table->id;
        }

        return true;
	}

	/**
	 * Method to delete an attendance record
	 * @param   int $id Element id
	 * @return  bool
	 */
	public function deleteAtt($id)
	{
		//$id = GacaleventsHelper::checkAttendee($event_id, $user_id)->id;
		$table = $this->getAttTable();

		$table->load($id);
		$table->state = -2;

		return $table->store();

	}

	/**
	 * Method to add an attendee
	 * @param   int $event_id Event id
	 * @param   int $attendee User id
	 * @return  bool
	 *
	 * Action status to know what to do with records
	 * 0 = apology for the event or cancelled attendance and submit apology for the event
	 * 1 = attending event or cancel an apology and set as attending
	 * -2 = trash the record and thus not have an attendance or apology
	 */
	public function registerAttendee($event_id = 0, $attendee = 0, $status = 0)
	{
		$params = ComponentHelper::getParams('com_gacalevents');
		$partnerShip  = $params->get( 'partner_mship' );
        $act_log = $params->get('act_log', 0);
		$psuf  = $params->get( 'profile_suffix', 'b4wdc' );
		$ppart  = $params->get( 'profile_partner', 'partner' );
		$locProfKey = 'profile'.$psuf.'.'.$ppart;
		$user  = Factory::getApplication()->getIdentity();

		// get the current date-time based on timezone
		$today = Factory::getDate()->toSql();
		$attRecord = GacaleventsHelper::checkAttendee($event_id, $attendee);

		$table = $this->getAttTable();

        // if an attendance record exists and is set then change the status
		if (isset($attRecord) && $attRecord->id > 0) {
			$table->load($attRecord->id);
			$table->state = $status;
	
			return $table->store();
		} else {

    		$data = array();
    		$data['event'] = $event_id;
            $data['attendee'] = $attendee;
            $data['created_date'] = $today;
            $data['created_by'] = $user->id;
    		$data['att_cat'] = 0;
            $data['state'] = $status;
    
    		if ($attendee) {
    			$member = GanamesHelper::breakdownNamesFromUserID($attendee, $locProfKey);
    			if ($partnerShip) {
    				$data['pub_name'] = GanamesHelper::combineNames($member);
    				$data['qty_att'] = 2;
    			} else {
    				$data['pub_name'] = $member->name;
    				$data['qty_att'] = 1;
    			}
    
    		    $name = $data['pub_name'];
    		    $last_name = (strpos($name, ' ') === false) ? '' : preg_replace('#.*\s([\w-]*)$#', '$1', $name);
    		    $first_name = trim( preg_replace('#'.$last_name.'#', '', $name ) );
    
    			$data['pub_fname'] = $first_name;
    			$data['pub_sname'] = $last_name;
    			$data['pub_partner'] = $member->partner;
    		}
    
    		if ($table->save($data) === true) {
    			Factory::getApplication()->enqueueMessage(Text::_('COM_GACALEVENTS_ATTEND_REGO_ATTEND_SUCC_MSG'), 'notice');
    			/* ---------------------------------------------------------------- */
    			if ($act_log && $table->id) {
    				// gather information and log in a new action log record
    				GacaleventsHelper::recordActionLog($member, $event_id, $status);
    			}
    			/* ---------------------------------------------------------------- */
    			return $table->id;
    		} else {
    			Factory::getApplication()->enqueueMessage(Text::_('COM_GACALEVENTS_ATTEND_REGO_ATTEND_FAIL_MSG'), 'warning');
    			return false;
    		}
		}
	}

	/**
	 * Method to generate an email about the event and email to all users
	 * @param   int $id Event id
	 * @return  bool
	 */
	public function sendReminder($id)
	{
		$event = GacommunicationsHelper::sendReminder($id);

		return $event;
	}

    public function extractAttendees($event_id)
    {
        $user  = Factory::getApplication()->getIdentity();
		// get the current date-time based on timezone
		$date = new DateTime();
		$config = Factory::getConfig();
		$date->setTimezone(new DateTimeZone($config->get('offset')));
		$today = date_format($date,'Ymd-His');

        $event = GacaleventsHelper::getEvent($event_id);
        $attendees = GacaleventsHelper::getAttendees($event_id);

		// now load the records into xml for sending to requested user
		$output = 'images/events/attendees-'.$event_id.'-'.$today.'.xls';
		$output = Path::clean( JPATH_SITE . '/' . $output );
		if (is_array($attendees)) {
			// create xml file of data
            header('Content-type: text/xml; charset=UTF-8');
            $xw = new XMLWriter();
            $xw->openUri($output);
            $xw->startDocument('1.0');
            $xw->startElement('xml');

			foreach ($attendees as $att) {
				$att_name  = $att->pub_name;
				$att_email  = $att->pub_email;
				$att_phone  = $att->pub_phone;
				$att_from  = $att->pub_from;

				$xw->startElement('attendees');
				if ($event->formal_event) {
					$xw->startElement('no');
					$xw->text($att->qty_att);
					$xw->endElement();
					$xw->startElement('category');
					$xw->text($att_cat);
					$xw->endElement();
					$xw->startElement('title');
					$xw->text($att->pub_title);
					$xw->endElement();
					$xw->startElement('first_name');
					$xw->text($att->pub_fname);
					$xw->endElement();
					$xw->startElement('surname');
					$xw->text($att->pub_sname);
					$xw->endElement();
					$xw->startElement('post_noms');
					$xw->text(trim($post_noms));
					$xw->endElement();
					$xw->startElement('partner');
					$xw->text($att->pub_partner);
					$xw->endElement();
					$xw->startElement('position');
					$xw->text($att->position);
					$xw->endElement();
					$xw->startElement('organisation');
					$xw->text($att->pub_from);
					$xw->endElement();
					$xw->startElement('address1');
					$xw->text($att->pub_address1);
					$xw->endElement();
					$xw->startElement('address2');
					$xw->text($att->pub_address2);
					$xw->endElement();
					$xw->startElement('address3');
					$xw->text($att->pub_address3);
					$xw->endElement();
					$xw->startElement('suburb');
					$xw->text($att->pub_suburb);
					$xw->endElement();
					$xw->startElement('state');
					$xw->text($att->pub_state);
					$xw->endElement();
					$xw->startElement('pcode');
					$xw->text($att->pub_pcode);
					$xw->endElement();
				} else {
					$xw->startElement('name');
					$xw->text($att_name);
					$xw->endElement();
					$xw->startElement('email');
					$xw->text($att_email);
					$xw->endElement();
					$xw->startElement('phone');
					$xw->text($att_phone);
					$xw->endElement();
					$xw->startElement('from');
					$xw->text($att_from);
					$xw->endElement();
					$xw->startElement('reg_date');
					$xw->text($att->created_date);
					$xw->endElement();
					$xw->startElement('event');
					$xw->text($event->title);
					$xw->endElement();
					$xw->startElement('event_date');
					$xw->text($event->depart_date);
					$xw->endElement();
					$xw->startElement('att_number');
					$xw->text($event->qty_att);
					$xw->endElement();
					$xw->startElement('comment');
					$xw->text($att->comment);
					$xw->endElement();
				}
                $xw->endElement();
			}
            $xw->endElement();
            $xw->endDocument();
			$xw->flush();
		} else {
			Factory::getApplication()->enqueueMessage(Text::_('COM_GACALEVENTS_EXTRACT_FAILED'), 'danger');
		}

		$subject = 'Extract of Attendees - '.$event->title;
		$body = 'Attached';
		$recipients = array($user->email);
		$sent = GacaleventsHelper::processEmail($output, $subject, $recipients, $body);

		if ($sent) {
			Factory::getApplication()->enqueueMessage(Text::_('COM_GACALEVENTS_EXTRACT_SUCCESSFULLY'), 'message');
		}

    }

}
