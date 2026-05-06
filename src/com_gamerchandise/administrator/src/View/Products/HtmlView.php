<?php
/**
 * @version    4.1.2
 * @package    com_gamerchandise
 * @author     Glenn Arkell <glenn@glennarkell.com.au>
 * @copyright  2021 Glenn Arkell
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace GlennArkell\Component\Gamerchandise\Administrator\View\Products;

// No direct access
defined('_JEXEC') or die;

use \Joomla\CMS\Factory;
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\Uri\Uri;
use \Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use \Joomla\CMS\Toolbar\Toolbar;
use \Joomla\CMS\Toolbar\ToolbarHelper;
use \Joomla\CMS\Helper\ContentHelper;
use \Joomla\CMS\HTML\HTMLHelper;
use \GlennArkell\Component\Gamerchandise\Administrator\Helper\GamerchandiseHelper;

/**
 * View class for a list of records.
 * @since  1.6
 */
class HtmlView extends BaseHtmlView
{
	protected $items;

	protected $pagination;

	protected $state;

	public $filterForm;

	public $activeFilters;

	/**
	 * Display the view
	 * @param   string  $tpl  Template name
	 * @return void
	 * @throws Exception
	 */
	public function display($tpl = null)
	{
		$this->state = $this->get('State');
		$this->items = $this->get('Items');
		$this->pagination = $this->get('Pagination');
        $this->filterForm = $this->get('FilterForm');
        $this->activeFilters = $this->get('ActiveFilters');

		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			throw new \Exception(implode("\n", $errors), 500);
		}

		$this->addToolbar();

        HTMLHelper::stylesheet(Uri::base().'media/com_gamerchandise/css/gamerchandise.css');
        HTMLHelper::stylesheet(Uri::base().'media/com_gamerchandise/css/list.css');

		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 * @return void
	 * @since    1.6
	 */
	protected function addToolbar()
	{
		$progNameL = 'product';
		$progNameC = 'Product';
		$state = $this->get('State');

		$canDo = ContentHelper::getActions('com_gamerchandise','component',0);

		$customIcon = '';

		if (file_exists(JPATH_SITE . '/media/com_gamerchandise/images/l_'.$progNameL.'s.png')) {
			$customIcon = $progNameL.'s';
		}

		ToolbarHelper::title(Text::_('COM_GAMERCHANDISE_TITLE_'.STRTOUPPER($progNameL).'S'), $customIcon);

		$toolbar = Toolbar::getInstance('toolbar');
        $toolbar->link('JTOOLBAR_DASHBOARD', 'index.php?option=com_cpanel&view=cpanel&dashboard=gamerchandise');

		// Check if the form exists before showing the add/edit buttons
		$formPath = JPATH_ADMINISTRATOR . '/components/com_gamerchandise/src/View/'.$progNameC.'s';

		if (file_exists($formPath)) {
			if ($canDo->get('core.create')) {
				$toolbar->addNew($progNameL.'.add');
			}
		}

		if ($canDo->get('core.edit.state')  || count($this->transitions)) {
			$dropdown = $toolbar->dropdownButton('status-group')
				->text('JTOOLBAR_CHANGE_STATUS')
				->toggleSplit(false)
				->icon('fas fa-ellipsis-h')
				->buttonClass('btn btn-action')
				->listCheck(true);

			$childBar = $dropdown->getChildToolbar();

			if (isset($this->items[0]->state)) {
				$childBar->edit($progNameL.'.edit');
				$childBar->publish($progNameL.'s.publish')->listCheck(true);
				$childBar->unpublish($progNameL.'s.unpublish')->listCheck(true);
				$childBar->archive($progNameL.'s.archive')->listCheck(true);
				$childBar->save2copy($progNameL.'s.duplicate', 'Duplicate')->listCheck(true);
			} elseif (isset($this->items[0])) {
				// If this component does not use state then show a direct delete button as we can not trash
				$toolbar->delete($progNameL.'s.delete')
				->text('JTOOLBAR_EMPTY_TRASH')
				->message('JGLOBAL_CONFIRM_DELETE')
				->listCheck(true);
			}

			if (isset($this->items[0]->checked_out)) {
				$childBar->checkin($progNameL.'s.checkin')->listCheck(true);
			}

			if (isset($this->items[0]->state) && $this->state->get('filter.state') != -2) {
				$childBar->trash($progNameL.'s.trash')->listCheck(true);
			}
		}

		// Show trash and delete for components that uses the state field
		if (isset($this->items[0]->state)) {

			if ($this->state->get('filter.state') == -2 && $canDo->get('core.delete')) {
				$toolbar->delete($progNameL.'s.delete')
					->text('JTOOLBAR_EMPTY_TRASH')
					->message('JGLOBAL_CONFIRM_DELETE')
					->listCheck(true);
			}
		}

		// Show the button with number of entries from the query
		$nRecords = $this->pagination->total;
		$toolbar->standardButton('nrecords')
			->icon('fa fa-info-circle')
			->text($nRecords . ' Records')
			->task('')
			->onclick('return false')
			->listCheck(false);

        $compVersion = GamerchandiseHelper::getComponentVersion();
		$toolbar->standardButton('compVers')
			->icon('fas fa-code-branch')
			->text($compVersion . ' Version')
			->task('')
			->onclick('return false')
			->listCheck(false);

		// Show the Options button to set parameters
		if ($canDo->get('core.admin')) {
			$toolbar->preferences('com_gamerchandise');
		}

	}

	/**
	 * Method to order fields 
	 * @return void
	 */
	protected function getSortFields()
	{
		return array(
			'a.`id`' => Text::_('JGRID_HEADING_ID'),
			'a.`ordering`' => Text::_('JGRID_HEADING_ORDERING'),
			'a.`state`' => Text::_('JSTATUS'),
			'a.`created_date`' => Text::_('COM_GAMERCHANDISE_CREATED_DATE'),
			'a.`modified_date`' => Text::_('COM_GAMERCHANDISE_MODIFIED_DATE'),
		);
	}

    /**
     * Check if state is set
     * @param   mixed  $state  State
     * @return bool
     */
    public function getState($state)
    {
        return isset($this->state->{$state}) ? $this->state->{$state} : false;
    }
}
