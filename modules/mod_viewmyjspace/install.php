<?php
/**
* @version $Id: install.php $
* @version		2.0.3 21/10/2012
* @author       Bernard Saulmé
* @copyright	Copyright (C) 2012 Bernard Saulmé
* @license      GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
*/
 
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die;
if (!defined('DS')) define('DS', DIRECTORY_SEPARATOR);

class mod_viewmyjspaceInstallerScript
{
	public function __construct($installer) {
		$this->installer = $installer;
	}
 
	public function postflight($type, $parent) {

		// rename manifest ...
		$retour = JFile::move('_mod_viewmyjspace.xml','mod_viewmyjspace.xml',JPATH_ROOT.DS.'modules'.DS.'mod_viewmyjspace');

		if ($retour != 1)
			echo "Retour:".$retour;

		return true;
	}
}

?>
