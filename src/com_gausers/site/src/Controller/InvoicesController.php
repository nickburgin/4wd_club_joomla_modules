<?php
/**
 * @version    5.1.6
 * @package    Com_Gausers
 * @author     Glenn Arkell <glenn@glennarkell.com.au>
 * @copyright  2018 Glenn Arkell
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace GlennArkell\Component\Gausers\Site\Controller;

// No direct access
\defined('_JEXEC') or die;

use \Joomla\CMS\Application\SiteApplication;
use \Joomla\CMS\Factory;
use \Joomla\CMS\Language\Multilanguage;
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\MVC\Controller\FormController;
use \Joomla\CMS\Router\Route;
use \Joomla\CMS\Uri\Uri;
use \Joomla\Utilities\ArrayHelper;

/**
 * List class.
 * @since  1.6.0
 */
class InvoicesController extends FormController
{
	/**
	 * Proxy for getModel.
	 * @param   string  $name    The model name.
	 * @param   string  $prefix  The class prefix.
	 * @param   array   $config  Configuration array for model.
	 * @return object	The model
	 * @since	1.6
	 */
	public function getModel($name = 'Invoices', $prefix = 'Site', $config = array('ignore_request' => true))
	{
		return parent::getModel($name, $prefix, $config);
	}

    public function disableuser() {

        $app = Factory::getApplication();

        // Get the model.
        $model = $this->getModel();
        
        $userOK = $model->bulkBlockUsers(1);
        
        // Check if ok
        if ($userOK) {
            Factory::getApplication()->enqueueMessage(Text::_("COM_GAUSERS_BLOCK_SUCCESS"), "success");
        } else {
            Factory::getApplication()->enqueueMessage(Text::_("COM_GAUSERS_BLOCK_FAILED"), "warning");
        }

        $this->setRedirect(Route::_('index.php?option=com_gausers&view=invoices', false));
    }

    public function reinstateuser() {

        $app = Factory::getApplication();

        // Get the model.
        $model = $this->getModel();
        
        $userOK = $model->bulkBlockUsers(0);
        
        // Check if ok
        if ($userOK) {
            Factory::getApplication()->enqueueMessage(Text::_("COM_GAUSERS_UNBLOCK_SUCCESS"), "success");
        } else {
            Factory::getApplication()->enqueueMessage(Text::_("COM_GAUSERS_UNBLOCK_FAILED"), "warning");
        }

        $this->setRedirect(Route::_('index.php?option=com_gausers&view=invoices', false));
    }

	/**
	 * Method to reminder invoice pdf to user
	 */
	public function reminderInv()
	{
		// Check for request forgeries.
		$this->checkToken('get');

		// Initialise variables.
		$app	= Factory::getApplication();
		$model	= $this->getModel();

        // Now add the loaded data to the database via a function in the model
        $sent	= $model->reminderInv();

    	// check if ok and display appropriate message
        if ($sent) {
            $app->enqueueMessage(Text::_('COM_GAUSERS_ITEM_REMINDER_SUCCESSFULLY'), 'notice');
        } else {
            $app->enqueueMessage(Text::_('COM_GAUSERS_ITEM_REMINDER_FAILED'), 'warning');
        }
		$this->setRedirect(Route::_('index.php?option=com_gausers&view=invoices', false));

		return true;
	}

}
