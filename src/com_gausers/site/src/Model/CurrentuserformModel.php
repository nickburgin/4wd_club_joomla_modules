<?php
/**
 * @version     5.1.6
 * @package     com_gausers
 * @copyright   Copyright (C) 2013. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Glenn Arkell <glenn@glennarkell.com.au> - http://www.glennarkell.com.au
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
use \Joomla\CMS\User\User;
use \Joomla\CMS\User\UserHelper;
use \Joomla\CMS\User\UserFactory;
use \Joomla\CMS\Access\Access;
use \Joomla\Filesystem\File;
use \Joomla\Filesystem\Folder;
use \Joomla\Filesystem\Path;
use \Joomla\CMS\Date\Date;
use \GlennArkell\Component\Gausers\Administrator\Helper\GausersHelper;
use \GlennArkell\Component\Gausers\Administrator\Helper\GaimgmgmntHelper;
use \GlennArkell\Component\Gausers\Administrator\Helper\GauserverifyHelper;
use \GlennArkell\Component\Gausers\Administrator\Helper\GainvoiceHelper;
use \GlennArkell\Component\Gausers\Administrator\Helper\GaauditHelper;
use \GlennArkell\Component\Gausers\Administrator\Helper\GaregistrationHelper;
use \GlennArkell\Component\Gausers\Administrator\Helper\GalistmembersHelper;
use \GlennArkell\Component\Gausers\Administrator\Helper\GaaddresslistHelper;
use \GlennArkell\Component\Gausers\Administrator\Helper\GanamesHelper;
use \GlennArkell\Component\Gausers\Administrator\Helper\GaemailHelper;
use \GlennArkell\Component\Gausers\Administrator\Helper\GamailchimplistHelper;

/**
 * Form model.
 * @since  1.6
 */
class CurrentuserformModel extends FormModel
{
    
    var $item = null;

	/**
	 * Method to auto-populate the model state.
	 * Note. Calling getState in this method will result in recursion.
	 * @since	1.6
	 */
	protected function populateState()
	{
		$app = Factory::getApplication('com_gausers');

		// Load state from the request userState on edit or from the passed variable on default
        if ($app->input->get('layout') == 'edit') {
            $id = $app->getUserState('com_gausers.edit.currentuser.id');
        } elseif ($app->input->get('layout') == 'training') {
            $id = $app->getUserState('com_gausers.edit.currentuser.id');
        } else {
            $id = $app->input->get('id');
            $app->setUserState('com_gausers.edit.currentuser.id', $id);
        }
		$this->setState('currentuser.id', $id);

		// Load the parameters.
        $params = $app->getParams();
        $params_array = $params->toArray();
        if(isset($params_array['item_id'])){
            $this->setState('currentuser.id', $params_array['item_id']);
        }
		$this->setState('params', $params);

	}
        

