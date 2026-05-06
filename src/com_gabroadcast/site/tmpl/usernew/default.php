<?php
/**
 * @version    4.2.1
 * @package    Com_Gabroadcast
 * @author     Glenn Arkell <glenn@glennarkell.com.au>
 * @copyright  2019 Glenn Arkell
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access
defined('_JEXEC') or die;

use \Joomla\CMS\Factory;
use \Joomla\CMS\Router\Route;
use \Joomla\CMS\Language\Text;
use \GlennArkell\Component\Gabroadcast\Administrator\Helper\GabroadcastHelper;

// load any assets required
$this->document->getWebAssetManager()
    ->usePreset('com_gabroadcast.gabroadcastpreset');

//Load admin language file
$lang = Factory::getLanguage();
$lang->load('com_gabroadcast', JPATH_ADMINISTRATOR);

$user = GabroadcastHelper::getSpecificUser();
$canEdit = $user->authorise('core.edit', 'com_gabroadcast');

if (!$canEdit && $user->authorise('core.edit.own', 'com_gabroadcast')) {
	$canEdit = $user->id == $this->item->created_by;
}

$canDelete = $user->authorise('core.delete','com_gabroadcast.usernew.'.$this->item->id);

?>

<div class="item_fields">

	<table class="table">
		

		<tr>
			<th><?php echo Text::_('COM_GABROADCAST_FORM_LBL_CREATED_DATE'); ?></th>
			<td><?php echo $this->item->created_date; ?></td>
		</tr>

		<tr>
			<th><?php echo Text::_('COM_GABROADCAST_FORM_LBL_MODIFIED_DATE'); ?></th>
			<td><?php echo $this->item->modified_date; ?></td>
		</tr>

		<tr>
			<th><?php echo Text::_('COM_GABROADCAST_FORM_LBL_USERNEW_CAT_ID'); ?></th>
			<td><?php echo $this->item->cat_id_name; ?></td>
		</tr>

		<tr>
			<th><?php echo Text::_('COM_GABROADCAST_FORM_LBL_USERNEW_NEWS_SUBJECT'); ?></th>
			<td><?php echo $this->item->news_subject; ?></td>
		</tr>

		<tr>
			<th><?php echo Text::_('COM_GABROADCAST_FORM_LBL_USERNEW_NEWS_DETAIL'); ?></th>
			<td><?php echo nl2br($this->item->news_detail ?? ''); ?></td>
		</tr>

		<tr>
			<th><?php echo Text::_('COM_GABROADCAST_FORM_LBL_USERNEW_PENDING_BCAST'); ?></th>
			<td><?php echo str_replace(',', '<br />', $this->item->pending_bcast ?? ''); ?></td>
		</tr>

		<tr>
			<th><?php echo Text::_('COM_GABROADCAST_FORM_LBL_USERNEW_COMMENT'); ?></th>
			<td><?php echo nl2br($this->item->comment ?? ''); ?></td>
		</tr>

	</table>

</div>

<a class="btn btn-secondary" href="<?php echo Route::_('index.php?option=com_gabroadcast&task=usernew.cancel'); ?>">
	<i class="icon-undo"></i> <?php echo Text::_("COM_GABROADCAST_RETURN"); ?>
</a>

<?php if ($canDelete) : ?>

	<a class="btn btn-danger pull-right" href="#deleteModal" role="button" data-bs-toggle="modal">
		<i class="icon-trash"></i> <?php echo Text::_("COM_GABROADCAST_DELETE_ITEM"); ?>
	</a>

	<div id="deleteModal" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="deleteModal" aria-hidden="true">
		<div class="modal-header">
			<button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">&times;</button>
			<h3><?php echo Text::_('COM_GABROADCAST_DELETE_ITEM'); ?></h3>
		</div>
		<div class="modal-body">
			<p><?php echo Text::sprintf('COM_GABROADCAST_DELETE_CONFIRM', $this->item->id); ?></p>
		</div>
		<div class="modal-footer">
			<button class="btn" data-dismiss="modal">Close</button>
			<a href="<?php echo Route::_('index.php?option=com_gabroadcast&task=usernew.remove&id=' . $this->item->id, false, 2); ?>" class="btn btn-danger">
				<?php echo Text::_('COM_GABROADCAST_DELETE_ITEM'); ?>
			</a>
		</div>
	</div>

<?php endif; ?>
