<?php

/**
 * @version    6.0.0
 * @package    Com_Gausers
 * @author     Glenn Arkell <glenn@glennarkell.com.au>
 * @copyright  2018 Glenn Arkell
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace GlennArkell\Component\Gausers\Site\Controller;

// No direct access
\defined('_JEXEC') or die;

use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\Utilities\ArrayHelper;
use \GlennArkell\Component\Gausers\Administrator\Helper\GausersHelper;

/**
 * Form class.
 * @since  1.6.0
 */
class NewmemberformController extends FormController
{
	/**
	 * Method to check out an item for editing and redirect to the edit form.
	 * @return void
	 * @since    1.6
	 */
	public function edit($key = NULL, $urlVar = NULL)
	{
		$app = Factory::getApplication();

		// Get the previous edit id (if any) and the current edit id.
		$previousId = (int) $app->getUserState('com_gausers.edit.newmember.id');
		$editId     = $app->input->getInt('id', 0);

		// Set the user id for the user to edit in the session.
		$app->setUserState('com_gausers.edit.newmember.id', $editId);

		// Get the model.
		$model = $this->getModel('Newmemberform', 'Site');

		// Redirect to the edit screen.
		$this->setRedirect(Route::_('index.php', false));
	}

	/**
	 * Method to save a user's profile data.
	 * @return void
	 * @throws Exception
	 * @since  1.6
	 */
	public function save($key = NULL, $urlVar = NULL)
	{
		// Check for request forgeries.
		$this->checkToken();

		// Initialise variables.
		$app   = Factory::getApplication();
		$model = $this->getModel('Newmemberform', 'Site');

		// Get the user data.
		$data = Factory::getApplication()->input->get('jform', array(), 'array');
        
		// Validate the posted data.
		$form = $model->getForm();

		if (!$form) {
			throw new \Exception($model->getError(), 500);
		}

		// Validate the posted data.
		$data = $model->validate($form, $data);

		// Check for errors.
		if ($data === false) {
			// Get the validation messages.
			$errors = $model->getErrors();

			// Push up to three validation messages out to the user.
			for ($i = 0, $n = count($errors); $i < $n && $i < 3; $i++) {
				if ($errors[$i] instanceof Exception) {
					$app->enqueueMessage($errors[$i]->getMessage(), 'warning');
				} else {
					$app->enqueueMessage($errors[$i], 'warning');
				}
			}

			$input = $app->input;
			$jform = $input->get('jform', array(), 'ARRAY');

			// Save the data in the session.
			$app->setUserState('com_gausers.edit.newmember.data', $jform);

			// Redirect back to the edit screen.
			$this->setRedirect(Route::_('index.php?option=com_gausers&view=newmemberform', false));

			$this->redirect();
		}
        $this->setMessage(Text::_('COM_GAUSERS_ITEM_SAVED_SUCCESSFULLY'));
	}

	/**
	 * Method to abort current operation
	 * @return void
	 * @throws Exception
	 */
	public function cancel($key = NULL)
	{
		//$menu = Factory::getApplication()->getMenu();
		//$item = $menu->getActive();
		//$url  = (empty($item->link) ? 'index.php' : $item->link);
		$this->setRedirect(Route::_(Uri::base(), false));
		$this->redirect();

	}

	/**
	 * Method to remove data
	 * @return void
	 * @throws Exception
     * @since 1.6
	 */
	public function remove()
    {
		$menu = Factory::getApplication()->getMenu();
		$item = $menu->getActive();
		$url  = (empty($item->link) ? 'index.php' : $item->link);
		$this->setRedirect(Route::_($url, false));
	}

	public function newMbrApplication()
	{
		// Check for request forgeries.
		$this->checkToken();

		// Initialise variables.
		$app   = Factory::getApplication();
		$model = $this->getModel('Newmemberform', 'Site');

		// Get the user data.
		$data = Factory::getApplication()->input->get('jform', array(), 'array');
        
		// Validate the posted data.
		$form = $model->getForm();

		if (!$form) {
			throw new \Exception($model->getError(), 500);
		}

		// Validate the posted data.
		$data = $model->validate($form, $data);

		// Check for errors.
		if ($data === false) {
			// Get the validation messages.
			$errors = $model->getErrors();

			// Push up to three validation messages out to the user.
			for ($i = 0, $n = count($errors); $i < $n && $i < 3; $i++) {
				if ($errors[$i] instanceof Exception) {
					$app->enqueueMessage($errors[$i]->getMessage(), 'warning');
				} else {
					$app->enqueueMessage($errors[$i], 'warning');
				}
			}

			$input = $app->input;
			$jform = $input->get('jform', array(), 'ARRAY');

			// Save the data in the session.
			$app->setUserState('com_gausers.edit.newmember.data', $jform);

			// Redirect back to the edit screen.
			$this->setRedirect(Route::_('index.php?option=com_gausers&view=newmemberform', false));

			$this->redirect();
		}

		// Attempt to create an application using the data.
		$return	= $model->createNewMbrForm($data);

 		if ($return === false) {
 			$app->enqueueMessage(Text::_('COM_GAUSERS_ITEM_SAVED_FAILED'), 'warning');
		} else {
 			$app->enqueueMessage(Text::_('COM_GAUSERS_THANKS_FOR_APPLYING'), 'success');
            // Flush the data from the session.
    		$app->setUserState('com_gausers.edit.newmember.data', null);
		}
		
		$this->setRedirect(Route::_(Uri::base(), false));
		$this->redirect();

	}

}