	/**
	 * Method to get an ojbect.
	 * @param	integer	The id of the object to get.
	 * @return	mixed	Object on success, false on failure.
	 */
	public function getItem($id = null)
	{
		if ($this->item === null)
		{
			$this->item = false;

			if (empty($id)) {
				$id = $this->getState('currentuser.id');
			}

            $params = ComponentHelper::getParams('com_gausers');
            $profile_suffix  = $params->get('profile_suffix');
            $local_profile = 'profile'.$profile_suffix;
            // setup the vaccination field settings
			$hideVax  = $params->get('hide_vax', 1);
			$vax_field1  = $params->get('vax_field1', 0);
			$exempt_field1  = $params->get('exempt_field1', 0);
			$medcond1  = $params->get('medcond1');
			$vax_field2  = $params->get('vax_field2', 0);
			$exempt_field2  = $params->get('exempt_field2', 0);
			$medcond2  = $params->get('medcond2');
			$defMship  = $params->get('default_mship', 1);


			if (!$id) {
				// new user record needs to be created
                $this->item = new User;
			} else {

				// Attempt to load the member (an array of objects - if if just the one array item).
				$member = GanamesHelper::breakdownNamesFromUserID($id);
				//foreach ($members as $member) {
    				if ($member)
    				{
    	                $user = GausersHelper::getSpecificUser();
    	                //$member->id = $id;
    	                $mshipId = GainvoiceHelper::getLastInvoiceMship($id);
    	                if ($mshipId) {
                            $member->mship_id = $mshipId->mship_id;
                        } else {
                            $member->mship_id = $defMship;
                        }
    	                $canMembers = $user->authorise('core.members', 'com_gausers');
    	                $canTrg = $user->authorise('core.trgcerts', 'com_gausers');
    	                $canEdit = $user->authorise('core.edit', 'com_gausers');
    	                $canEditOwn = $user->authorise('core.edit.own', 'com_gausers');
    
    	                if ($canEditOwn && $user->id == $id) { $canEditOwn = true; } else { $canEditOwn = false; }
    
    	                if ($canMembers || $canTrg || $canEditOwn) {
    						// do nothing and allow through
    					} else {
    	                    throw new \Exception(Text::_('JERROR_ALERTNOAUTHOR'));
    	                }
    
    					$up = UserHelper::getProfile($id);

                        $mainProf = isset($up->profile) && $up->profile > '' ? $up->profile : array();
                        
                        if ($profile_suffix > ' ') {
                            $locProf = $up->$local_profile;
                            unset($locProf['memtype']);
                        } else {
                            $locProf = array();
                        }

                        $profile = array_merge($mainProf, $locProf);
                        ksort($profile);
                        //
                        foreach ($profile as $key => $val ) {
                            $member->$key = $val;
                        }
    
    					$this->item = $member;

            			// this will check if an image is loaded and set it up into the item
                        $this->item = GaimgmgmntHelper::checkForImage($this->item);

    				} // end of blank member
				//} // end of foreach

			} // end of id reference of user

			// get the medical records for member and partner
			if ($hideVax) {
				// don't get vax stuff
			} elseif ($id) {
				$vaxObj1 = GausersHelper::getCustomFieldValue($vax_field1, $member->id);
				if (isset($vaxObj1->fieldValue)) {
					$this->item->vaxed1 = $vaxObj1->fieldValue;
				} else {
					$this->item->vaxed1 = 0;
				}
				$exptObj1 = GausersHelper::getCustomFieldValue($exempt_field1, $member->id);
				if (isset($exptObj1->fieldValue)) {
					$this->item->vax_exempt1 = $exptObj1->fieldValue;
				} else {
					$this->item->vax_exempt1 = 0;
				}
				$medObj1 = GausersHelper::getCustomFieldValue($medcond1, $member->id);
				if (isset($medObj1->fieldValue)) {
					$this->item->medcond1 = $medObj1->fieldValue;
				} else {
					$this->item->medcond1 = '';
				}
	
				$vaxObj2 = GausersHelper::getCustomFieldValue($vax_field2, $member->id);
				if (isset($vaxObj2->fieldValue)) {
					$this->item->vaxed2 = $vaxObj2->fieldValue;
				} else {
					$this->item->vaxed2 = 0;
				}
				$exptObj2 = GausersHelper::getCustomFieldValue($exempt_field2, $member->id);
				if (isset($exptObj2->fieldValue)) {
					$this->item->vax_exempt2 = $exptObj2->fieldValue;
				} else {
					$this->item->vax_exempt2 = 0;
				}
				$medObj2 = GausersHelper::getCustomFieldValue($medcond2, $member->id);
				if (isset($medObj2->fieldValue)) {
					$this->item->medcond2 = $medObj2->fieldValue;
				} else {
					$this->item->medcond2 = '';
				}
			}
		}

		return $this->item;
	}
    
	public function getInvTable($type = 'Invoice', $prefix = 'Administrator', $config = array())
	{   
        return parent::getTable($type, $prefix, $config);
	}

	/**
	 * Method to get the profile form.
	 *
	 * The base form is loaded from XML 
     * 
	 * @param	array	$data		An optional array of data for the form to interogate.
	 * @param	boolean	$loadData	True if the form is to load its own data (default case), false if not.
	 * @return	JForm	A JForm object on success, false on failure
	 * @since	1.6
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm('com_gausers.currentuser', 'currentuserform', array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form)) {
			return false;
		}

		return $form;
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return	mixed	The data for the form.
	 * @since	1.6
	 */
	protected function loadFormData()
	{
		$data = Factory::getApplication()->getUserState('com_gausers.edit.currentuser.data', array());
        if (empty($data)) {
            $data = $this->getItem();

        }
        //Factory::getApplication()->setUserState('com_gausers.test.data',$data);
        
        return $data;
	}

