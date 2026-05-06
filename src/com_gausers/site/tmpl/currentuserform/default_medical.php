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
$canAdmin = $data['view']->canAdmin;
// this is the user id of member viewing the form
$user_id = $data['view']->user_id;
$canCustFld = $data['view']->canCustFld;

?>
            <?php /* test for administrator or owner of record for updating purposes */ ?>
			<?php if ($canAdmin || $item->id == $user_id) : ?>

    			<?php if ($canCustFld) : ?>
    				<?php echo $form->renderField('vaxed1'); ?>
    				<?php echo $form->renderField('vax_exempt1'); ?>
    				<?php echo $form->renderField('medcond1'); ?>
                    <?php if ($params->get('vax_field2', 0) && $params->get('mship_single', 0)) : ?>
    					<?php echo $form->renderField('vaxed2'); ?>
    					<?php echo $form->renderField('vax_exempt2'); ?>
    					<?php echo $form->renderField('medcond2'); ?>
    				<?php endif; ?>
    			<?php else : ?>
        			<?php echo Text::_('COM_GAUSERS_MEDICAL_ACCESS_RESTRICTED'); ?>
    				<?php
        				$form->setFieldAttribute('vaxed1', 'readonly', 'true');
        				$form->setFieldAttribute('vax_exempt1', 'readonly', 'true');
            			$form->setFieldAttribute('vaxed2', 'readonly', 'true');
        				$form->setFieldAttribute('vax_exempt2', 'readonly', 'true');
        			?>
                    <?php echo $form->renderField('vaxed1'); ?>
    				<?php echo $form->renderField('vax_exempt1'); ?>
    				<?php echo $form->renderField('medcond1'); ?>
                    <?php if ($params->get('vax_field2', 0) && $params->get('mship_single', 0)) : ?>
    					<?php echo $form->renderField('vaxed2'); ?>
    					<?php echo $form->renderField('vax_exempt2'); ?>
    					<?php echo $form->renderField('medcond2'); ?>
    				<?php endif; ?>
				<?php endif; ?>

            <?php /* NOT an administrator or owner of record */ ?>
			<?php else : ?>
				<input type="hidden" name="jform[vaxed1]" value="<?php echo $item->vaxed1; ?>" />
				<input type="hidden" name="jform[vax_exempt1]" value="<?php echo $item->vax_exempt1; ?>" />
				<input type="hidden" name="jform[medcond1]" value="<?php echo $item->medcond1; ?>" />
                <?php if ($params->get('vax_field2', 0) && $params->get('mship_single', 0)) : ?>
					<input type="hidden" name="jform[vaxed2]" value="<?php echo $item->vaxed2; ?>" />
					<input type="hidden" name="jform[vax_exempt2]" value="<?php echo $item->vax_exempt2; ?>" />
					<input type="hidden" name="jform[medcond2]" value="<?php echo $item->medcond2; ?>" />
				<?php endif; ?>
			<?php endif; ?>
