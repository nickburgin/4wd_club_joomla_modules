<?php
/**
 * @version     2.0.00
 * @package     com_gabroadcast
 * @copyright   Copyright (C) 2013. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Glenn Arkell <glenn@glennarkell.com.au> - http://www.glennarkell.com.au
 */
// no direct access
defined('_JEXEC') or die;

use \Joomla\CMS\Factory;
use \Joomla\CMS\Router\Route;
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\HTML\HTMLHelper;
use \GlennArkell\Component\Gabroadcast\Administrator\Helper\GabroadcastHelper;

// load any assets required
$this->document->getWebAssetManager()
    ->usePreset('com_gabroadcast.gabroadcastpreset');

//Load admin language file
$lang = Factory::getLanguage();
$lang->load('com_gabroadcast', JPATH_ADMINISTRATOR);

$user = GabroadcastHelper::getSpecificUser();
$canManage = $user->authorise('core.attupload', 'com_gabroadcast');

?>

<div class="usernew-edit front-end-edit">
    <h2>Upload Attachments</h2>
    <form id="form-usernew" action="<?php echo Route::_('index.php?option=com_gabroadcast&task=usernewform.uplattachfile'); ?>" 
		method="post" class="form-validate" enctype="multipart/form-data">
        <div class="span10 form-horizontal">
			<fieldset name="general" class="adminform">

	            <?php if ($canManage) : ?>
					<?php echo $this->form->renderField('bcasttype'); ?>
	            <?php else : ?>
		            <input type="hidden" name="jform[bcasttype]" value="<?php echo $this->params->get('forsale'); ?>" />
	            <?php endif; ?>
				<?php echo $this->form->renderField('bcfile_name'); ?>
				<input type="hidden" name="jform[news_subject]" value="Upload" />

			</fieldset>

        </div>
        
        <div class="clearfix"> </div>

        <div>
            <button type="submit" class="validate btn btn-primary"><span><?php echo Text::_('JSUBMIT'); ?></span></button>
            <?php echo Text::_('or'); ?>
            <a href="<?php echo Route::_('index.php?option=com_gabroadcast&task=usernewform.cancel'); ?>" 
				class="btn btn-secondary" title="<?php echo Text::_('JCANCEL'); ?>"> <?php echo Text::_('JCANCEL'); ?>
			</a>

            <input type="hidden" name="option" value="com_gabroadcast" />
            <input type="hidden" name="task" value="usernewform.uplattachfile" />
            <?php echo HTMLHelper::_('form.token'); ?>
        </div>
    </form>
</div>
