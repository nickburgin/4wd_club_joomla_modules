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
// this is the user id of member viewing the form
$user_id = $data['view']->user_id;

$incl_partner = $params->get( 'incl_partner' );
$trg_group = $params->get('trg_group',0);
$use_clubnumber = $params->get('use_clubnumber',0);
$fld_clubnumber = $params->get('fld_clubnumber',0);

?>
			<h4><?php echo Text::_('COM_GAUSERS_P_TRAINING_HEADER'); ?></h4>
            <?php /* test for administrator or owner of record for updating purposes */ ?>
			<?php if ($canAdmin || $item->id == $user_id) : ?>
				<?php
					if ($item->id == $user_id) {
						// need to set fields to readonly
						$form->setFieldAttribute('trg_profp', 'readonly', 'true');
						$form->setFieldAttribute('trg_profpc', 'readonly', 'true');
						$form->setFieldAttribute('trg_leadp', 'readonly', 'true');
						$form->setFieldAttribute('trg_leadpc', 'readonly', 'true');
						$form->setFieldAttribute('trg_instp', 'readonly', 'true');
						$form->setFieldAttribute('trg_instpc', 'readonly', 'true');
						$form->setFieldAttribute('trg_csawp', 'readonly', 'true');
						$form->setFieldAttribute('trg_csawpc', 'readonly', 'true');
						$form->setFieldAttribute('trg_trckp', 'readonly', 'true');
						$form->setFieldAttribute('trg_trckpc', 'readonly', 'true');
						$form->setFieldAttribute('trg_faidp', 'readonly', 'true');
						$form->setFieldAttribute('trg_faidpc', 'readonly', 'true');
						$form->setFieldAttribute('trg_foodp', 'readonly', 'true');
						$form->setFieldAttribute('trg_foodpc', 'readonly', 'true');
						$form->setFieldAttribute('cert4_dvctp', 'readonly', 'true');
						$form->setFieldAttribute('cert4_dvdtp', 'readonly', 'true');
						$form->setFieldAttribute('cert4_dvfilep', 'readonly', 'true');
						$form->setFieldAttribute('cert4_csctp', 'readonly', 'true');
						$form->setFieldAttribute('cert4_csdtp', 'readonly', 'true');
						$form->setFieldAttribute('cert4_csfilep', 'readonly', 'true');
					}
				?>

    			<?php if (!in_array('p_usi',$ignorArray)) {echo $form->renderField('p_usi');} ?>
    			<?php if (!in_array('trg_profp',$ignorArray)) {echo $form->renderField('trg_profp');} ?>
    			<?php if (!in_array('trg_profpc',$ignorArray)) {echo $form->renderField('trg_profpc');} ?>
    			<?php if (!in_array('trg_leadp',$ignorArray)) {echo $form->renderField('trg_leadp');} ?>
    			<?php if (!in_array('trg_leadpc',$ignorArray)) {echo $form->renderField('trg_leadpc');} ?>
    			<?php if (!in_array('trg_instp',$ignorArray)) {echo $form->renderField('trg_instp');} ?>
    			<?php if (!in_array('trg_instpc',$ignorArray)) {echo $form->renderField('trg_instpc');} ?>
    			<?php if (!in_array('trg_csawp',$ignorArray)) {echo $form->renderField('trg_csawp');} ?>
    			<?php if (!in_array('trg_csawpc',$ignorArray)) {echo $form->renderField('trg_csawpc');} ?>
    			<?php if (!in_array('trg_trckp',$ignorArray)) {echo $form->renderField('trg_trckp');} ?>
    			<?php if (!in_array('trg_trckpc',$ignorArray)) {echo $form->renderField('trg_trckpc');} ?>
    			<?php if (!in_array('trg_othrp',$ignorArray)) {echo $form->renderField('trg_othrp');} ?>
    			<?php if (!in_array('trg_faidp',$ignorArray)) {echo $form->renderField('trg_faidp');} ?>
    			<?php if (!in_array('trg_faidpc',$ignorArray)) {echo $form->renderField('trg_faidpc');} ?>
    			<?php if (!in_array('trg_foodp',$ignorArray)) {echo $form->renderField('trg_foodp');} ?>
    			<?php if (!in_array('trg_foodpc',$ignorArray)) {echo $form->renderField('trg_foodpc');} ?>
    			<?php if (!in_array('cert4_dvctp',$ignorArray)) {echo $form->renderField('cert4_dvctp');} ?>
    			<?php if (!in_array('cert4_dvdtp',$ignorArray)) {echo $form->renderField('cert4_dvdtp');} ?>
    			<?php if (!in_array('cert4_dvfilep',$ignorArray)) {echo $form->renderField('cert4_dvfilep');} ?>
    			<?php if (!in_array('cert4_csctp',$ignorArray)) {echo $form->renderField('cert4_csctp');} ?>
    			<?php if (!in_array('cert4_csdtp',$ignorArray)) {echo $form->renderField('cert4_csdtp');} ?>
    			<?php if (!in_array('cert4_csfilep',$ignorArray)) {echo $form->renderField('cert4_csfilep');} ?>

             <?php else : ?>
	             <h4><?php echo Text::_('COM_GAUSERS_NOTHING'); ?></h4>
             <?php endif; ?>
