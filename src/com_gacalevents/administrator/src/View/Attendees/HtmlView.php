<?php
/**
 * @version    3.3.1
 * @package    Com_Gacalevents
 * @author     Glenn Arkell <glenn@glennarkell.com.au>
 * @copyright  2021 Glenn Arkell
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace GlennArkell\Component\Gacalevents\Administrator\View\Attendees;

// No direct access
defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use \GlennArkell\Component\Gacalevents\Administrator\Helper\GacaleventsHelper;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Language\Text;
use Joomla\Component\Content\Administrator\Extension\ContentComponent;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\Helper\ContentHelper;

/**
 * View class for a list of records.
 * @since  1.6
 */
class HtmlView extends BaseHtmlView
{
	protected $items;

	protected $pagination;

	protected $state;

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
			throw new GenericDataException(implode("\n", $errors), 500);
		}

		$this->addToolbar();

		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 * @return void
	 * @since    1.6
	 */
	protected function addToolbar()
	{
		$state = $this->get('State');

		$canDo = ContentHelper::getActions('com_gacalevents','component',0);

		$customIcon = 'users';

		ToolbarHelper::title(Text::_('COM_GACALEVENTS_TITLE_ATTENDEES'), $customIcon);

		$toolbar = Toolbar::getInstance('toolbar');
        $toolbar->link('JTOOLBAR_DASHBOARD', 'index.php?option=com_cpanel&view=cpanel&dashboard=gacalevents');

		// Check if the form exists before showing the add/edit buttons
		$formPath = JPATH_ADMINISTRATOR . '/components/com_gacalevents/src/View/Attendees';

		if (file_exists($formPath)) {
			if ($canDo->get('core.create')) {
				$toolbar->addNew('attendee.add');
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
				$childBar->publish('attendees.publish')->listCheck(true);
				$childBar->unpublish('attendees.unpublish')->listCheck(true);
				$childBar->archive('attendees.archive')->listCheck(true);
			} elseif (isset($this->items[0])) {
				// If this component does not use state then show a direct delete button as we can not trash
				$toolbar->delete('attendees.delete')
				->text('JTOOLBAR_EMPTY_TRASH')
				->message('JGLOBAL_CONFIRM_DELETE')
				->listCheck(true);
			}

			if (isset($this->items[0]->checked_out)) {
				$childBar->checkin('attendees.checkin')->listCheck(true);
			}

			if (isset($this->items[0]->state)) {
				$childBar->trash('attendees.trash')->listCheck(true);
			}
		}

		// Show trash and delete for components that uses the state field
		if (isset($this->items[0]->state)) {

			if ($this->state->get('filter.state') == ContentComponent::CONDITION_TRASHED && $canDo->get('core.delete')) {
				$toolbar->delete('attendees.delete')
					->text('JTOOLBAR_EMPTY_TRASH')
					->message('JGLOBAL_CONFIRM_DELETE')
					->listCheck(true);
			}
		}

		$nRecords = $this->pagination->total;
		$toolbar->standardButton('nrecords')
			->icon('fa fa-info-circle')
			->text($nRecords . ' Records')
			->task('')
			->onclick('return false')
			->listCheck(false);

		if ($canDo->get('core.admin')) {
			$toolbar->preferences('com_gacalevents');
		}

	}

	/**
	 * Method to order fields 
	 * @return void
	 */
	protected function getSortFields()
	{
		return array(
			'a.id' => Text::_('JGRID_HEADING_ID'),
			'a.ordering' => Text::_('JGRID_HEADING_ORDERING'),
			'a.state' => Text::_('JSTATUS'),
			'a.created_date' => Text::_('COM_GACALEVENTS_EVENTS_CREATED_DATE'),
			'a.modified_date' => Text::_('COM_GACALEVENTS_EVENTS_MODIFIED_DATE'),
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
