<?php
/*
* @version $Id: helper.php $
* @version		2.4.0 01/06/2014
* @package		mod_viewmyjspace
* @author       Bernard Saulmé
* @copyright	Copyright (C) 2010-2011-2012-2013-2014 Bernard Saulmé
* @license      GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
*/

// Pas d'acces direct
defined('_JEXEC') or die;
if (!defined('DS')) define('DS', DIRECTORY_SEPARATOR);

class modViewMyJspaceHelper {

	// Retrieve pagename & id & content
	public static function getListPage($triemode = 0, $affmax = 0, $emptymode = 0, $nonvisiblemode = 0, $publish = 1, $resultmode = 0, $catid_list = '', $language = '') {
		$db	= JFactory::getDBO();
		$result	= null;

		$query = "SELECT DISTINCT mjs.id, mjs.userid, jos.userid AS connect, mjs.title, mjs.pagename, mjs.last_update_date, mjs.blockView";
		// id (username) = 1, pagename = 2, last_update_date = 16 for display (search)			
		if ($resultmode & 4)
			$query .= ", mjs.metakey";
		if ($resultmode & 8)
			$query .= ", mjs.create_date";
		if ($resultmode & 32)
			$query .= ", mjs.hits";
		if ($resultmode & 256)
			$query .= ", mjs.content";
		// 64 for image (search)
		$query .= " FROM `#__myjspace` mjs LEFT JOIN `#__session` jos ON mjs.userid=jos.userid WHERE 1=1 ";

		if ($emptymode == 0)
			$query .= " AND mjs.content != '' ";

		if ($nonvisiblemode == 0)
			$query .= " AND blockView != 0";

		if ($publish == 1 && $nonvisiblemode == 0)
			$query .= " AND mjs.publish_up < NOW() AND (mjs.publish_down >= NOW() OR mjs.publish_down = '0000-00-00 00:00:00')";

		if ($catid_list != '') {
			$nb_catid = count($catid_list);
			for ($i = 0; $i < $nb_catid; $i++)
				$catid_list[$i] = $db->Quote($catid_list[$i]);
			$catid_list_str = implode(',', $catid_list);
			$query .= " AND `catid` IN (".$catid_list_str.")";
		}			

		if ($language)
			$query .= " AND `language` IN ('*',".$db->Quote($language).")";

		if ($triemode == 0)
			$query .= " ORDER BY mjs.pagename ASC";
		else if ($triemode == 1)
			$query .= " ORDER BY mjs.pagename DESC";
		else if ($triemode == 2)
			$query .= " ORDER BY RAND()";
		else if ($triemode == 3)
			$query .= " ORDER BY mjs.create_date DESC";
		else if ($triemode == 4)
			$query .= " ORDER BY mjs.last_update_date DESC";
		else if ($triemode == 5)
			$query .= " ORDER BY mjs.hits DESC";
			
		if ($affmax != 0)
			$query .= " LIMIT $affmax";

		$db->setQuery($query);
		$result = $db->loadObjectList();

		if ($db->getErrorNum()) {
			JError::raiseWarning( 500, $db->stderr() );
		}
		
		return $result;
	}

	// Retrieve page number
	public static function getNbPage($emptymode = 0, $nonvisiblemode = 0, $publish = 1, $catid_list = '', $language = '') {
		$db = JFactory::getDBO();
		$result	= null;

		// Select page
		$query = "SELECT COUNT(*) FROM `#__myjspace` WHERE 1 = 1 ";

		if ($emptymode == 0)
			$query .= " AND `content` != '' ";

		if ($nonvisiblemode == 0)
			$query .= " AND `blockView` != 0";

		if ($publish == 1 && $nonvisiblemode == 0)
			$query .= " AND `publish_up` < NOW() AND (`publish_down` >= NOW() OR `publish_down` = '0000-00-00 00:00:00')";

		if ($catid_list != '') {
			$nb_catid = count($catid_list);
			for ($i = 0; $i < $nb_catid; $i++)
				$catid_list[$i] = $db->Quote($catid_list[$i]);
			$catid_list_str = implode(',', $catid_list);
			$query .= " AND `catid` IN (".$catid_list_str.")";
		}

		if ($language)
			$query .= " AND `language` IN ('*',".$db->Quote($language).")";

		$db->setQuery($query);
		$result = $db->loadResult();
		if ($db->getErrorNum()) {
			JError::raiseWarning(500, $db->stderr());
		}

		if ($result == null)
			$result = 0;

		return $result;
	}

	// Image connected/not connected or updated since 'delay'
	public static function aff_img($connecte = 0, $dateupdate = '0000-00-00 00:00:00', $delais = 0, $affimgcon = 0) {
		if ($affimgcon != 0) {

			$link_pre = 'modules/mod_viewmyjspace/images/';

			if ($connecte) // Connected
				$retour = '<img src="'.$link_pre.'tick.png" style="width:10px; border:none; margin-left:3px;margin-right:3px;" alt="" />';
			else
				$retour = '<img src="'.$link_pre.'rating_star_blank.png" style="width:10px; border:none; margin-left:3px;margin-right:3px;" alt="" />';

			// If option for page updated since 'delay'
			if ($delais != 0 && (time() - strtotime($dateupdate)) < $delais && ($connecte))
				$retour = '<img src="'.$link_pre.'rating_star_green.png" style="width:10px; border:none; margin-left:3px;margin-right:3px;" alt="" />';
			if ($delais != 0 && (time() - strtotime($dateupdate)) < $delais && $delais != 0 && !($connecte))
				$retour = '<img src="'.$link_pre.'rating_star.png" style="width:10px; border:none; margin-left:3px;margin-right:3px;" alt="" />';
		} else
			$retour = '';

		return $retour;
	}

}

?>
