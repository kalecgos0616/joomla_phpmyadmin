<?php
/**
* @version $Id: install.php $
* @version		2.1.0 08/06/2013
* @package		plg_quickiconmyjspace
* @author       Bernard Saulmé
* @copyright	Copyright (C) 2012-2013 Bernard Saulmé
* @license      GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
*/

// no direct access
defined('_JEXEC') or die;

/*
 * Enable, the plugin after install J1.6+
 */
 
class plgQuickiconmyjspaceInstallerScript {
	function postflight($type, $parent) {
		
		$group = 'quickicon';
		$element = 'myjspace';

		if ($type == 'install') { // Enable plugin
			$this->enablePlugin($group, $element);
		}
	}

 	function enablePlugin($group, $element) {
		$plugin = JTable::getInstance('extension');
		if (!$plugin->load(array('type'=>'plugin', 'folder'=>$group, 'element'=>$element))) {
			return false;
		}
		$plugin->enabled = 1;
		return $plugin->store();
	}
}

?>
