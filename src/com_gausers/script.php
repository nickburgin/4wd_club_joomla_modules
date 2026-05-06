<?php
/**
 * @version     5.1.6
 * @package     com_gausers
 * @copyright   Copyright (C) 2011. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Created by Glenn Arkell - http://www.glennarkell.com.au
 */

// No direct access
defined('_JEXEC') or die();

define('MODIFIED', 1);
define('NOT_MODIFIED', 2);

use \Joomla\CMS\Factory;
use \Joomla\Filesystem\File;
use \Joomla\Filesystem\Folder;
use \Joomla\Filesystem\Path;
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\Table\Table;
use \Joomla\CMS\Date\Date;
use \Joomla\CMS\User\UserHelper;
use \Joomla\CMS\Mail\MailTemplate;
use \Joomla\CMS\MVC\Model\AdminModel;
use \Joomla\CMS\Component\ComponentHelper;
use \Joomla\CMS\Installer\Installer;
use \Joomla\CMS\Installer\InstallerScript;
use \GlennArkell\Component\Gausers\Administrator\Helper\GausersHelper;
// use \Joomla\CMS\Installer\Adapter\ComponentAdapter;
// use \Joomla\CMS\Installer\Adapter\ModuleAdapter;
// use \Joomla\CMS\Installer\Adapter\PluginAdapter;
// use \Joomla\CMS\Installer\Adapter\TemplateAdapter;
use \Joomla\CMS\Filter\OutputFilter;

/**
 * Updates the database structure of the component
 *
 * @version  Release: 0.2b
 * @author   Component Creator <support@component-creator.com>
 * @since    0.1b
 */
class com_gausersInstallerScript extends InstallerScript
{

	/**
	 * The title of the component (printed on installation and uninstallation messages)
	 * @var string
	 */
	protected $extension = 'User Membership System';

	public $compName = 'gausers';

	public $compVersion = '5.1.6';
	public $oldVersion = '0';

    public $mailTags = array("name","email","sitename","link_text","emailbody");
    public $mailTmplSuffixs = array("mbrnew","mbrsec");

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

		//$this->oldVersion = $this->getComponentVersion($this->compName);

		if (JVERSION < $this->minimumJoomla) {
			Factory::getApplication()->enqueueMessage(Text::sprintf('COM_'.STRTOUPPER($this->compName).'_INSTALL_CHECK_FAIL',$this->minimumJoomla,JVERSION), 'danger');
			return false;
		} else {
			Factory::getApplication()->enqueueMessage(Text::sprintf('COM_'.STRTOUPPER($this->compName).'_INSTALL_CHECK_OK',$this->minimumJoomla,JVERSION), 'message');
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
		echo '<p>' . Text::_('COM_'.STRTOUPPER($this->compName).'_INSTALL_TEXT') . '</p>';

		$this->installPlugins($parent);
		$this->installModules($parent);
		
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

        $this->createFolder('images', 'members');
        $this->createFolder('images/members', 'invoices');
        $this->createFolder('images/members', 'barcodes');
        $this->createFolder('images/members', 'membersphotos');
        $this->createFolder('images/members', 'import');
        $this->createFolder('images/members', 'applics');
        
        $catName = 'payment';
        $cattype = array(
                    'Direct Debit'=>'Pay directly into bank account.',
                    'Cash'=>'Pay cash at meeting or function.',
                    'PayPal'=>'Pay via PayPal account.',
                    'Cheque'=>'Pay using a cheque which is a real nuisance.'
                    );
        $this->createCategories('com_'.$this->compName.'.'.$catName, $cattype);

	}

