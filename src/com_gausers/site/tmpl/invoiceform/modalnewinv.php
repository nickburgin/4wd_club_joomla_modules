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

// load any assets required
$wa = $this->document->getWebAssetManager()
    ->usePreset('com_gausers.gauserspreset');
GausersHelper::loadTmplStyleModal($wa);

//Load admin language file
$lang = Factory::getLanguage();
$lang->load('com_gausers', JPATH_ADMINISTRATOR);

$new_users = $this->params->get('new_users', 0);
$auto_users = $this->params->get('auto_users',0);

$wa = $this->document->getWebAssetManager();
GausersHelper::loadTmplStyleModal($wa);

?>

<div class="usernew-edit front-end-edit">
    <?php if (!empty($this->item->id)): ?>
        <h2>Edit <?php echo $this->item->id; ?></h2>
    <?php else: ?>
        <h2>Create Membership Invoice</h2>
    <?php endif; ?>

    <form id="form-invoice" action="<?php echo Route::_('index.php'); ?>" method="post" class="form-validate" enctype="multipart/form-data" target="_parent">
        <div class="span12 form-horizontal">
            <?php if ($auto_users): ?>
				<input type="hidden" name="jform[invoice_amt]" value="0.00" />
				<input type="hidden" name="jform[user_id]" value="0" />
				<?php if ($profile_suffix == 'raf') : ?>
					<?php $this->form->removeField('region'); ?>
	            <?php else: ?>
					<?php $this->form->removeField('plain_region'); ?>
				<?php endif; ?>

				<?php echo $this->form->renderField('mship_id'); ?>
				<?php echo $this->form->renderFieldset('newmember'); ?>

            <?php else: ?>
				<input type="hidden" name="jform[invoice_amt]" value="0.00" />
				<?php echo $this->form->renderField('mship_id'); ?>
				<?php echo $this->form->renderField('user_id'); ?>

			<?php endif; ?>

	        <div class="clearfix"> </div>
	        <div>
	            <button type="submit" class="validate btn btn-primary"><span><?php echo Text::_('JSUBMIT'); ?></span></button>
	        </div>
            <input type="hidden" name="option" value="com_gausers" />
            <input type="hidden" name="task" value="invoice.newinv" />
            <?php echo HTMLHelper::_('form.token'); ?>

        </div>

    </form>
</div>
