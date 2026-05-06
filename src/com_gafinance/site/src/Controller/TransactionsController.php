<?php
/**
 * @version    5.2.3
 * @package    Com_Gafinance
 * @author     Glenn Arkell <glenn@glennarkell.com.au>
 * @copyright  2021 Glenn Arkell
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace GlennArkell\Component\Gafinance\Site\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\Utilities\ArrayHelper;
use \GlennArkell\Component\Gafinance\Administrator\Helper\GafinanceHelper;

/**
 * Multiples list class.
 * @since  1.6.0
 */
class TransactionsController extends FormController
{
	/**
	 * Proxy for getModel.
	 *
	 * @param   string  $name    The model name. Optional.
	 * @param   string  $prefix  The class prefix. Optional
	 * @param   array   $config  Configuration array for model. Optional
	 *
	 * @return object	The model
	 *
	 * @since	1.6
	 */
	public function getModel($name = 'Transactions', $prefix = 'Site', $config = array())
	{
		return parent::getModel($name, $prefix, array('ignore_request' => true));
	}

	/**
	 * Create Audit Extract data
	 * @return void
	 * @throws Exception
	 */
	public function auditExtract()
	{
		// Initialise variables.
		$app = Factory::getApplication();

		// Checking if the user can remove object
		$user    = GafinanceHelper::getSpecificUser();

		if ($user->authorise('core.treasury', 'com_gafinance'))
		{
			$model = $this->getModel('Transactions', 'Site');

			// Attempt to save the data.
			$return = $model->auditExtract($user);

			// Check for errors.
			if ($return === false) {
				$this->setMessage(Text::sprintf('Extract failed', $model->getError()), 'warning');
			} else {
                $app->enqueueMessage(Text::_('COM_GAFINANCE_EXTRACT_SUCCESSFULLY'), 'success');
			}

			// Redirect to the list screen.
			$menu = Factory::getApplication()->getMenu();
			$item = $menu->getActive();
			$this->setRedirect(Route::_($item->link, false));
		} else {
			throw new GenericDataException(Text::_('COM_GAFINANCE_NO_AUTH'),403);
		}
	}

}
