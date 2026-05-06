<?php
/**
 * @version    3.3.1
 * @package    Com_Gacalevents
 * @author     Glenn Arkell <glenn@glennarkell.com.au>
 * @copyright  2021 Glenn Arkell
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace GlennArkell\Component\Gacalevents\Administrator\Field;

// No direct access to this file
defined('_JEXEC') or die;

// import the list field type
use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Form\Field\ListField;
use \GlennArkell\Component\Gacalevents\Administrator\Helper\GacaleventsHelper;

class EventlistField extends ListField
{
    /**
     * The field type.
     * @var         string
     */
    protected $type = 'Eventlist';

    /**
     * Method to get a list of options for a list input.
     * @return      array           An array of JHtml options.
     */
    protected function getOptions()
    {
        $options = array();
        $table = '#__gacalevents_events';
		$label = 'Event';
		$field = 'CONCAT(a.title," (",a.depart_date,")")';     // this can also be a combinations of files ie CONCAT(a.title," (",a.depart_date,")")
        $options = GacaleventsHelper::getListOptions($table, $label, $field );

        $options = array_merge(parent::getOptions(), $options);

        return $options;
    }
}
