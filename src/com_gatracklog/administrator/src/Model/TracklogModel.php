<?php
/**
 * @version     4.2.0
 * @package     pkg_mypackage
 * @subpackage  com_gatracklog
 * @author     Glenn Arkell <glenn@glennarkell.com.au>
 * @copyright  2021 Glenn Arkell
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace GlennArkell\Component\Gatracklog\Administrator\Model;

// No direct access.
defined('_JEXEC') or die;

use Joomla\CMS\Table\Table;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\Event\Dispatcher;
use Joomla\CMS\Helper\TagsHelper;
use \GlennArkell\Component\Gatracklog\Administrator\Helper\GatracklogHelper;

/**
 * Single model.
 * @since  1.6
 */
class TracklogModel extends AdminModel
{
	/**
	 * @var      string    The prefix to use with controller messages.
	 * @since    1.6
	 */
	protected $text_prefix = 'COM_GATRACKLOG';

	/**
	 * @var   	string  	Alias to manage history control
	 * @since   3.2
	 */
	public $typeAlias = 'com_gatracklog.tracklog';

	/**
	 * @var null  Item data
	 * @since  1.6
	 */
	protected $item = null;

    /**
     * Batch copy/move command. If set to false, the batch copy/move command is not supported
     * @var  string
     */
    protected $batch_copymove = 'category_id';

    /**
     * Allowed batch commands
     * @var array
     */
    protected $batch_commands = [
        'language_id'   => 'batchLanguage',
        'tag'           => 'batchTag',
    ];

	/**
	 * Returns a reference to the a Table object, always creating it.
	 * @param   string  $type    The table type to instantiate
	 * @param   string  $prefix  A prefix for the table class name. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 * @return    Table    A database object
	 * @since    1.6
	 */
	public function getTable($type = 'Tracklog', $prefix = 'Administrator', $config = array())
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
			'com_gatracklog.tracklog', 
			'tracklog',
			array('control' => 'jform', 
			'load_data' => $loadData ) 
			);

		if (empty($form)) {
			//return false;
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
		$data = Factory::getApplication()->getUserState('com_gatracklog.edit.tracklog.data', array());

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
	 * Method to duplicate a Record
	 * @param   array  &$pks  An array of primary key IDs.
	 * @return  boolean  True if successful.
	 * @throws  Exception
	 */
	public function duplicate(&$pks)
	{
		$user = Factory::getApplication()->getIdentity();

		// Access checks.
		if (!$user->authorise('core.create', 'com_gatracklog')) {
			throw new \Exception(Text::_('JERROR_CORE_CREATE_NOT_PERMITTED'));
		}

		$table = $this->getTable();

		foreach ($pks as $pk) {

			if ($table->load($pk, true))
			{
				// Reset the id to create a new record.
				$table->id = 0;
				$table->state = 0;

				if (!$table->check()) {
					throw new \Exception($table->getError());
				}

				if (in_array(false, $result, true) || !$table->store(true)) {
					throw new \Exception($table->getError());
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
	 * Method to batch records
	 * @return  boolean  True if successful.
	 * @throws  Exception
	 */
	public function batch($commands, $pks, $contexts)
	{
		if ($commands['move_copy'] == 'm' && isset($commands['category_id'])) {
            //
            $table = $this->getTable();
            foreach ($pks as $pk) {
                if ($table->load($pk, true)) {
                    $table->track_zone = $commands['category_id'];
    				if (!$table->check()) {
    					throw new \Exception($table->getError());
    				}

    				if (!$table->store()) {
    					throw new \Exception($table->getError());
    				}
    			}
    		}
        }
		return parent::batch($commands, $pks, $contexts);

	}

	/**
	 * Method to null a date in Record
	 * @param   array  &$pks  An array of primary key IDs.
	 * @return  boolean  True if successful.
	 * @throws  Exception
	 */
	public function nullExpDate(&$pks)
	{
		$user = Factory::getApplication()->getIdentity();
        $dateFldName = 'created_date';

		// Access checks.
		if (!$user->authorise('core.manage', 'com_gatracklog')) {
			throw new \Exception(Text::_('COM_GATRACKLOG_ERROR_MESSAGE_NOT_AUTHORISED'));
		}

		foreach ($pks as $pk)
        {
            $db = Factory::getContainer()->get('DatabaseDriver');
            $db->setQuery('UPDATE #__gatracklog_tracklogs SET '.$dateFldName.' = NULL WHERE id = '.(int) $pk);
            try {
        		$db->execute();
        	} catch (RuntimeException $e) {
        	    throw new \Exception($e);
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
				$db->setQuery('SELECT MAX(ordering) FROM #__gatracklog_tracklogs');
				$max             = $db->loadResult();
				$table->ordering = $max + 1;
			}
		}
	}
}
