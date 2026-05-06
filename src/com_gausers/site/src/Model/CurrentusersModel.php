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
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\Component\Fields\Administrator\Helper\FieldsHelper;
use Joomla\CMS\Helper\TagsHelper;
use Joomla\CMS\Layout\FileLayout;
use Joomla\Database\ParameterType;
use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\User\UserHelper;
use Joomla\CMS\Component\ComponentHelper;
use \GlennArkell\Component\Gausers\Administrator\Helper\GausersHelper;

/**
 * Methods supporting a list of records.
 * @since  1.6
 */
class CurrentusersModel extends ListModel
{
    /**
     * Constructor.
     * @param    array    An optional associative array of configuration settings.
     * @see        JController
     * @since    1.6
     */
    public function __construct($config = array()) {
        parent::__construct($config);
    }

    /**
     * Method to auto-populate the model state.
     * Note. Calling getState in this method will result in recursion.
     * @since	1.6
     */
    protected function populateState($ordering = null, $direction = null) 
	{
        $app  = Factory::getApplication();
		$list = $app->getUserState($this->context . '.list');

		$ordering  = isset($list['filter_order'])     ? $list['filter_order']     : null;
		$direction = isset($list['filter_order_Dir']) ? $list['filter_order_Dir'] : null;

		if(empty($ordering)) {
			$ordering = $app->getUserStateFromRequest($this->context . '.filter_order', 'filter_order', $app->get('filter_order'));
			if (!in_array($ordering, $this->filter_fields)) {
				$ordering = "a.name";
			}
			$this->setState('list.ordering', $ordering);
		}
		if(empty($direction)) {
			$direction = $app->getUserStateFromRequest($this->context . '.filter_order_Dir', 'filter_order_Dir', $app->get('filter_order_Dir'));
			if (!in_array(strtoupper($direction ?? ''), array('ASC', 'DESC', ''))) {
				$direction = "ASC";
			}
			$this->setState('list.direction', $direction);
		}

		$list['limit']     = $app->getUserStateFromRequest($this->context . '.list.limit', 'limit', $app->get('list_limit'), 'uint');
		$list['start']     = $app->input->getInt('start', 0);
		$list['ordering']  = $ordering;
		$list['direction'] = $direction;
		
		$app->setUserState($this->context . '.list', $list);

        // List state information.
        parent::populateState($ordering, $direction);

        $context = $this->getUserStateFromRequest($this->context.'.filter.search', 'filter_search');
        $this->setState('filter.search', $context);
        $status = $this->getUserStateFromRequest($this->context.'.filter.state', 'filter_state');
        $this->setState('filter.state', $status);
        $mship = $this->getUserStateFromRequest($this->context.'.filter.mship', 'filter_mship');
        $this->setState('filter.mship', $mship);
        $ugroup = $this->getUserStateFromRequest($this->context.'.filter.ugroup', 'filter_ugroup');
        $this->setState('filter.ugroup', $ugroup);
        $pgroup = $this->getUserStateFromRequest($this->context.'.filter.pgroup', 'filter_pgroup');
        $this->setState('filter.pgroup', $pgroup);

    }

