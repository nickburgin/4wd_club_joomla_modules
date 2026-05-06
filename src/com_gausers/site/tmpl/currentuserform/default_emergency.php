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

//Load admin language file
$lang = Factory::getLanguage();
$lang->load('com_gausers', JPATH_ADMINISTRATOR);

$data = $displayData;
$item = $data['view']->get('item');
$params = $data['view']->get('params');
$form = $data['view']->get('form');
$ignorArray = $data['view']->ignorArray;
$ignorStdArray = $data['view']->ignorStdArray;
$useProfArray = $data['view']->useProfArray;
$localProfile = $data['view']->localProfile;
$canAdmin = $data['view']->canAdmin;
// this is the user id of member viewing the form
$user_id = $data['view']->user_id;

?>
	            <?php /* test for administrator or owner of record for updating purposes */ ?>
			    <?php if ($canAdmin || $item->id == $user_id) : ?>

					<?php echo $form->renderFieldset('gausers_emerg'); ?>

			    <?php endif; ?>
