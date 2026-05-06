<?php
/**
 * @version    5.3.0
 * @package    com_gatripsys
 * @author     Glenn Arkell <glenn@glennarkell.com.au>
 * @copyright  2021 Glenn Arkell
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace GlennArkell\Component\Gatripsys\Site\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\Utilities\ArrayHelper;
use \GlennArkell\Component\Gatripsys\Administrator\Helper\GatripsysHelper;
use \GlennArkell\Component\Gatripsys\Administrator\Helper\GainvoiceHelper;

/**
 * Item class.
 * @since  1.6.0
 */
class AttendeeController extends BaseController
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
		$previousId = (int) $app->getUserState('com_gatripsys.edit.attendee.id');
		$editId     = $app->input->getInt('id', 0);

		// Set the record id to edit in the session.
		$app->setUserState('com_gatripsys.edit.attendee.id', $editId);

		// Get the model.
		$model = $this->getModel('Attendee', 'Site');

		// Check out the record
		if ($editId) {
			$model->checkout($editId);
		}

		// Check in the previous record.
		if ($previousId && $previousId !== $editId) {
			$model->checkin($previousId);
		}

		// Redirect to the edit screen.
		$this->setRedirect(Route::_('index.php?option=com_gatripsys&view=attendeeform&layout=edit', false));
	}

	/**
	 * Remove data
	 * @return void
	 * @throws Exception
	 */
	public function removeAttendee()
	{
		// Initialise variables.
		$app = Factory::getApplication();

		// Checking if the user can remove object
		$user = GatripsysHelper::getSpecificUser();

		if ($user->authorise('core.create', 'com_gatripsys'))
		{
			$model = $this->getModel('Attendee', 'Site');

			// Get the user submitted data.
			$id = $app->input->getInt('id', 0);
			$trip_id = $app->input->getInt('trip_id', 0);

			// Attempt to save the data.
			$return = $model->publish($id, 2);

			// Check for errors.
			if ($return === false) {
				$this->setMessage(Text::sprintf(Text::_('COM_GATRIPSYS_ITEM_DELETEDATTEND_FAILED'), $model->getError()), 'warning');
			} else {
                $app->enqueueMessage(Text::_('COM_GATRIPSYS_ITEM_DELETEDATTEND_SUCCESSFULLY'), 'success');
			}

			// Redirect to the list screen.
			$app->redirect(Route::_('index.php?option=com_gatripsys&view=trip&id='.(int)$trip_id, false));
		} else {
			throw new \Exception(500);
		}
	}

	/**
	 * Accept Attendance
	 * @return void
	 * @throws Exception
	 */
	public function acceptAttendee()
	{
		// Initialise variables.
		$app = Factory::getApplication();

		// Checking if the user can remove object
		$user = GatripsysHelper::getSpecificUser();

		if ($user->authorise('core.create', 'com_gatripsys'))
		{
			$model = $this->getModel('Attendee', 'Site');

			// Get the user submitted data.
			$id = $app->input->getInt('id', 0);
			$trip_id = $app->input->getInt('trip_id', 0);

			// Attempt to save the data.
			$return = $model->publish($id, 1);

			// Check for errors.
			if ($return === false) {
				$this->setMessage(Text::sprintf(Text::_('COM_GATRIPSYS_ITEM_ATTEND_FAILED'), $model->getError()), 'warning');
			} else {
                $app->enqueueMessage(Text::_('COM_GATRIPSYS_ITEM_ATTEND_SUCCESSFULLY'), 'success');
			}

			// Redirect to the list screen.
			$app->redirect(Route::_('index.php?option=com_gatripsys&view=trip&id='.(int)$trip_id, false));
		} else {
			throw new \Exception(500);
		}
	}

	/**
	 * Accept Attendance
	 * @return void
	 * @throws Exception
	 */
	public function unacceptAttendee()
	{
		// Initialise variables.
		$app = Factory::getApplication();

		// Checking if the user can remove object
		$user = GatripsysHelper::getSpecificUser();

		if ($user->authorise('core.create', 'com_gatripsys'))
		{
			$model = $this->getModel('Attendee', 'Site');

			// Get the user submitted data.
			$id = $app->input->getInt('id', 0);
			$trip_id = $app->input->getInt('trip_id', 0);

			// Attempt to save the data.
			$return = $model->publish($id, 0);

			// Check for errors.
			if ($return === false) {
				$this->setMessage(Text::sprintf(Text::_('COM_GATRIPSYS_ITEM_UNATTEND_FAILED'), $model->getError()), 'warning');
			} else {
                $app->enqueueMessage(Text::_('COM_GATRIPSYS_ITEM_UNATTEND_SUCCESSFULLY'), 'success');
			}

			// Redirect to the list screen.
			$app->redirect(Route::_('index.php?option=com_gatripsys&view=trip&id='.(int)$trip_id, false));
		} else {
			throw new \Exception(500);
		}
	}

	/**
	 * Reject Attendance
	 * @return void
	 * @throws Exception
	 */
	public function rejectAttendee()
	{
		// Initialise variables.
		$app = Factory::getApplication();

		// Checking if the user can remove object
		$user = GatripsysHelper::getSpecificUser();

		if ($user->authorise('core.create', 'com_gatripsys'))
		{
			$model = $this->getModel('Attendee', 'Site');

			// Get the user submitted data.
			$id = $app->input->getInt('id', 0);
			$trip_id = $app->input->getInt('trip_id', 0);

			// Attempt to save the data.
			$return = $model->publish($id, -2);

			// Check for errors.
			if ($return === false) {
				$this->setMessage(Text::sprintf(Text::_('COM_GATRIPSYS_ITEM_ATTEND_FAILED'), $model->getError()), 'warning');
			} else {
                $app->enqueueMessage(Text::_('COM_GATRIPSYS_ITEM_ATTEND_REJECTED'), 'success');
			}

			// Redirect to the list screen.
			$app->redirect(Route::_('index.php?option=com_gatripsys&view=trip&id='.(int)$trip_id, false));
		} else {
			throw new \Exception(500);
		}
	}

	/**
	 * Method to mark all attendees as approved
	 * @return void
	 * @throws Exception
	 * @since  5.3.0
	 */
	public function approveAll()
	{
		// Initialise variables.
		$app = Factory::getApplication();

		// Checking if the user can remove object
		$user = GatripsysHelper::getSpecificUser();

		if ($user->authorise('core.create', 'com_gatripsys'))
		{
			$model = $this->getModel('Attendee', 'Site');

			// Get the user submitted data.
			$trip_id = $app->input->getInt('trip_id', 0);

			// Attempt to save the data.
			$return = $model->approveAll($trip_id);

			// Check for errors.
			if ($return === false) {
				$this->setMessage(Text::sprintf(Text::_('COM_GATRIPSYS_APPROVALS_FAILED'), $model->getError()), 'warning');
			} else {
                $app->enqueueMessage(Text::_('COM_GATRIPSYS_APPROVALS_SUCCESSFUL'), 'success');
			}

			// Redirect to the list screen.
			$app->redirect(Route::_('index.php?option=com_gatripsys&view=trip&id='.(int)$trip_id, false));
		} else {
			throw new \Exception(500);
		}

	}

	/**
	 * Method to mark all attendees as approved
	 * @return void
	 * @throws Exception
	 * @since  5.3.0
	 */
	public function unapproveAll()
	{
		// Initialise variables.
		$app = Factory::getApplication();

		// Checking if the user can remove object
		$user = GatripsysHelper::getSpecificUser();

		if ($user->authorise('core.create', 'com_gatripsys'))
		{
			$model = $this->getModel('Attendee', 'Site');

			// Get the user submitted data.
			$trip_id = $app->input->getInt('trip_id', 0);

			// Attempt to save the data.
			$return = $model->unapproveAll($trip_id);

			// Check for errors.
			if ($return === false) {
				$this->setMessage(Text::sprintf(Text::_('COM_GATRIPSYS_UNAPPROVALS_FAILED'), $model->getError()), 'warning');
			} else {
                $app->enqueueMessage(Text::_('COM_GATRIPSYS_UNAPPROVALS_SUCCESSFUL'), 'success');
			}
			
			//check for invoices and costs
			//$inv = GainvoiceHelper::checkForInvoices($trip_id);

			// Redirect to the list screen.
			$app->redirect(Route::_('index.php?option=com_gatripsys&view=trip&id='.(int)$trip_id, false));
		} else {
			throw new \Exception(500);
		}

	}

}
