<?php
/*
* @version $Id: default.php $
* @version		2.4.0 11/06/2014
* @package		mod_viewmyjspace
* @author       Bernard Saulmé
* @copyright	Copyright (C) 2010-2011-2012-2013-2014 Bernard Saulmé
* @license      GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
*/

// Pas d'acces direct
defined('_JEXEC') or die;

// Use the component ACL
if (version_compare(JVERSION, '1.6.0', 'ge') && ($params->get('use_com_acl', 0) && !JFactory::getUser()->authorise('user.search', 'com_myjspace'))) 
	return;

if (!@file_exists(JPATH_ROOT.DS.'components'.DS.'com_myjspace'.DS.'helpers'.DS.'util.php'))
	return;
require_once JPATH_ROOT.DS.'components'.DS.'com_myjspace'.DS.'helpers'.DS.'util.php';

if ($showmode1 & 256)
	require_once JPATH_ROOT.DS.'components'.DS.'com_myjspace'.DS.'helpers'.DS.'util_acl.php';

$document = JFactory::getDocument();
$document->addStyleSheet('modules/mod_viewmyjspace/assets/viewmyjspace.css');
$document->addStyleDeclaration($style_str);
if ($separ > 0) {
	$document->addStyleSheet('components/com_myjspace/assets/myjsp_blocks.css');
	echo $chaine_ie;
}

echo "<div class=\"mod_viewmyjspace\">\n";

$count_text = '';
if ($showmode != 1) {
	if ($nbpages > 1)
		$count_text = JText::sprintf('ECHOPAGES', $nbpages);
	else
		$count_text = JText::sprintf('ECHOPAGE', $nbpages);
			
	if ($countmode < 2)
		$count_text = '<div class="vmyjsp_nbpage">'.$count_text.'</div>';
	else
		$count_text = '<div class="vmyjsp_nbpage"><a href="'.Jroute::_('index.php?option=com_myjspace&view=search').'">'.JText::_('MOD_VIEWMYJSPACE_ALL_PAGES').'('.$nbpages.')</a></div>';
}
if ($countmode == 0 || $countmode == 2)
	echo $count_text;

