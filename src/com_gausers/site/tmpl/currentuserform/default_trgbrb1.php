<?php
/**
 * @version     5.1.6
 * @package     com_gausers
 * @copyright   Copyright (C) 2012. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Created by Glenn Arkell - http://www.glennarkell.com
 */

defined('JPATH_BASE') or die;

use \Joomla\CMS\Factory;
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\Router\Route;
use \Joomla\CMS\Session\Session;
use \Joomla\CMS\Plugin\PluginHelper;
use \Joomla\CMS\HTML\HTMLHelper;

$data = $displayData;
$item = $data['view']->get('item');
$params = $data['view']->get('params');
$form = $data['view']->get('form');
$localProfile = $data['view']->localProfile;
$canAdmin = $data['view']->canAdmin;
$canTrg = $data['view']->canTrg;
$ignorArray = $data['view']->ignorArray;
// this is the user id of member viewing the form
$user_id = $data['view']->user_id;

$incl_partner = $params->get( 'incl_partner' );
$trg_group = $params->get('trg_group',0);
$use_clubnumber = $params->get('use_clubnumber',0);
$fld_clubnumber = $params->get('fld_clubnumber',0);

?>
<style>
    .form-control {
        width: 95% !important;
    }
</style>
			<?php /* test for administrator or owner of record for updating purposes */ ?>
			<?php if ($canAdmin || $item->id == $user_id) : ?>
				<?php
					if (!$canTrg && $item->id == $user_id) {
						// need to set fields to readonly
						$form->setFieldAttribute('trg_b2b', 'readonly', 'true');
						$form->setFieldAttribute('trg_b2bc_txt', 'readonly', 'true');
						$form->setFieldAttribute('trg_intro', 'readonly', 'true');
						$form->setFieldAttribute('trg_introc_txt', 'readonly', 'true');
						$form->setFieldAttribute('trg_bio', 'readonly', 'true');
						$form->setFieldAttribute('trg_bioc_txt', 'readonly', 'true');
						$form->setFieldAttribute('trg_faid', 'readonly', 'true');
						$form->setFieldAttribute('trg_faidc_txt', 'readonly', 'true');
						$form->setFieldAttribute('trg_food', 'readonly', 'true');
						$form->setFieldAttribute('trg_foodc_txt', 'readonly', 'true');

					}
				?>
    			<?php if (!in_array('m_usi',$ignorArray)) {echo $form->renderField('m_usi');} ?>
    			<?php if (!in_array('trg_b2b',$ignorArray)) {echo $form->renderField('trg_b2b');} ?>
    			<?php if (!in_array('trg_b2bc',$ignorArray)) {
                    if (isset($item->trg_b2bc_txt) && $item->trg_b2bc_txt) {
                        echo $form->renderField('trg_b2bc_txt');
                    } else {
                        echo $form->renderField('trg_b2bc');
                    }
                    }
                ?>
    			<?php if (!in_array('trg_intro',$ignorArray)) {echo $form->renderField('trg_intro');} ?>
    			<?php if (!in_array('trg_introc',$ignorArray)) {
                    if (isset($item->trg_introc_txt) && $item->trg_introc_txt) {
                        echo $form->renderField('trg_introc_txt');
                        echo '<a style="float:right;margin-top:-40px;" href="'.$item->trg_introc_txt.'" target="_blank"><i class="icon-info"></i></a>';
                    } else {
                        echo $form->renderField('trg_introc');
                    }
                    }
                ?>
    			<?php if (!in_array('trg_bio',$ignorArray)) {echo $form->renderField('trg_bio');} ?>
    			<?php if (!in_array('trg_bioc',$ignorArray)) {
                    if (isset($item->trg_bioc_txt) && $item->trg_bioc_txt) {
                        echo $form->renderField('trg_bioc_txt');
                    } else {
                        echo $form->renderField('trg_bioc');
                    }
                    }
                ?>
    			<?php if (!in_array('trg_othr',$ignorArray)) {echo $form->renderField('trg_othr');} ?>

    			<?php if (!in_array('trg_faid',$ignorArray)) {echo $form->renderField('trg_faid');} ?>
    			<?php if (!in_array('trg_faidc',$ignorArray)) {
                    if (isset($item->trg_faidc_txt) && $item->trg_faidc_txt) {
                        echo $form->renderField('trg_faidc_txt');
                    } else {
                        echo $form->renderField('trg_faidc');
                    }
                    }
                ?>
    			<?php if (!in_array('trg_food',$ignorArray)) {echo $form->renderField('trg_food');} ?>
    			<?php if (!in_array('trg_foodc',$ignorArray)) {
                    if (isset($item->trg_foodc_txt) && $item->trg_foodc_txt) {
                        echo $form->renderField('trg_foodc_txt');
                    } else {
                        echo $form->renderField('trg_foodc');
                    }
                    }
                ?>

             <?php else : ?>
	             <h4><?php echo Text::_('COM_GAUSERS_NOTHING'); ?></h4>
             <?php endif; ?>