	/**
	 * Method to update the component
	 * @param   mixed $parent Object who called this method.
	 * @return void
	 */
	public function update($parent)
	{
		// $parent is the class calling this method
		echo '<p>' . Text::_('COM_'.STRTOUPPER($this->compName).'_UPDATE_TEXT') . '</p>';

		$this->installPlugins($parent);
		$this->installModules($parent);
        $this->createFolder('images/members', 'applics');

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
	 * Method to uninstall the component
	 * @param   mixed $parent Object who called this method.
	 * @return void
	 */
	public function uninstall($parent)
	{
		// $parent is the class calling this method
		echo '<p>' . Text::_('COM_'.STRTOUPPER($this->compName).'_UNINSTALL_TEXT') . '</p>';

		$this->uninstallPlugins($parent);
		$this->uninstallModules($parent);

		$dashB = $this->checkDashboard($this->compName);
		if ($dashB) {
			$this->removeDashboardMenu($dashB->id);
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
	 * @param   string $type   type
	 * @param   string $parent parent
	 * @return boolean
	 * @since Kunena
	 */
	public function postflight($type, $parent)
	{
		// $parent is the class calling this method
		echo '<p>' . Text::_('COM_'.STRTOUPPER($this->compName).'_POSTFLIGHT_'.STRTOUPPER($type).'_TEXT') . '</p>';

		if (STRTOUPPER($type) == 'INSTALL') {
			// do something
		}

		if (STRTOUPPER($type) == 'UPDATE') 
        {
			// do something

			// update the component name in MailTemplates tables until fix in core
    		// update mail template records until core is updated
            foreach ($this->mailTmplSuffixs as $tmpl) {
                $template_id = 'com_'.$this->compName.'.'.$tmpl;
                $tmplupds = $this->updateMailTemplates($template_id);
                if ($tmplupds) {
                    Factory::getApplication()->enqueueMessage('Mail Templates Updated - '.$template_id, 'notice');
                }
            }

			// remove old SQL change files
 	        //$path = Path::clean( JPATH_ADMINISTRATOR . '/components/com_'.$this->compName.'/sql/updates/mysql/' );
 			//$this->deleteFiles($path, '4.9.5', '.sql');

//             if ($this->oldVersion < $this->compVersion) {
//                 $catName = '.payment';
//                 $cattype = array(
//                             'Direct Debit'=>'Pay directly into bank account.',
//                             'Cash'=>'Pay cash at meeting or function.',
//                             'PayPal'=>'Pay via PayPal account.',
//                             'Cheque'=>'Pay using a cheque which is a real nuisance.',
//                             'Unknown'=>'Probably paid before payment types added.'
//                             );
//                 $lastCatID = $this->createCategories('com_'.$this->compName.$catName, $cattype);
//                 $this->updateData('#__gausers_invoices', 'pay_type', $lastCatID, 'pay_type = 0 AND paid_date IS NOT NULL');
//             }

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

	/**
	 * Update for new membership types records
	 */
	public function insertMemtype($memtype = '', $join = 0, $subs = 0)
	{
		if (!empty($memtype)) {
			//
			$db    = Factory::getContainer()->get('DatabaseDriver');
			$query = $db->getQuery(true);
			$db->setQuery('INSERT into #__gausers_mshiptypes (state, title, joining_fee, subscrib_amt) VALUES (1, '.$db->quote($memtype).','.$db->quote($join).','.$db->quote($subs).')');
			
			try {
				$db->execute();
				Factory::getApplication()->enqueueMessage('New Membership Type created - '.$memtype);
			    return true;
			} catch (Exception $ex) {
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
			return $this->oldVersion;
		}
	}

	/**
	 * Get membership types from profile
	 * @return boolean or object
	 */
	public function getMemtypes($prof)
	{
        $result = false;
		if ($prof) {
			$db = Factory::getContainer()->get('DatabaseDriver');
	        $db->setQuery(' SELECT profile_value FROM #__user_profiles WHERE profile_key = '.$db->Quote($prof.'.memtype').' GROUP BY profile_value ' );
		    try {
		        $result = $db->loadObjectList();
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
	 * Update template records until core updated
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
	 * Removes the dashboard menu module
	 * @param int $id The dashboard module id reference
	 * @return  void
	 */
	public function removeMailTemplate($template_id)
	{
		$this->deleteData('#__mail_templates', 'template_id', $template_id, '=');
		Factory::getApplication()->enqueueMessage(Text::_('COM_'.STRTOUPPER($this->compName ?? '').'_EMAILTMPL_REMOVE_SUCCESS', 'notice'));
	}

	/**
	 * Create a new Mail Template
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

		return $category->id;
	}

	/**
	 * Clean Up Profile data to remove quotes
	 * @return  void
	 */
	public function cleanUpProfileData()
	{
// 		$db    = Factory::getContainer()->get('DatabaseDriver');
// 		$query = $db->getQuery(true);
// 		$db->setQuery('UPDATE #__user_profiles SET profile_value = REPLACE(profile_value, '"', '') ');
// 		$db->execute();
// 		Factory::getApplication()->enqueueMessage('Profile Records cleaned . . . ');

	}

	/**
	 * Update date data regards to
	 */
	public function updateDateFields($table, $field)
	{
		$db    = Factory::getContainer()->get('DatabaseDriver');
		$query = $db->getQuery(true);
		$db->setQuery('UPDATE '.$db->quotename($table).' SET '.$db->quotename($field).' = NULL WHERE '.$db->quotename($field).' = '.$db->Quote('0000-00-00 00:00:00').' ');
		$db->execute();

		Factory::getApplication()->enqueueMessage('Date Field Updated to null for '.$field);
	}

	/**
	 *Update data
	 * @param string $table Table name to be updated
	 * @param string $fieldname field name to be updated
	 * @param string $newvalue value to set the above fieldname
	 * @param string $wherefield field name of the where clause to test for
	 * @param string $oldvalue value of the field name in the where clause
	 * @param string $opatr operator for the test in the where clause
	 * @return  void
	 */
	public function updateData($table = '#__users', $fieldname = 'name', $newvalue = 'Newname', $whereclause)
	{
		//Factory::getApplication()->enqueueMessage('Records updated . . . '.$table.' - '.$fieldname.' - '.$newvalue.' - '.$wherevalue);
        $db    = Factory::getContainer()->get('DatabaseDriver');
		$query = $db->getQuery(true);
		$db->setQuery('UPDATE '.$table.' SET '.$fieldname.' = '.$db->quote($newvalue).' WHERE '.$whereclause);
		$db->execute();
		Factory::getApplication()->enqueueMessage('Records updated . . . '.$table.' - '.$fieldname.' - '.$newvalue);

	}

	/**
	 *Update profile data
	 */
	public function updateProfData($id, $newvalue, $wherevalue = 'profiledocs.cert_name')
	{
		//Factory::getApplication()->enqueueMessage('Records updated . . . '.$table.' - '.$fieldname.' - '.$newvalue.' - '.$wherevalue);
        $db    = Factory::getContainer()->get('DatabaseDriver');
		$query = $db->getQuery(true);
		$db->setQuery('UPDATE #__user_profiles SET profile_value = '.$db->quote($newvalue).' WHERE user_id = '.(int) $id.' AND profile_key = '.$db->quote($wherevalue));
		$db->execute();
		Factory::getApplication()->enqueueMessage('Profile updated . . . '.$id.' - '.$newvalue);

	}

	/**
	 *Update profile data
	 */
	public function insertProfData($id, $newvalue, $keyvalue = 'profiledocs.cert_name')
	{
		//Factory::getApplication()->enqueueMessage('Records updated . . . '.$table.' - '.$fieldname.' - '.$newvalue.' - '.$wherevalue);
        $db    = Factory::getContainer()->get('DatabaseDriver');
		$query = $db->getQuery(true);
		$db->setQuery('INSERT into #__user_profiles ( user_id, profile_key, profile_value, ordering) VALUES ('.(int) $id.', '.$db->quote($keyvalue).', '.$db->quote($newvalue).', 0)');
		$db->execute();
		Factory::getApplication()->enqueueMessage('        Profile inserted . . . '.$id.' - '.$newvalue);

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
	 * Get all user records and split the name field
	 */
	public function breakDownNames()
	{
		$db    = Factory::getContainer()->get('DatabaseDriver');
		$query = $db->getQuery(true);
		$query->select(' id, name, SUBSTRING_INDEX(SUBSTRING_INDEX(name, \' \', 1), \' \', -1) AS first_name ');
		$query->select(' If(  length(name) - length(replace(name, \' \', \'\'))>1, SUBSTRING_INDEX(SUBSTRING_INDEX(name, \' \', 2), \' \', -1) ,NULL) as middle1_name ');
		$query->select(' If(  If(  length(name) - length(replace(name, \' \', \'\'))>1, SUBSTRING_INDEX(SUBSTRING_INDEX(name, \' \', 3), \' \', -1) ,NULL) = SUBSTRING_INDEX(SUBSTRING_INDEX(name, \' \', 4), \' \', -1), null, If(  length(name) - length(replace(name, \' \', \'\'))>1, SUBSTRING_INDEX(SUBSTRING_INDEX(name, \' \', 3), \' \', -1) ,NULL)) as middle2_name ');
		$query->select(' SUBSTRING_INDEX(SUBSTRING_INDEX(name, \' \', 3), \' \', -1) AS last_name ');
        $query->from(' #__users ');
        $query->where(' block = 0 ');
        $query->where(' If(  length(name) - length(replace(name, \' \', \'\'))>1, SUBSTRING_INDEX(SUBSTRING_INDEX(name, \' \', 2), \' \', -1) ,NULL) IS NOT NULL ');
		$db->setQuery($query);
		try {
		    $result = $db->loadObjectList();
		    Factory::getApplication()->enqueueMessage('Names divided into 3 . . . ');
		} catch (RuntimeException $e) {
		    Factory::getApplication()->enqueueMessage($e->getMessage(), 'danger');
		    return false;
		}
        return $result;
	}

	/**
	 * ***************  Check for old data structure where columns need to be added  ***************
	 */

	/**
	 * Check for a columns in table
	 */
	private function checkColumns()
	{
		$colExists = $this->checkColumnExists('#__gausers_profile_audit', 'post_update');
		if (empty($colExists)) {
            Factory::getApplication()->enqueueMessage(Text::_('Column post_update NOT Exists'), 'warning');
            $this->createColumn('#__gausers_profile_audit', 'post_update', 'user_id', 'text');
        }

        $colExists = $this->checkColumnExists('#__gausers_profile_audit', 'pre_update');
		if (empty($colExists)) {
            Factory::getApplication()->enqueueMessage(Text::_('Column pre_update NOT Exists'), 'warning');
            $this->createColumn('#__gausers_profile_audit', 'pre_update', 'user_id', 'text');
        }

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
					$app->enqueueMessage('Plugin ' . $pluginName . ' was installed successfully', 'success');
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
						$app->enqueueMessage('Plugin ' . $pluginName . ' was uninstalled successfully', 'success');
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
						$app->enqueueMessage('Module ' . $moduleName . ' was installed successfully', 'success');
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
							$app->enqueueMessage('Module ' . $moduleName . ' was uninstalled successfully', 'success');
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
	function loadToActionLogConfig($extension, $type, $key = 'id', $title = '', $tablename = '', $txtpref = '')
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
