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
use Joomla\CMS\Form\FormHelper;
use \Joomla\CMS\User\UserHelper;

$data = $displayData;

// Load the form filters
$filters = $data['view']->filterForm->getGroup('filter');

/* get the setting that is relevant to filters */
$canMembers = $data['view']->get('canMembers');
$profGroup = $data['view']->get('profGroup');
$viewType = $data['view']->get('viewType');
$user = $data['view']->get('user');
$localProf = 'profile'.$data['view']->get('localProf');

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = Factory::getApplication()->getDocument()->getWebAssetManager();

// get profile if required
if ($viewType == 2) {
    $profile = UserHelper::getProfile($user->id);
}

/*
echo '<pre>Test<br />';
var_dump($filters['filter_pgroup']->default);
echo '</pre>';
*/
?>
<?php if ($filters) : ?>
    <?php foreach ($filters as $fieldName => $field) : ?>

        <?php if ($fieldName !== 'filter_search') : ?>

            <?php /* here is where you can selectively ignore a filter based on configuration settings */ ?>
            <?php if ($fieldName === 'filter_ugroup' && !$canMembers) { continue; } ?>
            <?php if ($fieldName === 'filter_pgroup' && $profGroup == '') { continue; } ?>
            <?php if ($fieldName === 'filter_state' && !$canMembers) { continue; } ?>

            <?php $dataShowOn = ''; ?>
            <?php if ($field->showon) : ?>
                <?php $wa->useScript('showon'); ?>
                <?php $dataShowOn = " data-showon='" . json_encode(FormHelper::parseShowOnConditions($field->showon, $field->formControl, $field->group)) . "'"; ?>
            <?php endif; ?>

            <?php if ($fieldName === 'filter_pgroup' && $viewType == 2) { $field->value = $profile->$localProf[$profGroup]; $field->readonly = true; } ?>

            <div class="js-stools-field-filter"<?php echo $dataShowOn; ?>>
                <span class="visually-hidden"><?php echo $field->label; ?></span>
                <?php echo $field->input; ?>
            </div>
        <?php endif; ?>
    <?php endforeach; ?>
<?php endif; ?>
