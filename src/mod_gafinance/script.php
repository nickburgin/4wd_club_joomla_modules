<?php
/*
 * ------------------------------------------------------------------------
 * @version     5.3
 * @copyright   Copyright (C) 2021. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * Author:      Glenn Arkell
 * Website:     https://www.glennarkell.com.au
 * ------------------------------------------------------------------------
*/

defined('_JEXEC') or die();

define('MODIFIED', 1);
define('NOT_MODIFIED', 2);

use \Joomla\CMS\Factory;
use \Joomla\CMS\Language\Text;
use \Joomla\Filesystem\File;
use \Joomla\Filesystem\Folder;
use \Joomla\Filesystem\Path;
use \Joomla\CMS\Installer\Installer;
use \Joomla\CMS\Installer\InstallerScript;
use \Joomla\CMS\Filter\OutputFilter;

/**
 * Updates the structure of the component
 */
class mod_gafinanceInstallerScript extends InstallerScript
{
	private $app;

	public $compName = 'gafinance';

	/**
	 * The minimum Joomla! version required to install this extension
	 * @var   string
	 */
	protected $minimumJoomla = '4.4';

	/**
	 *  Constructor
	 */
	public function __construct()
	{
		$this->app = Factory::getApplication();

        $this->gTours = array(
           'MOD_'.STRTOUPPER($this->compName).'_GUIDEDTOUR_WELCOME_LBL'=>'MOD_'.STRTOUPPER($this->compName).'_GUIDEDTOUR_WELCOME_DESC',
           'MOD_'.STRTOUPPER($this->compName).'_GUIDEDTOUR_CONFIG_LBL'=>'MOD_'.STRTOUPPER($this->compName).'_GUIDEDTOUR_CONFIG_DESC'
           );
	}

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
		echo '<p>' . Text::_('MOD_GAFINANCE_PREFLIGHT_TEXT') . '</p>';

