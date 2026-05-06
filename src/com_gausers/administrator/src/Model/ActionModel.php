<?php
/**
 * @version     6.0.0
 * @package     com_gausers
 * @copyright   Copyright (C) 2012. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Created by Glenn Arkell - http://www.glennarkell.com.au
 */

namespace GlennArkell\Component\Gausers\Administrator\Model;

// No direct access.
defined('_JEXEC') or die;

use Joomla\CMS\Table\Table;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\Event\Dispatcher;
use Joomla\CMS\Helper\TagsHelper;
use \GlennArkell\Component\Gausers\Administrator\Helper\GausersHelper;
use Joomla\CMS\User\UserHelper;
use Joomla\CMS\Access\Access;
use Joomla\Filter\OutputFilter;

/**
 * Gausers model.
 */
class ActionModel extends AdminModel
{
	/**
	 * @var		string	The prefix to use with controller messages.
	 * @since	1.6
	 */
	protected $text_prefix = 'COM_GAUSERS';

	/**
	 * @var   	string  	Alias to manage history control
	 * @since   3.2
	 */
	public $typeAlias = 'com_gausers.action';

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
	 *
	 * @param	type	The table type to instantiate
	 * @param	string	A prefix for the table class name. Optional.
	 * @param	array	Configuration array for model. Optional.
	 * @return	Table	A database object
	 * @since	1.6
	 */
	public function getTable($type = 'Action', $prefix = 'Administrator', $config = array())
	{
		return parent::getTable($type, $prefix, $config);
	}

	/**
	 * Method to get the record form.
	 *
	 * @param	array	$data		An optional array of data for the form to interogate.
	 * @param	boolean	$loadData	True if the form is to load its own data (default case), false if not.
	 * @return	JForm	A JForm object on success, false on failure
	 * @since	1.6
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Initialise variables.
		$app	= Factory::getApplication();

		// Get the form.
		$form = $this->loadForm('com_gausers.action', 'action', array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form)) {
			return false;
		}

		return $form;
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return	mixed	The data for the form.
	 * @since	1.6
	 */
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$data = Factory::getApplication()->getUserState('com_gausers.edit.action.data', array());

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
	 *
	 * @param	integer	The id of the primary key.
	 *
	 * @return	mixed	Object on success, false on failure.
	 * @since	1.6
	 */
	public function getItem($pk = null)
	{
		if ($item = parent::getItem($pk)) {

			//Do any procesing on fields here if needed

		}

		return $item;
	}

	/**
	 * Method to duplicate a record
	 * @param   array  &$pks  An array of primary key IDs.
	 * @return  boolean  True if successful.
	 * @throws  Exception
	 */
	public function duplicate(&$pks)
	{
		$user = Factory::getApplication()->getIdentity();

		// Access checks.
		if (!$user->authorise('core.create', 'com_gausers'))
		{
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

				if (in_array(false, $result, true) || !$table->store()) {
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
        //Factory::getApplication()->enqueueMessage($commands, 'warning');
        //Factory::getApplication()->setUserState('com_gausers.test.data', $pks);
		//$this->batch_commands = array_merge($this->batch_commands, $this->action_batch_commands);
		if ($commands['move_copy'] == 'm' && isset($commands['category_id'])) {
            //
            $table = $this->getTable();
            foreach ($pks as $pk) {
                if ($table->load($pk, true)) {
                    $table->category_id = $commands['category_id'];
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
	 * Prepare and sanitise the table prior to saving.
	 *
	 * @since	1.6
	 */
	protected function prepareTable($table)
	{
		//jimport('joomla.filter.output');

		if (empty($table->id)) {

			// Set ordering to the last item if not set
			if (@$table->ordering === '') {
				$db = Factory::getContainer()->get('DatabaseDriver');
				$db->setQuery('SELECT MAX(ordering) FROM #__gausers_actions');
				$max = $db->loadResult();
				$table->ordering = $max+1;
			}

		}
	}

}
