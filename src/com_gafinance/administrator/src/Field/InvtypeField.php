<?php
/**
 * @version    5.1.0
 * @package    Com_Gafinance
 * @author     Glenn Arkell <glenn@glennarkell.com.au>
 * @copyright  2021 Glenn Arkell
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace GlennArkell\Component\Gafinance\Administrator\Field;

defined('JPATH_BASE') or die;

use \Joomla\CMS\Form\FormField;
use \Joomla\CMS\HTML\HTMLHelper;
use \Joomla\CMS\Form\Field\ListField;
use \GlennArkell\Component\Gafinance\Administrator\Helper\GafinanceHelper;

class InvtypeField extends ListField
{
    /**
     * The field type.
     *
     * @var         string
     */
    protected $type = 'Invtype';

    /**
     * Method to get a list of options for a list input.
     *
     * @return      array           An array of JHtml options.
     */
    protected function getOptions()
    {
        $options = array();

        $options = GafinanceHelper::getListOptions('#__gafinance_invtypes', 'Invoice Type', 'invtype_name');

        $options = array_merge(parent::getOptions(), $options);

        return $options;
    }
}
