<?php
/**
 * @version     4.3.3
 * @package     com_gabroadcast
 * @author     Glenn Arkell <glenn@glennarkell.com.au>
 * @copyright  2019 Glenn Arkell
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace GlennArkell\Component\Gabroadcast\Site\Model;

// No direct access.
\defined('_JEXEC') or die;

use \Joomla\CMS\Factory;
use \Joomla\Utilities\ArrayHelper;
use \Joomla\CMS\MVC\Model\FormModel;
use \Joomla\CMS\Table\Table;
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\Component\ComponentHelper;
use \Joomla\CMS\Uri\Uri;
use \Joomla\Filesystem\File;
use \Joomla\Filesystem\Folder;
use \Joomla\Filesystem\Path;
use \Joomla\CMS\User\UserHelper;
use \GlennArkell\Component\Gabroadcast\Administrator\Helper\GabroadcastHelper;

/**
 * Gabroadcast model.
 *
 * @since  1.6
 */
class UsernewformModel extends FormModel
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
        $app = Factory::getApplication('com_gabroadcast');

        // Load state from the request userState on edit or from the passed variable on default
        if (Factory::getApplication()->input->get('layout') == 'edit') {
            $id = Factory::getApplication()->getUserState('com_gabroadcast.edit.usernew.id');
        } else {
            $id = Factory::getApplication()->input->get('id');
            Factory::getApplication()->setUserState('com_gabroadcast.edit.usernew.id', $id);
        }

        $this->setState('usernew.id', $id);

        // Load the parameters.
        $params       = $app->getParams();
        $params_array = $params->toArray();

        if (isset($params_array['item_id'])) {
                $this->setState('usernew.id', $params_array['item_id']);
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
            $this->item = false;

            if (empty($id)) {
                    $id = $this->getState('usernew.id');
            }

            // Get a level row instance.
            $table = $this->getTable();

            if ($table !== false && $table->load($id)) {
                $user = Factory::getApplication()->getIdentity();
                $id   = $table->id;

                $canEdit = $user->authorise('core.edit', 'com_gabroadcast') || $user->authorise('core.create', 'com_gabroadcast');

                if (!$canEdit && $user->authorise('core.edit.own', 'com_gabroadcast')) {
                        $canEdit = $user->id == $table->created_by;
                }

                if (!$canEdit) {
                        throw new \Exception(Text::_('JERROR_ALERTNOAUTHOR'), 403);
                }

                // Check published state.
                if ($published = $this->getState('filter.published')) {
                        if (isset($table->state) && $table->state != $published) {
                                return $this->item;
                        }
                }

                // Convert the Table to a clean Object.
                $properties = $table->getProperties(1);
                $this->item = ArrayHelper::toObject($properties, 'stdClass');
                
            }
        }

        return $this->item;
    }

    /**
     * Method to get the table
     * @param   string $type   Name of the Table class
     * @param   string $prefix Optional prefix for the table class name
     * @param   array  $config Optional configuration array for Table object
     * @return  Table|boolean Table if found, boolean false on failure
     */
    public function getTable($type = 'Usernew', $prefix = 'Administrator', $config = array())
    {
        return parent::getTable($type, $prefix, $config);
    }

    /**
     * Get an item by alias
     * @param   string $alias Alias string
     * @return int Element id
     */
    public function getItemIdByAlias($alias)
    {
        $table      = $this->getTable();
        $properties = $table->getProperties();

        if (!in_array('alias', $properties)) {
                return null;
        }

        $table->load(array('alias' => $alias));

        return $table->id;

    }

    /**
     * Method to check in an item.
     * @param   integer $id The id of the row to check out.
     * @return  boolean True on success, false on failure.
     * @since    1.6
     */
    public function checkin($id = null)
    {
        // Get the id.
        $id = (!empty($id)) ? $id : (int) $this->getState('usernew.id');
        
        if ($id) {
            // Initialise the table
            $table = $this->getTable();

            // Attempt to check the row in.
            if (method_exists($table, 'checkin')) {
                if (!$table->checkin($id)) {
                    return false;
                }
            }
        }

        return true;
        
    }

    /**
     * Method to check out an item for editing.
     * @param   integer $id The id of the row to check out.
     * @return  boolean True on success, false on failure.
     * @since    1.6
     */
    public function checkout($id = null)
    {
        // Get the user id.
        $id = (!empty($id)) ? $id : (int) $this->getState('usernew.id');
        
        if ($id) {
            // Initialise the table
            $table = $this->getTable();

            // Get the current user object.
            $user = Factory::getApplication()->getIdentity();

            // Attempt to check the row out.
            if (method_exists($table, 'checkout')) {
                if (!$table->checkout($user->id, $id)) {
                    return false;
                }
            }
        }

        return true;
        
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
        $form = $this->loadForm('com_gabroadcast.usernew', 'usernewform', array(
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
        $data = Factory::getApplication()->getUserState('com_gabroadcast.edit.usernew.data', array());

        if (empty($data)) {
            $data = $this->getItem();
        }
        
        if ($data) {
            return $data;
        }

        return array();
    }

    /**
     * Method to save the form data.
     * @param   array $data The form data
     * @return bool
     */
    public function save($data)
    {
        $user  = Factory::getApplication()->getIdentity();
		$today = Factory::getDate()->toSql();
        $id    = (!empty($data['id'])) ? $data['id'] : (int) $this->getState('usernew.id');

        if ($id) {
            // Check the user can edit this item
            $authorised = ($user->authorise('core.edit', 'com_gabroadcast') || $user->authorise('core.edit.own', 'com_gabroadcast'));
        } else {
            // Check the user can create new items in this section
            $authorised = $user->authorise('core.create', 'com_gabroadcast');
        }

        if ($authorised !== true) {
            //throw new \Exception(Text::_('JERROR_ALERTNOAUTHOR'), 403);
            Factory::getApplication()->enqueueMessage(Text::_('JERROR_ALERTNOAUTHOR'), 'danger');
            return false;
        }

        $params = ComponentHelper::getParams('com_gabroadcast');
        $incl_unsub  = $params->get('incl_unsub', 0);
        $unsub_article  = $params->get('unsub_article', 0);
        $limit_set = $params->get('limit_set',0);
        $filter_users = $params->get('filter_users',0);

        if (!empty($data['incl_article'])) {
            $article = GabroadcastHelper::getArticle($data['incl_article']);
            $article->introtext = str_replace('src="images', 'src="'.Uri::base().'images', $article->introtext);
            $data['news_detail'] = $data['news_detail'].'<h4>'.$article->title.'</h4>'.$article->introtext;
		}

        if ($incl_unsub && $unsub_article) {
			$data['news_detail'] .= '<p style="font-size:0.6em;text-align:center;"><a class="unsubscribe" href="';
            $data['news_detail'] .= Uri::base().'index.php?option=com_content&view=article&id='.$unsub_article.'" alt="" target="_blank" >unsubscribe</a></p>';
		}

        $bc_type = Factory::getApplication()->getUserState('com_gabroadcast.bcasttype.id', 1);
        $bc = GabroadcastHelper::getBcastTypes($bc_type);
        Factory::getApplication()->setUserState('com_gabroadcast.bcasttype.id', null);

        $newRec = array();
        $newRec['id'] = $id;
        $newRec['state'] = (!empty($data['state'])) ? $data['state'] : 1;
        $newRec['created_by'] = (!empty($data['created_by'])) ? $data['created_by'] : $user->id;
        $newRec['created_date'] = (!empty($data['created_date'])) ? $data['created_date'] : $today;
        $newRec['cat_id'] = $data['cat_id'];
        $newRec['fin_users_only'] = $data['fin_users_only'];
        $newRec['news_subject'] = $data['news_subject'];
        $newRec['user_custfld'] = $data['user_custfld'];
        $newRec['news_detail'] = $data['news_detail'];
        $newRec['attach_file'] = '';

        if (!empty($data['attach_file'])) {
            $data['filename'] = $data['attach_file'];
            $areaGroup = (isset($data['user_proffld']) && !empty($data['user_proffld'])) ? strtolower($data['user_proffld']) : false;
            if ($areaGroup) {
    			$newRec['attach_file'] = $bc->attach_dir.'/'.$areaGroup.'/'.$data['attach_file'];
    			$data['attach_file'] = $bc->attach_dir.'/'.$areaGroup.'/'.$data['attach_file'];
            } else {
    			$newRec['attach_file'] = $bc->attach_dir.'/'.$data['attach_file'];
    			$data['attach_file'] = $bc->attach_dir.'/'.$data['attach_file'];
    		}
		}

        $table = $this->getTable();

        if ($table->save($newRec) === true)
        {
			$data['id'] = $table->id;
            //GabroadcastHelper::addNews($data, $params, $user);
            GabroadcastHelper::createNewsEmail($data, $params, $user);

            return $table->id;
        } else {
            return false;
        }
        
    }

    /**
     * Method to delete data
     * @param   int $pk Item primary key
     * @return  int  The id of the deleted item
     * @throws Exception
     * @since 1.6
     */
    public function delete($pk)
    {
        $user = Factory::getApplication()->getIdentity();

        
        if (empty($pk)) {
            $pk = (int) $this->getState('usernew.id');
        }

        if ($pk == 0 || $this->getItem($pk) == null) {
            throw new \Exception(Text::_('COM_GABROADCAST_ITEM_DOESNT_EXIST'), 404);
        }

        if ($user->authorise('core.delete', 'com_gabroadcast') !== true) {
            throw new \Exception(Text::_('JERROR_ALERTNOAUTHOR'), 403);
        }

        $table = $this->getTable();
        $table->load($pk);
        $table->state = -2;

        if ($table->store($pk) !== true) {
            throw new \Exception(Text::_('JERROR_FAILED'), 501);
        }

        return $pk;
        
    }

    /**
     * Check if data can be saved
     * @return bool
     */
    public function getCanSave()
    {
        $table = $this->getTable();

        return $table !== false;
    }
    
    public function uplattachfile($data)
	{
        $user = Factory::getApplication()->getIdentity();
        if($user->authorise('core.attupload', 'com_gabroadcast') !== true){
            Factory::getApplication()->enqueueMessage(Text::_('JERROR_ALERTNOAUTHOR'), 'danger');
            return false;
        }

        $params = ComponentHelper::getParams('com_gabroadcast');
        $safeFileOptions  = $params->get( 'safe_files' );
        $safeFileOptions  = $params->get( 'safe_files' );
        $filterType = $params->get('filter_type','p');
        $filterUsers = $params->get('filter_users',0);

		//$file_ext = substr($data['bcfile_name']['name'],-3);
		$file_ext = \pathinfo($data['bcfile_name']['name'], PATHINFO_EXTENSION);

        $profile_suffix = $params->get('profile_suffix','b4wdc');
        $locProf = 'profile'.$profile_suffix;
        $locGrps = $params->get('prof_field','locgrp');
        $locGrp = \explode('.',$locGrps);

        $profile = UserHelper::getProfile($user->id);

		if (!in_array(\strtolower($file_ext ?? ''), $safeFileOptions)) {
			Factory::getApplication()->enqueueMessage(Text::_('File format ('.$file_ext.') not allowed'), 'danger');
			return false;
		}

        if (file_exists('file://'.$data['bcfile_name']['tmp_name'])) {
			$fileName = File::makeSafe($data['bcfile_name']['name']);
			$fileName = \str_replace(' ', '_', $fileName);
			$src = $data['bcfile_name']['tmp_name'];

			$attach_dir = GabroadcastHelper::getBcastTypes($data['bcasttype'])->attach_dir;
			// test is menu item wants to use filter
			$useFilter = Factory::getApplication()->getUserState('com_gabroadcast.use_filter.data');
            if ($useFilter) {
                if ($filterUsers && $filterType == 'p' && \is_array($locGrp) && isset($profile->$locProf[$locGrp[1]])) {
                    $userArea = \strtolower($profile->$locProf[$locGrp[1]] ?? '');
                    $attach_dir = $attach_dir.'/'.$userArea;
                }
            }
            $path = Path::clean( JPATH_SITE . '/'.$attach_dir );
			$destfile = $path.'/'.$fileName;

			if ( File::upload($src, $destfile, false, false, $safeFileOptions) ) {
				Factory::getApplication()->enqueueMessage(Text::_('File ('.$fileName.') Uploaded Successfully'), 'message');
			} else {
				Factory::getApplication()->enqueueMessage(Text::_('File ('.$fileName.') Upload Failed'), 'danger');
			}

			return true;
  		} else {
			return false;
		}

    }
}
