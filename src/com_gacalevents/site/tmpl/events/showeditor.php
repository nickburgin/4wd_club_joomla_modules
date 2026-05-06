<?php
/**
 * @version    3.0.0
 * @package    Com_Gacalevents
 * @author     Glenn Arkell <glenn@glennarkell.com.au>
 * @copyright  2021 Glenn Arkell
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access
defined('_JEXEC') or die;

use \Joomla\CMS\HTML\HTMLHelper;
use \Joomla\CMS\Factory;
use \Joomla\CMS\Uri\Uri;
use \Joomla\CMS\Router\Route;
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\Layout\LayoutHelper;
use \GlennArkell\Component\Gacalevents\Administrator\Helper\GanamesHelper;
use \GlennArkell\Component\Gacalevents\Administrator\Helper\GabuttonsHelper;
use \GlennArkell\Component\Gacalevents\Administrator\Helper\GacaleventsHelper;
use \GlennArkell\Component\Gacalevents\Administrator\Helper\GacommunicationsHelper;

// load any assets required
$wa = $this->document->getWebAssetManager()
    ->usePreset('com_gacalevents.gacaleventspreset');

// Load admin language file
$lang = Factory::getApplication()->getLanguage();
$lang->load('com_gacalevents', JPATH_ADMINISTRATOR);

$app  = Factory::getApplication();
$user       = GacaleventsHelper::getSpecificUser();
$listOrder  = $this->state->get('list.ordering', 'a.depart_date');
$listDirn   = $this->state->get('list.direction', 'ASC');
$canCreate  = $user->authorise('core.create', 'com_gacalevents');
$canEdit    = $user->authorise('core.edit', 'com_gacalevents');
$canCheckin = $user->authorise('core.manage', 'com_gacalevents');
$canChange  = $user->authorise('core.edit.state', 'com_gacalevents');
$canDelete  = $user->authorise('core.delete', 'com_gacalevents');
$canSocial  = $user->authorise('core.social', 'com_gacalevents');

$prevmonth = "";
$bgcolour = "";

// Get any parameters
$bg1_colour = $this->params->get('bg1_colour', null);
$bg2_colour = $this->params->get('bg2_colour', null);
$htx_colour = $this->params->get('htx_colour', null);
$contactlabel = $this->params->get('contact_label', null);
$locationlabel = $this->params->get('location_label', null);
$event_email = $this->params->get('event_email', null);
$header_text = $this->params->get('header_text', null);
$header_text = str_replace(["\r\n", "\r", "\n"], "</p><p>", $header_text);
$header_text2 = $this->params->get('header_text2', null);
$header_text2 = str_replace(["\r\n", "\r", "\n"], "</p><p>", $header_text2);

$show_editor = $this->params->get('show_editor', 0);
$editor_gp = $this->params->get('editor_gp', 0);
$authid = $this->params->get('authorised_id', 0);
if (is_array($authid) && in_array($user->id, $authid)) {$apprvuser = true; } else { $apprvuser = false; }

$nuLink = GacaleventsHelper::getHTTPQuery(null, 'task', 'eventform.edit', 'id', 0);
$nuURL = 'index.php?'.http_build_query($nuLink, '', '&amp;');
$edLink = GacaleventsHelper::getHTTPQuery(null, 'view', 'events', null, null);
$edLink = GacaleventsHelper::getHTTPQuery($edLink, null, null, 'layout', 'showeditor');
$edURL = 'index.php?'.http_build_query($edLink, '', '&amp;');

?>

<h2><?php echo Text::_('COM_GACALEVENTS_TITLE_EVENTS'); ?></h2>
<?php echo $header_text; ?>
<?php echo $header_text2; ?>

<form action="<?php echo htmlspecialchars(Uri::getInstance()->toString()); ?>" method="post"
      name="adminForm" id="adminForm" class="com-content-category__articles">

	<?php if ($event_email) : ?>
		<p>Contact: <?php echo HTMLHelper::_('email.cloak', $event_email, 1, $this->params->get('coord_name', null), 0); ?></p>
	<?php endif; ?>

	<?php echo LayoutHelper::render('default_filter', array('view' => $this), dirname(__FILE__)); ?>

<?php if ($this->items) : ?>
    <div class="table-responsive com-contact-categories categories-list">
    <table class="eventnone" id="eventList">
		<tfoot class="eventnone">
    		<tr class="eventnone">
    			<td class="eventnone" colspan="<?php echo isset($this->items[0]) ? count(get_object_vars($this->items[0])) : 10; ?>">
    				<?php echo $this->pagination->getListFooter(); ?>
    			</td>
    		</tr>
		</tfoot>
		<tbody>
        <?php foreach ($this->items as $i => $eventitem): ?>
            <?php
                // setup the date to display
    			if ($eventitem->depart_date == $eventitem->return_date || $eventitem->return_date == '0000-00-00 00:00:00' || \is_null($eventitem->return_date) ) {
                    $depart_date = HTMLHelper::_('date', $eventitem->depart_date, Text::_('COM_GACALEVENTS_DISPLAY_DATEFULL'));
                    $htmlDate = '<div class="center">' . $depart_date . '</div>';
    			} else {
                    $depart_date = HTMLHelper::_('date', $eventitem->depart_date, Text::_('COM_GACALEVENTS_DISPLAY_DATEFULL'));
                    $return_date = HTMLHelper::_('date', $eventitem->return_date, Text::_('COM_GACALEVENTS_DISPLAY_DATEFULL'));
    				$htmlDate = '<div class="center">'.$depart_date.'<br /><br />'.$return_date.'</div>';
    			}
            ?>
			<?php if ($eventitem->depart_month === $prevmonth): ?>
				<tr class="eventborder" style="background-color:<?php echo $bgcolour; ?>;">
                        <td class="eventdate">
						<?php echo $htmlDate; ?>
					</td>
					<td class="eventnone">
						<p><strong><?php echo $eventitem->title; ?></strong></p>
							<?php if ($eventitem->leader != ""): ?>
						<?php endif; ?>
						<?php if ($eventitem->depart_point): ?>
							<p><strong><?php echo $locationlabel; ?>: </strong><?php echo $eventitem->depart_point_name; ?></p>
						<?php endif; ?>
						<?php if (!empty($eventitem->brief_desc)): ?>
							<p><?php echo $eventitem->brief_desc; ?></p>
						<?php endif; ?>
						<?php echo $eventitem->event_details; ?><br />
						<div class="clearfix"> </div>
						<?php echo $attendList; echo $apologyList; ?>
					</td>
				</tr>
			<?php else: ?>
				<?php $prevmonth = $eventitem->depart_month; ?>
				<?php if ($bgcolour == $bg1_colour) { $bgcolour = $bg2_colour; } else { $bgcolour = $bg1_colour; } ?>
				<tr class="eventmonth" style="background-color:<?php echo $bgcolour; ?>;">
					<td class="eventmonth" colspan=3 style="color:<?php echo $htx_colour; ?>;" >
						<?php echo $eventitem->depart_month; ?>
					</td>
				</tr>
				<tr class="eventhead" style="background-color:<?php echo $bgcolour; ?>;">
					<td class="eventdate" style="width:17%;">
						<?php echo Text::_('COM_GACALEVENTS_EVENTS_DEPART_DATE'); ?>
					</td>
					<td class="eventhead" style="width:80%;">
						<?php echo Text::_('COM_GACALEVENTS_FORM_LBL_EVENT_EVENT_DETAILS'); ?>
					</td>
				</tr>
				<tr class="eventborder" style="background-color:<?php echo $bgcolour; ?>;">
					<td class="eventdate">
						<?php echo $htmlDate; ?>
                    </td>
					<td class="eventnone">
						<p><strong><?php echo $eventitem->title; ?></strong></p>
						<?php if ($eventitem->leader != ""): ?>
							<p><strong><?php echo $contactlabel; ?>: </strong><?php echo $eventitem->leader; ?></p>
						<?php endif; ?>
						<?php if ($eventitem->depart_point): ?>
							<p><strong><?php echo $locationlabel; ?>: </strong><?php echo $eventitem->depart_point_name; ?></p>
						<?php endif; ?>
						<?php if (!empty($eventitem->brief_desc)): ?>
							<p><?php echo $eventitem->brief_desc; ?></p>
						<?php endif; ?>
						<?php echo $eventitem->event_details; ?><br />
					</td>
				</tr>
			<?php endif; ?>
		<?php endforeach; ?>
		</tbody>
	</table>
    </div>
	<?php else: ?>
		<p>There are no entries in the calendar for the current period.</p>
	<?php endif; ?>
	
	<p>&nbsp;</p>

	<input type="hidden" name="task" value=""/>
	<input type="hidden" name="boxchecked" value="0"/>
	<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>"/>
	<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>"/>
	<?php echo HTMLHelper::_('form.token'); ?>
</form>
