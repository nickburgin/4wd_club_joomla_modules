<?php
/*
# ------------------------------------------------------------------------
# @copyright   Copyright (C) 2014. All rights reserved.
# @license     GNU General Public License version 2 or later
# Author:      Glenn Arkell
# Websites:    https://www.glennarkell.com.au
# ------------------------------------------------------------------------
*/
// no direct access
defined('_JEXEC') or die( 'Restricted access' );

use Joomla\Filesystem\Path;
use Joomla\Filesystem\File;
use Joomla\Filesystem\Folder;

class modGlennsNewslettersHelper
{
	var $newsItems;

    /**
     * Retrieves the designated newsletter files to show
     *
     * @param array $params An object containing the module parameters
     * @access public
     */
    public static function getNewsletters( $params )
    {
        JHtml::_('stylesheet','mod_glennsnewsletters/default.css', false, true);
     	$default_fold = $params->get('default_fold', 'newsletters');
     	$sort_type = $params->get('sort_type', '0');
     	$asc_order = $params->get('default_order', '0');
     	$man_fold = $params->get('man_fold');
     	$default_file = $params->get('default_file', 'pdf');

		if (!empty($man_fold)) { $default_fold = $man_fold; }
        // get the files from the selected folder
        //$folder_exists = Folder::exists(JPATH_ROOT .'/images/'.$default_fold);  // failed in J4
        $folder_exists = file_exists(JPATH_ROOT .'/images/'.$default_fold);
        if ($folder_exists) {
            $foundfiles = Folder::files(JPATH_ROOT .'/images/'.$default_fold, '.'.$default_file);

            $arrobj = array();
            $array = array();
            $counter = 0;

            if ($sort_type) {
				foreach($foundfiles as $value) {
					$newsfile = 'images/'.$default_fold.'/'.$value;
					$newstitle = substr(str_replace('_',' ',$value),0,-4);
	                $array['newsfile'] = $newsfile;
	                $array['title'] = $newstitle;
	                $counter = filemtime($newsfile);
	                $object = new stdClass();
	                foreach($array as $key => $value) :
	                    $object->$key = $value;
	                endforeach;
	                $arrobj[$counter] = $object;
	   			}

	            if (!$asc_order) {
					$arrobj = array_reverse($arrobj);
				}
	   			$newsItems = $arrobj;
			} else {

	            if (!$asc_order) {
					$foundfiles = array_reverse($foundfiles);
				}
	            foreach($foundfiles as $value) {
	                $newsfile = 'images/'.$default_fold.'/'.$value;
	                $newstitle = substr(str_replace('_',' ',$value),0,-4);
	                $array['newsfile'] = $newsfile;
	                $array['title'] = $newstitle;
	                $object = new stdClass();
	                foreach($array as $key => $value) :
	                    $object->$key = $value;
	                endforeach;
	                $arrobj[$counter] = $object;
	                $counter++;
	            }
	            $newsItems = $arrobj;
            }
        }

    	return $newsItems;
    }
}
?>