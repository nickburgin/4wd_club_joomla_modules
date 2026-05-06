<?php
/**
 * @version    5.3.0
 * @package    Com_Gatripsys
 * @author     Glenn Arkell <glenn@glennarkell.com.au>
 * @copyright  2016 Glenn Arkell
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormHelper;

$data = $displayData;

// Load the form filters
$filters = $data['view']->filterForm->getGroup('filter');

/* get the setting that is relevant to filters */
/* $canMembers = $data['view']->get('canMembers'); */

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = Factory::getApplication()->getDocument()->getWebAssetManager();

?>
<?php if ($filters) : ?>
    <?php foreach ($filters as $fieldName => $field) : ?>

        <?php if ($fieldName !== 'filter_search') : ?>

            <?php /* here is where you can filter out a filter based on configuration settings */ ?>

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
<?php endif; ?>
