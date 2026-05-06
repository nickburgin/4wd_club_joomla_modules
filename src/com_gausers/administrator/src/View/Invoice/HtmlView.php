<?php
/**
 * @version     6.0.0
 * @package     com_gausers
 * @copyright   Copyright (C) 2012. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Created by Glenn Arkell - http://www.glennarkell.com
 */

namespace GlennArkell\Component\Gausers\Administrator\View\Invoice;

// No direct access
defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Factory;
use \GlennArkell\Component\Gausers\Administrator\Helper\GausersHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Helper\ContentHelper;

/**
 * View to edit
 */
class HtmlView extends BaseHtmlView
{
	protected $state;
	protected $item;
	protected $form;

	/**
	 * Display the view
	 */
	public function display($tpl = null)
	{
		$this->state	= $this->get('State');
		$this->item		= $this->get('Item');
		$this->form		= $this->get('Form');

		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			throw new \Exception(implode("\n", $errors));
			return false;
		}

		$this->addToolbar();

        HTMLHelper::stylesheet(Uri::base().'media/com_gausers/css/gausers.css');
		HTMLHelper::stylesheet(Uri::base().'media/com_gausers/css/form.css');

		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 */
	protected function addToolbar()
	{
		Factory::getApplication()->input->set('hidemainmenu', true);

		$progNameL = 'invoice';
		$user  = Factory::getApplication()->getIdentity();
		$isNew = ($this->item->id == 0);

		if (isset($this->item->checked_out)) {
			$checkedOut = !($this->item->checked_out == 0 || $this->item->checked_out == $user->id);
		} else {
			$checkedOut = false;
		}

		$canDo = ContentHelper::getActions('com_gausers','component',0);

		$customIcon = '';

		if (file_exists(JPATH_SITE . '/media/com_gausers/images/f_'.$progNameL.'.png')) {
			$customIcon = $progNameL;
		}

		ToolbarHelper::title(Text::_('COM_GAUSERS_TITLE_'.STRTOUPPER($progNameL)), $customIcon);

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
			ToolbarHelper::versions('com_gausers.'.$progNameL, $this->item->id);
		}

		if (empty($this->item->id)) {
			ToolbarHelper::cancel($progNameL.'.cancel', 'JTOOLBAR_CANCEL');
		} else {
			ToolbarHelper::cancel($progNameL.'.cancel', 'JTOOLBAR_CLOSE');
		}
	}

}
