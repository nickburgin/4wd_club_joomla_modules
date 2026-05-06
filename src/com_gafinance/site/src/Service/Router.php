<?php

/**
 * @version    5.2.3
 * @package    Com_Gafinance
 * @author     Glenn Arkell <glenn@glennarkell.com.au>
 * @copyright  2021 Glenn Arkell
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace GlennArkell\Component\Gafinance\Site\Service;

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
use \GlennArkell\Component\Gafinance\Administrator\Helper\GafinanceHelper;

/**
 * Class Component Router
 */
class Router extends RouterView
{
	private $noIDs;

	/**
	 * The category factory
	 * @var CategoryFactoryInterface
	 * @since  5.2.3
	 */
	private $categoryFactory;

	/**
	 * The category cache
	 * @var  array
	 * @since  5.2.3
	 */
	private $categoryCache = [];

	public function __construct(SiteApplication $app, AbstractMenu $menu, CategoryFactoryInterface $categoryFactory, DatabaseInterface $db)
	{
		$params = Factory::getApplication()->getParams('com_gafinance');
		$this->noIDs = (bool) $params->get('sef_ids');
		$this->categoryFactory = $categoryFactory;
		
		/* -------------   Transactions  -------------------- */
		$transactions = new RouterViewConfiguration('transactions');
		$this->registerView($transactions);

		$transaction = new RouterViewConfiguration('transaction');
		$transaction->setKey('id')->setParent($transactions);
		$this->registerView($transaction);

		$transactionform = new RouterViewConfiguration('transactionform');
		$transactionform->setKey('id');
		$this->registerView($transactionform);

		/* -------------   Accounts  -------------------- */
		$accounts = new RouterViewConfiguration('accounts');
		$this->registerView($accounts);

		$account = new RouterViewConfiguration('account');
		$account->setKey('id')->setParent($accounts);
		$this->registerView($account);

		$accountform = new RouterViewConfiguration('accountform');
		$accountform->setKey('id');
		$this->registerView($accountform);

		/* -------------   Patrons  -------------------- */
		$patrons = new RouterViewConfiguration('patrons');
		$this->registerView($patrons);

		$patron = new RouterViewConfiguration('patron');
		$patron->setKey('id')->setParent($patrons);
		$this->registerView($patron);

		$patronform = new RouterViewConfiguration('patronform');
		$patronform->setKey('id');
		$this->registerView($patronform);

		/* -------------   Invoices  -------------------- */
		$invoices = new RouterViewConfiguration('invoices');
		$invoices->setKey('id')->setNestable();
		$this->registerView($invoices);

		$invoice = new RouterViewConfiguration('invoice');
		$invoice->setKey('id')->setParent($invoices, 'catid');
		$this->registerView($invoice);

		$invoiceform = new RouterViewConfiguration('invoiceform');
		$invoiceform->setKey('id');
		$this->registerView($invoiceform);

		/* -------------   Business Assets  -------------------- */
		$busassets = new RouterViewConfiguration('busassets');
		$this->registerView($busassets);

		$busasset = new RouterViewConfiguration('busasset');
		$busasset->setKey('id')->setParent($busassets);
		$this->registerView($busasset);

		$busassetform = new RouterViewConfiguration('busassetform');
		$busassetform->setKey('id');
		$this->registerView($busassetform);

		/* -------------   General stuff  -------------------- */
		parent::__construct($app, $menu);

		$this->attachRule(new MenuRules($this));
		$this->attachRule(new StandardRules($this));
		$this->attachRule(new NomenuRules($this));
	}

	/* -------------   Item Segments  -------------------- */
	/**
	 * Method to get the segment(s) for an transaction
	 * @param   string  $id     ID of the transaction to retrieve the segments for
	 * @param   array   $query  The request that is built right now
	 * @return  array|string  The segments of this item
	 */
	public function getTransactionSegment($id, $query)
	{
		return array((int) $id => $id);
	}

	/**
	 * Method to get the segment(s) for an account
	 */
	public function getAccountSegment($id, $query)
	{
		return array((int) $id => $id);
	}

	/**
	 * Method to get the segment(s) for an invoice
	 */
	public function getInvoiceSegment($id, $query)
	{
		return array((int) $id => $id);
	}

	/**
	 * Method to get the segment(s) for an patron
	 */
	public function getPatronSegment($id, $query)
	{
		return array((int) $id => $id);
	}