	/**
	 * Method to save the form data.
	 *
	 * @param	array		The form data.
	 * @return	mixed		The user id on success, false on failure.
	 * @since	1.6
	 */
	public function save($data)
	{
		// prepare passed data - id = 0 if new user record being created here
		$user_id = (!empty($data['id'])) ? $data['id'] : (int)$this->getState('currentuser.id');

        $data['city'] = (!empty($data['city'])) ? strtoupper($data['city'] ?? '') : strtoupper($data['postal_city'] ?? '');
        $data['region'] = (isset($data['plain_region'])) ? strtoupper($data['plain_region'] ?? '') : strtoupper($data['region'] ?? '');
        $data['postal_region'] = (isset($data['postal_plain_region'])) ? strtoupper($data['postal_plain_region'] ?? '') : strtoupper($data['postal_region'] ?? '');

        $user = GausersHelper::getSpecificUser();
		$canMembers  = $user->authorise('core.members', 'com_gausers');
		$canAdmin  = $user->authorise('core.admin', 'com_gausers');
		$canAdmin = ($canMembers || $canAdmin) ? 1 : 0;

        //Check the user can edit this item
        $authorised = ($canAdmin || $user->authorise('core.trgcerts', 'com_gausers') || $user->id == $user_id);

        if (!$authorised) {
            throw new \Exception(403, Text::_('JERROR_ALERTNOAUTHOR'));
            return false;
        }

        GaauditHelper::getPreUpdatedData($user_id);

        // set up basic component information
		$params = ComponentHelper::getParams('com_gausers');
		$default_mship  = $params->get('default_mship');
		$hide_vax  = $params->get('hide_vax', 1);
        $prof_suf  = $params->get('profile_suffix');
        $log_actions  = $params->get('log_actions');
        $localprof  = 'profile'.$prof_suf.'.';
        $newApplic  = $params->get('newMember', 0);
        //$temp_group = $params->get('temp_group',0);
        //$temp_mship = $params->get('temp_mship',0);


        // get mship type id reference for use later if needed
        $mship_id = (!empty($data['mship_id'])) ? $data['mship_id'] : $default_mship;
        Factory::getApplication()->setUserState('com_gausers.mship.type.id',$mship_id);

        if ($user_id && $canAdmin) {
            GainvoiceHelper::checkMshipType($user_id, $mship_id);
        }

        if (!isset($data['region']) && $data['postal_code'] > '') {
			//set up state or territory
			Switch (substr($data['postal_code'],0,1)) {
				case 1: $data['region'] = 'ACT'; break;
				case 2: $data['region'] = 'NSW'; break;
				case 3: $data['region'] = 'VIC'; break;
				case 4: $data['region'] = 'QLD'; break;
				case 5: $data['region'] = 'SA'; break;
				case 6: $data['region'] = 'WA'; break;
				case 7: $data['region'] = 'TAS'; break;
				case 8: $data['region'] = 'VIC'; break;
				default: $data['region'] = 'VIC';
			}
		}

        if (!$user_id) {
			// create new user
			$user_id = GaregistrationHelper::setupNewUserData($data);
			if (!$user_id) {
				Factory::getApplication()->enqueueMessage(Text::_('COM_GAUSERS_NEW_USER_FAILED_ID'), 'danger');
                return false;
			} else {
                Factory::getApplication()->enqueueMessage(Text::sprintf('COM_GAUSERS_NEW_USER_SUCCESS_ID',$user_id), 'notice');
        		if ($newApplic) {
                    //set user block to 1 due to assuming a new user record created before payment
                    $this->blockUser($user_id);
                }
    		}
		}

    	// fields specifically for Covid19
        if ($hide_vax) {
            // ignore these fields 
        } else {
            $vax_field1  = $params->get('vax_field1', 0);
            $exempt_field1  = $params->get('exempt_field1', 0);
            $med_cond1  = $params->get('med_cond1');
    		$vax_field2  = $params->get('vax_field2', 0);
            $exempt_field2  = $params->get('exempt_field2', 0);
            $med_cond2  = $params->get('med_cond2');
            if ($vax_field1) {
    			$vaxSet1 = GausersHelper::getCustomFieldValue($vax_field1, $user_id);
    			if (isset($vaxSet1->fieldValue)) {
    				GausersHelper::updateCustomFieldValue($vax_field1, $user_id, $data['vaxed1']);
    			} else {
    				GausersHelper::createCustomFieldValue($vax_field1, $user_id, $data['vaxed1']);
    			}
    		}
            if ($exempt_field1) {
    			$vaxExSet1 = GausersHelper::getCustomFieldValue($exempt_field1, $user_id);
    			if (isset($vaxExSet1->fieldValue)) {
    				GausersHelper::updateCustomFieldValue($exempt_field1, $user_id, $data['vax_exempt1']);
    			} else {
    				GausersHelper::createCustomFieldValue($exempt_field1, $user_id, $data['vax_exempt1']);
    			}
    		}
            if ($med_cond1) {
    			$medCondSet1 = GausersHelper::getCustomFieldValue($med_cond1, $user_id);
    			if (isset($medCondSet1->fieldValue)) {
    				GausersHelper::updateCustomFieldValue($med_cond1, $user_id, $data['medcond1']);
    			} else {
    				GausersHelper::createCustomFieldValue($med_cond1, $user_id, $data['medcond1']);
    			}
    		}
    
            if ($vax_field2) {
    			$vaxSet2 = GausersHelper::getCustomFieldValue($vax_field2, $user_id);
    			if (isset($vaxSet2->fieldValue)) {
    				GausersHelper::updateCustomFieldValue($vax_field2, $user_id, $data['vaxed2']);
    			} else {
    				GausersHelper::createCustomFieldValue($vax_field2, $user_id, $data['vaxed2']);
    			}
    		}
            if ($exempt_field2) {
    			$vaxExSet2 = GausersHelper::getCustomFieldValue($exempt_field2, $user_id);
    			if (isset($vaxExSet2->fieldValue)) {
    				GausersHelper::updateCustomFieldValue($exempt_field2, $user_id, $data['vax_exempt2']);
    			} else {
    				GausersHelper::createCustomFieldValue($exempt_field2, $user_id, $data['vax_exempt2']);
    			}
    		}
            if ($med_cond2) {
    			$medCondSet2 = GausersHelper::getCustomFieldValue($med_cond2, $user_id);
    			if (isset($medCondSet2->fieldValue)) {
    				GausersHelper::updateCustomFieldValue($med_cond2, $user_id, $data['medcond2']);
    			} else {
    				GausersHelper::createCustomFieldValue($med_cond2, $user_id, $data['medcond2']);
    			}
    		}
		}

        // cycle through passed data array to load profile information
        foreach ($data as $key => $value) {
            $localprofkey = $localprof.$key;
            
            // ignore display type fields profile data and just get real data
			Switch ($key) {
				case 'id': /* do nothing */; break;
				case 'name': GausersHelper::updateUser($user_id, $key, $value); break;
				case 'email': GausersHelper::updateUser($user_id, $key, $value); break;
				case 'address1': GausersHelper::updateUserProfile($user_id, 'profile.'.$key, $value); break;
				case 'address2': GausersHelper::updateUserProfile($user_id, 'profile.'.$key, $value); break;
				case 'city': GausersHelper::updateUserProfile($user_id, 'profile.'.$key, $value); break;
				case 'region': GausersHelper::updateUserProfile($user_id, 'profile.'.$key, $value); break;
				case 'postal_code': GausersHelper::updateUserProfile($user_id, 'profile.'.$key, $value); break;
				case 'country': GausersHelper::updateUserProfile($user_id, 'profile.'.$key, $value); break;
				case 'phone': GausersHelper::updateUserProfile($user_id, 'profile.'.$key, $value); break;
				case 'aboutme': GausersHelper::updateUserProfile($user_id, 'profile.'.$key, $value); break;
				case 'favoritebook': GausersHelper::updateUserProfile($user_id, 'profile.'.$key, $value); break;
				case 'website': GausersHelper::updateUserProfile($user_id, 'profile.'.$key, $value); break;
				case 'name_ro': /* do nothing */; break;
				case 'plain_region': /* do nothing */; break;
				case 'm_img_txt': /* do nothing */; break;
				case 'p_img_txt': /* do nothing */; break;
				case 'cert4_dvfile_ro': /* do nothing */; break;
				case 'cert4_dvfilep_ro': /* do nothing */; break;
				case 'cert4_csfile_ro': /* do nothing */; break;
				case 'cert4_csfilep_ro': /* do nothing */; break;
				case 'trg_b2bc_txt': /* do nothing */; break;
				case 'trg_b2bpc_txt': /* do nothing */; break;
				case 'trg_introc_txt': /* do nothing */; break;
				case 'trg_intropc_txt': /* do nothing */; break;
				case 'trg_bioc_txt': /* do nothing */; break;
				case 'trg_biopc_txt': /* do nothing */; break;
				case 'trg_faidc_txt': /* do nothing */; break;
				case 'trg_faidpc_txt': /* do nothing */; break;
				case 'trg_foodc_txt': /* do nothing */; break;
				case 'trg_foodpc_txt': /* do nothing */; break;
				default: GausersHelper::updateUserProfile($user_id, $localprofkey, $value);
			}
        }

        if ($log_actions) {
			$actionlogged = GausersHelper::recordActionLog($user, $user->id, 'member', $user_id);
		}

		GaauditHelper::createAuditTrail($data, $user_id, $params);

        return $user_id;

	}

