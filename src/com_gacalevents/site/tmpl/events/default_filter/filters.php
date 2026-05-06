<?php

/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Form\FormHelper;

$data = $displayData;

// Load the form filters
$filters = $data['view']->filterForm->getGroup('filter');
$extrainfo = $data['view']->filterForm->getGroup('extrainfo');

// get the setting that is relevant to filters
//$canMembers = $data['view']->get('canMembers');

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = Factory::getApplication()->getDocument()->getWebAssetManager();

?>
<?php if ($filters) : ?>
    <?php foreach ($filters as $fieldName => $field) : ?>

        <?php if ($fieldName !== 'filter_search') : ?>
            <?php 
                // here is where you can filter out a filter based on configuration settings
                //if ($fieldName === 'filter_state' && !$canMembers) { continue; }
            ?>
            <?php $dataShowOn = ''; ?>
            <?php if ($field->showon) : ?>
                <?php $wa->useScript('showon'); ?>
                <?php $dataShowOn = " data-showon='" . json_encode(FormHelper::parseShowOnConditions($field->showon, $field->formControl, $field->group)) . "'"; ?>
            <?php endif; ?>
            <div class="js-stools-field-filter"<?php echo $dataShowOn; ?>>
                <span class="visually-hidden"><?php echo $field->label; ?></span>
                <?php echo $field->input; ?>
            </div>
        <?php endif; ?>
    <?php endforeach; ?>

    <?php if ($extrainfo) : ?>
        <?php foreach ($extrainfo as $infoName => $info) : ?>
        <div id="system-message-container" aria-live="polite">
            <joomla-alert type="danger" close-text="Close" dismiss="false" style="animation-name: joomla-alert-fade-in;" role="alert">
                <div class="alert-danger" style="font-size:0.3em important;">
                    <span class="danger"><?php echo Text::_('COM_GACALEVENTS_FILTER_DATEFR_DESC'); ?></span>
                </div>
            </joomla-alert>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
<?php endif; ?>

