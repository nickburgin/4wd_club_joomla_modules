<?php

/**
 * @package     pkg_gausers
 * @subpackage  com_gausers
 * @copyright   Copyright (C) 2013. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Glenn Arkell <glenn@glennarkell.com.au> - http://www.glennarkell.com.au
 */

defined('_JEXEC') or die;

use Joomla\CMS\Layout\LayoutHelper;

/** @var \GlennArkell\Component\Gausers\Administrator\View\Actions\HtmlView $this */

$displayData = [
    'textPrefix' => 'COM_GAUSERS',
    'formURL'    => 'index.php?option=com_gausers&view=actions',
    'helpURL'    => 'https://www.glennarkell.com.au/images/manuals/User_Membership_System.pdf',
    'icon'       => 'icon-copy action',
];

$user = $this->getCurrentUser();

if ($user->authorise('core.create', 'com_gausers') || count($user->getAuthorisedCategories('com_gausers', 'core.create')) > 0) {
    $displayData['createURL'] = 'index.php?option=com_gausers&task=action.add';
}

echo LayoutHelper::render('emptystate', $displayData);
