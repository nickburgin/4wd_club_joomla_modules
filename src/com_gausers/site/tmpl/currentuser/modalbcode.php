<?php
/**
 * @version     6.0.0
 * @package     com_gausers
 * @copyright   Copyright (C) 2013. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Glenn Arkell <glenn@glennarkell.com.au> - http://www.glennarkell.com.au
 */
// no direct access
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\User\UserHelper;
use \GlennArkell\Component\Gausers\Administrator\Helper\GausersHelper;

// load any assets required
$wa = $this->document->getWebAssetManager()
    ->usePreset('com_gausers.gauserspreset');
GausersHelper::loadTmplStyleModal($wa);

//Load admin language file
$lang = Factory::getApplication()->getLanguage();
$lang->load('com_gausers', JPATH_ADMINISTRATOR);

$barcode = Factory::getApplication()->getUserState('com_gausers.barcode.data');
Factory::getApplication()->setUserState('com_gausers.barcode.data', null);

$item = Factory::getApplication()->getMenu()->getActive();
$member = GausersHelper::breakdownNamesFromUserID($this->item->id);

/*
echo '<pre>Test<br />';
print_r($this->item);
echo '</pre>';
print_r(Factory::getApplication()->getUserState('com_gausers.test.data'));
Factory::getApplication()->setUserState('com_gausers.test.data', null);
*/

?>
<style>
	.modal-body {
		max-height:450px;
	}
</style>

<h4 class="center"><img style="max-width:300px;" src="<?php echo $this->params->get('namebadge_img'); ?>" alt="" /></h3>
<h1 class="xlargefont center"><strong><?php echo $member->firstname; ?></strong></h3>
<h1 class="center"><?php echo $member->surname; ?></h1>
<p class="center"><img src="<?php echo $barcode; ?>" alt="Barcode for <?php echo $this->item->name; ?>" /></p>
<p>&nbsp;</p>
<p class="center"><a class="btn btn-warning" href="<?php echo $item->link; ?>" target="_parent">Close</a></p>


