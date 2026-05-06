<?php
/**
 * @version     4.0.2
 * @package     com_gaforsale
 * @copyright   Copyright (C) 2011. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Created by Glenn Arkell - http://www.glennarkell.com.au
 */

// No direct access
define('MODIFIED', 1);
define('NOT_MODIFIED', 2);

defined('_JEXEC') or die();

use \Joomla\CMS\Factory;
use \Joomla\CMS\Installer\Installer;
use \Joomla\Filesystem\File;
use \Joomla\Filesystem\Folder;
use \Joomla\Filesystem\Path;
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\Table\Table;
use \Joomla\CMS\MVC\Model\AdminModel;
use \Joomla\CMS\Component\ComponentHelper;
use \Joomla\CMS\Installer\InstallerScript;
use \Joomla\CMS\Installer\Adapter\InstallerAdapter;
use \Joomla\CMS\Installer\Adapter\ComponentAdapter;
use \Joomla\CMS\Installer\Adapter\ModuleAdapter;
use \Joomla\CMS\Installer\Adapter\PluginAdapter;

/**
 * Updates the database structure of the component
 *
 * @version  Release: 0.2b
 * @author   Component Creator <support@component-creator.com>
 * @since    0.1b
 */
class com_gaforsaleInstallerScript extends InstallerScript
{
	/**
	 * The title of the component (printed on installation and uninstallation messages)
	 * @var string
	 */
	protected $extension = 'For Sale System';

	public $comp_name = 'gaforsale';

	/**
	 * The minimum Joomla! version required to install this extension
	 * @var   string
	 */
	protected $minimumJoomla = '4.0';

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
		echo '<p>' . Text::_('COM_GAFORSALE_PREFLIGHT_'.STRTOUPPER($type).'_TEXT') . '</p>';

		$version = $this->getComponentVersion($this->comp_name);

