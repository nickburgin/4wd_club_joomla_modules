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
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Session\Session;
use \GlennArkell\Component\Gatripsys\Administrator\Helper\GatripsysHelper;

/**
 * Controller class.
 * @since  1.6
 */
class InvoiceController extends BaseController
{
	/**
	 * Method to check out an item for editing and redirect to the edit form.
	 * @return void
	 * @since    1.6
	 */
	public function edit()
	{
		$app = Factory::getApplication();

		// Get the previous edit id (if any) and the current edit id.
		$previousId = (int) $app->getUserState('com_gatripsys.edit.invoice.id');
		$editId     = $app->input->getInt('id', 0);

		// Set the user id for the user to edit in the session.
		$app->setUserState('com_gatripsys.edit.invoice.id', $editId);

		// Get the model.
		$model = $this->getModel('Invoice', 'Site');

		// Check out the item
		if ($editId) {
			$model->checkout($editId);
		}

		// Check in the previous user.
		if ($previousId && $previousId !== $editId) {
			$model->checkin($previousId);
		}

		// Redirect to the edit screen.
		$this->setRedirect(Route::_('index.php?option=com_gatripsys&view=invoiceform&layout=edit', false));
	}

	/**
	 * Method to save a user's profile data.
	 * @return    void
	 * @throws Exception
	 * @since    1.6
	 */
	public function publish()
	{
		// Initialise variables.
		$app = Factory::getApplication();

		// Checking if the user can remove object
		$user = GatripsysHelper::getSpecificUser();

		if ($user->authorise('core.edit', 'com_gatripsys') || $user->authorise('core.edit.state', 'com_gatripsys')) {
			$model = $this->getModel('Invoice', 'Site');

			// Get the user data.
			$id    = $app->input->getInt('id');
			$state = $app->input->getInt('state');

			// Attempt to save the data.
			$return = $model->publish($id, $state);

			// Check for errors.
			if ($return === false) {
				$this->setMessage(Text::sprintf('Save failed: %s', $model->getError()), 'warning');
			}

			// Clear the profile id from the session.
			$app->setUserState('com_gatripsys.edit.invoice.id', null);

			// Flush the data from the session.
			$app->setUserState('com_gatripsys.edit.invoice.data', null);

			// Redirect to the list screen.
			$this->setMessage(Text::_('COM_GATRIPSYS_ITEM_SAVED_SUCCESSFULLY'));
			$menu = Factory::getApplication()->getMenu();
			$item = $menu->getActive();

			if (!$item) {
				// If there isn't any menu item active, redirect to list view
				$this->setRedirect(Route::_('index.php?option=com_gatripsys&view=invoices', false));
			} else {
                $this->setRedirect(Route::_('index.php?Itemid='. $item->id, false));
			}
		} else {
			$this->setMessage(Text::_('COM_GATRIPSYS_NOT_AUTHORISED'), 'warning');
		}
	}

	/**
	 * Remove data
	 * @return void
	 * @throws Exception
	 */
	public function remove()
	{
		// Initialise variables.
		$app = Factory::getApplication();

		// Checking if the user can remove object
		$user = GatripsysHelper::getSpecificUser();

		if ($user->authorise('core.invoice', 'com_gatripsys')) {

			$model = $this->getModel('Invoice', 'Site');

			// Get the user data.
			$id = $app->input->getInt('id', 0);

			// Attempt to save the data.
			$return = $model->delete($id);

			// Check for errors.
			if ($return === false) {
				$this->setMessage(Text::sprintf('Delete failed', $model->getError()), 'warning');
			} else {
				if ($return) {
					$model->checkin($return);
				}
                $app->enqueueMessage(Text::_('COM_GATRIPSYS_ITEM_DELETED_SUCCESSFULLY'), 'success');
			}
		} else {
			$this->setMessage(Text::_('COM_GATRIPSYS_NOT_AUTHORISED'), 'warning');
		}

		// Redirect to the list screen.
		$app->redirect(Route::_('index.php?option=com_gatripsys&view=invoices', false));
	}

