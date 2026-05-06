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
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use \GlennArkell\Component\Gafinance\Administrator\Helper\GafinanceHelper;

// load any assets required
$this->document->getWebAssetManager()
    ->usePreset('com_gafinance.gafinancepreset');

// Load admin language file
$lang = Factory::getLanguage();
$lang->load('com_gafinance', JPATH_ADMINISTRATOR);

$user    = GafinanceHelper::getSpecificUser();
$listOrder  = $this->state->get('list.ordering', 'a.inv_date');
$listDirn   = $this->state->get('list.direction', 'asc');
$canCreate  = $user->authorise('core.create', 'com_gafinance');
$canEdit    = $user->authorise('core.edit', 'com_gafinance');
$canCheckin = $user->authorise('core.manage', 'com_gafinance');
$canChange  = $user->authorise('core.edit.state', 'com_gafinance');
$canDelete  = $user->authorise('core.delete', 'com_gafinance');
$canTreasurer  = $user->authorise('core.treasury', 'com_gafinance');

$inv_prefix = $this->params->get('inv_prefix');
$inv_no_offset = $this->params->get('inv_no_offset');

?>

<h2><?php echo Text::_('COM_GAFINANCE_TITLE_INVOICES'); ?></h2>

<form action="<?php echo htmlspecialchars(Uri::getInstance()->toString()); ?>" method="post"
      name="adminForm" id="adminForm">

	<?php echo LayoutHelper::render('default_filter', array('view' => $this), dirname(__FILE__)); ?>

    <div class="table-responsive">
	<table class="table table-striped" id="invoiceList">
		<thead>
		<tr>
			<th class=''>
				<?php echo HTMLHelper::_('grid.sort',  'COM_GAFINANCE_INVOICES_CLIENT_ID', 'a.client_id', $listDirn, $listOrder); ?>
			</th>
			<th class="hidden-phone">
				<?php echo HTMLHelper::_('grid.sort',  'COM_GAFINANCE_INVOICES_INVOICE_TYPE', 'a.invoice_type', $listDirn, $listOrder); ?>
			</th>
			<th class='right'>
				<?php echo HTMLHelper::_('grid.sort',  'COM_GAFINANCE_INVOICES_INVOICE_COST', 'a.invoice_cost', $listDirn, $listOrder); ?>
			</th>
			<th class=''>
				<?php echo HTMLHelper::_('grid.sort',  'COM_GAFINANCE_INVOICES_INV_DATE', 'a.inv_date', $listDirn, $listOrder); ?>
			</th>


			<?php if ($canTreasurer): ?>
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

			<?php 
				if (!$canEdit && $canEditOwn) {
					$canEdit = $user->id == $item->created_by;
				}
				$inv = 'images/finances/invoices/'.$inv_prefix.str_pad(($item->id + $inv_no_offset), 6, '0', STR_PAD_LEFT).'.pdf';
				
				// Set up links for actions
				$edInv = GafinanceHelper::getHTTPQuery(null, 'task', 'invoice.edit', 'id', $item->id);
				$edlink = 'index.php?'.http_build_query($edInv, '', '&amp;');
				$pdfInv = GafinanceHelper::getHTTPQuery(null, 'task', 'invoice.genInv', 'id', $item->id);
				$pdflink = 'index.php?'.http_build_query($pdfInv, '', '&amp;');
				$sendInv = GafinanceHelper::getHTTPQuery(null, 'task', 'invoice.sendInv', 'id', $item->id);
				$sendlink = 'index.php?'.http_build_query($sendInv, '', '&amp;');
				$canInv = GafinanceHelper::getHTTPQuery(null, 'task', 'invoice.cancelInvoice', 'id', $item->id);
				$canlink = 'index.php?'.http_build_query($canInv, '', '&amp;');
				$payInv = GafinanceHelper::getHTTPQuery(null, 'task', 'invoice.markPaid', 'id', $item->id);
				$paylink = 'index.php?'.http_build_query($payInv, '', '&amp;');
				$archInv = GafinanceHelper::getHTTPQuery(null, 'task', 'invoice.archiveInvoice', 'id', $item->id);
				$archlink = 'index.php?'.http_build_query($archInv, '', '&amp;');
 			?>

			<tr class="row<?php echo $i % 2; ?>">

				<td>
					<?php if (isset($item->checked_out) && $item->checked_out) : ?>
						<?php echo HTMLHelper::_('jgrid.checkedout', $i, $item->uEditor, $item->checked_out_time, 'invoices.', $canCheckin); ?>
					<?php endif; ?>
					<?php if($canTreasurer): ?>
						<a href="<?php echo Route::_('index.php?option=com_gafinance&view=invoice&id='.(int) $item->id); ?>">
							<?php echo $this->escape($item->client_id_name); ?>
						</a>
					<?php else: ?>
						<?php echo $item->client_id_name; ?>
					<?php endif; ?>
				</td>
				<td class="hidden-phone">
					<?php echo $item->invoice_type_name; ?>
				</td>
				<td class="right">
					<?php echo number_format($item->invoice_cost,2); ?>
				</td>
				<td>
					<?php echo !empty($item->inv_date) ? HTMLHelper::date($item->inv_date, Text::_('COM_GAFINANCE_DISPLAY_DATETXT')) : '-'; ?>
				</td>

				<?php if ($canTreasurer): ?>
					<td class="center hidden-phone">
						<?php if($item->state == 5): ?>      <!-- Prepared Only and ready to create PDF -->
                            <a class="btn btn-mini btn-primary" href="<?php echo Route::_($edlink); ?>" title="Update Invoice">
                                <i class="icon-edit"></i>
                            </a>
                            <a class="btn btn-mini btn-success" href="<?php echo Route::_($pdflink); ?>" title="Generate Invoice PDF">
                                <i class="icon-file-2"></i>
                            </a>
						<?php elseif($item->state == 4): ?>      <!-- Invoices ready to be emailed -->
                            <a class="btn btn-mini btn-warning" href="<?php echo Route::_($sendlink.'&smail=0'); ?>" title="<?php echo Text::_('COM_GAFINANCE_EMAIL_INVOICE_DESC');?>">
                                <i class="icon-arrow-right-4"></i>
                            </a>
                            <a class="btn btn-mini btn-warning" href="<?php echo Route::_($sendlink.'&smail=1'); ?>" title="<?php echo Text::_('COM_GAFINANCE_POST_INVOICE_DESC');?>">
                                <i class="icon-mail-2"></i>
                            </a>
                            <a class="btn btn-mini btn-danger" href="<?php echo Route::_($canlink); ?>" title="Cancel Invoice">
                                <i class="icon-cancel-2"></i>
                            </a>
							<?php if(\file_exists($inv)): ?>
								<a class="btn btn-mini btn-info" href="<?php echo $inv; ?>" target="_blank" title="Preview Invoice">
		                            <i class="icon-search"></i>
		                        </a>
							<?php endif; ?>
						<?php elseif($item->state == 3): ?>      <!-- Invoices to be paid -->
                                <a class="btn btn-secondary btn-credit" href="<?php echo Route::_($paylink); ?>" title="Click to mark Invoice as Paid" >
                                    <i class="icon-credit"></i>
                                </a>
							<?php if(\file_exists($inv)): ?>
								<a class="btn btn-mini btn-info" href="<?php echo $inv; ?>" target="_blank" title="Preview Invoice">
		                            <i class="icon-search"></i>
		                        </a> &nbsp; &nbsp;
                                <a class="btn btn-mini btn-warning" href="<?php echo Route::_($sendlink.'&smail=0'); ?>" title="Click to email Invoice to Client">
                                    <i class="icon-arrow-right-4"></i>
                                </a>
							<?php endif; ?>
						<?php else: ?>      <!-- All Completed Invoices -->
							<?php if(\file_exists($inv)): ?>
								<a class="btn btn-mini btn-info" href="<?php echo $inv; ?>" target="_blank" title="Preview Invoice">
		                            <i class="icon-search"></i>
		                        </a>
							<?php endif; ?>
                            <a class="btn btn-mini btn-secondary" href="<?php echo Route::_($archlink); ?>" title="Click to Archive Invoice">
                                <i class="icon-archive"></i>
                            </a>
						<?php endif; ?>
					</td>
				<?php endif; ?>

			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>
        </div>
	<?php if ($canCreate) : ?>
		<a href="<?php echo Route::_('index.php?option=com_gafinance&task=invoiceform.edit&id=0', false, 0); ?>"
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
