<?php
/**
 * @version    5.1.2
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
use \GlennArkell\Component\Gatripsys\Administrator\Helper\GamodalHelper;

$app = Factory::getApplication();
// load any assets required
$wa = $this->document->getWebAssetManager()
    ->usePreset('com_gatripsys.gatripsyspreset');
//This is important for Cloud White template scheme
GatripsysHelper::loadTmplStyleModal($wa);
$wa->addInlineStyle('.center {text-align:center !important;} button.btn-close {background-color:red !important;}');

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

$id = isset($this->item->id) && $this->item->id ? $this->item->id : 0;
$submitLink = GamodalHelper::getHTTPQuery(null, 'task', 'attendee.removeAttendee', 'id', $id);
$submitLink = GamodalHelper::getHTTPQuery($submitLink, null, null, 'trip_id', $trip_id);

$this->form->setFieldAttribute('in_party', 'type', 'hidden');
$this->form->setFieldAttribute('user_id', 'type', 'hidden');
$this->form->setFieldAttribute('user_name', 'type', 'hidden');
$this->form->setFieldAttribute('state', 'type', 'hidden');
$this->form->setFieldAttribute('ordering', 'type', 'hidden');
$this->form->setFieldAttribute('from_modal', 'default', 1);
$this->form->setFieldAttribute('trip_id', 'default', $trip_id);
$this->form->setFieldAttribute('trip_id', 'type', 'hidden');
$this->form->setFieldAttribute('id', 'default', $id);
$this->form->setFieldAttribute('id', 'type', 'hidden');
$this->form->setFieldAttribute('modified_by', 'default', $user->id);
$this->form->setFieldAttribute('modified_date', 'default', $today);
$this->form->setFieldAttribute('modified_date', 'type', 'hidden');
if (!$id) {
    $this->form->setFieldAttribute('created_by', 'default', $user->id);
    $this->form->setFieldAttribute('created_date', 'default', $today);
}
$this->form->setFieldAttribute('created_date', 'type', 'hidden');
$this->form->setFieldAttribute('approved_by', 'default', 0);
$this->form->setFieldAttribute('approved_by', 'type', 'hidden');
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

    <div class="form-horizontal" style="padding: 0 10px !important;">
        <div class="row-fluid">

			<h2>&nbsp;</h2>
            <h1 class="center"><?php echo Text::_('GABOOKING_DELETE_CONFIRM'); ?></h1>
            <?php echo $this->form->renderFieldset('sysinfo'); ?>
			<?php echo $this->form->renderFieldset('general'); ?>
			<p class="center"><?php echo Text::_('GABOOKING_CANCEL_POPUP'); ?></p>

        </div>

		<div class="btn-group center">
			<button type="submit" class="validate btn btn-danger"><?php echo Text::_('GABOOKING_CANCEL'); ?></button>
		</div>

		<input type="hidden" name="task" value="attendee.removeAttendee" />
		<?php echo HTMLHelper::_('form.token'); ?>
	</form>
</div>
