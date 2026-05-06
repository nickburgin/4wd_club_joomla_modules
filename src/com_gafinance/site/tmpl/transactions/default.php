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
$listOrder  = $this->state->get('list.ordering', 'a.tran_date');
$listDirn   = $this->state->get('list.direction', 'desc');
$canCreate  = $user->authorise('core.create', 'com_gafinance');
$canEdit    = $user->authorise('core.edit', 'com_gafinance');
$canEditOwn = $user->authorise('core.edit.own', 'com_gafinance');
$canCheckin = $user->authorise('core.manage', 'com_gafinance');
$canChange  = $user->authorise('core.edit.state', 'com_gafinance');
$canDelete  = $user->authorise('core.delete', 'com_gafinance');
$canTreasurer = $user->authorise('core.treasury', 'com_gafinance');

$pageOpenID = isset($this->items[0]) ? $this->items[0]->id : 0;

$baseURL = 'index.php?';
$createTran = GafinanceHelper::getHTTPQuery(null, 'task', 'transactionform.edit', 'id', 0);
$createURL = $baseURL.\http_build_query($createTran, '', '&amp;');
$rptTran = GafinanceHelper::getHTTPQuery(null, 'view', 'transactionform', 'layout', 'rptreq');
$rptURL = $baseURL.\http_build_query($rptTran, '', '&amp;');

/*
echo '<pre>Test<br />';
print_r($this->pagination->getRowOffset(10));
echo '</pre>';
print_r($this->pagination->getRowOffset(10));
*/
?>

<h2><?php echo Text::_('COM_GAFINANCE_TITLE_TRANSACTIONS'); ?></h2>

