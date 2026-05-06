<?php
/**
 * @version     5.1.0
 * @package     com_gafinance
 * @copyright   Copyright (C) 2011. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Created by Glenn Arkell - http://www.glennarkell.com
 */

// no direct access
defined('_JEXEC') or die;

use \Joomla\CMS\Factory;
use \Joomla\CMS\Router\Route;
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\Date\Date;
use \Joomla\CMS\Uri\Uri;
use \Joomla\CMS\HTML\HTMLHelper;
use \GlennArkell\Component\Gafinance\Administrator\Helper\GafinanceHelper;

// load any assets required
$this->document->getWebAssetManager()
    ->usePreset('com_gafinance.gafinancepreset');

// get all the data ready for display
$app = Factory::getApplication();
$isModal = $app->input->get( 'print' ) == 1;
$dispHtml = '';
$rptData = $app->getUserState('com_gafinance.rptprint.data');
if (is_array($rptData)) {
	foreach ($rptData AS $rp) {
		$dispHtml .= $rp;
	}
} else {
	$dispHtml .= $rptData;
}
$menu = $app->getUserState('com_gafinance.menuitem.id');
// No set up the modal button from the display page or the print button from the modal
// 'print=1' will only be present in the url of the modal window, not in the presentation of the page
$baseURL = 'index.php?';
$prtTran = GafinanceHelper::getHTTPQuery(null, 'view', 'transactionform', 'layout', 'finreport');
$prtTran = GafinanceHelper::getHTTPQuery($prtTran, null, null, 'tmpl', 'component');
//$prtTran = GafinanceHelper::getHTTPQuery($prtTran, null, null, 'Itemid', $menu);
$prtTran = GafinanceHelper::getHTTPQuery($prtTran, null, null, 'print', 1);
$href1 = $baseURL.\http_build_query($prtTran, '', '&amp;');
$href2 = '"#" onclick="window.print(); return false;"';

$modalname = 'modal-print';
$html = '<a class="btn btn-info" href="#'.$modalname.'" data-bs-toggle="modal">';
$html .= '<i class="icon-plus"></i>'.Text::_('COM_GAFINANCE_PRINT').'</a>';
$modalParams = 	array( 'url'=>$href1, 'title'=>'Print', 'closeButton'=>true,
	'modalWidth'=>60, 'bodyHeight'=>45, 'backdrop'=>'static'
	);

$rptReq = GafinanceHelper::getHTTPQuery(null, 'view', 'transactionform', 'layout', 'rptreq');
$rptReq = GafinanceHelper::getHTTPQuery($rptReq, null, null, 'Itemid', $menu->id);
$rptURL = $baseURL.\http_build_query($rptReq, '', '&amp;');

/*
echo '<pre>Test<br />';
print_r($menu);
echo '</pre>';
*/
?>

<?php echo $dispHtml; ?>

<div style="clear:both;margin-top:20px;"></div>
<?php
    if ($isModal) {
        echo '<a class="btn btn-info" href='.$href2.' >'.Text::_('COM_GAFINANCE_PRINT').'</a>';
    } else {
        echo $html .= HTMLHelper::_('bootstrap.renderModal', $modalname, $modalParams);
        echo '<a class="btn btn-secondary" href='.$rptURL.' >'.Text::_('COM_GAFINANCE_REPORTS').'</a>';
    }

?>
<div style="clear:both;margin-bottom:20px;"></div>