        $path = Path::clean( JPATH_SITE . '/modules/mod_gafinance/' );
        $this->deleteFiles($path, 'helper', '.php');
        $this->deleteFiles($path, 'mod_gafinance', '.php');
        $this->deleteFiles($path, 'tmpl/blank', '.php');

	}

	/**
	 * method to run after an install/update/uninstall method
	 * @return void
	 */
	public function postflight($type, $parent)
	{
        // check if Guided Tours is ok for install
//         if (JVERSION <= $this->minimumJoomla) {
//             // don't install guided tours
//             Factory::getApplication()->enqueueMessage(Text::sprintf('GA_INSTALL_NOGT',JVERSION), 'message');
//         } else {
//             // reinstall parameter to be passed
//             $this->checkGuidedTours(false);
//         }
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

	/**
	 * *********************  Guided Tours Setup  *******************************
	 */

	/**
	 * Check for Guided Tours
	 * @param   boolean  true if reinstall is to be performed
	 */
	public function checkGuidedTours($reinstall)
	{
        // check for guided tours and create if required
        if (isset($this->gTours) && is_array($this->gTours) && !empty($this->gTours)) {
            foreach ($this->gTours as $gtUid => $desc) {
                $tourUID =  STRTOLOWER('mod_'.$this->compName.'-'.Text::_($gtUid));
                $tourExists = $this->checkTourExists($tourUID);

                if ($tourExists && $reinstall) {
                    // remove old GTs because changes made and we need to remove old steps
                    $this->removeTour($tourExists->id);
                    $tourExists = false;
                }

                if (!$tourExists) {
                    // set the start url and create tour
                    if ($gtUid == 'MOD_'.STRTOUPPER($this->compName).'_GUIDEDTOUR_WELCOME_LBL') {
                        $url = 'administrator/index.php';
                    } elseif ($gtUid == 'MOD_'.STRTOUPPER($this->compName).'_GUIDEDTOUR_CONFIG_LBL') {
                        $url = 'administrator/index.php?option=com_modules&view=modules&client_id=0';
                    } else {
                        continue;
                    }
                    $gtID = $this->createGuidedTour($gtUid, $desc, $url);
                    $this->app->enqueueMessage(Text::sprintf('MOD_'.STRTOUPPER($this->compName).'_GUIDEDTOUR_LOADED', Text::_($gtUid), $gtID), 'notice');
                    // create steps array
                    $recs = $this->setupGuidedTourSteps($gtUid);
                    // create tour steps
                    $this->createGuidedTourSteps($gtID, $recs);
                }
            }
        }

	}

	/**
	 * Check if a Guided Tour entry exists
	 * @param   string  $tourUid for the uid reference
	 * @return boolean or object
	 */
	public function checkTourExists($tourUid)
	{
        $result = false;
		$db = Factory::getContainer()->get('DatabaseDriver');
	    $db->setQuery(' SELECT * FROM #__guidedtours WHERE extensions = '.$db->Quote('["mod_'.$this->compName.'"]') . ' AND uid = '.$db->Quote('mod_'.$tourUid) );
		try {
		    $result = $db->loadObject();
		} catch (RuntimeException $e) {
		    $this->app->enqueueMessage($e->getMessage(), 'danger');
		}

		return $result;
	}

	/**
	 * Create a Guided Tour record
	 * @param   string  $tourName for the uid reference and title
	 * @param   string  $url to set where the tour starts from
	 * @return boolean or record id
	 */
	public function createGuidedTour($tourName, $desc, $url)
	{
        $today = Factory::getDate()->toSql();
        $userId = Factory::getApplication()->getIdentity()->id;
        $db = Factory::getContainer()->get('DatabaseDriver');
        $gtour = new \stdClass();
        $gtour->title = $tourName;
        $gtour->uid = 'mod_'.$this->compName.'-'.STRTOLOWER(Text::_($tourName));
        $gtour->description = $desc;
        $gtour->extensions = '["mod_'.$this->compName.'"]';
        $gtour->url = $url;
        $gtour->created = $today;
        $gtour->created_by = $userId;
        $gtour->modified = $today;
        $gtour->modified_by = $userId;
        $gtour->language = '*';
        $gtour->published = 1;
        $gtour->note = '';
        $gtour->access = 1;
        if ($url == 'administrator/index.php') {
            $gtour->autostart = 1;
        }
		try {
		    $result = $db->insertObject('#__guidedtours', $gtour);
            return $db->insertid();
		} catch (RuntimeException $e) {
		    $this->app->enqueueMessage($e->getMessage(), 'warning');
		    return false;
		}

	}

	/**
	 * Create a Guided Tour record
	 */
	public function createGuidedTourSteps($id, $recs)
	{
        $today = Factory::getDate()->toSQL();
        $userId = Factory::getApplication()->getIdentity()->id;
        foreach ($recs as $data) {
            $db = Factory::getContainer()->get('DatabaseDriver');
            $gtstep = new \stdClass();
            $gtstep->tour_id = $id;
            $gtstep->title = $data['title'];
            $gtstep->published = 1;
            $gtstep->description = $data['desc'];
            $gtstep->position = $data['position'];
            $gtstep->target = $data['target'];
            $gtstep->type = $data['type'];
            $gtstep->interactive_type = $data['intertype'];
            $gtstep->url = $data['url'];
            $gtstep->language = '*';
            $gtstep->created = $today;
            $gtstep->created_by = $userId;
            $gtstep->modified = $today;
            $gtstep->modified_by = $userId;
    		try {
    		    $result = $db->insertObject('#__guidedtour_steps', $gtstep);
    		} catch (RuntimeException $e) {
    		    $this->app->enqueueMessage($e->getMessage(), 'warning');
    		}
		}
        return true;

	}

	/**
	 * Removes the guided tour entries
	 * @param int $id The guided tour ID reference
	 * @return  void
	 */
	public function removeTour($id)
	{
		$db = Factory::getContainer()->get('DatabaseDriver');
	    $db->setQuery(' DELETE FROM #__guidedtours WHERE id = '. (int) $id );
		try {
		    $db->execute();
			$this->app->enqueueMessage(Text::_('MOD_'.STRTOUPPER($this->compName).'_REMOVE_GUIDEDTOUR_SUCCESS'), 'notice');
		} catch (RuntimeException $e) {
		    $this->app->enqueueMessage($e->getMessage(), 'danger');
		}

		$db1 = Factory::getContainer()->get('DatabaseDriver');
	    $db1->setQuery(' DELETE FROM #__guidedtour_steps WHERE tour_id = '. (int) $id );
		try {
		    $db1->execute();
			$this->app->enqueueMessage(Text::_('MOD_'.STRTOUPPER($this->compName).'_REMOVE_GUIDEDTOURSTEPS_SUCCESS'), 'notice');
		} catch (RuntimeException $e1) {
		    $this->app->enqueueMessage($e1->getMessage(), 'danger');
		}
	}

	/**
	 * Set up the step entries
	 * @param   integer  $ref to identify which tour the steps belong to
	 * types - 0 = Next, 1 = Redirect, 2 = Interactive
	 * interactive types - 1 = Form Submit, 2 = Text Field, 4 = Button, 3 = Other
	 * @return boolean or array
	 */
	public function setupGuidedTourSteps($ref)
	{
        $returnURL = 'administrator/index.php?option=com_cpanel&view=cpanel&dashboard='.$this->compName;
        $recs = array();

        if ($ref == 'MOD_'.STRTOUPPER($this->compName).'_GUIDEDTOUR_WELCOME_LBL') {
            $recs = $this->setupStdWelcomeSteps();
        }

        if ($ref == 'MOD_'.STRTOUPPER($this->compName).'_GUIDEDTOUR_CONFIG_LBL') {
            // from the list screen, redirect to the dasboard
            $recs[] = $this->setupStdRedirectStep('', $returnURL);
            // link to the dasboard entry to see the list screen
            $recs[] = $this->setupStdListlinkStep('config');

            $recs[] = $this->setupStdFieldStep('bottom', 'accnt_id', 'config');
            // module settings - right side
            $recs[] = $this->setupStdFieldStep('left', 'showtitle', 'config');
            $recs[] = $this->setupStdFieldStep('left', 'position', 'config');
            $recs[] = $this->setupStdFieldStep('left', 'published', 'config');
            $recs[] = $this->setupStdFieldStep('left', 'access', 'config');
            $recs[] = $this->setupStdFieldStep('left', 'position', 'config');

            // next tab
            $recs[] = $this->setupStdTabStep('assignment');
            $recs[] = $this->setupStdFieldStep('bottom', 'assignment', 'config');
            $recs[] = $this->setupStdFieldStep('bottom', 'menuselect', 'config');

            // next tab
            $recs[] = $this->setupStdTabStep('attrib-advanced');
            $recs[] = $this->setupStdFieldStep('bottom', 'params_moduleclass_sfx', 'config');

            // next tab
            $recs[] = $this->setupStdTabStep('permissions');
            $recs[] = $this->setupStdFieldStep('bottom', 'create', 'config');

            $recs[] = $this->setupStdSaveCloseStep($returnURL);
            $recs[] = $this->setupStdCongratsStep($returnURL);

        }

		return $recs;
	}

	/**
	 * Setup standard guided tour steps
	 * @param   string $return URL
	 * @param   string $target
	 * @return  array $rec
	 */
	public function setupStdWelcomeSteps($target = '', $return = '')
	{
        $recs[] = array(
              'title'=>'MOD_'.STRTOUPPER($this->compName).'_GUIDEDTOUR_WELCOME_MENU_LBL',
              'desc'=>'MOD_'.STRTOUPPER($this->compName).'_GUIDEDTOUR_WELCOME_MENU_DESC',
              'position'=>'right',
              'target'=>'#sidebarmenu',
              'type'=>0,
              'intertype'=>2,
              'url'=>''
              );
        $recs[] = array(
              'title'=>'MOD_'.STRTOUPPER($this->compName).'_GUIDEDTOUR_WELCOME_DASHBOARD_LBL',
              'desc'=>'MOD_'.STRTOUPPER($this->compName).'_GUIDEDTOUR_WELCOME_DASHBOARD_DESC',
              'position'=>'right',
              'target'=>'.menu-dashboard a[href*="dashboard='.$this->compName.'"]',
              'type'=>2,
              'intertype'=>4,
              'url'=>''
              );
        $recs[] = array(
              'title'=>'MOD_'.STRTOUPPER($this->compName).'_GUIDEDTOUR_CONGRATS_LBL',
              'desc'=>'MOD_'.STRTOUPPER($this->compName).'_GUIDEDTOUR_CONGRATS_DESC',
              'position'=>'bottom',
              'target'=>'',
              'type'=>0,
              'intertype'=>1,
              'url'=>'#cpanel-modules a[href*="view='.$this->compName.'"]'
              );

		return $recs;
	}

	/**
	 * Setup standard guided tour steps
	 * @param   string $return URL
	 * @param   string $target
	 * @return  array $rec
	 */
	public function setupStdRedirectStep($target = '', $return = '')
	{
        $rec = array(
              'title'=>'COM_'.STRTOUPPER($this->compName).'_GUIDEDTOUR_REDIRECT_LBL',
              'desc'=>'COM_'.STRTOUPPER($this->compName).'_GUIDEDTOUR_REDIRECT_DESC',
              'position'=>'top',
              'target'=>$target,
              'type'=>1,
              'intertype'=>2,
              'url'=>$return
              );

		return $rec;
	}

	/**
	 * Setup standard guided tour steps
	 * @param   string $return URL
	 * @param   string $list (singular of the list plurals)
	 * @return  array $rec
	 */
	public function setupStdListlinkStep($list = '')
	{
        if ($list == 'config') {

            $target = 'div.cpanel-modules.cpanel-'.$this->compName.' ul.list-group li.list-group-item a[href*="option=com_modules&view=modules&client_id=0"]';
            $url = 'administrator/index.php?option=com_config&view=component&component=com_'.$this->compName.'&path=&return=';
        } else {
            $target = 'div.cpanel-modules.cpanel-'.$this->compName.' ul.list-group li.list-group-item a[href*="option=com_'.$this->compName.'&view='.$list.'s"]';
            $url = 'administrator/index.php?option=com_'.$this->compName.'&view='.$list.'s';
        }
        // link to the dasboard entry to see the list screen
        $rec = array(
              'title'=>'MOD_'.STRTOUPPER($this->compName).'_GUIDEDTOUR_'.STRTOUPPER($list).'_LIST_LBL',
              'desc'=>'MOD_'.STRTOUPPER($this->compName).'_GUIDEDTOUR_'.STRTOUPPER($list).'_LIST_DESC',
              'position'=>'bottom',
              'target'=>$target,
              'type'=>2,
              'intertype'=>1,
              'url'=>$url
              );

		return $rec;
	}

	/**
	 * Setup standard guided tour steps
	 * @param   string $return URL
	 * @param   string $list (singular of the list plurals)
	 * @return  array $rec
	 */
	public function setupStdNewStep($list = '')
	{
        $rec = array(
              'title'=>'MOD_'.STRTOUPPER($this->compName).'_GUIDEDTOUR_'.STRTOUPPER($list).'_NEW_LBL',
              'desc'=>'MOD_'.STRTOUPPER($this->compName).'_GUIDEDTOUR_'.STRTOUPPER($list).'_NEW_DESC',
              'position'=>'bottom',
              'target'=>'.button-new',
              'type'=>2,
              'intertype'=>1,
              'url'=>''
              );

		return $rec;
	}

	/**
	 * Setup standard guided tour steps
	 * @param   string $return URL
	 * @param   string $list (singular of the list plurals)
	 * @return  array $rec
	 */
	public function setupStdFieldStep($position = 'bottom', $field = '', $list = '')
	{
        if ($field == 'create') {
            $target = '';
        } else {
            $target = '#jform_'.$field;
        }
        $rec = array(
              'title'=>'MOD_'.STRTOUPPER($this->compName).'_GUIDEDTOUR_'.STRTOUPPER($list).'_'.STRTOUPPER($field).'_LBL',
              'desc'=>'MOD_'.STRTOUPPER($this->compName).'_GUIDEDTOUR_'.STRTOUPPER($list).'_'.STRTOUPPER($field).'_DESC',
              'position'=>$position,
              'target'=>$target,
              'type'=>2,
              'intertype'=>2,
              'url'=>''
              );

		return $rec;
	}

	/**
	 * Setup standard guided tour steps
	 * @param   string $return URL
	 * @param   string $list
	 * @return  array $rec
	 */
	public function setupStdTabStep($tab = '')
	{
        $rec = array(
              'title'=>'MOD_'.STRTOUPPER($this->compName).'_GUIDEDTOUR_TAB_LBL',
              'desc'=>'MOD_'.STRTOUPPER($this->compName).'_GUIDEDTOUR_TAB_DESC',
              'position'=>'bottom',
              'target'=>'button[aria-controls='.$tab.']',
              'type'=>2,
              'intertype'=>4,
              'url'=>''
              );

		return $rec;
	}

	/**
	 * Setup standard guided tour steps
	 * @param   string $return URL
	 * @return  array $rec
	 */
	public function setupStdSaveCloseStep($return = '')
	{
        $rec = array(
              'title'=>'MOD_'.STRTOUPPER($this->compName).'_GUIDEDTOUR_SAVECLOSE_LBL',
              'desc'=>'MOD_'.STRTOUPPER($this->compName).'_GUIDEDTOUR_SAVECLOSE_DESC',
              'position'=>'bottom',
              'target'=>'#toolbar #toolbar-save button.button-save',
              'type'=>2,
              'intertype'=>1,
              'url'=>$return
              );

		return $rec;
	}

	/**
	 * Setup standard guided tour steps
	 * @param   string $return URL
	 * @return  array $rec
	 */
	public function setupStdCongratsStep($return = '')
	{
        $rec = array(
              'title'=>'MOD_'.STRTOUPPER($this->compName).'_GUIDEDTOUR_CONGRATS_LBL',
              'desc'=>'MOD_'.STRTOUPPER($this->compName).'_GUIDEDTOUR_CONGRATS_DESC',
              'position'=>'bottom',
              'target'=>'',
              'type'=>1,
              'intertype'=>2,
              'url'=>$return
              );
		return $rec;
	}

}
