<?php
/**
* @version $Id: legacy.php $
* @version		2.0.3 20/10/2012
* @package		com_myjspace
* @author       Bernard Saulmé
* @copyright	Copyright (C) 2012 Bernard Saulmé
* @license      GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
*/

// Pas d'accès direct
defined('_JEXEC') or die;

// Allow Legacy for J!1.5, J!1.6, J!1.7, J!2.5 < 2.5.6
if (version_compare(JVERSION, '2.5.6', 'lt')) {
	jimport('joomla.application.component.controller');
	jimport('joomla.application.component.view');
	class JControllerLegacy extends JController {};
	class JViewLegacy extends JView {};
}

?>
