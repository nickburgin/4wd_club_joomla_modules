<?php
/*
# ------------------------------------------------------------------------
# @version     5.3
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

$app = Factory::getApplication();
$wa = $app->getDocument()->getWebAssetManager();
$wa->registerAndUseStyle('mod_gafinance', 'mod_gafinance/style.css');

// Load admin language file
$lang = Factory::getLanguage();
$lang->load('com_gafinance', JPATH_ADMINISTRATOR);

$moduleclass_sfx	= $params->get('moduleclass_sfx', '');
$ht = $params->get('header_tag');
$hc = $params->get('header_class');
$mship = $params->get('mship', 0);

$cntr = 0;
$total_amount = 0;
$openBal = Factory::getApplication()->getUserState('mod_gafinance.balance.open');
$closeBal = Factory::getApplication()->getUserState('mod_gafinance.balance.close');

?>

<div class="mod_gafinance" >
	<div id="trans<?php echo $module->id; ?>">
		<h2>Transactions for <?php echo $items[0]->accnt_name; ?> Account</h2>
	    <table class="table table-striped" id="transList">
			<tr><th width="13%">Date</th><th width="48%">Description</th><th>Type</th><th width="13%">Debit</th><th width="13%">Credit</th><th width="13%">Balance</th></tr>
			<?php foreach ($items as $i => $item) : ?>
				<?php 
                    if ($item->tran_type == 'E') {
                        $transType = Text::_('COM_GAFINANCE_EXPENSE');             
                    } elseif ($item->tran_type == 'I') { 
                        $transType = Text::_('COM_GAFINANCE_INCOME');
                    } elseif ($item->tran_type == 'D') {
                        $transType = Text::_('COM_GAFINANCE_JENTRY');
                    } else {
                        $transType = Text::_('COM_GAFINANCE_BALRESET');
                    }
                    $tran_date = !$item->tran_date || $item->tran_date == '0000-00-00' ? '-' : HTMLHelper::date($item->tran_date, Text::_('MOD_GAFINANCE_DISPLAY_DATETXT'));
                    
                    // change to name if membership
                    if ($item->cat_id == $mship) {
                        $tran_desc = $item->user_name;
                    } else {
                        $tran_desc = $item->tran_desc;
                    }

                ?>
                <tr class="row<?php echo $i % 2; ?>">
					<td class="transdate small"><?php echo $tran_date; ?></td>
					<td class="translist small"><?php echo $item->cat_name.' - '.$tran_desc.' - '.$item->tran_ref; ?></td>
					<td class="translist small"><?php echo $transType; ?></td>
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
