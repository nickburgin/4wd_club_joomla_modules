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
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\Utilities\ArrayHelper;

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
		$previousId = (int) $app->getUserState('com_gafinance.edit.invoice.id');
		$editId     = $app->input->getInt('id', 0);

		// Set the user id for the user to edit in the session.
		$app->setUserState('com_gafinance.edit.invoice.id', $editId);

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
		$this->setRedirect(Route::_('index.php?option=com_gafinance&view=invoiceform&layout=edit', false));
	}

	/**
	 * Method to save data
	 * @return    void
	 * @throws Exception
	 * @since    1.6
	 */
	public function publish()
	{
		// Initialise variables.
		$app = Factory::getApplication();

		// Checking if the user can remove object
		$user = Factory::getApplication()->getIdentity();

		if ($user->authorise('core.edit', 'com_gafinance') || $user->authorise('core.edit.state', 'com_gafinance')) {
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
			$app->setUserState('com_gafinance.edit.invoice.id', null);

			// Flush the data from the session.
			$app->setUserState('com_gafinance.edit.invoice.data', null);

			// Redirect to the list screen.
			$this->setMessage(Text::_('COM_GAFINANCE_ITEM_SAVED_SUCCESSFULLY'));
			$menu = Factory::getApplication()->getMenu();
			$item = $menu->getActive();

			if (!$item) {
				// If there isn't any menu item active, redirect to list view
				$this->setRedirect(Route::_('index.php?option=com_gafinance&view=invoices', false));
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
		$user = Factory::getApplication()->getIdentity();

		if ($user->authorise('core.delete', 'com_gafinance')) {
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

                $app->setUserState('com_gafinance.edit.invoice.id', null);
                $app->setUserState('com_gafinance.edit.invoice.data', null);

                $app->enqueueMessage(Text::_('COM_GAFINANCE_ITEM_DELETED_SUCCESSFULLY'), 'success');
                $app->redirect(Route::_('index.php?option=com_gafinance&view=invoices', false));
			}

			// Redirect to the list screen.
			$menu = Factory::getApplication()->getMenu();
			$item = $menu->getActive();
			$this->setRedirect(Route::_($item->link, false));
		} else {
			throw new \Exception(500);
		}
	}

	/**
	 * Generate a PDF of invoice data
	 */
	public function genInv()
	{
		$app = Factory::getApplication();

		// Get the current edit id.
		$editId	= Factory::getApplication()->input->getInt('id', null, 'array');

		// Set the user id for the user to edit in the session.
		$app->setUserState('com_gafinance.edit.invoice.id', $editId);

		// Get the model.
		$model = $this->getModel('Invoice', 'Site');

		// generate the PDF
		if ($editId) {
            $client = $model->generateInv($editId);
	        $app->enqueueMessage(Text::sprintf('COM_GAFINANCE_INVOICE_PDF_CREATED', $client), 'message');
		}

        $app->setUserState('com_gafinance.edit.invoice.id', null);
        $this->setRedirect(Route::_('index.php?option=com_gafinance&view=invoices', false));

	}

	/**
	 * Send PDF to the patron/client
	 */
	public function sendInv()
	{
		$app = Factory::getApplication();

		// Get the current edit id.
		$editId	= $app->input->getInt('id', null, 'array');
		$smail	= $app->input->getInt('smail');

		// Set the user id for the user to edit in the session.
		$app->setUserState('com_gafinance.edit.invoice.id', $editId);

		// Get the model.
		$model = $this->getModel('Invoice', 'Site');

		// send the PDF
		if ($editId) {
            $client = $model->sendInv($editId, $smail);
	        if ($smail) {
				$app->enqueueMessage(Text::sprintf('COM_GAFINANCE_INVOICE_PDF_POSTED', $client), 'message');
			} else {
				$app->enqueueMessage(Text::sprintf('COM_GAFINANCE_INVOICE_PDF_SENT', $client), 'message');
			}
		}

        $app->setUserState('com_gafinance.edit.invoice.id', null);
        $this->setRedirect(Route::_('index.php?option=com_gafinance&view=invoices', false));

	}

	/**
	 * Invoice to be marked as paid
	 */
	public function markPaid()
	{
		$app = Factory::getApplication();

		// Get the current edit id.
		$editId	= Factory::getApplication()->input->getInt('id', null, 'array');

		// Set the user id for the user to edit in the session.
		$app->setUserState('com_gafinance.edit.invoice.id', $editId);

		// Get the model.
		$model = $this->getModel('Invoice', 'Site');

		// generate the PDF
		if ($editId) {
            $client = $model->markPaid($editId);
	        $app->enqueueMessage(Text::sprintf('COM_GAFINANCE_INVOICE_MARKED_PAID', $client), 'message');
		}

        $app->setUserState('com_gafinance.edit.invoice.id', null);
        $this->setRedirect(Route::_('index.php?option=com_gafinance&view=invoices', false));

	}

	/**
	 * Cancel the invoice
	 */
	public function cancelInvoice()
	{
		$app = Factory::getApplication();

		// Get the current edit id.
		$editId	= Factory::getApplication()->input->getInt('id', null, 'array');

		// Set the user id for the user to edit in the session.
		$app->setUserState('com_gafinance.edit.invoice.id', $editId);

		// Get the model.
		$model = $this->getModel('Invoice', 'Site');

		// generate the PDF
		if ($editId) {
            $client = $model->cancelInvoice($editId);
	        $app->enqueueMessage(Text::sprintf('COM_GAFINANCE_INVOICE_CANCELLED', $client), 'message');
		} else {
			$this->setMessage(Text::sprintf('Invoice Not Found', 'danger'));
		}

        $app->setUserState('com_gafinance.edit.invoice.id', null);
        $this->setRedirect(Route::_('index.php?option=com_gafinance&view=invoices', false));

	}

	/**
	 * Archive the complete invoice
	 */
	public function archiveInvoice()
	{
		$app = Factory::getApplication();

		// Get the current edit id.
		$editId	= Factory::getApplication()->input->getInt('id', null, 'array');

		// Set the user id for the user to edit in the session.
		$app->setUserState('com_gafinance.edit.invoice.id', $editId);

		// Get the model.
		$model = $this->getModel('Invoice', 'Site');

		// generate the PDF
		if ($editId) {
            $client = $model->archiveInvoice($editId);
	        $app->enqueueMessage(Text::sprintf('COM_GAFINANCE_INVOICE_ARCHIVED', $client), 'message');
		} else {
			$this->setMessage(Text::sprintf('Invoice Not Found', 'danger'));
		}

        $app->setUserState('com_gafinance.edit.invoice.id', null);
        $this->setRedirect(Route::_('index.php?option=com_gafinance&view=invoices', false));

	}
}
