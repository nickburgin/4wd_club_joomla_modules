<?php
/**
 * @version    4.2.0
 * @package    pkg_mypackage
 * @subpackage com_gatracklog
 * @author     Glenn Arkell <glenn@glennarkell.com.au>
 * @copyright  2021 Glenn Arkell
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace GlennArkell\Component\Gatracklog\Administrator\Helper;

// No direct access
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\User\UserHelper;
use Joomla\CMS\User\UserFactoryInterface;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\Filesystem\Path;
use Joomla\Filesystem\File;
use Joomla\Filesystem\Folder;
use Joomla\CMS\User\User;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Mail\MailTemplate;
use Joomla\CMS\Access\Access;
use Joomla\CMS\Installer\Installer;
use Joomla\Session\SessionInterface;
use Joomla\CMS\Application\SiteApplication;
use \GlennArkell\Component\Gatracklog\Administrator\Helper\GapdfHelper;

/**
 * Main component helper.
 */
class GatracklogHelper
{
    /** --------------------------------------------------------------------------------------------------   **/
    /** -----------------------------  Regularly used for most components       --------------------------   **/
    /** --------------------------------------------------------------------------------------------------   **/

    /**
     * Prints out a variable value in human readable format
     */
    public static function print_r2($val){
        echo '<pre>Test<br />';
        \print_r($val);
        echo  '</pre>';
    }

    /**
     * Gets the user record for the specific id reference
     */
    public static function getSpecificUser($id = 0)
	{
		if ($id) {
			$container = Factory::getContainer();
			$userFactory = $container->get(UserFactoryInterface::class);
			$user = $userFactory->loadUserById($id);
			//$user = Factory::getContainer()->get(UserFactoryInterface::class)->loadUserById($id);
		} else {
			$user = Factory::getApplication()->getIdentity();
		}
		unset($user->password);
		unset($user->activation);
		unset($user->params);
		unset($user->lastResetTime);
		unset($user->resetCount);
		unset($user->otpKey);
		unset($user->otep);
		unset($user->requireReset);

		return $user;
	}

	/**
	 * Set up a web asset object for use
	 */
	public static function setupGAWebApp()
	{
        // Boot the DI container.
        $container = Factory::getContainer();
        // Alias the session service key to the web session service.
        $container->alias(SessionInterface::class, 'session.web.site');
        // Get the application.
        $app      = $container->get(SiteApplication::class);
        //$template = $app->getTemplate(true);
        //$params   = $template->params;
        //$wa = Factory::getApplication()->getDocument()->getWebAssetManager();
        
        return $app;
	}

    /**
     * Load template over-rides when using modal view
     * This is important for Cloud White template scheme
     * because modals have the primary css over-ride any
     * css loaded in the template index.php file
     */
    public static function loadTmplStyleModal($wa)
	{
        $tmpl = Factory::getApplication()->getTemplate(true);
        if ($tmpl->params->get('colorName') == 'colors_white') {
        	$wa->addInlineStyle('.btn-primary {background-color:'.$tmpl->params->get('btnPbgColor').' !important;}');
        	$wa->addInlineStyle('.nav-link {color:var(--template-contrast) !important;}');
        	$wa->addInlineStyle('.form-check-input:checked, .form-select[multiple] option:checked, [multiple].custom-select option:checked {background-color:'.$tmpl->params->get('btnPbgColor').' !important;}');
        	$wa->addInlineStyle('.page-item.active .page-link {color: var(--rkic41site-color-link); background-color: #c1cee1 !important; border-color: #dfe3e7 !important;}');
        	$wa->addInlineStyle('.form-select[multiple] option:checked, [multiple].custom-select option:checked {background-color: var(--rkic41site-color-primary-border) !important;}');
        }
		return true;
	}

	/**
	 * Get the version of the component
	 * @return version element of manifest
	 */
	public static function getComponentVersion()
	{
		$componentXML = Installer::parseXMLInstallFile(Path::clean(JPATH_ADMINISTRATOR . '/components/com_gatracklog/gatracklog.xml'));

		return $componentXML['version'];
	}

