<?php
/**
* @version $Id: user_event.php $
* @version		2.3.5 05/05/2014
* @package		com_myjspace
* @author       Bernard Saulmé
* @copyright	Copyright (C) 2010-2011-2012-2013-2014 Bernard Saulmé
* @license      GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die;
if (!defined('DS')) define('DS', DIRECTORY_SEPARATOR);

// Component Helper
jimport('joomla.application.component.helper');
jimport('joomla.filter.filteroutput');

// -----------------------------------------------------------------------------

// Theses function are here because they can be call from user or admin interface

class BSUserEvent
{
// Constructor
	function bshelperuserevent() {}

// Rename personal page folder or create	
	public static function Adm_ren_folder($foldername_new = '', $keep = 0, $url_redirect = 'index.php') 
	{
        require_once JPATH_COMPONENT_SITE.DS.'helpers'.DS.'user.php';

		$app = JFactory::getApplication();
		$user_page = New BSHelperUser();
		$foldername_old = $user_page->foldername;

		$pparams = JComponentHelper::getParams('com_myjspace');		
		$link_folder = $pparams->get('link_folder', 1);
		$uploadadmin = $pparams->get('uploadadmin', 1);
		$uploadimg = $pparams->get('uploadimg', 1);
		$foldername_redirect_url = $pparams->get('foldername_redirect_url', 'index.php');

		$foldername_new = trim($foldername_new); // White-space stripped from the beginning and end
		$foldername_new = trim($foldername_new, '/'); // '/' stripped from the beginning and end

		if (BSHelperUser::checkFoldername($foldername_new)) { // Test if characters allowed
			if ($user_page->updateFoldername($foldername_new, $link_folder, $keep, $foldername_redirect_url)) {
				if ($uploadadmin == 1 || $uploadimg == 1) // Rename folders inside all pages content !
					self::adm_rename_folder_in_pages($foldername_old, $foldername_new);

				$app->redirect($url_redirect, JText::_('COM_MYJSPACE_FOLDERNAMEUPDATED'), 'message');
			} else
				$app->redirect($url_redirect, JText::_('COM_MYJSPACE_ERRUPDATINGFOLDERNAMEFILE'), 'error');
		} else
			$app->redirect($url_redirect, JText::_('COM_MYJSPACE_NOTVALIDFOLDERNAME'), 'error');
	}

// Removes the personal page record from the database and files
// $tab_id can be a single page (numeric) or array of pages id
 	public static function Adm_page_remove($tab_id = null, $url_redirect = 'index.php', $caller = 'site')
	{
        require_once JPATH_COMPONENT_SITE.DS.'helpers'.DS.'user.php';
        require_once JPATH_COMPONENT_SITE.DS.'helpers'.DS.'util.php';

		$app = JFactory::getApplication();

		if (!is_array($tab_id) || !isset($tab_id[0])) {
			$valeur = $tab_id;
			$tab_id = array();
			$tab_id[0] = $valeur;
		}

		$pparams = JComponentHelper::getParams('com_myjspace');
		$max_page = $pparams->get('max_page_delete', 20); // To be added into real option ?
		if (count($tab_id) > $max_page) { // safety control ... not too much
			$app->redirect($url_redirect, JText::sprintf('COM_MYJSPACE_PAGEDELETETOOMUCH', $max_page), 'error');
			return;
		}

		$total = 0;
		foreach ($tab_id as &$id) {
			$user_page = New BSHelperUser();
			$user_page->id = $id; // To set page id
			$user_page->loadPageInfoOnly(); // To get pagename & foldername
		
			$Itemid_config = get_menu_itemid('index.php?option=com_myjspace&view=config');

			// If page locked (admin & edit | edit) - Front-end
			if ($user_page->blockEdit != 0 && $caller == 'site') {
				$app->enqueueMessage(JText::sprintf('COM_MYJSPACE_PAGEDELETED', 0), 'warning');
				$app->redirect(JRoute::_('index.php?option=com_myjspace&view=config&Itemid='.$Itemid_config), JText::_('COM_MYJSPACE_EDITLOCKED'), 'error');	
				return;		
			}

			if (version_compare(JVERSION, '3.1.4', 'ge')) { // Joomla tags
				$db	= JFactory::getDBO();
				$user_JTableMyjspace = New JTableMyjspace($db);
				$user_JTableMyjspace->delete($id); // delete tag & ucm_ tables content
			}

			if (!$user_page->deletePage($pparams->get('link_folder', 1))) { // Delete
				$app->enqueueMessage(JText::sprintf('COM_MYJSPACE_ERRDELETINGPAGE', $user_page->title), 'error');
				break;
			}
			$total = $total + 1;
		}
		unset($id);

		if (count($tab_id) > 1)
			$app->redirect($url_redirect, JText::sprintf('COM_MYJSPACE_PAGEDELETEDS', $total), 'message');
		else
			$app->redirect($url_redirect, JText::sprintf('COM_MYJSPACE_PAGEDELETED', $total), 'message');
	}
	
// Save (=update) page content
	public static function Adm_save_page_content($id = 0, &$content = null, $name_page_max_size = 0, $url_redirect = 'index.php', $caller = 'site')
	{
        require_once JPATH_COMPONENT_SITE.DS.'helpers'.DS.'user.php';
        require_once JPATH_COMPONENT_SITE.DS.'helpers'.DS.'util.php';

		$app = JFactory::getApplication();

		// Size test
		if ($name_page_max_size > 0 && strlen($content) > $name_page_max_size) {
			if ($url_redirect != null)
				$app->redirect($url_redirect, JText::_('COM_MYJSPACE_ERRCREATEPAGESIZE').' '.$name_page_max_size, 'error');
			return;
		}

		// Param
		$pparams = JComponentHelper::getParams('com_myjspace');
		$user = JFactory::getuser();
		$email_user = $pparams->get('email_user', 0);
		$email_admin = $pparams->get('email_admin', 0);	
		$email_admin_from = $pparams->get('email_admin_from', '');	
		
		$user_page = New BSHelperUser();
		$user_page->id = $id; // To set pageid
		$user_page->loadPageInfoOnly(); // Get info (for pagename)
		$user_page->modified_by = $user->id;

		// If page locked (admin & edit)
		if ($user_page->blockEdit == 2 && $caller == 'site') {
			$app->redirect(JRoute::_('index.php?option=com_myjspace&view=config',false), JText::_('COM_MYJSPACE_EDITLOCKED'), 'error');	
			return;		
		}

		// Begin workaround
			// Update image link or link (relative & absolute), ok with Tiny mce V 3.4.3.2
			if ($caller == 'admin') {
				$uri_rel = str_replace('/administrator', '', JURI::base(true));
				$content = str_replace('href="../'.$user_page->foldername.'/'.$user_page->pagename.'/', 'href="'.$uri_rel.'/'.$user_page->foldername.'/'.$user_page->pagename.'/', $content);
				$content = str_replace('src="../'.$user_page->foldername.'/'.$user_page->pagename.'/', 'src="'.$uri_rel.'/'.$user_page->foldername.'/'.$user_page->pagename.'/', $content);
			}
		// End workaround
		
		$user_page->content = $content; // To set content
		if ($user_page->updateUserContent()) {
			if ($pparams->get('show_tags', 0) == 1 && version_compare(JVERSION, '3.1.4', 'ge')) { // Joomla tags
				$my_JHelperTags = new JHelperTags;
				$tags = $my_JHelperTags->getTagIds($user_page->id, 'com_myjspace.see'); // Get tags for this page
				$tags = ($tags) ? explode(',', $tags) : array();
				if (count($tags) > 0) {
					$db	= JFactory::getDBO();
					$user_JTableMyjspace = New JTableMyjspace($db);
					$user_JTableMyjspace->get_row_BSHelperUser($user_page);
					$user_JTableMyjspace->newTags = $tags;
					$user_JTableMyjspace->store(); // Store updated content in ucm table + tags
				}
			}

			if ($pparams->get('lock_page_after_update', 0) == 1) { // Force to 'lock' after update
				$user_page->blockView = 0; 
				$user_page->SetConfPage(2); // Update blockView table

				if ($email_admin == 1) { // Send email to Admin
					$subject = JText::sprintf('COM_MYJSPACE_EMAIL_SUBJECT2', $user_page->pagename);
					$site_msg = str_replace('/administrator', '', JURI::base());
					$body = JText::sprintf('COM_MYJSPACE_EMAIL_CONTENT2', $user_page->pagename, $site_msg);
					send_mail('', $email_admin_from, $subject, $body);			
				}
			}
		
			if ($email_user == 1 && $caller == 'admin') { // Send email to user
				$subject = JText::sprintf('COM_MYJSPACE_EMAIL_SUBJECT2', $user_page->pagename);
				$site_msg = str_replace('/administrator', '', JURI::base());
				$body = JText::sprintf('COM_MYJSPACE_EMAIL_CONTENT2', $user_page->pagename, $site_msg);
				$user = JFactory::getuser($user_page->userid);
				send_mail($email_admin_from, $user->email, $subject, $body);			
			}

			if ($url_redirect != null)
				$app->redirect($url_redirect, JText::_('COM_MYJSPACE_EUPDATINGPAGE'), 'message');
		} else if ($url_redirect != null)
			$app->redirect($url_redirect, JText::_('COM_MYJSPACE_ERRUPDATINGPAGE'), 'error');
	}

// Save (=update) page (out of content)
	public static function Adm_save_page_conf($id = 0, $userid = 0, $pagename = null, $blockview = 1, $blockedit = 0, $publish_up = '', $publish_down = '', $metakey = '', $template = '', $mjs_model_page = 0, $catid = 0, $access = null, $language = '', $tags = null, $url_redirect = null, $caller = 'site', $redirect = true)
	{
		// JPATH_ROOT to allow to call out of the component
		require_once JPATH_ROOT.DS.'components'.DS.'com_myjspace'.DS.'helpers'.DS.'user.php';
        require_once JPATH_ROOT.DS.'components'.DS.'com_myjspace'.DS.'helpers'.DS.'util.php';

		$app = JFactory::getApplication();

		$user_page = New BSHelperUser();
		$creation = 0;

		// Param
		$pparams = JComponentHelper::getParams('com_myjspace');		
		$link_folder = $pparams->get('link_folder', 1);
		$name_page_size_min = $pparams->get('name_page_size_min', 0);
		$name_page_size_max = $pparams->get('name_page_size_max', 20);
		$pagename_username = $pparams->get('pagename_username', 0);
		$uploadadmin = $pparams->get('uploadadmin', 1);
		$uploadimg = $pparams->get('uploadimg', 1);
		$email_admin = $pparams->get('email_admin', 0);	
		$email_user = $pparams->get('email_user', 0);	
		$email_admin_from = $pparams->get('email_admin_from', '');	
		$email_admin_to = $pparams->get('email_admin_to', '');
		$publish_mode = $pparams->get('publish_mode', 2);
		$user_mode_view	= $pparams->get('user_mode_view', 1);
		$nb_max_page_category = $pparams->get('nb_max_page_category', 0);
		// Category checks
		$category_label = BSHelperUser::GetCategoryLabel($catid);
		if ($catid && ($category_label)) { // If catid specified & exists
			$default_catid = $catid;
		} else { // Use default catid
			$default_catid = $pparams->get('default_catid', 0);
			$categories = BSHelperUser::GetCategories(1);
			if ($default_catid == 0 && count($categories) && $caller == 'site_create')
				$default_catid = $categories[0]['value'];
			if (!$category_label) // Incorrect catid => default catid
				$catid = $default_catid;
		}

		// Default url to redirect => from front-end or back-end ?
		if ($url_redirect == null) {
			if (JFactory::getApplication()->isAdmin())
				$url_redirect = JRoute::_('index.php?option=com_myjspace&view=page&id='.$id, false); // id=0 (new page ko) will redirect to view=pages (from view=page)
			else
				$url_redirect = JRoute::_('index.php?option=com_myjspace&view=config&id='.$id, false);
		}

		if ($pagename_username == 0 && (strlen($pagename) < $name_page_size_min || strlen($pagename) > $name_page_size_max)) { // Check pagename length
			if ($redirect)
				$app->redirect($url_redirect, JText::sprintf('COM_MYJSPACE_ADMIN_NAME_PAGE_SIZE_ERROR', $name_page_size_min, $name_page_size_max), 'error');
			return 0;
		}

		$user_page->id = $id;
		// Charge page info if page (id) exists
		if ($pparams->get('show_tags', 0) == 1 && version_compare(JVERSION, '3.1.4', 'ge')) // Joomla tags
			$user_page->loadPageInfo();
		else
			$user_page->loadPageInfoOnly();

		// Pagename & title
		$title = trim($pagename);
		$pagename = BSHelperUser::stringURLSafe($pagename); // create title alias for the pagename

		if ($pagename == '' || is_numeric($pagename)) { // Check naming (no empty & no numeric)
			if ($redirect)
				$app->redirect($url_redirect, JText::_('COM_MYJSPACE_NOTVALIDPAGENAME'), 'error');
			return 0;
		}

		$id_recup = $user_page->id;
		if ($userid != 0)
			$user_page->userid = $userid; // In case if page do not already exists

		if ($nb_max_page_category > 0) { // If nb page(s) per user per category limited
			$count_thiscatid = $user_page->CountUserPageCategory($catid);
			if ($count_thiscatid >= $nb_max_page_category && $user_page->catid != $catid) {
				if ($caller == 'site' || $caller == 'site_create')
					$view = 'config';
				else
					$view = 'page&id='.$user_page->id;
				if ($redirect) {
					$url = JRoute::_('index.php?option=com_myjspace&view='.$view, false);
					$app->redirect($url, JText::sprintf('COM_MYJSPACE_MAXCATEGORYREACHED', $nb_max_page_category, BSHelperUser::GetCategoryLabel($catid)), 'error');
				}
				return 0;
			}
		}

		// If page locked (admin & edit)
		if ($user_page->blockEdit == 2 && $caller == 'site') {
			if ($redirect)
				$app->redirect(JRoute::_('index.php?option=com_myjspace&view=config', false), JText::_('COM_MYJSPACE_EDITLOCKED'), 'error');	
			return 0;		
		}

		if ($user_page->pagename != $pagename) { // Test if pagename change (or new page)
			if (BSHelperUser::ifExistPageName($pagename)) {
				if ($redirect)
					$app->redirect(JRoute::_('index.php?option=com_myjspace&view=pages', false), JText::_('COM_MYJSPACE_PAGEEXISTS'), 'error');
				return 0;	
			}

			if ($user_page->pagename != '' && $link_folder == 1) { // If page exists & page with directory configured for upload
		// To be completed in case of issue ? 
		// if (!$user_page->pagename || $user_page->pagename = '' || !(pagename) || pagename = '') 
				@rename(JPATH_SITE.DS.$user_page->foldername.DS.$user_page->pagename, JPATH_SITE.DS.$user_page->foldername.DS.$pagename);
				$user_page->CreateDirFilePage($pagename, $pparams->get('index_pagename_id', 1));

				// In that case if we change page content; for url (option with page content allowed)
				if ($uploadadmin == 1 || $uploadimg == 1) {
					// Charge page content
					$user_page->loadPageInfo();

					// Update url & image link (relative & absolute)
					$user_page->content = preg_replace('!src=(.*)'.$user_page->foldername.'/'.$user_page->pagename.'!isU', 'src=$1'.$user_page->foldername.'/'.$pagename.'', $user_page->content);
					$user_page->content = preg_replace('!href=(.*)'.$user_page->foldername.'/'.$user_page->pagename.'!isU', 'href=$1'.$user_page->foldername.'/'.$pagename.'', $user_page->content);

					// re-save modified content & old config not modified!
					$user_page->updateUserContent();
				}
			} else { // New page
				$creation = 1;

				if ($nb_max_page_category > 0) { // If nb page(s) per user per category limited
					$count_thiscatid = $user_page->CountUserPageCategory($default_catid);
					if ($count_thiscatid >= $nb_max_page_category) {
						if ($caller == 'site' || $caller == 'site_create')
							$view = 'config';
						else
							$view = 'createpage';
						if ($redirect) {
							$url = JRoute::_('index.php?option=com_myjspace&view='.$view, false);
							$app->redirect($url, JText::sprintf('COM_MYJSPACE_MAXCATEGORYREACHED', $nb_max_page_category, BSHelperUser::GetCategoryLabel($default_catid)), 'error');
						}
						return 0;
					}
				}

				// Page creation (DB & directory & file, if page with directory configured)
				if (!($id_recup) && (!($user_page->id = $user_page->createPage($pagename, $default_catid)) || ($link_folder == 1 && $user_page->CreateDirFilePage($pagename, $pparams->get('index_pagename_id', 1)) == 0))) { // A completer en cas d'erreur de l'un ou de l'autre seulement ?
					if ($redirect)
						$app->redirect(JRoute::_('index.php?option=com_myjspace&view=pages', false), JText::_('COM_MYJSPACE_ERRCREATEPAGE'), 'error');
					// Clean-up to be made, in case ?
					return $user_page->id;
				}
				// Model Page(s) ?
				$mjs_model_page = self::model_pagename_id($mjs_model_page); // select the model page id (or file name) to be used
				if ($mjs_model_page) { // If model page to use
					if (intval($mjs_model_page) != 0) { // If it's a number != 0, it's a page id
						$user_page->content = $user_page->GetContentPageId($mjs_model_page);
					} else { // File content to upload
						$user_page->content = @file_get_contents($mjs_model_page);
						if (strlen($user_page->content) <= 92160 && strstr($user_page->content, '<body>') && preg_match('#<body(.*)>(.*)</body>#Us', $user_page->content, $sortie)) {
							if (count($sortie) >= 3)
								$user_page->content = $sortie[2];						
						}
					}
					
					if ($user_page->content)
						$user_page->updateUserContent();
				}
				
//				if (count(self::model_pagename_list($catid)) > 0) {
					// Non SEF
					$url_redirect = str_replace('&id=0', '&id='.$user_page->id, $url_redirect);
					// SEF
					$url_redirect .= '#####';
					$url_redirect = str_replace('/0#####', '/'.$user_page->id, $url_redirect);
//				}
				if ($email_admin == 1) { // Send Email to admin
					$subject = JText::sprintf('COM_MYJSPACE_EMAIL_SUBJECT1', $pagename);					
					$body = JText::sprintf('COM_MYJSPACE_EMAIL_CONTENT1', $pagename, JURI::base());
					send_mail($email_admin_from, $email_admin_to, $subject, $body);
				}
			}
		}

		if ($caller == 'site_create') // Act (redirect) now as admin for site & auto-create
			$caller = 'admin';

		// Update with param received & keep the old one if none received
		$user_page->title = $title;
		$user_page->pagename = $pagename;
		if ($access !== null)
			$user_page->access = $access;
		$user_page->blockView = $blockview;
		$user_page->blockEdit = $blockedit;
		if ($language != '')
			$user_page->language = $language;

		// Metakey (comment)
		$user_page->metakey = trim(substr($metakey, 0, 150)); // Max. 150 characters

		// Template
		$user_page->template = trim(substr($template, 0, 50)); // Max. 50 characters

		// Catid
		$user_page->catid = $catid;

		// Publish dates (with the right format & valid)
		$user_page->publish_up = valid_date(trim($publish_up), JText::_('COM_MYJSPACE_DATE_CALENDAR'));
		$user_page->publish_down = valid_date(trim($publish_down), JText::_('COM_MYJSPACE_DATE_CALENDAR'));

		// Right selection (all $droits= 31) to avoid use to change unauthorised with some kind of direct url access ...
		$droits = 0;
		if ($pagename_username == 0 || $caller == 'admin2' || $caller == 'admin' || $creation == 1)
			$droits += 1;
		if ($user_mode_view == 1 || $caller == 'admin2')
			$droits += 2;			
		if ($caller == 'admin2' || $caller == 'admin')
			$droits += 4; // blockedit
		if ($publish_mode == 2 || ($publish_mode == 1 && $caller == 'admin2'))
			$droits += (8 + 16);
		$droits += 32; // metakey
		$droits += 64; // template
		if ($pparams->get('select_category', 1) == 1 || $caller == 'admin2' || $caller == 'admin')
			$droits += 128;	// catid
		if ($access !== null)
			$droits += 512;	// access
		if ($userid != 0)
			$droits += 256;
		if ($language != '') // language
			$droits += 2048;

		if ($user_page->SetConfPage($droits)) { // Update page config

			if ($pparams->get('show_tags', 0) == 1 && version_compare(JVERSION, '3.1.4', 'ge')) { // Joomla tags
				$db	= JFactory::getDBO();
				$user_JTableMyjspace = New JTableMyjspace($db);
				$user_JTableMyjspace->get_row_BSHelperUser($user_page);
				$user_JTableMyjspace->newTags = $tags;
				$user_JTableMyjspace->store();
			}

			if ($email_user == 1 && $creation == 0 && $caller == 'admin2') { // Send email to user
				require_once JPATH_ROOT.DS.'components'.DS.'com_myjspace'.DS.'helpers'.DS.'util_acl.php';
				$subject = JText::sprintf('COM_MYJSPACE_EMAIL_SUBJECT2', $pagename);
				$edit_msg = 'COM_MYJSPACE_TITLEMODEEDIT'.$blockedit;
				$site_msg = str_replace('/administrator', '', JURI::base());
				$body = JText::sprintf('COM_MYJSPACE_EMAIL_CONTENT2', $pagename, $site_msg);
				$body .= "\n  ". JText::_('COM_MYJSPACE_TITLEMODEEDIT').' : '.JText::_($edit_msg);
				$body .= "\n  ". JText::_('COM_MYJSPACE_TITLEMODEVIEW').' : '.get_assetgroup_label($blockview);
				$user = JFactory::getuser($user_page->userid);
				send_mail($email_admin_from, $user->email, $subject, $body);			
			}
			if ($caller != 'admin' && $redirect)
				$app->redirect($url_redirect, JText::_('COM_MYJSPACE_EUPDATINGPAGE'), 'message');

		} else if ($caller != 'admin' && $redirect)
			$app->redirect($url_redirect, JText::_('COM_MYJSPACE_ERRUPDATINGPAGE'), 'error');
		else if ($redirect)
			$app->redirect(JRoute::_('index.php?option=com_myjspace&view=pages', false), JText::_('COM_MYJSPACE_ERRUPDATINGPAGE'), 'error');

		return $user_page->id;
	}


// Reset page hit
	public static function Adm_reset_page_access($id = 0, $url_redirect = 'index.php', $caller = 'site')
	{
        require_once JPATH_COMPONENT_SITE.DS.'helpers'.DS.'user.php';

		$app = JFactory::getApplication();

		$user_page = New BSHelperUser();
		$user_page->id = $id; // To set page id
		$user_page->loadPageInfoOnly();
		
		// If page locked (admin & edit)
		if ($user_page->blockEdit == 2 && $caller == 'site') {
			$app->redirect(JRoute::_('index.php?option=com_myjspace&view=config',false), JText::_('COM_MYJSPACE_EDITLOCKED') , 'error');	
			return;		
		}

		if ($user_page->ResetLastAccess()) // Reset hit
			$app->redirect($url_redirect, JText::_('COM_MYJSPACE_EUPDATINGPAGE'), 'message');
		else
			$app->redirect($url_redirect, JText::_('COM_MYJSPACE_ERRUPDATINGPAGE'), 'error');	
	}	


// Delete the selected file for a user
	public static function Adm_delete_file($id = 0, $file_name = '', $url_redirect = 'index.php', $caller = 'site') {
	    require_once JPATH_COMPONENT_SITE.DS.'helpers'.DS.'user.php';

		$app = JFactory::getApplication();

		// Extra controls
		$forbiden_files = array('', '.', '..', '.htaccess');
		$forbiden_types = array('htm', 'html', 'php');
		$type_parts = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
		if (in_array($type_parts, $forbiden_types) || in_array(strtolower($file_name), $forbiden_files)) { 
			if ($file_name == '')
				$file_name = JText::_('COM_MYJSPACE_UPLOADCHOOSE');
			$app->redirect($url_redirect, JText::_('COM_MYJSPACE_UPLOADNOALLOWED').' '.JText::_('COM_MYJSPACE_UPLOADERROR11').$file_name, 'error');
			return;
		}

		$user_page = New BSHelperUser();
		$user_page->id = $id; // To set page id
		$user_page->loadPageInfoOnly(); // To get pagename & foldername

		// If page locked (admin & edit)
		if ($user_page->blockEdit == 2 && $caller == 'site') {
			$app->redirect(JRoute::_('index.php?option=com_myjspace&view=config',false), JText::_('COM_MYJSPACE_EDITLOCKED'), 'error');	
			return;		
		}

		if (@unlink(JPATH_ROOT.DS.$user_page->foldername.DS.$user_page->pagename.DS.utf8_decode($file_name))) {
			$user_page->SetConfPage(0); // Page update date 
			$app->redirect($url_redirect, JText::_('COM_MYJSPACE_UPLOADERROR10').$file_name, 'message');
		} else
			$app->redirect($url_redirect, JText::_('COM_MYJSPACE_UPLOADERROR11').$file_name, 'error');	
	}


// Upload the file for a user into his personal folder 
	public static function Adm_upload_file($id = 0, $FileObject = null, $url_redirect = 'index.php', $caller = 'site') {
	    require_once JPATH_COMPONENT_SITE.DS.'helpers'.DS.'user.php';
	    require_once JPATH_COMPONENT_SITE.DS.'helpers'.DS.'util.php';

		$app = JFactory::getApplication();

		// User
		$user_page = New BSHelperUser();
		$user_page->id = $id; // To set page id
		$user_page->loadPageInfoOnly(); // To get pagename & foldername

		// If page locked (admin & edit)
		if ($user_page->blockEdit == 2 && $caller == 'site') {
			$app->redirect(JRoute::_('index.php?option=com_myjspace&view=config',false), JText::_('COM_MYJSPACE_EDITLOCKED') , 'error');	
			return 0;		
		}

		// Secure
		if ($user_page->pagename == '') {
			$app->redirect($url_redirect, JText::_(COM_MYJSPACE_UPLOADNOALLOWED), 'error');	
			return 0;
		}

		// 'Params'
		$pparams = JComponentHelper::getParams('com_myjspace');
		$DestPath = JPATH_ROOT.DS.$user_page->foldername.DS.$user_page->pagename.DS;
		$resize_x = $pparams->get('resize_x', 800);
		$resize_y = $pparams->get('resize_y', 600);
		$uploadfile = strtolower(str_replace(' ', '', $pparams->get('uploadfile', '*'))); // Files suffixes
		$uploadimg = $pparams->get('uploadimg', 1);
		$uploadmedia = $pparams->get('uploadmedia', 0);

		$forbiden_files = array('', '.', '..', '.htaccess');
		$forbiden_types = array('htm', 'html', 'php');

		$allowed_types = array();
		if ($uploadimg == 1)
			$allowed_types = array_merge($allowed_types, array('jpg', 'png', 'gif'));

		if ($uploadmedia == 1) {
			$uploadfile = str_replace(array('|', ' '), array(',', ''), $uploadfile); // Compatibility with MyJspace < 1.7.7 and cleanup
			$uploadfile_tab = explode(',', $uploadfile);
			$allowed_types = array_merge($allowed_types, $uploadfile_tab);
		}

		$file_max_size = $pparams->get('file_max_size', '307200');
		$dir_max_size = $pparams->get('dir_max_size', '2097152');
		$StatusMessage = '';
		$ActualFileName = '';
		$error = 1;

		list($rien, $dir_size_var) = dir_size($DestPath);
		$FileBasename = utf8_decode(basename($FileObject['name']));		
		list($void_w, $void_h, $image_type) = @getimagesize($FileObject['tmp_name']);
		$type_parts = strtolower(pathinfo($FileObject['name'], PATHINFO_EXTENSION));

		if (!isset($FileObject) || $FileObject['size'] <= 0 || in_array($type_parts, $forbiden_types) || in_array(strtolower($FileBasename), $forbiden_files) || !($uploadfile_tab[0] == '*' || in_array($type_parts, $allowed_types))) {		
			$StatusMessage = JText::_('COM_MYJSPACE_UPLOADERROR2');
		} else if ($image_type >= 1 && $image_type <= 3 && !in_array($type_parts, array('jpg', 'png', 'gif'))) { // image not correctly suffixed
			$StatusMessage = JText::_('COM_MYJSPACE_UPLOADERROR2');		
		} else {
			$ActualFileName = $DestPath.$FileBasename;	// Path & name to file
			$actual_filesize = intval(@filesize($ActualFileName));
			if ($actual_filesize > 0)									// If the file already exists, inform user
				$StatusMessage .= JText::_('COM_MYJSPACE_UPLOADERROR6');

			// If image it may be resized
			$StatusMessage_tmp = '';
			$ActualFileName_tmp = '';
			$FileResized = false;
			if ($resize_x != 0 || $resize_y != 0) { // Resize & gif | jpg | png
				$ActualFileName_tmp = tempnam(sys_get_temp_dir(), 'bs_');
				if (resize_image($FileObject['tmp_name'], $resize_x, $resize_y, $ActualFileName_tmp) == true) { // Resize if image
					$StatusMessage_tmp .= JText::_('COM_MYJSPACE_UPLOADERROR1');
					$FileObject_size = intval(@filesize($FileObject['tmp_name'])); // File uploaded
					$ActualFileName_size = intval(@filesize($ActualFileName_tmp)); // Size after resized		
					if ($ActualFileName_size < $FileObject_size) { // Only smaller file :-)
						$FileResized = true; // The file to be used is ActualFileName_tmp
						$FileObject["size"] = $ActualFileName_size; 
					}
				}
			}

			if ($FileObject["size"] > $file_max_size) { // File size limit
				$StatusMessage = JText::_('COM_MYJSPACE_UPLOADERROR4').convertSize($FileObject['size']).JText::_('COM_MYJSPACE_UPLOADERROR3').convertSize($file_max_size);
			} else if (($dir_size_var + $FileObject["size"] - $actual_filesize) > $dir_max_size) { // Folder size limit
				$StatusMessage = JText::_('COM_MYJSPACE_UPLOADERROR5').convertSize($FileObject['size']+$dir_size_var).JText::_('COM_MYJSPACE_UPLOADERROR3').convertSize($dir_max_size);
			} else { // Move file to user page
				if (($FileResized && @rename($ActualFileName_tmp, $ActualFileName)) || move_uploaded_file($FileObject['tmp_name'], $ActualFileName)) {
					$StatusMessage .= $StatusMessage_tmp.JText::_('COM_MYJSPACE_UPLOADERROR9');
					$error = 0;
				} else {
					$StatusMessage .= ' '.JText::_('COM_MYJSPACE_UPLOADERROR12');				
				}
				@chmod($ActualFileName, 0644);
			}
			if ($ActualFileName_tmp != '')
				@unlink($ActualFileName_tmp);		
		}

		$StatusMessage .= '.';
		$StatusMessage = str_replace("\n", '. ', $StatusMessage);
		$StatusMessage = str_replace(" .", '.', $StatusMessage);
		if (preg_match('#^. #', $StatusMessage) == 1)
			$StatusMessage = substr($StatusMessage, 1);

		if ($error == 0) {
			$link = str_replace('/administrator', '', JURI::base(true)); 
			$StatusMessage .= '<br />Url: '.$link.'/'.$user_page->foldername.'/'.$user_page->pagename.'/'.basename($FileObject['name']);
			$user_page->SetConfPage(0); // Page update date

			$app->redirect($url_redirect, $StatusMessage, 'message');
		} else
			$app->redirect($url_redirect, $StatusMessage, 'error');

		return !$error;
	}


// Delete all folders and indexes file for a personal page (no sub-folder deleted)
	public static function adm_delete_folder()
	{
	    require_once JPATH_COMPONENT_SITE.DS.'helpers'.DS.'user.php';

		$folder = BSHelperUser::getFoldername();
		$userpage_list = BSHelperUser::loadPagename();

		$nb_page = count($userpage_list);
		if ($nb_page <= 0)
			return(array(0, JText::_('COM_MYJSPACE_ADMIN_CREATE_FOLDER_1')));

		$compte_dir_ok = 0;
		$compte_dir_ko = 0;
		$compte_ide_ok = 0;
		$compte_ide_ko = 0;

		for ($i = 0; $i < $nb_page; $i++) {
			if (@unlink(JPATH_ROOT.DS.$folder.DS.$userpage_list[$i]['pagename'].DS.'index.php'))
				$compte_ide_ok = $compte_ide_ok +1;
			else
				$compte_ide_ko = $compte_ide_ko +1;
			
			if (@rmdir(JPATH_ROOT.DS.$folder.DS.$userpage_list[$i]['pagename']))
				$compte_dir_ok = $compte_dir_ok +1;
			else
				$compte_dir_ko = $compte_dir_ko +1;
		}

		return(array($compte_ide_ko + $compte_dir_ko, JText::_('COM_MYJSPACE_ADMIN_DELETE_FOLDER_1').$compte_dir_ok.' : ok (dir), '.$compte_ide_ok.' : ok (index), '.$compte_dir_ko.' : ko (dir), '.$compte_ide_ko.' : ko (index) /'. $nb_page));
	}


// Create (or Recreate after delete) all folders and indexes for personal pages
	public static function adm_create_folder()
	{
	    require_once JPATH_COMPONENT_SITE.DS.'helpers'.DS.'user.php';

		$pparams = JComponentHelper::getParams('com_myjspace');
		$user_page = New BSHelperUser();
		$userpage_list = BSHelperUser::loadPagename();

		$nb_page = count($userpage_list);
		if ($nb_page <= 0)
			return(array(0, JText::_('COM_MYJSPACE_ADMIN_CREATE_FOLDER_1')));

		$retour_ok = 0;
		for ($i = 0; $i < $nb_page; $i++) {
			if ($user_page->CreateDirFilePage($userpage_list[$i]['pagename'], $pparams->get('index_pagename_id', 1), $userpage_list[$i]['id']))
				$retour_ok = $retour_ok+1;
		}
	
		return(array($nb_page-$retour_ok, JText::_('COM_MYJSPACE_ADMIN_CREATE_FOLDER_2').$retour_ok.'/'.$nb_page));
	}


// Delete all empty pages (= content & folders) 
	public static function adm_delete_empty_pages()
	{
	    require_once JPATH_COMPONENT_SITE.DS.'helpers'.DS.'user.php';

		$pparams = JComponentHelper::getParams('com_myjspace');		
		$link_folder = $pparams->get('link_folder', 1);

		$folder = BSHelperUser::getFoldername();
		$userpage_list = BSHelperUser::loadPagename(-1, 0, 0, 0, -1); // Page List with content empty

		$nb_page = count($userpage_list);
		if ($nb_page <= 0)
			return(array(0, JText::_('COM_MYJSPACE_ADMIN_CREATE_FOLDER_1')));

		$compte_del_ok = 0;
		$compte_del_ko = 0;
		$user_page = New BSHelperUser();

		for ($i = 0; $i < $nb_page; $i++) {
			$user_page->id = $userpage_list[$i]['id'];
			$user_page->pagename = $userpage_list[$i]['pagename'];
			$user_page->foldername = $folder;
			if ($user_page->deletePage($link_folder, 0)) // Delete but do not force to delete files
				$compte_del_ok = $compte_del_ok + 1;
			else
				$compte_del_ko = $compte_del_ko + 1;
		}

		return(array($compte_del_ko, JText::sprintf('COM_MYJSPACE_ADMIN_DELETE_EMPTY_PAGES_1', $compte_del_ok, $compte_del_ko)));
	}


// Rename old folder name in all pages
	public static function adm_rename_folder_in_pages($foldername_old = '', $foldername_new = '')
	{
	    require_once JPATH_COMPONENT_SITE.DS.'helpers'.DS.'user.php';

		if ($foldername_old == $foldername_new)
			return 0;

		$userpage_list = BSHelperUser::loadPagename(-1, 0, 0, 0, 1, array('content' => 1), '/'.$foldername_old.'/'); // Only page with 'potential' url content 	
		$nb_page = count($userpage_list);

		if ($nb_page <= 0)
			return 0;

		$uri_rel = str_replace('/administrator', '', JURI::base(true));
		$uri_abs = str_replace('/administrator', '', JURI::base()); 
		$user_page = New BSHelperUser();			
			
		for ($i = 0; $i < $nb_page; $i++) {
			// User info 
			$user_page->id = $userpage_list[$i]['id'];
			$user_page->loadPageInfo();
			// Update image link or  link (relative & absolute)
			$user_page->content = str_replace('href="'.$uri_rel.'/'.$foldername_old.'/'.$user_page->pagename, 'href="'.$uri_rel.'/'.$foldername_new.'/'.$user_page->pagename, $user_page->content);
			$user_page->content = str_replace('href="'.$uri_abs.$foldername_old.'/'.$user_page->pagename, 'href="'.$uri_abs.$foldername_new.'/'.$user_page->pagename, $user_page->content);
			$user_page->content = str_replace('src="'.$uri_rel.'/'.$foldername_old.'/'.$user_page->pagename, 'src="'.$uri_rel.'/'.$foldername_new.'/'.$user_page->pagename, $user_page->content);
			$user_page->content = str_replace('src="'.$uri_abs.$foldername_old.'/'.$user_page->pagename, 'src="'.$uri_abs.$foldername_new.'/'.$user_page->pagename, $user_page->content);
			// Save modified content content
			$user_page->updateUserContent();
		}
		
		return 1;
	}
	

// Move all personal pages folders to another root folder
// return the number of sub-folders renamed
	public static function adm_rename_folders($old_root_folder = '', $new_root_folder = '')
	{
	    require_once JPATH_COMPONENT_SITE.DS.'helpers'.DS.'user.php';
	
		$userpage_list = BSHelperUser::loadPagename();

		$nb_page = count($userpage_list);
		if ($nb_page <= 0)
			return 0;

		$retour_ok = 0;
		for ($i = 0; $i < $nb_page; $i++) {
			if (@rename($old_root_folder.DS.$userpage_list[$i]['pagename'], $new_root_folder.DS.$userpage_list[$i]['pagename']))
				$retour_ok = $retour_ok+1;
		}
		return $retour_ok;
	}


// List of model pages
// Return: tab of model, for a specify catid or all if 0
//		$tab[$i]['pagename'] = pagename, $tab[$i]['catid'] = catid, $tab[$i]['type'] = 0 page id, 1 pagename, 3 file
	public static function model_pagename_list($catid = 0)
	{
		$pparams = JComponentHelper::getParams('com_myjspace');
		$model_pagename = $pparams->get('model_pagename', '');
		if ($model_pagename == '')
			return array();
		$model_pagename_tab_init = array_merge(array(JText::_('COM_MYJSPACE_MODELTOBESELECTED')), explode(',', $model_pagename));

		$model_pagename_tab_count = count($model_pagename_tab_init);
		$user_page = New BSHelperUser();

		for ($i = 0; $i < $model_pagename_tab_count; $i++) { // Page check and find the name & catid
			$pagename_tmp = array();
			$model_pagename_tab[$i]['pagename'] = '';
			$model_pagename_tab[$i]['text'] = '';
			$model_pagename_tab[$i]['id'] = 0;
			$model_pagename_tab[$i]['catid'] = 0;
			$model_pagename_tab[$i]['category'] = '';
			$model_pagename_tab[$i]['file'] = '';
			$model_pagename_tab[$i]['type'] = 1; // 0 page id (does not exists replaced automatically with 1), 1 pagename, 3 file
				
			if (strstr($model_pagename_tab_init[$i], ':')) {
				$pagename_tmp = explode(':', $model_pagename_tab_init[$i]);
				$model_pagename_tab[$i]['pagename'] = trim($pagename_tmp[0]);
				if (array_key_exists(1, $pagename_tmp)) {
					$model_pagename_tab[$i]['catid'] = trim($pagename_tmp[1]);
					$model_pagename_tab[$i]['category'] = BSHelperUser::GetCategoryLabel($model_pagename_tab[$i]['catid']);
				} else {
					$model_pagename_tab[$i]['catid'] = 0;
				}
			} else {
				$model_pagename_tab[$i]['pagename'] = trim($model_pagename_tab_init[$i]);
			}

			if (intval($model_pagename_tab[$i]['pagename']) == $model_pagename_tab[$i]['pagename'] && intval($model_pagename_tab[$i]['pagename']) != 0) { // number
				$user_page->id = $model_pagename_tab[$i]['pagename'];
				$user_page->loadPageInfoOnly(0);
				$model_pagename_tab[$i]['id'] = $user_page->id;
				$model_pagename_tab[$i]['pagename'] = $user_page->pagename; // Replace the id with the pagename
				$model_pagename_tab[$i]['text'] = $user_page->title;
			} else { // text
				// Check if pagename
				$user_page->id = 0;
				$user_page->pagename = $model_pagename_tab[$i]['pagename'];
				$user_page->loadPageInfoOnly(1);
				if ($user_page->pagename == null) { // Not an existing pagename => file to upload
					if (strncmp($model_pagename_tab[$i]['pagename'], 'http', 4) == 0) // URL
						$model_pagename_tab[$i]['file'] = $model_pagename_tab[$i]['pagename'];
					else if ($i != 0) // page
						$model_pagename_tab[$i]['file'] = JPATH_ROOT.DS.$model_pagename_tab[$i]['pagename'];
					$chaine_tab = explode('.', basename($model_pagename_tab[$i]['pagename']));
					$model_pagename_tab[$i]['pagename'] = $chaine_tab[0];
					$model_pagename_tab[$i]['type'] = 2;
					$model_pagename_tab[$i]['text'] = str_replace('_', ' ', $model_pagename_tab[$i]['pagename']);
				} else { // 'real' page
					$model_pagename_tab[$i]['id'] = $user_page->id;
					$model_pagename_tab[$i]['pagename'] = $user_page->pagename;
					$model_pagename_tab[$i]['text'] = $user_page->title;
				}
			}

			if ($catid == 0 && $model_pagename_tab[$i]['category'] != '') // Add Catid if not catid selection
				$model_pagename_tab[$i]['text'] .= ' : '.$model_pagename_tab[$i]['category'];
	
			if ($i != 0 && $catid != 0) { // If catid filter, keep only the concerned catid
				if (array_key_exists(1, $pagename_tmp)) {
					if (trim($pagename_tmp[1]) == $catid)
						$model_pagename_tab[$i]['pagename'] = $pagename_tmp[0];
					else
						unset($model_pagename_tab[$i]);
				} else
						unset($model_pagename_tab[$i]);
			}
		}
		
		return $model_pagename_tab;
	}


// Check the validity for the model field option (error & warning)
	public static function model_pagename_valid()
	{
		// Get the models
		$model_page_list = self::model_pagename_list();

		$error = '';
		$warning = '';
		$count_id_non_zero = 0;
		
		// Checks
		foreach ($model_page_list as $key => $value) {
			if ($key != 0) {
				if ($model_page_list[$key]['catid'] > 0)
					$count_id_non_zero = $count_id_non_zero + 1;

				if ($model_page_list[$key]['category'] == '' && $model_page_list[$key]['catid'] > 0)
					$error .= JText::sprintf('COM_MYJSPACE_ADMIN_MODEL_CATID', $model_page_list[$key]['catid']);
		
				if ($model_page_list[$key]['type'] == 2 &&strlen(@file_get_contents($model_page_list[$key]['file'])) <= 0)
					$error .= JText::sprintf('COM_MYJSPACE_ADMIN_MODEL_PAGENAME', $model_page_list[$key]['pagename']);
			}
		}

		if ((count($model_page_list)-1) != $count_id_non_zero && $count_id_non_zero > 0)
			$warning .= JText::_('COM_MYJSPACE_ADMIN_MODEL_ERROR1');
			
		return array($error, $warning);
	}


//  Transform the model page name or model page id into page id, if exists. Else keep the name
	public static function model_pagename_id($pagename = 0)
	{
		if (!$pagename)
			return 0;
			
		if ($pagename < 0)
			return $pagename * -1;

		$model_page_list = self::model_pagename_list();

		foreach ($model_page_list as $key => $value) {
			if ($model_page_list[$key]['pagename'] == $pagename) {
				if ($model_page_list[$key]['type'] == 2)
					return $model_page_list[$key]['file'];
				else
					return $model_page_list[$key]['id'];
			}
		}

		return $pagename; // This is not supposed to be reached
	}			

}

?>
