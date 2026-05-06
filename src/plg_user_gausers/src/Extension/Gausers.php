<?php
/**
 * @version 	5.3
 * @copyright	Copyright (C) 2011 - 2019 Glenn Arkell. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace GlennArkell\Plugin\User\Gausers\Extension;

defined('JPATH_BASE') or die;

use \Joomla\CMS\Factory;
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\Plugin\CMSPlugin;
use \Joomla\Database\DatabaseAwareTrait;
use \Joomla\CMS\Component\ComponentHelper;
use \Joomla\CMS\Plugin\PluginHelper;
use \Joomla\Filesystem\Path;
use \GlennArkell\Component\Gausers\Administrator\Helper\GaregistrationHelper;

/**
 * Additional information for the profile plugin.
 */
final class Gausers extends CMSPlugin
{
    use DatabaseAwareTrait;

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
        // Load plugin language files
        $this->loadLanguage();

		$comParams = ComponentHelper::getParams('com_gausers');
        $exclMbrs = $comParams->get('exclude_member');
        $idList    = '';
        if (is_array($exclMbrs)) {
            foreach ( $exclMbrs as $exc) { $idList .= $exc.',';}
        }

		$params = $this->params;
		$def_usergroup = $this->params->get('def_usergroup', 2);
		$def_filename = $this->params->get('def_filename', 'members.csv');
		$file_ext = \substr($def_filename,-3);
		//$prof_suffix = $this->params->get('prof_suffix');
		//$prof_fields = $this->params->get('prof_fields');
		//$fields = array();
		//$fields[] = \str_getcsv($prof_fields);
		$fileName = Path::clean( JPATH_SITE . '/images/members/'.$def_filename );

        if (\file_exists('file://'.$fileName)) {

			if ($file_ext == 'csv' || $file_ext == 'CSV') {

			    /* Map Rows and Loop Through Them */
			    $rows   = \array_map('str_getcsv', file($fileName));
			    $header = \array_shift($rows);
			    $members    = array();
			    foreach($rows as $row) {
			        $members[] = \array_combine($header, $row);
			    }

				foreach ($members as $member) {
					// now load the record if not already exists
					$exists = self::checkEmailExists($member, $comParams);
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
                $idList = \substr($idList,0,-1);
                $rowsAffected = self::blockUsers($idList);
                $this->app->enqueueMessage(Text::_('PLG_USER_GAUSERLOADER_MBR_ROWS'), 'notice');
			} else {
                $this->app->enqueueMessage(Text::_('PLG_USER_GAUSERLOADER_FILENOTCSV'), 'danger');
            }
		}

		return true;
	}

	/**
	 * Get member from database
	 * @param   array  $mbr - user details from CSV
	 * @return  object  ObjectList of members data
	 */
	public static function checkEmailExists( $member )
	{
		// count user records
		$db = Factory::getContainer()->get('DatabaseDriver');
		$query = $db->getQuery(true);
		$query->clear()
			->select(' *, p.profile_value AS phone, c.profile_value AS city ')
			->from($db->quoteName('#__users'))
			->join($db->quoteName('#__user_profiles', 'p') . ' ON id = p.user_id AND p.profile_key = '.$db->Quote('profile.phone') )
			->join($db->quoteName('#__user_profiles', 'c') . ' ON id = c.user_id AND c.profile_key = '.$db->Quote('profile.city') )
			->where($db->quoteName('email') . ' = ' . $db->Quote($member['email']) );

		$db->setQuery($query);

		try {
			$result = $db->loadResult();
		} catch (RuntimeException $e) {
			Factory::getApplication()->enqueueMessage(Text::_('PLG_USER_GAUSERLOADER_CHECKEMAIL_FAILED'), 'warning');
			$result = 0;
		}

		return $result;
	}

	/**
	 * Update member record as disabled
	 * @param   array  $idList - csv of enabled users
	 * @return  boolean
	 */
	public static function blockUsers($userIDs) {

		$db = Factory::getContainer()->get('DatabaseDriver');
		$db->setQuery('UPDATE #__users SET block = 1 WHERE id NOT IN ('.$userIDs.')' );
		try {
			$db->execute();
			return true;
		} catch (RuntimeException $e) {
			Factory::getApplication()->enqueueMessage($e->getMessage(), 'danger');
			return false;
		}
	}

}
