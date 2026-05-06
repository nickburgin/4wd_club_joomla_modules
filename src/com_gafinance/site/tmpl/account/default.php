<?php
/**
 * @version    5.2.3
 * @package    Com_Gafinance
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
use \GlennArkell\Component\Gafinance\Administrator\Helper\GafinanceHelper;

// load any assets required
$this->document->getWebAssetManager()
    ->usePreset('com_gafinance.gafinancepreset');

// Load admin language file
$lang = Factory::getLanguage();
$lang->load('com_gafinance', JPATH_ADMINISTRATOR);

$user    = GafinanceHelper::getSpecificUser();
$canEdit = GafinanceHelper::canUserEdit($this->item, $user);
$canDelete = $user->authorise('core.delete','com_gafinance');

// Display a heading for the page
if ($this->params->get('page_title', '') > '') {
	echo '<h1>'.$this->params->get('page_title').'</h1>';
} else { 
	echo '<h1>'.Text::_('COM_GAFINANCE_TITLE_ACCOUNT').'</h1>';
}
?>

<div class="item_fields">

	<table class="table">

		<tr>
			<th><?php echo Text::_('COM_GAFINANCE_FORM_LBL_CREATED_DATE'); ?></th>
			<td><?php echo $this->item->created_date; ?></td>
		</tr>

		<tr>
			<th><?php echo Text::_('COM_GAFINANCE_FORM_LBL_ACCOUNT_ACCNT_NAME'); ?></th>
			<td><?php echo $this->item->accnt_name; ?></td>
		</tr>

		<tr>
			<th><?php echo Text::_('COM_GAFINANCE_FORM_LBL_ACCOUNT_ACCNT_BSB'); ?></th>
			<td><?php echo $this->item->accnt_bsb; ?></td>
		</tr>

		<tr>
			<th><?php echo Text::_('COM_GAFINANCE_FORM_LBL_ACCOUNT_ACCNT_NUMBER'); ?></th>
			<td><?php echo $this->item->accnt_number; ?></td>
		</tr>

		<tr>
			<th><?php echo Text::_('COM_GAFINANCE_FORM_LBL_COMMENT'); ?></th>
			<td><?php echo nl2br($this->item->comment); ?></td>
		</tr>

	</table>

</div>

<?php /* ----------------------   Return button   ------------------------------ */ ?>
<a class="btn btn-secondary" href="<?php echo Route::_('index.php?option=com_gafinance&view=accounts'); ?>">
	<i class="icon-undo"></i> <?php echo Text::_("COM_GAFINANCE_RETURN"); ?>
</a>

<?php /* ----------------------   Edit button   ------------------------------ */ ?>
<?php if($canEdit && $this->item->checked_out == 0): ?>
	<a class="btn btn-warning"
		href="<?php echo Route::_('index.php?option=com_gafinance&task=account.edit&id='.$this->item->id); ?>">
		<i class="icon-edit"></i> <?php echo Text::_("COM_GAFINANCE_EDIT_ITEM"); ?>
	</a>
<?php endif; ?>

<?php /* ----------------------   Delete button and modal  ------------------------------ */ ?>
<?php if ($canDelete) : ?>
	<button class="btn btn-danger w5rem mb-1" data-bs-target="#deleteModal" data-bs-toggle="modal">
		<i class="icon-trash"></i> <?php echo Text::_("COM_GAFINANCE_DELETE_ITEM"); ?>
	</button>

	<?php HTMLHelper::_('bootstrap.renderModal', 'deleteModal'); ?>

	<div id="deleteModal" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="deleteModal" aria-hidden="true">
		<div class="modal-header">
			<button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">&times;</button>
			<h3><?php echo Text::_('COM_GAFINANCE_DELETE_ITEM'); ?></h3>
		</div>
		<div class="modal-body">
			<p><?php echo Text::sprintf('COM_GAFINANCE_DELETE_CONFIRM', $this->item->id); ?></p>
		</div>
		<div class="modal-footer">
			<button class="btn" data-bs-dismiss="modal">Close</button>
			<a class="btn btn-danger"
				href="<?php echo Route::_('index.php?option=com_gafinance&task=account.remove&id=' . $this->item->id, false, 2); ?>">
				<?php echo Text::_('COM_GAFINANCE_DELETE_ITEM'); ?>
			</a>
		</div>
	</div>

<?php endif; ?>

<div class="item_fields">
    <div class="container-reconciliation" style="clear:both;">
	     <jdoc:include type="modules" name="reconciliation-<?php echo $this->item->id; ?>" style="none" />
    </div>
</div>
