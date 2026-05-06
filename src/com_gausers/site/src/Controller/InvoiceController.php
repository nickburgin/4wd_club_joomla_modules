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
use \Joomla\CMS\MVC\Controller\BaseController;
use \Joomla\CMS\Router\Route;
use \Joomla\CMS\Uri\Uri;
use \Joomla\CMS\Component\ComponentHelper;
use \Joomla\Utilities\ArrayHelper;
use \GlennArkell\Component\Gausers\Administrator\Helper\GausersHelper;

/**
 * Item class.
 * @since  1.6.0
 */
class InvoiceController extends BaseController
{
	/**
	 * Method to check out an item for editing and redirect to the edit form.
	 * @return void
	 * @since    1.6
     * @throws Exception
	 */
	public function edit()
	{
		$app = Factory::getApplication();

		// Get the previous edit id (if any) and the current edit id.
		$previousId = (int) $app->getUserState('com_gausers.edit.invoice.id');
		$editId     = $app->input->getInt('id', 0);

		// Set the user id for the user to edit in the session.
		$app->setUserState('com_gausers.edit.invoice.id', $editId);

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
		$this->setRedirect(Route::_('index.php?option=com_gausers&view=invoiceform&layout=edit', false));
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
		$user = GausersHelper::getSpecificUser();

		if ($user->authorise('core.edit', 'com_gausers') || $user->authorise('core.edit.state', 'com_gausers'))
		{
			$model = $this->getModel('Invoice', 'Site');

			// Get the user data.
			$id    = $app->input->getInt('id');
			$state = $app->input->getInt('state');

			// Attempt to save the data.
			$return = $model->publish($id, $state);

			// Check for errors.
			if ($return === false)
			{
				$this->setMessage(Text::sprintf('Save failed: %s', $model->getError()), 'warning');
			}

			// Clear the profile id from the session.
			$app->setUserState('com_gausers.edit.invoice.id', null);

			// Flush the data from the session.
			$app->setUserState('com_gausers.edit.invoice.data', null);

			// Redirect to the list screen.
			$this->setMessage(Text::_('COM_GAUSERS_ITEM_SAVED_SUCCESSFULLY'));
			$menu = Factory::getApplication()->getMenu();
			$item = $menu->getActive();

			if (!$item) {
				// If there isn't any menu item active, redirect to list view
				$this->setRedirect(Route::_('index.php?option=com_gausers&view=invoices', false));
			} else {
                $this->setRedirect(Route::_('index.php?Itemid='. $item->id, false));
			}
		} else {
			throw new \Exception(500);
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
		$user = GausersHelper::getSpecificUser();

		if ($user->authorise('core.delete', 'com_gausers')) {
			$model = $this->getModel('Invoice', 'Site');

			// Get the user data.
			$id = $app->input->getInt('id', 0);

			// Attempt to save the data.
			$return = $model->delete($id);

			// Check for errors.
			if ($return === false) {
				$this->setMessage(Text::sprintf('Delete failed', $model->getError()), 'warning');
			} else {
				// Check in the profile.
				if ($return) {
					$model->checkin($return);
				}

                $app->setUserState('com_gausers.edit.invoice.id', null);
                $app->setUserState('com_gausers.edit.invoice.data', null);

                $app->enqueueMessage(Text::_('COM_GAUSERS_ITEM_DELETED_SUCCESSFULLY'), 'success');
                $app->redirect(Route::_('index.php?option=com_gausers&view=invoices', false));
			}

			// Redirect to the list screen.
            $item = Factory::getApplication()->getMenu()->getActive();
            $this->setRedirect(Route::_($item->link, false));
		} else {
			throw new \Exception(500);
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

		$this->setRedirect(Route::_('index.php?option=com_gausers&view=invoices', false));
	}

	/**
	 * Method to resend invoice pdf to user
	 */
	public function resendInv()
	{
		// Check for request forgeries.
		$this->checkToken('get');

		// Initialise variables.
		$app	= Factory::getApplication();
		$model	= $this->getModel('Invoice', 'Site');

		// Get the data from the link GET
		$data = array();
		$data['id'] = $app->input->get('id');

        // Now add the loaded data to the database via a function in the model
        $sent	= $model->resendInv($data);

    	// check if ok and display appropriate message
        if ($sent) {
            // Redirect to the list screen.
            $app->enqueueMessage(Text::_('COM_GAUSERS_ITEM_RESENT_SUCCESSFULLY'), 'notice');
        } else {
            $app->enqueueMessage(Text::_('COM_GAUSERS_ITEM_RESEND_FAILED'), 'warning');
        }
		$this->setRedirect(Route::_('index.php?option=com_gausers&view=invoices', false));

		return true;
	}

	/**
	 * Method to create a new invoice record
	 */
	public function newinv()
	{
		// Check for request forgeries.
		$this->checkToken();

		// Initialise variables.
		$app	= Factory::getApplication();
		$model	= $this->getModel('Invoice', 'Site');

		// Get the user data.
		$data = $app->input->get('jform', array(), 'array');
		
		$auto_users  = ComponentHelper::getParams('com_gausers')->get('auto_users',0);

		// test for auto create new users and if so, register the new member
		if (isset($data['name']) && $auto_users) {
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
			$app->setUserState('com_gausers.edit.invoice.data', $data);

			// Redirect back to the edit screen.
			$id = (int)$app->getUserState('com_gausers.edit.invoice.id');
			$this->setMessage(Text::sprintf('Save failed', $model->getError()), 'warning');
			$this->setRedirect(Route::_('index.php?option=com_gausers&view=invoice&layout=edit&id=0', false));
			return false;
		}

        // Clear the profile id from the session.
        $app->setUserState('com_gausers.edit.invoice.id', null);

        // Redirect to the list screen.
        $this->setMessage(Text::_('COM_GAUSERS_ITEM_SAVED_SUCCESSFULLY'));
        //$this->setRedirect(Route::_('index.php?option=com_gausers&view=invoices', false));
        $item = Factory::getApplication()->getMenu()->getActive();
        $this->setRedirect(Route::_($item->link, false));

		// Flush the data from the session.
		$app->setUserState('com_gausers.edit.invoice.data', null);
	}

    public function generateInv()
	{
		// Check for request forgeries.
		$this->checkToken('get');

		// Initialise variables.
		$app   = Factory::getApplication();
		$model	= $this->getModel('Invoice', 'Site');

		// Attempt to generate new invoices.
		$return = $model->generateInv();

		// Redirect to the list screen.
		if ($return) {
			$this->setMessage(Text::_('COM_GAUSERS_INV_GEN_SUCCESSFULLY'));
		}
		$this->setRedirect(Route::_('index.php?option=com_gausers&view=invoices', false));

    }

}
