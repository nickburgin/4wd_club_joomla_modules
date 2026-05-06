<?php
/**
 * @version    4.2.0
 * @subpackage com_gatracklog
 * @author     Glenn Arkell <glenn@glennarkell.com.au>
 * @copyright  2021 Glenn Arkell
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use \GlennArkell\Component\Gatracklog\Administrator\Helper\GatracklogHelper;
use \GlennArkell\Component\Gatracklog\Administrator\Helper\GamodalHelper;

// load any assets required
$this->getDocument()->getWebAssetManager()
    ->usePreset('com_gatracklog.gatracklogpreset');


// Load admin language file
Factory::getApplication()->getLanguage()->load('com_gatracklog', JPATH_ADMINISTRATOR);

$user = Factory::getApplication()->getIdentity();
$canEdit = GatracklogHelper::canUserEdit($user, $this->item);

$canDelete = $user->authorise('core.delete','com_gatracklog.tracklog.'.$this->item->id);

$removeTran = GamodalHelper::getHTTPQuery(null, 'task', 'tracklog.remove', 'id', $this->item->id);
$removeURL = 'index.php?'.\http_build_query($removeTran, '', '&amp;');
$editTran = GamodalHelper::getHTTPQuery(null, 'task', 'tracklog.edit', 'id', $this->item->id);
$editURL = 'index.php?'.\http_build_query($editTran, '', '&amp;');
$canTran = GamodalHelper::getHTTPQuery(null, 'task', 'tracklog.cancel', 'id', $this->item->id);
$canURL = 'index.php?'.\http_build_query($canTran, '', '&amp;');

// setup the modal links for a member leaving the club
$lhtml = GamodalHelper::setupModalButton('view', 'tracklogform', 'id', $this->item->id, 'modal', 'modal-myModal', 'success', '', 'COM_GATRACKLOG_ADD_COMMENT', 'fas fa-plus', '')

// $lveLink = GatracklogHelper::getHTTPQuery(null, 'view', 'tracklogform', 'id', $this->item->id);
// $lveLink = GatracklogHelper::getHTTPQuery($lveLink, null, null, 'tmpl', 'component');
// $lveLink = GatracklogHelper::getHTTPQuery($lveLink, null, null, 'layout', 'modal');
// $lmodparams = array( 'url'        => 'index.php?'.http_build_query($lveLink, '', '&amp;'),
//         'title'      => Text::_("COM_GATRACKLOG_ADD_COMMENT"), 'closeButton'=> true,
//         'modalWidth' => 60, 'bodyHeight' => 35, 'backdrop'   => 'static' );
// $lmodname = 'modal-myLeftModal'.$this->item->id;
// $lhtml = '<a class="btn btn-success" href="#'.$lmodname.'" data-bs-toggle="modal">';
// $lhtml .= '<i class="fas fa-plus" title="'.Text::_('COM_GATRACKLOG_ADD_COMMENT').'"></i> '.Text::_('COM_GATRACKLOG_ADD_COMMENT').'</a>';

// Display a heading for the page
if ($this->params->get('page_title', '') > '') {
	echo '<h1>'.$this->params->get('page_title').'</h1>';
} else { 
	echo '<h1>'.Text::_('COM_GAGATRACKLOG_TITLE_TRACKLOG').'</h1>';
}
?>

<div class="item_fields">

	<table class="table">

		<tr>
			<th><?php echo Text::_('COM_GATRACKLOG_FORM_LBL_TRACKLOG_NAME'); ?></th>
			<td><?php echo $this->item->name; ?></td>
		</tr>

		<tr>
			<th><?php echo Text::_('COM_GATRACKLOG_FORM_LBL_TRACKLOG_RATING'); ?></th>
			<td>
				<?php $cat_params = json_decode($this->item->cat->params); ?>
				<?php echo '<img src="'.$cat_params->image.'" style="width:14px;" alt="'.$cat_params->image_alt.'" title="'.$cat_params->image_alt.'"/>'; ?>
				<?php echo $this->item->rating_name; ?>
			</td>
		</tr>

		<tr>
			<th><?php echo Text::_('COM_GATRACKLOG_FORM_LBL_TRACKLOG_TRACK_ZONE'); ?></th>
			<td><?php echo $this->item->track_zone_name; ?></td>
		</tr>

		<tr>
			<th><?php echo Text::_('COM_GATRACKLOG_FORM_LBL_TRACKLOG_SEASON_CLOSE'); ?></th>
			<td><?php echo $this->item->season_close_name; ?></td>
		</tr>

		<tr>
			<th><?php echo Text::_('COM_GATRACKLOG_FORM_LBL_CREATED_DATE'); ?></th>
			<td><?php 
					$tdate = $this->item->created_date;
					echo $tdate > 0 ? HTMLHelper::_('date', $tdate, Text::_('DATE_FORMAT_LC6')) : '-';
				?>
			</td>
		</tr>

		<tr>
			<th><?php echo Text::_('COM_GATRACKLOG_FORM_LBL_COMMENT'); ?></th>
			<td><?php echo nl2br($this->item->comment); ?></td>
		</tr>

	</table>

	<h3><?php echo Text::_('COM_GATRACKLOG_TRACKCOMMENT_HEADER'); ?></h3>
	<table class="table table-striped" id="tracklogList">
		<thead>
		<tr>
			<th class=''>
				<?php echo Text::_('COM_GATRACKLOG_TRACKCOMMENT_USER_ID'); ?>
			</th>
			<th class=''>
				<?php echo Text::_('COM_GATRACKLOG_TRACKCOMMENT_CREATED_DATE'); ?>
			</th>
			<th class=''>
				<?php echo Text::_('COM_GATRACKLOG_TRACKCOMMENT_COMMENT'); ?>
			</th>
			<?php if($user->authorise('core.delete','com_gatracklog')):?>
				<th class=''>
					<?php echo Text::_('COM_GATRACKLOG_ACTIONS'); ?>
				</th>
			<?php endif; ?>
		</tr>
		</thead>
		<tbody>
			<?php if(!empty($this->item->tracklog_comments)): ?>
				<?php foreach ($this->item->tracklog_comments as $i => $citem) : ?>
					<tr class="row<?php echo $i % 2; ?>">
						<td>
							<?php if ($citem->user_name != '') { echo $citem->user_name; } ?>
						</td>
						<td>
							<?php $comdate = $citem->created_date; echo $comdate > 0 ? HTMLHelper::_('date', $comdate, Text::_('COM_GATRACKLOG_DISPLAY_DATETIME')) : '-';?>
						</td>
						<td>
							<span class="small"><?php echo $citem->comment; ?></span>
						</td>
						<?php if($user->authorise('core.delete','com_gatracklog')):?>
							<td>
								<a class="btn btn-danger"
									href="<?php echo Route::_('index.php?option=com_gatracklog&task=tracklog.removeComment&id='.$citem->id.'&track_id='.$this->item->id, false, 2); ?>"
									title="Delete Comment">
									<i class="icon-trash"></i>
								</a>
							</td>
						<?php endif; ?>

					</tr>
				<?php endforeach; ?>
			<?php endif; ?>
		</tbody>
	</table>

</div>

<?php /* ----------------------   Return button   ------------------------------ */ ?>
<a class="btn btn-secondary" href="<?php echo Route::_($canURL); ?>">
	<i class="icon-undo"></i> <?php echo Text::_("COM_GAGATRACKLOG_RETURN"); ?>