    /**
     * Build an SQL query to load the list data.
     * @return	JDatabaseQuery
     * @since	1.6
     */
    protected function getListQuery() 
	{
        $menu = Factory::getApplication()->getMenu()->getActive();
        // View type param in menu item - 0 = General list & 1 = Training list & 2 = Profile Group
        $viewType = $menu->getParams()->get('view_type', 0);

		$user       = Factory::getApplication()->getIdentity();
		$canMembers = $user->authorise('core.members','com_gausers');

		$params = ComponentHelper::getParams('com_gausers');
        $profsuf  = $params->get( 'profile_suffix' );
        $xclude  = $params->get( 'exclude_member' );
        $temp_group  = $params->get( 'temp_group' );
        $exclude_group  = $params->get( 'exclude_group' ); // this is a multiple field so an array
        $xtrainfo  = $params->get( 'profile_params' );
        $admin_id  = $params->get( 'admin_id' );
        $incl_partner  = $params->get( 'incl_partner' );
        $use_clubnumber  = $params->get( 'use_clubnumber', 0 );
        $fld_clubnumber  = $params->get( 'fld_clubnumber', 0 );
        $privacy_type  = $params->get( 'privacy_type', 'Profile' );
        $privacy_switchc  = $params->get( 'privacy_switchc', 0 );
        $privacy_switchp  = $params->get( 'privacy_switchp', 0 );
        $profile_group  = $params->get( 'profile_group', 0 );
		$profile_suffix  = $params->get( 'profile_suffix', 'b4wdc' );
		$localProf = 'profile'.$profile_suffix;
		$profGroup = $localProf.'.'.$profile_group;
        $xtraselect = "";
        $xtrajoin = "";
        $xtrawhere = "";
        $i = 0;
        
        if (isset($xclude)) {
            $xcludeMbrs = implode(',',$xclude);
		} else {
			$xcludeMbrs = 0;
		}
		
		// set profile field if view type option 2
		if ($viewType == 2) {
            $profile = UserHelper::getProfile($user->id);
            $filterGroupProf = $profile->$localProf[$profile_group];
            $this->setState('filter.pgroup', $filterGroupProf);
        }

        // Create a new query object.
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        // Select the required fields from the table.
        //$query->select( $viewType . ' AS viewType ');
        $query->select(' a.id, a.name, a.email, a.block, a.registerDate, a.lastvisitDate ');
        $query->select(" SUBSTRING_INDEX(SUBSTRING_INDEX(a.name, ' ', 1), ' ', -1) AS firstname " );
        $query->select(" If( length(a.name) - length(replace(a.name, ' ', ''))>1, SUBSTRING_INDEX(SUBSTRING_INDEX(a.name, ' ', 2), ' ', -1) ,NULL) as middle1_name " );
        $query->select(" If( If( length(a.name) - length(replace(a.name, ' ', ''))>1, SUBSTRING_INDEX(SUBSTRING_INDEX(a.name, ' ', 3), ' ', -1) ,NULL) = SUBSTRING_INDEX(SUBSTRING_INDEX(a.name, ' ', 4), ' ', -1), null, If( length(a.name) - length(replace(a.name, ' ', ''))>1, SUBSTRING_INDEX(SUBSTRING_INDEX(a.name, ' ', 3), ' ', -1) ,NULL)) as middle2_name " );
        $query->select(" SUBSTRING_INDEX(SUBSTRING_INDEX(a.name, ' ', 4), ' ', -1) AS surname " );
        $query->from('#__users AS a');

    	if ($xcludeMbrs) {
			$query->where( ' a.id NOT IN ('.$xcludeMbrs.')' );
		}

        /* -------------------   Set up fields to get based on the View Type parameter  ----------  */
        
        if ($viewType == 1) {
            // Only get the data for training list view
            $query->select(' if(o.profile_value IS NULL, "", o.profile_value) AS partner ');
            $query->select(' substr(o.profile_value, 1, LOCATE(" ",o.profile_value)) AS firstnamep ');
            $query->select(' substr(o.profile_value, LOCATE(" ",o.profile_value)+1) AS surnamep ');

            $query->select(' if(q.profile_value IS NULL, "", q.profile_value) AS prof_date ');
            $query->select(' if(r.profile_value IS NULL, "", r.profile_value) AS csaw_date ');
            $query->select(' if(s.profile_value IS NULL, "", s.profile_value) AS faid_date ');
            $query->select(' if(t.profile_value IS NULL, "", t.profile_value) AS wexp_date ');

            $query->select(' if(cq.profile_value IS NULL, "", cq.profile_value) AS prof_cert ');
            $query->select(' if(cr.profile_value IS NULL, "", cr.profile_value) AS csaw_cert ');
            $query->select(' if(cs.profile_value IS NULL, "", cs.profile_value) AS faid_cert ');
            $query->select(' if(cu.profile_value IS NULL, "", cu.profile_value) AS wreg_cert ');

            $query->select(' if(v.profile_value IS NULL, "", v.profile_value) AS prof_datep ');
            $query->select(' if(w.profile_value IS NULL, "", w.profile_value) AS csaw_datep ');
            $query->select(' if(x.profile_value IS NULL, "", x.profile_value) AS faid_datep ');
            $query->select(' if(y.profile_value IS NULL, "", y.profile_value) AS wexp_datep ');

            $query->select(' if(cv.profile_value IS NULL, "", cv.profile_value) AS prof_certp ');
            $query->select(' if(cw.profile_value IS NULL, "", cw.profile_value) AS csaw_certp ');
            $query->select(' if(cx.profile_value IS NULL, "", cx.profile_value) AS faid_certp ');
            $query->select(' if(cz.profile_value IS NULL, "", cz.profile_value) AS wreg_certp ');

       		$query->join('LEFT', ' #__user_profiles AS o ON a.id = o.user_id AND o.profile_key = "profile'.$profsuf.'.partner" ');

    		$query->join('LEFT', ' #__user_profiles AS q ON a.id = q.user_id AND q.profile_key = "profile'.$profsuf.'.trg_prof" ' );
    		$query->join('LEFT', ' #__user_profiles AS r ON a.id = r.user_id AND r.profile_key = "profile'.$profsuf.'.trg_csaw" ' );
    		$query->join('LEFT', ' #__user_profiles AS s ON a.id = s.user_id AND s.profile_key = "profile'.$profsuf.'.trg_faid" ' );
    		$query->join('LEFT', ' #__user_profiles AS t ON a.id = t.user_id AND t.profile_key = "profile'.$profsuf.'.wwk_exp" ' );

    		$query->join('LEFT', ' #__user_profiles AS cq ON a.id = cq.user_id AND cq.profile_key = "profile'.$profsuf.'.trg_profc" ' );
    		$query->join('LEFT', ' #__user_profiles AS cr ON a.id = cr.user_id AND cr.profile_key = "profile'.$profsuf.'.trg_csawc" ' );
    		$query->join('LEFT', ' #__user_profiles AS cs ON a.id = cs.user_id AND cs.profile_key = "profile'.$profsuf.'.trg_faidc" ' );
    		$query->join('LEFT', ' #__user_profiles AS cu ON a.id = cu.user_id AND cu.profile_key = "profile'.$profsuf.'.wwk_regc" ' );

    		$query->join('LEFT', ' #__user_profiles AS v ON a.id = v.user_id AND v.profile_key = "profile'.$profsuf.'.trg_profp" ' );
    		$query->join('LEFT', ' #__user_profiles AS w ON a.id = w.user_id AND w.profile_key = "profile'.$profsuf.'.trg_csawp" ' );
    		$query->join('LEFT', ' #__user_profiles AS x ON a.id = x.user_id AND x.profile_key = "profile'.$profsuf.'.trg_faidp" ' );
    		$query->join('LEFT', ' #__user_profiles AS y ON a.id = y.user_id AND y.profile_key = "profile'.$profsuf.'.wwk_expp" ' );

    		$query->join('LEFT', ' #__user_profiles AS cv ON a.id = cv.user_id AND cv.profile_key = "profile'.$profsuf.'.trg_profpc" ' );
    		$query->join('LEFT', ' #__user_profiles AS cw ON a.id = cw.user_id AND cw.profile_key = "profile'.$profsuf.'.trg_csawpc" ' );
    		$query->join('LEFT', ' #__user_profiles AS cx ON a.id = cx.user_id AND cx.profile_key = "profile'.$profsuf.'.trg_faidpc" ' );
    		$query->join('LEFT', ' #__user_profiles AS cz ON a.id = cz.user_id AND cz.profile_key = "profile'.$profsuf.'.wwk_regpc" ' );

         } else {
            // ensure variable is array for the count to work
            if (!is_array($xtrainfo)) {
    			$xtrainfo = array($xtrainfo);
    		}
            if (count($xtrainfo) == 0 || empty($xtrainfo[0])) { $xtraselect = ""; $xtrajoin = ""; }
    	    else {
               // cycle through items to add code to the query
               foreach ($xtrainfo as $xtradata) {
                   if ($xtradata == "") {
                   } else {
                       $i++;
                       if ($i == 1) { $j = 'f'; }
                       elseif ($i == 2) { $j = 'g'; }
                       elseif ($i == 3) { $j = 'h'; }
                       else { $j = 'k'; }
    
                       $xtraselect .= ", ".$j.".profile_value AS ".$xtradata;
                       $xtrajoin .= ' LEFT JOIN #__user_profiles AS '.$j.' ON a.id = '.$j.'.user_id AND '.$j.'.profile_key = "profile'.$profsuf.'.'.$xtradata.'" ';
    
    	               	// Filter by search on other extra fields selected
    	                $search = $this->getState('filter.search');
    					if (!empty($search)) {
    						$search = $db->Quote('%' . $db->escape($search, true) . '%');
    						$xtrawhere .= '  OR  '.$j.'.profile_value LIKE '.$search;
    					}
    				}
    			}
                if (!empty($search)) {
                    $xtrawhere .= ' OR e.profile_value LIKE '.$search.' ';
                }
            }
            //$usePost = 'REPLACE(v.profile_value,'."","")';
            //$query->select(' ugm.group_id AS group_registered ');
            $query->select(' if(b.profile_value IS NULL, "", b.profile_value) AS address ');
            $query->select(' if(c.profile_value IS NULL, "", c.profile_value) AS city ');
            $query->select(' if(cr.profile_value IS NULL, "", cr.profile_value) AS region ');
            $query->select(' if(d.profile_value IS NULL, "", d.profile_value) AS postcode ');
            $query->select(' if(e.profile_value IS NULL, "", e.profile_value) AS phone '. $xtraselect);
            $query->select(' if(fn.profile_value IS NULL, 0, fn.profile_value) AS fwdvic_no ');
            $query->select(' if(v.profile_value IS NULL, 0, v.profile_value) AS use_post ');
            $query->select(' if(w.profile_value IS NULL, "", w.profile_value) AS postal_address1 ');
            $query->select(' if(x.profile_value IS NULL, "", x.profile_value) AS postal_address2 ');
            $query->select(' if(y.profile_value IS NULL, "", y.profile_value) AS postal_city ');
            $query->select(' if(yr.profile_value IS NULL, "", yr.profile_value) AS postal_region ');
            $query->select(' if(z.profile_value IS NULL, "", z.profile_value) AS postal_post_code ');
            $query->select(' if(o.profile_value IS NULL, "", o.profile_value) AS partner ');
            $query->select(' substr(o.profile_value, 1, LOCATE(" ",o.profile_value)) AS firstnamep ');
            $query->select(' substr(o.profile_value, LOCATE(" ",o.profile_value)+1) AS surnamep ');
            $query->select(' if(mi.profile_value IS NULL, "", mi.profile_value) AS m_img ');
            $query->select(' if(pi.profile_value IS NULL, "", pi.profile_value) AS p_img ');
            $query->select(' if(am.profile_value IS NULL, "", am.profile_value) AS aboutme ');
            $query->select(' if(fb.profile_value IS NULL, "", fb.profile_value) AS favoritebook ');
            if ($use_clubnumber && $fld_clubnumber != "") {
    			$query->select(' if(m.profile_value IS NULL, "", m.profile_value) AS "'.$fld_clubnumber.'"');
        		$query->join('LEFT', ' #__user_profiles AS m ON a.id = m.user_id AND m.profile_key = "profile'.$profsuf.'.'.$fld_clubnumber.'" ' );
        	}
    		$query->join('LEFT', ' #__user_profiles AS fn ON a.id = fn.user_id AND fn.profile_key = "profile'.$profsuf.'.fwdvic_no" ' );
    		$query->join('LEFT', ' #__user_profiles AS v ON a.id = v.user_id AND v.profile_key = "profile'.$profsuf.'.use_post" ' );
    		$query->join('LEFT', ' #__user_profiles AS w ON a.id = w.user_id AND w.profile_key = "profile'.$profsuf.'.postal_address1" ' );
    		$query->join('LEFT', ' #__user_profiles AS x ON a.id = x.user_id AND x.profile_key = "profile'.$profsuf.'.postal_address2" ' );
    		$query->join('LEFT', ' #__user_profiles AS y ON a.id = y.user_id AND y.profile_key = "profile'.$profsuf.'.postal_city" ' );
    		$query->join('LEFT', ' #__user_profiles AS yr ON a.id = yr.user_id AND yr.profile_key = "profile'.$profsuf.'.postal_region" ' );
    		$query->join('LEFT', ' #__user_profiles AS z ON a.id = z.user_id AND z.profile_key = "profile'.$profsuf.'.postal_post_code" ' );
    		$query->join('LEFT', ' #__user_profiles AS b ON a.id = b.user_id AND b.profile_key = "profile.address1" ' );
    		$query->join('LEFT', ' #__user_profiles AS c ON a.id = c.user_id AND c.profile_key = "profile.city" ' );
    		$query->join('LEFT', ' #__user_profiles AS cr ON a.id = cr.user_id AND cr.profile_key = "profile.region" ' );
    		$query->join('LEFT', ' #__user_profiles AS d ON a.id = d.user_id AND d.profile_key = "profile.postal_code" ' );
    		$query->join('LEFT', ' #__user_profiles AS e ON a.id = e.user_id AND e.profile_key = "profile.phone" ' .$xtrajoin );
    		$query->join('LEFT', ' #__user_profiles AS o ON a.id = o.user_id AND o.profile_key = "profile'.$profsuf.'.partner" ');
    		$query->join('LEFT', ' #__user_profiles AS mi ON a.id = mi.user_id AND mi.profile_key = "profile'.$profsuf.'.m_img" ');
    		$query->join('LEFT', ' #__user_profiles AS pi ON a.id = pi.user_id AND pi.profile_key = "profile'.$profsuf.'.p_img" ');
    		$query->join('LEFT', ' #__user_profiles AS am ON a.id = am.user_id AND am.profile_key = "profile.aboutme" ');
    		$query->join('LEFT', ' #__user_profiles AS fb ON a.id = fb.user_id AND fb.profile_key = "profile.favoritebook" ');
    		//$query->join('LEFT', ' #__user_usergroup_map AS ugm ON a.id = ugm.user_id AND ugm.group_id = 2 ');
    		//$query->where( ' ugm.group_id IS NOT NULL ' );
            if (!$canMembers && $privacy_type == 'Profile') {
    			$query->select(' if(priv.profile_value IS NULL, 0, priv.profile_value) AS privacy ');
        		$query->join('LEFT', ' #__user_profiles AS priv ON a.id = priv.user_id AND priv.profile_key = "profile'.$profsuf.'.'.$privacy_switchp.'" ' );
        	} elseif (!$canMembers && $privacy_type == 'Custom' && $privacy_switchc) {
    			$query->select(' if(priv.value IS NULL, 0, priv.value) AS privacy ');
        		$query->join('LEFT', ' #__fields_values AS priv ON a.id = priv.item_id AND priv.field_id = '.(int) $privacy_switchc );
        		$query->where( ' if(priv.value IS NULL, 0, priv.value) = 0 ' );
        	} else {
                $query->select(' 0 AS privacy ');
        	}

        }

        /* -------------   All the basic filtering and search params  -------------------- */


        // Filter by selected user group
        $ugroup = $this->getState('filter.ugroup');
        if ($ugroup) {
            $query->join('LEFT', ' #__user_usergroup_map AS ugm ON ugm.user_id = a.id AND ugm.group_id = '.(int) $ugroup);
            $query->where( ' ugm.group_id IS NOT NULL ' );
        } else {
    		if (is_array($exclude_group)) {
    			foreach ($exclude_group AS $x => $Xgroup) {
    				$query->join('LEFT', ' #__user_usergroup_map AS gm'.$x.' ON gm'.$x.'.user_id = a.id AND gm'.$x.'.group_id = '.(int) $Xgroup);
    				$query->where( ' gm'.$x.'.group_id IS NULL ' );
                }
    		}
        }

        // Filter by selected profile group
        $pgroup = $this->getState('filter.pgroup');
        if ( $profile_group > '' && ($pgroup || $viewType == 2)) {
            $query->join('LEFT', ' #__user_profiles AS uprof ON uprof.user_id = a.id AND uprof.profile_key = '.$db->quote($profGroup).' AND uprof.profile_value = '.$db->quote($pgroup));
            $query->where( ' uprof.user_id IS NOT NULL ' );
        }

        // Filter by search in title
        $status = $this->getState('filter.state');
        if (!$canMembers) {
            $query->where( ' a.block = 0 ' );
        } else {
            if ($status == 1) {
                $query->where( ' a.block = 0 ' );
            } elseif ($status == 2) {
                $query->where( ' a.block = 1 ' );
            }
        }

        // Filter by search in title
        $mship = $this->getState('filter.mship');
        if ($mship) {
            //only get members with mship
    		$query->select(' inv.end_date AS mship_expiry ');
            $query->join('INNER', ' #__gausers_invoices AS inv ON a.id = inv.user_id ' );
    		$query->where( ' inv.id = ( SELECT MAX(inv2.id) FROM #__gausers_invoices AS inv2 WHERE inv2.user_id = a.id AND inv2.state = 2 ) ' );
    		$query->where( ' inv.mship_id = '.(int)$mship );
    		$query->where( ' inv.end_date IS NOT NULL ' );
        }

        // Filter by search in title
        $search = $this->getState('filter.search');
        if (!empty($search)) {
            if (stripos($search, 'id:') === 0) {
                $query->where('a.id = ' . (int) substr($search, 3));
            } else {
                $search = $db->Quote('%' . $db->escape($search, true) . '%');
                $query->where('( a.name LIKE '.$search.' OR a.email LIKE '.$search.' OR o.profile_value LIKE '.$search.' OR e.profile_value LIKE '.$search.' ' .$xtrawhere .')' );
            }
        }

    	//$query->order(' if(substr(name, (LOCATE(" ",name)+1), 1)="&",substr(name, LOCATE(" ",name,(LOCATE(" ",name)+3))+1),substr(name, LOCATE(" ",name)+1)) ');
        $query->order(" SUBSTRING_INDEX(SUBSTRING_INDEX(a.name, ' ', 4), ' ', -1) ASC " );
        return $query;

    }

    public function getItems() {
        return parent::getItems();
    }

	/**
	 * Overrides the default function to check Date fields format, identified by
	 * "_dateformat" suffix, and erases the field if it's not correct.
	 * @return void
	 */
	protected function loadFormData()
	{
		$app              = Factory::getApplication();
		$filters          = $app->getUserState($this->context . '.filter', array());
		$error_dateformat = false;

		foreach ($filters as $key => $value) {
			if (strpos($key, '_dateformat') && !empty($value) && $this->isValidDate($value) == null) {
				$filters[$key]    = '';
				$error_dateformat = true;
			}
		}

		if ($error_dateformat) {
			$app->enqueueMessage(Text::_("COM_GAUSERS_SEARCH_FILTER_DATE_FORMAT"), "warning");
			$app->setUserState($this->context . '.filter', $filters);
		}

		return parent::loadFormData();
	}

	/**
	 * Checks if a given date is valid and in a specified format (YYYY-MM-DD)
	 * @param   string  $date  Date to be checked
	 * @return bool
	 */
	private function isValidDate($date)
	{
		$date = str_replace('/', '-', $date);
		return (date_create($date)) ? Factory::getDate($date)->format("Y-m-d") : null;
	}
}
