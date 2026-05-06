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
$ignorArray = $data['view']->ignorArray;
// this is the user id of member viewing the form
$user_id = $data['view']->user_id;

$incl_partner = $params->get( 'incl_partner' );
$trg_group = $params->get('trg_group',0);
$use_clubnumber = $params->get('use_clubnumber',0);
$fld_clubnumber = $params->get('fld_clubnumber',0);

?>
            <h4><?php echo Text::_('COM_GAUSERS_M_TRAINING_HEADER'); ?></h4>
			<?php /* test for administrator or owner of record for updating purposes */ ?>
			<?php if ($canAdmin || $item->id == $user_id) : ?>

				<?php
					if ($item->id == $user_id) {
						// need to set fields to readonly
						$form->setFieldAttribute('trg_prof', 'readonly', 'true');
						$form->setFieldAttribute('trg_profc', 'readonly', 'true');
						$form->setFieldAttribute('trg_lead', 'readonly', 'true');
						$form->setFieldAttribute('trg_leadc', 'readonly', 'true');
						$form->setFieldAttribute('trg_inst', 'readonly', 'true');
						$form->setFieldAttribute('trg_instc', 'readonly', 'true');
						$form->setFieldAttribute('trg_csaw', 'readonly', 'true');
						$form->setFieldAttribute('trg_csawc', 'readonly', 'true');
						$form->setFieldAttribute('trg_trck', 'readonly', 'true');
						$form->setFieldAttribute('trg_trckc', 'readonly', 'true');
						$form->setFieldAttribute('trg_faid', 'readonly', 'true');
						$form->setFieldAttribute('trg_faidc', 'readonly', 'true');
						$form->setFieldAttribute('trg_food', 'readonly', 'true');
						$form->setFieldAttribute('trg_foodc', 'readonly', 'true');
						$form->setFieldAttribute('cert4_dvct', 'readonly', 'true');
						$form->setFieldAttribute('cert4_dvdt', 'readonly', 'true');
						$form->setFieldAttribute('cert4_dvfile', 'readonly', 'true');
						$form->setFieldAttribute('cert4_csct', 'readonly', 'true');
						$form->setFieldAttribute('cert4_csdt', 'readonly', 'true');
						$form->setFieldAttribute('cert4_csfile', 'readonly', 'true');
					}
				?>
    			<?php if (!in_array('m_usi',$ignorArray)) {echo $form->renderField('m_usi');} ?>
    			<?php if (!in_array('trg_prof',$ignorArray)) {echo $form->renderField('trg_prof');} ?>
    			<?php if (!in_array('trg_profc',$ignorArray)) {echo $form->renderField('trg_profc');} ?>
    			<?php if (!in_array('trg_lead',$ignorArray)) {echo $form->renderField('trg_lead');} ?>
    			<?php if (!in_array('trg_leadc',$ignorArray)) {echo $form->renderField('trg_leadc');} ?>
    			<?php if (!in_array('trg_inst',$ignorArray)) {echo $form->renderField('trg_inst');} ?>
    			<?php if (!in_array('trg_instc',$ignorArray)) {echo $form->renderField('trg_instc');} ?>
    			<?php if (!in_array('trg_csaw',$ignorArray)) {echo $form->renderField('trg_csaw');} ?>
    			<?php if (!in_array('trg_csawc',$ignorArray)) {echo $form->renderField('trg_csawc');} ?>
    			<?php if (!in_array('trg_trck',$ignorArray)) {echo $form->renderField('trg_trck');} ?>
    			<?php if (!in_array('trg_trckc',$ignorArray)) {echo $form->renderField('trg_trckc');} ?>
    			<?php if (!in_array('trg_othr',$ignorArray)) {echo $form->renderField('trg_othr');} ?>
    			<?php if (!in_array('trg_faid',$ignorArray)) {echo $form->renderField('trg_faid');} ?>
    			<?php if (!in_array('trg_faidc',$ignorArray)) {echo $form->renderField('trg_faidc');} ?>
    			<?php if (!in_array('trg_food',$ignorArray)) {echo $form->renderField('trg_food');} ?>
    			<?php if (!in_array('trg_foodc',$ignorArray)) {echo $form->renderField('trg_foodc');} ?>
    			<?php if (!in_array('cert4_dvct',$ignorArray)) {echo $form->renderField('cert4_dvct');} ?>
    			<?php if (!in_array('cert4_dvdt',$ignorArray)) {echo $form->renderField('cert4_dvdt');} ?>
    			<?php if (!in_array('cert4_dvfile',$ignorArray)) {echo $form->renderField('cert4_dvfile');} ?>
    			<?php if (!in_array('cert4_csct',$ignorArray)) {echo $form->renderField('cert4_csct');} ?>
    			<?php if (!in_array('cert4_csdt',$ignorArray)) {echo $form->renderField('cert4_csdt');} ?>
    			<?php if (!in_array('cert4_csfile',$ignorArray)) {echo $form->renderField('cert4_csfile');} ?>

             <?php else : ?>
	             <h4><?php echo Text::_('COM_GAUSERS_NOTHING'); ?></h4>
             <?php endif; ?>
