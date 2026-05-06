<?php
/**
 * @version     4.5.5
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
//$localProfile = $data['view']->localProfile;
$canAdmin = $data['view']->canAdmin;
// this is the user id of member viewing the form
$user_id = $data['view']->user_id;

?>
	            <?php /* test for administrator or owner of record for updating purposes */ ?>
			    <?php if ($canAdmin || $item->id == $user_id) : ?>

					<?php
                        if (empty($ignorArray)) {
                            echo $form->renderField('emailnews');
                            echo $form->renderField('privacy');
                        } else {
                            if (!in_array('emailnews',$ignorArray)) { echo $form->renderField('emailnews'); }
                            if (!in_array('privacy',$ignorArray)) { echo $form->renderField('privacy'); }
                        }
                        if (empty($ignorStdArray)) {
                            echo $form->renderField('aboutme');
                        } else {
                            if (!in_array('aboutme',$ignorStdArray)) { echo $form->renderField('aboutme'); }
                        }
                    ?>
	            <?php /* NOT an administrator or owner of record */ ?>
				<?php else : ?>

					<input type="hidden" name="jform[emailnews]" value="<?php echo $item->emailnews; ?>" />
					<input type="hidden" name="jform[privacy]" value="<?php echo $item->privacy; ?>" />
                    <input type="hidden" name="jform[aboutme]" value="<?php echo $item->aboutme; ?>" />

			    <?php endif; ?>
