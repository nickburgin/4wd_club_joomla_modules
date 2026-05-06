<?php

/**
 * @package    Com_Gacalevents
 * @author     Glenn Arkell <glenn@glennarkell.com.au>
 * @copyright  2021 Glenn Arkell
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
use \Joomla\CMS\MVC\Model\AdminModel;
use \Joomla\CMS\Component\ComponentHelper;
use \Joomla\CMS\Installer\InstallerScript;
use \Joomla\CMS\Installer\Adapter\InstallerAdapter;
use \Joomla\CMS\Installer\Adapter\ComponentAdapter;
use \Joomla\CMS\Installer\Adapter\ModuleAdapter;
use \Joomla\CMS\Installer\Adapter\PluginAdapter;
use \Joomla\CMS\Filter\OutputFilter;

/**
 * Sets up all the elements of the component
 *
 */
class com_gacaleventsInstallerScript extends InstallerScript
{
	/**
	 * The title of the component (printed on installation and uninstallation messages)
	 * @var string
	 */
	protected $extension = 'Calendar of Events';

	public $compName = 'gacalevents';

    public $mailTags = array("name","email","sitename","link_text","emailbody");
    public $mailTmplSuffixs = array("eventnew", "eventremind");

	/**
	 * The minimum Joomla! version required to install this extension
	 * @var   string
	 */
	protected $minimumJoomla = '5.0';

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
		echo '<p>' . Text::_('COM_'.STRTOUPPER($this->compName).'_INSTALL_TEXT') . '</p>';

		// Set a Dashboard Entry
		// @params string $dashboard and string $preset
		$this->addDashboardMenu($this->compName, $this->compName);
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

		$dashB = $this->checkDashboard($this->compName);
		if (!$dashB) {
			$this->addDashboardMenu($this->compName, $this->compName);
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

		$this->removeDashboard($this->compName);
	}

	/**
	 * @param   string $type   type
	 * @param   string $parent parent
	 * @return boolean
	 */
	public function postflight($type, $parent)
	{
		// $parent is the class calling this method
		echo '<p>' . Text::_('COM_'.STRTOUPPER($this->compName).'_POSTFLIGHT_'.STRTOUPPER($type).'_TEXT') . '</p>';

		if (STRTOUPPER($type) == 'INSTALL') {
			// do something

            // Load categories
			$cattitles = array(
					"General Event/Function"
					);
            $genCatID = $this->createCategory($cattitles, 'com_gacalevents.events');

			// create a departure point category
			$deptPt = array(
					"A Special Place"
					);
	        $deptptCatID = $this->createCategory($deptPt, 'com_gacalevents.deptpt');

		}

		if (STRTOUPPER($type) == 'UPDATE') {

			// remove js file and folder if they exist (stopped using this jin J5)
			$pathMediaJS = Path::clean( JPATH_SITE . '/media/com_'.$this->compName.'/js/' );
			$this->deleteFiles($pathMediaJS, 'form','.js');
			$this->deleteFolder($pathMediaJS);
			$pathMediaF = Path::clean( JPATH_SITE . '/media/com_'.$this->compName.'/fonts/' );
			$this->deleteFolder($pathMediaF);

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

	/* ***************************  Setup categories  ********************************* */
    /**
    * Function to create category records
    * @param array category titles
    * @param string category group or type
    * @return void
    */
    public function createCategory($cat_titles, $cat_ext)
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
                throw new Exception(500, $category->getError());
                return false;
            }
            // Now store the category
            if (!$category->store(true)) {
                throw new Exception(500, $category->getError());
                return false;
            }
	 	}
        // Build the path for our category
        $category->rebuildPath($category->id);
        echo '<p>' . Text::_('Categories created') . '</p>';
        return $category->id;
	}

	/**
	 * Get the version of the component
	 * @return version element of manifest
	 */
	public static function getComponentVersion($compName = 'gacalevents')
	{
		$extPath = JPATH_ADMINISTRATOR . '/components/com_'.$compName.'/'.$compName.'.xml';
		$componentXML = Installer::parseXMLInstallFile(Path::clean($extPath));

		return $componentXML['version'];
	}

	/**
	 * *********************  All the dashboard stuff  *******************************
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

	/* ***************************  Email Templates  ********************************* */

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
	 * *********************  All the clean up stuff  *******************************
	 */

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

}
