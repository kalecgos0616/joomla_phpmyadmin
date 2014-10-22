<?php
/**
* @version $Id: view.html.php $
* @version		2.4.1 12/07/2014
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

class MyjspaceViewSee extends JViewLegacy
{
	function display($tpl = null)
	{
        require_once JPATH_COMPONENT_SITE.DS.'helpers'.DS.'user.php';
        require_once JPATH_COMPONENT_SITE.DS.'helpers'.DS.'util.php';
        require_once JPATH_COMPONENT_SITE.DS.'helpers'.DS.'util_acl.php';

		// Config
		$pparams = JComponentHelper::getParams('com_myjspace');
		$app = JFactory::getApplication();
		$document = JFactory::getDocument();

		$params = $app->getParams(); // Use param ?
		$catid = JRequest::getInt('catid', $params->get('catid', 0));
		if (JRequest::getInt('id', 0) == 0 && JRequest::getVar('pagename', '') == '') {
			JRequest::setVar('id', $params->get('id', 0), 'GET');
			JRequest::setVar('pagename', $params->get('pagename', ''), 'GET');
		}

		$nb_max_page = $pparams->get('nb_max_page', 1);

		// Params
		$Itemid = JRequest::getInt('Itemid', 0);
		$Itemid_pages = get_menu_itemid('index.php?option=com_myjspace&view=pages', $Itemid, $catid);
		$id = JRequest::getInt('id', 0);
		if ($id == 0) {
			$pageid_tab = JRequest::getVar('cid', array(0));
			$id = intval($pageid_tab[0]);
		}
		$return = JRequest::getVar('return', '');
		$icon = JRequest::getInt('icon', 1);

		// User info
		$user_actual = JFactory::getuser();
		$user_page = New BSHelperUser();

		// Auto-create for edit ?
		$auto_create_page = $pparams->get('auto_create_page', 3);
		$auto_create_page_edit = ($auto_create_page == 2) || ($auto_create_page == 3);

		// Criteria : User info or pagename
		$pagename = '';
		if ($id > 0)
			$id = $id;
		else if (JRequest::getVar('pagename', ''))
			$pagename = str_replace(':', '-', JRequest::getVar('pagename', ''));
		else  {
			// Page id - check
			$list_page_tab = $user_page->GetListPageId($user_actual->id, $id, $catid);
			$nb_page = count($list_page_tab);
			if ($nb_page == 1) {
				$id = $list_page_tab[0]['id'];
			} else if ($auto_create_page_edit == 1 && $nb_max_page > 1 && $nb_page != 0) {
				$app->redirect(Jroute::_('index.php?option=com_myjspace&view=pages&Itemid='.$Itemid_pages, false)); // Default lview=see
				return;
			} else if ($auto_create_page_edit == 0 && ($nb_page > 1 || $nb_max_page > 1)) {
				$app->redirect(Jroute::_('index.php?option=com_myjspace&view=pages&Itemid='.$Itemid_pages, false)); // Default lview=see
				return;
			}
		}

		// Personal page info
		$user_page->id = $id;
		if ($pagename != '') {
			$user_page->pagename = $pagename;
			$user_page->loadPageInfo(1);
		} else
			$user_page->loadPageInfo();

		// User (for page) info
		$table = JUser::getTable();
		if ($table->load($user_page->userid)) { // Test if user exist before to retrive info
			$user = JFactory::getUser($user_page->userid);
		} else { // User no no exist any more !
			$user = new stdClass();
			$user->id = 0;
			$user->username = ' '; // '' to do NOT display a page with no user
			$user->name = '';
		}

		// Increment hits, if : not empy, not the owner, no block  ... :-)
		$allow_plugin = $pparams->get('allow_plugin', 1);
		$page_increment = $pparams->get('page_increment', 1);
		if ($page_increment == 1 && $user_actual->id != $user_page->userid && $user_page->content != null && ($user_page->blockView == 1 || ($user_page->blockView == 2 && $user_actual->username != "")))
			$user_page->updateLastAccess(hash("crc32b", addr_ip()));

        // Content
		$uploadadmin = $pparams->get('uploadadmin', 1);
		$uploadimg = $pparams->get('uploadimg', 1);
		$tag_mysp_file_separ = $pparams->get('tag_mysp_file_separ', ' ');
		$chaine_files = '';
		if ($uploadadmin == 1 && $uploadimg == 1) { // May be add optional in the future
			require_once JPATH_COMPONENT_SITE.DS.'helpers'.DS.'util.php';
			$tab_list_file = list_file_dir(JPATH_ROOT.DS.$user_page->foldername.DS.$user_page->pagename, '*', 1);
			$nb = count($tab_list_file);
			for ($i = 0 ; $i < $nb ; ++$i)
				$chaine_files .= '<a href="'.JURI::base().$user_page->foldername.'/'.$user_page->pagename.'/'.$tab_list_file[$i].'">'.$tab_list_file[$i].'</a>'.$tag_mysp_file_separ; 
		}

		if ($pparams->get('allow_user_content_var', 1))
			$content = $user_page->traite_prefsuf($user_page->content, $user, $page_increment, JText::_('COM_MYJSPACE_DATE_FORMAT'), $chaine_files, $Itemid, false);
		else
			$content = $user_page->content;

		// [register]
		if ($pparams->get('editor_bbcode_register', 0) == 1 && strlen($content) <= 92160) { // Allow to use the dynamic tag [register]
			$uri = JFactory::getURI();
			$return_here = $uri->toString();
			if ($pparams->get('url_login_redirect', '')) 
				$url = $pparams->get('url_login_redirect', '');
			else {
				if (version_compare(JVERSION, '1.6.0', 'ge'))
					$url = 'index.php?option=com_users&view=login';
				else
					$url = 'index.php?option=com_user&view=login';
				$url .= '&return='.base64_encode($return_here); // to redirect to the originaly call page
				$url =  Jroute::_($url, false);
			}
 
			if ($user_actual->id != 0)// if registered
				$content = preg_replace('!\[register\](.+)\[/register\]!isU', '$1', $content);
			else // if not registered
				$content = preg_replace('!\[register\](.+)\[/register\]!isU', JText::sprintf('COM_MYJSPACE_REGISTER', $url), $content);		
		}

		// Force default dates
		if ($pparams->get('publish_mode', 2) == 0) { // do not take into account the dates
			$user_page->publish_up = '0000-00-00 00:00:00';
			$user_page->publish_down = '0000-00-00 00:00:00';
		}
		if ($user_page->publish_down == '0000-00-00 00:00:00')
			$user_page->publish_down = date('Y-m-d 00:00:00', strtotime("+1 day"));

		// Specific context
		$prefix = '';
		$suffix = '';
		$aujourdhui = time();

		// If ACL filter for display this page
		$user_mode_view_acl = $pparams->get('user_mode_view_acl', 0);
		if (version_compare(JVERSION, '1.6.0', 'ge') && $user_mode_view_acl == 1)
			$access = $user_actual->getAuthorisedViewLevels();
		else
			$access = array();

		if ($user_page->id == 0) {
			$content = JText::_('COM_MYJSPACE_PAGENOTFOUND');
			$allow_plugin = 0;
		} else if ($user_page->blockView == 0 && $user_actual->id != $user_page->userid) {
			$content = JText::_('COM_MYJSPACE_NOTALLOWED');
			$allow_plugin = 0;
		} else if ($user_page->blockView >= 2 && (($user_mode_view_acl == 0 && $user_actual->id <= 0) || ($user_mode_view_acl == 1 && !in_array($user_page->blockView, $access)))) {
			$content = JText::sprintf('COM_MYJSPACE_PAGERESERVED', get_assetgroup_label($user_page->blockView));
			$allow_plugin = 0;
		} else if ($user_page->content == null) {
			$content = JText::_('COM_MYJSPACE_PAGEEMPTY');
			$allow_plugin = 0;
		} else if ((strtotime($user_page->publish_up) > $aujourdhui || strtotime($user_page->publish_down) <= $aujourdhui) && ($user_actual->id != $user_page->userid || $pparams->get('publish_mode', 2) == 1)) {
			$content = JText::_('COM_MYJSPACE_PAGEUNPLUBLISHED');
			$allow_plugin = 0;
		} else {
		// Top and bottom
			if ($pparams->get('page_prefix', ''))
				$prefix = '<span class="top_myjspace">'.$user_page->traite_prefsuf($pparams->get('page_prefix', ''), $user, $page_increment, JText::_('COM_MYJSPACE_DATE_FORMAT'), $chaine_files, $Itemid, true).'</span><br />';
				
			$page_suffix = $pparams->get('page_suffix', '#bsmyjspace');
			if ($page_suffix == '#bsmyjspace' && $pparams->get('display_myjspace_ref', 1) == 0)
				$page_suffix = '';
			if ($page_suffix)
				$suffix = '<span class="bottom_myjspace">'.$user_page->traite_prefsuf($page_suffix, $user, $page_increment, JText::_('COM_MYJSPACE_DATE_FORMAT'), $chaine_files, $Itemid, true).'</span><br />';
		}
		$content = '<div class="myjspace-prefix">'.$prefix.'</div><div class="myjspace-content"></div>'.$content.'<div class="myjspace-suffix">'.$suffix.'</div>';

		// Set the pagename, for the case if the view page is call using id (to help for betted Search engine ref with SEO in some cases: pagebreak ...)
		JRequest::setVar('pagename', $user_page->pagename, 'GET');
		JRequest::setVar('id', '', 'GET');

		// Lightbox usage
		$add_lightbox = $pparams->get('add_lightbox', 1);
		$this->assignRef('add_lightbox', $add_lightbox);		

		// Process the prepare content for plugins
		$contenu = new stdClass();
		$contenu->text = $content;
		$contenu->toc = '';
		if ($pparams->get('pagetitle', 1)) { // Browser page title
			if (version_compare(JVERSION, '1.6.0', 'ge'))
				$contenu->page_title = JText::sprintf('COM_MYJSPACE_PAGETITLE', $document->getTitle(), $user_page->title); 
			else
				$contenu->page_title = $user_page->title;
		}
		$contenu->metadesc = $user_page->metakey; // HTML description

		// HTML content
		if ($pparams->get('pageauthor', 1) == 1) { // Meta data : author
			if ($app->getCfg('MetaAuthor', 1) == '1')
				$document->setMetaData('author', $user->name);
        }

		if ($allow_plugin >= 1) {
			JPluginHelper::importPlugin('content');
			$dispatcher	= JDispatcher::getInstance();

			$contenu->id = $user_page->id; // To have a 'false' article id (can identify all page as same J! article ...)
			$contenu->catid = $user_page->catid;
			$contenu->title = $user_page->title;
			$contenu->alias = $user_page->pagename;
			$contenu->introtext = $content; // introtext = text
			$contenu->created_by = $user_page->userid; // author id
			$contenu->publish_up = $user_page->publish_up;
			$contenu->publish_down = $user_page->publish_down;
			$contenu->created = $user_page->create_date;
			$contenu->modified = $user_page->last_update_date;
			$contenu->hits = $user_page->hits;
			$contenu->category_title = BSHelperUser::GetCategoryLabel($user_page->catid);
			if ($user_page->blockView == 0)
				$contenu->state = 0;
			else
				$contenu->state = 1;

			$params = clone($app->getParams('com_content')); // To have all (false) 'regular content' default data if a plugin for content call it
			$limitstart = JRequest::getString('limitstart', 0);

			if (version_compare(JVERSION, '1.6.0', 'lt'))
				$results = $dispatcher->trigger('onPrepareContent', array(&$contenu, &$params, &$limitstart, 0));
			else
				$results = $dispatcher->trigger('onContentPrepare', array('com_content.myjspace', &$contenu, &$params, &$limitstart, 0));

			if ($allow_plugin > 1) { // 1.6+
				$contenu->event = new stdClass();
				$results = $dispatcher->trigger('onContentAfterTitle', array('com_content.myjspace', &$contenu, &$params, &$limitstart, 0));
				$contenu->event->afterDisplayTitle = trim(implode("\n", $results));

				$results = $dispatcher->trigger('onContentBeforeDisplay', array('com_content.myjspace', &$contenu, &$params, &$limitstart, 0));
				$contenu->event->beforeDisplayContent = trim(implode("\n", $results));

				$results = $dispatcher->trigger('onContentAfterDisplay', array('com_content.myjspace', &$contenu, &$params, &$limitstart, 0));
				$contenu->event->afterDisplayContent = trim(implode("\n", $results));
			}
		}

		if ($contenu->page_title) // Browser page title
			$document->setTitle($contenu->page_title);

		if ($contenu->metadesc) // Description
			$document->setDescription($contenu->metadesc);

		// Page template specific
		if ($user_page->template != '') {
			if (version_compare(JVERSION, '3.2.0', 'ge')) { // system plugin usage
				$mjspTemplateSet = JRequest::getVar('mjspTemplateSet', '', 'cookie', 'var');
				if ($mjspTemplateSet != $user_page->template) {
					setcookie('mjspTemplateSet', $user_page->template, time()+86400, '');
					$app->redirect(JRoute::_('index.php?option=com_myjspace&view=see&pagename='.$user_page->pagename, false));
					return;
				}
			} else { // use 'only' API
				$app->setTemplate($user_page->template);
			}
		} 

		// Breadcrumbs
		$pathway = $app->getPathway();
		$pathway->addItem($user_page->title, '');

		// Background image 
		$file_background = $user_page->foldername.'/'.$user_page->pagename.'/background.jpg';
		if (@file_exists($file_background)) {
			$css_background = "background-image:url('".$file_background."');";
		} else
			$css_background = '';

		// Tags
		$show_tags = $pparams->get('show_tags', 0);
		if (version_compare(JVERSION, '3.1.4', 'ge') && $show_tags == 1) {
			$contenu->tagLayout = new JLayoutFile('joomla.content.tags');
			$contenu->tags = new JHelperTags;
			$contenu->tags->getItemTags('com_myjspace.see', $user_page->id);		
		} else
			$show_tags = 0;

		// Edit link icon
		$edit_icon = null;
		$icon_edit_view_see = $pparams->get('icon_edit_view_see', 2);
		if ($icon == 1 && $icon_edit_view_see > 0 && $user_actual->id != 0 && ($user_actual->id == $user_page->userid || $user_page->id == 0)) { // user connected & page owner
			$title_edit = JText::_('COM_MYJSPACE_TITLEEDIT1');
			$title_config = JText::_('COM_MYJSPACE_TITLECONFIG1');
			$Itemid_edit = get_menu_itemid('index.php?option=com_myjspace&view=edit', $Itemid, $catid);
			$Itemid_config = get_menu_itemid('index.php?option=com_myjspace&view=config', $Itemid, $catid);
			$return_url = '';
			$edit_icon_edit = '';
			$edit_icon_config = '';
			if ($return != '')
				$return_url = '&return='.$return;
			if ($catid != 0) // Catid url
				$catid_url = '&catid='.$catid;
			else
				$catid_url = '';
			$url_edit = Jroute::_('index.php?option=com_myjspace&view=edit&id='.$user_page->id.$return_url.$catid_url.'&Itemid='.$Itemid_edit, false);
			$url_config = Jroute::_('index.php?option=com_myjspace&view=config&id='.$user_page->id.$return_url.$catid_url.'&Itemid='.$Itemid_config, false);

			if (version_compare(JVERSION, '1.6.0', 'lt')) { // J!1.5
				$url_icon_edit = 'components/com_myjspace/images/icon-16-edit.png';
				$url_icon_config = 'components/com_myjspace/images/icon-16-config.png';

				if ($user_page->blockEdit == 0)
					$edit_icon_edit	= "<span class=\"hasTip\" title=\"$title_edit\"><a href=\"$url_edit\" ><img src=\"$url_icon_edit\" alt=\"$title_edit\" /></a></span>";

				if ( $user_page->blockEdit != 2)
					$edit_icon_config	= "<span class=\"hasTip\" title=\"$title_config\"><a href=\"$url_config\" ><img src=\"$url_icon_config\" alt=\"$title_config\" /></a></span>";

				if ($edit_icon_edit != '' || $edit_icon_config != '')
					$edit_icon = "
					<table class=\"contentpaneopen\">
					<tr>
						<td align=\"right\" width=\"100%\" class=\"buttonheading\">
							$edit_icon_edit
						</td>
						<td align=\"right\" width=\"100%\" class=\"buttonheading\">
							$edit_icon_config
						</td>
					</tr>
					</table>
					";
			} else if (version_compare(JVERSION, '3.0.0', 'lt') || $icon_edit_view_see == 2) { // J!1.6, 1.7, 2.5 ... or forced 
				$url_icon_edit = 'components/com_myjspace/images/icon-16-edit.png';
				$url_icon_config = 'components/com_myjspace/images/icon-16-config.png';

				if (JFactory::getUser()->authorise('user.edit', 'com_myjspace') && $user_page->blockEdit == 0)
					$edit_icon_edit	= "<span class=\"hasTip\" title=\"$title_edit\"><a href=\"$url_edit\" ><img src=\"$url_icon_edit\" alt=\"$title_edit\" /></a></span>";

				if (JFactory::getUser()->authorise('user.config', 'com_myjspace') && $user_page->blockEdit != 2)
					$edit_icon_config	= "<span class=\"hasTip\" title=\"$title_config\"><a href=\"$url_config\" ><img src=\"$url_icon_config\" alt=\"$title_config\" /></a></span>";

				if ($edit_icon_edit != '' || $edit_icon_config != '')
					$edit_icon = "
					<ul class=\"actions\">
						<li class=\"edit-iconX\">
							$edit_icon_edit
							$edit_icon_config
						</li>
					</ul>
					";
			} else { // >= J!3.0
				if (JFactory::getUser()->authorise('user.edit', 'com_myjspace') && $user_page->blockEdit == 0)
					$edit_icon_edit = "<li class=\"edit-icon\"><a href=\"$url_edit\" ><span class=\"hasTip icon-edit tip\" title=\"$title_edit\"></span>&#160;$title_edit&#160;</a></li>";

				if (JFactory::getUser()->authorise('user.config', 'com_myjspace') && $user_page->blockEdit != 2)
					$edit_icon_config = "<li class=\"edit-options\"><a href=\"$url_config\" ><span class=\"hasTip icon-options tip\" title=\"$title_config\"></span>&#160;$title_config&#160;</a></li>";

				if ($edit_icon_edit != '' || $edit_icon_config != '')
					$edit_icon = "			
					<div class=\"btn-group pull-right\">
						<a class=\"btn dropdown-toggle\" data-toggle=\"dropdown\" href=\"#\"> <span class=\"icon-cog\"></span> <span class=\"caret\"></span> </a>
							<ul class=\"dropdown-menu actions\">
								$edit_icon_edit
								$edit_icon_config
							</ul>
					</div>
					";
			}
		}

		// Var assign
		$this->assignRef('allow_plugin', $allow_plugin);			
		$this->assignRef('contenu', $contenu);			
		$this->assignRef('css_background', $css_background);
		$this->assignRef('show_tags', $show_tags);
		$this->assignRef('edit_icon', $edit_icon);

		parent::display($tpl);
	}

}
?>