    /**
     * Gets the edit permission for an user
     * @param   mixed  $item  The item
     * @return  bool
     */
    public static function canUserEdit($user, $item)
    {
        $permission = false;

        if ($user->authorise('core.edit', 'com_gatracklog')) {
            $permission = true;
        } else {
            if (isset($item->created_by) && $item->created_by) {
                if ($user->authorise('core.edit.own', 'com_gatracklog') && $item->created_by == $user->id) {
                    $permission = true;
                }
            } else {
                if ($user->authorise('core.create', 'com_gatracklog')) {
                    $permission = true;
                }
            }
        }

        return $permission;
    }

	/**
	 * Get the Model from another component for use
	 * @param   string  $name    The model name.
	 * @param   string  $prefix  The class prefix.
	 * @param   array   $config  Configuration array for model.
	 * @return object	The model
	 */
	public function getForeignModel($name = 'Actionlog', $prefix = 'Administrator', $config = array('ignore_request' => true))
	{
		$fmodel = Factory::getApplication()->bootComponent('com_actionlogs')->getMVCFactory()->createModel($name, $prefix, $config);

		return $fmodel;
	}

	/**
	 * Create a new record based on params
	 * @param   string  $table  Table name.
	 * @param   string  $key    The reference key
	 * @param   string  $value  Key value tyo be loaded
	 * @return boolean	false on failure
	 */
	public static function createNewRecord($table, $key, $value, $params)
	{
        $state = $params->get('auto_create', 0) ? 1 : 0;
        $new_rec = new \stdClass();
        $new_rec->id = 0;
        $new_rec->state = $state;
        $new_rec->trip_id = $key;
        $new_rec->user_id = $value;
        $new_rec->approved_by = $value;
        $new_rec->comment = 'Auto created.';

		try {
			$result = Factory::getContainer()->get('DatabaseDriver')->insertObject($table, $new_rec);
			Factory::getApplication()->enqueueMessage(Text::_('COM_GATRACKLOG_NEWREC_SUCCESSFUL'), 'notice');
		} catch (RuntimeException $e) {
			Factory::getApplication()->enqueueMessage($e->getMessage().' '.Text::_('COM_GATRACKLOG_NEWREC_FAILED'), 'danger');
			return false;
		}
		return true;
	}

    /** --------------------------------------------------------------------------------------------------   **/
    /** -----------------------------  Get options for selection fields etc     --------------------------   **/
    /** --------------------------------------------------------------------------------------------------   **/

	public static function getListOptions($table = '#__gatracklog_tracklogs', $label = 'Tracklog', $fld_value = 'id', $fld_text = 'tran_desc' )
	{
        // setup for other languages regarding the selection
        $sel_label = strtoupper('COM_GATRACKLOG_SELECT_'.$label);
        $sel_label = Text::_($sel_label);
		// get the records
        $db = Factory::getContainer()->get('DatabaseDriver');
		$query	= $db->getQuery(true);
        $query->clear();
		$query->select(' "0" as "value", " - Select '.$label.' - " as "text" UNION SELECT '.$db->quoteName($fld_value).' as value, '.$db->quoteName($fld_text).' as text ');
		$query->from( $db->quoteName($table) );
		if ($table == '#__users') {
            $query->where(' block = 0' );
        } else {
            $query->where(' state = 1' );
        }
		$query->order(' text ASC ' );
		$db->setQuery($query);

	    try {
	        $options = $db->loadObjectList();
	    } catch (RuntimeException $e) {
	        Factory::getApplication()->enqueueMessage($e->getMessage(),'danger');
	        return false;
	    }

		return $options;
	}

	public static function getCategoryOptions($ext = 'com_gatracklog', $label = 'Category Type', $field = 'title' )
	{
		// get the categories
        $db = Factory::getContainer()->get('DatabaseDriver');
		$query	= $db->getQuery(true);
        $query->clear();
		$query->select(' "0" as "value", " - Select '.$label.' - " as "text" UNION SELECT id as value, '.$field.' as text ');
		$query->from( ' #__categories ' );
		$query->where(' published = 1' );
		$query->where(' extension = '.$db->quote($ext) );
		$query->order(' text ASC ' );
		$db->setQuery($query);

	    try {
	        $options = $db->loadObjectList();
	    } catch (RuntimeException $e) {
	        Factory::getApplication()->enqueueMessage($e->getMessage(),'danger');
	        return false;
	    }

		return $options;
	}

