<?php
/**
* @version $Id: install.php $
* @version		2.3.0 24/10/2013
* @package		plg_myjsp
* @author       Bernard Saulmé
* @copyright	Copyright (C) 2013 Bernard Saulmé
* @license      GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
*/

defined('_JEXEC') or die;
if (!defined('DS')) define('DS', DIRECTORY_SEPARATOR);

/*
 * Enable, the plugin after install >= J1.6
 */

class plgeditorsmyjspInstallerScript {
	function postflight($type, $parent) {

		$app = JFactory::getApplication();

		// Cleanup old files from previous versions (v2.2.2)
		$plugin_base = JPATH_PLUGINS.DS.'editors'.DS.'myjsp';
		@unlink($plugin_base.DS.'tiny_mce'.DS.'plugins'.DS.'myjspuploader'.DS.'img'.DS.'progress.gif');
		@unlink($plugin_base.DS.'tiny_mce'.DS.'plugins'.DS.'myjspuploader'.DS.'file_list_js.php');
		@unlink($plugin_base.DS.'tiny_mce'.DS.'plugins'.DS.'myjspuploader'.DS.'session_joomla.php');
		@unlink($plugin_base.DS.'tiny_mce'.DS.'plugins'.DS.'myjspuploader'.DS.'uploader.php');

		// Get this plugin group, element
		$group = 'editors';
		$element = 'myjsp';

		// Rename manifest ...
		$retour = JFile::move('_'.$element.'.xml',$element.'.xml',JPATH_ROOT.DS.'plugins'.DS.$group.DS.$element);
		if ($retour != 1)
			$app->enqueueMessage('Retour:'.$retour, 'error');

   }
}
?>
