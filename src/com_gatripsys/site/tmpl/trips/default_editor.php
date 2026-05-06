<?php
/**
 * @version    5.3.0
 * @package    Com_Gatripsys
 * @author     Glenn Arkell <glenn@glennarkell.com.au>
 * @copyright  2016 Glenn Arkell
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use \GlennArkell\Component\Gatripsys\Administrator\Helper\GatripsysHelper;
use \GlennArkell\Component\Gatripsys\Administrator\Helper\GainvoiceHelper;

// load any assets required
$this->document->getWebAssetManager()
    ->usePreset('com_gatripsys.gatripsyspreset');

// Load language file
$lang = Factory::getLanguage();
$lang->load('com_gatripsys', JPATH_ADMINISTRATOR, 'en-GB', true);
$lang->load('com_gatripsys', JPATH_SITE, 'en-GB', true);

$tc_group = $this->params->get('trip_coord');
$admin_id = $this->params->get('admin_id');
$block_book_full = $this->params->get('block_book_full');
$hlite_book_full = $this->params->get('hlite_book_full');
$block_regby = $this->params->get('block_regby');
$show_editor = $this->params->get('show_editor', 0);
$editor_gp = $this->params->get('editor_gp', 0);
$mship_single = $this->params->get('mship_single', 0);
$disp_daysago = $this->params->get('disp_daysago', 0);

$todaysDate = GatripsysHelper::getTodaysDate();
$user       = GatripsysHelper::getSpecificUser();

$listOrder  = $this->state->get('list.ordering', 'a.dept_date');
$listDirn   = $this->state->get('list.direction', 'asc');

$canCreate  = $user->authorise('core.create', 'com_gatripsys');
$canEdit    = $user->authorise('core.edit', 'com_gatripsys');
$canEditOwn    = $user->authorise('core.edit.own', 'com_gatripsys');
$canCheckin = $user->authorise('core.manage', 'com_gatripsys');
$canChange  = $user->authorise('core.edit.state', 'com_gatripsys');
$canDelete  = $user->authorise('core.delete', 'com_gatripsys');

$canTrip  = $user->authorise('core.trip', 'com_gatripsys');
$canTrip = ($canTrip || $admin_id == $user->id) ? 1 : 0;

$canLead = 0;

$canAdmin = (in_array($tc_group,$user->groups, FALSE)) ? 1 : 0;
$canAdmin = (!$canAdmin && $admin_id == $user->id) ? 1 : 0;
$canEditor = (in_array($editor_gp,$user->groups, FALSE)) ? 1 : 0;

$path = 'images/trips/Trip';

// clear the any previously viewed trip id - used in modal views
Factory::getApplication()->setUserState('com_gatripsys.view.trip.id',null);
$menu = Factory::getApplication()->getUserState('com_gatripsys.menuitem.id');

/*
$trip = GatripsysHelper::getTripInformation(406);
echo '<pre>Test<br />';
print_r($trip);
echo '</pre>';
print_r(Factory::getApplication()->getUserState('com_gatripsys.test.data'));
*/
?>

<h2><?php echo $this->params->get('page_heading'); ?></h2>

<p><span style="color:<?php echo $this->params->get('ht_color'); ?>;"><?php echo $this->params->get('header_text'); ?></span></p>

