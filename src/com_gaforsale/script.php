<?php
/**
 * @version     4.2.2
 * @package     com_gaforsale
 * @copyright   Copyright (C) 2011. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Created by Glenn Arkell - http://www.glennarkell.com.au
 */

// No direct access
defined('_JEXEC') or die();

define('MODIFIED', 1);
define('NOT_MODIFIED', 2);

use \Joomla\CMS\Factory;
use \Joomla\CMS\Language\Text;
use \Joomla\Filesystem\File;
use \Joomla\Filesystem\Folder;
use \Joomla\Filesystem\Path;
use \Joomla\CMS\Table\Table;
use \Joomla\CMS\Mail\MailTemplate;
use \Joomla\CMS\Component\ComponentHelper;
use \Joomla\CMS\Installer\Installer;
use \Joomla\CMS\Installer\InstallerScript;
use \Joomla\CMS\Filter\OutputFilter;

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

	public $compName = 'gaforsale';
	
    public $oldVersion = '0';

	public $mailTmplSuffixs = array("fsitems", "fstombrs", "fsremind");

	public $mailTags = array("sitename","link_text","name","item_desc","item_price","created_date", "seller_contact", "seller_phone");

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

		if (JVERSION < $this->minimumJoomla) {
			Factory::getApplication()->enqueueMessage('Minimum Joomla! Version is '.$this->minimumJoomla.' and your version is '.JVERSION.'. This is not the right version for your installation . . . ', 'danger');
			return false;
		} else {
			Factory::getApplication()->enqueueMessage('Minimum Joomla! Version is '.$this->minimumJoomla.' and your version is '.JVERSION.'. This installation will proceed . . . ', 'message');
			return parent::preflight($type, $parent);
		}


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

		// Set a Dashboard Entry
		// @params string $dashboard and string $preset
		$this->addDashboardMenu($this->compName, $this->compName);

		// install the Mail Templates
		$this->installMailTemplates();

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

		$dashB = $this->checkDashboard($this->compName);
		if (!$dashB) {
			$this->addDashboardMenu($this->compName, $this->compName);
		}

		// install the Mail Templates
		$this->installMailTemplates();
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

        foreach ($this->mailTmplSuffixs as $tmpl) {
            $template_id = 'com_'.$this->compName.'.'.$tmpl;
            $tmplExists = $this->checkMailTemplates($template_id);
            if ($tmplExists) {
                $this->removeMailTemplate($template_id);
            }
        }

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
	        //$path = Path::clean( JPATH_ADMINISTRATOR . '/components/com_'.$this->compName.'/sql/updates/mysql/' );
			//$this->deleteFiles($path, '3.1.0', '.sql');
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
	 * *********************  Regularly used installation stuff  *******************************
	 */

	/**
	 * Create some special Category records
	 * @param   none
	 * @return boolean
	 */
	public function createCategories($extension, $cattype)
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
	 * *********************  Dashborad stuff  *******************************
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
	 * *********************  MailTemplate stuff  *******************************
	 */
	/**
	 * Install MailTemplate entrys
	 * @return boolean or object
	 */
	public function installMailTemplates()
	{
        foreach ($this->mailTmplSuffixs as $tmpl) {
            $template_id = 'com_'.$this->compName.'.'.$tmpl;
            $tmplExists = $this->checkMailTemplates($template_id);
            if (!$tmplExists) {
                $this->addMailTemplate($tmpl, $this->mailTags);
            } else {
                $this->updateMailTemplates($tmplExists);
            }
        }

		return true;
	}

	/**
	 * Update template records if tags changed
	 * @param   object $tmpl
	 */
	public function updateMailTemplates($tmpl)
	{
        // check tags
        $params = json_decode($tmpl->params);

        if (count($params->tags) != count($this->mailTags)) {
            $params->tags = $this->mailTags;
            $tmpl->params = json_encode($params);
            $result = Factory::getContainer()->get('DatabaseDriver')->updateObject('#__mail_templates', $tmpl, 'template_id');
            Factory::getApplication()->enqueueMessage(Text::sprintf('COM_'.STRTOUPPER($this->compName).'_MAILTMPL_UPDATED', $tmpl->template_id), 'success');
        }

	}

	/**
	 * Check if a MailTemplate entry exists
	 * @param   string $template_id (component + ext)
	 * @return boolean or object
	 */
	public function checkMailTemplates($template_id = 0)
	{
        $result = false;
		if ($template_id) {
			$db = Factory::getContainer()->get('DatabaseDriver');
	        $db->setQuery(' SELECT * FROM #__mail_templates WHERE template_id = '.$db->Quote($template_id) );
		    try {
		        $result = $db->loadObject();
		    } catch (RuntimeException $e) {
		        Factory::getApplication()->enqueueMessage($e->getMessage(), 'danger');
		    }
	    }

		return $result;
	}

	/**
	 * Removes the mailTemplate records
	 * @param   string $template_id (component + ext)
	 * @return  void
	 */
	public function removeMailTemplate($template_id)
	{
		$this->deleteData('#__mail_templates', 'template_id', $template_id, '=');
		Factory::getApplication()->enqueueMessage(Text::_('COM_'.STRTOUPPER($this->compName ?? '').'_EMAILTMPL_REMOVE_SUCCESS', 'notice'));
	}

	/**
	 * Create a new Mail Template
	 * @param   string $template_id (just the ext)
	 * @param   array $tags
	 * @return  void
	 */
	public function addMailTemplate($template_id, $tags)
	{
        $result = MailTemplate::createTemplate(
        	'com_'.$this->compName.'.'.$template_id,
        	'COM_'.STRTOUPPER($this->compName).'_EMAILTMPL_'.STRTOUPPER($template_id).'_SUBJECT',
        	'COM_'.STRTOUPPER($this->compName).'_EMAILTMPL_'.STRTOUPPER($template_id).'_BODY',
        	$tags,
        	'COM_'.STRTOUPPER($this->compName).'_EMAILTMPL_'.STRTOUPPER($template_id).'_HTMLBODY'
        );

		Factory::getApplication()->enqueueMessage('Email Template Record Created . . . '.$template_id);

	}

	/**
	 * *********************  Data and Files stuff  *******************************
	 */
	/**
	 * Update data
	 * @param string $table Table name to be updated
	 * @param string $fieldname field name to be updated
	 * @param string $newvalue value to set the above fieldname
	 * @param string $wherefield field name of the where clause to test for
	 * @param string $oldvalue value of the field name in the where clause
	 * @param string $opatr operator for the test in the where clause
	 * @return  void
	 */
	public function updateData($table = '#__user_profiles', $wherefield = 'profile_key', $fieldname = 'profile_value', $oldvalue = 'Life', $newvalue = 'Life Member', $opatr = '=')
	{
		$db    = Factory::getContainer()->get('DatabaseDriver');
		$query = $db->getQuery(true);
		$db->setQuery('UPDATE '.$table.' SET '.$fieldname.' = '.$newvalue.' WHERE '.$wherefield.' '.$opatr.' '.$db->quote($oldvalue));
		$db->execute();
		Factory::getApplication()->enqueueMessage('Records updated . . . '.$table);

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
	public function dropTable($table = null)
	{
		$db    = Factory::getContainer()->get('DatabaseDriver');
		$query = $db->getQuery(true);
		$db->setQuery('DROP TABLE IF EXISTS '.$table);
		$db->execute();
		Factory::getApplication()->enqueueMessage('Table dropped . . . '.$table);

	}

	/**
	 * Create data
	 */
	public function createData()
	{
		$rec = new \stdClass();
		$rec->id = 0;
		$rec->ordering = 0;
		$rec->state = 1;
		$rec->checked_out = 0;
		$rec->checked_out_time = NULL;
		$rec->created_by = 0;
		$rec->created_date = '2020-07-23 12:32:00';

        foreach ($days AS $ddate) {
            $rec->diary_date = $ddate;
            $result = Factory::getContainer()->get('DatabaseDriver')->insertObject('#__garafsqn_diarys', $rec);
        }

		Factory::getApplication()->enqueueMessage('Records created . . . ');

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
			return $this->oldVersion;
		}
	}

	/**
	 * ***************  Check for old data structure where columns need to be added  ***************
	 */

	/**
	 * Check for a columns in table
	 */
	private function checkColumns()
	{
		$table = '#__gausers_profile_audit';
		$column = 'post_update';
		$after = 'user_id';
		$type = 'text';

        $colExists = $this->checkColumnExists($table, $column);
		if (empty($colExists)) {
            Factory::getApplication()->enqueueMessage(Text::_('Column '.$column.' NOT Exists'), 'warning');
            $this->createColumn($table, $column, $after, $type);
        }

		$column = 'pre_update';
        $colExists = $this->checkColumnExists($table, $column);
		if (empty($colExists)) {
            Factory::getApplication()->enqueueMessage(Text::_('Column '.$column.' NOT Exists'), 'warning');
            $this->createColumn($table, $column, $after, $type);
        }

	}

	/**
	 * Check if a Database field exists
	 * @param   string $table
	 * @param   string $field
	 * @param   string $type
	 * @return boolean
	 */
	public function checkDBFields($table, $field, $ftype)
	{
		$db = Factory::getContainer()->get('DatabaseDriver');
	    $db->setQuery(' SELECT count(*) FROM information_schema.columns WHERE table_schema = '.$db->Quote($this->dbName).' AND table_name ='.$db->Quote($table).' AND column_name = '.$db->Quote($field) );
		try {
		    $return = $db->loadResult();
		} catch (RuntimeException $e) {
		    $return = false;
            Factory::getApplication()->enqueueMessage($e->getMessage(), 'danger');
		}
		return $return;
	}

	/**
	 * Check for a column in table
	 * @param   table name
	 * @param   column name
	 * @return  boolean
	 */
	private function checkColumnExists($table, $column)
	{
		$db    = Factory::getContainer()->get('DatabaseDriver');
		$db->setQuery('SHOW COLUMNS FROM '.$db->quotename($table) . ' LIKE '.$db->quote($column));
		try {
		    return $db->loadObject();
		} catch (RuntimeException $e) {
		    Factory::getApplication()->enqueueMessage($e->getMessage(), 'danger');
		    return false;
		}
	}

	/**
	 * Check for a column in table
	 * @param   table name
	 * @param   column name
	 * @return  boolean
	 */
	private function createColumn($table, $column, $after, $type)
	{
		$db    = Factory::getContainer()->get('DatabaseDriver');
		$db->setQuery('ALTER TABLE '.$db->quotename($table) . ' ADD '.$db->quotename($column).' '.$type.' NULL AFTER '.$db->quotename($after));
		try {
		    $db->execute();
		    Factory::getApplication()->enqueueMessage('Column '. $column . ' created', 'warning');
		} catch (RuntimeException $e) {
		    Factory::getApplication()->enqueueMessage($e->getMessage(), 'danger');
		}
	}

}
