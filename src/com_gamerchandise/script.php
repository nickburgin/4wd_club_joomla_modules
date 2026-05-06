<?php
/**
 * @version     4.1.2
 * @package     com_gamerchandise
 * @copyright   Copyright (C) 2011. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Created by Glenn Arkell - http://www.glennarkell.com.au
 */

// No direct access
defined('_JEXEC') or die();

define('MODIFIED', 1);
define('NOT_MODIFIED', 2);

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
use \Joomla\CMS\Filter\OutputFilter;

/**
 * Updates the database structure of the component
 *
 * @version  Release: 0.2b
 * @author   Component Creator <support@component-creator.com>
 * @since    0.1b
 */
class com_gamerchandiseInstallerScript extends InstallerScript
{

	/**
	 * The title of the component (printed on installation and uninstallation messages)
	 * @var string
	 */
	protected $extension = 'Merchandise System';

	public $compName = 'gamerchandise';

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
		echo '<p>' . Text::_('COM_' . STRTOUPPER($this->compName) . '_PREFLIGHT_' . STRTOUPPER($type) . '_TEXT') . '</p>';

		if (JVERSION < $this->minimumJoomla) {
			Factory::getApplication()->enqueueMessage('Minimum Joomla! Version is '.$this->minimumJoomla.' and your version is '.JVERSION.'. This is not the right version for your installation . . . ', 'danger');
			return false;
		} else {
			Factory::getApplication()->enqueueMessage('Minimum Joomla! Version is '.$this->minimumJoomla.' and your version is '.JVERSION.'. This installation will proceed . . . ', 'message');
			return parent::preflight($type, $parent);
		}

        return true;
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
		echo '<p>' . Text::_('COM_' . STRTOUPPER($this->compName) . '_INSTALL_TEXT') . '</p>';

        $this->createFolder('images', 'merchandise');

		$this->addDashboardMenu($this->compName, $this->compName);
		
