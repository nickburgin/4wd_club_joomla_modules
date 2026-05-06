<?php

/**
 * @version    4.0.7
 * @package    com_gamerchandise
 * @author     Glenn Arkell <glenn@glennarkell.com.au>
 * @copyright  2021 Glenn Arkell
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace GlennArkell\Component\Gamerchandise\Site\Service;

// No direct access
defined('_JEXEC') or die;

use \Joomla\CMS\Component\Router\RouterViewConfiguration;
use \Joomla\CMS\Component\Router\RouterView;
use \Joomla\CMS\Component\Router\Rules\StandardRules;
use \Joomla\CMS\Component\Router\Rules\NomenuRules;
use \Joomla\CMS\Component\Router\Rules\MenuRules;
use \Joomla\CMS\Factory;
use \Joomla\CMS\Categories\Categories;
use \Joomla\CMS\Application\SiteApplication;
use \Joomla\CMS\Categories\CategoryFactoryInterface;
use \Joomla\CMS\Categories\CategoryInterface;
use \Joomla\Database\DatabaseInterface;
use \Joomla\CMS\Menu\AbstractMenu;
use \GlennArkell\Component\Gamerchandise\Administrator\Helper\GamerchandiseHelper;

/**
 * Class Component Router
 */
class Router extends RouterView
{
	private $noIDs;

	/**
	 * The category factory
	 * @var CategoryFactoryInterface
	 * @since  4.0.7
	 */
	private $categoryFactory;

	/**
	 * The category cache
	 * @var  array
	 * @since  4.0.7
	 */
	private $categoryCache = [];

	public function __construct(SiteApplication $app, AbstractMenu $menu, CategoryFactoryInterface $categoryFactory, DatabaseInterface $db)
	{
		$params = Factory::getApplication()->getParams('com_gamerchandise');
		$this->noIDs = (bool) $params->get('sef_ids');
		$this->categoryFactory = $categoryFactory;
		
		/* -------------------------------------------------------- */
		/* -------   products Config  --------------- */
		/* -------------------------------------------------------- */
		$products = new RouterViewConfiguration('products');
		$this->registerView($products);

		$product = new RouterViewConfiguration('product');
		$product->setKey('id')->setParent($products, 'cat_id');
		$this->registerView($product);

		$productform = new RouterViewConfiguration('productform');
		$productform->setKey('id');
		$this->registerView($productform);

		/* -------------------------------------------------------- */
		/* -------   sales Config  --------------- */
		/* -------------------------------------------------------- */
		$sales = new RouterViewConfiguration('sales');
		$this->registerView($sales);

		$sale = new RouterViewConfiguration('sale');
		$sale->setKey('id')->setParent($sales);
		$this->registerView($sale);

		$saleform = new RouterViewConfiguration('saleform');
		$saleform->setKey('id');
		$this->registerView($saleform);

		/* -------------------------------------------------------- */
		/* -------   parent construct and rules  --------------- */
		/* -------------------------------------------------------- */
		parent::__construct($app, $menu);

		$this->attachRule(new MenuRules($this));
		$this->attachRule(new StandardRules($this));
		$this->attachRule(new NomenuRules($this));
	}

	/* -------------------------------------------------------- */
	/* -------   transactions segments & id linkage  --------------- */
	/* -------------------------------------------------------- */
	/**
	 * Method to get the segment(s)
	 * @param   string  $id     ID of the category to retrieve the segments for
	 * @param   array   $query  The request that is built right now
	 * @return  array|string  The segments of this item
	 */
	public function getProductsSegment($id, $query)
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

	public function getSalesSegment($id, $query)
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
	public function getProductSegment($id, $query)
	{
		return array((int) $id => $id);
	}

	public function getSaleSegment($id, $query)
	{
		return array((int) $id => $id);
	}

	/**
	 * Method to get the segment(s) for a record when using a form
	 * @param   string  $id     ID of the record to retrieve the segments for
	 * @param   array   $query  The request that is built right now
	 * @return  array|string  The segments of this item
	 */
	public function getProductformSegment($id, $query)
	{
		return $this->getProductSegment($id, $query);
	}

	public function getSaleformSegment($id, $query)
	{
		return $this->getSaleSegment($id, $query);
	}

	/**
	 * Method to get the id reference for a record segment
	 * @param   string  $segment  Segment of the record to retrieve the ID for
	 * @param   array   $query    The request that is parsed right now
	 * @return  mixed   The id of this item or false
	 */
	public function getProductId($segment, $query)
	{
		return (int) $segment;
	}

	public function getSaleId($segment, $query)
	{
		return (int) $segment;
	}

	/**
	 * Method to get the id reference for a record segment when using a form
	 * @param   string  $segment  Segment of the record to retrieve the ID for
	 * @param   array   $query    The request that is parsed right now
	 * @return  mixed   The id of this item or false
	 */
	public function getProductformId($segment, $query)
	{
		return $this->getProductId($segment, $query);
	}

	public function getSaleformId($segment, $query)
	{
		return $this->getSaleId($segment, $query);
	}

	/**
	 * Method to get the id for a record list
	 * @param   string  $segment  Segment to retrieve the ID for
	 * @param   array   $query    The request that is parsed right now
	 * @return  mixed   The id of this item or false
	 */
	public function getProductsId($segment, $query)
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

	public function getSalesId($segment, $query)
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
	 * @since   4.0.7
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
