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

use Joomla\CMS\Factory;
use Joomla\Filesystem\Path;
use Joomla\Filesystem\File;
use Joomla\Filesystem\Folder;

class modGlennSlideshowHelper
{
	var $categoryAlias;

    /**
     * Retrieves the designated slides to show
     *
     * @param array $params An object containing the module parameters
     * @access public
     */
    public static function getSlideshow( $params )
    {
        $articleItems = '';
		JHtml::_('stylesheet','mod_glennslideshow/default.css', false, true);
     	$default_cat = $params->get('default_cat','uncategorised');
      	$use_category_folder = $params->get('use_category_folder',0); // 0 = category, 1 = folder
     	$default_fold = $params->get('default_fold', 'images');
     	$default_file = $params->get('default_file', 'jpg');
     	$manual_fold = $params->get('manual_fold', 'images');

     	if ($use_category_folder == 0) {
    		// Query the articles table to get the articles in the selected category
    		$db = Factory::getDbo();
    		$query = $db->getQuery(true);
    		$query->select(' * ');
    		$query->from($db->quoteName('#__content'));
    		$query->where($db->quoteName('catid') . ' = '. (int) $default_cat);
    		$query->where($db->quoteName('state') . ' = 1 ');
    		$db->setQuery((string)$query);
			try {
				$articleItems = $db->loadObjectList();
			} catch (RuntimeException $e) {
				Factory::getApplication()->enqueueMessage($e->getMessage());
				$articleItems = false;
			}
        } else {
			if ($use_category_folder == 1) {
				$chosen_fold = $default_fold;
			} else {
				$chosen_fold = $manual_fold;
			}
            // get the images from the selected folder
            $folder_exists = file_exists(JPATH_ROOT .'/images/'.$chosen_fold);
            if ($folder_exists) {
                $images = Folder::files(JPATH_ROOT .'/images/'.$chosen_fold, '.'.$default_file);
                $arrobj = array();
                $array = array();
                $counter = 0;
                foreach($images as $value) {
                    $imagefile = 'images/'.$chosen_fold.'/'.$value;
                    //$imagefile = $default_fold.'/'.$value;
                    $imagetitle = substr(str_replace('_',' ',$value),0,-4);
                    $array['images'] = $imagefile;
                    $array['title'] = $imagetitle;
                    $object = new stdClass();
                    foreach($array as $key => $value) :
                       $object->$key = $value;
                    endforeach;
                    $arrobj[$counter] = $object;
                    $counter++;
                }
                $articleItems = $arrobj;
            }
        }
        
    	return $articleItems;
    }
}
?>