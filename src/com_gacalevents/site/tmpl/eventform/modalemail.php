<?php
/**
 * @version    1.2.4
 * @package    Com_Gacalevents
 * @author     Glenn Arkell <glenn@glennarkell.com.au>
 * @copyright  2021 Glenn Arkell
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// no direct access
defined('_JEXEC') or die;

use \Joomla\CMS\Factory;
use \Joomla\CMS\Router\Route;
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\HTML\HTMLHelper;
use \GlennArkell\Component\Gacalevents\Administrator\Helper\GacaleventsHelper;
use \GlennArkell\Component\Gacalevents\Administrator\Helper\GacommunicationsHelper;

// load any assets required
$wa = $this->document->getWebAssetManager();
$wa->usePreset('com_gacalevents.gacaleventspreset');

//Load admin language file
$lang = Factory::getLanguage();
$lang->load('com_gacalevents', JPATH_ADMINISTRATOR);

$user = GacaleventsHelper::getSpecificUser();
$event_id = Factory::getApplication()->getUserState('com_gacalevents.edit.event.id', 0);
$event = GacaleventsHelper::getEvent($event_id);

$canAdmin  = $user->authorise('core.admin', 'com_gacalevents');
$canCreate  = $user->authorise('core.create', 'com_gacalevents');

$submitLink = GacaleventsHelper::getHTTPQuery(null, 'task', 'eventform.sendEmail', null, null);
$submitLink = GacaleventsHelper::getHTTPQuery($submitLink, null, null, 'tmpl', 'component');

// check what template is used to add specific styling
//GacaleventsHelper::loadTmplStyleModal($wa);

/*
echo '<pre>Test<br />';
print_r($event_id);
echo '</pre>';
*/

?>

<div class="attendee-edit front-end-edit">
	<h3><?php echo Text::sprintf('COM_GACALEVENTS_ATTENDEES_MESSAGE', $event->title); ?></h3>

	<form id="form-attendee" action="<?php echo Route::_('index.php?'.http_build_query($submitLink, '', '&amp;')); ?>"
		method="post" class="form-validate form-horizontal" enctype="multipart/form-data" target="_parent">

    <div class="form-horizontal">
        <div class="row-fluid">
        	<input type="hidden" name="jform[from_modal]" value="1" />
			<input type="hidden" name="jform[event_id]" value="<?php echo $event_id; ?>" />

			<?php echo $this->form->renderFieldset('notifyEmail'); ?>

        </div>

		<div class="btn-group">
			<button type="submit" class="validate btn btn-primary"><?php echo Text::_('JSUBMIT'); ?></button>
		</div>

		<input type="hidden" name="task" value="eventform.sendEmail" />
		<?php echo HTMLHelper::_('form.token'); ?>
	</form>
</div>
