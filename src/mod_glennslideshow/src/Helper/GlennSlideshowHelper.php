<?php
/*
 * ------------------------------------------------------------------------
 * @version     4.6
 * @copyright   Copyright (C) 2021. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * Author:      Glenn Arkell
 * Website:    http://www.glennarkell.com.au
 * ------------------------------------------------------------------------
*/

namespace GlennArkell\Module\GlennSlideshow\Site\Helper;

// no direct access
defined('_JEXEC') or die( 'Restricted access' );

use \Joomla\CMS\Factory;
use \Joomla\Filesystem\Path;
use \Joomla\Filesystem\File;
use \Joomla\Filesystem\Folder;
use \Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use \Joomla\CMS\Uri\Uri;
use \Joomla\CMS\Table\Table;

class GlennSlideshowHelper
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
        $wa = Factory::getApplication()->getDocument()->getWebAssetManager();
        $wa->registerAndUseStyle('glennslideshowCore', 'mod_glennslideshow/css/default.css');

		$articleItems = '';
     	$default_cat = $params->get('default_cat',0);
      	$use_category_folder = $params->get('use_category_folder',0); // 0 = category, 1 = folder
     	$default_fold = $params->get('default_fold', 'images');
     	$default_file = $params->get('default_file', '.jpg');

     	if ($use_category_folder == 0) {
            $category = Table::getInstance('Category');
            $category->load($default_cat);
    		// Query the articles table to get the articles in the selected category
    		$db = Factory::getContainer()->get('DatabaseDriver');
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
            if (empty($articleItems)) {
                Factory::getApplication()->enqueueMessage(Text::sprintf('MOD_GLENNSLIDESHOW_CATEGORY_EMPTY', $category->title), 'warning');
            }
        } else {
			$chosen_fold = $default_fold;
			$path = Path::clean('images/'.$chosen_fold);

            // get the images from the selected folder
            $folder_exists = file_exists($path);

            if ($folder_exists) {
				$images = Folder::files($path, $default_file);
				
				if (!empty($images)) {
                    Factory::getApplication()->setUserState('mod_glennslideshow.data.test',$images);
    				$arrobj = array();
                    $array = array();
                    $counter = 0;
                    foreach($images as $value) {
                        $imagefile = 'images/'.$chosen_fold.'/'.$value;
                        $imagetitle = substr(str_replace('_',' ',$value ?? ''),0,-4);
    
                        $array['images'] = $imagefile;
                        $array['title'] = $imagetitle;
                        $array['size'] = \getimagesize($imagefile);
                        $object = new \stdClass;
                        foreach($array as $key => $value) :
                           $object->$key = $value;
                        endforeach;
                        $arrobj[$counter] = $object;
                        $counter++;
                    }
                    $articleItems = $arrobj;
                } else {
                    Factory::getApplication()->enqueueMessage(Text::sprintf('MOD_GLENNSLIDESHOW_FOLDER_EMPTY', $path), 'warning');
                }
            }
        }

    	return $articleItems;
    }
}
?>