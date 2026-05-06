<?php
/**
 * @version     6.0.0
 * @package     com_gausers
 * @copyright   Copyright (C) 2012. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Created by Glenn Arkell - http://www.glennarkell.com
 */

// no direct access
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\Session\SessionInterface;
use Joomla\CMS\Application\SiteApplication;
use \GlennArkell\Component\Gausers\Administrator\Helper\GausersHelper;

//Load admin language file
$lang = Factory::getApplication()->getLanguage();
$lang->load('com_gausers', JPATH_ADMINISTRATOR);

$wa = $this->document->getWebAssetManager()
    ->usePreset('com_gausers.gauserspreset');
GausersHelper::loadTmplStyleModal($wa);

// if (isset($this->item->action_record)) {
//     $this->item->act_name = $this->item->action_record->act_name;
//     $this->item->comment = $this->item->action_record->comment;
// }
//$user = GausersHelper::getSpecificUser($id);

// get the current date-time based on timezone
// $date = Factory::getDate();
// $today = date_format($date,'Y-m-d H:i:s');
// $this->form->setFieldAttribute('user_id', 'default', $id);
// $this->form->setFieldAttribute('user_id', 'type', 'hidden');

?>

<div class="front-end-edit">
<form
	id="form-gauser" action="<?php echo Route::_('index.php?option=com_gausers&task=currentuserform.updAction&tmpl=component'); ?>"
	method="post" class="form-validate form-horizontal" enctype="multipart/form-data" target="_parent">

    <div class="row-fluid">
        <div class="span12 form-horizontal">
        
            <?php echo $this->form->renderFieldset('mbractions'); ?>
            <?php echo $this->form->renderField('comment'); ?>

        </div>
        <p class="center">
            <button type="submit" class="validate btn btn-primary"><?php echo Text::_('JSUBMIT'); ?></button>
        </p>
    </div>
    <div>
        <input type="hidden" name="task" value="currentuserform.updAction" />
        <?php echo HTMLHelper::_('form.token'); ?>
    </div>
</form>
</div>
