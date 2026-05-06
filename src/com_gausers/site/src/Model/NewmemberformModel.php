<?php
/**
 * @version     5.1.6
 * @package     com_gausers
 * @author     Glenn Arkell <glenn@glennarkell.com.au>
 * @copyright  2019 Glenn Arkell
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace GlennArkell\Component\Gausers\Site\Model;

// No direct access.
defined('_JEXEC') or die;

use \Joomla\CMS\Factory;
use \Joomla\Utilities\ArrayHelper;
use \Joomla\CMS\MVC\Model\FormModel;
use \Joomla\CMS\Table\Table;
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\Component\ComponentHelper;
use \Joomla\Filesystem\File;
use \Joomla\Filesystem\Folder;
use \Joomla\Filesystem\Path;
use \Joomla\CMS\User\User;
use \GlennArkell\Component\Gausers\Administrator\Helper\GaemailHelper;
use \GlennArkell\Component\Gausers\Administrator\Helper\GambrapplicHelper;

/**
 * Form model.
 * @since  1.6
 */
class NewmemberformModel extends FormModel
{
    private $item = null;

    /**
     * Method to auto-populate the model state.
     * Note. Calling getState in this method will result in recursion.
     * @return void
     * @since  1.6
     */
    protected function populateState()
    {
        $app = Factory::getApplication('com_gausers');

        // Load the parameters.
        $params       = $app->getParams();
        $params_array = $params->toArray();

        if (isset($params_array['item_id'])) {
            $this->setState('newmember.id', $params_array['item_id']);
        }

        $this->setState('params', $params);
    }

    /**
     * Method to get an ojbect.
     * @param   integer $id The id of the object to get.
     * @return Object|boolean Object on success, false on failure.
     * @throws Exception
     */
    public function getItem($id = null)
    {
        if ($this->item === null) {
            $this->item = new \stdClass();
        }

        return $this->item;
    }

    /**
     * Method to get the profile form.
     * The base form is loaded from XML
     * @param   array   $data     An optional array of data for the form to interogate.
     * @param   boolean $loadData True if the form is to load its own data (default case), false if not.
     * @return    JForm    A JForm object on success, false on failure
     * @since    1.6
     */
    public function getForm($data = array(), $loadData = true)
    {
        // Get the form.
        $form = $this->loadForm('com_gausers.newmember', 'newmemberform', array(
                        'control'   => 'jform',
                        'load_data' => $loadData
                )
        );

        if (empty($form)) {
                return false;
        }

        return $form;
    }

    /**
     * Method to get the data that should be injected in the form.
     * @return    mixed    The data for the form.
     * @since    1.6
     */
    protected function loadFormData()
    {
        $data = Factory::getApplication()->getUserState('com_gausers.edit.newmember.data', array());
        Factory::getApplication()->setUserState('com_gausers.edit.newmember.data', null);
        if (empty($data)) {
            $data = $this->getItem();
        }
        

        return $data;
    }

    /**
     * Method to save the form data.
     * @param   array $data The form data
     * @return bool
     * @throws Exception
     * @since 1.6
     */
	/**
	 * Method to send a new member application form
	 * @param   array $data user submitted data
	 * @return  bool
	 */
	public function createNewMbrForm($data)
	{
		$item = ArrayHelper::toObject($data);
        $app = Factory::getApplication();
        $lang = Factory::getLanguage();
        $lang->load('com_gausers', JPATH_ADMINISTRATOR);
        $lang->load('com_gausers', JPATH_SITE);
        $mailfrom	= $app->get('mailfrom');
        $fromname	= $app->get('fromname');
        $params = ComponentHelper::getParams('com_gausers');
		$tmplMail  = $params->get('tmplMail', 1);

		if ($data['email']) {
    		// setup data
            $data['sitename'] = $fromname;
            $data['recips'][] = array('email'=>$data['email'], 'name'=>$data['name']);
            $data['cc_recips'][] = array('email'=>$mailfrom, 'name'=>$fromname);

            $attachfile = GambrapplicHelper::newMemberForm($item, $params);
            $filename = Factory::getApplication()->getUserState('com_gausers.file.newmember.name');
            Factory::getApplication()->setUserState('com_gausers.file.newmember.name', null);
    
            if ($tmplMail) {
                $link = null;
        		$sentOK = GaemailHelper::sendEmailTemplate('com_gausers.mbrnew', $data, $filename, $link, $attachfile);
    		} else {
                $body = GaemailHelper::setupEmailContent($item);
                $subject = 'Member Application Form';
                $sentOK = GaemailHelper::sendEmail(array($item->email), $body, $subject, $attachfile, null, null);
    		}
		}

        return $sentOK;

	}


}
