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
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\HTML\HTMLHelper;
use \GlennArkell\Component\Gatripsys\Administrator\Helper\GatripsysHelper;
use \GlennArkell\Component\Gatripsys\Administrator\Helper\GamodalHelper;

$app = Factory::getApplication();
// load any assets required
$wa = $this->document->getWebAssetManager()
    ->usePreset('com_gatripsys.gatripsyspreset');
//This is important for Cloud White template scheme
GatripsysHelper::loadTmplStyleModal($wa);

$wa->useStyle('com_gatripsys.form');

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

$submitLink = GamodalHelper::getHTTPQuery(null, 'task', 'attendeeform.save', null, null);

$id = isset($this->item->id) && $this->item->id ? $this->item->id : 0;


if (!$canLead) {
    $this->form->setFieldAttribute('user_id', 'default', $user->id);
    $this->form->setFieldAttribute('user_id', 'type', 'hidden');
    $this->form->setFieldAttribute('user_name', 'default', $user->name);
} else {
    $this->form->setFieldAttribute('user_name', 'type', 'hidden');
}    
$this->form->setFieldAttribute('state', 'type', 'hidden');
$this->form->setFieldAttribute('ordering', 'type', 'hidden');
$this->form->setFieldAttribute('from_modal', 'default', 1);
$this->form->setFieldAttribute('trip_id', 'default', $trip_id);
$this->form->setFieldAttribute('trip_id', 'type', 'hidden');
$this->form->setFieldAttribute('id', 'default', $id);
$this->form->setFieldAttribute('id', 'type', 'hidden');
$this->form->setFieldAttribute('modified_by', 'default', $user->id);
$this->form->setFieldAttribute('created_by', 'default', $user->id);
$this->form->setFieldAttribute('modified_date', 'default', $today);
$this->form->setFieldAttribute('modified_date', 'type', 'hidden');
$this->form->setFieldAttribute('created_date', 'default', $today);
$this->form->setFieldAttribute('created_date', 'type', 'hidden');
$this->form->setFieldAttribute('approved_by', 'default', 0);
$this->form->setFieldAttribute('approved_by', 'type', 'hidden');
/*
GatripsysHelper::gaPrint($this->item);
*/
?>

<div class="attendee-edit front-end-edit">
	<h2><?php echo Text::_($trip_title); ?></h2>

	<form id="form-attendee" action="<?php echo Route::_('index.php?'.http_build_query($submitLink, '', '&amp;')); ?>"
		method="post" class="form-validate form-horizontal" enctype="multipart/form-data" target="_parent">

    <div class="form-horizontal" style="padding: 0 10px !important;">
        <div class="row-fluid">

			<?php echo $this->form->renderFieldset('sysinfo'); ?>

			<?php if ($trip_cost > '0.00' && $charge_trip) : ?>
				<p class="small"><?php echo Text::sprintf('COM_GATRIPSYS_CHARGE_TRIP_NOTICE', '$'.$trip_cost); ?></p>
			<?php endif; ?>

			<?php echo $this->form->renderFieldset('general'); ?>
			<?php echo $this->form->renderField('comment'); ?>

        </div>

		<div class="btn-group">
			<button type="submit" class="validate btn btn-primary"><?php echo Text::_('JSUBMIT'); ?></button>
		</div>

		<input type="hidden" name="task" value="attendeeform.save" />
		<?php echo HTMLHelper::_('form.token'); ?>
	</form>
</div>
