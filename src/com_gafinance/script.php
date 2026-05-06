<?php
/**
 * @version    5.1.0
 * @package    Com_Gafinance
 * @author     Glenn Arkell <glenn@glennarkell.com.au>
 * @copyright  2023 Glenn Arkell
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

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
use \Joomla\CMS\Mail\MailTemplate;
use \Joomla\CMS\MVC\Model\AdminModel;
use \Joomla\CMS\Component\ComponentHelper;
use \Joomla\CMS\Installer\InstallerScript;
use \Joomla\CMS\Installer\Adapter\InstallerAdapter;
use \Joomla\CMS\Installer\Adapter\ComponentAdapter;
use \Joomla\CMS\Installer\Adapter\ModuleAdapter;
use \Joomla\CMS\Installer\Adapter\PluginAdapter;
use \Joomla\CMS\Filter\OutputFilter;

/**
 * Updates the structure of the component
 */
class com_gafinanceInstallerScript extends InstallerScript
{
	/**
	 * The title of the component (printed on installation and uninstallation messages)
	 * @var string
	 */
	protected $extension = 'Finance System';

	public $compName = 'gafinance';

	public $mailTmplSuffixs = array("trans");

	public $mailTags = array("sitename","link_text","tran_ref","tran_date");

	public $version = '5.1.0';

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
		echo '<p>' . Text::_('COM_'.STRTOUPPER($this->compName).'_PREFLIGHT_'.STRTOUPPER($type).'_TEXT') . '</p>';

        //$this->checkColumns();

        $version = $this->getComponentVersion($this->compName);
        if ($version) { $this->version = $version; }

        Factory::getApplication()->enqueueMessage(Text::sprintf('GA_VERSION_CHECK',$this->version), 'message');

		if (JVERSION < $this->minimumJoomla) {
			Factory::getApplication()->enqueueMessage(Text::sprintf('GA_INSTALL_CHECK_FAIL',$this->minimumJoomla,JVERSION), 'danger');
			return false;
		} else {
			Factory::getApplication()->enqueueMessage(Text::sprintf('GA_INSTALL_CHECK_OK',$this->minimumJoomla,JVERSION), 'message');
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
		//$parent->getParent()->setRedirectURL('index.php?option=com_gafinance');
		echo '<p>' . Text::_('COM_' . STRTOUPPER($this->compName) . '_INSTALL_TEXT') . '</p>';

		// Set a Dashboard Entry
		// @params string $dashboard and string $preset
		$this->addDashboardMenu($this->compName, $this->compName);

		// setup id for the template
        foreach ($this->mailTmplSuffixs as $tmpl) {
            $template_id = 'com_'.$this->compName.'.'.$tmpl;
            $tmplExists = $this->checkMailTemplates($template_id);
            if (!$tmplExists) {
                $this->addMailTemplate($tmpl, $this->mailTags);
            }
        }
	}

	/**
	 * method to uninstall the component
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

        foreach ($this->mailTmplSuffixs as $tmpl) {
            $template_id = 'com_'.$this->compName.'.'.$tmpl;
            $tmplExists = $this->checkMailTemplates($template_id);
            if ($tmplExists) {
                $this->removeMailTemplate($template_id);
            }
        }

	}
 
	/**
	 * method to update the component
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

		// setup id for the template
        foreach ($this->mailTmplSuffixs as $tmpl) {
            $template_id = 'com_'.$this->compName.'.'.$tmpl;
            $tmplExists = $this->checkMailTemplates($template_id);
            if (!$tmplExists) {
                $this->addMailTemplate($tmpl, $this->mailTags);
            }
        }

	}

	/**
	 * method to run after an install/update/uninstall method
	 * @return void
	 */
	public function postflight($type, $parent)
	{
		// $parent is the class calling this method
		echo '<p>' . Text::_('COM_' . STRTOUPPER($this->compName) . '_POSTFLIGHT_' . STRTOUPPER($type) . '_TEXT') . '</p>';

		if (STRTOUPPER($type) == 'INSTALL') {
			// do something
			$cats = array(
    			'General Operations'=>'General operations of the club/group',
    			'Bank Charges or Interest'=>'',
    			'Membership'=>'Costs and income related to memberships, includes affiliation charges',
    			'Merchandise'=>'',
    			'Donations'=>'',
    			'IT Related'=>'Club laptop, software, website etc',
    			'Advertising or Sponsorship'=>'',
    			'Training'=>'Costs and income relating to training activities',
    			'Venue Costs'=>'Costs associated with club/group such as club rooms or meeting locations',
    			'Miscellaneous'=>''
			);
 			$this->createCategories('com_'.$this->compName, $cats);
		}

		if (STRTOUPPER($type) == 'UPDATE') {
			// do something
			$path = Path::clean( JPATH_ADMINISTRATOR . '/components/com_gafinance/sql/updates/mysql/' );
			$this->deleteFiles($path, '0.0.01', '.sql');
			$this->deleteFiles($path, '3.4.00', '.sql');
 			$this->deleteFiles($path, '3.4.0', '.sql');
 			$this->deleteFiles($path, '4.0.5', '.sql');
 			$this->deleteFiles($path, '4.1.0', '.sql');

			// remove js file and folder if they exist (stopped using this jin J5)
			$pathMediaJS = Path::clean( JPATH_SITE . '/media/com_'.$this->compName.'/js/' );
			$this->deleteFiles($pathMediaJS, 'form','.js');
			$this->deleteFolder($pathMediaJS);

			$pathModTmpl = Path::clean( JPATH_SITE . '/modules/mod_'.$this->compName.'/' );
			$this->deleteFiles($pathModTmpl, 'tmpl/list','.php');
			$this->deleteFiles($pathModTmpl, 'tmpl/item','.php');
			$this->deleteFiles($pathModTmpl, 'tmpl/form','.php');
			$this->deleteFiles($pathModTmpl, 'helper','.php');
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
	 * Update template records until core updated
	 * NO LONGER NEEDED since 4.3.4
	 */
	public function updateMailTemplates($template_id)
	{
		$table = '#__mail_templates';
        $db    = Factory::getContainer()->get('DatabaseDriver');
		$query = $db->getQuery(true);
		$db->setQuery('SELECT * FROM #__mail_templates WHERE '.$db->quotename('template_id').' = '.$db->quote($template_id));
		try {
		    $tmpl = $db->loadObject();
            if (!isset($tmpl->extension) || $tmpl->extension == '') {
                $params = json_decode($tmpl->params);
                $params->tags = $params->tags[0];
                $tmpl->params = json_encode($params);
                $tmpl->extension = 'com_'.$this->compName;
                $result = Factory::getContainer()->get('DatabaseDriver')->updateObject('#__mail_templates', $tmpl, 'template_id');
            }
            return true;
		} catch (RuntimeException $e) {
		    Factory::getApplication()->enqueueMessage($e->getMessage(), 'warning');
		    return false;
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
	public static function getComponentVersion($component)
	{
		$componentTest = ComponentHelper::getComponent($component, true);
        if (empty($componentTest->enabled)) {
            return 0;
        } else {
            $componentXML = Installer::parseXMLInstallFile(Path::clean(JPATH_ADMINISTRATOR . '/components/com_'.$component.'/'.$component.'.xml'));
            return $componentXML['version'];
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