</a>

<?php echo $lhtml .= HTMLHelper::_('bootstrap.renderModal', $lmodname, $lmodparams); ?>

<a class="btn btn-info" title="<?php echo Text::_('COM_GATRACKLOG_SEND_LOG_DESC'); ?>"
	href="<?php echo Route::_('index.php?option=com_gatracklog&task=tracklog.sendtrklog&id='.$this->item->id); ?>">
	<i class="icon-mail"></i> <?php echo Text::_('COM_GATRACKLOG_SEND_LOG'); ?>
</a>

<?php /* ----------------------   Edit button   ------------------------------ */ ?>
<?php if($canEdit && $this->item->checked_out == 0): ?>
	<a class="btn btn-warning" href="<?php echo Route::_($editURL); ?>">
		<i class="icon-edit"></i> <?php echo Text::_("COM_GAGATRACKLOG_EDIT_ITEM"); ?>
	</a>
<?php endif; ?>

<?php /* ----------------------   Delete button and modal  ------------------------------ */ ?>
<?php if ($canDelete) : ?>
	<a href="<?php echo Route::_($removeURL, false, 2); ?>" class="btn btn-danger delete-button" type="button">
        <i class="icon-trash" ></i>
    </a>
    <script type="text/javascript">
    	jQuery(document).ready(function () {
    		jQuery('.delete-button').click(deleteItem);
    	});
    	function deleteItem() {
    		if (!confirm("<?php echo Text::_('COM_GAGATRACKLOG_DELETE_MESSAGE'); ?>")) {
    			return false;
    		}
    	}
    </script>
<?php endif; ?>