    public function genMembersDirectory()
	{
        $user = GausersHelper::getSpecificUser();

        if($user->authorise('core.dldir','com_gausers')) {
			$extractOK = GausersHelper::createMembersDirectory();
			if ($extractOK) {
				Factory::getApplication()->enqueueMessage(Text::_('COM_GAUSERS_DIR_GEN_SUCCESSFULLY'));
			}
			return $extractOK;
        }

        return true;

	}

    public function genMembersList()
	{
        $user = GausersHelper::getSpecificUser();

        if($user->authorise('core.dldir','com_gausers')) {
			$extractOK = GalistmembersHelper::createPDF();
			if ($extractOK) {
				Factory::getApplication()->enqueueMessage(Text::_('COM_GAUSERS_DIR_GEN_SUCCESSFULLY'));
			}
			return $extractOK;
        }

        return true;

	}

    public function sendWelcome($user_id = 0)
	{
        if($user_id) {
			GaregistrationHelper::sendNewUserWelcome($user_id);
        }

        return true;

	}

    public function delete($data)
    {
        $id = (!empty($data['id'])) ? $data['id'] : (int)$this->getState('currentuser.id');
        if(GausersHelper::getSpecificUser()->authorise('core.delete', 'com_gausers') !== true){
            throw new \Exception(403, Text::_('JERROR_ALERTNOAUTHOR'));
            return false;
        }
        $table = $this->getTable();
        if ($table->delete($data['id']) === true) {
            return $id;
        } else {
            return false;
        }

        return true;
    }

