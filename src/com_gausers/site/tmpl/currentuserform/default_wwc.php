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
$ignorArray = $data['view']->ignorArray;
$wwchildren = $data['view']->wwchildren;
// this is the user id of member viewing the form
$user_id = $data['view']->user_id;

$incl_partner = $params->get('incl_partner',0);

?>
	            <h4><?php echo Text::_('COM_GAUSERS_M_WWC_HEADER'); ?></h4>
				<?php /* test for administrator or allowed to edit */ ?>
			    <?php if ($canAdmin || $wwchildren) : ?>

	    			<?php if (!in_array('wwk_reg',$ignorArray)) {echo $form->renderField('wwk_reg');} ?>
	    			<?php if (!in_array('wwk_exp',$ignorArray)) {echo $form->renderField('wwk_exp');} ?>

		            <?php if ($incl_partner) : ?>
			            <h4><?php echo Text::_('COM_GAUSERS_P_WWC_HEADER'); ?></h4>
		    			<?php if (!in_array('wwk_regp',$ignorArray)) {echo $form->renderField('wwk_regp');} ?>
		    			<?php if (!in_array('wwk_expp',$ignorArray)) {echo $form->renderField('wwk_expp');} ?>
				    <?php endif; ?>

				<?php /* NOT an administrator or allowed to edit */ ?>
				<?php else : ?>
					<?php
						if ($item->id == $user_id) {
							// need to set fields to readonly
							$form->setFieldAttribute('wwk_reg', 'readonly', 'true');
							$form->setFieldAttribute('wwk_exp', 'readonly', 'true');
							$form->setFieldAttribute('wwk_regp', 'readonly', 'true');
							$form->setFieldAttribute('wwk_expp', 'readonly', 'true');

			    			if (!in_array('wwk_reg',$ignorArray)) {echo $form->renderField('wwk_reg');}
			    			if (!in_array('wwk_exp',$ignorArray)) {echo $form->renderField('wwk_exp');}
			    			if ($incl_partner) {
								echo '<h4>'.Text::_('COM_GAUSERS_P_WWC_HEADER').'</h4>';
								if (!in_array('wwk_regp',$ignorArray)) {echo $form->renderField('wwk_regp');}
				    			if (!in_array('wwk_expp',$ignorArray)) {echo $form->renderField('wwk_expp');}
			    			}
						} else {
							echo '<input type="hidden" name="jform[wwk_reg]" value="'.$item->wwk_reg.'" />';
							echo '<input type="hidden" name="jform[wwk_exp]" value="'.$item->wwk_exp.'" />';
							echo '<input type="hidden" name="jform[wwk_regp]" value="'.$item->wwk_regp.'" />';
							echo '<input type="hidden" name="jform[wwk_expp]" value="'.$item->wwk_expp.'" />';
						}
					?>

			    <?php endif; ?>
