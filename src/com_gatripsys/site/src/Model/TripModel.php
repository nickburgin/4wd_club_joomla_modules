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
use \GlennArkell\Component\Gatripsys\Administrator\Helper\GatripsysHelper;
use \GlennArkell\Component\Gatripsys\Administrator\Helper\GatriprptHelper;
use \GlennArkell\Component\Gatripsys\Administrator\Helper\GanotificationsHelper;

/**
 * Item model.
 * @since  1.6
 */
class TripModel extends ItemModel
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
			$id = Factory::getApplication()->getUserState('com_gatripsys.edit.trip.id');
		} else {
			$id = Factory::getApplication()->input->get('id');
			Factory::getApplication()->setUserState('com_gatripsys.edit.trip.id', $id);
		}

		$this->setState('trip.id', $id);

		// Load the parameters.
		$params       = $app->getParams();
		$params_array = $params->toArray();

		if (isset($params_array['item_id'])) {
			$this->setState('trip.id', $params_array['item_id']);
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
                $id = $this->getState('trip.id');
            }

            // Get a level row instance.
            $table = $this->getTable();

            // Attempt to load the row.
            if ($table->load($id)) {

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

		if (isset($this->_item->regby_date) && $this->_item->regby_date > '') {
			$jdate = new Date($this->_item->regby_date);
			$this->_item->regby_date_disp = $jdate->format(Text::_('DATE_FORMAT_LC3'));
		} else { $this->_item->regby_date_disp = 'TBA'; }
		if (isset($this->_item->dept_date) && $this->_item->dept_date > '') {
			$jdate = new Date($this->_item->dept_date);
			$this->_item->dept_date_disp = $jdate->format(Text::_('DATE_FORMAT_LC3'));
		} else { $this->_item->dept_date_disp = 'TBA'; }
		if (isset($this->_item->ret_date) && $this->_item->ret_date > '') {
			$jdate = new Date($this->_item->ret_date);
			$this->_item->ret_date_disp = $jdate->format(Text::_('DATE_FORMAT_LC3'));
		} else { $this->_item->ret_date_disp = 'TBA'; }

		if ($this->_item->rating) {
			$this->_item->rating_name = GatripsysHelper::getCategory($this->_item->rating)->title;
			$this->_item->cat_params = GatripsysHelper::getCategory($this->_item->rating)->params;
        } else {
			$this->_item->rating_name = '';
			$this->_item->cat_params = '';
   		}
		if ($this->_item->trip_type) {
			$this->_item->trip_type_name = GatripsysHelper::getCategory($this->_item->trip_type)->title;
        } else {
			$this->_item->trip_type_name = '';
   		}
		if ($this->_item->suited_for) {
			$this->_item->suited_for_name = GatripsysHelper::getCategory($this->_item->suited_for)->title;
        } else {
			$this->_item->suited_for_name = '';
   		}
		if ($this->_item->insurance_cat) {
			$this->_item->insurance_cat_name = GatripsysHelper::getCategory($this->_item->insurance_cat)->title;
        } else {
			$this->_item->insurance_cat_name = '';
   		}
		if (isset($this->_item->leader) && ($this->_item->leader == "TBA" || $this->_item->leader == 0)) {
			$this->_item->leader_name = 'TBA';
			$this->_item->leader_phone = '';
		} elseif (isset($this->_item->leader) && $this->_item->leader > 0) {
			$leader = GatripsysHelper::getSpecificUser($this->_item->leader);
			$this->_item->leader_name = $leader->name;
			$lp = UserHelper::getProfile( $leader->id );
			if (isset($lp->profile['phone'])) {
				$this->_item->leader_phone = $lp->profile['phone'];
			} else {
				$this->_item->leader_phone = 'No Phone';
			}
        } else {
			$this->_item->leader_name = '';
			$this->_item->leader_phone = '';
   		}

		if (isset($this->_item->trip_tec) && $this->_item->trip_tec > 0) {
			$this->_item->tec_name = GatripsysHelper::getSpecificUser($this->_item->trip_tec)->name;
        } else {
			$this->_item->tec_name = '';
		}

		// get all the booked attendees for the trip
		$this->_item->bookings = GatripsysHelper::getTripAttendees($this->_item->id);

		// get all the incidents for the trip
		$this->_item->incidents = GatripsysHelper::getTripIncidents($this->_item->id);

        return $this->_item;
    }

	/**
	 * Get an instance of Table class
	 * @param   string $type   Name of the Table class to get an instance of.
	 * @param   string $prefix Prefix for the table class name. Optional.
	 * @param   array  $config Array of configuration values for the Table object. Optional.
	 * @return  Table|bool Table if success, false on failure.
	 */
	public function getTable($type = 'Trip', $prefix = 'Administrator', $config = array())
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
            $aliasKey   = $this->getAliasFieldNameByView('trip');
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
		$id = (!empty($id)) ? $id : (int) $this->getState('trip.id');
                
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
		$id = (!empty($id)) ? $id : (int) $this->getState('trip.id');

                
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
		$params  = ComponentHelper::getParams('com_gatripsys');
		$user = GatripsysHelper::getSpecificUser();
        $today = GatripsysHelper::getTodaysDate();
		// Trip Status - each passed state represents a change to the following
		// 2=Approved and taking Bookings - waiting for trip leader to close trip,
		// 4=Closed and waiting on trip-coord to finalise,
		// 5=Cancelled,
		// 6=Finalised or completed,
		$table = $this->getTable();

		$table->load($id);
		$table->state = $state;
		$table->modified_by = $user->id;
		$table->modified_date = $today;

		try {
            $allOK = $table->store();
            if ($state == 2) {
                // approved the trip so notify leader & users
                $data = array('trip_id'=>$id, 'not_attendee'=>1);
                GanotificationsHelper::notifyLeader($data, 'tripaprvtl', 0);
                GatripsysHelper::createNewRecord('#__gatripsys_attendees', $data['trip_id'], $table->leader, $params);
                Factory::getApplication()->enqueueMessage(Text::_('COM_GATRIPSYS_TL_NOTIFIED_MESSAGE'), 'message');
                if ($params->get('notif_users', 0)) {
                    GanotificationsHelper::notifyUsersNewTrip($id, 'tripaprv');
                    Factory::getApplication()->enqueueMessage(Text::_('COM_GATRIPSYS_NOTIFICATION_APPROVAL'), 'message');
                }
            } elseif ($state == 4) { // closed - generate report
                $rptOK = GatriprptHelper::createTripReport($id, 0);
                GanotificationsHelper::notifyTripCoord($id);
            } elseif ($state == 6) { // finalised - generate report
                $rptOK = GatriprptHelper::createTripReport($id, 1);
            } elseif ($state == 5) { // cancel trip
                if ($params->get('notif_users', 0)) {
                    GanotificationsHelper::notifyMembers($id, 'tripcan');
                }
                GanotificationsHelper::notifyTripCoord($id);
            }
            return $allOK;
	    } catch (RuntimeException $e) {
	        return false;
	    }

	}

	/**
	 * Method to delete an item
	 * @param   int $id Element id
	 * @return  bool
	 */
	public function delete($id)
	{
		$user = GatripsysHelper::getSpecificUser();
        $today = GatripsysHelper::getTodaysDate();

		$table = $this->getTable();
		$table->load($id);
		$table->state = -2;
		$table->modified_by = $user->id;
		$table->modified_date = $today;

		return $table->store();
	}
	
	/**
	 * Generate the report
	 * @return bool
	 */
	public function genRpt($id, $final)
	{
		$allOK = GatriprptHelper::createTripReport($id, $final);

		return $allOK;
	}

	/**
	 * Method to delete pdf
	 * @param   int  $id  Element id
	 * @return  bool
	 */
	public function deletePDF($id, $final)
	{
		$deleteOK = false;
		$table = $this->getTable();
		$table->load($id);

		if ($id) {
			$path = Path::clean( JPATH_SITE . '/images/trips' );
			$trip  = str_pad($id, 6, '0', STR_PAD_LEFT);

			if ($final) {
				$tripfile = $path.'/Trip'.$trip.'_final.pdf';
			} else {
				$tripfile = $path.'/Trip'.$trip.'.pdf';
			}

			if (file_exists($tripfile)) {
				$deleteOK = File::delete($tripfile);
			}

			if ($deleteOK) {
				$table->state = 2;
				$deleteOK = $table->store();
			}
  		}

		return $deleteOK;
	}

	/**
	 * Method to delete an item
	 * @param   int $id Element id
	 * @return  bool
	 */
	public function deleteTripFile($id, $fileDel)
	{
		$params  = ComponentHelper::getParams('com_gatripsys');
		$delete_file = $params->get('delete_file', 0);
		$delete_OK = false;

		$table = $this->getTable();
		$table->load($id);

		// Checking if the user can remove object
		$user = GatripsysHelper::getSpecificUser();
		$canEdit = $user->authorise('core.edit', 'com_gatripsys');
		$canEditOwn = $user->authorise('core.edit.own', 'com_gatripsys');
		if (!$canEdit) {
            if ($canEditOwn && ($user->id == $table->leader || $user->id == $table->created_by)) { $canEdit = true; }
		}

 		if ($canEdit) {
			$file_name = $fileDel == 'i' ? $table->trip_img : $table->trip_plan;

			// delete the physical file
			$tran_file = Path::clean( JPATH_SITE . '/' . $file_name );

			if ($delete_file) {
				if (file_exists($tran_file)) {
					$delete_OK = File::delete($tran_file);
				} else {
					$delete_OK = true;
				}
			} else {
				$delete_OK = true;
			}

			if ($delete_OK) {
				if ($fileDel == 'i') { $table->trip_img = ''; } else { $table->trip_plan = ''; }
				$result = $table->store();
			} else {
				$result = false;
			}
		} else {
			$result = false;
		}

        return $result;
                
	}
}
