<?php
/**
 * @version    3.3.1
 * @package    Com_Gacalevents
 * @author     Glenn Arkell <glenn@glennarkell.com.au>
 * @copyright  2021 Glenn Arkell
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access
defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use \GlennArkell\Component\Gacalevents\Administrator\Helper\GacaleventsHelper;

// load any assets required
$wa = $this->document->getWebAssetManager()
    ->useScript('keepalive')
    ->useScript('form.validate')
    ->usePreset('com_gacalevents.gacaleventspreset');

// check what template is used to add specific styling
GacaleventsHelper::loadTmplStyleModal($wa);

// Load admin language file
Factory::getApplication()->getLanguage()->load('com_gacalevents', JPATH_ADMINISTRATOR);

$user = Factory::getApplication()->getIdentity();
$event_id = Factory::getApplication()->getUserState('com_gacalevents.edit.attendee.event_id');
$user_id = Factory::getApplication()->getUserState('com_gacalevents.edit.attendee.attendee');
$event = GacaleventsHelper::getEvent($event_id);
$disp_depart_date = HTMLHelper::date($event->depart_date, Text::_('COM_GACALEVENTS_DISPLAY_DATE'), 'UTC');

$id = $this->item ? $this->item->id : 0;

$canAdmin  = $user->authorise('core.admin', 'com_gacalevents');
$canCreate  = $user->authorise('core.create', 'com_gacalevents');

$submitLink = GacaleventsHelper::getHTTPQuery(null, 'task', 'attendeeform.save', null, null);
//$submitLink = GacaleventsHelper::getHTTPQuery($submitLink, null, null, 'tmpl', 'component');
$submitURL = 'index.php?'.http_build_query($submitLink, '', '&amp;');

//GacaleventsHelper::gaPrint($event_id, 'Test');
//GacaleventsHelper::gaPrint(Factory::getApplication()->getUserState('com_gacalevents.test.data'), 'Test');

?>

<div class="attendee-edit front-end-edit">
	<h3><?php echo Text::sprintf('COM_GACALEVENTS_ATTENDING_EVENT', $event->title, $disp_depart_date); ?></h3>

	<form id="form-attendee" action="<?php echo Route::_($submitURL); ?>"
		method="post" class="form-validate form-horizontal" enctype="multipart/form-data" target="_parent">

    <div class="form-horizontal">
        <div class="row-fluid">
        	<input type="hidden" name="jform[from_modal]" value="0" />
			<input type="hidden" name="jform[id]" value="<?php echo $id; ?>" />
			<input type="hidden" name="jform[user_id]" value="<?php echo $user_id; ?>" />
			<input type="hidden" name="jform[event_id]" value="<?php echo $event_id; ?>" />

			<?php echo $this->form->renderField('attendee'); ?>
			<?php echo $this->form->renderField('qty_att'); ?>

        </div>

		<div class="btn-group">
			<button type="submit" class="validate btn btn-primary"><?php echo Text::_('JSUBMIT'); ?></button>
		</div>

		<input type="hidden" name="task" value="attendeeform.save" />
		<?php echo HTMLHelper::_('form.token'); ?>
	</form>
</div>
