<?php
/**
 * @version     5.3.0
 * @package     com_gatripsys
 * @copyright   Copyright (C) 2012. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Created by Glenn Arkell - http://www.glennarkell.com
 */

// no direct access
defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Language\Text;
use \GlennArkell\Component\Gatripsys\Administrator\Helper\GatripsysHelper;

/** @var \Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->document->getWebAssetManager();
$wa->useScript('table.columns')
    ->useScript('multiselect');

$user	= GatripsysHelper::getSpecificUser();
$userId	= $user->get('id');
$listOrder	= $this->state->get('list.ordering');
$listDirn	= $this->state->get('list.direction');
//$trashed	= $this->state->get('filter.state') == -2 ? true : false;
$canOrder	= $user->authorise('core.edit.state', 'com_gatripsys');
$saveOrder	= $listOrder == 'a.ordering';
if ($saveOrder)
{
	$saveOrderingUrl = 'index.php?option=com_gatripsys&task=invoices.saveOrderAjax&tmpl=component';
	HTMLHelper::_('sortablelist.sortable', 'invoiceList', 'adminForm', strtolower($listDirn), $saveOrderingUrl);
}
$sortFields = $this->getSortFields();


/*
echo '<pre>Test <br />';
print_r(\Joomla\CMS\Factory::getApplication()->getUserState('com_gatripsys.test.data'));
echo '</pre>';
*/

?>

<form action="<?php echo Route::_('index.php?option=com_gatripsys&view=invoices'); ?>" method="post" name="adminForm" id="adminForm">
	<div class="row">
		<div class="col-md-12">
			<div id="j-main-container" class="j-main-container">

            	<?php echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this)); ?>

			<div class="clearfix"></div>
    	<table class="table table-striped" id="invoiceList">
    		<thead>
    			<tr>
                    <td class="w-1 text-center">
                        <?php echo HTMLHelper::_('grid.checkall'); ?>
                    /td>
					<th width="1%" class="nowrap center hidden-phone">
						<?php echo HTMLHelper::_('searchtools.sort', '', 'a.ordering', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING'); ?>
					</th>
					<th width="1%" class="nowrap center">
						<?php echo HTMLHelper::_('searchtools.sort', 'JSTATUS', 'a.state', $listDirn, $listOrder); ?>
					</th>
    				<th class='nowrap'>
    				<?php echo HTMLHelper::_('searchtools.sort',  'COM_GATRIPSYS_INVOICES_MEMBER_NAME', 'member_name', $listDirn, $listOrder); ?>
    				</th>
    				<th class='nowrap'>
    				<?php echo HTMLHelper::_('searchtools.sort',  'COM_GATRIPSYS_TRIP_TITLE', 'trip_title', $listDirn, $listOrder); ?>
    				</th>
    				<th class='nowrap center'>
    				<?php echo HTMLHelper::_('searchtools.sort',  'COM_GATRIPSYS_INVOICES_INVOICE_AMT', 'a.invoice_amt', $listDirn, $listOrder); ?>
    				</th>
    				<th class='nowrap center hidden-phone'>
    				<?php echo HTMLHelper::_('searchtools.sort',  'COM_GATRIPSYS_CREATED_DATE', 'a.created_date', $listDirn, $listOrder); ?>
    				</th>
    				<th class='nowrap center hidden-phone'>
    				<?php echo HTMLHelper::_('searchtools.sort',  'COM_GATRIPSYS_INVOICES_PAID_DATE', 'a.paid_date', $listDirn, $listOrder); ?>
    				</th>
                    <?php if (isset($this->items[0]->id)) { ?>
                    <th width="1%" class="nowrap center hidden-phone">
                        <?php echo HTMLHelper::_('searchtools.sort',  'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
                    </th>
                    <?php } ?>
    			</tr>
    		</thead>
    		<tfoot>
    			<tr>
    				<td colspan="10">
    					<?php echo $this->pagination->getListFooter(); ?>
    				</td>
    			</tr>
    		</tfoot>
    		<tbody>
    		<?php foreach ($this->items as $i => $item) :
    			$ordering	= ($listOrder == 'a.ordering');
    			$canCreate	= $user->authorise('core.create',		'com_gatripsys');
    			$canEdit	= $user->authorise('core.edit',			'com_gatripsys');
    			$canCheckin	= $user->authorise('core.manage',		'com_gatripsys');
    			$canChange	= $user->authorise('core.edit.state',	'com_gatripsys');
    			$trip = GatripsysHelper::getTripFromAttend($item->att_id);
    			?>
    
    			<tr class="row<?php echo $i % 2; ?>">
					<td >
						<?php echo HTMLHelper::_('grid.id', $i, $item->id); ?>
					</td>
					<td class="order nowrap center hidden-phone">
					<?php if ($canChange) :
						$disableClassName = '';
						$disabledLabel	  = '';
						if (!$saveOrder) :
							$disabledLabel    = Text::_('JORDERINGDISABLED');
							$disableClassName = 'inactive tip-top';
						endif; ?>
						<span class="sortable-handler hasTooltip <?php echo $disableClassName?>" title="<?php echo $disabledLabel?>">
							<i class="icon-menu"></i>
						</span>
						<input type="text" style="display:none" name="order[]" size="5" value="<?php echo $item->ordering;?>" class="width-20 text-area-order " />
					<?php else : ?>
						<span class="sortable-handler inactive" >
							<i class="icon-menu"></i>
						</span>
					<?php endif; ?>
					</td>
					<td class="center hidden-phone">
						<?php echo HTMLHelper::_('jgrid.published', $item->state, $i, 'invoices.', $canChange, 'cb'); ?>
						<?php if ($item->state == -2) {echo "Cancelled";} elseif ($item->state == 3) {echo "Refunded";} elseif ($item->state == 2) {echo "Paid";}?>
					</td>
    				<td class="nowrap">
    				    <?php if ($canEdit) : ?>
    				          <a href="<?php echo Route::_('index.php?option=com_gatripsys&task=invoice.edit&id='.(int) $item->id); ?>">
    					      <?php echo $item->member_name; ?></a>
    		            <?php else : ?>
    					      <?php echo $item->member_name; ?>
    		            <?php endif; ?>
    				</td>
    				<td class="nowrap">
    					<?php echo $trip->title; ?>
    				</td>
    				<td class="nowrap center">
    					<?php echo $item->invoice_amt; ?>
    				</td>
    				<td class="nowrap center hidden-phone">
    					<?php echo $item->created_date; ?>
    				</td>
    				<td class="nowrap center hidden-phone">
    					<?php echo $item->paid_date; ?>
    				</td>

    				<td class="nowrap center hidden-phone">
    					<?php echo (int) $item->id; ?>
    				</td>
    			</tr>
    			<?php endforeach; ?>
    		</tbody>
    	</table>

		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
		<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
		<?php echo HTMLHelper::_('form.token'); ?>
	</div>
	</div>
	</div>
</form>
<script>
    window.toggleField = function (id, task, field) {

        var f = document.adminForm, i = 0, cbx, cb = f[ id ];

        if (!cb) return false;

        while (true) {
            cbx = f[ 'cb' + i ];

            if (!cbx) break;

            cbx.checked = false;
            i++;
        }

        var inputField   = document.createElement('input');

        inputField.type  = 'hidden';
        inputField.name  = 'field';
        inputField.value = field;
        f.appendChild(inputField);

        cb.checked = true;
        f.boxchecked.value = 1;
        window.submitform(task);

        return false;
    };
</script>
