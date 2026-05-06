<?php
/**
 * @version    3.0.0
 * @package    Com_Gacalevents
 * @author     Glenn Arkell <glenn@glennarkell.com.au>
 * @copyright  2021 Glenn Arkell
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace GlennArkell\Component\Gacalevents\Site\Model;

// No direct access.
defined('_JEXEC') or die;

use \Joomla\CMS\Factory;
use \Joomla\Utilities\ArrayHelper;
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\Table\Table;
use \Joomla\CMS\MVC\Model\FormModel;
use \Joomla\CMS\Component\ComponentHelper;
use \GlennArkell\Component\Gacalevents\Administrator\Helper\GacaleventsHelper;
use \GlennArkell\Component\Gacalevents\Administrator\Helper\GanamesHelper;

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
        $app = Factory::getApplication('com_gacalevents');

        // Load state from the request userState on edit or from the passed variable on default
        if ($app->input->get('layout') == 'edit') {
                $id = $app->getUserState('com_gacalevents.edit.attendee.id');
                $event_id = $app->getUserState('com_gacalevents.edit.attendee.event_id');
                $attendee = $app->getUserState('com_gacalevents.edit.attendee.attendee');
        } else {
                $id = $app->input->get('id');
                $event_id = $app->input->get('event_id');
                $attendee = $app->input->get('attendee');
                $app->setUserState('com_gacalevents.edit.attendee.id', $id);
                $app->setUserState('com_gacalevents.edit.attendee.event_id', $event_id);
                $app->setUserState('com_gacalevents.edit.attendee.attendee', $attendee);
        }

        $this->setState('attendee.id', $id);
        $this->setState('attendee.event_id', $event_id);
        $this->setState('attendee.attendee', $attendee);

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
            }

            // Get a level row instance.
            $table = $this->getTable();

            if ($table !== false && $table->load($id) && !empty($table->id)) {
                $user = GacaleventsHelper::getSpecificUser();
                $id   = $table->id;

                $canEdit = $user->authorise('core.edit', 'com_gacalevents') || $user->authorise('core.create', 'com_gacalevents');

                if (!$canEdit && $user->authorise('core.edit.own', 'com_gacalevents')) {
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

			if (isset($this->item->attendee)) {
				$this->item->attendee_name = Factory::getUser($this->item->attendee)->name;
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
            $user = GacaleventsHelper::getSpecificUser();

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
     * Method to get the form.
     * The base form is loaded from XML
     * @param   array   $data     An optional array of data for the form to interogate.
     * @param   boolean $loadData True if the form is to load its own data (default case), false if not.
     * @return    JForm    A JForm object on success, false on failure
     * @since    1.6
     */
    public function getForm($data = array(), $loadData = true)
    {
        // Get the form.
        $form = $this->loadForm('com_gacalevents.attendee', 'attendeeform', array(
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
     * @return    array  The default data is an empty array.
     * @since    1.6
     */
    protected function loadFormData()
    {
        $data = Factory::getApplication()->getUserState('com_gacalevents.edit.attendee.data', array());

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
        $attendee    = (!empty($data['attendee'])) ? $data['attendee'] : 0;
        $state = (!empty($data['state'])) ? 1 : 1;
        $user  = GacaleventsHelper::getSpecificUser();

        if ($id) {
            // Check the user can edit this item
            $authorised = $user->authorise('core.edit', 'com_gacalevents') || $authorised = $user->authorise('core.edit.own', 'com_gacalevents');
        } else {
            // Check the user can create new items in this section
            $authorised = $user->authorise('core.create', 'com_gacalevents') || $authorised = $user->authorise('core.edit.own', 'com_gacalevents');
        }

        if ($authorised !== true) {
            throw new \Exception(Text::_('JERROR_ALERTNOAUTHOR'), 403);
        }
        
        if ($attendee) {
			$params = ComponentHelper::getParams('com_gacalevents');
			$partnerShip  = $params->get( 'partner_mship' );
			$psuf  = $params->get( 'profile_suffix', 'b4wdc' );
			$ppart  = $params->get( 'profile_partner', 'partner' );
			$locProfKey = 'profile'.$psuf.'.'.$ppart;

			$member = GanamesHelper::breakdownNamesFromUserID($attendee, $locProfKey);

			if ($partnerShip) {
				$data['pub_name'] = GanamesHelper::combineNames($member);
			} else {
				$data['pub_name'] = $member->name;
			}

		    $name = $data['pub_name'];
		    $last_name = (strpos($name, ' ') === false) ? '' : preg_replace('#.*\s([\w-]*)$#', '$1', $name);
		    $first_name = trim( preg_replace('#'.$last_name.'#', '', $name ) );

			$data['pub_fname'] = $first_name;
			$data['pub_sname'] = $last_name;
			$data['pub_partner'] = $member->partner;
		}

        $table = $this->getTable();

        if ($table->save($data) === true) {
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
        $user = GacaleventsHelper::getSpecificUser();

        if (empty($pk)) {
            $pk = (int) $this->getState('attendee.id');
        }

        if ($pk == 0 || $this->getItem($pk) == null) {
            throw new \Exception(Text::_('GACALEVENTS_ITEM_DOESNT_EXIST'), 404);
        }

        if ($user->authorise('core.delete', 'com_gacalevents') !== true) {
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
    
}
