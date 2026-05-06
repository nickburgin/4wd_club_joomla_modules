<?php
/**
 * @version     5.1.6
 * @package     com_gausers
 * @copyright   Copyright (C) 2012. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Created by Glenn Arkell - http://www.glennarkell.com
 */

defined('JPATH_BASE') or die;

use \Joomla\CMS\Factory;
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\Router\Route;
use \Joomla\CMS\Session\Session;
use \Joomla\CMS\Plugin\PluginHelper;
use \Joomla\CMS\HTML\HTMLHelper;

$data = $displayData;
$item = $data['view']->get('item');
$params = $data['view']->get('params');
$form = $data['view']->get('form');
$localProfile = $data['view']->localProfile;
$canAdmin = $data['view']->canAdmin;
$ignorArray = $data['view']->ignorArray;
// this is the user id of member viewing the form
$user_id = $data['view']->user_id;

$incl_partner = $params->get( 'incl_partner' );
$trg_group = $params->get('trg_group',0);
$use_clubnumber = $params->get('use_clubnumber',0);
$fld_clubnumber = $params->get('fld_clubnumber',0);

?>
	            <h4><?php echo Text::_('COM_GAUSERS_VEHICLE_HEADER'); ?></h4>
				<?php /* test for administrator or owner of record for updating purposes */ ?>
			    <?php if ($canAdmin || $item->id == $user_id) : ?>

	    			<?php if (!in_array('vehicle_make',$ignorArray)) {echo $form->renderField('vehicle_make');} ?>
	    			<?php if (!in_array('vehicle_model',$ignorArray)) {echo $form->renderField('vehicle_model');} ?>
	    			<?php if (!in_array('vehicle_year',$ignorArray)) {echo $form->renderField('vehicle_year');} ?>
	    			<?php if (!in_array('vehicle_rego',$ignorArray)) {echo $form->renderField('vehicle_rego');} ?>
	    			<?php if (!in_array('vehicle_trans',$ignorArray)) {echo $form->renderField('vehicle_trans');} ?>
	    			<?php if (!in_array('vehicle_fuel',$ignorArray)) {echo $form->renderField('vehicle_fuel');} ?>
	    			<?php if (!in_array('vehicle_winch',$ignorArray)) {echo $form->renderField('vehicle_winch');} ?>
	    			<?php if (!in_array('alt_vehicle',$ignorArray)) {echo $form->renderField('alt_vehicle');} ?>
	    			<?php if (!in_array('alt_vehicle_rego',$ignorArray)) {echo $form->renderField('alt_vehicle_rego');} ?>

				    <h4><?php echo Text::_('COM_GAUSERS_COMMS_HEADER'); ?></h4>
	    			<?php if (!in_array('camp_caravan',$ignorArray)) {echo $form->renderField('camp_caravan');} ?>
	    			<?php if (!in_array('camp_other',$ignorArray)) {echo $form->renderField('camp_other');} ?>
	    			<?php if (!in_array('hf_net',$ignorArray)) {echo $form->renderField('hf_net');} ?>
	    			<?php if (!in_array('hf_callsign',$ignorArray)) {echo $form->renderField('hf_callsign');} ?>
	    			<?php if (!in_array('hf_selcall',$ignorArray)) {echo $form->renderField('hf_selcall');} ?>
	    			<?php if (!in_array('hf2_net',$ignorArray)) {echo $form->renderField('hf2_net');} ?>
	    			<?php if (!in_array('hf2_callsign',$ignorArray)) {echo $form->renderField('hf2_callsign');} ?>
	    			<?php if (!in_array('hf2_selcall',$ignorArray)) {echo $form->renderField('hf2_selcall');} ?>

	            <?php /* NOT an administrator or owner of record */ ?>
				<?php else : ?>

					<input type="hidden" name="jform[vehicle_make]" value="<?php echo $item->vehicle_make; ?>" />
					<input type="hidden" name="jform[vehicle_model]" value="<?php echo $item->vehicle_model; ?>" />
					<input type="hidden" name="jform[vehicle_year]" value="<?php echo $item->vehicle_year; ?>" />
					<input type="hidden" name="jform[vehicle_rego]" value="<?php echo $item->vehicle_rego; ?>" />
					<input type="hidden" name="jform[vehicle_trans]" value="<?php echo $item->vehicle_trans; ?>" />
					<input type="hidden" name="jform[vehicle_fuel]" value="<?php echo $item->vehicle_fuel; ?>" />
					<input type="hidden" name="jform[vehicle_winch]" value="<?php echo $item->vehicle_winch; ?>" />
					<input type="hidden" name="jform[alt_vehicle]" value="<?php echo $item->alt_vehicle; ?>" />
					<input type="hidden" name="jform[alt_vehicle_rego]" value="<?php echo $item->alt_vehicle_rego; ?>" />
					<input type="hidden" name="jform[camp_caravan]" value="<?php echo $item->camp_caravan; ?>" />
					<input type="hidden" name="jform[camp_other]" value="<?php echo $item->camp_other; ?>" />
					<input type="hidden" name="jform[hf_net]" value="<?php echo $item->hf_net; ?>" />
					<input type="hidden" name="jform[hf_callsign]" value="<?php echo $item->hf_callsign; ?>" />
					<input type="hidden" name="jform[hf_selcall]" value="<?php echo $item->hf_selcall; ?>" />
					<input type="hidden" name="jform[hf2_net]" value="<?php echo $item->hf2_net; ?>" />
					<input type="hidden" name="jform[hf2_callsign]" value="<?php echo $item->hf2_callsign; ?>" />
					<input type="hidden" name="jform[hf2_selcall]" value="<?php echo $item->hf2_selcall; ?>" />
			    <?php endif; ?>
