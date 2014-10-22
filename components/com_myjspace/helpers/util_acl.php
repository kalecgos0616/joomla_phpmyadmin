<?php
/**
* @version $Id: util_acl.php $
* @version		2.3.0 12/10/2013
* @package		com_myjspace
* @author       Bernard Saulmé
* @copyright	Copyright (C) 2010-2011-2012-2013 Bernard Saulmé
* @license      GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die;
if (!defined('DS')) define('DS', DIRECTORY_SEPARATOR);

function get_assetgroup_list($all = false) {

	$pparams = JComponentHelper::getParams('com_myjspace');

	$group_list[0] = new stdClass();
	$group_list[0]->value = 0;
	$group_list[0]->text = JText::_('COM_MYJSPACE_TITLEMODEVIEW1');

	if (version_compare(JVERSION, '1.6.0', 'ge') && ($pparams->get('user_mode_view_acl', 0) == 1 || $all)) {
		$group_list = array_merge($group_list, JHtml::_('access.assetgroups'));
	} else {
		$group_list[1] = new stdClass();
		$group_list[1]->value = 1;
		$group_list[1]->text = JText::_('COM_MYJSPACE_TITLEMODEVIEW0');
		$group_list[2] = new stdClass();
		$group_list[2]->value = 2;
		$group_list[2]->text = JText::_('COM_MYJSPACE_TITLEMODEVIEW2');
	}

	return $group_list;
}

function get_assetgroup_label($access = 0, $all = false) {

	$group_list = get_assetgroup_list($all);
	foreach ($group_list as $value) {
		if ($value->value == $access) {
			return $value->text;
		}
	}

	return '';
}

?>