    public function blockUser($id)
	{
		$db = Factory::getContainer()->get('DatabaseDriver');
		$db->setQuery('UPDATE #__users SET block = 1 WHERE id = '.(int)$id );
	    try {
	        $db->execute();
	        Factory::getApplication()->enqueueMessage('Member Blocked', 'notice');
	    } catch (RuntimeException $e) {
	        Factory::getApplication()->enqueueMessage($e->getMessage().' Member Block Failed');
	        return false;
	    }
        return true;
    }

    public function changeBlockStatus($id)
	{
		$u = GausersHelper::getSpecificUser($id);
        if ($u->block) {
			$block = 0;
	    } else {
		 	$block = 1;
	 	}

		$db = Factory::getContainer()->get('DatabaseDriver');
		$db->setQuery('UPDATE #__users SET block = '.(int)$block.' WHERE id = '.(int)$id );
	    try {
	        $db->execute();
	        Factory::getApplication()->enqueueMessage('Member Status Updated', 'notice');
	    } catch (RuntimeException $e) {
	        Factory::getApplication()->enqueueMessage($e->getMessage().' Member Status Update Failed');
	        return false;
	    }
	    
	    return true;

	}

    /**
     * Create an extract of members based on selected profile elements
     * Standard elements are name, email, address, phone
     * @return bool
     */
    public function generateExtract()
	{
        $params = ComponentHelper::getParams('com_gausers');
        $extract_members  = $params->get('extract_members');

        if($extract_members) {
			//$extractOK = GausersHelper::generateExtract(); // old one
			$extractOK = GausersHelper::createMemberExtract();
			if ($extractOK) {
				Factory::getApplication()->enqueueMessage(Text::_('COM_GAUSERS_EXT_GEN_SUCCESSFULLY'));
			}
			return $extractOK;
        }

        return true;

	}

