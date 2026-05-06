<?php
/**
 * @version     6.0.0
 * @package     com_gausers
 * @copyright   Copyright (C) 2012. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Created by Glenn Arkell - http://www.glennarkell.com
 */

defined('JPATH_BASE') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\HTML\HTMLHelper;

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
			<?php /* test for administrator or owner of record for updating purposes */ ?>
			<?php if ($canAdmin || $item->id == $user_id) : ?>

				<?php
					if (!$canTrg && $item->id == $user_id) {
						// need to set fields to readonly
						$form->setFieldAttribute('trg_b2bp', 'readonly', 'true');
						$form->setFieldAttribute('trg_b2bpc_txt', 'readonly', 'true');
						$form->setFieldAttribute('trg_introp', 'readonly', 'true');
						$form->setFieldAttribute('trg_intropc_txt', 'readonly', 'true');
						$form->setFieldAttribute('trg_biop', 'readonly', 'true');
						$form->setFieldAttribute('trg_biopc_txt', 'readonly', 'true');
						$form->setFieldAttribute('trg_faidp', 'readonly', 'true');
						$form->setFieldAttribute('trg_faidpc_txt', 'readonly', 'true');
						$form->setFieldAttribute('trg_foodp', 'readonly', 'true');
						$form->setFieldAttribute('trg_foodpc_txt', 'readonly', 'true');
					}
				?>
    			<?php if (!in_array('p_usi',$ignorArray)) {echo $form->renderField('p_usi');} ?>
    			<?php if (!in_array('trg_b2b',$ignorArray)) {echo $form->renderField('trg_b2b');} ?>
    			<?php if (!in_array('trg_b2bc',$ignorArray)) {
                    if (isset($item->trg_b2bc_txt) && $item->trg_b2bc_txt) {
                        echo $form->renderField('trg_b2bc_txt');
                    } else {
                        echo $form->renderField('trg_b2bc');
                    }
                    }
                ?>
    			<?php if (!in_array('trg_introp',$ignorArray)) {echo $form->renderField('trg_introp');} ?>
    			<?php if (!in_array('trg_intropc',$ignorArray)) {
                    if (isset($item->trg_intropc_txt) && $item->trg_intropc_txt) {
                        echo $form->renderField('trg_intropc_txt');
                    } else {
                        echo $form->renderField('trg_intropc');
                    }
                    }
                ?>
    			<?php if (!in_array('trg_biop',$ignorArray)) {echo $form->renderField('trg_biop');} ?>
    			<?php if (!in_array('trg_biopc',$ignorArray)) {
                    if (isset($item->trg_biopc_txt) && $item->trg_biopc_txt) {
                        echo $form->renderField('trg_biopc_txt');
                    } else {
                        echo $form->renderField('trg_biopc');
                    }
                    }
                ?>
    			<?php if (!in_array('trg_othrp',$ignorArray)) {echo $form->renderField('trg_othrp');} ?>

    			<?php if (!in_array('trg_faidp',$ignorArray)) {echo $form->renderField('trg_faidp');} ?>
    			<?php if (!in_array('trg_faidpc',$ignorArray)) {
                    if (isset($item->trg_faidpc_txt) && $item->trg_faidpc_txt) {
                        echo $form->renderField('trg_faidpc_txt');
                    } else {
                        echo $form->renderField('trg_faidpc');
                    }
                    }
                ?>
    			<?php if (!in_array('trg_foodp',$ignorArray)) {echo $form->renderField('trg_foodp');} ?>
    			<?php if (!in_array('trg_foodpc',$ignorArray)) {
                    if (isset($item->trg_foodpc_txt) && $item->trg_foodpc_txt) {
                        echo $form->renderField('trg_foodpc_txt');
                    } else {
                        echo $form->renderField('trg_foodpc');
                    }
                    }
                ?>

             <?php else : ?>
	             <h4><?php echo Text::_('COM_GAUSERS_NOTHING'); ?></h4>
             <?php endif; ?>
