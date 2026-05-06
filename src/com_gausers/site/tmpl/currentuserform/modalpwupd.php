<?php
/**
 * @version     5.1.6
 * @package     com_gausers
 * @copyright   Copyright (C) 2012. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Created by Glenn Arkell - http://www.glennarkell.com
 */

// no direct access
defined('_JEXEC') or die;

use \Joomla\CMS\Factory;
use \Joomla\CMS\Router\Route;
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\Uri\Uri;
use \Joomla\CMS\HTML\HTMLHelper;
use \Joomla\Session\SessionInterface;
use \Joomla\CMS\Application\SiteApplication;
use \GlennArkell\Component\Gausers\Administrator\Helper\GausersHelper;

//Load admin language file
$lang = Factory::getLanguage();
$lang->load('com_gausers', JPATH_ADMINISTRATOR);
$lang->load('com_users', JPATH_SITE);

$wa = $this->document->getWebAssetManager()
    ->usePreset('com_gausers.gauserspreset');
GausersHelper::loadTmplStyleModal($wa);

// add style to increase the size of the modal window to fit the calendar
?>
<div class="front-end-edit">
<form
	id="form-gauser" action="<?php echo Route::_('index.php?option=com_gausers&task=currentuserform.updpw&tmpl=component'); ?>"
	method="post" class="form-validate form-horizontal" enctype="multipart/form-data" target="_parent">

    <div class="row-fluid">
        <div class="span12 form-horizontal">
        
            <input type="hidden" name="jform[id]" value="<?php echo $this->item->id; ?>" />
            <input type="hidden" name="jform[user_id]" value="<?php echo $this->item->id; ?>" />

			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('password1'); ?></div>
				<div class="controls"><?php echo $this->form->getInput('password1'); ?></div>
			</div>
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('password2'); ?></div>
				<div class="controls"><?php echo $this->form->getInput('password2'); ?></div>
			</div>

        </div>
        <p class="center">
            <button type="submit" class="validate btn btn-primary"><?php echo Text::_('JSUBMIT'); ?></button>
        </p>
    </div>
    <div>
        <input type="hidden" name="task" value="currentuserform.updpw" />
        <?php echo HTMLHelper::_('form.token'); ?>
    </div>
</form>
</div>
