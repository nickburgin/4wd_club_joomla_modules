<?php
/**
 * @version    5.1.0
 * @package    Com_Gatripsys
 * @author     Glenn Arkell <glenn@glennarkell.com.au>
 * @copyright  2018 Glenn Arkell
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace GlennArkell\Component\Gatripsys\Site\Controller;

// No direct access.
defined('_JEXEC') or die;

use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\Utilities\ArrayHelper;

/**
 * List controller class.
 * @since  1.6
 */
class InvoicesController extends FormController
{
	/**
	 * Proxy for getModel.
	 * @param   string  $name    The model name. Optional.
	 * @param   string  $prefix  The class prefix. Optional
	 * @param   array   $config  Configuration array for model. Optional
	 * @return object	The model
	 * @since	1.6
	 */
	public function getModel($name = 'Invoices', $prefix = 'Site', $config = array('ignore_request' => true))
	{
		$model = parent::getModel($name, $prefix, $config);

		return $model;
	}

	/**
	 * Send a reminder email to  Users who still haven't paid their invoice.
	 */
    public function sendReminder() {

        $app = Factory::getApplication();

        // Get the model.
        $model = $this->getModel();

        $userOK = $model->sendReminder();

        // Check if ok
        if ($userOK) {
            $this->setMessage(Text::_('COM_GATRIPSYS_REMINDERS_SUCCESSFUL', 'notice'));
        } else {
            $this->setMessage(Text::_('COM_GATRIPSYS_REMINDERS_FAILED', 'error'));
        }

        $this->setRedirect(Route::_('index.php?option=com_gatripsys&view=invoices', false));
    }

}