<form action="<?php echo htmlspecialchars(Uri::getInstance()->toString()); ?>" method="post"
      name="adminForm" id="adminForm">

	<?php echo LayoutHelper::render('default_filter', array('view' => $this), dirname(__FILE__)); ?>

    <div class="table-responsive">

	    <?php if($canTreasurer): ?>
	    	<p class="center">As you have treasury access, here is a download button for audit purposes.
				<a href="<?php echo Route::_('index.php?option=com_gafinance&task=transactions.auditExtract'); ?>" title="Audit Extract">
				<i class="icon-download"></i>
				</a>
			</p>
	    <?php endif; ?>
		<table class="table table-striped" id="transactionList">
			<thead>
			<tr>
				<?php if (isset($this->items[0]->state)): ?>
					<th width="5%" class="hidden-phone">
						<?php echo HTMLHelper::_('grid.sort', 'GABANKED', 'a.state', $listDirn, $listOrder); ?>
					</th>
				<?php endif; ?>
	
				<th class=''>
					<?php echo HTMLHelper::_('grid.sort',  'COM_GAFINANCE_TRANSACTIONS_USER_ID', 'user_id_name', $listDirn, $listOrder); ?>
				</th>
				<th class=''>
					<?php echo HTMLHelper::_('grid.sort',  'COM_GAFINANCE_DATE', 'a.tran_date', $listDirn, $listOrder); ?>
				</th>
				<th class=''>
					<?php echo HTMLHelper::_('grid.sort',  'COM_GAFINANCE_TRAN_DESC', 'a.tran_desc', $listDirn, $listOrder); ?>
				</th>
				<th class=''>
					<?php echo HTMLHelper::_('grid.sort',  'COM_GAFINANCE_TRAN_TYPE', 'cat_id_name', $listDirn, $listOrder); ?>
				</th>
				<th class=''>
					<?php echo HTMLHelper::_('grid.sort',  'COM_GAFINANCE_AMOUNT', 'a.tran_amount', $listDirn, $listOrder); ?>
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
					if ($item->state == 0 && $item->tran_type == 'I') {
						$classstyle = ' green';
					} elseif ($item->state == 0 && $item->tran_type == 'E') {
						$classstyle = ' red';
					} else {
						$classstyle = '';
					}
					if ($item->tran_amount < 0) {
						$eistyle1 = '<span style="color:#bd363f;">'; $eistyle2 = '</span>';
					} else { 
						$eistyle1 = ''; $eistyle2 = ''; 
					}
					
					if ($this->params->get('mship_single',0)) {
						//get member information
						$member = GafinanceHelper::breakdownNamesFromUserID($item->user_id);
						// combine name fields and overload the user_id
						$item->user_id = GafinanceHelper::combineNames($member);
					}
					
					if (!$canEdit && $canEditOwn) {
						$canEdit = $user->id == $item->created_by;
					}
					
				?>

				<tr class="row<?php echo $i % 2; ?>">

					<?php if (isset($this->items[0]->state)) : ?>
						<?php $class = ($canChange) ? 'active' : 'disabled'; ?>
						<td class="center hidden-phone">
							<a class="btn btn-micro <?php echo $class; ?>" href="<?php echo ($canChange) ? Route::_('index.php?option=com_gafinance&task=transaction.publish&id=' . $item->id . '&state=' . (($item->state + 1) % 2), false, 2) : '#'; ?>">
							<?php if ($item->state == 1): ?>
								<i class="icon-publish"></i>
							<?php else: ?>
								<i class="icon-unpublish"></i>
							<?php endif; ?>
							</a>
						</td>
					<?php endif; ?>
	
					<td class="small">
						<?php if (isset($item->checked_out) && $item->checked_out) : ?>
							<?php echo HTMLHelper::_('jgrid.checkedout', $i, $item->uEditor, $item->checked_out_time, 'transactions.', $canCheckin); ?>
						<?php endif; ?>
						<?php if($canTreasurer): ?>
							<a href="<?php echo Route::_('index.php?option=com_gafinance&view=transaction&id='.(int) $item->id); ?>">
								<?php echo $this->escape($item->user_id_name); ?>
							</a>
						<?php else: ?>
							<?php echo $item->user_id_name; ?>
						<?php endif; ?>
					</td>
					<td class="small">
						<span class="<?php echo $classstyle; ?>">
                            <?php echo $item->tran_date > 0 ? HTMLHelper::date($item->tran_date, Text::_('COM_GAFINANCE_DISPLAY_DATETXT')) : '-'; ?>
                        </span>
					</td>
					<td class="small">
						<span class="<?php echo $classstyle; ?>">
    						<?php if (!empty($item->tran_file) && \file_exists($item->tran_file)) : ?>
    							<a href="<?php echo $item->tran_file; ?>" target="_blank"><?php echo substr($item->tran_desc,0,55); ?></a>
    						<?php else: ?>
    							<?php echo substr($item->tran_desc,0,55).' - '.$item->tran_ref; ?>
    						<?php endif; ?>
                        </span>
					</td>
					<td class="small">
						<span class="<?php echo $classstyle; ?>">
    						<?php echo $item->cat_id_name; ?>
                        </span>
					</td>
					<td class="small right">
						<?php echo $eistyle1.number_format($item->tran_amount,2).$eistyle2; ?>
					</td>

					<?php if ($canTreasurer): ?>
						<td class="center hidden-phone">
							<a href="<?php echo Route::_('index.php?option=com_gafinance&task=transaction.edit&id=' . $item->id, false, 2); ?>" 
								class="btn btn-secondary" type="button"><i class="icon-edit" ></i>
							</a>
							<?php if ($canDelete): ?>
								<a href="<?php echo Route::_('index.php?option=com_gafinance&task=transactionform.remove&id=' . $item->id, false, 2); ?>"
									class="btn btn-danger delete-button" type="button"><i class="icon-trash" ></i>
								</a>
							<?php endif; ?>
						</td>
					<?php endif; ?>
	
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
        <p> </p>
        <p class="small center"><span style="color:red;">Note:</span> Items in red indicate cheques unpresented and items in green are funds to be banked.</p>

    </div>
	<?php if ($canCreate || $canTreasurer) : ?>
		<a href="<?php echo Route::_($createURL); ?>" class="btn btn-success btn-small">
            <i class="icon-plus"></i> <?php echo Text::_('COM_GAFINANCE_ADD_ITEM'); ?>
        </a>
	<?php endif; ?>
	<?php if ($canTreasurer) : ?>
		<a href="<?php echo Route::_($rptURL); ?>" class="btn btn-warning btn-small">
            <i class="icon-print"></i> <?php echo Text::_('COM_GAFINANCE_REPORTS'); ?>
        </a>
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
