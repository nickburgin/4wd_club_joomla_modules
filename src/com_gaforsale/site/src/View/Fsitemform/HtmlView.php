<?php

/**
 * @version    4.0.2
 * @package    Com_Gaforsale
 * @author     Glenn Arkell <glenn@glennarkell.com.au>
 * @copyright  2021 Glenn Arkell
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace GlennArkell\Component\Gaforsale\Site\View\Fsitemform;

// No direct access
defined('_JEXEC') or die;

use \Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use \Joomla\CMS\Factory;
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\Uri\Uri;
use \Joomla\CMS\HTML\HTMLHelper;

/**
 * View class for a form.
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
		$this->item    = $this->get('Item');
		$this->params  = $app->getParams('com_gaforsale');
		$this->canSave = $this->get('CanSave');
		$this->form		= $this->get('Form');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new \Exception(implode("\n", $errors));
		}

		$this->_prepareDocument();

        HTMLHelper::stylesheet(Uri::base().'media/com_gaforsale/css/gaforsale.css');
        HTMLHelper::stylesheet(Uri::base().'media/com_gaforsale/css/form.css');

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
		$menu = $app->getMenu()->getActive();
		$title = null;

		if ($menu) {
			$this->params->def('page_heading', $this->params->get('page_title', $menu->title));
		} else {
			$this->params->def('page_heading', Text::_('COM_GAFINANCE_DEFAULT_PAGE_TITLE'));
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
