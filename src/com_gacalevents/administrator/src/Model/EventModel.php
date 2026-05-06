<?php
/**
 * @version    3.3.1
 * @package    Com_Gacalevents
 * @author     Glenn Arkell <glenn@glennarkell.com.au>
 * @copyright  2021 Glenn Arkell
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace GlennArkell\Component\Gacalevents\Administrator\Model;

// No direct access.
defined('_JEXEC') or die;

use Joomla\CMS\Table\Table;
use Joomla\CMS\Factory;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Helper\TagsHelper;
use Joomla\CMS\Component\ComponentHelper;
use \GlennArkell\Component\Gacalevents\Administrator\Helper\GacaleventsHelper;

/**
 * Single model.
 * @since  1.6
 */
class EventModel extends AdminModel
{
	/**
	 * @var      string    The prefix to use with controller messages.
	 * @since    1.6
	 */
	protected $text_prefix = 'COM_GACALEVENTS';

	/**
	 * @var   	string  	Alias to manage history control
	 * @since   3.2
	 */
	public $typeAlias = 'com_gacalevents.event';

	/**
	 * @var null  Item data
	 * @since  1.6
	 */
	protected $item = null;

	/**
	 * Returns a reference to the a Table object, always creating it.
	 * @param   string  $type    The table type to instantiate
	 * @param   string  $prefix  A prefix for the table class name. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 * @return    Table    A database object
	 * @since    1.6
	 */
	public function getTable($type = 'Event', $prefix = 'Administrator', $config = array())
	{
		return parent::getTable($type, $prefix, $config);
	}

	/**
	 * Method to get the record form.
	 * @param   array    $data      An optional array of data for the form to interogate.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 * @return  Form  A Form object on success, false on failure
	 * @since    1.6
     * @throws
	 */
	public function getForm($data = array(), $loadData = true)
	{
            // Initialise variables.
            $app = Factory::getApplication();

            // Get the form.
            $form = $this->loadForm( 
					'com_gacalevents.event', 'event',
					array('control' => 'jform',
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
	 * @return   mixed  The data for the form.
	 * @since    1.6
     * @throws
	 */
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$data = Factory::getApplication()->getUserState('com_gacalevents.edit.event.data', array());

		if (empty($data)) {
			if ($this->item === null) {
				$this->item = $this->getItem();
			}

			$data = $this->item;

		}

		return $data;
	}

	/**
	 * Method to get a single record.
	 * @param   integer  $pk  The id of the primary key.
	 * @return  mixed    Object on success, false on failure.
	 * @since    1.6
	 */
	public function getItem($pk = null)
	{
            if ($item = parent::getItem($pk)) {
            	if (isset($item->params)) {
            		$item->params = json_encode($item->params);
            	}
            	
                // Do any procesing on fields here if needed
            }

            return $item;
            
	}

	/**
	 * Method to duplicate an Event
	 * @param   array  &$pks  An array of primary key IDs.
	 * @return  boolean  True if successful.
	 * @throws  Exception
	 */
	public function duplicate(&$pks)
	{
		$user = Factory::getApplication()->getIdentity();
		$params = ComponentHelper::getParams('com_gacalevents');
		$duplic_days = $params->get('duplic_days', '7');
		$repeatEvent = $params->get('repeat_event', 0);
		if ($repeatEvent) {
            // setup duplication information
            $repeatTimes = $params->get('repeat_qty', 1);
            $repeatGap = $params->get('repeat_setting', 7);
            $repeatType = $params->get('repeat_type', 'day');
        }
        $duplicDays = $repeatEvent ? $repeatGap : $duplic_days;
        $duplicPeriod = $repeatEvent ? $repeatType : 'day';

		// Access checks.
		if (!$user->authorise('core.create', 'com_gacalevents')) {
			throw new \Exception(Text::_('JERROR_CORE_CREATE_NOT_PERMITTED'));
		}

		$table = $this->getTable();

		foreach ($pks as $pk) {

			if ($table->load($pk, true))
			{
				// Test for multiple repeats
                if ($repeatEvent && $repeatTimes > 1) {
                    // cycle through
                    //$origDdate = new Date($table->depart_date);
                    //$origRdate = new Date($table->return_date);
                    $record_id = $pk;
                    for ($x = 1; $x <= $repeatTimes; $x++) {
        				$subtable = $this->getTable();
        				$subtable->load($record_id);
                        //reset and load new record
                        $subtable->id = 0;
        				$subtable->state = 1;
        				$subtable->created_by = $user->id;
        				$ddate = new Date($subtable->depart_date . ' +'.$duplicDays.' '.$duplicPeriod);
        				$rdate = new Date($subtable->return_date . ' +'.$duplicDays.' '.$duplicPeriod);
        				$subtable->depart_date = $ddate->toSql();
        				$subtable->return_date = $rdate->toSql();

        				if (!$subtable->check()) {
        					throw new \Exception($subtable->getError());
        				}
        				
        				if (!$subtable->store()) {
        					throw new \Exception($subtable->getError());
        				} else {
                            $record_id = $subtable->id;
                        }
    				}
                } else {
                    // Reset the id to create a new record.
    				$table->id = 0;
    				$table->state = 1;
    				$table->created_by = $user->id;
    				$ddate = new Date($table->depart_date . ' +'.$duplicDays.' '.$duplicPeriod);
    				$rdate = new Date($table->return_date . ' +'.$duplicDays.' '.$duplicPeriod);
    				$table->depart_date = $ddate->toSql();
    				$table->return_date = $rdate->toSql();

    				if (!$table->check()) {
    					throw new \Exception($table->getError());
    				}
    				
    				if (!$table->store()) {
    					throw new \Exception($table->getError());
    				}
				}

			} else {
				throw new \Exception($table->getError());
			}
                    
		}

		// Clean cache
		$this->cleanCache();

		return true;
	}

	/**
	 * Prepare and sanitise the table prior to saving.
	 * @param   Table  $table  Table Object
	 * @return void
	 * @since    1.6
	 */
	protected function prepareTable($table)
	{
		if (empty($table->id)) {
			// Set ordering to the last item if not set
			if (@$table->ordering === '') {
				$db = Factory::getContainer()->get('DatabaseDriver');
				$db->setQuery('SELECT MAX(ordering) FROM #__gacalevents_events');
				$max             = $db->loadResult();
				$table->ordering = $max + 1;
			}
		}
	}
}
