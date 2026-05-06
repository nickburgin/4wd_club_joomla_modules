<?php
/**
 * @version    5.2.3
 * @package    Com_Gafinance
 * @author     Glenn Arkell <glenn@glennarkell.com.au>
 * @copyright  2021 Glenn Arkell
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace GlennArkell\Component\Gafinance\Administrator\View\Budget;

// No direct access
defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Factory;
use \GlennArkell\Component\Gafinance\Administrator\Helper\GafinanceHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Helper\ContentHelper;

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
		$progNameL = 'budget';
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

		// If not checked out, can save the item.
		if (!$checkedOut && ($canDo->get('core.edit') || ($canDo->get('core.create')))) {
			ToolbarHelper::apply($progNameL.'.apply', 'JTOOLBAR_APPLY');
			ToolbarHelper::save($progNameL.'.save', 'JTOOLBAR_SAVE');
		}

		if (!$checkedOut && ($canDo->get('core.create'))) {
			ToolbarHelper::save2new($progNameL.'.save2new', 'JTOOLBAR_SAVE_AND_NEW');
		}

		// If an existing item, can save to a copy.
		if (!$isNew && $canDo->get('core.create')) {
			ToolbarHelper::save2copy($progNameL.'.save2copy', 'JTOOLBAR_SAVE_AS_COPY');
		}

		// Button for version control
		if ($this->state->params->get('save_history', 1) && $user->authorise('core.edit')) {
			ToolbarHelper::versions('com_'.$compName.'.'.$progNameL, $this->item->id);
		}

		if (empty($this->item->id)) {
			ToolbarHelper::cancel($progNameL.'.cancel', 'JTOOLBAR_CANCEL');
		} else {
			ToolbarHelper::cancel($progNameL.'.cancel', 'JTOOLBAR_CLOSE');
		}

		ToolbarHelper::divider();
		ToolbarHelper::inlinehelp('hide-aware-inline-help');
	}
}
