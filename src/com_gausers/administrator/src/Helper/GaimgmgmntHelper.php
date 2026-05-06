<?php

/**
 * @version     5.1.6
 * @package     com_gausers
 * @copyright   Copyright (C) 2013. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Glenn Arkell <glenn@glennarkell.com.au> - http://www.glennarkell.com.au
**/

namespace GlennArkell\Component\Gausers\Administrator\Helper;

defined( '_JEXEC' ) or die( 'Restricted access' );

use \Joomla\CMS\Factory;
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\Component\ComponentHelper;
use \Joomla\Utilities\ArrayHelper;
use \Joomla\CMS\User\UserHelper;
use \Joomla\CMS\Access\Access;
use \Joomla\Filesystem\File;
use \Joomla\Filesystem\Folder;
use \Joomla\Filesystem\Path;
use \GlennArkell\Component\Gausers\Administrator\Helper\GausersHelper;

class GaimgmgmntHelper
{
	/**
	 * Method to delete an image
	 * @param   int $id record reference id
	 * @param   string $fileDel file type to be removed
	 * @param   object $params component parameters
	 * @return  bool
	 ****** WARNING this helper is also used by the plugin plg_user_profileb4wdc  *************
	 */
	public static function deleteImgFile($id = 0, $fileDel = 'm', $params = null)
	{
		if (!$id) {
			Factory::getApplication()->enqueueMessage(Text::_('File Reference Missing'), 'danger');
			return false;
		} else {
			$delete_file = $params->get('delete_file', 0);
			$profile_suffix = $params->get('profile_suffix', 'b4wdc');
			$locProf = 'profile'.$profile_suffix;
			$result = false;

			$u = GausersHelper::getSpecificUser($id);
	        $up = UserHelper::getProfile($id);
	
			// Checking if the user can remove object
			$user = GausersHelper::getSpecificUser();
			$canEdit = $user->authorise('core.edit', 'com_gausers');
			$canEditOwn = $user->authorise('core.edit.own', 'com_gausers');
			if (!$canEdit) {
	            if ($canEditOwn && ($user->id == $u->id)) { $canEdit = true; }
			}
			$prof_key = $locProf.'.'.$fileDel.'_img';
			$file_name = $up->$locProf[$fileDel.'_img'];

	 		if ($canEdit) {
				// update profile field
				$db = Factory::getContainer()->get('DatabaseDriver');
				$db->setQuery('UPDATE #__user_profiles SET profile_value = "" WHERE user_id = '.(int) $id . ' AND profile_key = '.$db->Quote($prof_key));
				if (!$db->execute()) {
					$delete_OK = false;
					Factory::getApplication()->enqueueMessage(Text::_('Profile Update Failed'), 'danger');
				} else {
					$delete_OK = true;
				}

				if ($delete_OK) {
					$result = $id;
					// delete the physical file
					$tran_file = Path::clean( JPATH_SITE . '/' . $file_name );
					if (file_exists($tran_file)) {
						if ($delete_file) {
							File::delete($tran_file);
						}
					}
				} else {
					$result = false;
				}

			} else {
				$result = false;
				Factory::getApplication()->enqueueMessage(Text::_('Not Authorised to Edit this Record'), 'danger');
			}

	        return $result;
		}

	}

