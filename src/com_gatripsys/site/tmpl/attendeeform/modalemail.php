<?php
/**
 * @version    5.3.0
 * @package    Com_Gatripsys
 * @author     Glenn Arkell <glenn@glennarkell.com.au>
 * @copyright  2016 Glenn Arkell
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// no direct access
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use \GlennArkell\Component\Gatripsys\Administrator\Helper\GatripsysHelper;

// load any assets required
$wa = $this->document->getWebAssetManager()
    ->usePreset('com_gatripsys.gatripsyspreset');
//This is important for Cloud White template scheme
GatripsysHelper::loadTmplStyleModal($wa);

//Load admin language file
$lang = Factory::getLanguage();
$lang->load('com_gatripsys', JPATH_ADMINISTRATOR);

$user = GatripsysHelper::getSpecificUser();
$trip_id = Factory::getApplication()->getUserState('com_gatripsys.edit.trip.id', 0);
$trip = GatripsysHelper::getTripInformation($trip_id);
$today = GatripsysHelper::getTodaysDate();

$canAdmin  = $user->authorise('core.admin', 'com_gatripsys');
$canTrip  = $user->authorise('core.trip', 'com_gatripsys');
$charge_trip = $this->params->get('charge_trip', 0);
$admin_id = $this->params->get('admin_id');
$canAdmin  = ($user->id == $admin_id) ? true : $canAdmin;
$canLead = (($user->id == $trip->leader) || $canTrip || $canAdmin) ? true : false;

$submitLink = GatripsysHelper::getHTTPQuery(null, 'task', 'attendeeform.sendEmail', null, null);
$submitLink = GatripsysHelper::getHTTPQuery($submitLink, null, null, 'tmpl', 'component');
$submitLink = 'index.php?'.http_build_query($submitLink, '', '&amp;');

$recipients = Text::_('COM_GATRIPSYS_TRIP_NEWS_RECIPIENTS');
$attendees = GatripsysHelper::getTripAttendees($trip_id);
// save attendees for emailing
Factory::getApplication()->setUserState('com_gatripsys.trip.attendees', $attendees);
// gather names for display
foreach ($attendees AS $att) {
	$recipients .= $att->state ? $att->attend_name.', ' : '';
}
$recipients = substr($recipients,0,-2);

$this->form->setFieldAttribute('state', 'type', 'hidden');
$this->form->setFieldAttribute('ordering', 'type', 'hidden');
$this->form->setFieldAttribute('from_modal', 'default', 1);
$this->form->setFieldAttribute('from_modal', 'type', 'hidden');
$this->form->setFieldAttribute('trip_id', 'default', $trip_id);
$this->form->setFieldAttribute('trip_id', 'type', 'hidden');
$this->form->setFieldAttribute('id', 'default', 0);
$this->form->setFieldAttribute('id', 'type', 'hidden');
$this->form->setFieldAttribute('modified_by', 'default', $user->id);
$this->form->setFieldAttribute('created_by', 'default', $user->id);
$this->form->setFieldAttribute('modified_date', 'default', $today);
$this->form->setFieldAttribute('modified_date', 'type', 'hidden');
$this->form->setFieldAttribute('created_date', 'default', $today);
$this->form->setFieldAttribute('created_date', 'type', 'hidden');
$this->form->setFieldAttribute('approved_by', 'default', 0);
$this->form->setFieldAttribute('approved_by', 'type', 'hidden');
$this->form->setFieldAttribute('user_id', 'type', 'hidden');
$this->form->setFieldAttribute('user_name', 'type', 'hidden');
$this->form->setFieldAttribute('in_party', 'type', 'hidden');

/*
echo '<pre>Test<br />';
print_r($attendees);
echo '</pre>';
*/

?>

<div class="attendee-edit front-end-edit">
	<h2><?php echo Text::sprintf('COM_GATRIPSYS_TRIP_NEWS_MESSAGE', $trip->title); ?></h2>

	<p><?php echo $recipients; ?></p>
	<p><span class="small"><em><?php echo Text::_('COM_GATRIPSYS_TRIP_NEWS_PRE_TEXT'); ?></em></span></p>

	<form id="form-attendee" action="<?php echo Route::_($submitLink); ?>"
		method="post" class="form-validate form-horizontal" enctype="multipart/form-data" target="_parent">

    <div class="form-horizontal" style="padding: 0 10px !important;">
        <div class="row-fluid">

			<?php echo $this->form->renderFieldset('sysinfo'); ?>
			<?php echo $this->form->renderFieldset('general'); ?>

			<?php echo $this->form->renderFieldset('emailinfo'); ?>

        </div>

		<div class="btn-group">
			<button type="submit" class="validate btn btn-primary"><?php echo Text::_('JSUBMIT'); ?></button>
		</div>

		<input type="hidden" name="task" value="attendeeform.sendEmail" />
		<?php echo HTMLHelper::_('form.token'); ?>
	</form>
</div>
