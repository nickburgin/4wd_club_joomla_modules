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
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use \GlennArkell\Component\Gacalevents\Administrator\Helper\GacaleventsHelper;

// load any assets required
$wa = $this->document->getWebAssetManager()
    ->usePreset('com_gacalevents.gacaleventspreset');

// Load admin language file
Factory::getApplication()->getLanguage()->load('com_gacalevents', JPATH_ADMINISTRATOR);

$user = Factory::getApplication()->getIdentity();
$canEdit = $user->authorise('core.edit', 'com_gacalevents');

if (!$canEdit && $user->authorise('core.edit.own', 'com_gacalevents'))
{
	$canEdit = $user->id == $this->item->created_by;
}

$edLink = GacaleventsHelper::getHTTPQuery(null, 'task', 'event.edit', 'id', $this->item->id);
$edURL = 'index.php?'.http_build_query($edLink, '', '&amp;');
$vuLink = GacaleventsHelper::getHTTPQuery(null, 'view', 'events', null, null);
$vuURL = 'index.php?'.http_build_query($vuLink, '', '&amp;');
$delLink = GacaleventsHelper::getHTTPQuery(null, 'task', 'event.remove', 'id', $this->item->id);
$delURL = 'index.php?'.http_build_query($delLink, '', '&amp;');

?>

<div class="item_fields">

	<table class="table">
		

		<tr>
			<th><?php echo Text::_('COM_GACALEVENTS_FORM_LBL_EVENT_CREATED_DATE'); ?></th>
			<td><?php echo $this->item->created_date; ?></td>
		</tr>

		<tr>
			<th><?php echo Text::_('COM_GACALEVENTS_FORM_LBL_EVENT_MODIFIED_DATE'); ?></th>
			<td><?php echo $this->item->modified_date; ?></td>
		</tr>

		<tr>
			<th><?php echo Text::_('COM_GACALEVENTS_FORM_LBL_EVENT_TITLE'); ?></th>
			<td><?php echo $this->item->title; ?></td>
		</tr>

		<tr>
			<th><?php echo Text::_('COM_GACALEVENTS_FORM_LBL_EVENT_BRIEF_DESC'); ?></th>
			<td><?php echo $this->item->brief_desc; ?></td>
		</tr>

		<tr>
			<th><?php echo Text::_('COM_GACALEVENTS_FORM_LBL_EVENT_EVENT_DETAILS'); ?></th>
			<td><?php echo $this->item->event_details; ?></td>
		</tr>

		<tr>
			<th><?php echo Text::_('COM_GACALEVENTS_FORM_LBL_EVENT_COMMENT'); ?></th>
			<td><?php echo nl2br($this->item->comment); ?></td>
		</tr>

	</table>

</div>

<a class="btn btn-secondary" href="<?php echo Route::_($vuURL); ?>">
	<i class="icon-undo"></i> <?php echo Text::_("COM_GACALEVENTS_RETURN"); ?>
</a>

<?php if($canEdit && $this->item->checked_out == 0): ?>

	<a class="btn btn-warning" href="<?php echo Route::_($edURL); ?>">
		<i class="icon-edit"></i> <?php echo Text::_("COM_GACALEVENTS_EDIT_ITEM"); ?>
	</a>

<?php endif; ?>

<?php if ($user->authorise('core.delete','com_gacalevents.event.'.$this->item->id)) : ?>

	<a class="btn btn-danger" href="#deleteModal" role="button" data-toggle="modal">
		<i class="icon-trash"></i> <?php echo Text::_("COM_GACALEVENTS_DELETE_ITEM"); ?>
	</a>

	<div id="deleteModal" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="deleteModal" aria-hidden="true">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
			<h3><?php echo Text::_('COM_GACALEVENTS_DELETE_ITEM'); ?></h3>
		</div>
		<div class="modal-body">
			<p><?php echo Text::sprintf('COM_GACALEVENTS_DELETE_CONFIRM', $this->item->id); ?></p>
		</div>
		<div class="modal-footer">
			<button class="btn" data-dismiss="modal">Close</button>
			<a href="<?php echo Route::_($delURL); ?>" class="btn btn-danger">
				<?php echo Text::_('COM_GACALEVENTS_DELETE_ITEM'); ?>
			</a>
		</div>
	</div>

<?php endif; ?>
