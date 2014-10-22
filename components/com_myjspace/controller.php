<?php
/**
* @version $Id: controller.php $
* @version		2.3.4 15/04/2014
* @package		com_myjspace
* @author       Bernard Saulmé
* @copyright	Copyright (C) 2010-2011-2012-2013-2014 Bernard Saulmé
* @license      GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die;
if (!defined('DS')) define('DS', DIRECTORY_SEPARATOR);

require_once JPATH_COMPONENT_SITE.DS.'helpers'.DS.'legacy.php';
		
jimport('joomla.application.component.controller');

class MyjspaceController extends JControllerLegacy
{
	function display($cachable = false, $urlparams = false)
	{
	  	$user = JFactory::getuser();
		$pparams = JComponentHelper::getParams('com_myjspace');
		$app = JFactory::getApplication();		
		$params = $app->getParams();
		$acces_ok = true;
		$get_view = JRequest::getCmd('view', '');
		
		// J! >= 1.6 ACL
		if (version_compare(JVERSION, '1.6.0', 'ge')) {
			// View
			if ($get_view != '' && !JFactory::getUser()->authorise('user.'.$get_view, 'com_myjspace'))
				$acces_ok = false;
		}

		// If not connected => redirection to login page for 'admin' & 'delete', 'edit', 'see (if no page id and pagename)'
		$id = JRequest::getInt('id', $params->get('id', 0));
		$pagename = JRequest::getVar('pagename', $params->get('pagename', 0));
		if (!isset($user->username) && ( $get_view == 'config' || $get_view == 'delete' || $get_view == 'edit' || ($get_view == 'see' && $id == 0 && $pagename == ''))) {
			$acces_ok = false; // Login redirection
		}

		if ($acces_ok == false && !isset($user->username)) { // Redirect to login page
			$uri = JFactory::getURI();
			$return = $uri->toString();
			
			if ($pparams->get('url_login_redirect', '')) 
				$url = $pparams->get('url_login_redirect', '');
			else {
				if (version_compare(JVERSION, '1.6.0', 'ge'))
					$url = 'index.php?option=com_users&view=login';
				else
					$url = 'index.php?option=com_user&view=login';
				$url .= '&return='.base64_encode($return); // To redirect to the originally call page
				$url = JRoute::_($url, false);
			}

			$this->setRedirect($url);
			return;
		} else if ($acces_ok == false && isset($user->username)) { // Not allowed
			$this->setRedirect('index.php', JText::_('COM_MYJSPACE_NOTALLOWED'), 'error');		
		}

		parent::display();
	}

// Compatibility <= 1.2
	function view_page()
	{
		$id = JRequest::getInt('id');
		$Itemid = JRequest::getInt('Itemid', 0);
		$return	= JRoute::_('index.php?option=com_myjspace&view=see&id='.$id.'&Itemid='.$Itemid, false);
		$this->setRedirect($return);
	}

// Save page content (view edit)
	function save()
	{
		JRequest::checkToken('post') or jexit(JText::_('JINVALID_TOKEN'));

		if (version_compare(JVERSION, '1.6.0', 'ge') && !JFactory::getUser()->authorise('user.edit', 'com_myjspace')) {
			$this->setRedirect('index.php', JText::_('COM_MYJSPACE_NOTALLOWED'), 'error');			
			return;
		}
		
        require_once JPATH_COMPONENT_SITE.DS.'helpers'.DS.'user_event.php';
        require_once JPATH_COMPONENT_SITE.DS.'helpers'.DS.'util.php';
        require_once JPATH_COMPONENT_SITE.DS.'helpers'.DS.'user.php';
		
		$pparams = JComponentHelper::getParams('com_myjspace');

		$id = JRequest::getInt('id', 0);
		$pagename = JRequest::getVar('pagename', '');
		$return = JRequest::getVar('return', '');
	
		$user = JFactory::getuser();
		$user_page = New BSHelperUser();
		if ($pparams->get('share_page', 0) != 0)
			$access = $user->getAuthorisedViewLevels();
		else
			$access = null;
		$list_page_tab = $user_page->GetListPageId($user->id, $id, 0, $access);
		if (count($list_page_tab) != 1 || $pagename == '') { // For 'my' page
			$this->setRedirect('index.php', JText::_('COM_MYJSPACE_NOTALLOWED'), 'error');
			return;
		}
		
		$content = JRequest::getVar('mjs_content', '@@vide@@', 'POST', 'STRING', JREQUEST_ALLOWRAW);
		if ($content == '@@vide@@') { // To allow really empty page
			$this->setRedirect('index.php', JText::_('COM_MYJSPACE_ERRUPDATINGPAGE'), 'error');
			return;
		}

		$pparams = JComponentHelper::getParams('com_myjspace');
		
		if ($pparams->get('editor_bbcode', 1) == 1)
			$content = bs_bbcode($content, $pparams->get('editor_bbcode_width', 800), $pparams->get('editor_bbcode_height'));

		$url = JRoute::_('index.php?option=com_myjspace&view=see&pagename='.$pagename, false);
		if ($return != '') {
			$return = base64_decode($return);
			if (JURI::isInternal($return))
				$url = $return;
		}

		BSUserEvent::Adm_save_page_content($id, $content, $pparams->get('name_page_max_size', 92160), $url, 'site');
	}
	
// Save page config (& create page if no exist)
	function save_config()
	{
		JRequest::checkToken('post') or jexit(JText::_('JINVALID_TOKEN'));

		$Itemid = JRequest::getInt('Itemid', 0);
		$id = JRequest::getInt('id', 0);

		if (version_compare(JVERSION, '1.6.0', 'ge') && !JFactory::getUser()->authorise('user.config', 'com_myjspace')) {
			$this->setRedirect('index.php', JText::_('COM_MYJSPACE_NOTALLOWED'), 'error');
			return;
		}
		
        require_once JPATH_COMPONENT_SITE.DS.'helpers'.DS.'user_event.php';
        require_once JPATH_COMPONENT_SITE.DS.'helpers'.DS.'user.php';

		$pparams = JComponentHelper::getParams('com_myjspace');

		$prefix_publish = (version_compare(JVERSION, '1.6.0', 'ge')) ? 'jform_' : '';

		$pagename = JRequest::getVar('mjs_pagename', '');
		$resethits = JRequest::getVar('resethits', 'no');
		$publish_up = JRequest::getVar($prefix_publish.'publish_up', '');
		$publish_down = JRequest::getVar($prefix_publish.'publish_down', '');
		$metakey = JRequest::getVar('mjs_metakey', '');
		$mjs_template = JRequest::getVar('mjs_template', '');
		$mjs_model_page = JRequest::getInt('mjs_model_page', 0);
		$mjs_categories = JRequest::getInt('mjs_categories', 0);
	
		$user = JFactory::getuser(); 
		$user_page = New BSHelperUser();
		$list_page_tab = $user_page->GetListPageId($user->id, $id); // do not filter on catid, because it can be an update
		if (count($list_page_tab) != 1) // For 'my' page
			$id = 0;
		
		if ($pparams->get('share_page', 0) == 2)
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

		// Model page
		$model_page = 0;
		if ($mjs_model_page != 0) {
			$model_pagename_tab = BSUserEvent::model_pagename_list();	
			if (array_key_exists($mjs_model_page, $model_pagename_tab)) {
				if ($model_pagename_tab[$mjs_model_page]['catid'] != 0)
					$mjs_categories = $model_pagename_tab[$mjs_model_page]['catid'];
				$model_page = $model_pagename_tab[$mjs_model_page]['pagename'];				
			}
		}

		if ($resethits == 'yes' && $id != 0) {
			BSUserEvent::Adm_reset_page_access($id, JRoute::_('index.php?option=com_myjspace&view=config&id='.$id.'&Itemid='.$Itemid, false), 'site');		
		} else if ($resethits == 'yes' && $id == 0) {
			$this->setRedirect('index.php', JText::_('COM_MYJSPACE_NOTALLOWED'), 'error');
		} else {
			if ($pparams->get('user_mode_view', 1) == 0)
				$blockview = $pparams->get('user_mode_view_default', 1); // Do do take param in this case (safety)
			else
				$blockview = JRequest::getVar('mjs_mode_view', $pparams->get('user_mode_view_default', 1));

			BSUserEvent::Adm_save_page_conf($id, $user->id, $pagename, $blockview, 0, $publish_up, $publish_down, $metakey, $mjs_template, $model_page, $mjs_categories, $mjs_share, $mjs_language, $tags, JRoute::_('index.php?option=com_myjspace&view=config&id='.$id.'&Itemid='.$Itemid, false), 'site');
		}
	}

// Delete page
	function del_page()
	{
		JRequest::checkToken('post') or jexit(JText::_('JINVALID_TOKEN'));

		if (version_compare(JVERSION, '1.6.0', 'ge') && !JFactory::getUser()->authorise('user.delete', 'com_myjspace')) {
			$this->setRedirect('index.php', JText::_('COM_MYJSPACE_NOTALLOWED'), 'error');
			return;
		}

        require_once JPATH_COMPONENT_SITE.DS.'helpers'.DS.'user_event.php';
        require_once JPATH_COMPONENT_SITE.DS.'helpers'.DS.'user.php';
        require_once JPATH_COMPONENT_SITE.DS.'helpers'.DS.'util.php';

		$Itemid = JRequest::getInt('Itemid', 0);
		$Itemid_see = get_menu_itemid('index.php?option=com_myjspace&view=see', $Itemid); // Compatibility old install
		$Itemid_see = get_menu_itemid('index.php?option=com_myjspace&view=see&id=&pagename=', $Itemid_see);		
		$Itemid_config = get_menu_itemid('index.php?option=com_myjspace&view=config', $Itemid);

		$user = JFactory::getuser(); 
		$user_page = New BSHelperUser();
		$list_page_tab = $user_page->GetListPageId($user->id, JRequest::getInt('id', 0)); 
		
		if (count($list_page_tab) == 1) // For 'my' page
			$pageid = $list_page_tab[0]['id'];
		else {
			$this->setRedirect('index.php', JText::_('COM_MYJSPACE_NOTALLOWED'), 'error');
			return;
		}
		
		$pparams = JComponentHelper::getParams('com_myjspace');
		$auto_create_page = $pparams->get('auto_create_page', 3);

		if ($auto_create_page != 3 && $auto_create_page != 1)
			BSUserEvent::Adm_page_remove($pageid, JRoute::_('index.php?option=com_myjspace&view=config&Itemid='.$Itemid_config, false));
		else
			BSUserEvent::Adm_page_remove($pageid, JRoute::_('index.php?option=com_myjspace&view=see&Itemid='.$Itemid_see, false));
	}

// Upload file for user page
	function upload_file()
	{
		JRequest::checkToken('post') or jexit(JText::_('JINVALID_TOKEN'));

		if (version_compare(JVERSION, '1.6.0', 'ge') && !JFactory::getUser()->authorise('user.config', 'com_myjspace')) {
			$this->setRedirect('index.php', JText::_('COM_MYJSPACE_NOTALLOWED'), 'error');
			return;
		}
	
        require_once JPATH_COMPONENT_SITE.DS.'helpers'.DS.'user_event.php';
        require_once JPATH_COMPONENT_SITE.DS.'helpers'.DS.'user.php';

		$Itemid = JRequest::getInt('Itemid', 0);
		
		$layout = JRequest::getVar('layout', '');
		if ($layout == 'upload')
			$layout = '&tmpl=component&layout=return';
		
		$user = JFactory::getuser(); 
		$user_page = New BSHelperUser();
		$list_page_tab = $user_page->GetListPageId($user->id, JRequest::getInt('id', 0)); 
		
		if (count($list_page_tab) == 1) // For 'my' page
			$pageid = $list_page_tab[0]['id'];
		else {
			$this->setRedirect('index.php', JText::_('COM_MYJSPACE_NOTALLOWED'), 'error');
			return;
		}

		if (!isset($_FILES['upload_file']))
			return;
		$FileObject = $_FILES['upload_file'];
		
		BSUserEvent::Adm_upload_file($pageid, $FileObject, JRoute::_('index.php?option=com_myjspace&view=config&id='.$pageid.'&Itemid='.$Itemid.$layout, false), 'site');
	}
	
// Delete file from user page
	function delete_file()
	{
		JRequest::checkToken('post') or jexit(JText::_('JINVALID_TOKEN'));

		if (version_compare(JVERSION, '1.6.0', 'ge') && !JFactory::getUser()->authorise('user.config', 'com_myjspace')) {
			$this->setRedirect('index.php', JText::_('COM_MYJSPACE_NOTALLOWED'), 'error');
			return;
		}

        require_once JPATH_COMPONENT_SITE.DS.'helpers'.DS.'user_event.php';
        require_once JPATH_COMPONENT_SITE.DS.'helpers'.DS.'user.php';

		$Itemid = JRequest::getInt('Itemid', 0);

		$layout = JRequest::getVar('layout', '');
		if ($layout == 'upload')
			$layout = '&tmpl=component&layout=return';

		$user = JFactory::getuser(); 
		$user_page = New BSHelperUser();
		$list_page_tab = $user_page->GetListPageId($user->id, JRequest::getInt('id', 0)); 

		if (count($list_page_tab) == 1) // For 'my' page
			$pageid = $list_page_tab[0]['id'];
		else {
			$this->setRedirect('index.php', JText::_('COM_MYJSPACE_NOTALLOWED'), 'error');
			return;
		}

		$file_name = JRequest::getVar('delete_file');
		BSUserEvent::Adm_delete_file($pageid, $file_name, JRoute::_('index.php?option=com_myjspace&view=config&id='.$pageid.'&Itemid='.$Itemid.$layout, false), 'site');
	}

// Set the page status
	function set_status()
	{
		JRequest::checkToken('get') or jexit(JText::_('JINVALID_TOKEN'));

		$id = JRequest::getInt('id', 0);

		if (version_compare(JVERSION, '1.6.0', 'ge') && !JFactory::getUser()->authorise('user.config', 'com_myjspace') && !JFactory::getUser()->authorise('user.edit', 'com_myjspace')) {
			$this->setRedirect('index.php', JText::_('COM_MYJSPACE_NOTALLOWED'), 'error');
			return;
		}

        require_once JPATH_COMPONENT_SITE.DS.'helpers'.DS.'user.php';

		$pparams = JComponentHelper::getParams('com_myjspace');
		if ($pparams->get('user_mode_view', 1) == 0)
			$blockview = $pparams->get('user_mode_view_default', 1); // Do do take param in this case (safety)
		else
			$blockview = JRequest::getVar('mjs_mode_view', $pparams->get('user_mode_view_default', 1));

		$user = JFactory::getuser();
		$user_page = New BSHelperUser();
		$user_page->id = $id;
		$user_page->blockView = $blockview;
		$user_page->SetConfPage(2);
	}
	
}
?>
