<?php
/**
 * @version     6.0.0
 * @package     com_gausers
 * @copyright   Copyright (C) 2013. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Glenn Arkell <glenn@glennarkell.com.au> - http://www.glennarkell.com.au
 */

namespace GlennArkell\Component\Gausers\Site\Model;

// No direct access.
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\MVC\Model\ItemModel;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\User\User;
use Joomla\CMS\User\UserHelper;
use \GlennArkell\Component\Gausers\Administrator\Helper\GausersHelper;
use \GlennArkell\Component\Gausers\Administrator\Helper\GanamesHelper;

/**
 * Item model.
 * @since  1.6
 */
class CurrentuserModel extends ItemModel
{
    
    public $_item;
    
	/**
	 * Method to auto-populate the model state.
	 * Note. Calling getState in this method will result in recursion.
	 * @return void
	 * @since    1.6
     * @throws Exception
	 */
	protected function populateState()
	{
		$app = Factory::getApplication('com_gausers');

		// Load state from the request userState on edit or from the passed variable on default
        if (Factory::getApplication()->input->get('layout') == 'edit') {
            $id = Factory::getApplication()->getUserState('com_gausers.edit.currentuser.id');
        } elseif (Factory::getApplication()->input->get('layout') == 'training') {
            $id = Factory::getApplication()->getUserState('com_gausers.edit.currentuser.id');
        } else {
            $id = Factory::getApplication()->input->get('id');
            Factory::getApplication()->setUserState('com_gausers.edit.currentuser.id', $id);
        }
		$this->setState('currentuser.id', $id);
		$this->setState('filter.published', 1);
		$this->setState('filter.archived', 2);

		// Load the parameters.
		$params = $app->getParams();
        $params_array = $params->toArray();
        if(isset($params_array['item_id'])){
            $this->setState('currentuser.id', $params_array['item_id']);
        }
		$this->setState('params', $params);

	}

	/**
	 * Method to get an object.
	 * @param   integer $id The id of the object to get.
	 * @return  mixed    Object on success, false on failure.
     * @throws Exception
	 */
	public function getItem($id = null)
	{
		if ($this->_item === null)
		{
			$this->_item = false;

			if (empty($id)) {
				$id = $this->getState('currentuser.id');
			}
			
			// Check if still null/0 id reference - then its the member wanting their own record
			if (!$id) {
				$id = Factory::getApplication()->getIdentity()->id;
			}

			$member = GanamesHelper::breakdownNamesFromUserID($id);
			//$member = $members[0];

			if ($member) {

                $user = Factory::getApplication()->getIdentity();
                $canMembers = $user->authorise('core.members', 'com_gausers');
                $canTrg = $user->authorise('core.trgcerts', 'com_gausers');
                $canEdit = $user->authorise('core.edit', 'com_gausers');
                $canEditOwn = $user->authorise('core.edit.own', 'com_gausers');
                $id = $member->id;

                if ($canEditOwn && $user->id == $id) { $canEditOwn = true; } else { $canEditOwn = false; }

                if ($canMembers || $canTrg || $canEditOwn) {
					// do nothing and allow through
				} else {
                    throw new \Exception(Text::_('JERROR_ALERTNOAUTHOR'));
                }
                
                $params = ComponentHelper::getParams('com_gausers');
                $profile_suffix  = $params->get('profile_suffix');
                $local_profile = 'profile'.$profile_suffix;

				$up = UserHelper::getProfile($id);
				if ($profile_suffix == 'b4wdc' || $profile_suffix == 'brb') {
					$up->$local_profile['second_phone'] = isset($up->$local_profile['2nd_phone']) ? $up->$local_profile['2nd_phone']:'';
					$up->$local_profile['second_email'] = isset($up->$local_profile['2nd_email']) ? $up->$local_profile['2nd_email']:'';
				}

                $member->profile = $up;

                $this->_item = $member;

			}
			
			if ($id) {
				// get details
				$this->_item->invoices = GausersHelper::getMemberInvoices($id);
				$this->_item->actions = GausersHelper::getRecordList('#__gausers_actions', 'user_id', $id);
			}
		}

		return $this->_item;
	}
    
	/**
	 * Method to get unpaid mship invoices and block the corresponding user
	 * @return bool
	 */
	public function bulkBlockUsers()
	{
		// get all unpaid invoice records
		$members = GausersHelper::getUnpaidInvoiceUsers();

		$blockUserList = '';
		foreach ($members AS $mbr) {
			$blockUserList .= $mbr->user_id.',';
		}
        $blockUserList = substr($blockUserList,0,-1);

        $db		= Factory::getContainer()->get('DatabaseDriver');
		$query	= $db->getQuery(true);
        $query->clear();
		$query->update(' #__users ');
		$query->set(' block = '.(int) 1);
		$query->where(' id IN ('.$blockUserList.')' );
		$db->setQuery((string)$query);

	    try {
			return $db->execute();
	    } catch (RuntimeException $e) {
	        Factory::getApplication()->enqueueMessage($e->getMessage().' Bulk Block Users');
	        return false;
	    }

		return true;
	}

	/**
	 * Method to block a user
	 * @param   int $id Element id
	 * @return  bool
	 */
	public function blockUser($id)
	{
        // set user record to be blocked
        $u = User::getInstance((int) $id);
        $u->set('block', '1');
        if (!$u->save()) {
			Factory::getApplication()->enqueueMessage(Text::_("COM_GAUSERS_BLOCK_FAILED".' '.$u->name), "warning");
		} else {
			Factory::getApplication()->enqueueMessage(Text::_("COM_GAUSERS_BLOCK_SUCCESS".' '.$u->name), "warning");
		}
	}
}
