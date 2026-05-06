<?php
/**
 * @version     5.1.6
 * @package     com_gausers
 * @copyright   Copyright (C) 2012. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Created by Glenn Arkell - http://www.glennarkell.com
 */

// no direct access
defined('_JEXEC') or die;

use \Joomla\CMS\HTML\HTMLHelper;
use \Joomla\CMS\Factory;
use \Joomla\CMS\Uri\Uri;
use \Joomla\CMS\Router\Route;
use \Joomla\CMS\Layout\LayoutHelper;
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\Date\Date;
use \Joomla\CMS\Component\ComponentHelper;
use \Joomla\CMS\User\UserHelper;
use \GlennArkell\Component\Gausers\Administrator\Helper\GausersHelper;

/** @var \Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->document->getWebAssetManager();
$wa->useScript('table.columns')
    ->useScript('multiselect');

$user	= GausersHelper::getSpecificUser();
$listOrder	= $this->state->get('list.ordering', 'member.name');
$listDirn	= $this->state->get('list.direction', 'ASC');
$saveOrder	= $listOrder == 'a.ordering';
$canOrder  = $user->authorise('core.edit.state', 'com_gausers');
$version = GausersHelper::getComponentVersion();

if ($saveOrder) {
	$saveOrderingUrl = 'index.php?option=com_gausers&task=invoices.saveOrderAjax&tmpl=component';
    HTMLHelper::_('sortablelist.sortable', 'invoiceList', 'adminForm', strtolower($listDirn), $saveOrderingUrl);
}

/*
echo '<pre>Test <br />';
print_r($profile);
echo '</pre>';
print_r(\Joomla\CMS\Factory::getApplication()->getUserState('com_gausers.test.data'));
*/

?>

<form action="<?php echo Route::_('index.php?option=com_gausers&view=invoices'); ?>" 
    method="post" name="adminForm" id="adminForm">
	<div class="row">
		<div class="col-md-12">
			<div id="j-main-container" class="j-main-container">

            	<?php echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this)); ?>

				<div class="clearfix"></div>
            	<table class="table table-striped" id="invoiceList">
        			<caption id="captionTable">
        				<?php echo Text::_('Version: '.$version); ?>
        			</caption>
            		<thead>
            			<tr>
                            <td class="w-1 text-center">
                                <?php echo HTMLHelper::_('grid.checkall'); ?>
                            </td>
        					<?php if (isset($this->items[0]->ordering)): ?>
        						<th width="1%" class="nowrap center hidden-phone">
        	                        <?php echo HTMLHelper::_('searchtools.sort', '', 'a.ordering', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING', 'icon-menu-2'); ?>
        	                    </th>
        					<?php endif; ?>
        					<?php if (isset($this->items[0]->state)): ?>
        						<th width="1%" class="nowrap center">
        							<?php echo HTMLHelper::_('searchtools.sort', 'JSTATUS', 'a.state', $listDirn, $listOrder); ?>
        						</th>
        					<?php endif; ?>
        
            				<th class='nowrap'>
            				<?php echo HTMLHelper::_('searchtools.sort',  'COM_GAUSERS_INVOICES_MEMBER_NAME', 'member.name', $listDirn, $listOrder); ?>
            				</th>
            				<th class='nowrap center'>
            				<?php echo HTMLHelper::_('searchtools.sort',  'COM_GAUSERS_INVOICES_INVOICE_AMT', 'a.invoice_amt', $listDirn, $listOrder); ?>
            				</th>
            				<th class='nowrap center hidden-phone'>
            				<?php echo HTMLHelper::_('searchtools.sort',  'COM_GAUSERS_CREATED_DATE', 'a.created_date', $listDirn, $listOrder); ?>
            				</th>
            				<th class='nowrap center'>
            				<?php echo HTMLHelper::_('searchtools.sort',  'COM_GAUSERS_INVOICES_PAID_DATE', 'a.paid_date', $listDirn, $listOrder); ?>
            				</th>
            				<th class='nowrap center'>
            				<?php echo HTMLHelper::_('searchtools.sort',  'COM_GAUSERS_INVOICES_MSHIP_ID', 'memtype_name', $listDirn, $listOrder); ?>
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
        					<td colspan="<?php echo isset($this->items[0]) ? count(get_object_vars($this->items[0])) : 10; ?>">
        						<?php echo $this->pagination->getListFooter(); ?>
        					</td>
        	   			</tr>
            		</tfoot>
            		<tbody>
            		<?php foreach ($this->items as $i => $item) :
            			$ordering	= ($listOrder == 'a.ordering');
            			$canCreate	= $user->authorise('core.create',		'com_gausers');
            			$canEdit	= $user->authorise('core.edit',			'com_gausers');
            			$canCheckin	= $user->authorise('core.manage',		'com_gausers');
            			$canChange	= $user->authorise('core.edit.state',	'com_gausers');
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
        					</td>
            				<td class="nowrap">
            				    <?php if ($canEdit) : ?>
            				          <a href="<?php echo Route::_('index.php?option=com_gausers&task=invoice.edit&id='.(int) $item->id); ?>">
            					      <?php echo $item->member_name; ?></a>
            		            <?php else : ?>
            					      <?php echo $item->member_name; ?>
            		            <?php endif; ?>
            				</td>
            				<td class="nowrap center">
            					<?php echo $item->invoice_amt; ?>
            				</td>
            				<td class="nowrap center hidden-phone">
             					<?php echo !empty($item->created_date) ? HTMLHelper::date($item->created_date, Text::_('COM_GAUSERS_ABRV_DISPLAY_DATE')) : ''; ?>
            				</td>
            				<td class="nowrap center hidden-phone">
            					<?php echo !empty($item->paid_date) ? HTMLHelper::date($item->paid_date, Text::_('COM_GAUSERS_ABRV_DISPLAY_DATE')) : ''; ?>
            				</td>
            				<td class="center">
            					<?php echo $item->memtype_name; ?>
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
                <input type="hidden" name="list[fullorder]" value="<?php echo $listOrder; ?> <?php echo $listDirn; ?>"/>
        		<?php echo HTMLHelper::_('form.token'); ?>
			</div>
		</div>
	</div>
</form>
