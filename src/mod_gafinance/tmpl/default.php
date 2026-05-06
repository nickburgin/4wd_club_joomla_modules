<?php
/*
# ------------------------------------------------------------------------
# @version     5.0.2
# @copyright   Copyright (C) 2014. All rights reserved.
# @license     GNU General Public License version 2 or later; see LICENSE.txt
# Author:      Glenn Arkell
# Websites:    http://www.glennarkell.com.au
# ------------------------------------------------------------------------
*/
// no direct access
defined('_JEXEC') or die;

use \Joomla\CMS\Factory;
use \Joomla\CMS\Router\Route;
use \Joomla\CMS\HTML\HTMLHelper;
use \Joomla\CMS\Uri\Uri;
use \Joomla\CMS\Language\Text;
use \GlennArkell\Module\Gafinance\Site\Helper\GafinanceHelper;
use \GlennArkell\Component\Gafinance\Administrator\Helper\GafinanceHelper AS CompFinanceHelper;

$moduleclass_sfx	= $params->get('moduleclass_sfx', '');
$ht = $params->get('header_tag');
$hc = $params->get('header_class');

$today = CompFinanceHelper::getTodaysDate();
$cntr = 0;
$total_amount = 0;
$openBal = Factory::getApplication()->getUserState('mod_gafinance.balance.open');
$closeBal = Factory::getApplication()->getUserState('mod_gafinance.balance.close');

?>

<div class="mod_gafinance" >
	<div id="trans<?php echo $module->id; ?>">
		<h2>Transactions for <?php echo $items[0]->accnt_name; ?> Account</h2>
	    <table class="table table-striped" id="transList">
			<tr><th>Date</th><th>Description</th><th>Type</th><th>Debit</th><th>Credit</th><th>Balance</th></tr>
			<?php foreach ($items as $i => $item) : ?>
				<tr class="row<?php echo $i % 2; ?>">
					<td class="translist"><?php echo $item->tran_date > 0 ? HTMLHelper::date($item->tran_date, Text::_('COM_GAFINANCE_NORMAL_DATE')) : '-'; ?></td>
					<td class="translist"><?php echo $item->cat_name.' - '.$item->tran_desc.' - '.$item->tran_ref; ?></td>
					<td class="translist"><?php if ($item->tran_type == 'E') { echo 'Expense'; } elseif ($item->tran_type == 'I') { echo 'Income'; } elseif ($item->tran_type == 'D') { echo 'Journal Entry'; } else { echo 'Balance Reset'; } ?></td>
					<td style="text-align:right;"><?php if ($item->tran_amount < 0) {echo number_format($item->tran_amount,2);} ?></td>
					<td style="text-align:right;"><?php if ($item->tran_amount > 0) {echo number_format($item->tran_amount,2);} ?></td>
					<td style="text-align:right;">
					<?php
					if ($item->tran_amount > 0) {
						echo number_format($closeBal,2);
						$closeBal = $closeBal - $item->tran_amount;
					} else {
						echo number_format($closeBal,2);
						$closeBal = $closeBal + ($item->tran_amount * -1);
					}

					?>
					</td>
				<tr>
	        <?php endforeach; ?>
        </table>
	</div>
</div>
