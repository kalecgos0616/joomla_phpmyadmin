<?php
/**
* @version $Id: search_fct.php $
* @version		2.4.8 13/06/2014
* @package		com_myjspace
* @author       Bernard Saulmé
* @copyright	Copyright (C) 2014 Bernard Saulmé
* @license      GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die;
if (!defined('DS')) define('DS', DIRECTORY_SEPARATOR);

require_once JPATH_COMPONENT_SITE.DS.'helpers'.DS.'user.php';
require_once JPATH_COMPONENT_SITE.DS.'helpers'.DS.'util.php';
require_once JPATH_COMPONENT_SITE.DS.'helpers'.DS.'util_acl.php';

class BSHelperViewSearch
{
	// inst (1)									: usually '$this' for call from view.html.php
	// separ_default (null) 					: display type (liste as default = 0), raw (1), block (2), wall(4)
	//												if null use the global option else use the value
	// search_aff_add_default (null) 			: columns to display (**)
	// type_search (1)							: pages with content (1), all (0)
	// publish (1)								: only page publushed (1), all (0)
	// search_advanced_criteria_default (null)	: display advance citeria (**)
	// language_filter_default (null)			: use the langue filter, (all page = 0)
	// rss_feed_default (null)					: display rss feed icon (0 = none)
	// extra_query (null)						: extra SQL query
	
	// (**) to be add into the default.xml as option to be selected else = constant
	//		is null use the (regular( default option, else use the value as default

	public static function pre_display($inst = null, $separ_default = null, $search_aff_add_default = null, $type_search = 1, $publish = 1, $search_advanced_criteria_default = null, $language_filter_default = null, $rss_feed_default = null, $extra_query = null) {		
		// Config
		$pparams = JComponentHelper::getParams('com_myjspace');
	  	$user = JFactory::getuser();
		$app = JFactory::getApplication();
		$document = JFactory::getDocument();
		$params = $app->getParams();

		// Param url
		$aff_sort = JRequest::getInt('sort', $params->get('sort', 4)); // Sort order
		$svalue = JRequest::getVar('svalue', $params->get('svalue', '')); // Search key for search content value
		$catid = JRequest::getInt('catid', $params->get('catid', 0)); // catid
		$layout = JRequest::getVar('layout', '');

		// Itemid(s)
		$Itemid = JRequest::getInt('Itemid', 0);
		$Itemid_see = get_menu_itemid('index.php?option=com_myjspace&view=see', $Itemid); // Compatibility old install
		$Itemid_see = get_menu_itemid('index.php?option=com_myjspace&view=see&id=&pagename=', $Itemid_see);
		$Itemid_config = get_menu_itemid('index.php?option=com_myjspace&view=config', $Itemid, $catid);
		$Itemid_edit = get_menu_itemid('index.php?option=com_myjspace&view=edit', $Itemid, $catid);
		$Itemid_delete = get_menu_itemid('index.php?option=com_myjspace&view=delete', $Itemid, $catid);

		// View options
		$separ = ($separ_default !== null) ? $separ_default : $params->get('separ', 0); // List-tab (0), raw (1), blocks (2), Wall (3) // Do no use the Option if paramete passed
		$aff_select = $params->get('select', 1); // Print the search selector
		$search_pagination = $params->get('search_pagination', 1);
		$search_max_line = intval($params->get('search_max_line', 200));
		$search_image_type = $params->get('search_image_type', 2);
		$search_image_effect_list = $params->get('search_image_effect_list', 2); // Effect on image click: Lytebox usage & preview, redirection
		$title_limit = intval($params->get('title_limit', 20)); // Max num chars for title if used
		$content_limit = intval($params->get('content_limit', 150)); // Max num chars for content if used
		$description_limit = intval($params->get('description_limit', 45)); // Max num chars for category if used
		$search_page_title = trim($params->get('search_page_title', '')); // Page title
		$search_image_default = trim($params->get('search_image_default', 'components/com_myjspace/images/default.png')); // Default img name	
		$search_block_width = intval($params->get('search_block_width', 150));
		$search_block_height = intval($params->get('search_block_height', 200));
		$search_block_width_min = intval($params->get('search_block_width_min', 150)); // Min img/block width
		$search_block_height_min = intval($params->get('search_block_height_min', 200)); // Min img/block height				
		$search_labels = intval($params->get('search_labels', 1));
		$search_image_video = intval($params->get('search_image_video', 1));

		$search_advanced_criteria_default = ($search_advanced_criteria_default !== null) ? $search_advanced_criteria_default : 1;
		$search_advanced_criteria = $params->get('search_advanced_criteria', $search_advanced_criteria_default);
		$search_image_effect = $params->get('search_image_effect', 3); // 0 non, 1 opacity, 2 zoom (for wall) (param to be added)
		$search_sort_use = $params->get('search_sort_use', 1); // Short using the column header

		// Table ordering
		$option = JRequest::getCmd('option');
		$lists['order'] = $app->getUserStateFromRequest("$option.filter_order", 'filter_order', 'hits', 'cmd');
		$lists['order_Dir']	= $app->getUserStateFromRequest("$option.filter_order_Dir", 'filter_order_Dir', '', 'word');
		if ($separ <= 1) { // Only for list & row mode
			$aff_sort = '`'.$lists['order'].'`'.' '.$lists['order_Dir'];
			// Check sort validity
			$allowed_field = array('pagename', 'userid', 'create_date', 'last_update_date', 'hits', 'catid', 'metakey', 'size', 'language', 'blockView');
			$allowed_order = array('asc', 'desc');
			if (!in_array($lists['order'], $allowed_field) || !in_array($lists['order_Dir'], $allowed_order))
				$aff_sort = '`last_update_date` desc'; // Default 'last updated' (4)
		}

		// Language
		$language_filter_default = ($language_filter_default !== null) ? $language_filter_default : 1;
		$language_filter = $params->get('language_filter', $language_filter_default);
		if ($language_filter > 0) { // Filter by language
			$lang = JFactory::getLanguage();
			$language = $lang->getTag();
		} else
			$language = '';
		if (version_compare(JVERSION, '1.6.0', 'ge'))
			$languages = JLanguageHelper::getLanguages('lang_code'); // languages list
		else
			$languages = array();

		// Categories
		$categories = BSHelperUser::GetCategories(1);
		$categories_label = BSHelperUser::GetCategoriesLabel($categories);

		$limit = $app->getUserStateFromRequest('global.list.limit', 'limit', $app->getCfg('list_limit'), 'int');
		$limitstart = JRequest::getInt('limitstart', 0); 
		if ($limit > $search_max_line)
			$limit = $search_max_line;

		// Display checked columns
		$search_aff_add_default = ($search_aff_add_default !== null) ? $search_aff_add_default : array(64,2,1);
		$search_aff_add = $params->get('search_aff_add', $search_aff_add_default);

		if (is_array($search_aff_add)) { // for J!1.6+
			$search_tmp = 0;
			foreach ($search_aff_add as $i => $value) {
				$search_tmp += $value;
			}
			$search_aff_add = $search_tmp;
		} else
			$search_aff_add = intval($search_aff_add);

		if ($search_aff_add & 64 && $search_image_type > 1)
			$search_aff_add_query = $search_aff_add | 256;
		else
			$search_aff_add_query = $search_aff_add;

		// Folder root dir & url
		$link_folder = $pparams->get('link_folder', 1);
		$link_folder_print = ($link_folder == 1) ? $pparams->get('link_folder_print', 1) : 0;
		$foldername = ($link_folder == 1) ? BSHelperUser::getFoldername() : null;

		// Selection checked
		$check_search = JRequest::getVar('check_search', $params->get('check_search', array('name', 'content', 'description')));
		foreach ($check_search as $i => $value) {
			$check_search_asso[$value] = '1';
		}

		// xml sitemap
		if ($layout == 'sitemap') { // All content out of $catid filter
			$aff_sort = 3; // Order by creation date
			$limit = 0;
			$check_search_asso = null;
			$svalue = '';
			$search_aff_add_query = 3;
			$limitstart = 0;
			$language = '';
		}

		// Authorization & search
		$total = 0;
		if ($limit >= 0) {
			if ($layout != 'sitemap') {
				jimport('joomla.html.pagination');
				$total = BSHelperUser::loadPagename($aff_sort, 0, 1, $publish, $type_search, $check_search_asso, $svalue, $search_aff_add_query, 0, true, $catid, $language, $extra_query);
				$pagination = new JPagination($total, $limitstart, $limit);
			} else {
				$pagination = new stdClass();
			}

			$result = BSHelperUser::loadPagename($aff_sort, $limit, 1, $publish, $type_search, $check_search_asso, $svalue, $search_aff_add_query, $limitstart, false, $catid, $language, $extra_query);
		} else {
			$result = array();
			$aff_select = 0;
			$search_page_title = '';
			$pagination = new stdClass();
		}

		// User Access (ACL)
		$user_mode_view_acl = $pparams->get('user_mode_view_acl', 0);
		if (version_compare(JVERSION, '1.6.0', 'ge') && $user_mode_view_acl == 1)
			$access = $user->getAuthorisedViewLevels();
		else
			$access = array();	

		// Rss feed
		$url_rss_feed = '';
		$rss_feed_default = ($rss_feed_default !== null) ? $rss_feed_default : 1;
		$rss_feed = $params->get('rss_feed', $rss_feed_default);
		if ($rss_feed > 0) {
			$url = 'index.php?option=com_myjspace&view=search&format=feed';
			if ($catid != 0 && !is_array($catid))
				$url .= '&catid='.$catid;
			if ($svalue != '')
				$url .= '&svalue='.$svalue;
			if (is_numeric($aff_sort) && $aff_sort != 4)
				$url .= '&sort='.$aff_sort;
			$url .= '&type=rss';
			$url_rss_feed = '<a href="'.JRoute::_($url).'"><img src="components/com_myjspace/images/rss.gif" alt="rss" title="rss" /></a>';
			$document->addHeadLink(JRoute::_($url), 'alternate', 'rel', array('type' => 'application/rss+xml', 'title' => 'RSS 1.0'));
		}

		// Mode block style
		if ($separ == 2) {
			$search_image_width = intval($search_block_width - min($search_block_width * 0.2, 30));
			$search_image_height = intval($search_image_width * 0.75);
			$style_str = "
/* com_myjspace */
.myjsp-blocks div.icon a {
	max-width: {$search_block_width}px;
	max-height: {$search_block_height}px;
	min-width: {$search_block_width_min}px;
	min-height: {$search_block_height_min}px;
}
.myjsp-blocks img {
	width: {$search_image_width}px;
	height: {$search_image_height}px;
}	
";
			$chaine_ie ="
