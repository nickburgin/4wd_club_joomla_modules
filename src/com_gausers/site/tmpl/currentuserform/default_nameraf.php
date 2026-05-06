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
$ignorStdArray = $data['view']->ignorStdArray;
// this is the user id of member viewing the form
$user_id = $data['view']->user_id;

$defMship = $params->get( 'default_mship', 1);

$form->setFieldAttribute('mship_id', 'default', $defMship);
$form->setFieldAttribute('mship_id', 'type', 'hidden');
$form->setFieldAttribute('id', 'type', 'hidden');

if (!$canAdmin) {
    $form->setFieldAttribute('crew_connect', 'readonly', 'true');
    $form->setFieldAttribute('crew_relation', 'type', 'hidden');
}

?>
            <?php echo $form->renderField('id'); ?>
			<?php echo $form->renderField('mship_id'); ?>
            <?php echo $form->renderField('name'); ?>
            <?php echo $form->renderField('email'); ?>
            <?php echo $form->renderField('crew_connect'); ?>
			<?php echo $form->renderField('crew_relation'); ?>
			<?php echo $form->renderField('closest'); ?>
			<?php echo $form->renderField('upd_notif'); ?>


