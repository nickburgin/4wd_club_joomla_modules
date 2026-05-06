<?php

/**
 * @version    5.1.6
 * @package    Com_Gausers
 * @author     Glenn Arkell <glenn@glennarkell.com.au>
 * @copyright  2019 Glenn Arkell
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * Usage       This id used specifically for Four Wheel Drive Clubs using plugin "profileb4wdc"
 */

defined('JPATH_BASE') or die;

use \Joomla\CMS\Factory;
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\Router\Route;
use \Joomla\CMS\Session\Session;
use \Joomla\CMS\Access\Access;
use \Joomla\CMS\Plugin\PluginHelper;
use \Joomla\CMS\HTML\HTMLHelper;
use \GlennArkell\Component\Gausers\Administrator\Helper\GausersHelper;
use \GlennArkell\Component\Gausers\Administrator\Helper\GaauditHelper;
use \GlennArkell\Component\Gausers\Administrator\Helper\GainvoiceHelper;

//Load admin language file
$lang = Factory::getLanguage();
$lang->load('com_gausers', JPATH_ADMINISTRATOR);

$data = $displayData;
$itemlist = $data['view']->get('items');
$params = $data['view']->get('params');
$xtras = $data['view']->get('xtra');

$canAdmin = $data['view']->get('canAdmin');
$canMembers = $data['view']->get('canMembers');
$rptType = $data['view']->get('rpt_type');
$ignorArray = $data['view']->ignorArray;
$ignorStdArray = $data['view']->ignorStdArray;

// data['view'] is the user id of member viewing the form
$user = $data['view']->user;
$today = GausersHelper::getTodaysDate();

$localProfile = $params->get( 'profile_suffix', 'b4wdc' );
$incl_partner = $params->get( 'incl_partner', 0);
$incl_years = $params->get('incl_years',0);
$indiv_inv = $params->get('indiv_inv',0);
$mship_single  = $params->get( 'mship_single', 0);
$use_barcodes  = $params->get( 'use_barcodes', 0 );
$use_clubnumber = $params->get('use_clubnumber',0);
$fld_clubnumber = $params->get('fld_clubnumber',0);
$hide_vax  = $params->get( 'hide_vax', 1 );
$vax_field1  = $params->get( 'vax_field1', 0 );
$exempt_field1  = $params->get( 'exempt_field1', 0 );
$vax_field2  = $params->get( 'vax_field2', 0 );
$exempt_field2  = $params->get( 'exempt_field2', 0 );
$disp_address  = $params->get('disp_address', 0);
$suburb_only  = $params->get('suburb_only', 0);
$welcome_note = $params->get('welcome_note',0);
$default_mship = $params->get('default_mship',0);