    /**
     * Collect information about member having passed away
     * @params $data = user_id, user_name, left_date, left_comment
     * @return bool
     */
    public function markAsDied($data)
	{
        $params = ComponentHelper::getParams('com_gausers');
        $deceased_group  = $params->get('deceased_group');
        $deceased_key  = $params->get('deceased_key','mdod');
        $prof_suffix  = $params->get('profile_suffix','b4wdc');
        $local_prof = 'profile'.$prof_suffix;
        $key = 'profile'.$prof_suffix.'.'.$deceased_key;
        
        $u = GausersHelper::getSpecificUser($data['user_id']);

        if (isset($data['single_user'])) {
			// just a single user record to be updated, proceed
			GausersHelper::resetMemberUserGroup($u, $deceased_group);
            GausersHelper::updateUserProfile($data['user_id'], $key, $data['left_date']);
            GausersHelper::unblockUser($u->id, 1);
		} else {
			// duplicate user process
			$u->dec_name = $data['dec_name'];
			$newUserID = GausersHelper::duplicateUserRecord($u, $deceased_group);
			if ($newUserID) {
				GausersHelper::duplicateUserProfile($u->id, $newUserID);
				// use the new id to do the rest.
				GausersHelper::updateUserProfile($newUserID, $key, $data['left_date']);
			}
			$up = UserHelper::getProfile($u->id);
			$partner = isset($up->$local_prof['partner']) ? $up->$local_prof['partner'] : '';
			if ($partner > '') {
				GausersHelper::updateUser($u->id, 'name', $partner);
			}
		}

		// now that data collected and updated, process the audit trace
		$data['comment'] = 'Member Passed Away'."\r\n".'Comment - '.$data['left_comment'];
		$user = GausersHelper::getSpecificUser();

        $data['updated_by'] = $user->id;
		$tran_id = GaauditHelper::createAuditTrail($data, $u->id, $params);
		GaauditHelper::saveMshipActions($data, $data['user_id'], $params);

        // catch an action log step
        GausersHelper::recordActionLog($user, $u->id, 'memberdied', $tran_id);

	}

    /**
     * Collect information about member leaving
     * @params $data = user_id, user_name, left_date, left_reason, left_comment
     * @return bool
     */
    public function markAsLeft($data)
	{
        $params = ComponentHelper::getParams('com_gausers');
        $leftclub_group  = $params->get('leftclub_group');
        $data['act_name'] = 'Left the Club';
		$data['comment'] = $data['act_name']."\r\n".'Reason - '.$data['left_reason']."\r\n".'Comment - '.$data['left_comment'];
		$user = GausersHelper::getSpecificUser();
        // get the user record for the member being updated
		$u = GausersHelper::getSpecificUser($data['user_id']);
        
		// disable user record
		$this->changeBlockStatus($data['user_id']);
		
		// create the audit trial record
		GaauditHelper::createAuditTrail($data, $data['user_id'], $params);
		GaauditHelper::saveMshipActions($data, $u, $params);

		// update the user records usergroup
		GausersHelper::resetMemberUserGroup($u, $leftclub_group);

        // catch an action log
        GausersHelper::recordActionLog($u, $user->id, 'memberleft', $u->id);
	}

    /**
     * Reset member as having returned
     * @params $id = user_id
     * @return bool
     */
    public function markAsReturned($id)
	{
        $params = ComponentHelper::getParams('com_gausers');
        $sendto_group  = $params->get('sendto_group');
		$user = GausersHelper::getSpecificUser();

        // get the user record for the member being updated
		$u = GausersHelper::getSpecificUser($id);
        $data = array('left_reason'=>'', 'left_comment'=>'Returned from sabbatical', 'act_name'=>'Returned to the Club' );

		// create the audit trial record
		GaauditHelper::createAuditTrail($data, $id, $params);
		GaauditHelper::saveMshipActions($data, $u, $params);

		// update the user records usergroup
		GausersHelper::resetMemberUserGroup($u, $sendto_group);

        // catch an action log
        GausersHelper::recordActionLog($u, $user->id, 'memberreturned', $id);
	}

