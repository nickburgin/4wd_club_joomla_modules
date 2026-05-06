<?php

/**
 * @version    0.0.1
 * @package    pkg_mypackage
 * @subpackage com_gatracklog
 * @author     Glenn Arkell <glenn@glennarkell.com.au>
 * @copyright  2024 Glenn Arkell
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Layout\LayoutHelper;

/** @var \Joomla\Component\Contact\Administrator\View\Contacts\HtmlView $this */

$displayData = [
    'textPrefix' => 'COM_GATRACKLOG',
    'formURL'    => 'index.php?option=com_gatracklog',
    'helpURL'    => 'https://www.glennarkell.com.au/index.php/gatracklog-mgmt-system',
    'icon'       => 'fa fa-settings tracklog',
];

$user = $this->getCurrentUser();

if ($user->authorise('core.create', 'com_gatracklog') || count($user->getAuthorisedCategories('com_gatracklog', 'core.create')) > 0) {
    $displayData['createURL'] = 'index.php?option=com_gatracklog&task=tracklog.add';
}

echo LayoutHelper::render('joomla.content.emptystate', $displayData);