    /**
     * Method to determine image settings & upload them
     * Used in CurrentuserformController
     * @param   array  $data is the submitted form array details
	 * @param   array $files is the submitted files array details
	 * @param   array $data data array with loaded files information
     */
    public static function idAndSetFiles($data = null, $files = null)
    {
		$params = ComponentHelper::getParams('com_gausers');
        $localProfile = $params->get('profile_suffix','b4wdc');

		// Four Wheel Drive Clubs using standard profile4wdc profile
        if ($localProfile == 'b4wdc' ) {
			// member images saved to profile
            $m_img	= ArrayHelper::getValue($files, 'm_img', '', 'array');
			$p_img	= ArrayHelper::getValue($files, 'p_img', '', 'array');
			if ($m_img['size']) {
				$data['m_img'] = self::uploadAttachment($m_img, $params, $data['id'].'m_img');
			} else {
				if (empty($data['m_img_disp'])) {
					$data['m_img'] = '';
				} else {
					$data['m_img'] = $data['m_img_disp'];
				}
			}
			if ($p_img['size']) {
				$data['p_img'] = self::uploadAttachment($p_img, $params, $data['id'].'p_img');
			} else {
				if (empty($data['p_img_disp'])) {
					$data['p_img'] = '';
				} else {
					$data['p_img'] = $data['p_img_disp'];
				}
			}

			// certificate images saved to profile
            $cert4_dvfile	= ArrayHelper::getValue($files, 'cert4_dvfile', '', 'array');
			$cert4_dvfilep	= ArrayHelper::getValue($files, 'cert4_dvfilep', '', 'array');
			$cert4_csfile	= ArrayHelper::getValue($files, 'cert4_csfile', '', 'array');
			$cert4_csfilep	= ArrayHelper::getValue($files, 'cert4_csfilep', '', 'array');
			if ($cert4_dvfile['size']) {
				$data['cert4_dvfile'] = self::uploadAttachment($cert4_dvfile, $params, $data['id'].'c4dv');
			} else {
				if (empty($data['cert4_dvfile_ro'])) {
					$data['cert4_dvfile'] = '';
				} else {
					$data['cert4_dvfile'] = $data['cert4_dvfile_ro'];
				}
			}
			if ($cert4_dvfilep['size']) {
				$data['cert4_dvfilep'] = self::uploadAttachment($cert4_dvfilep, $params, $data['id'].'c4dvp');
			} else {
				if (empty($data['cert4_dvfilep_ro'])) {
					$data['cert4_dvfilep'] = '';
				} else {
					$data['cert4_dvfilep'] = $data['cert4_dvfilep_ro'];
				}
			}
			if ($cert4_csfile['size']) {
				$data['cert4_csfile'] = self::uploadAttachment($cert4_csfile, $params, $data['id'].'c4cs');
			} else {
				if (empty($data['cert4_csfile_ro'])) {
					$data['cert4_csfile'] = '';
				} else {
					$data['cert4_csfile'] = $data['cert4_csfile_ro'];
				}
			}
			if ($cert4_csfilep['size']) {
				$data['cert4_csfilep'] = self::uploadAttachment($cert4_csfilep, $params, $data['id'].'c4csp');
			} else {
				if (empty($data['cert4_csfilep_ro'])) {
					$data['cert4_csfilep'] = '';
				} else {
					$data['cert4_csfilep'] = $data['cert4_csfilep_ro'];
				}
			}

		// Rotary Club images
        } elseif ($localProfile == 'rtry' ) {
			$m_img	= ArrayHelper::getValue($files, 'm_img', '', 'array');
			if ($m_img['size']) {
				$data['m_img'] = self::uploadAttachment($m_img, $params, $data['id'].'m_img');
			} else {
				if (empty($data['m_img_disp'])) {
					$data['m_img'] = '';
				} else {
					$data['m_img'] = $data['m_img_disp'];
				}
			}

		// DOCs Club images
        } elseif ($localProfile == 'docs' ) {
			$m_img	= ArrayHelper::getValue($files, 'm_img', '', 'array');
			if ($m_img['size']) {
				$data['m_img'] = self::uploadAttachment($m_img, $params, $data['id'].'m_img');
			} else {
				if (empty($data['m_img_disp'])) {
					$data['m_img'] = '';
				} else {
					$data['m_img'] = $data['m_img_disp'];
				}
			}

		// Beekeeping Club training images
		} elseif ($localProfile == 'brb' ) {
			// member images saved to profile
            $m_img	= ArrayHelper::getValue($files, 'm_img', '', 'array');
			if ($m_img['size']) {
				$data['m_img'] = self::uploadAttachment($m_img, $params, $data['id'].'m_img');
			} else {
				if (empty($data['m_img_disp'])) {
					$data['m_img'] = '';
				} else {
					$data['m_img'] = $data['m_img_disp'];
				}
			}
			$trg_b2bc = ArrayHelper::getValue($files, 'trg_b2bc', '', 'array');
			$trg_b2bpc = ArrayHelper::getValue($files, 'trg_b2bpc', '', 'array');
			$trg_introc = ArrayHelper::getValue($files, 'trg_introc', '', 'array');
			$trg_intropc = ArrayHelper::getValue($files, 'trg_intropc', '', 'array');
			$trg_bioc = ArrayHelper::getValue($files, 'trg_bioc', '', 'array');
			$trg_biopc = ArrayHelper::getValue($files, 'trg_biopc', '', 'array');

			if ($trg_b2bc['size']) {
				$data['trg_b2bc'] = self::uploadAttachment($trg_b2bc, $params, $data['id'].'b2b');
			} else {
				if (empty($data['trg_b2bc_txt'])) {
					$data['trg_b2bc'] = '';
				} else {
					$data['trg_b2bc'] = $data['trg_b2bc_txt'];
				}
			}

			if ($trg_b2bpc['size']) {
				$data['trg_b2bpc'] = self::uploadAttachment($trg_b2bpc, $params, $data['id'].'b2bp');
			} else {
				if (empty($data['trg_b2bpc_txt'])) {
					$data['trg_b2bpc'] = '';
				} else {
					$data['trg_b2bpc'] = $data['trg_b2bpc_txt'];
				}
			}

			if ($trg_introc['size']) {
				$data['trg_introc'] = self::uploadAttachment($trg_introc, $params, $data['id'].'intro');
			} else {
				if (empty($data['trg_introc_txt'])) {
					$data['trg_introc'] = '';
				} else {
					$data['trg_introc'] = $data['trg_introc_txt'];
				}
			}

			if ($trg_intropc['size']) {
				$data['trg_intropc'] = self::uploadAttachment($trg_intropc, $params, $data['id'].'introp');
			} else {
				if (empty($data['trg_intropc_txt'])) {
					$data['trg_intropc'] = '';
				} else {
					$data['trg_intropc'] = $data['trg_intropc_txt'];
				}
			}

			if ($trg_bioc['size']) {
				$data['trg_bioc'] = self::uploadAttachment($trg_bioc, $params, $data['id'].'bio');
			} else {
				if (empty($data['trg_bioc_txt'])) {
					$data['trg_bioc'] = '';
				} else {
					$data['trg_bioc'] = $data['trg_bioc_txt'];
				}
			}

			if ($trg_biopc['size']) {
				$data['trg_biopc'] = self::uploadAttachment($trg_biopc, $params, $data['id'].'biop');
			} else {
				if (empty($data['trg_biopc_txt'])) {
					$data['trg_biopc'] = '';
				} else {
					$data['trg_biopc'] = $data['trg_biopc_txt'];
				}
			}
		}

		// Other images standard with several groups profiles
        $trg_faidc = ArrayHelper::getValue($files, 'trg_faidc', '', 'array');
		$trg_faidpc = ArrayHelper::getValue($files, 'trg_faidpc', '', 'array');
		$trg_foodc = ArrayHelper::getValue($files, 'trg_foodc', '', 'array');
		$trg_foodpc = ArrayHelper::getValue($files, 'trg_foodpc', '', 'array');
		$wwk_reg = ArrayHelper::getValue($files, 'wwk_reg', '', 'array');
		$wwk_regp = ArrayHelper::getValue($files, 'wwk_regp', '', 'array');

		// Working with Children images
        if ($wwk_reg['size']) {
			$data['wwk_reg'] = self::uploadAttachment($wwk_reg, $params, $data['id'].'wwk');
		} else {
			if (empty($data['wwk_reg_txt'])) {
				$data['wwk_reg'] = '';
			} else {
				$data['wwk_reg'] = $data['wwk_reg_txt'];
			}
		}
        if ($wwk_regp['size']) {
			$data['wwk_regp'] = self::uploadAttachment($wwk_regp, $params, $data['id'].'wwkp');
		} else {
			if (empty($data['wwk_regp_txt'])) {
				$data['wwk_regp'] = '';
			} else {
				$data['wwk_regp'] = $data['wwk_regp_txt'];
			}
		}

		// First Aid training images
        if ($trg_faidc['size']) {
			$data['trg_faidc'] = self::uploadAttachment($trg_faidc, $params, $data['id'].'faid');
		} else {
			if (empty($data['trg_faidc_txt'])) {
				$data['trg_faidc'] = '';
			} else {
				$data['trg_faidc'] = $data['trg_faidc_txt'];
			}
		}

		if ($trg_faidpc['size']) {
			$data['trg_faidpc'] = self::uploadAttachment($trg_faidpc, $params, $data['id'].'faidp');
		} else {
			if (empty($data['trg_faidpc_txt'])) {
				$data['trg_faidpc'] = '';
			} else {
				$data['trg_faidpc'] = $data['trg_faidpc_txt'];
			}
		}

		// Food Handlers images
        if ($trg_foodc['size']) {
			$data['trg_foodc'] = self::uploadAttachment($trg_foodc, $params, $data['id'].'food');
		} else {
			if (empty($data['trg_foodc_txt'])) {
				$data['trg_foodc'] = '';
			} else {
				$data['trg_foodc'] = $data['trg_foodc_txt'];
			}
		}

		if ($trg_foodpc['size']) {
			$data['trg_foodpc'] = self::uploadAttachment($trg_foodpc, $params, $data['id'].'foodp');
		} else {
			if (empty($data['trg_foodpc_txt'])) {
				$data['trg_foodpc'] = '';
			} else {
				$data['trg_foodpc'] = $data['trg_foodpc_txt'];
			}
		}

        return $data;
	}

