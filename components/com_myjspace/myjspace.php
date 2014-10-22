<?php
/**
* @version $Id: myjspace.php $ 
* @version		2.3.4 15/04/2014
* @package		com_myjspace
* @author       Bernard Saulmé
* @copyright	Copyright (C) 2010-2011-2012-2013 Bernard Saulmé
* @license      GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die;
if (!defined('DS')) define('DS', DIRECTORY_SEPARATOR);

// Controller
if (version_compare(JVERSION, '2.5.6', 'ge')) { 
	$controller	= JControllerLegacy::getInstance('myjspace');
} else { // Allow Legacy for J!1.5, J!1.6, J!1.7, J!2.5 < 2.5.6
	require_once JPATH_COMPONENT.DS.'controller.php';
	if ($controller = JRequest::getWord('controller')) {
		$path = JPATH_COMPONENT.DS.'controllers'.DS.$controller.'.php';
		if (@file_exists($path)) {
			require_once $path;
		} else {
			$controller = '';
		}
	}
	$classname	= 'MyjspaceController'.$controller;
	$controller = new $classname();
}
// Execute request
$controller->execute(JRequest::getCmd('task'));
// Redirection if set by the controller
$controller->redirect();
?>
