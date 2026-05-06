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

$fields = $form->getFieldset('gausers_anon');

?>
            <?php /* test for administrator or owner of record for updating purposes */ ?>
			<?php if ($canAdmin || $item->id == $user_id) : ?>
            <?php /* NOT an administrator or owner of record */ ?>
			<?php else : ?>
                <?php
                    foreach ($fields as $f) {
                        $form->setFieldAttribute($f->fieldname, 'type', 'hidden');
                    }
                ?>
			<?php endif; ?>
            <?php /* Render the form details as required */ ?>
            <?php
                foreach ($fields as $f) {
                    if (!in_array($f->fieldname,$ignorArray)) {echo $form->renderField($f->fieldname);}
                }
            ?>