    /**
     * Method to check if image already set in the load form step
	 * @param   object $item from the getItem in the model
	 * @return   object $item loaded with image info
     */
    public static function checkForImage($item = null)
    {
		if (isset($item->m_img) && $item->m_img > '') {
			$item->m_img_disp = $item->m_img;
		} else {
			$item->m_img = '';
            $item->m_img_disp = 0;
		}

		if (isset($item->p_img) && $item->p_img > '') {
			$item->p_img_disp = $item->p_img;
		} else {
			$item->p_img_disp = 0;
		}

		if (isset($item->trg_bioc) && $item->trg_bioc > '') {
			$item->trg_bioc_txt = $item->trg_bioc;
		} else {
			$item->trg_bioc_txt = 0;
		}

		if (isset($item->trg_biopc) && $item->trg_biopc > '') {
			$item->trg_biopc_txt = $item->trg_biopc;
		} else {
			$item->trg_biopc_txt = 0;
		}

		if (isset($item->trg_introc) && $item->trg_introc > '') {
			$item->trg_introc_txt = $item->trg_introc;
		} else {
			$item->trg_introc_txt = 0;
		}

		if (isset($item->trg_intropc) && $item->trg_intropc > '') {
			$item->trg_intropc_txt = $item->trg_intropc;
		} else {
			$item->trg_intropc_txt = 0;
		}

		if (isset($item->trg_faidc) && $item->trg_faidc > '') {
			$item->trg_faidc_txt = $item->trg_faidc;
		} else {
			$item->trg_faidc_txt = 0;
		}

		if (isset($item->trg_faidpc) && $item->trg_faidpc > '') {
			$item->trg_faidpc_txt = $item->trg_faidpc;
		} else {
			$item->trg_faidpc_txt = 0;
		}

		if (isset($item->trg_foodc) && $item->trg_foodc > '') {
			$item->trg_foodc_txt = $item->trg_foodc;
		} else {
			$item->trg_foodc_txt = 0;
		}

		if (isset($item->trg_foodpc) && $item->trg_foodpc > '') {
			$item->trg_foodpc_txt = $item->trg_foodpc;
		} else {
			$item->trg_foodpc_txt = 0;
		}

		return $item;
	}

