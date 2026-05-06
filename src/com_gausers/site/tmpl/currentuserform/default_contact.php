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
$localProfile = $data['view']->localProfile;
$canAdmin = $data['view']->canAdmin;
$ignorArray = $data['view']->ignorArray;
$ignorStdArray = $data['view']->ignorStdArray;

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
                        if ($localProfile == 'raf' ) {
                            $form->setFieldAttribute('region', 'type', 'text');
                            $form->setFieldAttribute('region', 'default', '');
                        }
                    ?>
	            <?php /* NOT an administrator or owner of record */ ?>
				<?php else : ?>
					<?php $form->setFieldAttribute('address1', 'type', 'hidden'); ?>
					<?php $form->setFieldAttribute('address2', 'type', 'hidden'); ?>
					<?php $form->setFieldAttribute('city', 'type', 'hidden'); ?>
					<?php $form->setFieldAttribute('region', 'type', 'hidden'); ?>
					<?php $form->setFieldAttribute('postal_code', 'type', 'hidden'); ?>
					<?php $form->setFieldAttribute('country', 'type', 'hidden'); ?>
					<?php $form->setFieldAttribute('postal_address1', 'type', 'hidden'); ?>
					<?php $form->setFieldAttribute('postal_address2', 'type', 'hidden'); ?>
					<?php $form->setFieldAttribute('postal_city', 'type', 'hidden'); ?>
					<?php $form->setFieldAttribute('postal_region', 'type', 'hidden'); ?>
					<?php $form->setFieldAttribute('postal_post_code', 'type', 'hidden'); ?>
					<?php $form->setFieldAttribute('postal_country', 'type', 'hidden'); ?>
			    <?php endif; ?>

				<?php
                    if (!in_array('address1',$ignorStdArray)) { echo $form->renderField('address1'); }
                    if (!in_array('address2',$ignorStdArray)) { echo $form->renderField('address2'); }
                    if (!in_array('city',$ignorStdArray)) { echo $form->renderField('city'); }
                    if (!in_array('region',$ignorStdArray)) { echo $form->renderField('region'); }
                    if (!in_array('postal_code',$ignorStdArray)) { echo $form->renderField('postal_code'); }
                    if (!in_array('country',$ignorStdArray)) { echo $form->renderField('country'); }
                    if (!in_array('use_post',$ignorArray)) { echo $form->renderField('use_post'); }
                    if (!in_array('postal_address1',$ignorArray)) { echo $form->renderField('postal_address1'); }
                    if (!in_array('postal_address2',$ignorArray)) { echo $form->renderField('postal_address2'); }
                    if (!in_array('postal_city',$ignorArray)) { echo $form->renderField('postal_city'); }
                    if (!in_array('postal_region',$ignorArray)) { echo $form->renderField('postal_region'); }
                    if (!in_array('postal_post_code',$ignorArray)) { echo $form->renderField('postal_post_code'); }
                    if (!in_array('postal_country',$ignorArray)) { echo $form->renderField('postal_country'); }
                ?>