		//if ($version > '' && $version < '3.1.0') {
		//	Factory::getApplication()->enqueueMessage('Please upgrade your current version to 3.1.0 so that the latest database updates are included.', 'danger');
		//	return false;
		//} else {
			if (JVERSION < $this->minimumJoomla) {
				Factory::getApplication()->enqueueMessage('Minimum Joomla! Version is '.$this->minimumJoomla.' and your version is '.JVERSION.'. This is not the right version for your installation . . . ', 'danger');
				return false;
			} else {
				Factory::getApplication()->enqueueMessage('Minimum Joomla! Version is '.$this->minimumJoomla.' and your version is '.JVERSION.'. This installation will proceed . . . ', 'message');
				return parent::preflight($type, $parent);
			}
		//}


	}

	/**
	 * Method to install the component
	 * @param   mixed $parent Object who called this method.
	 * @return void
	 * @since 0.2b
	 */
	public function install($parent)
	{
		// $parent is the class calling this method
		echo '<p>' . Text::_('COM_GAFORSALE_INSTALL_TEXT') . '</p>';

		//$this->installDb($parent);
		//$this->installPlugins($parent);
		//$this->installModules($parent);
	}

	/**
	 * Method to update the component
	 * @param   mixed $parent Object who called this method.
	 * @return void
	 */
	public function update($parent)
	{
		// $parent is the class calling this method
		echo '<p>' . Text::_('COM_GAFORSALE_UPDATE_TEXT') . '</p>';

		//$this->installDb($parent);
		//$this->installPlugins($parent);
		//$this->installModules($parent);
	}

	/**
	 * Method to uninstall the component
	 * @param   mixed $parent Object who called this method.
	 * @return void
	 */
	public function uninstall($parent)
	{
		// $parent is the class calling this method
		echo '<p>' . Text::_('COM_GAFORSALE_UNINSTALL_TEXT') . '</p>';

		//$this->uninstallPlugins($parent);
		//$this->uninstallModules($parent);
	}

	/**
	 * @param   string $type   type
	 * @param   string $parent parent
	 * @return boolean
	 * @since Kunena
	 */
	public function postflight($type, $parent)
	{
		// $parent is the class calling this method
		echo '<p>' . Text::_('COM_GAFORSALE_POSTFLIGHT_'.STRTOUPPER($type).'_TEXT') . '</p>';

		if (STRTOUPPER($type) == 'INSTALL') {
			// do something
		}

		if (STRTOUPPER($type) == 'UPDATE') {
			// do something
	        $path = Path::clean( JPATH_ADMINISTRATOR . '/components/com_'.$this->comp_name.'/sql/updates/mysql/' );
			$this->deleteFiles($path, '0.0.01', '.sql');
			$this->deleteFiles($path, '0.0.1', '.sql');
			$this->deleteFiles($path, '1.0.02', '.sql');
			$this->deleteFiles($path, '1.0.03', '.sql');
			$this->deleteFiles($path, '1.1.00', '.sql');
			$this->deleteFiles($path, '3.0.04', '.sql');
			$this->deleteFiles($path, '3.1.0', '.sql');
		}

		return true;
	}

	/**
	 * @param   string  $parent  parent
	 * @return void
	 */
	public function discover_install($parent)
	{
		return self::install($parent);
	}

	/**
	 * *********************  Extra installation stuff  *******************************
	 */

	/**
	 * Copy in data from the old J3 version
	 */
	public function copyOldData($oldTable = null, $newTable = null, $oldFields = null, $newFields = null, $whereClause = '')
	{
		if ($oldTable) {
			//
			$db    = Factory::getContainer()->get('DatabaseDriver');
			$query = $db->getQuery(true);
			$db->setQuery('INSERT into '.$newTable.' ('.$newFields.') (SELECT '.$oldFields.' FROM '.$oldTable.' '.$whereClause.')');
			
			try {
				$db->execute();
				Factory::getApplication()->enqueueMessage('Data Copied from '.$oldTable.' into '.$newTable);
			    return true;
			} catch (Exception $ex) {
				Factory::getApplication()->enqueueMessage('Data Copied FAILED '.$ex);
			    return false;
			}

		}
		return false;
	}

	/* ***************************  Usual Extras to Help  ********************************* */
	/**
	 * Get the version of the component
	 * @return version element of manifest
	 */
	public static function getComponentVersion($comp_name)
	{
		$extPath = JPATH_ADMINISTRATOR . '/components/com_'.$comp_name.'/'.$comp_name.'.xml';
		$componentXML = Installer::parseXMLInstallFile(Path::clean($extPath));

		if ($componentXML) {
			return $componentXML['version'];
		} else {
			return $componentXML;
		}
	}

    /**
    * Function to create category records
    * @param array category titles
    * @param string category group or type
    * @return void
    */
    protected function createCategory($cat_titles, $cat_ext)
    {
		foreach ($cat_titles as $cat) {
            $category = Table::getInstance('Category');
            $category->extension = $cat_ext;
            $category->title = $cat;
            $category->description = '';
            $category->published = 1;
            $category->access = 1;
            $category->params = '{"category_layout":"","image":"","image_alt":""}';
            $category->metadata = '{"page_title":"","author":"","robots":""}';
            $category->language = '*';
            // Set the location in the tree
            $category->setLocation(1, 'last-child');
            // Check to make sure our data is valid
            if (!$category->check()) {
                throw new \Exception(500, $category->getError());
                return false;
            }
            // Now store the category
            if (!$category->store(true)) {
                throw new \Exception(500, $category->getError());
                return false;
            }
	 	}
        // Build the path for our category
        $category->rebuildPath($category->id);
        echo '<p>' . Text::_('Categories created') . '</p>';
        return $category->id;
	}

	/**
	 * Delete old data
	 */
	public function deleteData($table = '#__extensions', $field = 'element', $value = '%com_weblinks%', $opatr = 'LIKE')
	{
		$db    = Factory::getContainer()->get('DatabaseDriver');
		$query = $db->getQuery(true);
		$db->setQuery('DELETE from '.$table.' WHERE '.$field.' '.$opatr.' '.$db->quote($value));
		$db->execute();
		Factory::getApplication()->enqueueMessage('Records removed from . . . '.$table);

	}

	/**
	 * Drop old tables
	 */
	public function dropTable($table = '#__gaevents_events')
	{
		$db    = Factory::getContainer()->get('DatabaseDriver');
		$query = $db->getQuery(true);
		$db->setQuery('DROP TABLE IF EXISTS '.$table);
		$db->execute();
		Factory::getApplication()->enqueueMessage('Table dropped . . . '.$table);

	}

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

	/**
	 * Delete unwanted folder
	 */
	public function deleteFolder($path = null)
	{
		if (is_dir($path)) {
			Folder::delete($path);
			Factory::getApplication()->enqueueMessage('Deleted Folder - '.$path);
		}
	}

	/**
	 * *********************  All the plugin installation stuff  *******************************
	 */

	/**
	 * Installs plugins for this component
	 * @param   mixed $parent Object who called the install/update method
	 * @return void
	 */
	private function installPlugins($parent)
	{
		$installation_folder = $parent->getParent()->getPath('source');
		$app                 = Factory::getApplication();

		/* @var $plugins SimpleXMLElement */
		if (method_exists($parent, 'getManifest'))
		{
			$plugins = $parent->getManifest()->plugins;
		}
		else
		{
			$plugins = $parent->get('manifest')->plugins;
		}

		if (count($plugins->children()))
		{
			$db    = Factory::getContainer()->get('DatabaseDriver');
			$query = $db->getQuery(true);

			foreach ($plugins->children() as $plugin)
			{
				$pluginName  = (string) $plugin['plugin'];
				$pluginGroup = (string) $plugin['group'];
				$path        = $installation_folder . '/plugins/' . $pluginGroup . '/' . $pluginName;
				$installer   = new Installer;

				if (!$this->isAlreadyInstalled('plugin', $pluginName, $pluginGroup))
				{
					$result = $installer->install($path);
				}
				else
				{
					$result = $installer->update($path);
				}

				if ($result)
				{
					$app->enqueueMessage('Plugin ' . $pluginName . ' was installed successfully');
				}
				else
				{
					$app->enqueueMessage('There was an issue installing the plugin ' . $pluginName,
						'error');
				}

				$query
					->clear()
					->update('#__extensions')
					->set('enabled = 1')
					->where(
						array(
							'type LIKE ' . $db->quote('plugin'),
							'element LIKE ' . $db->quote($pluginName),
							'folder LIKE ' . $db->quote($pluginGroup)
						)
					);
				$db->setQuery($query);
				$db->execute();
			}
		}
	}

	/**
	 * Uninstalls plugins
	 * @param   mixed $parent Object who called the uninstall method
	 * @return void
	 */
	private function uninstallPlugins($parent)
	{
		$app     = Factory::getApplication();

		if (method_exists($parent, 'getManifest'))
		{
			$plugins = $parent->getManifest()->plugins;
		}
		else
		{
			$plugins = $parent->get('manifest')->plugins;
		}

		if (count($plugins->children()))
		{
			$db    = Factory::getContainer()->get('DatabaseDriver');
			$query = $db->getQuery(true);

			foreach ($plugins->children() as $plugin)
			{
				$pluginName  = (string) $plugin['plugin'];
				$pluginGroup = (string) $plugin['group'];
				$query
					->clear()
					->select('extension_id')
					->from('#__extensions')
					->where(
						array(
							'type LIKE ' . $db->quote('plugin'),
							'element LIKE ' . $db->quote($pluginName),
							'folder LIKE ' . $db->quote($pluginGroup)
						)
					);
				$db->setQuery($query);
				$extension = $db->loadResult();

				if (!empty($extension))
				{
					$installer = new Installer;
					$result    = $installer->uninstall('plugin', $extension);

					if ($result)
					{
						$app->enqueueMessage('Plugin ' . $pluginName . ' was uninstalled successfully');
					}
					else
					{
						$app->enqueueMessage('There was an issue uninstalling the plugin ' . $pluginName,
							'error');
					}
				}
			}
		}
	}

	/**
	 * *********************  All the module installation stuff  *******************************
	 */

	/**
	 * Installs modules for this component
	 * @param   mixed $parent Object who called the install/update method
	 * @return void
	 */
	private function installModules($parent)
	{
		$installation_folder = $parent->getParent()->getPath('source');
		$app                 = Factory::getApplication();

		if (method_exists($parent, 'getManifest'))
		{
			$modules = $parent->getManifest()->modules;
		}
		else
		{
			$modules = $parent->get('manifest')->modules;
		}

		if (!empty($modules))
		{

			if (count($modules->children()))
			{
				foreach ($modules->children() as $module)
				{
					$moduleName = (string) $module['module'];
					$path       = $installation_folder . '/modules/' . $moduleName;
					$installer  = new Installer;

					if (!$this->isAlreadyInstalled('module', $moduleName))
					{
						$result = $installer->install($path);
					}
					else
					{
						$result = $installer->update($path);
					}

					if ($result)
					{
						$app->enqueueMessage('Module ' . $moduleName . ' was installed successfully');
					}
					else
					{
						$app->enqueueMessage('There was an issue installing the module ' . $moduleName,
							'error');
					}
				}
			}
		}
	}

	/**
	 * Uninstalls modules
	 * @param   mixed $parent Object who called the uninstall method
	 * @return void
	 */
	private function uninstallModules($parent)
	{
		$app = Factory::getApplication();

		if (method_exists($parent, 'getManifest'))
		{
			$modules = $parent->getManifest()->modules;
		}
		else
		{
			$modules = $parent->get('manifest')->modules;
		}

		if (!empty($modules))
		{

			if (count($modules->children()))
			{
				$db    = Factory::getContainer()->get('DatabaseDriver');
				$query = $db->getQuery(true);

				foreach ($modules->children() as $plugin)
				{
					$moduleName = (string) $plugin['module'];
					$query
						->clear()
						->select('extension_id')
						->from('#__extensions')
						->where(
							array(
								'type LIKE ' . $db->quote('module'),
								'element LIKE ' . $db->quote($moduleName)
							)
						);
					$db->setQuery($query);
					$extension = $db->loadResult();

					if (!empty($extension))
					{
						$installer = new Installer;
						$result    = $installer->uninstall('module', $extension);

						if ($result)
						{
							$app->enqueueMessage('Module ' . $moduleName . ' was uninstalled successfully');
						}
						else
						{
							$app->enqueueMessage('There was an issue uninstalling the module ' . $moduleName,
								'error');
						}
					}
				}
			}
		}
	}

	/**
	 * Check if an extension is already installed in the system
	 * @param   string $type   Extension type
	 * @param   string $name   Extension name
	 * @param   mixed  $folder Extension folder(for plugins)
	 * @return boolean
	 */
	private function isAlreadyInstalled($type, $name, $folder = null)
	{
		$result = false;

		switch ($type)
		{
			case 'plugin':
				$result = file_exists(JPATH_PLUGINS . '/' . $folder . '/' . $name);
				break;
			case 'module':
				$result = file_exists(JPATH_SITE . '/modules/' . $name);
				break;
		}

		return $result;
	}

}
