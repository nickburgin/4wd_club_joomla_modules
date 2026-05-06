<?php
/**
 * @version    5.1.0
 * @package    com_gatripsys
 * @author     Glenn Arkell <glenn@glennarkell.com.au>
 * @copyright  2021 Glenn Arkell
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace GlennArkell\Component\Gatripsys\Site\Controller;

\defined('_JEXEC') or die;

use \Joomla\CMS\Application\SiteApplication;
use \Joomla\CMS\Factory;
use \Joomla\CMS\Language\Multilanguage;
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\MVC\Controller\BaseController;
use \Joomla\CMS\Router\Route;
use \Joomla\CMS\Uri\Uri;
use \Joomla\Utilities\ArrayHelper;
use \GlennArkell\Component\Gatripsys\Administrator\Helper\GatripsysHelper;

/**
 * Item class.
 * @since  1.6.0
 */
class TripController extends BaseController
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
		$previousId = (int) $app->getUserState('com_gatripsys.edit.trip.id');
		$editId     = $app->input->getInt('id', 0);

		// Set the record id to edit in the session.
		$app->setUserState('com_gatripsys.edit.trip.id', $editId);

		// Get the model.
		$model = $this->getModel('Trip', 'Site');

		// Check out the record
		if ($editId) {
			$model->checkout($editId);
		}

		// Check in the previous record.
		if ($previousId && $previousId !== $editId) {
			$model->checkin($previousId);
		}

		// Redirect to the edit screen.
		$this->setRedirect(Route::_('index.php?option=com_gatripsys&view=tripform&layout=edit', false));
	}

	/**
	 * Method to publish record
	 * @return    void
	 * @throws Exception
	 * @since    1.6
	 */
	public function publish()
	{
		// Initialise variables.
		$app = Factory::getApplication();

		// Get the user data.
		$id    = $app->input->getInt('id');
		$state = $app->input->getInt('state');

		// Checking if the user can remove object
        $trip = GatripsysHelper::getTrip($id);

		// Checking if the user can remove object
		$user = GatripsysHelper::getSpecificUser();
		$canEdit = GatripsysHelper::canUserEdit($user, $trip);

		if ($canEdit)
		{
			$model = $this->getModel('Trip', 'Site');

			// Attempt to save the data.
			$return = $model->publish($id, $state);

			// Check for errors.
			if ($return === false) {
				$this->setMessage(Text::sprintf('COM_GATRIPSYS_SAVE_FAILED', $model->getError()), 'warning');
			}

			// Clear the record id from the session.
			$app->setUserState('com_gatripsys.edit.trip.id', null);

			// Flush the data from the session.
			$app->setUserState('com_gatripsys.edit.trip.data', null);

			// Redirect to the list screen.
			$this->setMessage(Text::_('COM_GATRIPSYS_ITEM_SAVED_SUCCESSFULLY'));
			$menu = Factory::getApplication()->getMenu();
			$item = $menu->getActive();

			if (!$item) {
				// If there isn't any menu item active, redirect to list view
				$this->setRedirect(Route::_('index.php?option=com_gatripsys&view=trips', false));
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

		// Get the user submitted data.
		$id = $app->input->getInt('id', 0);

		// Checking if the user can remove object
        $trip = GatripsysHelper::getTrip($id);

		// Checking if the user can remove object
		$user = GatripsysHelper::getSpecificUser();
		$canEdit = GatripsysHelper::canUserEdit($user, $trip);

		if ($canEdit)
		{
			$model = $this->getModel('Trip', 'Site');

			// Attempt to save the data.
			$return = $model->delete($trip->id);

			// Check for errors.
			if ($return === false) {
				$this->setMessage(Text::sprintf(Text::_('COM_GATRIPSYS_ITEM_DELETED_FAILED'), $model->getError()), 'warning');
			} else {
                $app->enqueueMessage(Text::_('COM_GATRIPSYS_ITEM_DELETED_SUCCESSFULLY'), 'success');
			}
		} else {
			$this->setMessage(Text::_('COM_GATRIPSYS_NOT_AUTHORISED'), 'warning');
		}
		$this->setRedirect(Route::_('index.php?option=com_gatripsys&view=trips', false));
	}

	/**
	 * Report data
	 * @return void
	 * @throws Exception
	 */
	public function genRpt()
	{
		// Initialise variables.
		$app   = Factory::getApplication();
		$model = $this->getModel('Trip', 'Site');

		// Get the selected data.
		$id = $app->input->getInt('id');
		$rpt = $app->input->getInt('rpt');

		// Check for errors.
		if (!$id) {
            $this->setMessage(Text::_('COM_GATRIPSYS_ITEM_REPORTED_FAILED'));

			// Redirect back to the list screen.
			$this->setRedirect(Route::_('index.php?option=com_gatripsys&view=trips', false));
		}

		// Attempt to generate Report.
		$return = $model->genRpt($id, $rpt);

		// Check for errors.
		if ($return === false) {
			// Save the data in the session.
			$app->setUserState('com_gatripsys.edit.trip.data', $data);

			// Redirect back to the edit screen.
			$this->setMessage(Text::sprintf('COM_GATRIPSYS_ITEM_REPORT_FAILED', $model->getError()), 'warning');
			$this->setRedirect(Route::_('index.php?option=com_gatripsys&view=trip&id=' . $id, false));
		}

		// Redirect to the list screen.
		$this->setMessage(Text::_('COM_GATRIPSYS_ITEM_REPORTED_SUCCESSFULLY'));
		$this->setRedirect(Route::_('index.php?option=com_gatripsys&view=trip&id=' . $id, false));

	}

	/**
	 * Remove PDF
	 * @return void
	 * @throws Exception
	 */
	public function deletePDF()
	{
		// Initialise variables.
		$app = Factory::getApplication();

		$model = $this->getModel('Trip', 'Site');

		// Get the user data.
		$id = $app->input->getInt('id', 0);
		$rpt = $app->input->getInt('rpt', 0);

		// Attempt to save the data.
		$return = $model->deletePDF($id, $rpt);

		// Check for errors.
		if ($return === false) {
			$this->setMessage(Text::_('COM_GATRIPSYS_ITEM_DELETEDPDF_FAILED'.' = '.$id));
		} else {
			$this->setMessage(Text::_('COM_GATRIPSYS_ITEM_DELETEDPDF_SUCCESSFULLY'));
		}

		// Redirect to the item screen.
		$this->setRedirect(Route::_('index.php?option=com_gatripsys&view=trip&id='.(int) $id, false));
	}

}
