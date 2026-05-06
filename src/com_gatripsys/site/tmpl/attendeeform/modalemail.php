<?php
/**
 * @version    5.1.0
 * @package    Com_Gatripsys
 * @author     Glenn Arkell <glenn@glennarkell.com.au>
 * @copyright  2016 Glenn Arkell
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// no direct access
defined('_JEXEC') or die;

use \Joomla\CMS\Factory;
use \Joomla\CMS\Router\Route;
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\HTML\HTMLHelper;
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

$canAdmin  = $user->authorise('core.admin', 'com_gatripsys');
$canTrip  = $user->authorise('core.trip', 'com_gatripsys');
$charge_trip = $this->params->get('charge_trip', 0);
$admin_id = $this->params->get('admin_id');
$canAdmin  = ($user->id == $admin_id) ? true : $canAdmin;
$canLead = (($user->id == $trip->leader) || $canTrip || $canAdmin) ? true : false;

$submitLink = GatripsysHelper::getHTTPQuery(null, 'task', 'attendeeform.sendEmail', null, null);
$submitLink = GatripsysHelper::getHTTPQuery($submitLink, null, null, 'tmpl', 'component');

$recipients = Text::_('COM_GATRIPSYS_TRIP_NEWS_RECIPIENTS');
$attendees = GatripsysHelper::getTripAttendees($trip_id);
// save attendees for emailing
Factory::getApplication()->setUserState('com_gatripsys.trip.attendees', $attendees);
// gather names for display
foreach ($attendees AS $att) {
	$recipients .= $att->state ? $att->attend_name.', ' : '';
}
$recipients = substr($recipients,0,-2);

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

	<form id="form-attendee" action="<?php echo Route::_('index.php?'.http_build_query($submitLink, '', '&amp;')); ?>"
		method="post" class="form-validate form-horizontal" enctype="multipart/form-data" target="_parent">

    <div class="form-horizontal">
        <div class="row-fluid">
        	<input type="hidden" name="jform[from_modal]" value="1" />
        	<input type="hidden" name="jform[id]" value="0" />
        	<input type="hidden" name="jform[modified_by]" value="<?php echo $user->id; ?>" />
        	<input type="hidden" name="jform[created_by]" value="<?php echo $user->id; ?>" />
			<input type="hidden" name="jform[trip_id]" value="<?php echo $trip_id; ?>" />

			<?php echo $this->form->renderField('news_subject'); ?>

			<div class="control-group" style="width:98%;">
				<div class="control-label"><?php echo $this->form->getLabel('news_detail'); ?></div>
				<div class="controls"><?php echo $this->form->getInput('news_detail'); ?></div>
			</div>

        </div>

		<div class="btn-group">
			<button type="submit" class="validate btn btn-primary"><?php echo Text::_('JSUBMIT'); ?></button>
		</div>

		<input type="hidden" name="task" value="attendeeform.sendEmail" />
		<?php echo HTMLHelper::_('form.token'); ?>
	</form>
</div>