	/**
	 * Method to abort current operation
	 * @return void
	 * @throws Exception
	 */
	public function cancel($key = NULL)
	{
		$app = Factory::getApplication();

		$menu = Factory::getApplication()->getMenu();
		$item = $menu->getActive();
		$url  = (empty($item->link) ? 'index.php?option=com_gatripsys&view=invoices' : $item->link);
		$this->setRedirect(Route::_($url, false));
	}

	/**
	 * Method to resend invoice pdf to user
	 */
	public function resendInv()
	{
		// Check for request forgeries.
		Session::checkToken('get') or jexit(Text::_('JINVALID_TOKEN'));

		// Initialise variables.
		$app	= Factory::getApplication();
		$model	= $this->getModel('Invoice', 'Site');

		// Get the data from the link GET
		$data = array();
		$data['id'] = Factory::getApplication()->input->get('id');

        // Now add the loaded data to the database via a function in the model
        $sent	= $model->resendInv($data);

    	// check if ok and display appropriate message
        if ($sent) {
            // Redirect to the list screen.
            $app->enqueueMessage(Text::_('COM_GATRIPSYS_ITEM_RESENT_SUCCESSFULLY'), 'notice');
            $item = Factory::getApplication()->getMenu()->getActive();
            $this->setRedirect(Route::_($item->link, false));
        } else {
            echo "<h2>Invoice Resend Failed</h2>";
        }

		return true;
	}

	/**
	 * Method to create a new invoice record
	 */
	public function newinv()
	{
		// Check for request forgeries.
		Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

		// Initialise variables.
		$app	= Factory::getApplication();
		$model	= $this->getModel('Invoice', 'Site');

		// Get the user data.
		$data = $app->input->get('jform', array(), 'array');
		
		$auto_users  = ComponentHelper::getParams('com_gatripsys')->get('auto_users',0);

		// test for auto create new users and if so, register the new member
		if (isset($data['nm_name']) && $auto_users) {
			$new_rec = array();
			$new_rec['user_id'] = $model->setupNewUserData($data);
			$new_rec['invoice_amt'] = 0.00;
			$data = $new_rec;
		}

		// Attempt to save the data.
		$return	= $model->save($data);

		// Check for errors.
		if ($return === false) {
			// Save the data in the session.
			$app->setUserState('com_gatripsys.edit.invoice.data', $data);

			// Redirect back to the edit screen.
			$id = (int)$app->getUserState('com_gatripsys.edit.invoice.id');
			$this->setMessage(Text::sprintf('Save failed', $model->getError()), 'warning');
			$this->setRedirect(Route::_('index.php?option=com_gatripsys&view=invoice&layout=edit&id=0', false));
			return false;
		}

        // Clear the profile id from the session.
        $app->setUserState('com_gatripsys.edit.invoice.id', null);

        // Redirect to the list screen.
        $this->setMessage(Text::_('COM_GATRIPSYS_ITEM_SAVED_SUCCESSFULLY'));
        //$this->setRedirect(Route::_('index.php?option=com_gatripsys&view=invoices', false));
        $item = Factory::getApplication()->getMenu()->getActive();
        $this->setRedirect(Route::_($item->link, false));

		// Flush the data from the session.
		$app->setUserState('com_gatripsys.edit.invoice.data', null);
	}

    public function generateInv()
	{
		// Check for request forgeries.
		Session::checkToken('get') or jexit(Text::_('JINVALID_TOKEN'));

		// Initialise variables.
		$app   = Factory::getApplication();
		$model = $this->getModel('Invoice', 'Site');

		// Attempt to generate new invoices.
		$return = $model->generateInv();

		// Redirect to the list screen.
		if ($return) {
			$this->setMessage(Text::_('COM_GATRIPSYS_INV_GEN_SUCCESSFULLY'));
		}
		$this->setRedirect(Route::_('index.php?option=com_gatripsys&view=invoices', false));

    }
}
