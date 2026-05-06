<?php
/**
 * @version     4.3.3
 * @package     com_gabroadcast
 * @copyright   Copyright (C) 2013. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Glenn Arkell <glenn@glennarkell.com.au> - http://www.glennarkell.com.au
 */

namespace GlennArkell\Component\Gabroadcast\Administrator\Field;

defined('JPATH_BASE') or die;

use \Joomla\CMS\Factory;
use \Joomla\CMS\Form\FormField;
use \Joomla\CMS\HTML\HTMLHelper;
use \Joomla\CMS\Form\Field\ListField;
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\Component\ComponentHelper;
use \Joomla\CMS\User\UserHelper;
use \GlennArkell\Component\Gabroadcast\Administrator\Helper\GabroadcastHelper;

class AttachlistField extends ListField
{
	/**
	 * The form field type.
	 *
	 * @var		string
	 * @since	1.6
	 */
	protected $type = 'Attachlist';

	/**
	 * Name of the layout being used to render the field
	 * @var    string
	 * @since  4.3.3
	 */
	protected $layout = 'joomla.form.field.list';

	/**
	 * Method to get the field input markup.
	 *
	 * @return	string	The field input markup.
	 * @since	1.6
	 */
	protected function getInput()
	{
        $params = ComponentHelper::getParams('com_gabroadcast');
        $attach_sort  = $params->get( 'attach_sort', 1 ); // default is Ascending
        $menu = Factory::getApplication()->getMenu()->getActive();

        // check if filter on user profile/custom field
        $menuParams  = $menu->getParams();
        $bcType  = $menuParams->get( 'broadcast_type', 0 );
        $pc_filter  = $menuParams->get( 'pc_filter', 0 );
        if ($pc_filter && $bcType > 1) {
            $attach = GabroadcastHelper::getBcastTypes($bcType);
            // get the user data
            $user = Factory::getApplication()->getIdentity();
            $profile = UserHelper::getProfile($user->id);
            // setup the profile field
            $profile_suffix = $params->get('profile_suffix','b4wdc');
            $locProf = 'profile'.$profile_suffix;
            $locGrps = $params->get('prof_field','locgrp');
            $locGrp = explode('.',$locGrps);
            if (isset($profile->$locProf[$locGrp[1]])) {
                $userArea = strtolower($profile->$locProf[$locGrp[1]] ?? '');
                // join up the directory name
                $attach->attach_dir = $attach->attach_dir.'/'.$userArea;
            }
        } else {
            $attach = GabroadcastHelper::getBcastTypes($bcType);
        }

		// Initialize variables.
		$html = array();

        //Factory::getApplication()->setUserState('com_gabroadcast.test.data', $attach);
        if (is_dir($attach->attach_dir)) {
			if ($attach_sort) {
				$files = scandir($attach->attach_dir);
			} else {
				$files = scandir($attach->attach_dir, 1);   // extra parameter sets descending
			}
	        $html[] = '<select id="jform_attach_file" class="" name="jform[attach_file]" aria-invalid="false">';
	        $html[] = '<option value="0"> - Select File - </option>';
	        if (count($files) >= 3) {
	            foreach ($files as $file) {
	                // cycle through listing to add them to the selection
	                if(!is_dir($attach->attach_dir.'/'.$file) && $file!='index.html')  {
	                    $html[] = '<option value="'.$file.'">'.$file.'</option>';
	                }
	            }
	        }
	        $html[] = '</select>';
		} else {
			$html[] = '<style type="text/css"> #jform_attach_file-lbl { display: none; } </style>';
		}	

		return implode($html);
	}
}
