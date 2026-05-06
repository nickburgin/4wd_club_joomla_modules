<?php
/**
 * @version    4.3.3
 * @package    Com_Gabroadcast
 * @author     Glenn Arkell <glenn@glennarkell.com.au>
 * @copyright  Copyright (C) 2013. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\Filesystem\File;
use Joomla\Filesystem\Folder;
use Joomla\Filesystem\Path;
use Joomla\CMS\Mail\MailTemplate;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Installer\Installer;
use Joomla\CMS\Installer\InstallerScript;
use Joomla\CMS\Installer\Adapter\InstallerAdapter;
use Joomla\CMS\Installer\Adapter\ComponentAdapter;
use Joomla\CMS\Installer\Adapter\ModuleAdapter;
use Joomla\CMS\Installer\Adapter\PluginAdapter;
use Joomla\CMS\Filter\OutputFilter;

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

	public $mailTmplSuffixs = array("message");

	public $mailTags = array("sitename","link_text","subject","body", "name", "sender");

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

		// Set a Dashboard Entry
		// @params string $dashboard and string $preset
		$this->addDashboardMenu($this->comp_name, $this->comp_name);

		// setup id for the template
        foreach ($this->mailTmplSuffixs as $tmpl) {
            $template_id = 'com_'.$this->comp_name.'.'.$tmpl;
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
		echo '<p>' . Text::_('COM_' . STRTOUPPER($this->comp_name) . '_UNINSTALL_TEXT') . '</p>';

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

			//cleanup old language files
 			$pathLangS = Path::clean( JPATH_SITE . '/language/en-GB/' );
			$this->deleteFiles($pathLangS, 'en-GB.com_'.$this->comp_name,'.ini');
 			$pathLangA = Path::clean( JPATH_ADMINISTRATOR . '/language/en-GB/' );
			$this->deleteFiles($pathLangA, 'en-GB.com_'.$this->comp_name,'.ini');
			$this->deleteFiles($pathLangA, 'en-GB.com_'.$this->comp_name,'.sys.ini');

    		// setup id for the template
            foreach ($this->mailTmplSuffixs as $tmpl) {
                $template_id = 'com_'.$this->comp_name.'.'.$tmpl;
                $tmplExists = $this->checkMailTemplates($template_id);
                if (!$tmplExists) {
                    $this->addMailTemplate($tmpl, $this->mailTags);
                }
            }
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
                $tmpl->extension = 'com_'.$this->comp_name;
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
		Factory::getApplication()->enqueueMessage(Text::_('COM_'.STRTOUPPER($this->comp_name ?? '').'_EMAILTMPL_REMOVE_SUCCESS', 'notice'));
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
        	'com_'.$this->comp_name.'.'.$template_id,
        	'COM_'.STRTOUPPER($this->comp_name).'_EMAILTMPL_'.STRTOUPPER($template_id).'_SUBJECT',
        	'COM_'.STRTOUPPER($this->comp_name).'_EMAILTMPL_'.STRTOUPPER($template_id).'_BODY',
        	$tags,
        	'COM_'.STRTOUPPER($this->comp_name).'_EMAILTMPL_'.STRTOUPPER($template_id).'_HTMLBODY'
        );

		Factory::getApplication()->enqueueMessage('Email Template Record Created . . . '.$template_id);

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
