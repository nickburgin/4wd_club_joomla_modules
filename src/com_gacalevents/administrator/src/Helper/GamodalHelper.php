<?php
/**
 * @version    3.3.1
 * @package    pkg_gacalevents
 * @subpackage com_gacalevents
 * @author     Glenn Arkell <glenn@glennarkell.com.au>
 * @copyright  2023 Glenn Arkell
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace GlennArkell\Component\Gacalevents\Administrator\Helper;

// No direct access
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;

/**
 * Modal helper.
 */
class GamodalHelper
{
	/**
	 * Set up modal button
	 * @param   string $type - type of function (task or view)
	 * @param   string $controller - form controller file to use recordform.php
	 * @param   mixed  $field - field used in the url can be an assoc array of fields & values or an individual field
	 * @param   mixed  $value - of the field to match in the url
	 * @param   string $layout - tmpl layout file to use
	 * @param   string $modname - name of the modal window so is unique should there be more than 1 on a page
	 * @param   string $button - type of button to display (info, success, warning etc)
	 * @param   string $label - text to go on the button if required
	 * @param   string $title - title of the modal to display
	 * @param   string $icon - icon image to show on button
	 * @param   string $name - name that this record is relating to if required
	 *          example $actionBtn = GamodalHelper::setupModalButton('view', 'resreqform', 'id', $this->item->id, 'modalact', 'myActModal', 'info', 'Action', 'COM_GARESEARCH_ACT_TITLE', $icon, $name);
	 *          then echo $actionBtn; // where you want the button
	 * @return  string	html string for the button
	 */
    public static function setupModalButton($type, $controller, $field, $value, $layout, $modname, $button, $label, $title, $icon, $name)
	{
        $id = '';
        $modTitle = null;
        if ($title > '') {
            $modTitle = Text::_($title).' - '.$name;
        }

        $link = self::setupLink($type, $controller, $field, $value, $layout);

        $modalparams = array(
                        'url' => $link,
        		        'title'      => $modTitle,
                        'closeButton'=> true,
                        'keyboard'=> true,
				        'modalWidth' => 40,
				        'bodyHeight' => 35,
				        'viewHeight' => 40,
                        'backdrop'   => 'static'
                        );
        $modalname = 'modal-'.$modname.$value;
        $html = '<a class="btn btn-'.$button.'" href="#'.$modalname.'" ';
        $html .= 'data-bs-toggle="modal" data-bs-target="#'.$modalname.'" >';
        $html .= '<i class="'.$icon.'" title="'.$modTitle.'"></i> '.Text::_($label).'</a>';

        $html .= HTMLHelper::_('bootstrap.renderModal', $modalname, $modalparams);

		return $html;

	}

	/**
	 * Build the HTTP query array
	 * @param array of the http query if the http query already prepared and we want to add more
	 * @param string view or a task
	 * @param string control method to be used
	 * @param string reference indicator
	 * @param string/int reference string or id
	 * @return array
	 * @ hint - this is used with http_build_query($query_string, '', '&amp;') to build a clean url
	 */
	public static function getHTTPQuery($existQ = null, $viewTask = 'view', $contModel = 'event', $ref = 'id', $linkId = '0')
	{
        if ($contModel == 'article') {
            $compName = 'com_content';
        } else {
            $compName = 'com_gacalevents';
        }
        if (!$existQ) {
			$query_string = array();
			$query_string['option'] = $compName;
			$query_string[$viewTask] = $contModel;
			if ($ref) {
				$query_string[$ref] = $linkId;
			}
		} else {
			$query_string = $existQ;
			$query_string[$ref] = $linkId;
		}

		return $query_string;
	}

	/**
	 * Builds the html to display buttons
	 * @param   string  $type type of link task or view
	 * @param   string  $controller to call
	 * @param   mixed   $field (associative array with fieldname=>value) or string fieldname only
	 * @param   string  $value passed for string field
	 * @param   string  $layout special layout other than default
	 * @return  string  url of all relevant link for the Router
	 */
	public static function setupLink($type, $controller, $field, $value, $layout = null)
	{
        $cntr = 0;
        if (is_array($field)) {
            foreach ($field as $fld => $val) {
                $cntr++;
                if ($cntr == 1) {
                    $link = self::getHTTPQuery(null, $type, $controller, $fld, $val);
                } else {
                    $link = self::getHTTPQuery($link, null, null, $fld, $val);
                }
            }
        } else {
            $link = self::getHTTPQuery(null, $type, $controller, $field, $value);
            if ($field != 'id') {  // add this line to create a new record
                $link = self::getHTTPQuery($link, null, null, 'id', 0);
            }
        }
        if ($layout) {
            $link = self::getHTTPQuery($link, null, null, 'layout', $layout);
        }

        $urlLink = 'index.php?'.http_build_query($link, '', '&amp;');
        
        return $urlLink;
	}

}