    /** --------------------------------------------------------------------------------------------------   **/
    /** -----------------------------  Get some core reference type data elements   ----------------------   **/
    /** --------------------------------------------------------------------------------------------------   **/

    /**
    *   Method to get values for a member based on field id
    *   @param int $id field_id reference
    *   @param int $userId user id reference
    *   @return object field name and value
    */
	public static function getCustomFieldValue($id = 0, $userId = 0)
	{
        $db		= Factory::getContainer()->get('DatabaseDriver');
		$query	= $db->getQuery(true);
        $query->select('a.*')
            ->select(
                [
                    $db->quoteName('b.title', 'fieldName'),
                    $db->quoteName('a.value', 'fieldValue'),
                ]
            )
            ->from($db->quoteName('#__fields_values', 'a'))
            ->join('LEFT', $db->quoteName('#__fields', 'b'), $db->quoteName('b.id') . ' = ' . $db->quoteName('a.field_id'))

    		->where($db->quoteName('a.field_id') . ' = :id' )
    		->where($db->quoteName('a.item_id') . ' = :userId' )
            ->bind(':id', $id, ParameterType::INTEGER)
            ->bind(':userId', $userId, ParameterType::INTEGER);
		$db->setQuery($query);

	    try {
	        return $db->loadObject();
	    } catch (RuntimeException $e) {
	        Factory::getApplication()->enqueueMessage($e->getMessage().' Field Values');
	        return false;
	    }

	}

    /**
    *   Method to get the profile fields available
    *   if no local profile plugin and thus no local suffix provided then only std profile elements are listed
    *   @return array of object records
    */
	public static function getProfileFieldsOptions()
	{
        $params = ComponentHelper::getParams('com_gatracklog');
        $proflocal = $params->get('profile_suffix', '');
        $prof_key = 'profile'.$proflocal.'.';
        $keyLen = strlen($prof_key);
		$startkey = $keyLen+1;
		$sel_field = Text::_('COM_GATRACKLOG_SELECT_FIELD');

		// get the user records in the supplier group
        $db		= Factory::getContainer()->get('DatabaseDriver');
		$query	= $db->getQuery(true);
        $query->clear();
		$query->select(' "0" as "value", " - '.$sel_field.' - " as "text" UNION SELECT profile_key as value, substr(profile_key,'.$startkey.') as text ');
		$query->from( $db->quoteName('#__user_profiles') );
		$query->where(' substr(profile_key,1,'.$keyLen.') = '.$db->Quote($prof_key) );
		$query->group(' profile_key ' );
		$query->order(' text ASC ' );
		$db->setQuery($query);

	    try {
	        return $db->loadObjectList();
	    } catch (\RuntimeException $e) {
	        Factory::getApplication()->enqueueMessage($e->getMessage(),'danger');
	        return false;
	    }

	}

    /** --------------------------------------------------------------------------------------------------   **/
    /** -----------------  Get basic Record/s providing a table name and other reference data ------------   **/
    /** --------------------------------------------------------------------------------------------------   **/

    /**
    *   Method to get the required record
    *   @param string $table table name
    *   @param int $id id reference
    *   @return object record data
    */
	public static function getRecord($table = '', $id = 0)
	{
        $db		= Factory::getContainer()->get('DatabaseDriver');
		$query	= $db->getQuery(true);
        $query->select('a.*')
            ->from($db->quoteName(':table', 'a'))
    		->where($db->quoteName('a.id') . ' = :id' )
            ->bind(':id', $id, ParameterType::INTEGER)
            ->bind(':table', $table, ParameterType::STRING);
		$db->setQuery($query);

	    try {
	        return $db->loadObject();
	    } catch (RuntimeException $e) {
	        Factory::getApplication()->enqueueMessage($e->getMessage().' Record');
	        return false;
	    }

	}

    /**
    *   Method to get the required record
    *   @param string $table table name
    *   @param string $field field name to match on the id reference
    *   @param int $id id reference
    *   @return object record data
    */
	public static function getRecordList($table = '', $field = '', $id = 0)
	{
        $db		= Factory::getContainer()->get('DatabaseDriver');
		$query	= $db->getQuery(true);
        $query->select('a.*')
            ->from($db->quoteName(':table', 'a'))
    		->where($db->quoteName(':field') . ' = :id' )
            ->bind(':id', $id, ParameterType::INTEGER)
            ->bind(':field', $field, ParameterType::STRING)
            ->bind(':table', $table, ParameterType::STRING);
		$db->setQuery($query);

	    try {
	        return $db->loadObjectList();
	    } catch (RuntimeException $e) {
	        Factory::getApplication()->enqueueMessage($e->getMessage().' RecordList');
	        return false;
	    }

	}

