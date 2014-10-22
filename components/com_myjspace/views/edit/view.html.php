<?php
/**
* @version $Id: view.html.php $
* @version		2.3.5 05/05/2014
* @package		com_myjspace
* @author       Bernard Saulmé
* @copyright	Copyright (C) 2010-2011-2012-2013-2014 Bernard Saulmé
* @license      GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die;
if (!defined('DS')) define('DS', DIRECTORY_SEPARATOR);

require_once JPATH_COMPONENT_SITE.DS.'helpers'.DS.'legacy.php';

jimport('joomla.application.component.view');

class MyjspaceViewEdit extends JViewLegacy
{
	function display($tpl = null)
	{
        require_once JPATH_COMPONENT_SITE.DS.'helpers'.DS.'user.php';
        require_once JPATH_COMPONENT_SITE.DS.'helpers'.DS.'user_event.php';
	    require_once JPATH_COMPONENT_SITE.DS.'helpers'.DS.'util.php';

		// Config
		$pparams = JComponentHelper::getParams('com_myjspace');
		$app = JFactory::getApplication();
		$document = JFactory::getDocument();
		$params = $app->getParams();

		// Params
		$layout = JRequest::getVar('layout', '');

		if ($layout == '') {
			// User info
			$user = JFactory::getuser();
			$user_page = New BSHelperUser();

			$id = JRequest::getInt('id', 0);
			$return = JRequest::getVar('return', '');
			$catid = JRequest::getInt('catid', $params->get('catid', 0));

			$Itemid = JRequest::getInt('Itemid', 0);
			$Itemid_pages = get_menu_itemid('index.php?option=com_myjspace&view=pages', $Itemid, $catid);
			$Itemid_config = get_menu_itemid('index.php?option=com_myjspace&view=config', $Itemid, $catid);

			if ($id > 0 && $catid != 0)
				$catid = 0;
	
			// Catid url
			if ($catid != 0)
				$catid_url = '&catid='.$catid;
			else
				$catid_url = '';

			$pageid_tab = JRequest::getVar('cid', array());
			$cid0 = (is_array($pageid_tab) && isset($pageid_tab[0])) ? intval($pageid_tab[0]) : 0;
			if ($id == 0)
				$id = $cid0;

			if ($id < 0 && $cid0 > 0) { // New page to create with $cid0 page id as model
				$model_copy = $cid0;
				$user_page->id = $model_copy;
				$user_page->loadPageInfoOnly();
				$catid = $user_page->catid;
				unset($user_page);
				$user_page = New BSHelperUser();
			} else {
				$model_copy = 0; 
			}
			
			// Page id - check
			if ($pparams->get('share_page', 0) != 0)
				$access = $user->getAuthorisedViewLevels();
			else
				$access = null;
			$list_page_tab = $user_page->GetListPageId($user->id, $id, $catid, $access);
			$nb_page = count($list_page_tab);

			if ($id <= 0 || $nb_page != 1) {
				if ($id < 0 && $nb_page >= $pparams->get('nb_max_page', 1)) { // New page KO
					$app->redirect(JRoute::_('index.php?option=com_myjspace&view=pages&lview=edit&Itemid='.$Itemid_pages.$catid_url, false), JText::_('COM_MYJSPACE_MAXREACHED'), 'error');	
					return;
				} else if ($id < 0 || ($nb_page == 0 && $id <= 0)) { // New page
					$id = 0;
				} else if ($nb_page == 1) { // id=0 => Display the page
					$id = $list_page_tab[0]['id'];
				} else { // Display Pages list
					$app->redirect(JRoute::_('index.php?option=com_myjspace&view=pages&lview=edit&Itemid='.$Itemid_pages.$catid_url, false));
					return;
				}
			} else if ($nb_page > 1) { // Error
				$app->redirect('index.php', JText::_('COM_MYJSPACE_NOTALLOWED'), 'error');
				return;
			}

			// Personal page info
			$user_page->id = $id;
			$user_page->loadPageInfo();

			// Test if foldername exist => Alert Admin
			$link_folder = $pparams->get('link_folder', 1);
			if (!BSHelperUser::ifExistFoldername($user_page->foldername) && $link_folder == 1) {
				$app->redirect('index.php', JText::_('COM_MYJSPACE_ALERTYOURADMIN'), 'error');
				return;
			}

			// Create automatically page if none, if option 'auto_create_page' is activated & max 1 model
			$auto_create_page = $pparams->get('auto_create_page', 3);
			$model_page_list = ($model_copy > 0) ? array() : BSUserEvent::model_pagename_list($catid); // Model page list
			if ($user_page->pagename == null && ($auto_create_page == 2 || $auto_create_page == 3) && count($model_page_list) <= 2) {
				$blockview = $pparams->get('user_mode_view_default', 1);

				if ($model_copy > 0) {
					$model_page = $model_copy;
				} else {
					$model_page = 0;
					foreach ($model_page_list as $k => $v) { // Take the last one
						if ($k != 0 && $model_page_list[$k]['catid'] > 0)
							$catid = $model_page_list[$k]['catid'];
					   $model_page = $model_page_list[$k]['pagename'];
					}
				}

				$pagename = $user_page->GetPagenameFree($pparams->get('auto_pagename_rule', "#username"), $user, $catid);

				$id = BSUserEvent::Adm_save_page_conf(0, $user->id, $pagename, $blockview, 0, '', '', '', '', $model_page, $catid, null, '', null, null, 'site_create');

				$user_page->id = $id;
				$user_page->loadPageInfo(); // Reload the user data
			}

			// Catid url (if catid change)
			if ($catid != 0) {
				$catid_url = '&catid='.$catid;
				$Itemid_config = get_menu_itemid('index.php?option=com_myjspace&view=config', $Itemid, $catid);
			}

			JRequest::setVar('id', $id, 'GET'); // value used for upload from editor

			if ($user_page->pagename == '' && $layout != 'tags') { // Page not found => Go to create it
				$app->redirect(JRoute::_('index.php?option=com_myjspace&view=config&Itemid='.$Itemid_config.$catid_url, false));
				return;
			}

			$msgvide = null;
			$this->assignRef('msg', $msgvide);
			if ($user_page->blockEdit == 1)
				$this->assignRef('msg', JText::_('COM_MYJSPACE_EDITBLOCKED'));
			else if ($user_page->blockEdit == 2)
				$this->assignRef('msg', JText::_('COM_MYJSPACE_EDITLOCKED'));
		
			// Editor selection
			$editor_selection = $pparams->get('editor_selection', 'myjsp');
			if (check_editor_selection($editor_selection) == false || $editor_selection == '-') // Use the Joomla default editor
				$editor_selection = null;

			// Editor button
			if ($pparams->get('allow_editor_button', 1) == 1)
				$editor_button = array('readmore', 'article');
			else
				$editor_button = false;

			// Editor 'windows' size
			$edit_x = $pparams->get('user_edit_x', '100%');
			$edit_y = $pparams->get('user_edit_y', '600px');

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

			// Vars Assign
			$this->assignRef('user_page', $user_page);
			$this->assignRef('Itemid', $Itemid);
			$this->assignRef('editor_button', $editor_button);
			$this->assignRef('edit_x', $edit_x);
			$this->assignRef('edit_y', $edit_y);
			$this->assignRef('editor_selection', $editor_selection);
			$this->assignRef('return', $return);
		} else if ($layout == 'tags') {
			// Tags buttons
			$e_name = JRequest::getVar('e_name', 'mjs_editable');
			$allow_tag_myjsp_iframe = $pparams->get('allow_tag_myjsp_iframe', 1);
			$allow_tag_myjsp_include = $pparams->get('allow_tag_myjsp_include', 1);
			$share_page = $pparams->get('share_page', 0);
			$language_filter = $pparams->get('language_filter', 0);

			$this->assignRef('e_name', $e_name);
			$this->assignRef('allow_tag_myjsp_iframe', $allow_tag_myjsp_iframe);
			$this->assignRef('allow_tag_myjsp_include', $allow_tag_myjsp_include);
			$this->assignRef('share_page', $share_page);
			$this->assignRef('language_filter', $language_filter);			
		}
		
		parent::display($tpl);
	}
}
?>
