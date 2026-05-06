<?php

/**
 * @version     5.3.0
 * @package    pkg_gatripsys
 * @subpackage com_gatripsys
 * @author     Glenn Arkell <glenn@glennarkell.com.au>
 * @copyright  2021 Glenn Arkell
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

\defined('_JEXEC') or die();

use Joomla\CMS\Factory;
use Joomla\Filesystem\File;
use Joomla\Filesystem\Folder;
use Joomla\Filesystem\Path;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Table\Table;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Installer\Installer;
use Joomla\CMS\Installer\InstallerScript;
use Joomla\CMS\Mail\MailTemplate;
use Joomla\CMS\Filter\OutputFilter;

/**
 * Updates the database structure of the component
 */
class com_gatripsysInstallerScript extends InstallerScript
{
	/**
	 * The title of the component (printed on installation and uninstallation messages)
	 * @var string
	 */
	protected $extension = 'Trip Management System';

	public $compName = 'gatripsys';
	public $version = '5.3.0';
	public $dbName = 'j4idlers_db';

	public $mainView = 'trips';

    public $mailTags = array("coord_name","leader_name","member_name","booking_status","sitename","link_text","trip_title","dept_date");
    public $mailTmplSuffixs = array("tripnewtc","tripnewad","tripnew","tripaprvtl","tripaprv","tripcan","tripcantc","tripclose","tripfinal","booknew","bookaprv","bookcan","trippend");

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
        $this->version = $this->getComponentVersion();
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
	 * Method to install the component
	 * @param   mixed $parent Object who called this method.
	 * @return void
	 * @since 0.2b
	 */
	public function install($parent)
	{
		// $parent is the class calling this method
		echo '<p>' . Text::_('COM_GATRIPSYS_INSTALL_TEXT') . '</p>';
		
		// create folders
		$this->createFolders('images', 'trips');
		$this->createFolders('images', 'trips/trip_plans');
		$this->createFolders('images', 'trips/invoices');
		$this->createFolders('images', 'trips/trip_images');
		$this->createFolders('images', 'trips/trip_incidents');

		$this->setupMainCategories();

		$categories = array(
				"Tents / Off road Camper trailers / Off road Caravans only",
				"Tents / Off road Camper trailers Only",
				"Tents and High Clearance Vehicles only",
				"No Restrictions"
				);
		$this->createCategories($categories, '.suitedfor', '', '');
		$categories = array(
				"Short base camp trip",
				"Long base camp trip",
				"Short Touring Trip",
				"Long Touring Trip",
				"Member Training",
				"Non Vehicle Tour",
				"Day Trip",
				"Community Emergency",
				"Community Service",
				"Administration",
				"Other"
				);
		$this->createCategories($categories, '.triptype', '', '');

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
	 * Method to update the component
	 * @param   mixed $parent Object who called this method.
	 * @return void
	 */
	public function update($parent)
	{
		// $parent is the class calling this method
		echo '<p>' . Text::_('COM_GATRIPSYS_UPDATE_TEXT') . '</p>';

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
		echo '<p>' . Text::_('COM_GATRIPSYS_UNINSTALL_TEXT') . '</p>';

		$dashB = $this->checkDashboard($this->compName);
		if ($dashB) {
			$this->removeDashboard('cpanel-'.$this->compName);
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

			// update the component name in MailTemplates tables until fix in core
    		// update mail template records until core is updated
            foreach ($this->mailTmplSuffixs as $tmpl) {
                $template_id = 'com_'.$this->compName.'.'.$tmpl;
                $tmplupds = $this->updateMailTemplates($template_id);
                if ($tmplupds) {
                    Factory::getApplication()->enqueueMessage('Mail Templates Updated until core updated - '.$template_id, 'notice');
                }
            }

		}

		if (STRTOUPPER($type) == 'UPDATE') {
			// do something
 			$pathAdmin = Path::clean( JPATH_ADMINISTRATOR . '/components/com_gatripsys/sql/updates/mysql/' );
			$this->deleteFiles($pathAdmin, '4.2.2','.sql');

// 			$this->updateDateFields('#__gatripsys_trips', 'checked_out_time');
    		$this->createFolders('images', 'trips/trip_incidents');

            // check if Guided Tours is ok for install
            if (JVERSION <= '4.4.3') {
                // don't install guided tours
                Factory::getApplication()->enqueueMessage(Text::sprintf('GA_INSTALL_NOGT',JVERSION), 'message');
            } else {
                // reinstall parameter to be passed
                $this->checkGuidedTours(false);
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
                $tourUID =  STRTOLOWER($this->compName.'-'.Text::_($gtUid));
                $tourExists = $this->checkTourExists($tourUID);

                if ($tourExists && $reinstall) {
                    // remove old GTs because changes made and we need to remove old steps
                    $this->removeTour($tourExists->id);
                    $tourExists = false;
                }

                if (!$tourExists) {
                    // set the start url and create tour
                    if ($gtUid == 'COM_'.STRTOUPPER($this->compName).'_GUIDEDTOUR_TRIP_LBL') {
                        $url = 'index.php?option=com_'.$this->compName.'&view='.$this->mainView;
                    } elseif ($gtUid == 'COM_'.STRTOUPPER($this->compName).'_GUIDEDTOUR_CONFIG_LBL') {
                        $url = 'administrator/index.php?option=com_cpanel&view=cpanel&dashboard='.$this->compName;
                    } else {
                        continue;
                    }
                    $gtID = $this->createGuidedTour($gtUid, $desc, $url);
                    $this->app->enqueueMessage(Text::sprintf('COM_'.STRTOUPPER($this->compName).'_GUIDEDTOUR_LOADED', Text::_($gtUid), $gtID), 'notice');
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
	    $db->setQuery(' SELECT * FROM #__guidedtours WHERE extensions = '.$db->Quote('["com_'.$this->compName.'"]') . ' AND uid = '.$db->Quote($tourUid) );
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
        $gtour->uid = $this->compName.'-'.STRTOLOWER(Text::_($tourName));
        $gtour->description = $desc;
        $gtour->extensions = '["com_'.$this->compName.'"]';
        $gtour->url = $url;
        $gtour->created = $today;
        $gtour->created_by = $userId;
        $gtour->modified = $today;
        $gtour->modified_by = $userId;
        $gtour->language = '*';
        $gtour->published = 1;
        $gtour->note = '';
        $gtour->access = 1;
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
			$this->app->enqueueMessage(Text::_('COM_'.STRTOUPPER($this->compName).'_REMOVE_GUIDEDTOUR_SUCCESS'), 'notice');
		} catch (RuntimeException $e) {
		    $this->app->enqueueMessage($e->getMessage(), 'danger');
		}

		$db1 = Factory::getContainer()->get('DatabaseDriver');
	    $db1->setQuery(' DELETE FROM #__guidedtour_steps WHERE tour_id = '. (int) $id );
		try {
		    $db1->execute();
			$this->app->enqueueMessage(Text::_('COM_'.STRTOUPPER($this->compName).'_REMOVE_GUIDEDTOURSTEPS_SUCCESS'), 'notice');
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
        $recs = array();

        if ($ref == 'COM_'.STRTOUPPER($this->compName).'_GUIDEDTOUR_TRIP_LBL') {
            $returnURL = 'administrator/index.php?option=com_'.$this->compName.'&view='.$this->mainView;
            $recs[] = array(
                  'title'=>'COM_'.STRTOUPPER($this->compName).'_GUIDEDTOUR_TRIP_NEW_LBL',
                  'desc'=>'COM_'.STRTOUPPER($this->compName).'_GUIDEDTOUR_TRIP_NEW_DESC',
                  'position'=>'bottom',
                  'target'=>'.button-new',
                  'type'=>2,
                  'intertype'=>1,
                  'url'=>''
                  );
            $recs[] = array(
                  'title'=>'COM_'.STRTOUPPER($this->compName).'_GUIDEDTOUR_TRIP_TITLE_LBL',
                  'desc'=>'COM_'.STRTOUPPER($this->compName).'_GUIDEDTOUR_TRIP_TITLE_DESC',
                  'position'=>'bottom',
                  'target'=>'#jform_title',
                  'type'=>2,
                  'intertype'=>2,
                  'url'=>''
                  );
            $recs[] = array(
                  'title'=>'COM_'.STRTOUPPER($this->compName).'_GUIDEDTOUR_TRIP_RATING_LBL',
                  'desc'=>'COM_'.STRTOUPPER($this->compName).'_GUIDEDTOUR_TRIP_RATING_DESC',
                  'position'=>'top',
                  'target'=>'#jform_rating',
                  'type'=>2,
                  'intertype'=>2,
                  'url'=>''
                  );
            $recs[] = array(
                  'title'=>'COM_'.STRTOUPPER($this->compName).'_GUIDEDTOUR_TRIP_TRIP_TYPE_LBL',
                  'desc'=>'COM_'.STRTOUPPER($this->compName).'_GUIDEDTOUR_TRIP_TRIP_TYPE_DESC',
                  'position'=>'top',
                  'target'=>'#jform_trip_type',
                  'type'=>2,
                  'intertype'=>2,
                  'url'=>''
                  );
            $recs[] = array(
                  'title'=>'COM_'.STRTOUPPER($this->compName).'_GUIDEDTOUR_TRIP_SUITED_FOR_LBL',
                  'desc'=>'COM_'.STRTOUPPER($this->compName).'_GUIDEDTOUR_TRIP_SUITED_FOR_DESC',
                  'position'=>'top',
                  'target'=>'#jform_suited_for',
                  'type'=>2,
                  'intertype'=>2,
                  'url'=>''
                  );
            $recs[] = array(
                  'title'=>'COM_'.STRTOUPPER($this->compName).'_GUIDEDTOUR_TRIP_LEADER_LBL',
                  'desc'=>'COM_'.STRTOUPPER($this->compName).'_GUIDEDTOUR_TRIP_LEADER_DESC',
                  'position'=>'top',
                  'target'=>'joomla-field-fancy-select .choices #jform_leader',
                  'type'=>2,
                  'intertype'=>2,
                  'url'=>''
                  );
            $recs[] = array(
                  'title'=>'COM_'.STRTOUPPER($this->compName).'_GUIDEDTOUR_TRIP_TRIP_TEC_LBL',
                  'desc'=>'COM_'.STRTOUPPER($this->compName).'_GUIDEDTOUR_TRIP_TRIP_TEC_DESC',
                  'position'=>'top',
                  'target'=>'joomla-field-fancy-select .choices #jform_trip_tec',
                  'type'=>2,
                  'intertype'=>2,
                  'url'=>''
                  );
            $recs[] = array(
                  'title'=>'COM_'.STRTOUPPER($this->compName).'_GUIDEDTOUR_TRIP_MIN_NO_LBL',
                  'desc'=>'COM_'.STRTOUPPER($this->compName).'_GUIDEDTOUR_TRIP_MIN_NO_DESC',
                  'position'=>'bottom',
                  'target'=>'#jform_min_no',
                  'type'=>2,
                  'intertype'=>2,
                  'url'=>''
                  );
            $recs[] = array(
                  'title'=>'COM_'.STRTOUPPER($this->compName).'_GUIDEDTOUR_TRIP_MAX_NO_LBL',
                  'desc'=>'COM_'.STRTOUPPER($this->compName).'_GUIDEDTOUR_TRIP_MAX_NO_DESC',
                  'position'=>'top',
                  'target'=>'#jform_max_no',
                  'type'=>2,
                  'intertype'=>2,
                  'url'=>''
                  );
            $recs[] = array(
                  'title'=>'COM_'.STRTOUPPER($this->compName).'_GUIDEDTOUR_TRIP_MAX_PEOPLE_LBL',
                  'desc'=>'COM_'.STRTOUPPER($this->compName).'_GUIDEDTOUR_TRIP_MAX_PEOPLE_DESC',
                  'position'=>'top',
                  'target'=>'#jform_max_people',
                  'type'=>2,
                  'intertype'=>2,
                  'url'=>''
                  );
            $recs[] = array(
                  'title'=>'COM_'.STRTOUPPER($this->compName).'_GUIDEDTOUR_TAB_LBL',
                  'desc'=>'COM_'.STRTOUPPER($this->compName).'_GUIDEDTOUR_TAB_DESC',
                  'position'=>'bottom',
                  'target'=>'button[aria-controls=where]',
                  'type'=>2,
                  'intertype'=>4,
                  'url'=>''
                  );
            $recs[] = array(
                  'title'=>'COM_'.STRTOUPPER($this->compName).'_GUIDEDTOUR_TRIP_WHERE_GO_LBL',
                  'desc'=>'COM_'.STRTOUPPER($this->compName).'_GUIDEDTOUR_TRIP_WHERE_GO_DESC',
                  'position'=>'bottom',
                  'target'=>'#jform_where_go,#jform_where_go_ifr',
                  'type'=>2,
                  'intertype'=>3,
                  'url'=>''
                  );
            $recs[] = array(
                  'title'=>'COM_'.STRTOUPPER($this->compName).'_GUIDEDTOUR_TRIP_EXPIRY_DATE_LBL',
                  'desc'=>'COM_'.STRTOUPPER($this->compName).'_GUIDEDTOUR_TRIP_EXPIRY_DATE_DESC',
                  'position'=>'top',
                  'target'=>'#jform_expiry_date',
                  'type'=>2,
                  'intertype'=>2,
                  'url'=>''
                  );
            $recs[] = array(
                  'title'=>'COM_'.STRTOUPPER($this->compName).'_GUIDEDTOUR_TRIP_REQSEC_LBL',
                  'desc'=>'COM_'.STRTOUPPER($this->compName).'_GUIDEDTOUR_TRIP_REQSEC_DESC',
                  'position'=>'bottom',
                  'target'=>'#jform_reqsec',
                  'type'=>2,
                  'intertype'=>2,
                  'url'=>''
                  );
            $recs[] = array(
                  'title'=>'COM_'.STRTOUPPER($this->compName).'_GUIDEDTOUR_TAB_LBL',
                  'desc'=>'COM_'.STRTOUPPER($this->compName).'_GUIDEDTOUR_TAB_DESC',
                  'position'=>'bottom',
                  'target'=>'button[aria-controls=extrainfo]',
                  'type'=>2,
                  'intertype'=>4,
                  'url'=>''
                  );
            $recs[] = array(
                  'title'=>'COM_'.STRTOUPPER($this->compName).'_GUIDEDTOUR_TRIP_FIELD_ID_LBL',
                  'desc'=>'COM_'.STRTOUPPER($this->compName).'_GUIDEDTOUR_TRIP_FIELD_ID_DESC',
                  'position'=>'top',
                  'target'=>'#jform_field_id',
                  'type'=>2,
                  'intertype'=>2,
                  'url'=>''
                  );
            $recs[] = array(
                  'title'=>'COM_'.STRTOUPPER($this->compName).'_GUIDEDTOUR_SAVECLOSE_LBL',
                  'desc'=>'COM_'.STRTOUPPER($this->compName).'_GUIDEDTOUR_SAVECLOSE_DESC',
                  'position'=>'bottom',
                  'target'=>'#toolbar-save button.button-save',
                  'type'=>2,
                  'intertype'=>1,
                  'url'=>$returnURL
                  );
            $recs[] = array(
                  'title'=>'COM_'.STRTOUPPER($this->compName).'_GUIDEDTOUR_CONGRATS_LBL',
                  'desc'=>'COM_'.STRTOUPPER($this->compName).'_GUIDEDTOUR_CONGRATS_DESC',
                  'position'=>'bottom',
                  'target'=>'',
                  'type'=>0,
                  'intertype'=>1,
                  'url'=>$returnURL
                  );
        }

        if ($ref == 'COM_'.STRTOUPPER($this->compName).'_GUIDEDTOUR_CONFIG_LBL') {

            $returnURL = 'administrator/index.php?option=com_'.$this->compName.'&view='.$this->mainView;
            $recs[] = array(
                  'title'=>'COM_'.STRTOUPPER($this->compName).'_GUIDEDTOUR_CONFIG_SETTINGS_LBL',
                  'desc'=>'COM_'.STRTOUPPER($this->compName).'_GUIDEDTOUR_CONFIG_SETTINGS_DESC',
                  'position'=>'bottom',
                  'target'=>'div.cpanel-modules.cpanel-'.$this->compName.' ul.list-group li.list-group-item a.flex-grow-1',
                  'type'=>2,
                  'intertype'=>1,
                  'url'=>'administrator/index.php?option=com_config&view=component&component=com_'.$this->compName.'&path=&return='
                  );
            $recs[] = array(
                  'title'=>'COM_'.STRTOUPPER($this->compName).'_GUIDEDTOUR_TAB_LBL',
                  'desc'=>'COM_'.STRTOUPPER($this->compName).'_GUIDEDTOUR_TAB_DESC',
                  'position'=>'bottom',
                  'target'=>'button[aria-controls=integration]',
                  'type'=>2,
                  'intertype'=>4,
                  'url'=>''
                  );
            $recs[] = array(
                  'title'=>'COM_'.STRTOUPPER($this->compName).'_GUIDEDTOUR_CONFIG_SENDEMAIL_LBL',
                  'desc'=>'COM_'.STRTOUPPER($this->compName).'_GUIDEDTOUR_CONFIG_SENDEMAIL_DESC',
                  'position'=>'bottom',
                  'target'=>'#jform_send_email',
                  'type'=>2,
                  'intertype'=>2,
                  'url'=>''
                  );
            $recs[] = array(
                  'title'=>'COM_'.STRTOUPPER($this->compName).'_GUIDEDTOUR_CONFIG_TMPLEMAIL_LBL',
                  'desc'=>'COM_'.STRTOUPPER($this->compName).'_GUIDEDTOUR_CONFIG_TMPLEMAIL_DESC',
                  'position'=>'bottom',
                  'target'=>'#jform_tmpl_email',
                  'type'=>2,
                  'intertype'=>2,
                  'url'=>''
                  );
            $recs[] = array(
                  'title'=>'COM_'.STRTOUPPER($this->compName).'_GUIDEDTOUR_TAB_LBL',
                  'desc'=>'COM_'.STRTOUPPER($this->compName).'_GUIDEDTOUR_TAB_DESC',
                  'position'=>'bottom',
                  'target'=>'button[aria-controls=permissions]',
                  'type'=>2,
                  'intertype'=>4,
                  'url'=>''
                  );
            $recs[] = array(
                  'title'=>'COM_'.STRTOUPPER($this->compName).'_GUIDEDTOUR_CONFIG_CREATE_LBL',
                  'desc'=>'COM_'.STRTOUPPER($this->compName).'_GUIDEDTOUR_CONFIG_CREATE_DESC',
                  'position'=>'bottom',
                  'target'=>'',
                  'type'=>2,
                  'intertype'=>2,
                  'url'=>''
                  );
            $recs[] = array(
                  'title'=>'COM_'.STRTOUPPER($this->compName).'_GUIDEDTOUR_SAVECLOSE_LBL',
                  'desc'=>'COM_'.STRTOUPPER($this->compName).'_GUIDEDTOUR_SAVECLOSE_DESC',
                  'position'=>'bottom',
                  'target'=>'#toolbar #toolbar-save button.button-save',
                  'type'=>2,
                  'intertype'=>1,
                  'url'=>$returnURL
                  );
            $recs[] = array(
                  'title'=>'COM_'.STRTOUPPER($this->compName).'_GUIDEDTOUR_CONGRATS_LBL',
                  'desc'=>'COM_'.STRTOUPPER($this->compName).'_GUIDEDTOUR_CONFIG_CONGRATS_DESC',
                  'position'=>'bottom',
                  'target'=>'',
                  'type'=>0,
                  'intertype'=>1,
                  'url'=>$returnURL
                  );
        }

		return $recs;
	}

	/**
	 * ***************  Check for old data structure where columns need to be added  ***************
	 */

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
		$db->setQuery('ALTER TABLE '.$db->quotename($table) . ' ADD '.$db->quotename($column).' '.$type.' AFTER '.$db->quotename($after));
		try {
		    $db->execute();
		    Factory::getApplication()->enqueueMessage('Column '. $column . ' created', 'success');
		} catch (RuntimeException $e) {
		    Factory::getApplication()->enqueueMessage($e->getMessage(), 'danger');
		}
	}

	/**
	 * *********************  Any additional stuff  *******************************
	 */

	/**
	 * Get the version of the component
	 * @return version element of manifest
	 */
	public static function getComponentVersion()
	{
		$componentXML = Installer::parseXMLInstallFile(Path::clean(JPATH_ADMINISTRATOR . '/components/com_gatripsys/gatripsys.xml'));
        if (isset($componentXML)) {
            return $componentXML['version'];
        } else {
            return $this->version;
        }
	}

	/**
	 * method to create folders required for the component
	 * @return void
	 */
	public function createFolders($parent, $folder)
	{
        $path = Path::clean( JPATH_SITE . '/' . $parent . '/' . $folder );

		if (!is_dir($path) && !is_file($path)) {
            echo '<p>Did Not Exist - "'.Text::_($path).'" so it was created.</p>';
            Folder::create($path);
        } else {
			echo '<p>Already Exists - '.Text::_($path).'</p>';
		}
	}

	/**
	 * method to create folders required for the component
	 * @return void
	 */
	public function createCategories($categories = array(), $catName = '', $catDesc = '', $catImage = '')
	{
		$extName = 'com_gatripsys';
		
		foreach ($categories as $cat) {
            $category = Table::getInstance('Category');
            $category->extension = $extName.$catName;
            $category->title = $cat;
            $category->alias = OutputFilter::stringUrlSafe($cat);
            $category->description = $catDesc;
            $category->published = 1;
            $category->access = 1;
            $category->params = '{"category_layout":"","image":"'.$catImage.'","image_alt":"'.$cat.'"}';
            $category->metadata = '{"page_title":"","author":"","robots":""}';
            $category->language = '*';
            // Set the location in the tree
            $category->setLocation(1, 'last-child');
            // Check to make sure our data is valid
            if (!$category->check()) {
                throw new \Exception(500, $category->getError());
            }
            // Now store the category
            if (!$category->store(true)) {
                throw new \Exception(500, $category->getError());
            }
	 	}
        // Build the path for our category
        $category->rebuildPath($category->id);
        echo '<p>' . Text::sprintf('Categories created for '.$catName) . '</p>';
	}

	public function setupMainCategories()
	{
        $dest = Path::clean( JPATH_SITE . '/images/trips/' );
        $src = Path::clean(JPATH_SITE . '/media/com_gatripsys/images/');
        File::copy($src . 'signage_easy.jpg' , $dest . 'signage_easy.jpg');
        File::copy($src . 'signage_medium.jpg' , $dest . 'signage_medium.jpg');
        File::copy($src . 'signage_difficult.jpg' , $dest . 'signage_difficult.jpg');
        File::copy($src . 'signage_vdifficult.jpg' , $dest . 'signage_vdifficult.jpg');

		$rating1 = '<p>The following classifications will assist in determining an overall Trip Rating:</p>';
		$rating1 .= '<table class="table-bordered"><tbody><tr><th> </th><th>Easy</th></tr><tr>';
		$rating1 .= '<td><strong>Overall Description</strong></td><td>All Wheel Drive and High Range 4WD. Novice Drivers</td>';
		$rating1 .= '</tr><tr><td><strong>Advisory Sign</strong></td>';
		$rating1 .= '<td class="center"><img class="center" style="display: block; margin-left: auto; margin-right: auto;" src="images/trips/signage_easy.jpg" alt="Easy Sign - Green Circle" /></td>';
		$rating1 .= '</tr><tr><td><strong>Expected terrain and track conditions</strong></td>';
		$rating1 .= '<td>Mostly unsealed roads with no obstacles and minor gradients.</td></tr><tr>';
		$rating1 .= '<td><strong>Vehicle suitability</strong></td>';
		$rating1 .= '<td>All wheel Drive and High Range 4WD. Can be low clearance with single range and road tyres.</td>';
		$rating1 .= '</tr><tr><td><strong>Recovery equipment</strong></td><td> </td></tr><tr>';
		$rating1 .= '<td><strong>Driver Training / Experience</strong></td><td>Suitable for novice drivers.</td>';
		$rating1 .= '</tr><tr><td><strong>Weather</strong></td><td>May be difficult in wet conditions.</td></tr></tbody></table>';
        $this->createCategories(array('Easy'), '', $rating1, 'images\/trips\/signage_easy.jpg');

        $rating2 = '<p>The following classifications will assist in determining an overall Trip Rating:</p>';
		$rating2 .= '<table class="table-bordered"><tbody><tr><th> </th><th>Medium</th></tr><tr>';
		$rating2 .= '<td><strong>Overall Description</strong></td>';
		$rating2 .= '<td>Mainly High range 4WD but Low range required. Some 4WD experience recommended.</td>';
		$rating2 .= '</tr><tr><td><strong>Advisory Sign</strong></td>';
		$rating2 .= '<td class="center"><img class="center" src="images/trips/signage_medium.jpg" alt="Medium Sign - Blue Square" /></td>';
		$rating2 .= '</tr><tr><td><strong>Expected terrain and track conditions</strong></td>';
		$rating2 .= '<td>Tracks with some steep and/or rocky/slippery/sandy sections. May have shallow water crossings.</td>';
		$rating2 .= '</tr><tr><td><strong>Vehicle suitability</strong></td>';
		$rating2 .= '<td>Suitable for medium clearance vehicles with dual range and all terrain or road tyres.</td>';
		$rating2 .= '</tr><tr><td><strong>Recovery equipment</strong></td><td> </td></tr><tr>';
		$rating2 .= '<td><strong>Driver Training / Experience</strong></td>';
		$rating2 .= '<td>Recommended that drivers have experience or 4WD training. Recommended to be done in groups of vehicles.</td>';
		$rating2 .= '</tr><tr><td><strong>Weather</strong></td><td>Will be more difficult in wet conditions.</td></tr></tbody></table>';
        $this->createCategories(array('Medium'), '', $rating2, 'images\/trips\/signage_medium.jpg');

		$rating3 ='<p>The following classifications will assist in determining an overall Trip Rating:</p>';
		$rating3 .='<table class="table-bordered"><tbody><tr><th> </th><th>Difficult</th></tr>';
		$rating3 .='<tr><td><strong>Overall Description</strong></td>';
		$rating3 .='<td>Significant Low range 4WD with standard 4WD ground clearance. Should have 4WD driver training.</td>';
		$rating3 .='</tr><tr><td><strong>Advisory Sign</strong></td>';
		$rating3 .='<td class="center"><img class="center" style="display: block; margin-left: auto; margin-right: auto;" src="images/trips/signage_difficult.jpg" alt="Difficult Sign - Black Diamond" /></td>';
		$rating3 .='</tr><tr><td><strong>Expected terrain and track conditions</strong></td>';
		$rating3 .='<td>Tracks with frequent steep and/or rocky/slippery/sandy sections. Possible water crossings.</td>';
		$rating3 .='</tr><tr><td><strong>Vehicle suitability</strong></td>';
		$rating3 .='<td>Suitable for medium to high clearance vehicles with dual range and all terrain tyres.</td>';
		$rating3 .='</tr><tr><td><strong>Recovery equipment</strong></td><td>Recovery equipment required.</td></tr>';
		$rating3 .='<tr><td><strong>Driver Training / Experience</strong></td>';
		$rating3 .='<td>Recommended for drivers with reasonable experience or 4WD training. To be done in groups of vehicles.</td>';
		$rating3 .='</tr><tr><td><strong>Weather</strong></td><td>Will be more difficult in wet conditions.</td></tr></tbody></table>';
        $this->createCategories(array('Difficult'), '', $rating3, 'images\/trips\/signage_difficult.jpg');

		$rating4 = '<p>The following classifications will assist in determining an overall Trip Rating:</p>';
		$rating4 .= '<table class="table-bordered"><tbody><tr><th> </th><th>Very Difficult</th></tr><tr>';
		$rating4 .= '<td><strong>Overall Description</strong></td><td>Low range 4WD with High ground clearance. Experienced Drivers</td>';
		$rating4 .= '</tr><tr><td><strong>Advisory Sign</strong></td>';
		$rating4 .= '<td class="center"><img class="center" style="display: block; margin-left: auto; margin-right: auto;" src="images/trips/signage_vdifficult.jpg" alt="Very Difficult Sign - Double Black Diamond" /></td>';
		$rating4 .= '</tr><tr><td><strong>Expected terrain and track conditions</strong></td>';
		$rating4 .= '<td>Tracks with frequent very steep and/or rocky/slippery/sandy sections. May have difficult river crossings.</td>';
		$rating4 .= '</tr><tr><td><strong>Vehicle suitability</strong></td>';
		$rating4 .= '<td>Suitable for high clearance vehicles with dual range and tyres suitable for the terrain. (Mud Terrain tyres).</td>';
		$rating4 .= '</tr><tr><td><strong>Recovery equipment</strong></td><td>Winch / Recovery equipment required.</td>';
		$rating4 .= '</tr><tr><td><strong>Driver Training / Experience</strong></td>';
		$rating4 .= '<td>Drivers with extensive experience and advanced training should only attempt as there are several technical challenges. Recommended to be done in groups of four or more vehicles.</td>';
		$rating4 .= '</tr><tr><td><strong>Weather</strong></td><td>Will be more difficult in wet conditions.</td></tr></tbody></table>';
        $this->createCategories(array('Very Difficult'), '', $rating4, 'images\/trips\/signage_vdifficult.jpg');

	}

	/**
	 * Batch update of old J3 version of software
	 */
	function removeOldFiles()
	{
		$compDesc = 'com_gatripsys';
		$compName = 'gatripsys';
		$path = Path::clean( JPATH_SITE . '/components/' . $compDesc . '/');
		$this->deleteFiles($path, 'index', '.html');
		$this->deleteFiles($path, $compName, '.php');
		$this->deleteFiles($path, 'router', '.php');
		$this->deleteFiles($path, 'controller', '.php');

		$adpath = Path::clean( JPATH_ADMINISTRATOR . '/components/' . $compDesc . '/');
		$this->deleteFiles($adpath, 'index', '.html');
		$this->deleteFiles($adpath, $compName, '.php');
		$this->deleteFiles($adpath, 'access', '.xml');
		$this->deleteFiles($adpath, 'config', '.xml');
		$this->deleteFiles($adpath, 'controller', '.php');

        $folders = array(
			'controllers',
			'helpers',
			'models',
			'tables',
			'views'
			);
        $files = array(
			'trip',
			'attendee',
			'incident',
			'invoice'
			);

        // remove old folders and files - SITE
        foreach ($folders as $folder) {

			$path = Path::clean( JPATH_SITE . '/components/' . $compDesc . '/' . $folder .'/');
			$adpath = Path::clean( JPATH_ADMINISTRATOR . '/components/' . $compDesc . '/' . $folder .'/');

			if ($folder == 'controllers') {
				foreach ($files as $file) {
					$this->deleteFiles($path, 'index', '.html');
					$this->deleteFiles($adpath, 'index', '.html');
					$this->deleteFiles($path, $file, '.php');
					$this->deleteFiles($path, $file, 's.php');
					$this->deleteFiles($path, $file, 'form.php');
					$this->deleteFiles($path, $file, '.xml');
					$this->deleteFiles($adpath, $file, '.php');
					$this->deleteFiles($adpath, $file, 's.php');
				}
				$this->deleteFolders($path);
				$this->deleteFolders($adpath);
			}

			if ($folder == 'models') {
				foreach ($files as $file) {
					$this->deleteFiles($path, 'index', '.html');
					$this->deleteFiles($adpath, 'index', '.html');
					$this->deleteFiles($path, $file, '.php');
					$this->deleteFiles($path, $file, 's.php');
					$this->deleteFiles($path, $file, 'form.php');
					$this->deleteFiles($path, $file, '.xml');
					$this->deleteFiles($adpath, $file, '.php');
					$this->deleteFiles($adpath, $file, 's.php');

					$pathfield = $path . 'fields/';
					$adpathfield = $adpath . 'fields/';
					$this->deleteFiles($pathfield, 'index', '.html');
					$this->deleteFiles($adpathfield, 'index', '.html');
					$this->deleteFiles($adpathfield, 'submit', '.php');
					$this->deleteFiles($adpathfield, 'createdby', '.php');
					$this->deleteFiles($adpathfield, 'timecreated', '.php');
					$this->deleteFiles($adpathfield, 'modifiedby', '.php');
					$this->deleteFiles($adpathfield, 'timeupdated', '.php');

					$pathform = $path . 'forms/';
					$adpathform = $adpath . 'forms/';
					$this->deleteFiles($pathform, 'index', '.html');
					$this->deleteFiles($adpathform, 'index', '.html');
					$this->deleteFiles($pathform, 'filter_'.$file, 's.xml');
					$this->deleteFiles($pathform, $file, 'form.xml');
					$this->deleteFiles($adpathform, 'filter_'.$file, 's.xml');
					$this->deleteFiles($adpathform, $file, '.xml');
				}
				$this->deleteFolders($pathfield);
				$this->deleteFolders($pathform);
				$this->deleteFolders($path);
				$this->deleteFolders($adpathfield);
				$this->deleteFolders($adpathform);
				$this->deleteFolders($adpath);
			}

			if ($folder == 'views') {
				foreach ($files as $file) {
					$this->deleteFiles($path, 'index', '.html');
					$this->deleteFiles($adpath, 'index', '.html');
					// single
					$this->deleteFiles($path . $file . '/', 'view.html.php');
					$this->deleteFiles($path . $file . '/', 'index.html');
					$this->deleteFiles($path . $file . '/tmpl/', 'default.php');
					$this->deleteFiles($path . $file . '/tmpl/', 'index.html');
					$this->deleteFiles($path . $file . '/tmpl/', 'default.xml');
					$this->deleteFolders($path . $file . '/tmpl/');
					$this->deleteFolders($path . $file . '/');

					// form
					$this->deleteFiles($path . $file . 'form/', 'view.html.php');
					$this->deleteFiles($path . $file . 'form/', 'index.html');
					$this->deleteFiles($path . $file . 'form/tmpl/', 'default.php');
					$this->deleteFiles($path . $file . 'form/tmpl/', 'index.html');
					$this->deleteFiles($path . $file . 'form/tmpl/', 'default.xml');
					$this->deleteFolders($path . $file . 'form/tmpl/');
					$this->deleteFolders($path . $file . 'form/');

					// multiple
					$this->deleteFiles($path . $file . 's/', 'view.html.php');
					$this->deleteFiles($path . $file . 's/', 'index.html');
					$this->deleteFiles($path . $file . 's/tmpl/', 'default.php');
					$this->deleteFiles($path . $file . 's/tmpl/', 'default_filter.php');
					$this->deleteFiles($path . $file . 's/tmpl/', 'index.html');
					$this->deleteFiles($path . $file . 's/tmpl/', 'default.xml');
					$this->deleteFolders($path . $file . 's/tmpl/');
					$this->deleteFolders($path . $file . 's/');
				}
				$this->deleteFolders($path);
			}

			if ($folder == 'helpers') {
				$this->deleteFiles($path, 'index', '.html');
				$this->deleteFolders($path);
				$this->deleteFiles($adpath, 'index', '.html');
				$this->deleteFiles($adpath, $compName, '.php');
				$this->deleteFiles($adpath, 'listhelper', '.php');
				$this->deleteFolders($adpath);
			}

			if ($folder == 'tables') {
				$this->deleteFiles($adpath, 'index', '.html');
				foreach ($files as $file) {
					$this->deleteFiles($adpath, $file, '.php');
				}
				$this->deleteFolders($adpath);
			}
        }

		return true;
	}

	/**
	 * Delete unwanted file
	 */
	function deleteFiles($path = null, $file = null, $ext = null)
	{
        $path_to_file = $path . $file . $ext;
		if (is_file($path_to_file)) {
			File::delete($path_to_file);
			Factory::getApplication()->enqueueMessage('Deleted File - '.$path_to_file);
		}
	}

	/**
	 * Delete unwanted file
	 */
	function deleteFolders($path = null)
	{
		if (\file_exists($path)) {
			Folder::delete($path);
			Factory::getApplication()->enqueueMessage('Deleted Folder - '.$path);
		}
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
	 * Removes the dashboard menu module
	 * @param int $id The dashboard module id reference
	 * @return  void
	 */
	public function removeDashboardMenu($id)
	{
		$model  = Factory::getApplication()->bootComponent('com_modules')->getMVCFactory()->createModel('Module', 'Administrator', ['ignore_request' => true]);
        $table = $model->getTable();
        $table->load($id);
        $table->state = -2;
		if (!$table->store(true))
		{
			Factory::getApplication()->enqueueMessage(Text::sprintf('COM_'.STRTOUPPER($this->compName).'_REMOVE_DASHBOARD_FAIL', $model->getError()));
		}
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
	 * Update state data regards to
	 */
	public function updateStateFields($table, $oldVal, $newVal)
	{
		$db    = Factory::getContainer()->get('DatabaseDriver');
		$query = $db->getQuery(true);
		$db->setQuery('UPDATE '.$db->quotename($table).' SET '.$db->quotename('state').' = '.$newVal.' WHERE '.$db->quotename('state').' = '.(int)$oldVal);
		$db->execute();

		Factory::getApplication()->enqueueMessage('State Field Updated from '.$oldVal.' to '.$newVal);
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


}
