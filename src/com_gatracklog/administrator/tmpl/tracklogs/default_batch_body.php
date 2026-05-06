<?php

/**
 * @version     0.0.1
 * @package     pkg_mypackage
 * @subpackage  com_gatracklog
 * @copyright   Copyright (C) 2013. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Glenn Arkell <glenn@glennarkell.com.au> - http://www.glennarkell.com.au
 */

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;

$params = ComponentHelper::getParams('com_gatracklog');

$published = (int) $this->state->get('filter.published');

$user = $this->getCurrentUser();
?>

<div class="p-3">
    <div class="row">
        <?php if ($published >= 0) : ?>
            <div class="form-group col-md-6">
                <div class="controls">
                    <?php echo LayoutHelper::render('joomla.html.batch.item', ['extension' => 'com_gatracklog.tracklogs']); ?>
                </div>
            </div>
        <?php endif; ?>
        <div class="form-group col-md-6">
            <div class="controls">
                <?php echo LayoutHelper::render('joomla.html.batch.tag', []); ?>
            </div>
        </div>
        <div class="form-group col-md-6">
            <div class="controls">
                <?php echo LayoutHelper::render('joomla.html.batch.language', []); ?>
            </div>
        </div>
    </div>
</div>
<div class="btn-toolbar p-3">
    <joomla-toolbar-button task="tracklog.batch" class="ms-auto">
        <button type="button" class="btn btn-success"><?php echo Text::_('JGLOBAL_BATCH_PROCESS'); ?></button>
    </joomla-toolbar-button>
</div>
