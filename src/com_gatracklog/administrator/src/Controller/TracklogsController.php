<?php
/**
 * @version    4.2.0
 * @package    pkg_mypackage
 * @subpackage com_gatracklog
 * @author     Glenn Arkell <glenn@glennarkell.com.au>
 * @copyright  2021 Glenn Arkell
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace GlennArkell\Component\Gatracklog\Administrator\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Log\Log;
//use Joomla\Application\AbstractWebApplication as GaWebApp;

// Log::addLogger(
//     array(
//          'logger' => 'galogger',
//          'text_file' => 'com_gatracklog.tranerrors.php'
//     ),
//     Log::ALL,
//     array('com_gatracklog')
// );

//Log::addLogger( array( 'logger' => 'gatracklog','text_file' => 'gatracklog.trans.php'), Log::ALL, array('gatracklog'));
//Log::addLogger( array( 'logger' => 'messagequeue'), Log::ALL, array('com_gatracklog'));

/**
 * Multiples controller class.
 * @since  1.6.0
 */
class TracklogsController extends AdminController
{
	/**
	 * Method to clone existing Tracklogs
	 * @return void
     * @throws Exception
	 */
	public function duplicate()
	{
		//Log::add('an error to display from duplicate', Log::ALL, 'gatracklog');
		// Check for request forgeries
		$this->checkToken();

		// Get id(s)
		$pks = $this->input->post->get('cid', array(), 'array');

		try
		{
			if (empty($pks))
			{
				throw new \Exception(Text::_('COM_GATRACKLOG_NO_ELEMENT_SELECTED'));
			}

			ArrayHelper::toInteger($pks);
			$model = $this->getModel();
			$model->duplicate($pks);
			$this->setMessage(Text::_('COM_GATRACKLOG_ITEMS_SUCCESS_DUPLICATED'));
		}
		catch (Exception $e)
		{
			Factory::getApplication()->enqueueMessage($e->getMessage(), 'warning');
		}

		$this->setRedirect('index.php?option=com_gatracklog&view=tracklogs');
	}

	/**
	 * Method to null expiry dates Pictures
	 * @return void
     * @throws Exception
	 */
	public function nullDate()
	{
		// Check for request forgeries
		$this->checkToken();

		// Get id(s)
		$pks = $this->input->post->get('cid', array(), 'array');

		try
		{
			if (empty($pks))
			{
				throw new \Exception(Text::_('COM_GATRACKLOG_NO_ELEMENT_SELECTED'));
			}

			ArrayHelper::toInteger($pks);
			$model = $this->getModel();
			$model->nullExpDate($pks);
			$this->setMessage(Text::_('COM_GATRACKLOG_ITEMS_SUCCESS_DUPLICATED'));
		}
		catch (Exception $e)
		{
			Factory::getApplication()->enqueueMessage($e->getMessage(), 'warning');
		}

		$this->setRedirect('index.php?option=com_gatracklog&view=tracklogs');
	}

	/**
	 * Proxy for getModel.
	 * @param   string  $name    Optional. Model name
	 * @param   string  $prefix  Optional. Class prefix
	 * @param   array   $config  Optional. Configuration array for model
	 * @return  object	The Model
	 * @since    1.6
	 */
	public function getModel($name = 'Tracklog', $prefix = 'Administrator', $config = array('ignore_request' => true))
	{
		//Log::add('an error to display from getModel', Log::ALL, 'com_gatracklog');
		return parent::getModel($name, $prefix, $config);
	}

	/**
	 * Method to save the submitted ordering values for records via AJAX.
	 * @return  void
	 * @since   3.0
     * @throws Exception
     */
	public function saveOrderAjax()
	{
		//Log::add('an error to display', Log::ALL, 'msg-errors');
		// Get the input
		$input = Factory::getApplication()->input;
		//$input = GaWebApp::getInput();
		$pks   = $input->post->get('cid', array(), 'array');
		$order = $input->post->get('order', array(), 'array');

		// Sanitize the input
		ArrayHelper::toInteger($pks);
		ArrayHelper::toInteger($order);

		// Get the model
		$model = $this->getModel();

		// Save the ordering
		$return = $model->saveorder($pks, $order);

		if ($return) {
			echo "1";
		}

		// Close the application
		Factory::getApplication()->close();
	}
}