<!--[if lte IE 7]>
<style type=\"text/css\">
.myjsp-blocks img {
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
/* com_viewmyjspace */
.myjsp-blocks2 a:hover { background-color: transparent; }
 
.myjsp-blocks2 .pic {
	max-width: {$search_block_width}px;
	max-height: {$search_block_height}px;
	min-width: {$search_block_width_min}px;
	min-height: {$search_block_height_min}px;

	overflow: hidden;
	float: left;
	
	opacity: 1;
	filter: alpha(opacity=100);
}

.myjsp-blocks2 .grow img {
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

.myjsp-blocks2 .grow img:hover {
	 background-color: transparent;
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
.myjsp-blocks img {
	height: {$search_block_width}px;
}
</style>
<![endif]-->			
";
	} else {
			$style_str = '';
	}

		// Var assign
		$inst->assignRef('Itemid', $Itemid);
		$inst->assignRef('Itemid_see', $Itemid_see);
		$inst->assignRef('Itemid_config', $Itemid_config);
		$inst->assignRef('Itemid_edit', $Itemid_edit);
		$inst->assignRef('Itemid_delete', $Itemid_delete);
		$inst->assignRef('search_page_title', $search_page_title);
		$inst->assignRef('aff_select', $aff_select);
		$inst->assignRef('aff_sort', $aff_sort);
		$inst->assignRef('svalue', $svalue);
		$inst->assignRef('separ', $separ);		
		$inst->assignRef('result', $result);
		$inst->assignRef('search_aff_add', $search_aff_add);
		$inst->assignRef('search_aff_add_query', $search_aff_add_query);
		$inst->assignRef('search_image_effect_list', $search_image_effect_list);	
		$inst->assignRef('link_folder', $link_folder);
		$inst->assignRef('link_folder_print', $link_folder_print);
		$inst->assignRef('foldername', $foldername);
		$inst->assignRef('check_search_asso', $check_search_asso);
		$inst->assignRef('pagination', $pagination);
		$inst->assignRef('search_pagination', $search_pagination);
		$inst->assignRef('catid', $catid);
		$inst->assignRef('categories', $categories);
		$inst->assignRef('categories_label', $categories_label);
		$inst->assignRef('url_rss_feed', $url_rss_feed);
		$inst->assignRef('title_limit', $title_limit);
		$inst->assignRef('content_limit', $content_limit);
		$inst->assignRef('description_limit', $description_limit);
		$inst->assignRef('user', $user);
		$inst->assignRef('access', $access);
		$inst->assignRef('user_mode_view_acl', $user_mode_view_acl);
		$inst->assignRef('search_image_type', $search_image_type);
		$inst->assignRef('search_image_default', $search_image_default);
		$inst->assignRef('style_str', $style_str);
		$inst->assignRef('chaine_ie', $chaine_ie);
		$inst->assignRef('search_labels', $search_labels);
		$inst->assignRef('search_image_video', $search_image_video);
		$inst->assignRef('search_advanced_criteria', $search_advanced_criteria);
		$inst->assignRef('languages', $languages);
		$inst->assignRef('language_filter', $language_filter);
		$inst->assignRef('app', $app);
		$inst->assignRef('lists', $lists);
		$inst->assignRef('search_sort_use', $search_sort_use);

		return $total;
	}

	// Transform the page data to data to be displayed (options dependant)
	public static function transform_fields($inst, $i = 0) {

		$link_pre = "components/com_myjspace/images/";

		// Set default return variables
		$aff = new stdClass();
		$aff->pagename = ($inst->search_aff_add & 1) ? true : false;
		$aff->username = ($inst->search_aff_add & 2) ? true : false;
		$aff->description = ($inst->search_aff_add & 4) ? true : false;
		$aff->create_date = ($inst->search_aff_add & 8) ? true : false;
		$aff->update_date = ($inst->search_aff_add & 16) ? true : false;
		$aff->hits = ($inst->search_aff_add & 32) ? true : false;
		$aff->image = ($inst->search_aff_add & 64) ? true : false;
		$aff->category = ($inst->search_aff_add & 128) ? true : false;
		$aff->content = ($inst->search_aff_add & 256) ? true : false;
		$aff->size = ($inst->search_aff_add & 512) ? true : false;
		$aff->blockview = ($inst->search_aff_add & 1024) ? true : false;
		$aff->language = ($inst->search_aff_add & 2048) ? true : false;
		$aff->share_page = '';
		$aff->blockView_alt = '';
		$aff->page_url = '';
		$aff->title = '';
		$aff->text = '';
		$aff->local_folder = null;
		$aff->select = '';

		// Select
		$prefix = (version_compare(JVERSION, '1.6.0', 'ge')) ? 'Joomla.' : '';
		$aff->select = '<input type="radio" id="cb'.$i.'" name="cid[]" value="'.$inst->result[$i]['id'].'" onclick="'.$prefix.'isChecked(this.checked);" />';

		// Title
		$aff->title = clean_text($inst->result[$i]['title'], $inst->title_limit);

		if ($inst->link_folder_print)
			$aff->page_url = JURI::base(true).'/'.$inst->foldername.'/'.$inst->result[$i]['pagename'].'/';
		else
			$aff->page_url = Jroute::_('index.php?option=com_myjspace&view=see&pagename='.$inst->result[$i]['pagename'].'&Itemid='.$inst->Itemid_see);

		if ($inst->search_aff_add & 1 || $inst->separ >= 2) // Pagename (1)
			$aff->pagename = true;

		if ($inst->search_aff_add & 64) { // Image (64)
			if ($inst->search_aff_add_query & 256)
				$aff->text = $inst->result[$i]['content'];
			else
				$aff->text = '';

			if ($inst->link_folder == 1)
				$aff->local_folder = $inst->foldername.'/'.$inst->result[$i]['pagename'];
			else
				$aff->local_folder = null;

			if ($inst->separ != 3)
				$aff->image = exist_image_html($aff->local_folder, JPATH_SITE, 'img_preview', $inst->search_image_effect_list, $inst->result[$i]['title'], 'preview.jpg', $inst->search_image_default, $inst->search_image_type, $aff->text, $inst->search_image_video, $aff->page_url);
		}

		if ($inst->search_aff_add & 2) { // Username (2)
			$table = JUser::getTable();
			if ($table->load($inst->result[$i]['userid'])) { // Test if user exists before retrieving info
				$user = JFactory::getUser($inst->result[$i]['userid']);
			} else { // User does no exist any more !
				$user = new stdClass();
				$user->id = 0;
				$user->username = '';
			}
			$aff->username = $user->username;
		}

		if ($inst->search_aff_add & 8) { // Date created (8)
			$aff->create_date = date(JText::_('COM_MYJSPACE_DATE_FORMAT2'), strtotime($inst->result[$i]['create_date']));
		}

		if ($inst->search_aff_add & 16) { // Date updated (16)
			$aff->update_date = date(JText::_('COM_MYJSPACE_DATE_FORMAT2'), strtotime($inst->result[$i]['last_update_date']));
		}

		if ($inst->search_aff_add & 32) { // Hits (32)
			$aff->hits = $inst->result[$i]['hits'];
		}

		if ($inst->search_aff_add & 128) { // Category (128)
			if (isset($inst->categories_label[$inst->result[$i]['catid']]))
				$aff->category = $inst->categories_label[$inst->result[$i]['catid']];
			else
				$aff->category = '';
		}

		if ($inst->search_aff_add & 4) { // Description (4)
			$blockView = $inst->result[$i]['blockView'];
			if ($blockView >= 2 && (($inst->user_mode_view_acl == 0 && $inst->user->id <= 0) || ($inst->user_mode_view_acl == 1 && !in_array($blockView, $inst->access))))
				$aff->description = ' ';
			 else
				$aff->description = clean_text($inst->result[$i]['metakey'], $inst->description_limit).' '; 
		}

		if ($inst->search_aff_add & 256) { // Content (256) with no html & only some characters
			$blockView = $inst->result[$i]['blockView'];
			if ($blockView >= 2 && (($inst->user_mode_view_acl == 0 && $inst->user->id <= 0) || ($inst->user_mode_view_acl == 1 && !in_array($blockView, $inst->access)))) {
				$$aff->blockView_alt = get_assetgroup_label($blockView);
				$aff->content = '<img src="components/com_myjspace/images/publish_y.png" class="icon16" alt="'.$aff->blockView_alt.'" title="'.$aff->blockView_alt.'" />';
			} else {
				$aff->content = clean_html_text($inst->result[$i]['content'], $inst->content_limit).' ';
			}
		}

		if ($inst->search_aff_add & 512) { // Size (512)
			$aff->size = convertSize($inst->result[$i]['size']);
		}

		if ($inst->search_aff_add & 1024) { // Access Level (blockView) (1024)
			$blockView = $inst->result[$i]['blockView'];
			if ($blockView == 1)
				$blockView_img = "publish_g.png";
			else if ($blockView == 0)
				$blockView_img = "publish_r.png";
			else if ($blockView == 2)
				$blockView_img = "publish_y.png";
			else
				$blockView_img = "publish_x.png";
			$aff->blockView_alt = get_assetgroup_label($blockView);

			$aff->blockview = '<img src="'.$link_pre.$blockView_img.'" class="icon16" alt="'.$aff->blockView_alt.'" title="'.$aff->blockView_alt.'" />';
		}

		if ($inst->search_aff_add & 2048) { // Language & association (2048)
			if ($inst->separ <= 1) {
				if (isset($inst->languages[$inst->result[$i]['language']])) {
					$sef = $inst->languages[$inst->result[$i]['language']]->sef;
					$aff->language = JHtml::_('image', 'mod_languages/'.$sef.'.gif', $sef, array('title' => $sef), true);	
				} else {
					$aff->language = $inst->result[$i]['language'];
				}

				if ($inst->language_filter == 2 && isset($inst->app->item_associations) && $inst->app->item_associations == 1) {
					if (count(BSHelperUser::getAssociations($inst->result[$i]['id'])) > 0)
						$aff->language .= '<img src="'.$link_pre.'association.png" class="icon12" alt="'.JText::_('COM_MYJSPACE_LABELASSOCIATION').'" title="'.JText::_('COM_MYJSPACE_LABELASSOCIATION').'" />';
				}
			} else {
				$aff->language = $inst->result[$i]['language'];
			}
		}

		if ($inst->search_aff_add & 4096) { // Share page (4096)
			if ($inst->result[$i]['access'] > 0) {
				if ($inst->result[$i]['userid'] != $inst->user->id && $inst->user->id != 0) {
					$table = JUser::getTable();
					if ($table->load($inst->result[$i]['userid'])) {
						$user = JFactory::getUser($inst->result[$i]['userid']);
					} else {
						$user = new stdClass();
						$user->username = '-';
					}
					$aff->share_page = ' <img src="'.$link_pre.'share.png" class="icon12" alt="'.$user->username.'" title="'.JText::_('COM_MYJSPACE_LABELUSERNAME').JText::_('COM_MYJSPACE_2POINTS').$user->username.'" />';
				} else if ($inst->result[$i]['userid'] == $inst->user->id && $inst->result[$i]['userid'] > 0) {
					$aff->share_page = ' <img src="'.$link_pre.'share_nb.png" class="icon12" alt="access" title="'.JText::_('COM_MYJSPACE_TITLESHAREEDIT').JText::_('COM_MYJSPACE_2POINTS').get_assetgroup_label($inst->result[$i]['access'], true).'" />';
				}
			}
		}

		return $aff;
	}

	public static function title($inst) {
		// Config
		$app = JFactory::getApplication();
		$document = JFactory::getDocument();

		// Component
		$pparams = JComponentHelper::getParams('com_myjspace');
		
        // Web page title
		if ($pparams->get('pagetitle', 1) == 1) {
			if ($inst->search_page_title)
				$title = $inst->search_page_title;
			else
				$title = JText::_('COM_MYJSPACE_TITLESEARCH');
			if (empty($title)) {
				$title = $app->getCfg('sitename');
			} elseif ($app->getCfg('sitename_pagetitles', 0) == 1) {
				$title = JText::sprintf('JPAGETITLE', $app->getCfg('sitename'), $title);
			} elseif ($app->getCfg('sitename_pagetitles', 0) == 2) {
				$title = JText::sprintf('JPAGETITLE', $title, $app->getCfg('sitename'));
			}
			if ($title)
				$document->setTitle($title);
		}
	}
}
?>