	/**
	 * Method to get the segment(s) for an Busasset
	 */
	public function getBusassetSegment($id, $query)
	{
		return array((int) $id => $id);
	}

	/* -------------   Form Segments  -------------------- */
	/**
	 * Method to get the segment(s) for an transactionform
	 * @param   string  $id     ID of the transactionform to retrieve the segments for
	 * @param   array   $query  The request that is built right now
	 * @return  array|string  The segments of this item
	 */
	public function getTransactionformSegment($id, $query)
	{
		return $this->getTransactionSegment($id, $query);
	}

	/**
	 * Method to get the segment(s) for an accountform
	 */
	public function getAccountformSegment($id, $query)
	{
		return $this->getAccountSegment($id, $query);
	}

	/**
	 * Method to get the segment(s) for an invoiceform
	 */
	public function getInvoiceformSegment($id, $query)
	{
		return $this->getInvoiceSegment($id, $query);
	}

	/**
	 * Method to get the segment(s) for an patronform
	 */
	public function getPatronformSegment($id, $query)
	{
		return $this->getPatronSegment($id, $query);
	}

	/**
	 * Method to get the segment(s) for an Busassetform
	 */
	public function getBusassetformSegment($id, $query)
	{
		return $this->getBusassetSegment($id, $query);
	}

	/* -------------   List Segments  -------------------- */
	/**
	 * Method to get the segment(s) for a category
	 * @param   string  $id     ID of the category to retrieve the segments for
	 * @param   array   $query  The request that is built right now
	 * @return  array|string  The segments of this item
	 */
	public function getInvoicesSegment($id, $query)
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

	/* -------------   Item ID  -------------------- */
	/**
	 * Method to get the segment(s) for a transaction
	 * @param   string  $segment  Segment of the transaction to retrieve the ID for
	 * @param   array   $query    The request that is parsed right now
	 * @return  mixed   The id of this item or false
	 */
	public function getTransactionId($segment, $query)
	{
		return (int) $segment;
	}

	/**
	 * Method to get the segment(s) for an account
	 */
	public function getAccountId($segment, $query)
	{
		return (int) $segment;
	}

	/**
	 * Method to get the segment(s) for an invoice
	 */
	public function getInvoiceId($segment, $query)
	{
		return (int) $segment;
	}

	/**
	 * Method to get the segment(s) for a patron
	 */
	public function getPatronId($segment, $query)
	{
		return (int) $segment;
	}

	/**
	 * Method to get the segment(s) for a busasset
	 */
	public function getBusassetId($segment, $query)
	{
		return (int) $segment;
	}

	/* -------------   Form ID  -------------------- */
	/**
	 * Method to get the segment(s) for a transactionform
	 * @param   string  $segment  Segment of the transactionform to retrieve the ID for
	 * @param   array   $query    The request that is parsed right now
	 * @return  mixed   The id of this item or false
	 */
	public function getTransactionformId($segment, $query)
	{
		return $this->getTransactionId($segment, $query);
	}

	/**
	 * Method to get the segment(s) for an accountform
	 */
	public function getAccountformId($segment, $query)
	{
		return $this->getAccountId($segment, $query);
	}

	/**
	 * Method to get the segment(s) for an invoiceform
	 */
	public function getInvoiceformId($segment, $query)
	{
		return $this->getInvoiceId($segment, $query);
	}

	/**
	 * Method to get the segment(s) for a patronform
	 */
	public function getPatronformId($segment, $query)
	{
		return $this->getPatronId($segment, $query);
	}

	/**
	 * Method to get the segment(s) for a Busassetform
	 */
	public function getBusassetformId($segment, $query)
	{
		return $this->getBusassetId($segment, $query);
	}

	/* -------------   List ID  -------------------- */
	/**
	 * Method to get the id for a category
	 * @param   string  $segment  Segment to retrieve the ID for
	 * @param   array   $query    The request that is parsed right now
	 * @return  mixed   The id of this item or false
	 */
	public function getInvoicesId($segment, $query)
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

	/* -------------   General Categories  -------------------- */
	/**
	 * Method to get categories from cache
	 * @param   array  $options   The options for retrieving categories
	 * @return  CategoryInterface  The object containing categories
	 * @since   5.2.3
	 */
	private function getCategories(array $options = []): CategoryInterface
	{
		$key = serialize($options);

		if (!isset($this->categoryCache[$key])) {
			$this->categoryCache[$key] = $this->categoryFactory->createCategory($options);
		}

		return $this->categoryCache[$key];
	}
}
