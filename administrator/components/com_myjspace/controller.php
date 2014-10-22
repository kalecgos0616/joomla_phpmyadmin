<?php
/**
* @version $Id: controller.php $
* @version		2.3.1 11/11/2013
* @package		com_myjspace
* @author       Bernard Saulmé
* @copyright	Copyright (C) 2010-2011-2012-2013 Bernard Saulmé
* @license      GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die;
if (!defined('DS')) define('DS', DIRECTORY_SEPARATOR);

require_once JPATH_COMPONENT_SITE.DS.'helpers'.DS.'legacy.php';
require_once JPATH_COMPONENT_SITE.DS.'helpers'.DS.'user.php';

jimport('joomla.application.component.controller');

class MyjspaceController extends JControllerLegacy
{
// Displays a view
	function display($cachable = false, $urlparams = false)
	{
		// Load & add the menu
		require_once JPATH_COMPONENT.DS.'helpers'.DS.'myjspace.php';
		MyJspaceHelper::addSubmenu(JRequest::getCmd('view', 'myjspace'));
		
		switch ($this->getTask())
		{
			case 'edit'    :
			{
				JRequest::setVar('view', 'page');
			} break;
			case 'remove'    :
			{
				JRequest::setVar('task', 'remove');
			} break;
			case 'add'    :
			{
				// If no root pages folder existing & root page folder supposed to be used
				// Config
				$pparams = JComponentHelper::getParams('com_myjspace');
				require_once JPATH_COMPONENT_SITE.DS.'helpers'.DS.'user.php';
				$foldername = BSHelperUser::getFoldername();
				$link_folder = $pparams->get('link_folder', 1);
				// Test itself
				if (!BSHelperUser::ifExistFoldername($foldername) && $link_folder == 1) {
					$this->setRedirect(JRoute::_('index.php?option=com_myjspace&view=url', false), JText::_('COM_MYJSPACE_ALERTYOURADMIN'), 'error');
					return;
				}

				$this->setRedirect(JRoute::_('index.php?option=com_myjspace&view=createpage', false));
			} break;
		}
	
		parent::display();
	}
	
// Create an empty page or a page with a model
	function adm_create_page()
	{
		JRequest::checkToken('post') or jexit(JText::_('JINVALID_TOKEN'));

        require_once JPATH_COMPONENT_SITE.DS.'helpers'.DS.'user_event.php';
        require_once JPATH_COMPONENT_SITE.DS.'helpers'.DS.'user.php';
		
		$pagename = JRequest::getVar('mjs_pagename', '');
		$user_name = JRequest::getVar('mjs_username', '');
		$user_id = JRequest::getInt('mjs_userid', 0);
		$mjs_model_page = JRequest::getInt('mjs_model_page', 0);

		$pparams = JComponentHelper::getParams('com_myjspace');
		$nb_max_page = $pparams->get('nb_max_page', 1);

		if ($user_id > 0)
			$user = JFactory::getuser($user_id); // J1.6+
		else
			$user = JFactory::getuser($user_name); // J!1.5

		$user_page = New BSHelperUser();

		// Model page
		$catid = 0;
		$model_page = 0;
		if ($mjs_model_page != 0) {
			$model_pagename_tab = BSUserEvent::model_pagename_list();
			if (array_key_exists($mjs_model_page, $model_pagename_tab)) {
				$catid = $model_pagename_tab[$mjs_model_page]['catid'];
				$model_page = $model_pagename_tab[$mjs_model_page]['pagename'];				
			}
		}
		
		if ($pagename == '')
			$pagename = $user_page->GetPagenameFree($pparams->get('auto_pagename_rule', "#username"), $user, $catid);
		else {
			$pagename_check = $user_page->GetPagenameFree($pagename, $user, $catid);
			if (BSHelperUser::stringURLSafe($pagename) != $pagename_check)
				$pagename = $pagename_check; // New page name already exists, so we use the automatic proposal
		}
		if (($user) && $pagename != '') {
			$list_page_tab = $user_page->GetListPageId($user->id);
			if (count($list_page_tab) >= $pparams->get('nb_max_page',1))
				$this->setRedirect(JRoute::_('index.php?option=com_myjspace&view=pages', false), JText::_('COM_MYJSPACE_USERPAGEMAXREACH'), 'error');			
			else {
				$id = BSUserEvent::Adm_save_page_conf(0, $user->id, $pagename, $pparams->get('user_mode_view_default', 1), $pparams->get('user_mode_edit_default', 0), '', '', '', '', $model_page, $catid, null, '', null, null, 'admin');

				if ($id > 0)
					$this->setRedirect(JRoute::_('index.php?option=com_myjspace&view=page&task=edit&id='.$id, false));

				return;
			}
		} else // User do no exist
			$this->setRedirect(JRoute::_('index.php?option=com_myjspace&view=pages', false), JText::_('COM_MYJSPACE_NOTALLOWED'), 'error');
	}
	
// Remove the personal page record from the database and folder & files from disk
	function remove()
	{
		JRequest::checkToken('post') or jexit(JText::_('JINVALID_TOKEN'));

        require_once JPATH_COMPONENT_SITE.DS.'helpers'.DS.'user_event.php';
		$pageid_tab = JRequest::getVar('cid', array(0));

		BSUserEvent::adm_page_remove($pageid_tab, JRoute::_('index.php?option=com_myjspace&view=pages', false) , 'admin');
	}

// Save content & details
	function adm_save_page_all() {
		$this->adm_save_page_content(null); // Save content
		$this->adm_save_page(); // Save details
	}
	
// Save (update) page details 'only'
	function adm_save_page($url = '')
	{
		JRequest::checkToken('post') or jexit(JText::_('JINVALID_TOKEN'));

        require_once JPATH_COMPONENT_SITE.DS.'helpers'.DS.'user_event.php';

		$pparams = JComponentHelper::getParams('com_myjspace');

		$id = JRequest::getInt('id', 0);
		
		$prefix_publish = (version_compare(JVERSION, '1.6.0', 'ge')) ? 'jform_' : '';

		$pagename = JRequest::getVar('mjs_pagename', '');
		$blockview = JRequest::getVar('mjs_mode_view', $pparams->get('user_mode_view_default', 1));
		$blockedit = JRequest::getVar('mjs_mode_edit', 0);
		$resethits =  JRequest::getVar('resethits', 'no');
		$publish_up = JRequest::getVar($prefix_publish.'publish_up', '');
		$publish_down = JRequest::getVar($prefix_publish.'publish_down', '');
		$metakey = JRequest::getVar('mjs_metakey', '');
		$mjs_template = JRequest::getVar('mjs_template', '');
		$mjs_categories = JRequest::getInt('mjs_categories', 0);
		$user_id = JRequest::getInt('mjs_userid', 0);

		if ($pparams->get('share_page', 0) != 0)
			$mjs_share = JRequest::getInt('mjs_share', 0);
		else
			$mjs_share = null;

		$mjs_language = JRequest::getVar('mjs_language', '');

		if ($pparams->get('language_filter', 0) == 2) { // Associations
			$associations = JRequest::getVar('associations', array(0));		
			$associations[$mjs_language] = $id;
			BSHelperUser::setAssociations($associations);
		}

		// Get J!3.1+ tags
		$metadata = JRequest::getVar('metadata', array(0));
		$tags = (array_key_exists('tags', $metadata)) ? $metadata['tags'] : array();

		if (version_compare(JVERSION, '1.6.0', 'lt')) { // J!1.5
			$user_name = JRequest::getVar('mjs_username', '');
			$user = JFactory::getuser($user_name);
			if ($user)
				$user_id = $user->id;
			else
				$user_id = 0; // No change
		}

		if ($url == '')
			$url = 'index.php?option=com_myjspace&view=page&id='.$id;

		if ($resethits != 'yes') {
			BSUserEvent::Adm_save_page_conf($id, $user_id, $pagename, $blockview, $blockedit, $publish_up, $publish_down, $metakey, $mjs_template, 0, $mjs_categories, $mjs_share, $mjs_language, $tags, JRoute::_($url, false), 'admin2');
		} else {
			BSUserEvent::Adm_reset_page_access($id, JRoute::_($url, false), 'admin');
		}
	}

// Save (update) page details 'only' & exit to page list
	function adm_save_page_exit()
	{
		$this->adm_save_page('index.php?option=com_myjspace&view=pages');
	}  

// Upload file for user page
	function upload_file()
	{
		JRequest::checkToken('post') or jexit(JText::_('JINVALID_TOKEN'));

		$Itemid = JRequest::getInt('Itemid' , 0);
		$id = JRequest::getInt('id', 0);

		$layout = JRequest::getVar('layout', '');
		if ($layout == 'upload')
			$layout = '&tmpl=component&layout=return';

		require_once JPATH_COMPONENT_SITE.DS.'helpers'.DS.'user_event.php';
		
		if (!isset($_FILES['upload_file']))
			return;
		$FileObject = $_FILES['upload_file'];

		BSUserEvent::Adm_upload_file($id, $FileObject, JRoute::_('index.php?option=com_myjspace&view=page&id='.$id.$layout, false), 'admin');
	}

// Delete file from user page
	function delete_file()
	{
		JRequest::checkToken('post') or jexit(JText::_('JINVALID_TOKEN'));

		$Itemid = JRequest::getInt('Itemid' , 0);
		$id = JRequest::getInt('id', 0);

		$layout = JRequest::getVar('layout', '');
		if ($layout == 'upload')
			$layout = '&tmpl=component&layout=return';

		require_once JPATH_COMPONENT_SITE.DS.'helpers'.DS.'user_event.php';
		
		$file_name = JRequest::getVar('delete_file');
		BSUserEvent::Adm_delete_file($id, $file_name, JRoute::_('index.php?option=com_myjspace&view=page&id='.$id.$layout, false), 'admin');
	}

// Save(update) page content 'only' & exit to page list
	function adm_save_page_content_exit()
	{
		$this->adm_save_page_content('index.php?option=com_myjspace&view=pages');
	}	

// Save(update) page content 'only'
	function adm_save_page_content($url = '')
	{
		JRequest::checkToken('post') or jexit(JText::_('JINVALID_TOKEN'));

        require_once JPATH_COMPONENT_SITE.DS.'helpers'.DS.'user_event.php';
        require_once JPATH_COMPONENT_SITE.DS.'helpers'.DS.'util.php';

		$id = JRequest::getInt('id', 0);
		
		$content = JRequest::getVar('mjs_content', '@@vide@@', 'POST', 'STRING', JREQUEST_ALLOWRAW);
		if ($content == '@@vide@@') { // To allow really empty page
			$this->setRedirect(JRoute::_('index.php'), JText::_('COM_MYJSPACE_ERRUPDATINGPAGE'), 'error');
			return;
		}		

		$pparams = JComponentHelper::getParams('com_myjspace');
		if ($pparams->get('editor_bbcode', 1) == 1)
			$content = bs_bbcode($content, $pparams->get('editor_bbcode_width', 800), $pparams->get('editor_bbcode_height'));
	
		if ($url !== null) {
			if ($url == '')
				$url = JRoute::_('index.php?option=com_myjspace&view=page&id='.$id, false);
			$url = JRoute::_($url, false);
		}

		BSUserEvent::Adm_save_page_content($id, $content, $pparams->get('name_page_max_size', 92160), $url, 'admin');
	}
	
// Rename/create/move the personal Root pages folder or sub-folders
	function adm_ren_folder () 
	{
		JRequest::checkToken('post') or jexit(JText::_('JINVALID_TOKEN'));

        require_once JPATH_COMPONENT_SITE.DS.'helpers'.DS.'user_event.php';

		$foldername_new = JRequest::getVar('mjs_foldername');
		$keep = JRequest::getInt('keep', 0);
		
		BSUserEvent::Adm_ren_folder($foldername_new, $keep, JRoute::_('index.php?option=com_myjspace&view=url', false));
	}

// Create folders and link pages for all personal pages
	function adm_create_folder()
	{
		if (JRequest::checkToken('get') && JRequest::checkToken('post'))
			jexit(JText::_('JINVALID_TOKEN'));

        require_once JPATH_COMPONENT_SITE.DS.'helpers'.DS.'user_event.php';
		
		list($retour, $msg) = BSUserEvent::Adm_create_folder();
		$url = JRoute::_('index.php?option=com_myjspace&view=tools', false);
		if ($retour > 0)
			$this->setRedirect($url, JText::_($msg), 'error');
		else
			$this->setRedirect($url, JText::_($msg), 'message');
	}

// Delete folders and link pages for all personal pages
	function adm_delete_folder()
	{
		JRequest::checkToken('post') or jexit(JText::_('JINVALID_TOKEN'));

        require_once JPATH_COMPONENT_SITE.DS.'helpers'.DS.'user_event.php';
		
		list($retour, $msg) = BSUserEvent::Adm_delete_folder();
		$url = JRoute::_('index.php?option=com_myjspace&view=tools', false);
		if ($retour > 0)
			$this->setRedirect($url, JText::_($msg), 'error');
		else
			$this->setRedirect($url, JText::_($msg), 'message');
	}
	
// Delete all empty pages (= content + folder empty)
	function adm_delete_empty_pages()
	{
		JRequest::checkToken('post') or jexit(JText::_('JINVALID_TOKEN'));

        require_once JPATH_COMPONENT_SITE.DS.'helpers'.DS.'user_event.php';
		
		list($retour, $msg) = BSUserEvent::adm_delete_empty_pages();
		$url = JRoute::_('index.php?option=com_myjspace&view=tools', false);
		if ($retour > 0)
			$this->setRedirect($url, JText::_($msg), 'warning');
		else
			$this->setRedirect($url, JText::_($msg), 'message');
	}

// Other tools
	function other_tools()
	{
		JRequest::checkToken('post') or jexit(JText::_('JINVALID_TOKEN'));

		if (@file_exists(JPATH_COMPONENT_SITE.DS.'helpers'.DS.'other_tools.php')) { // Other tools ?
			require_once JPATH_COMPONENT_SITE.DS.'helpers'.DS.'other_tools.php';
			$other_tools = new OtherTools();
			$id = JRequest::getInt('id', 0);

			if ($id == -1) { // Remove the file
				list($retour, $msg) = array(true, 'COM_MYJSPACE_ADMIN_DELETE_FOLDER_1');
				@unlink(JPATH_COMPONENT_SITE.DS.'helpers'.DS.'other_tools.php');
			} else {
				list($retour, $msg) = $other_tools->action($id);
			}

			$url = JRoute::_('index.php?option=com_myjspace&view=tools', false);
			if ($retour == false)
				$this->setRedirect($url, JText::_($msg), 'warning');
			else
				$this->setRedirect($url, JText::_($msg), 'message');
		}
	}
}
?>
