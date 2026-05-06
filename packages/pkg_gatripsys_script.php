<?php
/**
 * @package     pkg_gatripsys
 * @copyright   Copyright (C) 2011. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Glenn Arkell - http://www.glennarkell.com.au
 */

defined('_JEXEC') or die();

use \Joomla\CMS\Installer\InstallerScript;

class pkg_gatripsysInstallerScript extends InstallerScript
{
    public $compName = 'gatripsys';

    protected $autoloadLanguage = true;

    public function postflight($type, $parent)
    {
        echo '<p>Postflight Package Install</p>';

        if (strtoupper($type) === 'UPDATE' || strtoupper($type) === 'INSTALL') {
            echo '<h4 style="text-align:center; color:red;">As this package contains the component and module, you should rebuild your update servers listing.<br>Go to System &gt; Update Sites and click the Rebuild button.</h4>';
        }

        return true;
    }
}
