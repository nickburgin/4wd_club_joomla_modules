<?php
/**
 * @version    4.2.1
 * @package    Com_Gabroadcast
 * @author     Glenn Arkell <glenn@glennarkell.com.au>
 * @copyright  2019 Glenn Arkell
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace GlennArkell\Component\Gabroadcast\Administrator\Model;

// No direct access.
\defined('_JEXEC') or die;

use \Joomla\CMS\Factory;
use \Joomla\CMS\MVC\Model\AdminModel;
use \Joomla\CMS\Table\Table;
use \Joomla\CMS\Language\Text;

/**
 * Singular model.
 *
 * @since  1.6
 */
class BcastModel extends AdminModel
{
	/**
	 * @var      string    The prefix to use with controller messages.
	 * @since    1.6
	 */
	protected $text_prefix = 'COM_GABROADCAST';

	/**
	 * @var   	string  	Alias to manage history control
	 * @since   3.2
	 */
	public $typeAlias = 'com_gabroadcast.bcast';

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
	 * @return    JTable    A database object
	 * @since    1.6
	 */
	public function getTable($type = 'Bcast', $prefix = 'Administrator', $config = array())
	{
		return parent::getTable($type, $prefix, $config);
	}

	/**
	 * Method to get the record form.
	 * @param   array    $data      An optional array of data for the form to interogate.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 * @return  JForm  A JForm object on success, false on failure
	 * @since    1.6
	 */
	public function getForm($data = array(), $loadData = true)
	{
            // Initialise variables.
            $app = Factory::getApplication();

            // Get the form.
            $form = $this->loadForm( 'com_gabroadcast.bcast', 'bcast', array('control' => 'jform', 'load_data' => $loadData ) );

            if (empty($form)) {
                return false;
            }

            return $form;
	}

	/**
	 * Method to get the data that should be injected in the form.
	 * @return   mixed  The data for the form.
	 * @since    1.6
	 */
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$data = Factory::getApplication()->getUserState('com_gabroadcast.edit.bcast.data', array());

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
                // Do any procesing on fields here if needed
            }

            return $item;
            
	}

	/**
	 * Prepare and sanitise the table prior to saving.
	 * @param   JTable  $table  Table Object
	 * @return void
	 * @since    1.6
	 */
	protected function prepareTable($table)
	{

		if (empty($table->id))
		{
			// Set ordering to the last item if not set
			if (@$table->ordering === '')
			{
				$db = Factory::getContainer()->get('DatabaseDriver');
				$db->setQuery('SELECT MAX(ordering) FROM #__gabroadcast_bcasts');
				$max             = $db->loadResult();
				$table->ordering = $max + 1;
			}
		}
	}
}
