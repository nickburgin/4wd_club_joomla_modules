<?php
/**
 * @version    6.0.0
 * @package    Com_Gausers
 * @author     Glenn Arkell <glenn@glennarkell.com.au>
 * @copyright  2017 Glenn Arkell
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace GlennArkell\Component\Gausers\Administrator\Controller;

// No direct access.
\defined('_JEXEC') or die;

use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Event\Dispatcher;

/**
 * Audits list controller class.
 *
 * @since  1.6
 */
class AuditsController extends AdminController
{
	/**
	 * Method to clone existing Audits
	 *
	 * @return void
	 */
	public function duplicate()
	{
		// Check for request forgeries
		$this->checkToken();

		// Get id(s)
		$pks = $this->input->post->get('cid', array(), 'array');

		try
		{
			if (empty($pks))
			{
				throw new \Exception(Text::_('COM_GAUSERS_NO_ELEMENT_SELECTED'));
			}

			ArrayHelper::toInteger($pks);
			$model = $this->getModel();
			$model->duplicate($pks);
			$this->setMessage(Text::_('COM_GAUSERS_ITEMS_SUCCESS_DUPLICATED'));
		}
		catch (Exception $e)
		{
			Factory::getApplication()->enqueueMessage($e->getMessage(), 'warning');
		}

		$this->setRedirect('index.php?option=com_gausers&view=audits');
	}

	/**
	 * Proxy for getModel.
	 *
	 * @param   string  $name    Optional. Model name
	 * @param   string  $prefix  Optional. Class prefix
	 * @param   array   $config  Optional. Configuration array for model
	 *
	 * @return  object	The Model
	 *
	 * @since    1.6
	 */
	public function getModel($name = 'Audit', $prefix = 'Administrator', $config = array('ignore_request' => true))
	{
		return parent::getModel($name, $prefix, $config);
	}

	/**
	 * Method to save the submitted ordering values for records via AJAX.
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	public function saveOrderAjax()
	{
		// Get the input
		$input = Factory::getApplication()->input;
		$pks   = $input->post->get('cid', array(), 'array');
		$order = $input->post->get('order', array(), 'array');

		// Sanitize the input
		ArrayHelper::toInteger($pks);
		ArrayHelper::toInteger($order);

		// Get the model
		$model = $this->getModel();

		// Save the ordering
		$return = $model->saveorder($pks, $order);

		if ($return)
		{
			echo "1";
		}

		// Close the application
		Factory::getApplication()->close();
	}

	/**
	 * Method to trigger import of users from CSV
	 * Plugin must be enabled and a csv file specified in the plugin parameters
	 * @return void
	 */
	public function importUsers()
	{
		// Check for request forgeries
		$this->checkToken();

		$isEnabled = PluginHelper::isEnabled('user', 'gausers');

		if ($isEnabled) {
            $plugin = PluginHelper::importPlugin('user', 'gausers');
    		try {
    			$results = Factory::getApplication()->triggerEvent('onGaloaderPrepareData');
    			//$dispatcher = Dispatcher::getInstance();
    			//$results = $dispatcher->triggerEvent('onGaloaderPrepareData');
    			$this->setMessage(Text::_('COM_GAUSERS_ITEMS_SUCCESS_IMPORT'));
    		} catch (Exception $e) {
    			Factory::getApplication()->enqueueMessage($e->getMessage(), 'warning');
    		}
		}

		$this->setRedirect('index.php?option=com_gausers&view=audits');
	}

	/**
	 * Method to trigger import of users data from CSV
	 * then cycle through to see if they exist
	 * @return void
	 */
	public function verifyUsers()
	{
		// Check for request forgeries
		$this->checkToken();

		// Get the model
		$model = $this->getModel();

		// Save the ordering
		$return = $model->verifyData();

		if ($return) {
            $this->setMessage(Text::_('COM_GAUSERS_ITEMS_SUCCESS_VERIFIED'));
 		}

		$this->setRedirect('index.php?option=com_gausers&view=audits');
	}

}
