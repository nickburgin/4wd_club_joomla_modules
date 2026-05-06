<?php
/**
 * @version 	5.1.6
 * @copyright	Copyright (C) 2011 - 2019 Glenn Arkell. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

use \Joomla\CMS\Factory;
use \Joomla\CMS\Date\Date;
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\Form\Form;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;
use \Joomla\Utilities\ArrayHelper;
use \Joomla\CMS\Plugin\CMSPlugin;
use \Joomla\CMS\User\UserHelper;
use \Joomla\CMS\Component\ComponentHelper;
use \Joomla\CMS\Installer\Installer;
use \Joomla\Filesystem\Path;
use \Joomla\CMS\Access\Access;
use \Joomla\CMS\Application\ApplicationHelper;
use \Joomla\CMS\Uri\Uri;
use \Joomla\CMS\Plugin\PluginHelper;
use \Joomla\CMS\String\PunycodeHelper;
use \Joomla\CMS\Router\Route;
use \Joomla\CMS\User\User;
use \GlennArkell\Plugin\User\Gausers\Helper\GausersHelper;
use \GlennArkell\Component\Gausers\Administrator\Helper\GaregistrationHelper;

/**
 * Additional information for the profile plugin.
 */
class PlgUserGausers extends CMSPlugin
{
	/**
	 * Application object.
	 *
	 * @var    JApplicationCms
	 * @since  4.0.0
	 */
	protected $app;

	/**
	 * Load the language file on instantiation.
	 *
	 * @var    boolean
	 * @since  3.1
	 */
    protected $autoloadLanguage = true;

	/**
	 * Triggered from the component controller
	 * @return	boolean
	 */
	public function onGaloaderPrepareData()
	{
		$comParams = ComponentHelper::getParams('com_gausers');
        $exclMbrs = $comParams->get('exclude_member');
        $idList    = '';
        if (is_array($exclMbrs)) {
            foreach ( $exclMbrs as $exc) { $idList .= $exc.',';}
        }

        $user = GausersHelper::getSpecificUser();
		$params = $this->params;
		$def_usergroup = $this->params->get('def_usergroup', 2);
		$def_filename = $this->params->get('def_filename', 'members.csv');
		$file_ext = substr($def_filename,-3);
		$prof_suffix = $this->params->get('prof_suffix');
		$prof_fields = $this->params->get('prof_fields');
		$fields = array();
		$fields[] = str_getcsv($prof_fields);
		$fileName = Path::clean( JPATH_SITE . '/images/members/'.$def_filename );

        if (file_exists('file://'.$fileName)) {

			if ($file_ext == 'csv' || $file_ext == 'CSV') {

			    /* Map Rows and Loop Through Them */
			    $rows   = array_map('str_getcsv', file($fileName));
			    $header = array_shift($rows);
			    $members    = array();
			    foreach($rows as $row) {
			        $members[] = array_combine($header, $row);
			    }

				foreach ($members as $member) {
					// now load the record if not already exists
					$exists = GausersHelper::checkNewUser($member);
                    $message = '';
					if (!$exists) {
						// returns a new user id reference
                        $ok = GaregistrationHelper::setupNewUserData($member);
						if ($ok) {
							$this->app->enqueueMessage(Text::sprintf('PLG_USER_GAUSERLOADER_MBR_CREATED',$member['name'],$ok), 'message');
						} else {
							$this->app->enqueueMessage(Text::_('PLG_USER_GAUSERLOADER_MBR_FAILED').' - '.$member['name'], 'warning');
						}
					} else {
                        // check if phone number changed
                        if ($member['phone'] != $exists->phone) {
                            $message .= ' Phone needs updating to '.$member['phone'].' - ';
                        }
                        if ($member['city'] != $exists->city) {
                            $message .= ' City needs updating - was '.$exists->city.' and should be '.$member['city'];
                        }
						$idList .= $exists->id.',';
                        $this->app->enqueueMessage(Text::sprintf('PLG_USER_GAUSERLOADER_MBR_EXISTS', $member['name'], $exists->id, $message), 'notice');
					}
				}
                // remove final comma and then disable others
                $idList = substr($idList,0,-1);
                $rowsAffected = GausersHelper::disabledUsers($idList);
                $this->app->enqueueMessage(Text::sprintf('PLG_USER_GAUSERLOADER_MBR_ROWS',$rowsAffected), 'notice');
			}
		}

		return true;
	}

}
