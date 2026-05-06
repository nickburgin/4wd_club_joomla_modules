<?php

/**
 * @version    5.1.0
 * @package    Com_Gatripsys
 * @author     Glenn Arkell <glenn@glennarkell.com.au>
 * @copyright  2016 Glenn Arkell
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace GlennArkell\Component\Gatripsys\Site\View\Incidentform;

// No direct access
defined('_JEXEC') or die;

use \Joomla\CMS\Factory;
use \Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\Uri\Uri;
use \Joomla\CMS\HTML\HTMLHelper;

/**
 * View to edit
 * @since  1.6
 */
class HtmlView extends BaseHtmlView
{
	protected $state;
	protected $item;
	protected $form;
	protected $params;
	protected $canSave;

	/**
	 * Display the view
	 * @param   string  $tpl  Template name
	 * @return void
	 * @throws Exception
	 */
	public function display($tpl = null)
	{
		$app  = Factory::getApplication();

		$this->state   = $this->get('State');
		$this->item    = $this->get('Data');
		$this->params  = $app->getParams('com_gatripsys');
		$this->canSave = $this->get('CanSave');
		$this->form		= $this->get('Form');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new \Exception(implode("\n", $errors));
		}

		$this->_prepareDocument();

        HTMLHelper::stylesheet(Uri::base() . 'media/com_gatripsys/gatripsys.css');
        HTMLHelper::stylesheet(Uri::base() . 'media/com_gatripsys/form.css');
		HTMLHelper::script(Uri::base() . 'media/com_gatripsys/js/form.js');

		parent::display($tpl);
	}

	/**
	 * Prepares the document
	 * @return void
	 * @throws Exception
	 */
	protected function _prepareDocument()
	{
		$app   = Factory::getApplication();
		// Because the application sets a default page title,
		// we need to get it from the menu item itself
		$menu = $app->getMenu()->getActive();
		$title = null;

		if ($menu) {
			$this->params->def('page_heading', $this->params->get('page_title', $menu->title));
		} else {
			$this->params->def('page_heading', Text::_('COM_GATRIPSYS_DEFAULT_PAGE_TITLE'));
		}

		$title = $this->params->get('page_title', '');

		if (empty($title)) {
			$title = $app->get('sitename');
		} elseif ($app->get('sitename_pagetitles', 0) == 1) {
			$title = Text::sprintf('JPAGETITLE', $app->get('sitename'), $title);
		} elseif ($app->get('sitename_pagetitles', 0) == 2) {
			$title = Text::sprintf('JPAGETITLE', $title, $app->get('sitename'));
		}

		$this->document->setTitle($title);

		if ($this->params->get('menu-meta_description')) {
			$this->document->setDescription($this->params->get('menu-meta_description'));
		}

		if ($this->params->get('menu-meta_keywords')) {
			$this->document->setMetadata('keywords', $this->params->get('menu-meta_keywords'));
		}

		if ($this->params->get('robots')) {
			$this->document->setMetadata('robots', $this->params->get('robots'));
		}
	}
}
