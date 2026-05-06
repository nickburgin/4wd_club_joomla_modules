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
use \Joomla\CMS\Uri\Uri;
use \Joomla\CMS\HTML\HTMLHelper;
use \GlennArkell\Component\Gatripsys\Administrator\Helper\GatripsysHelper;

$app = Factory::getApplication();
// load any assets required
$wa = $this->document->getWebAssetManager()
    ->usePreset('com_gatripsys.gatripsyspreset');
//This is important for Cloud White template scheme
GatripsysHelper::loadTmplStyleModal($wa);

//Load admin language file
$lang = Factory::getLanguage();
$lang->load('com_gatripsys', JPATH_ADMINISTRATOR);

$user = GatripsysHelper::getSpecificUser();
$today = GatripsysHelper::getTodaysDate();

$trip_id = $app->getUserState('com_gatripsys.edit.trip.id', 0);
$trip = GatripsysHelper::getTripInformation($trip_id);

$canAdmin  = $user->authorise('core.admin', 'com_gatripsys');
$canTrip  = $user->authorise('core.trip', 'com_gatripsys');
$charge_trip = $this->params->get('charge_trip', 0);
$admin_id = $this->params->get('admin_id');
$canAdmin  = ($user->id == $admin_id) ? 1 : $canAdmin;
$canLead = (($user->id == $trip->leader) || $canTrip || $canAdmin) ? 1 : 0;
if ($trip_id) {
	$trip_title = $trip->title;
	$trip_cost = $trip->trip_cost;
} else {
	$trip_title = 'No trip set';
	$trip_cost = 0;
}

$submitLink = GatripsysHelper::getHTTPQuery(null, 'task', 'attendeeform.save', null, null);

$id = isset($this->item->id) && $this->item->id ? $this->item->id : 0;
/*
echo '<pre>Test<br />';
print_r($this->item);
echo '</pre>';
*/
?>

<div class="attendee-edit front-end-edit">
	<h2><?php echo Text::_($trip_title); ?></h2>

	<form id="form-attendee" action="<?php echo Route::_('index.php?'.http_build_query($submitLink, '', '&amp;')); ?>"
		method="post" class="form-validate form-horizontal" enctype="multipart/form-data" target="_parent">

    <div class="form-horizontal">
        <div class="row-fluid">
        	<input type="hidden" name="jform[from_modal]" value="1" />
        	<input type="hidden" name="jform[id]" value="<?php echo $id; ?>" />
        	<input type="hidden" name="jform[modified_by]" value="<?php echo $user->id; ?>" />
        	<input type="hidden" name="jform[created_by]" value="<?php echo $user->id; ?>" />
        	<input type="hidden" name="jform[modified_date]" value="<?php echo $today; ?>" />
        	<input type="hidden" name="jform[created_date]" value="<?php echo $today; ?>" />
			<input type="hidden" name="jform[trip_id]" value="<?php echo $trip_id; ?>" />
        	<?php if ($canLead): ?>
				<div class="control-group">
					<div class="control-label"><?php echo $this->form->getLabel('user_id'); ?></div>
					<div class="controls"><?php echo $this->form->getInput('user_id'); ?></div>
				</div>
        	<?php else : ?>
				<input type="hidden" name="jform[user_id]" value="<?php echo $user->id; ?>" />
				<div class="control-group">
					<div class="control-label"><?php echo $this->form->getLabel('user_id'); ?></div>
					<div class="controls">
						<input type="text" name="jform[user_name]" value="<?php echo $user->name; ?>" readonly="true" class="readonly"/>
					</div>
				</div>
			<?php endif; ?>
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('in_party'); ?></div>
				<div class="controls"><?php echo $this->form->getInput('in_party'); ?></div>
			</div>
			<?php if ($trip_cost > '0.00' && $charge_trip) : ?>
				<p class="small"><?php echo Text::sprintf('COM_GATRIPSYS_CHARGE_TRIP_NOTICE', '$'.$trip_cost); ?></p>
			<?php endif; ?>
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('comment'); ?></div>
				<div class="controls"><?php echo $this->form->getInput('comment'); ?></div>
			</div>
        </div>

		<div class="btn-group">
			<button type="submit" class="validate btn btn-primary"><?php echo Text::_('JSUBMIT'); ?></button>
		</div>

		<input type="hidden" name="task" value="attendeeform.save" />
		<?php echo HTMLHelper::_('form.token'); ?>
	</form>
</div>
