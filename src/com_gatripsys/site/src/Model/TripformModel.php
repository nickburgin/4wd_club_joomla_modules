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
use \GlennArkell\Component\Gatripsys\Administrator\Helper\GatriprptHelper;

/**
 * Form model.
 * @since  1.6
 */
class TripformModel extends FormModel
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
                $id = $app->getUserState('com_gatripsys.edit.trip.id');
        } else {
                $id = $app->input->get('id');
                $app->setUserState('com_gatripsys.edit.trip.id', $id);
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
                    $id = $this->getState('trip.id');
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

			if (isset($this->item->leader) && $this->item->leader > 0) {
				$this->item->leader_name = GatripsysHelper::getSpecificUser($this->item->leader)->name;
			}

			if (isset($this->item->trip_tec)) {
				$this->item->tec_name = GatripsysHelper::getSpecificUser($this->item->trip_tec)->name;
			}

			if (isset($this->item->trip_plan) && $this->item->trip_plan > '') {
				$this->item->trip_plan_disp = $this->item->trip_plan;
			}

			if (isset($this->item->trip_img) && $this->item->trip_img > '') {
				$this->item->trip_img_disp = $this->item->trip_img;
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
    public function getTable($type = 'Trip', $prefix = 'Administrator', $config = array())
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
        $form = $this->loadForm('com_gatripsys.trip', 'tripform', array(
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
        $data = Factory::getApplication()->getUserState('com_gatripsys.edit.trip.data', array());

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
        $id    = (!empty($data['id'])) ? $data['id'] : (int) $this->getState('trip.id');
        $data['state'] = (!empty($data['state'])) ? $data['state'] : 0;
        $user  = GatripsysHelper::getSpecificUser();
        $today = GatripsysHelper::getTodaysDate();
	    $date = Factory::getDate();
	    $futureDate = $date->modify('+ 5 day');
		if (!isset($data['dept_date']) || empty($data['dept_date'])) {
			$data['dept_date'] = $futureDate->toSql();
		}
		if (!isset($data['ret_date']) || empty($data['ret_date'])) {
			$data['ret_date'] = $futureDate->toSql();
		}
		if (!isset($data['regby_date']) || empty($data['regby_date'])) {
			$data['regby_date'] = $futureDate->toSql();
		}

		$params = ComponentHelper::getParams('com_gatripsys');

        if ($id) {
            // Check the user can edit this item
            $authorised = $user->authorise('core.edit', 'com_gatripsys') || $authorised = $user->authorise('core.edit.own', 'com_gatripsys');
			$data['modified_date'] = $today;
			$data['modified_by'] = $user->id;
        } else {
            // Check the user can create new items in this section
            $authorised = $user->authorise('core.create', 'com_gatripsys');
            if ($authorised && $params->get('notif_tc',0) && $data['state'] == 0) {
                $data['state'] = 1;
                Factory::getApplication()->enqueueMessage(Text::_('COM_GATRIPSYS_TC_NOTIFIED_MESSAGE'), 'message');
            } else {
                $data['state'] = 1;
            }
        }

        if ($authorised !== true) {
            throw new \Exception(Text::_('JERROR_ALERTNOAUTHOR'), 403);
        }

		if ($params->get('auto_trip_apprv',0)) { $data['state'] = 2; }

		$table = $this->getTable();

        if ($table->save($data) === true) {

			if (!$id) {
				// notify of new trip record
                if (!$params->get('auto_trip_apprv',0) && $table->state == 1) {
                    GanotificationsHelper::notifyTripCoord($table->id);
                } else {
                    if ($params->get('notif_tc',0) && ($table->state == 1 || $table->state == 2)) {
                        GanotificationsHelper::notifyTripCoord($table->id);
                    }
                }
				if ($params->get('notif_users',0)) {
					if ($table->state == 2) {
						GanotificationsHelper::notifyUsersNewTrip($table->id, 'tripaprv');
						Factory::getApplication()->enqueueMessage(Text::_('COM_GATRIPSYS_NOTIFICATION_APPROVAL'), 'message');
		 			} elseif ($table->state == 1 && !$params->get('trip_approval',0)) {
						GanotificationsHelper::notifyUsersNewTrip($table->id, 'tripnew');
						Factory::getApplication()->enqueueMessage(Text::_('COM_GATRIPSYS_NO_NOTIFICATION_PENDING'), 'message');
					}
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

        if (empty($pk)) {
            $pk = (int) $this->getState('trip.id');
        }

        if ($pk == 0 || $this->getItem($pk) == null) {
            throw new \Exception(Text::_('COM_GATRIPSYS_ITEM_DOESNT_EXIST'), 404);
        }

        if ($user->authorise('core.delete', 'com_gatripsys') !== true) {
            throw new \Exception(Text::_('JERROR_ALERTNOAUTHOR'), 403);
        }

        $table = $this->getTable();

        if ($table->delete($pk) !== true) {
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
	 * Approve the Trip record
	 * @return bool
	 */
	public function approveTrip($data)
	{
        $app = Factory::getApplication();
        $user  = GatripsysHelper::getSpecificUser();
        $today = GatripsysHelper::getTodaysDate();
		$params = ComponentHelper::getParams('com_gatripsys');

		$table = $this->getTable();
		$table->load($data['id']);
		$table->state = 1;
		$table->modified_by = $user->id;
		$table->modified_date = $today;

		if ($table->store() === true) {
			if ($params->get('notif_users',0)) {
				GanotificationsHelper::notifyUsers($table->id, 'tripnew');
				$app->enqueueMessage(Text::_('COM_GATRIPSYS_NOTIFICATION_APPROVAL'), 'message');
 			}
			return true;
		} else {
			return false;
		}

	}

	/**
	 * Add comment to the Trip record
	 * @return bool
	 */
	public function addIncident($data)
	{
		$table = $this->getTable();
		$table->load($data['id']);
		$table->comment = $data['comment'];

		if ($table->store() === true) {
			return $table->id;
		} else {
			return false;
		}

	}

	/**
	 * Generate the final report
	 * @return bool
	 */
	public function genFinalRpt($data)
	{
		$final = 1;

		$allOK = GatriprptHelper::createTripReport($data['id'], $final);

		// now archive the record
		$table = $this->getTable();
		$table->load($data['id']);
		$table->state = 2;
        $table->store();

		return $allOK;
	}

    /**
     * Method to upload an attachment
     */
    public function uploadAttachment($tran_file = null, $params = null, $file_type = 'trip')
    {
        $safeFileOptions  = $params->get( 'safe_files' );
        $tripplandir  = $params->get( 'tripplan_dir', 'images/trips/trip_plans' );
        $tripimgdir  = $params->get( 'tripimg_dir', 'images/trips/trip_images' );
		$file_ext = substr($tran_file['name'],-3);

		if ($file_type == 'trip') { $dir = $tripimgdir; } else { $dir = $tripplandir; }
		if (!in_array($file_ext, $safeFileOptions)) {
			Factory::getApplication()->enqueueMessage(Text::_('File format ('.$file_ext.') not allowed'), 'danger');
			return false;
		}

        if (file_exists('file://'.$tran_file['tmp_name'])) {
			$fileName = File::makeSafe($tran_file['name']);
			$fileName = str_replace(' ', '_', $fileName);
			$src = $tran_file['tmp_name'];
			$fileName = $dir.'/'.$fileName;

			$path = Path::clean( JPATH_SITE . '/' );
			$destfile = $path.'/'.$fileName;

			if ( File::upload($src, $destfile, false, false, $safeFileOptions) ) {
				Factory::getApplication()->enqueueMessage(Text::_('File ('.$fileName.') Uploaded Successfully'), 'success');
				return $fileName;
			} else {
				Factory::getApplication()->enqueueMessage(Text::_('File ('.$fileName.') Upload Failed'), 'danger');
				return null;
			}
  		} else {
			Factory::getApplication()->enqueueMessage(Text::_('File ('.$fileName.') Does Not Exist'), 'danger');
			return null;
		}
    }
}
