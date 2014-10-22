<?php
/**
* @version $Id: view.html.php $
* @version		2.3.1 12/12/2013
* @package		com_myjspace
* @author       Bernard Saulmé
* @copyright	Copyright (C) 2010-2011-2012-2013 Bernard Saulmé
* @license      GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die;
if (!defined('DS')) define('DS', DIRECTORY_SEPARATOR);

require_once JPATH_COMPONENT_SITE.DS.'helpers'.DS.'legacy.php';

jimport('joomla.application.component.view');

class MyjspaceViewDelete extends JViewLegacy
{
	function display($tpl = null)
	{
        require_once JPATH_COMPONENT_SITE.DS.'helpers'.DS.'user.php';
        require_once JPATH_COMPONENT_SITE.DS.'helpers'.DS.'util.php';
		
		// Config
		$pparams = JComponentHelper::getParams('com_myjspace');
		$app = JFactory::getApplication();
		$document = JFactory::getDocument();
		$params = $app->getParams();

		// Params
		$Itemid = JRequest::getInt('Itemid', 0);
		$Itemid_config = get_menu_itemid('index.php?option=com_myjspace&view=config', $Itemid);
		$Itemid_see = get_menu_itemid('index.php?option=com_myjspace&view=see', $Itemid); // Compatibility old install
		$Itemid_see = get_menu_itemid('index.php?option=com_myjspace&view=see&id=&pagename=', $Itemid_see);
		$Itemid_pages = get_menu_itemid('index.php?option=com_myjspace&view=pages', $Itemid);

		$id = JRequest::getInt( 'id' , 0);
		$catid = JRequest::getInt('catid', $params->get('catid', 0));
		if ($id == 0) {
			$pageid_tab = JRequest::getVar('cid', array(0));
			$id = (is_array($pageid_tab) && isset($pageid_tab[0])) ? intval($pageid_tab[0]) : 0;	
		}

		// User info
		$user = JFactory::getuser();
		$user_page = New BSHelperUser();

		// Page id - check
		$list_page_tab = $user_page->GetListPageId($user->id, $id, $catid);
		$nb_page = count($list_page_tab);	
		if ($id <= 0 || $nb_page != 1) {
			if ($nb_page == 0 && $id > 0) {
				$app->redirect(Jroute::_('index.php?option=com_myjspace&view=pages&lview=delete&Itemid='.$Itemid_pages, false), JText::_('COM_MYJSPACE_NOTALLOWED') , 'error');
				return;
			} else if ($nb_page == 1) { // => the page
				$id = $list_page_tab[0]['id'];
			} else { // Display Pages list
				$app->redirect(Jroute::_('index.php?option=com_myjspace&view=pages&lview=delete&Itemid='.$Itemid_pages, false));
				return;
			}
		} else if ($nb_page > 1) { // Error 
			$app->redirect('index.php', JText::_('COM_MYJSPACE_NOTALLOWED'), 'error');
			return;
		}
		
		// Personal page info
		$user_page->id = $id;
		$user_page->loadPageInfoOnly();
		
		// If no page
		if ($user_page->id == 0) {
			$app->redirect(JRoute::_('index.php?option=com_myjspace&view=see&Itemid='.$Itemid_see, false));
			return;		
		}

		// If page locked (admin & edit | edit)
		if ($user_page->blockEdit != 0) {
			$app->redirect(Jroute::_('index.php?option=com_myjspace&view=config&Itemid='.$Itemid_config, false), JText::_('COM_MYJSPACE_EDITLOCKED'), 'error');	
			return;		
		}
		
        // Web page title
		if ($pparams->get('pagetitle',1) == 1) {
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

		// Vars assign
		$this->assignRef('Itemid', $Itemid);
		$this->assignRef('user_page', $user_page);
		
		parent::display($tpl);
	}
}
?>
