<?php
/**
 * @version     5.1.0
 * @package     com_gatripsys
 * @copyright   Copyright (C) 2014. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Glenn Arkell <glenn@glennarkell.com.au> - http://www.glennarkell.com.au
 */
// no direct access
defined('_JEXEC') or die;

use \Joomla\CMS\HTML\HTMLHelper;
use \Joomla\CMS\Factory;
use \Joomla\CMS\Uri\Uri;
use \Joomla\CMS\Router\Route;
use \Joomla\CMS\Language\Text;

$wa = $this->document->getWebAssetManager();
$wa->getRegistry()->addExtensionRegistryFile('com_gatripsys');
$wa->useScript('keepalive')
	->useScript('form.validate');

// Load admin language file
$lang = Factory::getApplication()->getLanguage();
$lang->load('com_gatripsys', JPATH_ADMINISTRATOR);

?>

<form action="<?php echo Route::_('index.php?option=com_gatripsys&layout=edit&id=' . (int) $this->item->id); ?>" 
	method="post" enctype="multipart/form-data" name="adminForm" id="incident-form" class="form-validate">

    <div class="form-horizontal">
        <?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', array('active' => 'general')); ?>

            <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'general', Text::_('COM_GATRIPSYS_TITLE_INCIDENT', true)); ?>
            <div class="row-fluid">
                <div class="span10 form-horizontal">

					<?php echo $this->form->renderField('trip_id'); ?>
					<?php echo $this->form->renderField('user_id'); ?>
					<?php echo $this->form->renderField('incid_date'); ?>
					<?php echo $this->form->renderField('map_ref'); ?>
					<?php echo $this->form->renderField('gps_ref'); ?>
					<?php echo $this->form->renderField('location'); ?>
	            </div>
            </div>
            <?php echo HTMLHelper::_('uitab.endTab'); ?>

            <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'details', Text::_('COM_GATRIPSYS_TITLE_DETAILS', true)); ?>
            <div class="row-fluid">
                <div class="span10 form-horizontal">
					<?php echo $this->form->renderField('pers_involved'); ?>
					<?php echo $this->form->renderField('witnesses'); ?>
					<?php echo $this->form->renderField('comment'); ?>

                </div>

            </div>
            <?php echo HTMLHelper::_('uitab.endTab'); ?>

            <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'xtra', Text::_('COM_GATRIPSYS_TITLE_SYSINFO', true)); ?>
            <div class="row-fluid">
                <div class="span10 form-horizontal">
					<?php echo $this->form->renderField('id'); ?>
					<?php echo $this->form->renderField('state'); ?>
					<?php echo $this->form->renderField('created_by'); ?>
					<?php echo $this->form->renderField('created_date'); ?>
					<?php echo $this->form->renderField('modified_by'); ?>
					<?php echo $this->form->renderField('modified_date'); ?>

                </div>
            </div>
            <?php echo HTMLHelper::_('uitab.endTab'); ?>

        <?php echo HTMLHelper::_('uitab.endTabSet'); ?>

        <input type="hidden" name="task" value="" />
        <?php echo HTMLHelper::_('form.token'); ?>

    </div>
</form>