<form action="<?php echo Route::_('index.php?option=com_gatripsys&view=trips'); ?>" method="post" name="adminForm" id="adminForm">

	<?php echo LayoutHelper::render('default_filter', array('view' => $this), dirname(__FILE__)); ?>

	<table class="table table-striped" id="tripList">
		<thead>
		<tr>
			<th class='small'>
				<?php echo Text::_('COM_GATRIPSYS_TRIPS_TITLE'); ?>
			</th>
			<th class='small hidden-phone'>
				<?php echo Text::_('COM_GATRIPSYS_TRIPS_RATING'); ?>
			</th>
			<th class='small'>
				<?php echo Text::_('COM_GATRIPSYS_TRIPS_LEADER'); ?>
			</th>
			<th class='center small hidden-phone'>
				<?php echo Text::_('COM_GATRIPSYS_TRIPS_MINMAX'); ?>
			</th>
			<th class='center small'>
				<?php echo Text::_('COM_GATRIPSYS_TRIPS_DEPT_DATE'); ?>
			</th>
			<th class='center small'>
				<?php echo Text::_('COM_GATRIPSYS_TRIPS_RET_DATE'); ?>
			</th>

		</tr>
		</thead>
		<tfoot>
		<tr>
			<td colspan="<?php echo isset($this->items[0]) ? count(get_object_vars($this->items[0])) : 10; ?>">
				<?php echo $this->pagination->getListFooter(); ?>
			</td>
		</tr>
		</tfoot>
		<tbody>
		<?php 
			foreach ($this->items as $i => $item) : 
				$leader_phone = str_replace('"', '', $item->leader_phone ?? '');
				$link_phone = str_replace(' ', '', $leader_phone ?? '');
				$finalised = false; $canLead = 0; $booking = true;

				if (!$canEdit && $canEditOwn) {
					$canEdit = $user->id == $item->created_by;
				}

				// get attendees of each trip and calc if full
				$attends = GatripsysHelper::getAttendeeCount($item->id);
				$trip_full = ($attends->vehicles >= $item->max_no && $item->max_no) ? 1 : 0;
				$tripClass = ($hlite_book_full && $trip_full) ? ' red' : '';
				$blockBook = ($trip_full && $block_book_full) ? 1 : 0;

			?>

			<tr class="row<?php echo $i % 2; ?><?php echo $tripClass; ?>">

				<td class="trips small">
					<a href="<?php echo Route::_('index.php?option=com_gatripsys&view=trip&id='.(int) $item->id); ?>">
						<?php echo $this->escape($item->title); ?></span>
					</a>
				</td>
				<td class="trips small hidden-phone">
					<?php if (isset($item->rating)) : ?>
						<?php echo $item->rating_name; ?>
					<?php else : ?>
						Not<br />Rated
					<?php endif; ?>
				</td>
				<td class="trips small">
					<?php echo $item->leader_name; ?><br />
                    <a href="mailto:<?php echo $link_phone; ?>" alt=""><?php echo $leader_phone; ?></a>
				</td>
				<td class="center trips small hidden-phone">
					<span title="<?php echo '( '.$attends->vehicles.' Vehicles & '.$attends->persons.' People)'; ?>">
						<?php echo $max_no = $item->max_no == 0 ? 'Unlimited' : $item->min_no.' - '.$item->max_no; ?>
					</span>
				</td>
				<td class="trips small">
					<?php echo !empty($item->dept_date) ? HTMLHelper::date($item->dept_date, Text::_('COM_GATRIPSYS_DISPLAY_DATETXT')) : ''; ?>
				</td>
				<td class="trips small">
					<?php echo !empty($item->ret_date) ? HTMLHelper::date($item->ret_date, Text::_('COM_GATRIPSYS_DISPLAY_DATETXT')) : ''; ?>
				</td>

			</tr>

		<?php endforeach; ?>
		</tbody>
	</table>
	<?php if ($hlite_book_full) : ?>
		<p class="small"><em>Note: </em> Highlighted trips indicate the max number of participants has been reached.</p>
	<?php endif; ?>
	<?php if ($show_editor) : ?>
		<a href="<?php echo Route::_('index.php?option=com_gatripsys&view=trips'); ?>" class="btn btn-warning btn-small"><i class="icon-undo"></i>
			<?php echo Text::_('COM_GATRIPSYS_RETURN'); ?>
		</a>
	<?php endif; ?>

	<input type="hidden" name="task" value=""/>
	<input type="hidden" name="boxchecked" value="0"/>
	<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>"/>
	<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>"/>
	<?php echo HTMLHelper::_('form.token'); ?>
</form>