    /**
     * Method to upload an image
     * @param   array  $tran_file is the file array details
	 * @param   object $params component parameters
     */
    public static function uploadAttachment($tran_file = null, $params = null, $mbrId = '')
    {
        $imgdir  = $params->get( 'img_location', 'images');
        $safeFileOptions  = $params->get( 'safe_imgs' );
		$file_ext = substr($tran_file['name'],-3);

		if (!in_array($file_ext, $safeFileOptions)) {
			Factory::getApplication()->enqueueMessage(Text::_('File format ('.$file_ext.') not allowed'), 'danger');
			return false;
		}

        if (file_exists('file://'.$tran_file['tmp_name'])) {
			$fileName = File::makeSafe($tran_file['name']);
			$fileName = str_replace(' ', '_', $fileName);
			$src = $tran_file['tmp_name'];
			$fileName = $imgdir.'/Mbr'.$mbrId.'_'.$fileName;

			$path = Path::clean( JPATH_SITE . '/' );
			$destfile = $path.'/'.$fileName;

			if ( File::upload($src, $destfile, false, false, $safeFileOptions) ) {
				Factory::getApplication()->enqueueMessage(Text::_('File ('.$fileName.') Uploaded Successfully'), 'success');
				return $fileName;
			} else {
				Factory::getApplication()->enqueueMessage(Text::_('File ('.$fileName.') Upload Failed'), 'danger');
				return null;
			}
  		} else {
			Factory::getApplication()->enqueueMessage(Text::_('File ('.$fileName.') Does Not Exist'), 'danger');
			return null;
		}
    }

}
?>