if (!$disp_address || ($disp_address && $suburb_only)) {
    $addClass = 'gausermem';
} else {
    $addClass = 'gauseraddr';
}
?>
<table class="table table-striped" >
	<thead>
	<tr class="gausernone" >
        <th class="gauserhead">Name</th>
        <th class="gauserhead">Driver</th>
        <th class="gauserhead">Chainsaw</th>
        <th class="gauserhead">First Aid</th>
        <th class="gauserhead">WWC</th>
         <?php if ($canMembers) : ?>
			 <th class="gauserhead">Actions</th>
         <?php endif; ?>
    </tr>
	</thead>

	<tbody>
    <?php foreach ($itemlist as $item) : ?>

        <?php // set up all the reference and preference data
    		
            // set up the edit link of a member record
    		$editLink = GausersHelper::getHTTPQuery(null, 'task', 'currentuserform.edit', 'id', $item->id);
            $editURL = 'index.php?'.http_build_query($editLink, '', '&amp;');
    
    		// remove quotes from profile fields
            $item->partner = str_replace('"', '', $item->partner);
            $item->prof_cert = str_replace('"', '', $item->prof_cert);
            $item->prof_date = str_replace('"', '', $item->prof_date);
            $item->prof_certp = str_replace('"', '', $item->prof_certp);
            $item->prof_datep = str_replace('"', '', $item->prof_datep);
            $item->csaw_cert = str_replace('"', '', $item->csaw_cert);
            $item->csaw_date = str_replace('"', '', $item->csaw_date);
            $item->csaw_certp = str_replace('"', '', $item->csaw_certp);
            $item->csaw_datep = str_replace('"', '', $item->csaw_datep);
            $item->faid_cert = str_replace('"', '', $item->faid_cert);
            $item->faid_date = str_replace('"', '', $item->faid_date);
            $item->faid_certp = str_replace('"', '', $item->faid_certp);
            $item->faid_datep = str_replace('"', '', $item->faid_datep);
            $item->wreg_cert = str_replace('"', '', $item->wreg_cert);
            $item->wexp_date = str_replace('"', '', $item->wexp_date);
            $item->wreg_certp = str_replace('"', '', $item->wreg_certp);
            $item->wexp_datep = str_replace('"', '', $item->wexp_datep);
    
    		if (!$incl_partner) { $item->partner = null; }

        ?>

        <tr class="gausernone" >
            <td class="gausername">
                <a href="mailto:<?php echo $item->email; ?>" ><?php echo $item->name ; ?></a>
                <?php if (!empty($item->partner)) { echo '<br />'.$item->partner; } ?>
            </td>
            <td class="gausertrg">
                <span title="<?php echo $item->prof_cert; ?>">
                    <?php echo $pd = !empty($item->prof_date) ? HtmlHelper::date($item->prof_date, Text::_('COM_GAUSERS_ABRV_DISPLAY_DATE')) : ' '; ?>
                </span>
                <?php if (!empty($item->partner)) : ?>
                    <br />
                    <span title="<?php echo $item->prof_certp; ?>">
                    <?php echo $item->prof_datep; ?>
                        <?php echo $pdp = !empty($item->prof_datep) ? HtmlHelper::date($item->prof_datep, Text::_('COM_GAUSERS_ABRV_DISPLAY_DATE')) : ' '; ?>
                    </span>
                <?php endif; ?>
            </td>
            <td class="gausertrg">
                <span title="<?php echo $item->csaw_cert; ?>">
                    <?php echo $cd = !empty($item->csaw_date) ? HtmlHelper::date($item->csaw_date, Text::_('COM_GAUSERS_ABRV_DISPLAY_DATE')) : ' '; ?>
                </span>
                <?php if (!empty($item->partner)) : ?>
                    <br />
                    <span title="<?php echo $item->csaw_certp; ?>">
                    <?php echo $item->csaw_datep; ?>
                        <?php echo $cdp = !empty($item->csaw_datep) ? HtmlHelper::date($item->csaw_datep, Text::_('COM_GAUSERS_ABRV_DISPLAY_DATE')) : ' '; ?>
                    </span>
                <?php endif; ?>
            </td>
            <td class="gausertrg">
                <span title="<?php echo $item->faid_cert; ?>">
                    <?php echo $fd = !empty($item->faid_date) ? HtmlHelper::date($item->faid_date, Text::_('COM_GAUSERS_ABRV_DISPLAY_DATE')) : ' '; ?>
                </span>
                <?php if (!empty($item->partner)) : ?>
                    <br />
                    <span title="<?php echo $item->faid_certp; ?>">
                    <?php echo $item->faid_datep; ?>
                        <?php echo $fdp = !empty($item->faid_datep) ? HtmlHelper::date($item->faid_datep, Text::_('COM_GAUSERS_ABRV_DISPLAY_DATE')) : ' '; ?>
                    </span>
                <?php endif; ?>
            </td>
            <td class="gausertrg">
                <span title="<?php echo $item->wreg_cert; ?>">
                    <?php echo $wd = !empty($item->wexp_date) ? HtmlHelper::date($item->wexp_date, Text::_('COM_GAUSERS_ABRV_DISPLAY_DATE')) : ' '; ?>
                </span>
                <?php if (!empty($item->partner)) : ?>
                    <br />
                    <span title="<?php echo $item->wreg_certp; ?>">
                    <?php echo $item->wexp_datep; ?>
                        <?php echo $wdp = !empty($item->wexp_datep) ? HtmlHelper::date($item->wexp_datep, Text::_('COM_GAUSERS_ABRV_DISPLAY_DATE')) : ' '; ?>
                    </span>
                <?php endif; ?>
            </td>

            <?php if ($canMembers) : ?>
	            <td class="gausermem">
					<a href="<?php echo Route::_($editURL); ?>"
                        class="btn btn-secondary"><i class="fas fa-edit" title="<?php echo Text::_('COM_GAUSERS_EDIT_ITEM'); ?>"></i>
                    </a>
                </td>
             <?php endif; ?>
	    </tr>
	<?php endforeach; ?>
	</tbody>
</table>
