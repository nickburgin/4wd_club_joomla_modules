<?php
/**
 * @version    5.1.0
 * @package    Com_Gafinance
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
use \GlennArkell\Component\Gafinance\Administrator\Helper\GafinanceHelper;

// load any assets required
$this->document->getWebAssetManager()
    ->usePreset('com_gafinance.gafinancepreset');

// Load admin language file
$lang = Factory::getLanguage();
$lang->load('com_gafinance', JPATH_ADMINISTRATOR);

$user    = GafinanceHelper::getSpecificUser();
$listOrder  = $this->state->get('list.ordering','a.accnt_name');
$listDirn   = $this->state->get('list.direction','ASC');
$canCreate  = $user->authorise('core.create', 'com_gafinance');
$canEdit    = $user->authorise('core.edit', 'com_gafinance');
$canCheckin = $user->authorise('core.manage', 'com_gafinance');
$canChange  = $user->authorise('core.edit.state', 'com_gafinance');
$canDelete  = $user->authorise('core.delete', 'com_gafinance');
$canTreasurer  = $user->authorise('core.treasury', 'com_gafinance');

?>

<h2><?php echo Text::_('COM_GAFINANCE_TITLE_ACCOUNTS'); ?></h2>

<form action="<?php echo htmlspecialchars(Uri::getInstance()->toString()); ?>" method="post"
      name="adminForm" id="adminForm" class="com-content-category__articles">

	<?php echo LayoutHelper::render('default_filter', array('view' => $this), dirname(__FILE__)); ?>

    <div class="table-responsive com-contact-categories categories-list">
	<table class="table table-striped" id="accountList">
		<thead>
		<tr>
			<?php if (isset($this->items[0]->state)): ?>
				<th width="5%" class="hidden-phone">
					<?php echo HTMLHelper::_('grid.sort', 'COM_GAFINANCE_ACTIVE', 'a.state', $listDirn, $listOrder); ?>
				</th>
			<?php endif; ?>

			<th class=''>
				<?php echo HTMLHelper::_('grid.sort',  'COM_GAFINANCE_ACCOUNTS_ACCNT_NAME', 'a.accnt_name', $listDirn, $listOrder); ?>
			</th>
			<th class=''>
				<?php echo HTMLHelper::_('grid.sort',  'COM_GAFINANCE_ACCOUNTS_ACCNT_BSB', 'a.accnt_bsb', $listDirn, $listOrder); ?>
			</th>
			<th class=''>
				<?php echo HTMLHelper::_('grid.sort',  'COM_GAFINANCE_ACCOUNTS_ACCNT_NUMBER', 'a.accnt_number', $listDirn, $listOrder); ?>
			</th>

			<?php if ($canEdit || $canDelete): ?>
				<th class="center hidden-phone">
					<?php echo Text::_('COM_GAFINANCE_ACTIONS'); ?>
				</th>
			<?php endif; ?>

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
		<?php foreach ($this->items as $i => $item) : ?>

			<?php $canEdit = GafinanceHelper::canUserEdit($item, $user); ?>

			<tr class="row<?php echo $i % 2; ?>">

				<?php if (isset($this->items[0]->state)) : ?>
					<?php $class = ($canChange) ? 'active' : 'disabled'; ?>
					<td class="center hidden-phone">
						<a class="btn btn-micro <?php echo $class; ?>" href="<?php echo ($canChange) ? Route::_('index.php?option=com_gafinance&task=account.publish&id=' . $item->id . '&state=' . (($item->state + 1) % 2), false, 2) : '#'; ?>">
							<?php if ($item->state == 1): ?>
								<i class="icon-publish"></i>
							<?php else: ?>
								<i class="icon-unpublish"></i>
							<?php endif; ?>
						</a>
					</td>
				<?php endif; ?>

				<td>
					<?php if (isset($item->checked_out) && $item->checked_out) : ?>
						<?php echo HTMLHelper::_('jgrid.checkedout', $i, $item->uEditor, $item->checked_out_time, 'accounts.', $canCheckin); ?>
					<?php endif; ?>
					<a href="<?php echo Route::_('index.php?option=com_gafinance&view=account&id='.(int) $item->id); ?>">
						<?php echo $this->escape($item->accnt_name); ?>
					</a>
				</td>
				<td>
					<?php echo $item->accnt_bsb; ?>
				</td>
				<td>
					<?php echo $item->accnt_number; ?>
				</td>
				
				<?php if ($canEdit || $canDelete): ?>
					<td class="center hidden-phone">
						<?php if ($canEdit): ?>
							<a href="<?php echo Route::_('index.php?option=com_gafinance&task=account.edit&id=' . $item->id, false, 2); ?>" class="btn btn-mini" type="button"><i class="icon-edit" ></i></a>
						<?php endif; ?>
						<?php if ($canDelete): ?>
							<a href="<?php echo Route::_('index.php?option=com_gafinance&task=accountform.remove&id=' . $item->id, false, 2); ?>" class="btn btn-mini delete-button" type="button"><i class="icon-trash" ></i></a>
						<?php endif; ?>
					</td>
				<?php endif; ?>

			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>
        </div>
	<?php if ($canCreate) : ?>
		<a href="<?php echo Route::_('index.php?option=com_gafinance&task=accountform.edit&id=0', false, 0); ?>"
		   class="btn btn-success btn-small"><i
				class="icon-plus"></i>
			<?php echo Text::_('COM_GAFINANCE_ADD_ITEM'); ?></a>
	<?php endif; ?>

	<input type="hidden" name="task" value=""/>
	<input type="hidden" name="boxchecked" value="0"/>
	<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>"/>
	<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>"/>
	<?php echo HTMLHelper::_('form.token'); ?>
</form>

<?php if($canDelete) : ?>
<script type="text/javascript">

	jQuery(document).ready(function () {
		jQuery('.delete-button').click(deleteItem);
	});

	function deleteItem() {

		if (!confirm("<?php echo Text::_('COM_GAFINANCE_DELETE_MESSAGE'); ?>")) {
			return false;
		}
	}
</script>
<?php endif; ?>
