<?php

/**
 * @version    4.0.2
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

$data = $displayData['view'];
$item = $data->record;

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
				<a href="<?php echo Route::_('index.php?option=com_gaforsale&task=fsitem.marksold&id=' . $item->id, false, 2); ?>"
					class="btn btn-success" type="button" title="<?php echo Text::_('COM_GAFORSALE_FSITEM_SOLD'); ?>">
			   	    <i class="icon-thumbs-up"></i>
	 			</a> &nbsp;
				<a href="<?php echo Route::_('index.php?option=com_gaforsale&task=fsitemform.edit&id=' . $item->id, false, 2); ?>" 
					class="btn btn-secondary" type="button" title="<?php echo Text::_('COM_GAFORSALE_EDIT_ITEM'); ?>">
					<i class="icon-edit" ></i>
				</a>
                <?php endif; ?>
            </td>
        </tr>
