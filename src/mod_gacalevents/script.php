<?php
/*
 * ------------------------------------------------------------------------
 * @version     5.3
 * @package     pkg_gacalevents
 * @subpackage  mod_gacalevents
 * @copyright   Copyright (C) 2021. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * Author:      Glenn Arkell
 * Website:     https://www.glennarkell.com.au
 * ------------------------------------------------------------------------
*/

\defined('_JEXEC') or die();

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\Filesystem\File;
use Joomla\Filesystem\Folder;
use Joomla\Filesystem\Path;
use Joomla\CMS\Installer\Installer;
use Joomla\CMS\Installer\InstallerScript;
use Joomla\CMS\Filter\OutputFilter;

/**
 * Updates the structure of the component
 */
class mod_gacaleventsInstallerScript extends InstallerScript
{
	/**
	 * Method called before install/update the component. Note: This method won't be called during uninstall process.
	 * @param   string $type   Type of process [install | update]
	 * @param   mixed  $parent Object who called this method
	 * @return boolean True if the process should continue, false otherwise
     * @throws Exception
	 */
	public function preflight($type, $parent)
	{
		// $parent is the class calling this method
		echo '<p>' . Text::_('MOD_GACALEVENTS_PREFLIGHT_TEXT') . '</p>';

        $path = Path::clean( JPATH_SITE . '/modules/mod_gacalevents/' );
        $this->deleteFiles($path, 'helper', '.php');
        $this->deleteFiles($path, 'mod_gacalevents', '.php');
        $this->deleteFiles($path, 'mod_gacalevents', '.php');
        $this->deleteFiles($path, 'tmpl/blank', '.php');
        $this->deleteFiles($path, 'tmpl/item', '.php');
        $this->deleteFiles($path, 'tmpl/list', '.php');

	}

	/**
	 * *********************  Data and Files stuff  *******************************
	 */
	/**
	/**
	 * Delete unwanted file
	 */
	public function deleteFiles($path = null, $file = null, $ext = null)
	{
        $path_to_file = $path . $file . $ext;
		if (is_file($path_to_file)) {
			File::delete($path_to_file);
			Factory::getApplication()->enqueueMessage('Deleted File - '.$path_to_file);
		}
	}

}
