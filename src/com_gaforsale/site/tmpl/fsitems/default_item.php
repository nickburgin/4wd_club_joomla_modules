<?php

/**
 * @version    4.2.2
 * @package    Com_Gaforsale
 * @author     Glenn Arkell <glenn@glennarkell.com.au>
 * @copyright  2021 Glenn Arkell
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

use \Joomla\CMS\HTML\HTMLHelper;
use \Joomla\CMS\Factory;
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\Router\Route;
use \Joomla\CMS\Session\Session;
use \GlennArkell\Component\Gaforsale\Administrator\Helper\GaforsaleHelper;

$data = $displayData['view'];
$item = $data->record;
$dispAdmin = $data->dispAdmin;
$soldLink = GaforsaleHelper::getHTTPQuery(null, 'task', 'fsitem.marksold', 'id', $item->id);
$soldURL = 'index.php?'.http_build_query($soldLink, '', '&amp;');
$remindLink = GaforsaleHelper::getHTTPQuery(null, 'task', 'fsitem.sendReminder', 'id', $item->id);
$remindURL = 'index.php?'.http_build_query($remindLink, '', '&amp;');
$editLink = GaforsaleHelper::getHTTPQuery(null, 'task', 'fsitemform.edit', 'id', $item->id);
$editURL = 'index.php?'.http_build_query($editLink, '', '&amp;');

?>
		<tr><td class="saleborder" width="70%">
                <p class="salename"><?php echo $item->item_desc; ?></p>
                <p><?php echo $item->item_details; ?></p>
                <?php if (isset($item->item_image) && $item->item_image != ''): ?>
                <p class="center">
					<img src="<?php echo $item->item_image; ?>" alt="<?php echo $item->item_desc; ?>" title="<?php echo $item->item_desc; ?>" />
				</p>
                <?php endif; ?>
            </td>
            <td class="saleborder" width="30%">
                <p class="saleprice">
                    <?php echo "$".number_format($item->item_price,2); ?>
                    <?php if ($item->neg_ono) : ?>
                        <?php echo '  (ono)'; ?>
                    <?php endif; ?>
                </p>
                <p><?php echo "<br /><strong>Name: </strong>" . $item->seller_contact . "<br /><strong>Contact: </strong>" . $item->seller_phone; ?></p>
                <?php if ($data->show_offerdate) : ?>
                    <p class="salerow">Date item offered for sale:<br /><?php echo $item->offered_date; ?></p>
                <?php endif; ?>
                <?php if ($data->dispOwner || $data->dispAdmin) : ?>
                    <p>&nbsp;</p>
    				<a href="<?php echo Route::_($soldURL); ?>" class="btn btn-success" type="button" title="<?php echo Text::_('COM_GAFORSALE_FSITEM_SOLD'); ?>">
    			   	    <i class="icon-thumbs-up"></i>
    	 			</a> &nbsp;
    				<a href="<?php echo Route::_($editURL); ?>" class="btn btn-secondary" type="button" title="<?php echo Text::_('COM_GAFORSALE_EDIT_ITEM'); ?>">
    					<i class="icon-edit" ></i>
    				</a> &nbsp;
    				<?php if ($data->dispAdmin) : ?>
        				<a href="<?php echo Route::_($remindURL); ?>" class="btn btn-outline-info" type="button" title="<?php echo Text::_('GAREMINDER'); ?>">
        					<i class="icon-mail" ></i>
        				</a>
    				<?php endif; ?>
                <?php endif; ?>
            </td>
        </tr>
