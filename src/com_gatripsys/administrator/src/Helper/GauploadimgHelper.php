<?php
/**
 * @version    5.2.2
 * @package    com_gatripsys
 * @author     Glenn Arkell <glenn@glennarkell.com.au>
 * @copyright  2021 Glenn Arkell
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace GlennArkell\Component\Gatripsys\Administrator\Helper;

// No direct access
defined('_JEXEC') or die;

use \Joomla\CMS\Factory;
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\Component\ComponentHelper;
use \Joomla\Filesystem\Path;
use \Joomla\Filesystem\File;
use \Joomla\Filesystem\Folder;
use \Joomla\CMS\Router\Route;
use \Joomla\CMS\Application\SiteApplication;
use \GlennArkell\Component\Gatripsys\Administrator\Helper\GatripsysHelper;

/**
 * Gatripsys helper.
 * @since  1.6
 */
class GauploadimgHelper
{

    /**
     * Method to upload an attachment
     */
    public static function uploadAttachment($dir, $tran_file)
    {
        $app = Factory::getApplication();
        $lang = Factory::getLanguage();
        $lang->load('com_gatripsys', JPATH_ADMINISTRATOR);
        if (file_exists('file://'.$tran_file['tmp_name'])) {
			$fileName = File::makeSafe($tran_file['name']);
			$fileName = str_replace(' ', '_', $fileName);
			$src = $tran_file['tmp_name'];
			$fileName = $dir.'/'.$fileName;

			$path = Path::clean( JPATH_SITE . '/' );
			$destfile = $path.'/'.$fileName;

			if ( File::upload($src, $destfile, false, false, $safeFileOptions) ) {
				$app->enqueueMessage(Text::sprintf('COM_GATRIPSYS_FILE_UPLOADED_SUCCESS', $fileName), 'success');
				return $fileName;
			} else {
				$app->enqueueMessage(Text::sprintf('COM_GATRIPSYS_FILE_UPLOADED_FAIL', $fileName), 'danger');
				return null;
			}
  		} else {
			$app->enqueueMessage(Text::sprintf('COM_GATRIPSYS_FILE_UPLOADED_NOFILE', $fileName), 'warning');
			return null;
		}
    }

}