    /**
     * Return a modified version of a given string with usable image paths for tours
     * pulled from guided tours updates in 5.2
     * @param   string  $description  The string to fix
     * @return  string
     */
    public static function fixImagePaths($description)
    {
        return preg_replace(
            [
                '*src="(?!administrator\/)images/*',
                '*src="media/*',
            ],
            [
                'src="../images/',
                'src="../media/',
            ],
            $description
        );
    }    
    
    /** --------------------------------------------------------------------------------------------------   **/
    /** -----------------------  Some specific processes for this particular component  ------------------   **/
    /** --------------------------------------------------------------------------------------------------   **/

	/**
	 * Send out an email
	 * @param   array   $recipients  mandatory
	 * @param   string  $body  - text of the email body
	 * @param   string  $subject  - text for the subject line
	 * @param   string  $attachfile - file location address
	 * @return  boolean	false on fail
	 */
    public static function sendEmail($recipients, $body, $subject, $attachfile)
	{
        //$params = ComponentHelper::getParams('com_gausers');
	    $app		= Factory::getApplication();
        $mailfrom	= $app->get('mailfrom');       // system email address
        $fromname	= $app->get('fromname');       // Site name or system name
        // Build the email and send
        $mail = Factory::getMailer();
        $mail->isHTML(true);
		$mail->addRecipient($recipients);
		$mail->setSender(array($mailfrom, $fromname));
		$mail->setSubject($subject);
		$mail->setBody($body);
		if (is_file($attachfile)) {
            $mail->addAttachment($attachfile);
        }
        $sent = $mail->Send();

        return true;
	}

	/**
	 * Gets the files attached to an item
	 * @param   int     $pk     The item's id
	 * @param   string  $table  The table's name
	 * @param   string  $field  The field's name
	 * @return  array  The files
	 */
	public static function getTracklogComments($id)
	{
        $db		= Factory::getContainer()->get('DatabaseDriver');
		$query = $db->getQuery(true);

		$query->select('a.*, b.name AS user_name');
		$query->from('#__gatracklog_trackcomments AS a');
		$query->join('LEFT','#__users AS b ON a.user_id = b.id');
		$query->where('a.track_id = ' . (int) $id);
		$query->where('a.state = 1');
		$query->order('a.created_date DESC');
		$db->setQuery($query);
	    try {
	        return $db->loadObjectList();
	    } catch (RuntimeException $e) {
	        Factory::getApplication()->enqueueMessage($e->getMessage());
	        return false;
	    }
	}

	/**
	 * Update record to trashed status
	 * @param   int     $pk     The item's id
	 * @return  boolean
	 */
	public static function deleteTracklogComments($id)
	{
        $db		= Factory::getContainer()->get('DatabaseDriver');
		$query = $db->getQuery(true);

		$query->update('#__gatracklog_trackcomments');
		$query->set('state = -2');
		$query->where('id = ' . (int) $id);
		$db->setQuery($query);
	    try {
	        return $db->execute();
	    } catch (RuntimeException $e) {
	        Factory::getApplication()->enqueueMessage($e->getMessage());
	        return false;
	    }
	}

	/**
	 * Gets the details of requested article
	 * @param   int     $id
	 * @return  an article object
	 */
	public static function getArticleDetails($id = 0)
	{
        $db		= Factory::getContainer()->get('DatabaseDriver');
		$query = $db->getQuery(true);

		$query->select('a.*, b.name AS created_by_name');
		$query->from('#__content AS a');
		$query->join('LEFT','#__users AS b ON a.created_by = b.id');
		$query->where('a.id = ' . (int) $id);
		$db->setQuery($query);
	    try {
	        return $db->loadObject();
	    } catch (RuntimeException $e) {
	        Factory::getApplication()->enqueueMessage($e->getMessage());
	        return false;
	    }
	}
}

