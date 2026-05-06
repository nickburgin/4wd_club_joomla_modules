<?php
/**
 * @version    4.2.1
 * @package    Com_Gabroadcast
 * @author     Glenn Arkell <glenn@glennarkell.com.au>
 * @copyright  Copyright (C) 2013. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

define('MODIFIED', 1);
define('NOT_MODIFIED', 2);

// No direct access
defined('_JEXEC') or die;

use \Joomla\CMS\Factory;
use \Joomla\CMS\Language\Text;
use \Joomla\Filesystem\File;
use \Joomla\Filesystem\Folder;
use \Joomla\Filesystem\Path;
use \Joomla\CMS\Component\ComponentHelper;
use \Joomla\CMS\Installer\Installer;
use \Joomla\CMS\Installer\InstallerScript;
use \Joomla\CMS\Installer\Adapter\InstallerAdapter;
use \Joomla\CMS\Installer\Adapter\ComponentAdapter;
use \Joomla\CMS\Installer\Adapter\ModuleAdapter;
use \Joomla\CMS\Installer\Adapter\PluginAdapter;
use \Joomla\CMS\Filter\OutputFilter;

class com_gabroadcastInstallerScript extends InstallerScript
{

	/**
	 * The title of the component (printed on installation and uninstallation messages)
	 * @var string
	 */
	protected $extension = 'Broadcast System';

	/**
	 * The minimum Joomla! version required to install this extension
	 * @var   string
	 */
	protected $minimumJoomla = '4.0';

	public $comp_name = 'gabroadcast';

	/**
	 * method to run before an install/update/uninstall method
	 * @return void
	 */
	public function preflight($type, $parent)
	{
		// $parent is the class calling this method
		echo '<p>' . Text::_('COM_' . STRTOUPPER($this->comp_name) . '_PREFLIGHT_' . STRTOUPPER($type) . '_TEXT') . '</p>';

		if (JVERSION < $this->minimumJoomla) {
			Factory::getApplication()->enqueueMessage('Minimum Joomla! Version is '.$this->minimumJoomla.' and your version is '.JVERSION.'. This is not the right version for your installation . . . ', 'danger');
			return false;
		} else {
			Factory::getApplication()->enqueueMessage('Minimum Joomla! Version is '.$this->minimumJoomla.' and your version is '.JVERSION.'. This installation will proceed . . . ', 'message');
			return parent::preflight($type, $parent);
		}

	}

	/**
	 * method to install the component
	 * @return void
	 */
	public function install($parent)
	{
		// $parent is the class calling this method
		//$parent->getParent()->setRedirectURL('index.php?option=com_gaadvertising');
		echo '<p>' . Text::_('COM_' . STRTOUPPER($this->comp_name) . '_INSTALL_TEXT') . '</p>';

		//$this->installPlugins($parent);
		//$this->installModules($parent);

		// Set a Dashboard Entry
		// @params string $dashboard and string $preset
		$this->addDashboardMenu($this->comp_name, $this->comp_name);
	}

	/**
	 * method to uninstall the component
	 * @return void
	 */
	public function uninstall($parent)
	{
		// $parent is the class calling this method
		echo '<p>' . Text::_('COM_' . STRTOUPPER($this->comp_name) . '_UNINSTALL_TEXT') . '</p>';

		//$this->uninstallPlugins($parent);
		//$this->uninstallModules($parent);
		$this->removeDashboard($this->comp_name);
	}
 
	/**
	 * method to update the component
	 * @return void
	 */
	public function update($parent)
	{
		// $parent is the class calling this method
		echo '<p>' . Text::_('COM_' . STRTOUPPER($this->comp_name) . '_UPDATE_TEXT') . '</p>';
		
		//$this->installPlugins($parent);
		//$this->installModules($parent);

		$dashB = $this->checkDashboard($this->comp_name);
		if (!$dashB) {
			$this->addDashboardMenu($this->comp_name, $this->comp_name);
		}
	}

	/**
	 * method to run after an install/update/uninstall method
	 * @return void
	 */
	public function postflight($type, $parent)
	{
		// $parent is the class calling this method
		echo '<p>' . Text::_('COM_' . STRTOUPPER($this->comp_name) . '_POSTFLIGHT_' . STRTOUPPER($type) . '_TEXT') . '</p>';

		if (STRTOUPPER($type) == 'INSTALL') {
			// do something
		}

		if (STRTOUPPER($type) == 'UPDATE') {
			// do something
			$pathAdmin = Path::clean( JPATH_ADMINISTRATOR . '/components/com_'.$this->comp_name.'/sql/updates/mysql/' );
			$this->deleteFiles($pathAdmin, '0.0.01','.sql');
			$this->deleteFiles($pathAdmin, '1.0.02','.sql');
			$this->deleteFiles($pathAdmin, '4.0.0','.sql');
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

	/* ***************************  Usual Extras to Help  ********************************* */

	/**
	 * Check if a Dashboard module entry exists
	 * @param   string $component Component name
	 * @return boolean or object
	 */
	public function checkDashboard($component = 0)
	{
        $result = false;
		if ($component) {
			$db = Factory::getContainer()->get('DatabaseDriver');
	        $db->setQuery(' SELECT * FROM #__modules WHERE position = '.$db->Quote('cpanel-'.$component) );
		    try {
		        $result = $db->loadObject();
		    } catch (RuntimeException $e) {
		        Factory::getApplication()->enqueueMessage($e->getMessage(), 'danger');
		    }
	    }

		return $result;
	}

	/**
	 * Check if a Dashboard module entry exists
	 * @param   string $component Component name
	 * @return boolean or object
	 */
	public function removeDashboard($component = 0)
	{
        $result = false;
		if ($component) {
			$db = Factory::getContainer()->get('DatabaseDriver');
	        $db->setQuery(' DELETE #__modules WHERE position = '.$db->Quote('cpanel-'.$component) );
		    try {
		        $result = $db->loadObject();
		    } catch (RuntimeException $e) {
		        Factory::getApplication()->enqueueMessage($e->getMessage(), 'danger');
		    }
	    }

		return $result;
	}

	/**
	 * *********************  Regularly used installation stuff  *******************************
	 */

	/**
	 * Delete unwanted file
	 */
	protected function deleteFiles($path = null, $file = null, $ext = null)
	{
        $path_to_file = $path.$file.$ext;
		if (is_file($path_to_file)) {
			File::delete($path_to_file);
			Factory::getApplication()->enqueueMessage('Deleted File - '.$path_to_file);
		}
	}

	/**
	 * Delete unwanted folder
	 */
	protected function deleteFolder($path = null)
	{
		if (is_dir($path)) {
			Folder::delete($path);
			Factory::getApplication()->enqueueMessage('Deleted Folder - '.$path);
		}
	}

	/**
	 * Create some special Category records
	 * @param   none
	 * @return boolean
	 */
	function createCategories($extension, $cattype)
	{
        $app = Factory::getApplication();
		
		foreach ($cattype as $title => $desc) {
            $category = Table::getInstance('Category');
            $category->extension = $extension;
            $category->title = $title;
            $category->alias = OutputFilter::stringUrlSafe($title);
            $category->description = $desc;
            $category->published = 1;
            $category->access = 1;
            $category->params = '{"category_layout":"","image":"","image_alt":""}';
            $category->metadata = '{"page_title":"","author":"","robots":""}';
            $category->language = '*';
            // Set the location in the tree
            $category->setLocation(1, 'last-child');
            // Check to make sure our data is valid
            if (!$category->check()) {
                $app->enqueueMessage(Text::_($category->getError()));
                return false;
            }
            // Now store the category
            if (!$category->store(true)) {
                $app->enqueueMessage(Text::_($category->getError()));
                return false;
            }
	 	}
        // Build the path for our category
        $category->rebuildPath($category->id);
        echo '<p>' . Text::_('New categories created') . '</p>';

		return true;
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
			$db = Factory::getContainer()->get('DatabaseDriver');
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
			$db = Factory::getContainer()->get('DatabaseDriver');
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
				$db = Factory::getContainer()->get('DatabaseDriver');
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

	/**
	 * *********************  All the action log stuff  *******************************
	 */
	/**
	 * Check if extension is set in Action Logs Extensions register
	 * @param   string $extension   Extension name
	 * @return boolean
	 */
	function checkIfActionLog($extension)
	{
		$result = false;

		$db = Factory::getContainer()->get('DatabaseDriver');
		$query = $db->getQuery(true)
			->select($db->quoteName('id'))
			->from($db->quoteName('#__action_logs_extensions'))
			->where($db->quoteName('extension') .' = '. $db->Quote($extension));
		$db->setQuery($query);
	    try {
	        // If it fails, it will throw a RuntimeException
	        $result = $db->loadResult();
	    } catch (RuntimeException $e) {
	        Factory::getApplication()->enqueueMessage($e->getMessage().' Check Action Log Entry', 'danger');
	    }

		return $result;
	}

	/**
	 * Insert extension to the Action Logs Extensions register
	 * @param   string $extension   Extension name
	 * @return boolean
	 */
	function loadToActionLog($extension)
	{
		$result = false;
        $db = Factory::getContainer()->get('DatabaseDriver');
        $db->setQuery(' INSERT into #__action_logs_extensions (extension) VALUES ('.$db->Quote($extension).') ' );
	    try {
	        // If it fails, it will throw a RuntimeException
	        $result = $db->execute();
	    } catch (RuntimeException $e) {
	        Factory::getApplication()->enqueueMessage($e->getMessage().' Load Action Log Entry', 'danger');
	        return false;
	    }

		return $result;
	}

	/**
	 * Check if extension is set in Action Logs Configuration register
	 * @param   string $extension   Extension name
	 * @return boolean
	 */
	function checkIfActionLogConfig($extension)
	{
		$result = false;

		$db = Factory::getContainer()->get('DatabaseDriver');
		$query = $db->getQuery(true)
			->select($db->quoteName('id'))
			->from($db->quoteName('#__action_log_config'))
			->where($db->quoteName('type_alias') .' = '. $db->Quote($extension));
		$db->setQuery($query);
	    try {
	        // If it fails, it will throw a RuntimeException
	        $result = $db->loadResult();
	    } catch (RuntimeException $e) {
	        Factory::getApplication()->enqueueMessage($e->getMessage().' Checking Action Log Configuration', 'danger');
	    }

		return $result;
	}

	/**
	 * Insert extension to the Action Log Configuration table
	 * @param   string $extension   Extension name
	 * @return boolean
	 */
	function loadToActionLogConfig($extension, $type, $key = 'id', $title, $tablename, $txtpref)
	{
		// Create and populate an object.
		$logConf = new stdClass();
		$logConf->id = 0;
		$logConf->type_title = $type;
		$logConf->type_alias = $extension;
		$logConf->id_holder = $key;
		$logConf->title_holder = $title;
		$logConf->table_name = $tablename;
		$logConf->text_prefix = $txtpref;

	    try {
	        // If it fails, it will throw a RuntimeException
			// Insert the object into the user profile table.
			$result = Factory::getContainer()->get('DatabaseDriver')->insertObject('#__action_log_config', $logConf);
	    } catch (RuntimeException $e) {
	        Factory::getApplication()->enqueueMessage($e->getMessage().' Configuring Action Log', 'danger');
	        return false;
	    }

		return $result;
	}
}
