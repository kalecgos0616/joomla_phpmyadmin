<?php
/*
* @version $Id: mod_viewmyjspace.php $
* @version		2.4.0 01/06/2014
* @author       Bernard Saulmé
* @copyright	Copyright (C) 2010-2011-2012-2013-2014 Bernard Saulmé
* @license      GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
*/

// Pas d'acces direct
defined('_JEXEC') or die;
if (!defined('DS')) define('DS', DIRECTORY_SEPARATOR);

if (@file_exists(JPATH_ROOT.DS.'components'.DS.'com_myjspace'.DS.'helpers'.DS.'user.php')) {

	// Include once mod_viewmyjspace functions
	require_once (dirname(__FILE__).DS.'helper.php');

	// Parameter reading
	$showmode = $params->get('showmode', 1);  // Counter or page list or both
	$countmode = $params->get('countmode', 0);  // Counter mode
	$separ = $params->get('separ', 0); // list, blocks
	$showmode1 = $params->get('showmode1', 1); // Content to be displayed
	$trie_tmp = $params->get('triemode', 2);
	$affmax = $params->get('affmax', 20);
	$affimgcon = $params->get('affimgcon', 1);
	$delaisimgcon = intval($params->get('delais', 604800));
	$emptymode = $params->get('emptymode', 0);
	$nonvisiblemode = $params->get('nonvisiblemode', 0);
	$catid_list = trim($params->get('catid_list', ''));
	$title_limit = intval($params->get('title_limit', 20)); // Max num chars for title
	// Form mode list 0 & 1
	$search_block_width = intval($params->get('search_block_width', 65)); // Max img/block width
	$search_block_height = intval($params->get('search_block_height', 85)); // Max img/block height
	$search_block_width_min = intval($params->get('search_block_width_min', 65)); // Min img/block width
	$search_block_height_min = intval($params->get('search_block_height_min', 85)); // Min img/block height
	$description_limit = intval($params->get('description_limit', 45)); // Max num chars for category if used
	$search_image_default = trim($params->get('search_image_default', 'components/com_myjspace/images/default.png')); // Default img name
	// For mode list (separ = 0)
	$showmode2 = $params->get('showmode2', 1); // Content orientation
	$showmode0 = $params->get('showmode0', 'inherit'); // Content align
	// For mode block | wall (separ = 1 | 3)
	$search_image_type = $params->get('search_image_type', 2);
	$search_image_video = $params->get('search_image_video', 1);
	$content_limit = intval($params->get('content_limit', 150)); // Max num chars for content if used
	// For image
	$search_image_effect = $params->get('search_image_effect', 3); // 0 non, 1 opacity, 2 zoom (for wall) (param to be added)

	// Component param
	$pparams = JComponentHelper::getParams('com_myjspace');
	$foldername = $pparams->get('foldername', 'myjsp');
	$language_filter = $pparams->get('language_filter', 0); // Filter by language
	$link_folder_print = $pparams->get('link_folder_print', 1); // Style for URL Joomla or folder
	$link_folder = $pparams->get('link_folder', 1);

	if ($language_filter > 0) { // filter by language
		$lang = JFactory::getLanguage();
		$language = $lang->getTag();
	} else
		$language = '';

	if ($catid_list != '')
		$catid_list = explode(',', $catid_list);

	// User Access
	$user = JFactory::getuser();
	$user_mode_view_acl = $pparams->get('user_mode_view_acl', 0);
	if (version_compare(JVERSION, '1.6.0', 'ge') && $user_mode_view_acl == 1)
		$access = $user->getAuthorisedViewLevels();
	else
		$access = array();	

	// Itemid = get the menu see id's
	if ($link_folder_print == 0) {
		require_once JPATH_ROOT.DS.'components'.DS.'com_myjspace'.DS.'helpers'.DS.'util.php';
		$app = JFactory::getApplication();
		$menu = $app->getMenu();
		$defaultMenu = $menu->getDefault();
		$itemid = $defaultMenu->id;
		$itemid = get_menu_itemid('index.php?option=com_myjspace&view=see', $itemid);
		$itemid = get_menu_itemid('index.php?option=com_myjspace&view=see&id=&pagename=', $itemid);
	} else {
		$itemid = 0;
	}

	// Display checked
	if (is_array($showmode1)) { // for J!1.6+
		$search_tmp = 0;
		foreach ($showmode1 as $i => $value) {
			$search_tmp += $value;
		}
		$showmode1 = $search_tmp;
	} else
		$showmode1 = intval($showmode1);

	if ($showmode1 < 0 || $showmode1 > 511) // Control
		$showmode1 = 1;

	if ($showmode1 & 64 && $showmode1 > 1)
		$showmode1_query = $showmode1 | 256;
	else
		$showmode1_query = $showmode1;

	if ($showmode == 1 || $showmode == 2)
		$names = modViewMyJspaceHelper::getListPage($trie_tmp, $affmax, $emptymode, $nonvisiblemode, 1, $showmode1_query, $catid_list, $language);
	else
		$names = null;

	if ($showmode == 0 || $showmode == 2)
		$nbpages = modViewMyJspaceHelper::getNbPage($emptymode, $nonvisiblemode, 1, $catid_list, $language);
	else
		$nbpages = -1;

	// Mode block style
	if ($separ == 1) {
		$search_image_width = intval($search_block_width - min($search_block_width * 0.2, 30));
		$search_image_height = intval($search_image_width * 0.75);
		$style_str = "
/* mod_viewmyjspace */
.mod-myjsp-blocks div.icon a {
	max-width: {$search_block_width}px;
	max-height: {$search_block_height}px;
	min-width: {$search_block_width_min}px;
	min-height: {$search_block_height_min}px;
}	
.mod-myjsp-blocks img {
	max-width: {$search_image_width}px;
	max-height: {$search_image_height}px;
}	
";
		$chaine_ie = "
<!--[if lte IE 7]>
<style type=\"text/css\">
.mod-myjsp-blocks img {
	height: {$search_block_width}px;
}
</style>
<![endif]-->			
";
	} else if ($separ == 3) {
	$search_block_width_grow = $search_block_width * 2;
	$search_block_height_grow = $search_block_height * 2;
	$search_block_width_min_grow = $search_block_width_min * 2;
	$search_block_height_min_grow = $search_block_height_min * 2;
		$style_str = "
/* mod_viewmyjspace */
.mod-myjsp-blocks2 a:hover { background-color: transparent; }
 
.mod-myjsp-blocks2 .pic {
	max-width: {$search_block_width}px;
	max-height: {$search_block_height}px;
	min-width: {$search_block_width_min}px;
	min-height: {$search_block_height_min}px;
	overflow: hidden;
	float: left;
	
	opacity: 1;
	filter: alpha(opacity=100);
}

.mod-myjsp-blocks2 .grow img {
	max-width: {$search_block_width}px;
	max-height: {$search_block_height}px;
	min-width: {$search_block_width_min}px;
	min-height: {$search_block_height_min}px;
 
	-webkit-transition: all 1s ease;
	-moz-transition: all 1s ease;
	-o-transition: all 1s ease;
	-ms-transition: all 1s ease;
	transition: all 1s ease;
}

.mod-myjsp-blocks2 .grow img:hover {
";
	if ($search_image_effect & 1) {
	$style_str .= "
	max-width: {$search_block_width_grow}px;
	max-height: {$search_block_height_grow}px;
	min-width: {$search_block_width_min_grow}px;
	min-height: {$search_block_height_min_grow}px;
	";
	}
	if ($search_image_effect & 2) {
	$style_str .= "
	opacity: 0.3;
	filter: alpha(opacity=30);
	";}
$style_str .= "
}
";
		$chaine_ie = "
<!--[if lte IE 7]>
<style type=\"text/css\">
.mod-myjsp-blocks img {
	height: {$search_block_width}px;
}
</style>
<![endif]-->			
";
	} else {
		$style_str = "
.mod-img_preview {
	max-width: {$search_block_width}px;
	max-height: {$search_block_height}px;
}
	";
		$chaine_ie = "";
	}

	require(JModuleHelper::getLayoutPath('mod_viewmyjspace'));
}

?>
