<?php
/**
 * @version    5.3.0
 * @package    com_gatripsys
 * @author     Glenn Arkell <glenn@glennarkell.com.au>
 * @copyright  2021 Glenn Arkell
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace GlennArkell\Component\Gatripsys\Site\Model;

// No direct access.
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Table\Table;
use Joomla\CMS\MVC\Model\ItemModel;
use Joomla\CMS\Helper\TagsHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\User\UserHelper;
use Joomla\CMS\Date\Date;
use Joomla\Filesystem\File;
use Joomla\Filesystem\Path;
use \GlennArkell\Component\Gatripsys\Administrator\Helper\GaemailHelper;
use \GlennArkell\Component\Gatripsys\Administrator\Helper\GatripsysHelper;
use \GlennArkell\Component\Gatripsys\Administrator\Helper\GainvoiceHelper;
use \GlennArkell\Component\Gatripsys\Administrator\Helper\GanotificationsHelper;

/**
 * Item model.
 * @since  1.6
 */
class AttendeeModel extends ItemModel
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
		$app  = Factory::getApplication('com_gatripsys');
		$user = GatripsysHelper::getSpecificUser();

		// Check published state
		if ((!$user->authorise('core.edit.state', 'com_gatripsys')) && (!$user->authorise('core.edit', 'com_gatripsys'))) {
			$this->setState('filter.published', 1);
			$this->setState('filter.archived', 2);
		}

		// Load state from the request userState on edit or from the passed variable on default
		if (Factory::getApplication()->input->get('layout') == 'edit') {
			$id = Factory::getApplication()->getUserState('com_gatripsys.edit.attendee.id');
		} else {
			$id = Factory::getApplication()->input->get('id');
			Factory::getApplication()->setUserState('com_gatripsys.edit.attendee.id', $id);
		}

		$this->setState('attendee.id', $id);

		// Load the parameters.
		$params       = $app->getParams();
		$params_array = $params->toArray();

		if (isset($params_array['item_id'])) {
			$this->setState('attendee.id', $params_array['item_id']);
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
                $id = $this->getState('attendee.id');
            }

            // Get a level row instance.
            $table = $this->getTable();

            // Attempt to load the row.
            if ($table->load($id)) {

                // Check published state.
                if ($published = $this->getState('filter.published')) {
                    if (isset($table->state) && $table->state != $published) {
                        throw new \Exception(Text::_('COM_GATRIPSYS_ITEM_NOT_LOADED'), 403);
                    }
                }

                // Convert the Table to a clean Object.
                $properties  = $table->getProperties(1);
                $this->_item = ArrayHelper::toObject($properties, 'stdClass');

            }

            if (empty($this->_item)) {
				throw new \Exception(Text::_('COM_GATRIPSYS_ITEM_NOT_LOADED'), 404);
			}
        }

		if (isset($this->_item->created_by)) {
			$this->_item->created_by_name = GatripsysHelper::getSpecificUser($this->_item->created_by)->name;
		}
		if (isset($this->_item->modified_by)) {
			$this->_item->modified_by_name = GatripsysHelper::getSpecificUser($this->_item->modified_by)->name;
		}
		if ($this->_item->approved_by ) {
			$this->_item->approved_by_name = GatripsysHelper::getSpecificUser($this->_item->approved_by)->name;
		} else {
			$this->_item->approved_by_name = 'Not Approved';
		}
		$this->_item->user_id_name = GatripsysHelper::getSpecificUser($this->_item->user_id)->name;

        return $this->_item;
    }

	/**
	 * Get an instance of Table class
	 * @param   string $type   Name of the Table class to get an instance of.
	 * @param   string $prefix Prefix for the table class name. Optional.
	 * @param   array  $config Array of configuration values for the Table object. Optional.
	 * @return  Table|bool Table if success, false on failure.
	 */
	public function getTable($type = 'Attendee', $prefix = 'Administrator', $config = array())
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
            $aliasKey   = $this->getAliasFieldNameByView('attendee');
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
	 * Publish the element
	 * @param   int $id    Item id
	 * @param   int $state Publish state
	 * @return  boolean
	 */
	public function publish($id, $state)
	{
		$table = $this->getTable();
		$user = GatripsysHelper::getSpecificUser();
        $today = GatripsysHelper::getTodaysDate();
		$params = ComponentHelper::getParams('com_gatripsys');
		$refund_notif = $params->get('refund_notif');
		$notif_leader = $params->get('notif_leader', 0);

		$table->load($id);
		$table->approved_by = $user->id;
		$table->state = $state;
		$table->modified_by = $user->id;
		$table->modified_date = $today;

		try {
			$table->store();

            $u  = GatripsysHelper::getSpecificUser($table->user_id);
            $invExist = GainvoiceHelper::getInvoiceForAttendee($id);

			$data = array();
			$data['id'] = $table->id;
			$data['trip_id'] = $table->trip_id;
			$data['user_id'] = $table->user_id;
			$data['in_party'] = $table->in_party;
			$data['state'] = $table->state;

            if ($state == 1) {
				// check if invoice required and create it if necessary
				if (!$invExist) {
                    $invOK = GainvoiceHelper::checkInvoiceReq($user, $data, $id, $params);
    				if (!$invOK) {
                        Factory::getApplication()->enqueueMessage('Failed to generate invoice for '.$u->name,'danger');
                    } else {
                        Factory::getApplication()->enqueueMessage('Approved '.$u->name,'message');
                    }
				} else {
                    Factory::getApplication()->enqueueMessage('Approved and Invoice already exists for '.$u->name,'message');
                }
                GanotificationsHelper::notifyMember($table->trip_id, $table->user_id, 'bookaprv' );
			} elseif ($state == -2) {
				if ($invExist) {
					GainvoiceHelper::cancelInvoice($invExist->id);
					GaemailHelper::sendEmail(array($invExist->att_name=>$invExist->att_email), 'Please contact Trip Leader', 'Trip Booking Rejected', 0,0,0);
				}
				Factory::getApplication()->enqueueMessage(Text::_('COM_GATRIPSYS_ITEM_ATTEND_REJECTED'),'warning');
			} elseif ($state == 2) {
				if ($invExist) {
					GainvoiceHelper::cancelInvoice($invExist->id);
				}
				Factory::getApplication()->enqueueMessage(Text::_('COM_GATRIPSYS_ITEM_ATTEND_CANCEL'),'message');
    			if ($notif_leader) {
                    $data['new_booking'] = 0;
                    GanotificationsHelper::notifyTripLeader($data, 'bookcan', $table->id );
                    Factory::getApplication()->enqueueMessage(Text::_('COM_GATRIPSYS_LEADER_NOTIFIED'),'message');
                }
			}

			return $id;

	    } catch (RuntimeException $e) {
	        Factory::getApplication()->enqueueMessage($e->getMessage(),'danger');
	        return false;
	    }

	}

	/**
	 * Method to approveAll attendees
	 * @param   int $id Element id
	 * @return  bool
	 */
	public function approveAll($trip_id)
	{
        $cntr = 0;
        $perscntr = 0;
        $db		= Factory::getContainer()->get('DatabaseDriver');
		$query	= $db->getQuery(true);
        $query->clear();
		$query->select(' a.*, t.max_no, t.max_people');
		$query->from(' #__gatripsys_attendees as a ');
		$query->join('LEFT', '#__gatripsys_trips as t ON t.id = '.(int)$trip_id);
		$query->where(' a.trip_id = '.(int)$trip_id );
		$query->where(' a.state IN (0,1) ' );
		$query->order(' a.state DESC, a.id ASC ' );
		$db->setQuery((string)$query);

	    try {
	        $atts = $db->loadObjectList();
	    } catch (RuntimeException $e) {
	        Factory::getApplication()->enqueueMessage($e->getMessage(),'danger');
	        return false;
	    }
	    
	    foreach ($atts AS $att) {
            $cntr++;
            $perscntr = $perscntr + $att->in_party;
            if ($att->state == 1) {
                // no need to update but count this record for waitlist calcs
            } else {
                if (($att->max_no && $cntr > $att->max_no) || ($att->max_people && $perscntr > $att->max_people)) {
                    // don't publish because this record on waitlist
                } else {
                    $pubOK = $this->publish($att->id, 1);
                }
            }
		}

		return true;

	}
	
	/**
	 * Method to unapproveAll attendees
	 * @param   int $id Element id
	 * @return  bool
	 */
	public function unapproveAll($trip_id)
	{
        $db		= Factory::getContainer()->get('DatabaseDriver');
		$query	= $db->getQuery(true);
        $query->clear();
		$query->select(' * ');
		$query->from(' #__gatripsys_attendees ');
		$query->where(' trip_id = '.(int)$trip_id );
		$query->where(' state = 1 ' );
		$db->setQuery((string)$query);

	    try {
	        $atts = $db->loadObjectList();
	    } catch (RuntimeException $e) {
	        Factory::getApplication()->enqueueMessage($e->getMessage(),'danger');
	        return false;
	    }
	    
	    foreach ($atts AS $att) {
            $pubOK = $this->publish($att->id, 0);
		}
		
		return true;

	}
	
}
