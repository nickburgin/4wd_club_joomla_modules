<?php
/**
 * @version    3.2.0
 * @package    Com_Gacalevents
 * @author     Glenn Arkell <glenn@glennarkell.com.au>
 * @copyright  2021 Glenn Arkell
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace GlennArkell\Component\Gacalevents\Administrator\Field;

// No direct access to this file
defined('_JEXEC') or die;

// import the list field type
use \Joomla\CMS\Factory;
use \Joomla\CMS\Form\FormField;
use \Joomla\CMS\Form\FormHelper;
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\Form\Field\ListField;
use \Joomla\CMS\User\UserHelper;
use \Joomla\CMS\Component\ComponentHelper;
use \GlennArkell\Component\Gacalevents\Administrator\Helper\GanamesHelper;
use \GlennArkell\Component\Gacalevents\Administrator\Helper\GacaleventsHelper;

class AttendingField extends ListField
{
    /**
     * The field type.
     * @var         string
     */
    protected $type = 'Attending';

    /**
     * Method to get a list of options for a list input.
     * @return      array           An array of JHtml options.
     */
    protected function getOptions()
    {
        $params = ComponentHelper::getParams('com_gacalevents');
    	$partner_mship  = $params->get('partner_mship',0);
    	$partnerFld  = $params->get('profile_partner','partner');

        $user = Factory::getApplication()->getIdentity();

        $m = GanamesHelper::breakdownNamesFromUserID($user->id, $partnerFld);
        
        $options[$user->name] = $user->name;
        if ($partner_mship) {
            if ( $m->partner > '') {
                $options[$m->partner] = $m->partner;
                $options[$m->fullname] = $m->fullname;
            }
        }

        $options = array_merge(parent::getOptions(), $options);

        return $options;
    }
}
