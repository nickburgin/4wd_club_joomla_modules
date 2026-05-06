<?php
/**
 * @version    6.0.0
 * @package    Com_Gausers
 * @author     Glenn Arkell <glenn@glennarkell.com.au>
 * @copyright  2021 Glenn Arkell
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace GlennArkell\Component\Gausers\Administrator\Helper;

// No direct access
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\User\UserHelper;

/**
 * Main helper.
 * @since  1.6
 */
class GamemberprofileHelper
{
	/**
	 * Get all the profile details
	 * @param   object  $user.
	 * @param   object  $params.
	 * @return  object	member object
	 */
    public static function getMemberProfile($user, $params)
	{
        $profsuf  = $params->get( 'profile_suffix', 'b4wdc' );
        $profsuf  = 'profile'.$profsuf;
        $exEmailPref = $params->get('exclude_email_pref','noemail');

		$profile = UserHelper::getProfile($user->id);

        $user->address1 = isset($profile->profile['address1']) ? str_replace('"', '', $profile->profile['address1']) : '';
        $user->address2 = isset($profile->profile['address2']) ? str_replace('"', '', $profile->profile['address2']) : '';
        $user->suburb = isset($profile->profile['city']) ? str_replace('"', '', $profile->profile['city']) : '';
        $user->region = isset($profile->profile['region']) ? str_replace('"', '', $profile->profile['region']) : '';
        $user->postcode = isset($profile->profile['postal_code']) ? str_replace('"', '', $profile->profile['postal_code']) : '';
        $user->phone = isset($profile->profile['phone']) ? str_replace('"', '', $profile->profile['phone']) : '';
        $user->phone = ($user->phone == '0') ? '' : $user->phone;

        $user->partner = isset($profile->$profsuf['partner']) ? $profile->$profsuf['partner'] : '';
        $user->altphone = isset($profile->$profsuf['altphone']) ? $profile->$profsuf['altphone'] : '';
        $user->altphone = ($user->altphone == '0') ? '' : $user->altphone;

        $user->use_post = isset($profile->$profsuf['use_post']) ? $profile->$profsuf['use_post'] : 0;
        $user->postal_address1 = isset($profile->$profsuf['postal_address1']) ? $profile->$profsuf['postal_address1'] : '';
        $user->postal_suburb = isset($profile->$profsuf['postal_city']) ? $profile->$profsuf['postal_city'] : '';
        $user->postal_region = isset($profile->$profsuf['postal_region']) ? $profile->$profsuf['postal_region'] : '';
        $user->postal_post_code = isset($profile->$profsuf['postal_post_code']) ? $profile->$profsuf['postal_post_code'] : '';

        $user->address = $user->use_post ? $user->postal_address1 : $user->address1.' '.$user->address2;
        $user->suburb = $user->use_post ? $user->postal_suburb : $user->suburb;
        $user->region = $user->use_post ? $user->postal_region : $user->region;
        $user->pcode = $user->use_post ? $user->postal_post_code : $user->postcode;

        $user->wwk_reg = isset($profile->$profsuf['wwk_reg']) ? $profile->$profsuf['wwk_reg'] : '';
        $user->wwk_exp = isset($profile->$profsuf['wwk_exp']) ? $profile->$profsuf['wwk_exp'] : '';

        if ($profsuf == 'b4wdc') {
            $user->snd_phone = isset($profile->$profsuf['2nd_phone']) ? $profile->$profsuf['2nd_phone'] : '';
            $user->snd_phone = ($user->snd_phone == '0') ? '' : $user->snd_phone;
            $user->sat_phone = isset($profile->$profsuf['hf2_selcall']) ? $profile->$profsuf['hf2_selcall'] : '';
            $user->sat_phone = ($user->sat_phone == '0') ? '' : $user->sat_phone;
            $user->camp_caravan = isset($profile->$profsuf['camp_caravan']) ? $profile->$profsuf['camp_caravan'] : '';
            $user->camp_caravan = ($user->camp_caravan == '0') ? '' : $user->camp_caravan;
            $user->camp_other = isset($profile->$profsuf['camp_other']) ? $profile->$profsuf['camp_other'] : '';
            $user->camp_other = ($user->camp_other == '0') ? '' : $user->camp_other;
            $user->vehicle_make = isset($profile->$profsuf['vehicle_make']) ? $profile->$profsuf['vehicle_make'] : '';
            $user->vehicle_make = ($user->vehicle_make == '0') ? '' : $user->vehicle_make;
            $user->vehicle_model = isset($profile->$profsuf['vehicle_model']) ? $profile->$profsuf['vehicle_model'] : '';
            $user->vehicle_model = ($user->vehicle_model == '0') ? '' : $user->vehicle_model;
            $user->vehicle_rego = isset($profile->$profsuf['vehicle_rego']) ? $profile->$profsuf['vehicle_rego'] : '';
            $user->vehicle_rego = ($user->vehicle_rego == '0') ? '' : $user->vehicle_rego;
            $user->vehicle_fuel = isset($profile->$profsuf['vehicle_fuel']) ? $profile->$profsuf['vehicle_fuel'] : '';
            $user->vehicle_fuel = ($user->vehicle_fuel == '0') ? '' : $user->vehicle_fuel;
        }

        if ($profsuf == 'ifmr') {
            $user->mobile = isset($profile->$profsuf['mobile']) && $profile->$profsuf['mobile'] != '0' ? $profile->$profsuf['mobile'] : '';
            $user->workphone = isset($profile->$profsuf['workphone']) && $profile->$profsuf['workphone'] != '0' ? $profile->$profsuf['workphone'] : '';
            $user->district = isset($profile->$profsuf['district']) && $profile->$profsuf['district'] != '0' ? $profile->$profsuf['district'] : '';
            $user->rotaryclub = isset($profile->$profsuf['rotaryclub']) && $profile->$profsuf['rotaryclub'] != '0' ? $profile->$profsuf['rotaryclub'] : '';
            $user->bike = isset($profile->$profsuf['bike']) && $profile->$profsuf['bike'] != '0' ? $profile->$profsuf['bike'] : '';
            $user->rego_by = isset($profile->$profsuf['rego_by']) && $profile->$profsuf['rego_by'] != '0' ? $profile->$profsuf['rego_by'] : '';
        }

        if ($profsuf == 'brb') {
            $user->trg_b2b = isset($profile->$profsuf['trg_b2b']) && $profile->$profsuf['trg_b2b'] != '0' ? $profile->$profsuf['trg_b2b'] : '';
            $user->trg_b2bp = isset($profile->$profsuf['trg_b2bp']) && $profile->$profsuf['trg_b2bp'] != '0' ? $profile->$profsuf['trg_b2bp'] : '';
            $user->trg_b2bc_txt = isset($profile->$profsuf['trg_b2bc_txt']) && $profile->$profsuf['trg_b2bc_txt'] != '0' ? $profile->$profsuf['trg_b2bc_txt'] : '';
            $user->trg_b2bpc_txt = isset($profile->$profsuf['trg_b2bpc_txt']) && $profile->$profsuf['trg_b2bpc_txt'] != '0' ? $profile->$profsuf['trg_b2bpc_txt'] : '';
            $user->trg_intro = isset($profile->$profsuf['trg_intro']) && $profile->$profsuf['trg_intro'] != '0' ? $profile->$profsuf['trg_intro'] : '';
            $user->trg_introp = isset($profile->$profsuf['trg_introp']) && $profile->$profsuf['trg_introp'] != '0' ? $profile->$profsuf['trg_introp'] : '';
            $user->trg_introc_txt = isset($profile->$profsuf['trg_introc_txt']) && $profile->$profsuf['trg_introc_txt'] != '0' ? $profile->$profsuf['trg_introc_txt'] : '';
            $user->trg_intropc_txt = isset($profile->$profsuf['trg_intropc_txt']) && $profile->$profsuf['trg_intropc_txt'] != '0' ? $profile->$profsuf['trg_intropc_txt'] : '';
            $user->trg_bio = isset($profile->$profsuf['trg_bio']) && $profile->$profsuf['trg_bio'] != '0' ? $profile->$profsuf['trg_bio'] : '';
            $user->trg_biop = isset($profile->$profsuf['trg_biop']) && $profile->$profsuf['trg_biop'] != '0' ? $profile->$profsuf['trg_biop'] : '';
            $user->trg_bioc_txt = isset($profile->$profsuf['trg_bioc_txt']) && $profile->$profsuf['trg_bioc_txt'] != '0' ? $profile->$profsuf['trg_bioc_txt'] : '';
            $user->trg_biopc_txt = isset($profile->$profsuf['trg_biopc_txt']) && $profile->$profsuf['trg_biopc_txt'] != '0' ? $profile->$profsuf['trg_biopc_txt'] : '';
            $user->locgrp = isset($profile->$profsuf['locgrp']) && $profile->$profsuf['locgrp'] != '0' ? $profile->$profsuf['locgrp'] : '';
            $user->regono = isset($profile->$profsuf['regono']) && $profile->$profsuf['regono'] != '0' ? $profile->$profsuf['regono'] : '';
        }

        if ($profsuf == 'docs') {
            $user->emerg_name = isset($profile->$profsuf['emerg_name']) && $profile->$profsuf['emerg_name'] != '0' ? $profile->$profsuf['emerg_name'] : '';
            $user->emerg_name = isset($profile->$profsuf['emerg_relationship']) && $profile->$profsuf['emerg_relationship'] != '0' ? $profile->$profsuf['emerg_relationship'] : '';
            $user->emerg_name = isset($profile->$profsuf['emerg_phone']) && $profile->$profsuf['emerg_phone'] != '0' ? $profile->$profsuf['emerg_phone'] : '';
            $user->emerg_name = isset($profile->$profsuf['emerg_email']) && $profile->$profsuf['emerg_email'] != '0' ? $profile->$profsuf['emerg_email'] : '';
        }

        if ($profsuf == 'becs') {
            $user->anonymous = isset($profile->$profsuf['anonymous']) ? $profile->$profsuf['anonymous'] : '';
            $user->anonaddress = isset($profile->$profsuf['anonaddress']) ? $profile->$profsuf['anonaddress'] : '';
            $user->anonpartner = isset($profile->$profsuf['anonpartner']) ? $profile->$profsuf['anonpartner'] : '';
            $user->anonemail = isset($profile->$profsuf['anonemail']) ? $profile->$profsuf['anonemail'] : '';
            $user->anonphone = isset($profile->$profsuf['anonphone']) ? $profile->$profsuf['anonphone'] : '';
        }

        $user->email = substr($user->email,0,strlen($exEmailPref)) == $exEmailPref ? 'No Email' : $user->email;

        $user->region = ($user->region == 'OTHER') ? '' : $user->region;

        return $user;
	}

}