    /**
     * Convert member from temporary
     * @params $id = user_id
     * @return bool
     */
    public function convertMship($id)
	{
        $params = ComponentHelper::getParams('com_gausers');
        $sendto_group  = $params->get('sendto_group');
        $mship_id  = $params->get('temp_mship');
        $default_mship  = $params->get('default_mship');
        $invoice_date  = $params->get('invoice_date');
		$user = GausersHelper::getSpecificUser();

        // get the user record for the member being updated
		$u = GausersHelper::getSpecificUser($id);
        $data = array('left_reason'=>'', 'left_comment'=>'Converted from temporary membership', 'act_name'=>'Convert from Temporary' );

		// create the audit trial record
		GaauditHelper::createAuditTrail($data, $id, $params);
		GaauditHelper::saveMshipActions($data, $u, $params);

		// update the user records usergroup
		GausersHelper::resetMemberUserGroup($u, $sendto_group);

        // catch an action log
        GausersHelper::recordActionLog($u, $user->id, 'convertmember', $id);
        
        // check mship type and generate invoice if necessary
        $inv = GainvoiceHelper::getLastInvoiceMship($u->id);
        if ($inv) {
            // get default mship details
            $dMship = GausersHelper::getMshiptypeID($default_mship);
            $mshipDate = new Date(strtotime($invoice_date));

            // calc new end date
            $eDate = $mshipDate->modify('+'.$dMship->mship_term.' '.$dMship->term_type);
            $expDate = date_format($eDate,'Y-m-d H:i:s');

            // calc difference between mships (default & temp)
            $amt = ($dMship->subscrib_amt + $dMship->joining_fee) - $inv->invoice_amt;

            if ($amt == 0) { return true; }
    
            $invRec = GainvoiceHelper::createNewInvoiceRec($u->id, $amt, $dMship, $expDate);

    	    $nextinv  = str_pad($invRec->id, 6, '0', STR_PAD_LEFT);
    		Factory::getApplication()->setUserState('com_gausers.nextinv.data', $nextinv);
    
            $data = array();
    		$data['nextinv'] = $nextinv;
    		$data['invRec'] = $invRec;
    		$data['mship'] = $dMship;
    		$data['conversion'] = 1;

            return GainvoiceHelper::createAdHocPDF($data, $id, $params);
        }
	}

    /**
     * Generate an INDIVIDUAL Invoice for a member (or a new member)
     * This is called from the user profile plugin onUserAfterSave() so that an
     * invoice can be created when public can create an account directly as well
     * as triggered from the Users/Members list - individual invoice button.  This
     * is NOT used when generating bulk invoices from the invoice list view.
     * @params $id = submitted user id from the form (new user means this will be 0)
     * @params $newId = new user record id
     * @return bool
     */
    public function generateInvoice($id, $newId)
	{
        $params = ComponentHelper::getParams('com_gausers');
        $defMship = $params->get('default_mship',1);
        $exEmailPref = $params->get('exclude_email_pref','noemail');
        $profSuffix = $params->get('profile_suffix','b4wdc');
        $locProf = 'profile'.$profSuffix;
        $mship_id = Factory::getApplication()->getUserState('com_gausers.mship.type.id');
		$u = GausersHelper::getSpecificUser($newId);

        $u->user_id = $u->id;

        $up = UserHelper::getProfile($u->id);
		// cycle through profile elements and load into main user object
		foreach ($up->profile AS $key => $value ) {
            $u->$key = $value;
            if ($key == 'city') { $u->suburb = $value; }
            if ($key == 'postal_code') { $u->pcode = $value; }
		}
		foreach ($up->$locProf AS $key => $value ) {
			$u->$key = $value;
            if ($key == 'postal_city') { $u->postal_suburb = $value; }
            if ($key == 'postal_post_code') { $u->postal_pcode = $value; }
		}

        // if new, then get default else get last mship record
        if ($id) {
            $mship = GainvoiceHelper::getLastInvoiceMship($u->id);
            if (empty($mship)) {
                $mship = GausersHelper::getMshiptypeID($defMship);
            }
        } else {
            if (!empty($mship_id)) {$defMship = $mship_id; }
            $mship = GausersHelper::getMshiptypeID($defMship);
        }

        $u->mship = $mship;

        Factory::getApplication()->setUserState('com_gausers.user.data', $u);

        $attachfile = GainvoiceHelper::mainInvoiceCreation($id, $u, $params);

        if ($attachfile == false) {
            Factory::getApplication()->enqueueMessage(Text::_('COM_GAUSERS_NO_INV_CREATED'), 'danger');
            return true;
        }

		if (substr($u->email,0,strlen($exEmailPref)) != $exEmailPref) {
            $subject = Text::_('COM_GAUSERS_INVOICE_EMAIL_SUBJECT');
            $body = Text::sprintf('COM_GAUSERS_INVOICE_EMAIL_SALUTATION',$u->name);
            $body .= Text::_('COM_GAUSERS_INVOICE_EMAIL_BODY');
    		$sentOK = GaemailHelper::sendEmail(array($u->email), $body, $subject, $attachfile);
		} else {
            Factory::getApplication()->enqueueMessage(Text::sprintf('COM_GAUSERS_USER_HAS_NO_EMAIL', $u->name));
        }

        return true;

	}

