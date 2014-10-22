<?php
/**
* @version $Id: view.html.php $
* @version		2.4.1 12/07/2014
* @package		com_myjspace
* @author       Bernard Saulmé
* @copyright	Copyright (C) 2010-2011-2012-2014 Bernard Saulmé
* @license      GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die;
if (!defined('DS')) define('DS', DIRECTORY_SEPARATOR);

require_once JPATH_COMPONENT_SITE.DS.'helpers'.DS.'legacy.php';

jimport('joomla.application.component.view');
 
class MyjspaceViewConfig extends JViewLegacy
{
	function display($tpl = null)
	{
        require_once JPATH_COMPONENT_SITE.DS.'helpers'.DS.'user.php';
        require_once JPATH_COMPONENT_SITE.DS.'helpers'.DS.'util.php';
        require_once JPATH_COMPONENT_SITE.DS.'helpers'.DS.'util_acl.php';
        require_once JPATH_COMPONENT_SITE.DS.'helpers'.DS.'user_event.php';

		// Config
		$pparams = JComponentHelper::getParams('com_myjspace');
		$app = JFactory::getApplication();
		$document = JFactory::getDocument();		
		$params = $app->getParams();

		// Upload layout
		$type = JRequest::getVar('type', ''); // '', media, image, file, undefined
		$skin = JRequest::getVar('skin', ''); 
		
		// Personal page info
		$user = JFactory::getuser();
		$user_page = New BSHelperUser();

		$id = JRequest::getInt('id', 0);
		$catid = JRequest::getInt('catid', $params->get('catid', 0));

		$pageid_tab = JRequest::getVar('cid', array());
		$cid0 = (is_array($pageid_tab) && isset($pageid_tab[0])) ? intval($pageid_tab[0]) : 0;
		if ($id == 0)
			$id = $cid0;

		if ($id < 0 && $cid0 > 0) { // new page to create with $cid0 page id as model
			$model_copy = $cid0;
			$user_page->id = $model_copy;
			$user_page->loadPageInfoOnly();
			$catid = $user_page->catid;
			unset($user_page);
			$user_page = New BSHelperUser();
		} else {
			$model_copy = 0; 	
		}

		// Itemid(s)
		$Itemid = JRequest::getInt('Itemid', 0);
		$Itemid_edit = get_menu_itemid('index.php?option=com_myjspace&view=edit', $Itemid, $catid);
		$Itemid_config = get_menu_itemid('index.php?option=com_myjspace&view=config', $Itemid, $catid);
		$Itemid_see = get_menu_itemid('index.php?option=com_myjspace&view=see', $Itemid, $catid);
		$Itemid_see = get_menu_itemid('index.php?option=com_myjspace&view=see&id=&pagename=', $Itemid_see, $catid); // Compatibility old install
		$Itemid_delete = get_menu_itemid('index.php?option=com_myjspace&view=delete', $Itemid, $catid);
		$Itemid_pages = get_menu_itemid('index.php?option=com_myjspace&view=pages', $Itemid, $catid);

		if ($id > 0 && $catid != 0)
			$catid = 0;
				
		// Param
		$file_max_size = $pparams->get('file_max_size', 307200);
		$auto_create_page = $pparams->get('auto_create_page', 3);	
		$nb_max_page = $pparams->get('nb_max_page', 1);
		$display_myjspace_ref = $pparams->get('display_myjspace_ref', 1);
		$language_filter = $pparams->get('language_filter', 0);
		if (version_compare(JVERSION, '3.0.3', 'lt'))
			$language_filter = min($language_filter, 1);
		$select_category = $pparams->get('select_category', 1);
		if ($catid > 0) // To prevent forced catid page (page name ...) to be changed or auto-create a new page :-( ...
			$select_category = 0;

		// Catid url
		if ($catid != 0)
			$catid_url = '&catid='.$catid;
		else
			$catid_url = '';

		// Language
		if ($language_filter > 0)
			$language_list = BSHelperUser::get_language_list();
		else
			$language_list = array();

		// Page id - check
		$list_page_tab = $user_page->GetListPageId($user->id, $id, $catid);
		$nb_page = count($list_page_tab);

		if ($id <= 0 || $nb_page != 1) {
			if ($id < 0 && $nb_page >= $nb_max_page) { // New page KO
				$app->redirect(Jroute::_('index.php?option=com_myjspace&view=pages&lview=config&Itemid='.$Itemid_pages.$catid_url, false), JText::_('COM_MYJSPACE_MAXREACHED'), 'error');	
				return;
			} else if ($nb_page == 0 && $id > 0 && $catid == 0) {
				$app->redirect(Jroute::_('index.php?option=com_myjspace&view=pages&lview=config&Itemid='.$Itemid_pages.$catid_url, false), JText::_('COM_MYJSPACE_NOTALLOWED'), 'error');
				return;
			} else if ($id < 0 || ($nb_page == 0 && $id <= 0)) { // New page
				$id = 0;
			} else if ($nb_page == 1) { // id= 0 => Display the page
				$id = $list_page_tab[0]['id'];
			} else { // Display Pages list
				$app->redirect(Jroute::_('index.php?option=com_myjspace&view=pages&lview=config&Itemid='.$Itemid_pages.$catid_url, false));
				return;
			}
		} else if ($nb_page > 1) { // Error 
			$app->redirect('index.php', JText::_('COM_MYJSPACE_NOTALLOWED') , 'error');
			return;
		}	

		// Retrieve user page info
		$user_page->id = $id;
		$user_page->loadPageInfoOnly();

		// Last update name: not necessary for J!1.5, not used
		$table = JUser::getTable();
		if ($table->load($user_page->modified_by)) { // Test if user exist before to retrieve info
			$modified_by = JFactory::getUser($user_page->modified_by);
		} else { // User no no exist any more !
			$modified_by = new stdClass();
			$modified_by->id = -1;
			$modified_by->username = ' ';
			$modified_by->name = '';
		}

		if ($user_page->pagename == '')
			$user_page->blockView = $pparams->get('user_mode_view_default', 1);

		// Links
		$link_folder = $pparams->get('link_folder', 1);
		$link_folder_print = $pparams->get('link_folder_print', 1);

		// Test if foldername exist => Admin
		$alert_root_page = 0;
		if (!BSHelperUser::ifExistFoldername($user_page->foldername) && $link_folder == 1) {
			$alert_root_page = 1;
		}	

		// Create automatically page if none & if option 'auto_create_page' is activated & less or equal than 1 model		
		$model_page_list = ($model_copy > 0) ? array() : BSUserEvent::model_pagename_list($catid); // Model page list
		if ($alert_root_page == 0 && $user_page->pagename == '' && ($auto_create_page == 1 || $auto_create_page == 3) && count($model_page_list) <= 2) {
			$blockview = $pparams->get('user_mode_view_default', 1);

			if ($model_copy > 0) {
				$model_page = $model_copy;
			} else {
				$model_page = 0;
				foreach ($model_page_list as $k => $v) { // take the last one
					if ($k != 0 && $model_page_list[$k]['catid'] > 0)
						$catid = $model_page_list[$k]['catid'];
				   $model_page = $model_page_list[$k]['pagename'];
				}
			}

			// Define the pagename/title for a default new page name
			$pagename = $user_page->GetPagenameFree($pparams->get('auto_pagename_rule', "#username"), $user, $catid);
			$id = BSUserEvent::Adm_save_page_conf(0, $user->id, $pagename, $blockview, 0, '', '', '', '', $model_page, $catid, null, '', null, null, 'site_create');
			// Add error msg if id = 0 ?
			$nb_page = $nb_page+1;
			$user_page->id = $id;
			$user_page->loadPageInfoOnly(); // Reload the user data
		}

		// Catid url (if catid change)
		if ($catid != 0) {
			$catid_url = '&catid='.$catid;

			$Itemid = JRequest::getInt('Itemid', 0);
			$Itemid_edit = get_menu_itemid('index.php?option=com_myjspace&view=edit', $Itemid, $catid);
			$Itemid_config = get_menu_itemid('index.php?option=com_myjspace&view=config', $Itemid, $catid);
			$Itemid_see = get_menu_itemid('index.php?option=com_myjspace&view=see', $Itemid, $catid);
			$Itemid_see = get_menu_itemid('index.php?option=com_myjspace&view=see&id=&pagename=', $Itemid_see, $catid); // Compatibility old install
			$Itemid_delete = get_menu_itemid('index.php?option=com_myjspace&view=delete', $Itemid, $catid);
			$Itemid_pages = get_menu_itemid('index.php?option=com_myjspace&view=pages', $Itemid, $catid);			
		}

		// Page link
		if ($user_page->pagename != '') { // Yet or not yes a page
			if ($link_folder_print == 1)
				$link = JURI::base().$user_page->foldername.'/'.$user_page->pagename.'/';
			else
				$link = str_replace(JURI::base(true).'/', '', JURI::base()).Jroute::_('index.php?option=com_myjspace&view=see&pagename='.$user_page->pagename.'&Itemid='.$Itemid_see);
		} else 
			$link = null;
	
		$user_mode_view = $pparams->get('user_mode_view', 1);
		$page_increment = $pparams->get('page_increment', 1);
		$pagename_username = $pparams->get('pagename_username', 0);
		$uploadimg = $pparams->get('uploadimg', 1);
		$uploadmedia = $pparams->get('uploadmedia', 1);
		$publish_mode = $pparams->get('publish_mode', 2);

		// Files uploaded
		if ($link_folder == 1 && ($uploadimg > 0 || $uploadmedia > 0)) {
			list($page_number, $page_size) = dir_size(JPATH_ROOT.DS.$user_page->foldername.DS.$user_page->pagename);
		} else {
			$page_size = 0;
			$page_number = 0;		
		}
		$page_size = convertSize($page_size);
		$dir_max_size = convertSize($pparams->get('dir_max_size', 2097152)); // Max upload
		$file_img_size = convertSize($pparams->get('file_max_size', 307200));
		$resize_x = $pparams->get('resize_x', 800);
		$resize_y = $pparams->get('resize_y', 600);
		if ($resize_x != 0 || $resize_y != 0) {
			if ($resize_x == 0)
				$resize_x = '&#8734';
			if ($resize_y == 0)
				$resize_y = '&#8734';
			if (function_exists("gd_info"))
				$file_img_size .= JText::sprintf('COM_MYJSPACE_LABELUSAGE3',$resize_x,$resize_y);
			else
				$file_img_size .= JText::_('COM_MYJSPACE_LABELUSAGE4');
		}
		
		// Files list
		$uploadadmin = $pparams->get('uploadadmin', 1);
		$tab_list_file = null;
		if ($type == 'image')
			$allowed_types = array('png', 'jpg', 'gif');
		else
			$allowed_types = array('*');
		if ($uploadadmin == 1 && ($uploadimg > 0 || $uploadmedia > 0))
			$tab_list_file = list_file_dir(JPATH_ROOT.DS.$user_page->foldername.DS.$user_page->pagename, $allowed_types, 1);
			
		// Dates check if not set with interesting date
		$user_page->create_date = html_date_empty($user_page->create_date, JText::_('COM_MYJSPACE_DATE_FORMAT'));
		$user_page->last_update_date = html_date_empty($user_page->last_update_date, JText::_('COM_MYJSPACE_DATE_FORMAT'));
		$user_page->last_access_date = html_date_empty($user_page->last_access_date, JText::_('COM_MYJSPACE_DATE_FORMAT'));

		// Lock or not
		$lock_img = JURI::base().'components/com_myjspace/assets/checked_out.png';
		$aujourdhui = time();

		if (strtotime($user_page->publish_up) >= $aujourdhui)
			$img_publish_up = '<img src="'.$lock_img.'" alt="lock" />';
		else
			$img_publish_up = '';

		if ($user_page->publish_down != '0000-00-00 00:00:00' && $user_page->publish_down != null && strtotime($user_page->publish_down) < $aujourdhui)
			$img_publish_down = '<img src="'.$lock_img.'" alt="lock" />';
		else
			$img_publish_down = '';

		$user_page->publish_up = html_date_empty($user_page->publish_up, JText::_('COM_MYJSPACE_DATE_CALENDAR'));
		$user_page->publish_down = html_date_empty($user_page->publish_down, JText::_('COM_MYJSPACE_DATE_CALENDAR'));
		
		// Automatic configuration :-) for new page (auto-create = 0)
		if ($user_page->pagename == '' || $link_folder == 0) {
			$uploadadmin = 0;
			$uploadimg = 0;
			$uploadmedia = 0;
		}

		// New page with auto-create = 0
		if ($user_page->pagename != '') {
			$msg_tmp = '';
		} else {
			$user_page->title = $user_page->GetPagenameFree($pparams->get('auto_pagename_rule', "#username"), $user, $catid);
			$msg_tmp = JText::_('COM_MYJSPACE_NEWPAGEINFO');
		}

        // Web page title
		if ($pparams->get('pagetitle', 1) == 1) {
			$title = $user_page->title;
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

		// Breadcrumbs
		$pathway = $app->getPathway();
		$pathway->addItem($user_page->title, '');
		
		// Templates list proposed for user selection
		$template_list = trim($pparams->get('template_list', ''));
		$tab_template = array();
		if ($template_list) {
			$template_list = explode(',', $template_list);
			foreach ($template_list as $value) {
				$value_tab = explode(':', $value);
				if (count($value_tab) == 2)
					$tab_template[trim(strtolower($value_tab[0]))] = trim($value_tab[1]);
				else
					$tab_template[trim(strtolower($value))] = trim($value);
			}
		}

		// Categories
		$categories = BSHelperUser::GetCategories(1);

		// Share edit
		$share_page = $pparams->get('share_page', 0);
		if (version_compare(JVERSION, '1.6.0', 'ge'))
			$group_list = JHtml::_('access.assetgroups');
		else
			$group_list = null;
		
		// Blockview list
		$blockview_list = get_assetgroup_list();
		
		// Show link admin
		$show_link_admin = $pparams->get('show_link_admin', 1);
		
		// Association
		$associations = array();
		$text_js = '';
		if ($language_filter == 2 && isset($app->item_associations) && $app->item_associations == 1) {
			$assoc_list = BSHelperUser::getAssociations($user_page->id);
			foreach ($language_list as $ltag => $lang_code) {
				$lang_tmp = $language_list[$ltag]->lang_code;
				if ($lang_tmp != '*' && $lang_tmp != $user_page->language) {
					if (isset($assoc_list[$lang_tmp]))
						$associations[$lang_tmp] = $assoc_list[$lang_tmp]->pagename;
					else
						$associations[$lang_tmp] = '';
				}
			}
		}

		// Joomla tags, J!3.1+
		if ($pparams->get('show_tags', 0) == 1 && version_compare(JVERSION, '3.1.4', 'ge')) {
			jimport('joomla.form.form');
			$pathToMyXMLFile = JPATH_COMPONENT_SITE.DS.'models'.DS.'forms'.DS.'myjspace.xml';
			$form = JForm::getInstance('myform', $pathToMyXMLFile);
			// Default tag value
			$my_JHelperTags = new JHelperTags;
			$tags = $my_JHelperTags->getTagIds($user_page->id, 'com_myjspace.see');
		} else {
			$tags = null;
			$form = null;
		}

		// Editor for 'upload' layout
		$editor_selection = $pparams->get('editor_selection', 'myjsp');
		if (check_editor_selection($editor_selection) == false || $editor_selection == '-') { // Use the Joomla default editor
			$editor_selection = $app->getCfg('editor');
		}

		// Vars assign
		$this->assignRef('user_page', $user_page);
		$this->assignRef('username', $user->username);
		$this->assignRef('modified_by', $modified_by->username);
		$this->assignRef('Itemid', $Itemid);
		$this->assignRef('Itemid_edit', $Itemid_edit);
		$this->assignRef('Itemid_config', $Itemid_config);
		$this->assignRef('Itemid_see', $Itemid_see);
		$this->assignRef('Itemid_delete', $Itemid_delete);
		$this->assignRef('Itemid_pages', $Itemid_pages);
		$this->assignRef('pagename_username', $pagename_username);
		$this->assignRef('alert_root_page', $alert_root_page);
		$this->assignRef('user_mode_view', $user_mode_view);
		$this->assignRef('blockview_list', $blockview_list);
		$this->assignRef('link', $link);
		$this->assignRef('msg_tmp', $msg_tmp);
		$this->assignRef('page_increment', $page_increment);
		$this->assignRef('uploadimg', $uploadimg);
		$this->assignRef('page_size', $page_size);
		$this->assignRef('page_number', $page_number);
		$this->assignRef('uploadadmin', $uploadadmin);
		$this->assignRef('uploadmedia', $uploadmedia);
		$this->assignRef('file_max_size', $file_max_size);
		$this->assignRef('tab_list_file', $tab_list_file);
		$this->assignRef('publish_mode', $publish_mode);
		$this->assignRef('img_publish_up', $img_publish_up);
		$this->assignRef('img_publish_down', $img_publish_down);
		$this->assignRef('dir_max_size', $dir_max_size);
		$this->assignRef('file_img_size', $file_img_size);
		$this->assignRef('tab_template', $tab_template);
		$this->assignRef('model_page_list', $model_page_list);
		$this->assignRef('nb_max_page', $nb_max_page);
		$this->assignRef('link_folder', $link_folder);
		$this->assignRef('link_folder_print', $link_folder_print);
		$this->assignRef('display_myjspace_ref', $display_myjspace_ref);
		$this->assignRef('categories', $categories);
		$this->assignRef('group_list', $group_list);
		$this->assignRef('share_page', $share_page);
		$this->assignRef('show_link_admin', $show_link_admin);
		$this->assignRef('language_list', $language_list);
		$this->assignRef('associations', $associations);
		$this->assignRef('catid_url', $catid_url);
		$this->assignRef('form', $form);
		$this->assignRef('tags', $tags);
		$this->assignRef('type', $type);
		$this->assignRef('skin', $skin);
		$this->assignRef('editor_selection', $editor_selection);
		$this->assignRef('catid', $catid);
		$this->assignRef('select_category', $select_category);
 
		parent::display($tpl);
	}
}

?>
