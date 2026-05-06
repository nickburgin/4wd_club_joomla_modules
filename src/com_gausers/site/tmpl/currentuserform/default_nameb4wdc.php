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
$ignorStdArray = $data['view']->ignorStdArray;
// this is the user id of member viewing the form
$user_id = $data['view']->user_id;

$defMship = $params->get( 'default_mship', 1);
$incl_partner = $params->get( 'incl_partner' );
$trg_group = $params->get('trg_group',0);
$use_clubnumber = $params->get('use_clubnumber',0);
$fld_clubnumber = $params->get('fld_clubnumber',0);

$form->setFieldAttribute('mship_id', 'default', $defMship);

/*
echo '<pre>Test<br />';
print_r($defMship);
echo '</pre>';
*/
?>
            <?php /* check using membership number or just the id reference */ ?>
			<?php if ($use_clubnumber && $fld_clubnumber != "") : ?>
	    		<?php $form->setFieldAttribute('id', 'type', 'hidden'); ?>
                <?php $form->setFieldAttribute('mship_no', 'readonly', 'true'); ?>
			<?php else : ?>
	    		<?php $form->setFieldAttribute('mship_no', 'type', 'hidden'); ?>
			<?php endif; ?>

            <?php /* test for administrator or owner of record for updating purposes */ ?>
			<?php if (!$canAdmin) : ?>
				<?php $form->setFieldAttribute('fwdvic_no', 'readonly', 'true'); ?>
                <?php $form->setFieldAttribute('mdod', 'type', 'hidden'); ?>
				<?php $form->setFieldAttribute('mship_id', 'readonly', 'true'); ?>
                <?php $form->setFieldAttribute('website', 'type', 'hidden'); ?>
			<?php endif; ?>

            <?php echo $form->renderField('id'); ?>
            <?php echo $form->renderField('mship_no'); ?>
			<?php echo $form->renderField('fwdvic_no'); ?>
			<?php echo $form->renderField('mship_id'); ?>

			<?php if (!in_array('mdod',$ignorArray)) {echo $form->renderField('mdod');}; ?>
            <?php if (!in_array('emailnews',$ignorArray)) {echo $form->renderField('emailnews');} ?>
            <?php if (!in_array('website',$ignorStdArray)) {echo $form->renderField('website');} ?>
            <?php if (!in_array('aboutme',$ignorStdArray)) {echo $form->renderField('aboutme');} ?>