		return true;

	}

	/**
	 * Method to update the component
	 * @param   mixed $parent Object who called this method.
	 * @return void
	 */
	public function update($parent)
	{
		// $parent is the class calling this method
		echo '<p>' . Text::_('COM_' . STRTOUPPER($this->compName) . '_UPDATE_TEXT') . '</p>';

		$dashB = $this->checkDashboard($this->compName);
		if (!$dashB) {
			$this->addDashboardMenu($this->compName, $this->compName);
		}

		return true;

	}

	/**
	 * Method to uninstall the component
	 * @param   mixed $parent Object who called this method.
	 * @return void
	 */
	public function uninstall($parent)
	{
		// $parent is the class calling this method
		echo '<p>' . Text::_('COM_' . STRTOUPPER($this->compName) . '_UNINSTALL_TEXT') . '</p>';

		$dashB = $this->checkDashboard($this->compName);
		if ($dashB) {
			foreach ($dashB as $dash) {
                $this->removeDashboardMenu($dash->id);
            }
		}

		return true;
		
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
		echo '<p>' . Text::_('COM_' . STRTOUPPER($this->compName) . '_POSTFLIGHT_' . STRTOUPPER($type) . '_TEXT') . '</p>';

		if (STRTOUPPER($type) == 'INSTALL') {
			// do something
			$cat_id1 = array(
					"Publications & Information",
					"Club Trinkets",
					"Shirts & Tops",
					"Jumpers & Jackets",
					"Head Wear"
					);
			$this->createCategory($cat_id1, 'com_'.$this->compName);
            Factory::getApplication()->enqueueMessage('Category Created - Product');
            // Load Colour categories
			$cat_id2 = array(
					" - Not Applicable - ",
					"Club Colours",
					"Navy"
					);
			$this->createCategory($cat_id2, 'com_'.$this->compName.'.prod_colours');
            Factory::getApplication()->enqueueMessage('Category Created - Colours/Designs');

            // Load Size categories
			$cat_id3 = array(
					" - Not Applicable - ",
					"Small",
					"Medium",
					"Large",
					"X-Large",
					"2X-Large",
					"3X-Large",
					"4X-Large",
					"10",
					"12",
					"14",
					"16",
					"18",
					"20",
					"22"
					);
			$this->createCategory($cat_id3, 'com_'.$this->compName.'.prod_sizes');
            Factory::getApplication()->enqueueMessage("Category Created - Product Sizes");
		}

		if (STRTOUPPER($type) == 'UPDATE') {
			// do something
	        $path = Path::clean( JPATH_ADMINISTRATOR . '/components/com_'.$this->compName.'/sql/updates/mysql/' );
			$this->deleteFiles($path, '0.0.01', '.sql');
			$this->deleteFiles($path, '0.0.06', '.sql');
			$this->deleteFiles($path, '3.0.00', '.sql');
			$this->deleteFiles($path, '3.0.04', '.sql');
			$this->deleteFiles($path, '3.1.0', '.sql');
			$this->deleteFiles($path, '4.0.0', '.sql');
			$this->deleteFiles($path, '4.0.1', '.sql');
			$this->deleteFiles($path, '4.0.2', '.sql');
			$this->deleteFiles($path, '4.0.5', '.sql');
            $this->deleteFiles($path, '0.0.1-20240319-0000', '.sql');

	        $pathM = Path::clean( JPATH_SITE . '/media/com_'.$this->compName.'/images/' );
			$this->deleteFiles($pathM, 'icon-16-male', '.png');
			$this->deleteFiles($pathM, 'icon-16-female', '.png');
			$this->deleteFiles($pathM, 'l_catgory', '.png');
			$this->deleteFiles($pathM, 'l_com_gamerchandise', '.png');
			$this->deleteFiles($pathM, 'l_poitems', '.png');
			$this->deleteFiles($pathM, 'l_ponums', '.png');
			$this->deleteFiles($pathM, 'l_products', '.png');
			$this->deleteFiles($pathM, 'l_sales', '.png');
			$this->deleteFiles($pathM, 's_catgory', '.png');
			$this->deleteFiles($pathM, 's_com_gamerchandise', '.png');
			$this->deleteFiles($pathM, 's_poitems', '.png');
			$this->deleteFiles($pathM, 's_ponums', '.png');
			$this->deleteFiles($pathM, 's_products', '.png');
			$this->deleteFiles($pathM, 's_sales', '.png');

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
	 * *********************  Dashboard stuff  *******************************
	 */
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
		        $result = $db->loadObjectList();
		    } catch (RuntimeException $e) {
		        Factory::getApplication()->enqueueMessage($e->getMessage(), 'danger');
		    }
	    }

		return $result;
	}

	/**
	 * Removes the dashboard menu module
	 * @param int $id The dashboard module id reference
	 * @return  void
	 */
	public function removeDashboardMenu($id)
	{
		$model  = Factory::getApplication()->bootComponent('com_modules')->getMVCFactory()->createModel('Module', 'Administrator', ['ignore_request' => true]);
        $table = $model->getTable();
        $table->load($id);

		if (!$table->delete())
		{
			Factory::getApplication()->enqueueMessage(Text::sprintf('COM_'.STRTOUPPER($this->compName).'_REMOVE_DASHBOARD_FAIL', $model->getError()));
		}
	}

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
	public static function getComponentVersion($compName)
	{
		$extPath = JPATH_ADMINISTRATOR . '/components/com_'.$compName.'/'.$compName.'.xml';
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
            $category->alias = OutputFilter::stringUrlSafe($cat);
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
	 * Create a folder
	 */
	public function createFolder($parent = null, $folder = null)
	{
        $path = Path::clean( JPATH_SITE . '/' . $parent . '/' . $folder );

		if (!is_dir($path) && !is_file($path)) {
            Folder::create($path);
			Factory::getApplication()->enqueueMessage('Folder Created - '.$path);
        } else {
			Factory::getApplication()->enqueueMessage('Folder Already Exists - '.$path);
		}
	}

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
		$logConf = new \stdClass();
		$logConf->id = 0;
		$logConf->type_title = $type;
		$logConf->type_alias = $extension;
		$logConf->id_holder = $key;
		$logConf->title_holder = $title;
		$logConf->table_name = $tablename;
		$logConf->text_prefix = $txtpref;

	    try {
			$result = Factory::getContainer()->get('DatabaseDriver')->insertObject('#__action_log_config', $logConf);
	    } catch (RuntimeException $e) {
	        Factory::getApplication()->enqueueMessage($e->getMessage().' Configuring Action Log', 'danger');
	        return false;
	    }

		return $result;
	}

}
