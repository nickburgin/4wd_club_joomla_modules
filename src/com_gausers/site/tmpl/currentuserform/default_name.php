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

//Load admin language file
$lang = Factory::getApplication()->getLanguage();
$lang->load('com_gausers', JPATH_ADMINISTRATOR);

$data = $displayData;
$item = $data['view']->get('item');
$params = $data['view']->get('params');
$form = $data['view']->get('form');
$ignorArray = $data['view']->ignorArray;
$ignorStdArray = $data['view']->ignorStdArray;
$useProfArray = $data['view']->useProfArray;
$localProfile = $data['view']->localProfile;
$incl_partner = $data['view']->incl_partner;
$canAdmin = $data['view']->canAdmin;
// this is the user id of member viewing the form
$user_id = $data['view']->user_id;

if ($localProfile == 'bdgs') {
    $form->setFieldAttribute('mdod', 'type', 'hidden');
    $form->setFieldAttribute('m_img', 'type', 'hidden');
    $form->setFieldAttribute('p_img', 'type', 'hidden');
    if ($incl_partner) {
        $form->setFieldAttribute('partner', 'type', 'hidden');
    }
    $form->setFieldAttribute('altemail', 'type', 'hidden');
    $form->setFieldAttribute('altphone', 'type', 'hidden');
    $form->setFieldAttribute('inc_altemail', 'type', 'hidden');
    $form->setFieldAttribute('emailnews', 'type', 'hidden');
    //$form->setFieldAttribute('phone', 'type', 'hidden');
}


?>
				<input type="hidden" name="jform[id]" value="<?php echo $item->id; ?>" />
                <?php if (!$canAdmin) : ?>
					<?php $form->setFieldAttribute('mship_no', 'readonly', 'true'); ?>
					<?php $form->setFieldAttribute('prime_mbr', 'readonly', 'true'); ?>
					<?php $form->setFieldAttribute('mship_id', 'readonly', 'true'); ?>
					<?php $form->setFieldAttribute('memtype', 'readonly', 'true'); ?>
					<?php $form->setFieldAttribute('mdod', 'readonly', 'true'); ?>
					<?php $form->setFieldAttribute('mdod', 'type', 'hidden'); ?>
				<?php endif ; ?>

                <?php echo $form->renderField('mship_id'); ?>

                <?php if (!$incl_partner) : ?>
					<?php $form->setFieldAttribute('partner', 'type', 'hidden'); ?>
					<?php $form->setFieldAttribute('p_img', 'type', 'hidden'); ?>
					<?php $form->setFieldAttribute('altemail', 'type', 'hidden'); ?>
					<?php $form->setFieldAttribute('altphone', 'type', 'hidden'); ?>
					<?php $form->setFieldAttribute('inc_altemail', 'type', 'hidden'); ?>
				<?php endif; ?>

	            <?php /* test for administrator or owner of record for updating purposes */ ?>
			    <?php if ($canAdmin || $item->id == $user_id) : ?>

					<?php if (!in_array('mdod',$ignorArray)) {echo $form->renderField('mdod');}; ?>
					<?php echo $form->renderField('memtype'); ?>
                    <?php echo $form->renderField('name'); ?>
					<?php if ($localProfile == 'docs') : ?>
                        <?php if (!in_array('mship_no',$ignorArray)) { echo $form->renderField('mship_no'); } ?>
                        <?php if (!in_array('prime_mbr',$ignorArray)) { echo $form->renderField('prime_mbr'); } ?>
                        <?php if (!in_array('cert_name',$ignorArray)) { echo $form->renderField('cert_name'); } ?>
                        <?php if (!in_array('yearofbirth',$ignorArray)) { echo $form->renderField('yearofbirth'); } ?>
					<?php endif; ?>
                    <?php echo $form->renderField('email'); ?>
					<?php 
                        if (empty($ignorArray)) {
                            echo $form->renderField('phone');
                            echo $form->renderField('m_img');
                            echo $form->renderField('emailnews');
                            echo $form->renderField('partner');
                            echo $form->renderField('p_img');
                            echo $form->renderField('altemail');
                            echo $form->renderField('altphone');
                            echo $form->renderField('inc_altemail');
                        } else {
                            if (!in_array('phone',$ignorArray)) { echo $form->renderField('phone'); }
                            if (!in_array('m_img',$ignorArray)) { echo $form->renderField('m_img'); }
                            if (!in_array('emailnews',$ignorArray)) { echo $form->renderField('emailnews'); }
                            if (!in_array('partner',$ignorArray)) { echo $form->renderField('partner'); }
                            if (!in_array('p_img',$ignorArray)) { echo $form->renderField('p_img'); }
                            if (!in_array('altemail',$ignorArray)) { echo $form->renderField('altemail'); }
                            if (!in_array('altphone',$ignorArray)) { echo $form->renderField('altphone'); }
                            if (!in_array('inc_altemail',$ignorArray)) { echo $form->renderField('inc_altemail'); }
                        }
                    ?>
					<?php if ($localProfile == 'docs') : ?>
                        <?php if (!in_array('email_contact',$ignorArray)) { echo $form->renderField('email_contact'); } ?>
                        <?php if (!in_array('allow_website',$ignorArray)) { echo $form->renderField('allow_website'); } ?>
                        <?php if (!in_array('website',$ignorArray)) { echo $form->renderField('website'); } ?>
					<?php endif; ?>
					<?php if ($localProfile == 'bdgs') : ?>
                        <?php if (!in_array('mship_no',$ignorArray)) { echo $form->renderField('mship_no'); } ?>
					<?php endif; ?>

	            <?php /* NOT an administrator or owner of record */ ?>
				<?php else : ?>

					<input type="text" name="jform[name]" value="<?php echo $item->name; ?>" readonly="true" class="readonly" />
					<input type="text" name="jform[email]" value="<?php echo $item->email; ?>" readonly="true" class="readonly" />
					<input type="hidden" name="jform[phone]" value="<?php echo $item->phone; ?>" />
					<input type="hidden" name="jform[emailnews]" value="<?php echo $item->emailnews; ?>" />
					<input type="hidden" name="jform[m_img]" value="<?php echo $item->m_img; ?>" />
					<input type="hidden" name="jform[partner]" value="<?php echo $item->partner; ?>" />
					<input type="hidden" name="jform[p_img]" value="<?php echo $item->p_img; ?>" />
					<input type="hidden" name="jform[altemail]" value="<?php echo $item->altemail; ?>" />
					<input type="hidden" name="jform[altphone]" value="<?php echo $item->altphone; ?>" />
					<input type="hidden" name="jform[inc_altemail]" value="<?php echo $item->inc_altemail; ?>" />
					<input type="hidden" name="jform[email_contact]" value="<?php echo $item->email_contact; ?>" />
					<input type="hidden" name="jform[allow_website]" value="<?php echo $item->allow_website; ?>" />
					<input type="hidden" name="jform[website]" value="<?php echo $item->website; ?>" />

			    <?php endif; ?>
