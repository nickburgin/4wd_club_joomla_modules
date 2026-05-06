<?php
/**
 * @version    5.1.6
 * @package    Com_Gausers
 * @author     Glenn Arkell <glenn@glennarkell.com.au>
 * @copyright  2017 Glenn Arkell
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace GlennArkell\Component\Gausers\Administrator\Model;

// No direct access.
defined('_JEXEC') or die;

use \Joomla\CMS\Table\Table;
use \Joomla\CMS\Factory;
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\Plugin\PluginHelper;
use \Joomla\CMS\MVC\Model\AdminModel;
use \Joomla\Event\Dispatcher;
use \Joomla\CMS\Helper\TagsHelper;
use \GlennArkell\Component\Gausers\Administrator\Helper\GausersHelper;
use \GlennArkell\Component\Gausers\Administrator\Helper\GauserverifyHelper;

/**
 * Gausers model.
 *
 * @since  1.6
 */
class AuditModel extends AdminModel
{
	/**
	 * @var      string    The prefix to use with controller messages.
	 * @since    1.6
	 */
	protected $text_prefix = 'COM_GAUSERS';

	/**
	 * @var   	string  	Alias to manage history control
	 * @since   3.2
	 */
	public $typeAlias = 'com_gausers.audit';

	/**
	 * @var null  Item data
	 * @since  1.6
	 */
	protected $item = null;

	/**
	 * Returns a reference to the a Table object, always creating it.
	 *
	 * @param   string  $type    The table type to instantiate
	 * @param   string  $prefix  A prefix for the table class name. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return    Table    A database object
	 *
	 * @since    1.6
	 */
	public function getTable($type = 'Audit', $prefix = 'Administrator', $config = array())
	{
		return parent::getTable($type, $prefix, $config);
	}

	/**
	 * Method to get the record form.
	 *
	 * @param   array    $data      An optional array of data for the form to interogate.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  JForm  A JForm object on success, false on failure
	 *
	 * @since    1.6
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Initialise variables.
		$app = Factory::getApplication();

		// Get the form.
		$form = $this->loadForm(
			'com_gausers.audit', 'audit',
			array('control' => 'jform',
				'load_data' => $loadData
			)
		);

		if (empty($form))
		{
			//return false;
		}

		return $form;
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return   mixed  The data for the form.
	 *
	 * @since    1.6
	 */
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$data = Factory::getApplication()->getUserState('com_gausers.edit.audit.data', array());

		if (empty($data))
		{
			if ($this->item === null)
			{
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
		if ($item = parent::getItem($pk))
		{
			// Do any procesing on fields here if needed
		}

		return $item;
	}

	/**
	 * Method to duplicate an Audit
	 * @param   array  &$pks  An array of primary key IDs.
	 * @return  boolean  True if successful.
	 * @throws  Exception
	 */
	public function duplicate(&$pks)
	{
		$user = GausersHelper::getSpecificUser();

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
	 * Prepare and sanitise the table prior to saving.
	 * @param   Table  $table  Table Object
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
				$db->setQuery('SELECT MAX(ordering) FROM #__gausers_profile_audit');
				$max             = $db->loadResult();
				$table->ordering = $max + 1;
			}
		}
	}

	/**
	 * Method to verify user database against csv file data.
	 */
	public function verifyData()
	{
		$result = GauserverifyHelper::adminVerifyFile();

		return $result;
	}

}
