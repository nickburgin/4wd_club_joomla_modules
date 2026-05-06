<?php

/**
 * @version    4.2.0
 * @package    pkg_mypackage
 * @subpackage com_gatracklog
 * @author     Glenn Arkell <glenn@glennarkell.com.au>
 * @copyright  2021 Glenn Arkell
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace GlennArkell\Component\Gatracklog\Site\Service;

// No direct access
defined('_JEXEC') or die;

use Joomla\CMS\Component\Router\RouterViewConfiguration;
use Joomla\CMS\Component\Router\RouterView;
use Joomla\CMS\Component\Router\Rules\StandardRules;
use Joomla\CMS\Component\Router\Rules\NomenuRules;
use Joomla\CMS\Component\Router\Rules\MenuRules;
use Joomla\CMS\Factory;
use Joomla\CMS\Categories\Categories;
use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Categories\CategoryFactoryInterface;
use Joomla\CMS\Categories\CategoryInterface;
use Joomla\Database\DatabaseInterface;
use Joomla\CMS\Menu\AbstractMenu;
use \GlennArkell\Component\Gatracklog\Administrator\Helper\GatracklogHelper;

/**
 * Class Component Router
 */
class Router extends RouterView
{
	private $noIDs;

	/**
	 * The category factory
	 * @var CategoryFactoryInterface
	 * @since  4.0.0
	 */
	private $categoryFactory;

	/**
	 * The category cache
	 * @var  array
	 * @since  4.0.0
	 */
	private $categoryCache = [];

	public function __construct(SiteApplication $app, AbstractMenu $menu, CategoryFactoryInterface $categoryFactory, DatabaseInterface $db)
	{
		$params = Factory::getApplication()->getParams('com_gatracklog');
		$this->noIDs = (bool) $params->get('sef_ids');
		$this->categoryFactory = $categoryFactory;
		
		/* -------------------------------------------------------- */
		/* -------   tracklogs Config  --------------- */
		/* -------------------------------------------------------- */
		$tracklogs = new RouterViewConfiguration('tracklogs');
		$this->registerView($tracklogs);

        $categories  = new RouterViewConfiguration('categories');
        $categories->setKey('id');
        $this->registerView($categories);
        $category = new RouterViewConfiguration('category');
        $category->setKey('id')->setParent($categories, 'rating');
        $this->registerView($category);

		$tracklog = new RouterViewConfiguration('tracklog');
		$tracklog->setKey('id')->setParent($category, 'rating');
		$this->registerView($tracklog);

		$tracklogform = new RouterViewConfiguration('tracklogform');
		$tracklogform->setKey('id');
		$this->registerView($tracklogform);

		/* -------------------------------------------------------- */
		/* -------   parent construct and rules  --------------- */
		/* -------------------------------------------------------- */
		parent::__construct($app, $menu);

		$this->attachRule(new MenuRules($this));
		$this->attachRule(new StandardRules($this));
		$this->attachRule(new NomenuRules($this));
	}

	/* -------------------------------------------------------- */
	/* -------   tracklogs segments & id linkage  --------------- */
	/* -------------------------------------------------------- */
	/**
	 * Method to get the segment(s)
	 * @param   string  $id     ID of the category to retrieve the segments for
	 * @param   array   $query  The request that is built right now
	 * @return  array|string  The segments of this item
	 */
	public function getTracklogsSegment($id, $query)
	{
		$category = $this->getCategories(["access" => true])->get($id);

		if ($category) {
			$path = array_reverse($category->getPath(), true);
			$path[0] = '1:root';

			if ($this->noIDs) {
				foreach ($path as &$segment) {
					list($id, $segment) = explode(':', $segment, 2);
				}
			}

			return $path;
		}

		return array();
	}

	/**
	 * Method to get the segment(s) for a single record
	 * @param   string  $id     ID of the record to retrieve the segments for
	 * @param   array   $query  The request that is built right now
	 * @return  array|string  The segments of this item
	 */
	public function getTracklogSegment($id, $query)
	{
		return array((int) $id => $id);
	}

	/**
	 * Method to get the segment(s) for a record when using a form
	 * @param   string  $id     ID of the record to retrieve the segments for
	 * @param   array   $query  The request that is built right now
	 * @return  array|string  The segments of this item
	 */
	public function getTracklogformSegment($id, $query)
	{
		return $this->getTracklogSegment($id, $query);
	}

	/**
	 * Method to get the id reference for a record segment
	 * @param   string  $segment  Segment of the record to retrieve the ID for
	 * @param   array   $query    The request that is parsed right now
	 * @return  mixed   The id of this item or false
	 */
	public function getTracklogId($segment, $query)
	{
		return (int) $segment;
	}

	/**
	 * Method to get the id reference for a record segment when using a form
	 * @param   string  $segment  Segment of the record to retrieve the ID for
	 * @param   array   $query    The request that is parsed right now
	 * @return  mixed   The id of this item or false
	 */
	public function getTracklogformId($segment, $query)
	{
		return $this->getTracklogId($segment, $query);
	}

	/**
	 * Method to get the id for a record list
	 * @param   string  $segment  Segment to retrieve the ID for
	 * @param   array   $query    The request that is parsed right now
	 * @return  mixed   The id of this item or false
	 */
	public function getTracklogsId($segment, $query)
	{
		if (isset($query['id'])) {
			$category = $this->getCategories(["access" => true])->get($query['id']);

			if ($category) {
				foreach ($category->getChildren() as $child) {
					if ($this->noIDs) {
						if ($child->alias == $segment) {
							return $child->id;
						}
					} else {
						if ($child->id == (int) $segment) {
							return $child->id;
						}
					}
				}
			}
		}

		return false;
	}

	/**
	 * Method to get categories from cache
	 * @param   array  $options   The options for retrieving categories
	 * @return  CategoryInterface  The object containing categories
	 * @since   4.0.0
	 */
	private function getCategories(array $options = []): CategoryInterface
	{
		$key = serialize($options);

		if (!isset($this->categoryCache[$key])) {
			$this->categoryCache[$key] = $this->categoryFactory->createCategory($options);
		}

		return $this->categoryCache[$key];
	}

	/* -------------------------------------------------------- */
	/* -------   other segments & id linkage  --------------- */
	/* -------------------------------------------------------- */
}
