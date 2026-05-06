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

use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\Utilities\ArrayHelper;
use \GlennArkell\Component\Gatripsys\Administrator\Helper\GatripsysHelper;

/**
 * Form class.
 * @since  1.6.0
 */
class AttendeeformController extends FormController
{
	/**
	 * Method to check out an item for editing and redirect to the edit form.
	 * @return void
	 * @since    1.6
     * @throws Exception
	 */
	public function edit($key = NULL, $urlVar = NULL)
	{
		$app = Factory::getApplication();

		// Get the previous edit id (if any) and the current edit id.
		$previousId = (int) $app->getUserState('com_gatripsys.edit.attendee.id');
		$editId     = $app->input->getInt('id', 0);
		$tripId     = $app->input->getInt('trip_id', 0);

		// Set the user id for the user to edit in the session.
		$app->setUserState('com_gatripsys.edit.attendee.id', $editId);
		$app->setUserState('com_gatripsys.edit.trip.id', $tripId);

		// Get the model.
		$model = $this->getModel('Attendeeform', 'Site');

		// Check out the record
		if ($editId) {
			$model->checkout($editId);
		}

		// Check in the previous record.
		if ($previousId) {
			$model->checkin($previousId);
		}

		// Redirect to the edit screen.
		if ($tripId) {
			$this->setRedirect(Route::_('index.php?option=com_gatripsys&view=attendeeform&tmpl=component&layout=modal&id='.$editId.'&trip_id='.$tripId, false));
		} else {
			$this->setRedirect(Route::_('index.php?option=com_gatripsys&view=attendeeform&layout=edit', false));
		}
	}

