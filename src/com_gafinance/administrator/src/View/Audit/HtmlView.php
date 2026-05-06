<?php
/**
 * @version    5.1.0
 * @package    Com_Gafinance
 * @author     Glenn Arkell <glenn@glennarkell.com.au>
 * @copyright  2021 Glenn Arkell
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace GlennArkell\Component\Gafinance\Administrator\View\Audit;

// No direct access
defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use \Joomla\CMS\Toolbar\ToolbarHelper;
use \Joomla\CMS\Factory;
use \GlennArkell\Component\Gafinance\Administrator\Helper\GafinanceHelper;
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\Uri\Uri;
use \Joomla\CMS\HTML\HTMLHelper;
use \Joomla\CMS\Helper\ContentHelper;

/**
 * View class for a record.
 * @since  1.6
 */
class HtmlView extends BaseHtmlView
{
	protected $state;

	protected $item;

	protected $form;

	protected $canDo;

	/**
	 * Display the view
	 * @param   string  $tpl  Template name
	 * @return void
	 * @throws Exception
	 */
	public function display($tpl = null)
	{
		$this->state = $this->get('State');
		$this->item  = $this->get('Item');
		$this->form  = $this->get('Form');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new \Exception(implode("\n", $errors));
		}

		$this->addToolbar();

		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 * @return void
	 * @throws Exception
	 */
	protected function addToolbar()
	{
		Factory::getApplication()->input->set('hidemainmenu', true);

		$compName = 'gafinance';
		$progNameL = 'audit';
		$user  = GafinanceHelper::getSpecificUser();
		$isNew = ($this->item->id == 0);

		if (isset($this->item->checked_out)) {
			$checkedOut = !($this->item->checked_out == 0 || $this->item->checked_out == $user->id);
		} else {
			$checkedOut = false;
		}

		$canDo = ContentHelper::getActions('com_'.$compName,'component',0);

		$customIcon = '';

		if (file_exists(JPATH_SITE . '/media/com_'.$compName.'/images/l_'.$progNameL.'s.png')) {
			$customIcon = $progNameL.'s';
		}

		ToolbarHelper::title(Text::_('COM_'.STRTOUPPER($compName ?? '').'_TITLE_'.STRTOUPPER($progNameL ?? '')), $customIcon);

		if (empty($this->item->id)) {
			ToolbarHelper::cancel($progNameL.'.cancel', 'JTOOLBAR_CANCEL');
		} else {
			ToolbarHelper::cancel($progNameL.'.cancel', 'JTOOLBAR_CLOSE');
		}

		ToolbarHelper::divider();
		ToolbarHelper::inlinehelp('hide-aware-inline-help');
	}
}
