<?php

/**
 * @version     5.3
 * @package     pkg_gafinance
 * @subpackage  mod_gafinance
 * @author      Glenn Arkell <glenn@glennarkell.com.au>
 * @copyright   2021 Glenn Arkell
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace GlennArkell\Module\Gafinance\Site\Helper;

defined('_JEXEC') or die;

use \Joomla\CMS\Factory;
use \Joomla\Filesystem\Path;
use \Joomla\Filesystem\File;
use \Joomla\Filesystem\Folder;
use \Joomla\CMS\HTML\HTMLHelper;
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\Application\SiteApplication;
use \Joomla\Database\DatabaseAwareInterface;
use \Joomla\Database\DatabaseAwareTrait;
use \Joomla\Registry\Registry;

/**
 * Helper for mod_gafinance
 * @package     com_gafinance
 * @subpackage  mod_gafinance
 */
class GafinanceHelper implements DatabaseAwareInterface
{
    use DatabaseAwareTrait;

    /**
     * Retrieves records to display
     * @param array $params An object containing the module parameters
     * @access public
     */
    public static function getFinance( Registry $params, SiteApplication $app )
    {
        $accnt_id = $params->get('accnt_id', 0);
        
    	// Get the opening Balance record
   		$dbOB = Factory::getContainer()->get('DatabaseDriver');
   		$dbOB->setQuery('SELECT tran_amount, tran_date FROM #__gafinance_transactions WHERE tran_type = "Z" AND state = 1 AND accnt_id = '.$dbOB->Quote($accnt_id).' AND tran_date  = (SELECT MAX(a.tran_date) FROM #__gafinance_transactions AS a WHERE a.tran_type = "Z" AND a.state = 1 AND a.accnt_id = '.$dbOB->Quote($accnt_id).')');
   		$openBal = $dbOB->loadObject();
   		Factory::getApplication()->setUserState('mod_gafinance.balance.open', $openBal->tran_amount);

    	// Get the closing Balance figure
   		$dbCB = Factory::getContainer()->get('DatabaseDriver');
   		$dbCB->setQuery('SELECT SUM(tran_amount) FROM #__gafinance_transactions WHERE tran_type != "Z" AND state = 1 AND tran_date >= '.$dbCB->Quote($openBal->tran_date).' AND accnt_id = '.$dbCB->Quote($accnt_id));
   		$closeBal = $dbCB->loadResult();
   		Factory::getApplication()->setUserState('mod_gafinance.balance.close', ($closeBal+$openBal->tran_amount));

    	// Get the records
   		$db = Factory::getContainer()->get('DatabaseDriver');
   		$query = $db->getQuery(true);
		$query->select(' a.* ');
   		$query->from('#__gafinance_transactions AS a ');
		$query->where($db->quoteName('a.state') . ' = 1 ');
		$query->where($db->quoteName('a.tran_date') . ' >= ' . $db->Quote($openBal->tran_date));
		$query->where($db->quoteName('a.accnt_id') . ' = ' . $db->Quote($accnt_id));
        $query->select('u.name AS user_name');
        $query->join('LEFT', '#__users AS u ON u.id=a.user_id');
		$query->select('cat_id.title AS cat_name');
		$query->join('LEFT', '#__categories AS cat_id ON cat_id.id = a.cat_id');
		$query->select('accnt_id.accnt_name AS accnt_name');
		$query->join('LEFT', '#__gafinance_accounts AS accnt_id ON accnt_id.id = a.accnt_id');
   		$query->order($db->quoteName('a.tran_date').' desc');
   		$db->setQuery((string)$query);

	    try {
	        // If it fails, it will throw a RuntimeException
	        $items = $db->loadObjectList();
	    } catch (RuntimeException $e) {
	        Factory::getApplication()->enqueueMessage($e->getMessage());
	        $items = false;
	    }

	    return $items;

    }
}