	/**
	 * Method to save data.
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
		$model = $this->getModel('Attendeeform', 'Site');

		// Get the user data.
		$data = Factory::getApplication()->input->get('jform', array(), 'array');
        $from_modal = $data['from_modal'];

		// Validate the posted data.
		$form = $model->getForm();

		if (!$form) {
			throw new \Exception($model->getError(), 500);
		}

		// set up the return link to use in case of failure
		$retLink = GatripsysHelper::getHTTPQuery(null, 'task', 'attendeeform.edit', 'id', 0);
		$retLink = GatripsysHelper::getHTTPQuery($retLink, null, null, 'trip_id', $data['trip_id']);
		$retLink = GatripsysHelper::getHTTPQuery($retLink, null, null, 'tmpl', 'component');
		$retLink = GatripsysHelper::getHTTPQuery($retLink, null, null, 'layout', 'modal');

		$tripLink = GatripsysHelper::getHTTPQuery(null, 'view', 'trip', 'id', $data['trip_id']);

		// Validate the posted data.
		$data = $model->validate($form, $data);

		// Check for errors.
		if ($data === false)
		{
			// Get the validation messages.
			$errors = $model->getErrors();

			// Push up to three validation messages out to the user.
			for ($i = 0, $n = count($errors); $i < $n && $i < 3; $i++) {
				if ($errors[$i] instanceof \Exception) {
					$app->enqueueMessage($errors[$i]->getMessage(), 'warning');
				} else {
					$app->enqueueMessage($errors[$i], 'warning');
				}
			}

			$input = $app->input;
			$jform = $input->get('jform', array(), 'ARRAY');

			// Save the data in the session.
			$app->setUserState('com_gatripsys.edit.attendee.data', $jform);

			// Redirect back to the edit screen.
			$id = (int) $app->getUserState('com_gatripsys.edit.attendee.id');
			$this->setRedirect(Route::_('index.php?'.http_build_query($retLink, '', '&amp;'), false));

			$this->redirect();
		}

		// Check if booking exceeds limit
		$atts = GatripsysHelper::getAttendeeCount($data['trip_id']);
		if ($data['id']) {
            $orig = GatripsysHelper::getRecord('#__gatripsys_attendees', $data['id']);
            $orig->vehicles = 1;
            if ($atts->max_pers && (($atts->persons - $orig->in_party) + $data['in_party']) > $atts->max_pers) {
                $app->enqueueMessage('Booking Exceeds maximum people', 'warning');
                $this->redirect(Route::_('index.php?'.http_build_query($tripLink, '', '&amp;'), false));
            }
            if ($atts->max_vehs && (($atts->vehicles - $orig->vehicles) + 1) > $atts->max_vehs) {
                $app->enqueueMessage('Booking Exceeds maximum vehicles', 'warning');
                $this->redirect(Route::_('index.php?'.http_build_query($tripLink, '', '&amp;'), false));
            }
        } else {
            if ($atts->max_pers && ($atts->persons + $data['in_party']) > $atts->max_pers) {
                $app->enqueueMessage('Booking Exceeds maximum people', 'warning');
                $this->redirect(Route::_('index.php?'.http_build_query($tripLink, '', '&amp;'), false));
            }
            if ($atts->max_vehs && ($atts->vehicles + 1) > $atts->max_vehs) {
                $app->enqueueMessage('Booking Exceeds maximum vehicles', 'warning');
                $this->redirect(Route::_('index.php?'.http_build_query($tripLink, '', '&amp;'), false));
            }
        }

		// Attempt to save the data.
		$return = $model->save($data);

		// Check for errors.
		if ($return === false)
		{
			// Save the data in the session.
			$app->setUserState('com_gatripsys.edit.attendee.data', $data);

			// Redirect back to the edit screen.
			$id = (int) $app->getUserState('com_gatripsys.edit.attendee.id');
			$this->setMessage(Text::sprintf('Save failed', $model->getError()), 'warning');
			$this->setRedirect(Route::_('index.php?'.http_build_query($retLink, '', '&amp;'), false));
		}

		// Check in the record.
		if ($return) {
			$model->checkin($return);
		}

		// Clear the record id from the session.
		$app->setUserState('com_gatripsys.edit.attendee.id', null);

		// Redirect to the list screen.
		$this->setMessage(Text::_('COM_GATRIPSYS_ITEM_SAVED_SUCCESSFULLY'));
        $this->setRedirect(Route::_('index.php?'.http_build_query($tripLink, '', '&amp;')));

		// Flush the data from the session.
		$app->setUserState('com_gatripsys.edit.attendee.data', null);
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
		$editId = (int) $app->getUserState('com_gatripsys.edit.attendee.id');
		$tripId = (int) $app->getUserState('com_gatripsys.edit.trip.id');

		// Get the model.
		$model = $this->getModel('Attendeeform', 'Site');

		// Check in the record
		if ($editId) {
			$model->checkin($editId);
		}

// 		$menu = Factory::getApplication()->getMenu();
// 		$item = $menu->getActive();
// 		$url  = (empty($item->link) ? 'index.php?option=com_gatripsys&view=trips' : $item->link);
// 		$this->setRedirect(Route::_($url, false));
		$tripLink = GatripsysHelper::getHTTPQuery(null, 'view', 'trip', 'id', $tripId);
        $this->setRedirect(Route::_('index.php?'.http_build_query($tripLink, '', '&amp;')));
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
        $model = $this->getModel('Attendeeform', 'Site');
        $pk    = $app->input->getInt('id');

        // Attempt to save the data
        try
        {
            $return = $model->delete($pk);

            // Check in the record
            $model->checkin($return);

            // Clear the record id from the session.
            $app->setUserState('com_gatripsys.edit.attendee.id', null);

            $menu = $app->getMenu();
            $item = $menu->getActive();
            $url = (empty($item->link) ? 'index.php?option=com_gatripsys&view=trips' : $item->link);

            // Redirect to the list screen
            $this->setMessage(Text::_('COM_GATRIPSYS_ITEM_DELETED_SUCCESSFULLY'));
            $this->setRedirect(Route::_($url, false));

            // Flush the data from the session.
            $app->setUserState('com_gatripsys.edit.attendee.data', null);
        }
        catch (\Exception $e)
        {
            $errorType = ($e->getCode() == '404') ? 'error' : 'warning';
            $this->setMessage($e->getMessage(), $errorType);
            $this->setRedirect('index.php?option=com_gatripsys&view=trips');
        }
    }

	/**
	 * Method to send all attendees an email
	 * @return void
	 * @throws Exception
	 * @since  4.0.0
	 */
	public function sendEmail()
	{
		// Check for request forgeries.
		$this->checkToken();

		// Initialise variables.
		$app   = Factory::getApplication();
		$model = $this->getModel('Attendeeform', 'Site');

		// Get the passed data.
		$data = Factory::getApplication()->input->get('jform', array(), 'array');
		$trip_id = $data['trip_id'];

		// Check for errors.
		if (!$trip_id) {
			$app->enqueueMessage(Text::_('COM_GATRIPSYS_NO_TRIP_ID'), 'warning');
			$this->setRedirect(Route::_('index.php?option=com_gatripsys&view=trips', false));
		} else {

			// Attempt to save the data.
			$trip = $model->sendEmail($data);

			// Check for errors.
			if ($trip === false) {
				// Redirect back to the trip list screen.
				$app->enqueueMessage(Text::_('COM_GATRIPSYS_MESSAGE_FAILED'), 'warning');
				$this->setRedirect(Route::_('index.php?option=com_gatripsys&view=trip&id='.$trip_id, false));
			}

			// Clear the session data and go back to the trip screen.
			$app->setUserState('com_gatripsys.edit.attendee.id', null);
	        $app->setUserState('com_gatripsys.edit.trip.id', null);
			$app->enqueueMessage(Text::_('COM_GATRIPSYS_MESSAGESENT_SUCCESSFULLY'), 'notice');
			$this->setRedirect(Route::_('index.php?option=com_gatripsys&view=trip&id='.$trip_id, false));

			// Flush the data from the session.
			$app->setUserState('com_gatripsys.edit.attendee.data', null);
		}
	}

}