if ($showmode != 0) {

	if ($separ == 0) {
		echo '<div class="vmyjsp_lstpage">'."\n";
		if ($showmode2 == 0) {
			$separ_l = '<span>';
			$separ_r = '</span> ';
		} else {
			$separ_l = '<div>';
			$separ_r = '</div>';
		}	
	} else if ($separ == 1) {
		echo "<div class=\"myjsp-blocks mod-myjsp-blocks\" id=\"myjsp-blocks\">\n";
	} else { // 3
		echo "<div class=\"mod-myjsp-blocks2\">\n";
	}

	$aff_image = false;
	$aff_url = false;
	$aff_username = false;
	$aff_create_date = false;
	$aff_update_date = false;
	$aff_hits = false;
	$aff_description = false;
	$aff_content = false;

	for ($i = 0; $i < count($names); ++$i) {
		// Title
		$title = clean_text($names[$i]->title, $title_limit);

		if ($showmode1 & 64 || $separ == 3) { // Image
			if ($showmode1_query & 256)
				$text = $names[$i]->content;
			else
				$text = '';
				
			if ($link_folder == 1)
				$local_folder = $foldername.'/'.$names[$i]->pagename;
			else
				$local_folder = null;

			if ($separ != 3)
				$aff_image = exist_image_html($local_folder, JPATH_SITE, 'mod-img_preview', 0, $names[$i]->title, 'preview.jpg', $search_image_default, $search_image_type, $text, $search_image_video);
		}

		if ($showmode1 & 1 || $separ >= 1) { // Pagename
			if ($link_folder_print)
				$aff_url = JURI::base(true).'/'.$foldername.'/'.$names[$i]->pagename.'/';
			else
				$aff_url = Jroute::_('index.php?option=com_myjspace&view=see&pagename='.$names[$i]->pagename.'&Itemid='.$itemid);
		}

		if ($showmode1 & 2) { // Username
			$table = JUser::getTable();
			if($table->load($names[$i]->userid)) { // Test if user exist before to retrieve info
				$user = JFactory::getUser($names[$i]->userid);
			} else { // User do not exist any more !
				$user = new stdClass();
				$user->id = 0;
				$user->username = '?';
			}
			$aff_username = $user->username;
		}

		if ($showmode1 & 8) { // Date created (8)
			$aff_create_date = date(JText::_('MOD_VIEWMYJSPACE_DATE_FORMAT'), strtotime($names[$i]->create_date));
		}

		if ($showmode1 & 16) { // Date updated (16)
			$aff_update_date = date(JText::_('MOD_VIEWMYJSPACE_DATE_FORMAT'), strtotime($names[$i]->last_update_date));
		}

		if ($showmode1 & 32) { // Hits (32)
			$aff_hits = $names[$i]->hits;
		}

		if ($showmode1 & 4) { // Description (4)
			$blockView = $names[$i]->blockView;
			if ($blockView >= 2 && (($user_mode_view_acl == 0 && $user->id <= 0) || ($user_mode_view_acl == 1 && !in_array($blockView, $access))))
				$aff_description = ' ';
			else
				$aff_description = clean_text($names[$i]->metakey, $description_limit); 
		}

		if ($showmode1 & 256) { // Content (256) with no html & only some characters
			$blockView = $names[$i]->blockView;
			if ($blockView >= 2 && (($user_mode_view_acl == 0 && $user->id <= 0) || ($user_mode_view_acl == 1 && !in_array($blockView, $this->access)))) {
				$blockView_alt = get_assetgroup_label($blockView);
				$aff_content = '<img src="components/com_myjspace/images/publish_y.png" style="width:13px; height:13px; border:none" alt="'.$blockView_alt.'" title="'.$blockView_alt.'" />';
			} else {
				$aff_content = clean_html_text($names[$i]->content, $content_limit);
			}
		}

		// Display
		if ($separ == 0) {
			echo '<div class="vmyjsp_onepage" style="text-align:'.$showmode0.'">';
			if ($aff_image)
				echo $separ_l.$aff_image.$separ_r;
			else if ($showmode1 & 64)
				echo $separ_l_img.' '.$separ_r;
			if ($aff_url)
				echo $separ_l.modViewMyJspaceHelper::aff_img($names[$i]->connect, $names[$i]->last_update_date, $delaisimgcon, $affimgcon).'<a href="'.$aff_url.'">'.$title.'</a>'.$separ_r;
			if ($aff_username)
				echo $separ_l.$aff_username.$separ_r;
			if ($aff_create_date)
				echo $separ_l.$aff_create_date.$separ_r;
			if ($aff_update_date)
				echo $separ_l.$aff_update_date.$separ_r;
			if ($aff_hits) 
				echo $separ_l.$aff_hits.$separ_r;
			if ($aff_description)
				echo $separ_l.$aff_description.$separ_r;
			if ($aff_content)
				echo $separ_l.$aff_content.$separ_r;
			echo "</div>\n";
		} else if ($separ == 1) {
			echo "<div class=\"icon\">\n";
			echo "<a href=\"".$aff_url."\" title=\"\n";
			if ($aff_username)
				echo JText::_('MOD_VIEWMYJSPACE_SHOWMODE1_2').': '.$aff_username."\n";
			if ($aff_create_date)
				echo JText::_('MOD_VIEWMYJSPACE_SHOWMODE1_8').': '.$aff_create_date."\n";
			if ($aff_update_date)
				echo JText::_('MOD_VIEWMYJSPACE_SHOWMODE1_16').': '.$aff_update_date."\n";
			if ($aff_hits)
				echo JText::_('MOD_VIEWMYJSPACE_SHOWMODE1_32').': '.$aff_hits."\n";

			echo "\n\" >";
			echo '<span class="myjsp-pagename">'.$title.'</span>';
			echo $aff_image;
			if (($aff_description) && $aff_description != ' ')
				echo '<span class="myjsp-desc">'.$aff_description.'</span>';
			echo '<span>'.$aff_content.'</span>';
			echo "</a></div>\n";
		} else if ($separ == 3) {
			echo "<span class=\"grow pic\">\n";
			echo "<a href=\"".$aff_url."\" >";
			$title .= "\n\n";
			if ($aff_username)
				$title .= JText::_('MOD_VIEWMYJSPACE_SHOWMODE1_2').': '.$aff_username."\n";
			if ($aff_create_date)
				$title .= JText::_('MOD_VIEWMYJSPACE_SHOWMODE1_8').': '.$aff_create_date."\n";
			if ($aff_update_date)
				$title .= JText::_('MOD_VIEWMYJSPACE_SHOWMODE1_16').': '.$aff_update_date."\n";
			if ($aff_hits)
				$title .= JText::_('MOD_VIEWMYJSPACE_SHOWMODE1_32').': '.$aff_hits."\n";
			if (($aff_description) && $aff_description != ' ')
				$title .= JText::_('MOD_VIEWMYJSPACE_SHOWMODE1_4').': '.$aff_description."\n";
			if ($aff_content)
				$title .= "\n".$aff_content."\n";
			$aff_image = exist_image_html($local_folder, JPATH_SITE, 'mod-img_preview', 0, $title, 'preview.jpg', $search_image_default, $search_image_type, $text, $search_image_video);
			echo $aff_image;
			echo "</a></span>\n";
		}
	}
	if ($separ != 0)
		echo '<div class="end-myjsp-block"> </div>';
	echo "</div>\n";
}

if ($countmode == 1 || $countmode == 3)
	echo $count_text;

echo "</div>\n";

?>
