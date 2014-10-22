<?php
/**
* @version $Id: util.php $
* @version		2.1.0 07/06/2013
* @package		com_myjspace
* @author       Bernard Saulmé
* @copyright	Copyright (C) 2013 Bernard Saulmé
* @license      GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
*/

// Pas d'accès direct
defined('_JEXEC') or die;
if (!defined('DS')) define('DS', DIRECTORY_SEPARATOR);

function myjsp_getFormToken() {
	if (version_compare(JVERSION, '1.6.0', 'lt')) {
		$token = JUtility::getToken();
	} else {
		$token = JSession::getFormToken();
	}
	return $token;
}

?>
