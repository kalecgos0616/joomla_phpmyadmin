<?php
/**
* @version $Id: install.php $
* @version		2.1.0 08/06/2013
* @package		plg_myjspacesearch
* @author       Bernard Saulmé
* @copyright	Copyright (C) 2011-2012-2013 Bernard Saulmé
* @license      GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
*/

defined('_JEXEC') or die;
if (!defined('DS')) define('DS', DIRECTORY_SEPARATOR);

/*
 * Enable, the plugin after install // >= J!1.6
 */
 
class plgSearchmyjspacesearchInstallerScript {
	function postflight($type, $parent) {

		$app = JFactory::getApplication();

		// Get this plugin group, element
		$group = 'search';
		$element = 'myjspacesearch';

		// Rename manifest ...
		$retour = JFile::move('_'.$element.'.xml',$element.'.xml',JPATH_ROOT.DS.'plugins'.DS.$group.DS.$element);
		if ($retour != 1)
			$app->enqueueMessage('Retour:'.$retour, 'error');

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