    public function uplattachfile($data)
	{
        if(GausersHelper::getSpecificUser()->authorise('core.attupload', 'com_gausers') !== true){
            throw new \Exception(403, Text::_('JERROR_ALERTNOAUTHOR'));
            return false;
        }

		$return = GauserverifyHelper::uplattachfile($data);
		
		return $return;
    }

	/**
	 * Method to delete an item
	 * @param   int $id Element id
	 * @return  bool
	 */
	public function deleteImgFile($id, $fileDel)
	{
		$params  = ComponentHelper::getParams('com_gausers');

        $result = GaimgmgmntHelper::deleteImgFile($id, $fileDel, $params);

        return $result;

	}

	/**
	 * Method to delete an item
	 * @param   int $id Element id
	 * @return  bool
	 */
	public function updPword($data)
	{
		$userId = (!empty($data['id'])) ? $data['id'] : (int) $this->getState('currentuser.id');
        $user = new User($userId);
		
		$data['password'] = $data['password1'];

		// Bind the data.
		if (!$user->bind($data)) {
			$this->setError($user->getError());
			return false;
		}

		// Store the data.
		if (!$user->save()) {
			$this->setError($user->getError());
			return false;
		}

        // Destroy all active sessions for the user after changing the password
		if ($data['password']) {
			UserHelper::destroyUserSessions($user->id, true);
		}

        return true;

	}

    /**
     * Collect information about member leaving
     * @params $data = user_id, user_name, left_date, left_reason, left_comment
     * @return bool
     */
    public function createAction($data)
	{
		// get the current date-time based on timezone
		$date = GausersHelper::getTodaysDate();
		$today = date_format($date,'Y-m-d H:i:s');
		$u = GausersHelper::getSpecificUser();

		$newAct = new \stdClass();
		$newAct->id=0;
		$newAct->ordering=0;
		$newAct->state=1;
		$newAct->checked_out=0;
		$newAct->created_by=$u->id;
		$newAct->created_date=$today;
		$newAct->user_id = $data['user_id'];
		$newAct->cat_id=0;
		$newAct->act_name= $data['act_name'];
		$newAct->comment = '<p>' . $data['comment'] . '</p>';

	    try {
	        $result = Factory::getContainer()->get('DatabaseDriver')->insertObject('#__gausers_actions', $newAct);
	    } catch (RuntimeException $e) {
	        Factory::getApplication()->enqueueMessage($e->getMessage());
	    }

        return $newAct->id;
	}

    public function genAddressList()
	{
        $user = GausersHelper::getSpecificUser();

        if($user->authorise('core.dldir','com_gausers')) {
			$extractOK = GaaddresslistHelper::createNameList();
			if ($extractOK) {
				Factory::getApplication()->enqueueMessage(Text::_('COM_GAUSERS_ADDRESS_GEN_SUCCESSFULLY'));
			}
			return $extractOK;
        }

        return true;

	}

    public function genMailChimpList()
	{
        $user = GausersHelper::getSpecificUser();

        if($user->authorise('core.dldir','com_gausers')) {
			$extractOK = GamailchimplistHelper::createList();
			if ($extractOK) {
				Factory::getApplication()->enqueueMessage(Text::_('COM_GAUSERS_MAILCHIMP_GEN_SUCCESSFULLY'));
			}
			return $extractOK;
        }

        return true;

	}

}
