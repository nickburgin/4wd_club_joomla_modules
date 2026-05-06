<?php

/**
 * @version    5.3.0
 * @package    Com_Gatripsys
 * @author     Glenn Arkell <glenn@glennarkell.com.au>
 * @copyright  2018 Glenn Arkell
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace GlennArkell\Component\Gatripsys\Site\Controller;

// No direct access
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;

/**
 * Controller class.
 * @since  1.6
 */
class InvoiceformController extends FormController
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
		$previousId = (int) $app->getUserState('com_gatripsys.edit.invoice.id');
		$editId     = $app->input->getInt('id', 0);

		// Set the user id for the user to edit in the session.
		$app->setUserState('com_gatripsys.edit.invoice.id', $editId);

		// Get the model.
		$model = $this->getModel('Invoiceform', 'Site');

		// Check out the item
		if ($editId) {
			$model->checkout($editId);
		}

		// Check in the previous user.
		if ($previousId) {
			$model->checkin($previousId);
		}

		// Redirect to the edit screen.
		$this->setRedirect(Route::_('index.php?option=com_gatripsys&view=invoiceform&layout=edit', false));
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
		Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

		// Initialise variables.
		$app   = Factory::getApplication();
		$model = $this->getModel('Invoiceform', 'Site');

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
			$app->setUserState('com_gatripsys.edit.invoice.data', $jform);

			// Redirect back to the edit screen.
			$id = (int) $app->getUserState('com_gatripsys.edit.invoice.id');
			$this->setRedirect(Route::_('index.php?option=com_gatripsys&view=invoiceform&layout=edit&id=' . $id, false));

			$this->redirect();
		}

		// test for new user to be created 
		if ($data['user_id'] == 0) {
			$data['user_id'] = GaregistrationHelper::setupNewUserData($data);
		}


		// Attempt to save the data.
		if ($data['user_id']) {
			$return = $model->save($data);
		} else {
			$return = false;
		}

		// Check for errors.
		if ($return === false) {
			// Save the data in the session.
			$app->setUserState('com_gatripsys.edit.invoice.data', $data);

			// Redirect back to the edit screen.
			$id = (int) $app->getUserState('com_gatripsys.edit.invoice.id');
			$this->setMessage(Text::sprintf('Save failed', $model->getError()), 'warning');
			$this->setRedirect(Route::_('index.php?option=com_gatripsys&view=invoiceform&layout=edit&id=' . $id, false));
		} else {
			$app->setUserState('com_gatripsys.edit.invoice.id', null);
	
			// Redirect to the list screen.
			$this->setMessage(Text::_('COM_GATRIPSYS_ITEM_SAVED_SUCCESSFULLY'));
			$menu = Factory::getApplication()->getMenu();
			$item = $menu->getActive();
			$url  = (empty($item->link) ? 'index.php?option=com_gatripsys&view=invoices' : $item->link);
			$this->setRedirect(Route::_($url, false));
	
			// Flush the data from the session.
			$app->setUserState('com_gatripsys.edit.invoice.data', null);
		}
	}

	/**
	 * Method to abort current operation
	 * @return void
	 * @throws Exception
	 */
	public function cancel($key = NULL)
	{
		$app = Factory::getApplication();

		// Get the current edit id.
		$editId = (int) $app->getUserState('com_gatripsys.edit.invoice.id');

		// Get the model.
		$model = $this->getModel('Invoiceform', 'Site');

		// Check in the item
		if ($editId) {
			$model->checkin($editId);
		}

		$menu = Factory::getApplication()->getMenu();
		$item = $menu->getActive();
		$url  = (empty($item->link) ? 'index.php?option=com_gatripsys&view=invoices' : $item->link);
		$this->setRedirect(Route::_($url, false));
	}

	/**
	 * Method to remove data
	 * @return void
	 * @throws Exception
     * @since 1.6
	 */
	public function remove()
    {
        $app   = Factory::getApplication();
        $model = $this->getModel('Invoiceform', 'Site');
        $pk    = $app->input->getInt('id');

        // Attempt to perform action
        try
        {
            $return = $model->delete($pk);

            // Clear the profile id from the session.
            $app->setUserState('com_gatripsys.edit.invoice.id', null);

            $menu = $app->getMenu();
            $item = $menu->getActive();
            $url = (empty($item->link) ? 'index.php?option=com_gatripsys&view=invoices' : $item->link);

            // Redirect to the list screen
            $this->setMessage(Text::_('COM_GATRIPSYS_ITEM_DELETED_SUCCESSFULLY'));
            $this->setRedirect(Route::_($url, false));

            // Flush the data from the session.
            $app->setUserState('com_gatripsys.edit.invoice.data', null);
        }
        catch (Exception $e)
        {
            $errorType = ($e->getCode() == '404') ? 'error' : 'warning';
            $this->setMessage($e->getMessage(), $errorType);
            $this->setRedirect('index.php?option=com_gatripsys&view=invoices');
        }
	}

    public function markAsPaid()
	{
		// Check for request forgeries.
		//Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

		// Initialise variables.
		$app   = Factory::getApplication();
		$model = $this->getModel('Invoiceform', 'Site');

		// Get the user data.
		$data = $app->input->get('jform', array(), 'array');

		// Attempt to save the data.
		$return = $model->markAsPaid($data);

		// Clear the record from the session.
		$app->setUserState('com_gatripsys.edit.invoice.id', null);

		// Redirect to the list screen.
		$this->setMessage(Text::_('COM_GATRIPSYS_MARKPAID_SUCCESSFULLY').' - '.$data['user_name']);
		$this->setRedirect(Route::_('index.php?option=com_gatripsys&view=invoices', false));

		// Flush the data from the session.
		$app->setUserState('com_gatripsys.edit.invoice.data', null);
    }

}
