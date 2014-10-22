<?php
/**
* @version $Id: pagebreakmyjspace.php (based on pagebreak.php) $
* @version		2.4.2 26/08/2014
* @package		plg_pagebreakmyjspace
* @author       Bernard Saulmé
* @copyright	Copyright (C) 2010-2011-2012-2013-2014 Bernard Saulmé
* @license      GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die;

jimport('joomla.html.parameter'); // J!1.6+
if (version_compare(JVERSION, '2.5.0', 'ge')) { // J!2.5.18+ J!3.2+
	jimport('joomla.html.pagination');
	jimport('joomla.html.html.sliders');
	jimport('joomla.html.html.tabs');
}
jimport('joomla.utilities.utility'); // J!2.5+

/**
* Page break plugin
*
* <b>Usage:</b>
* <code><hr class="system-pagebreak" /></code>
* <code><hr class="system-pagebreak" title="The page title" /></code>
* or
* <code><hr class="system-pagebreak" alt="The first page" /></code>
* or
* <code><hr class="system-pagebreak" title="The page title" alt="The first page" /></code>
* or
* <code><hr class="system-pagebreak" alt="The first page" title="The page title" /></code>
*
*/

class plgContentPagebreakMyjspace extends JPlugin
{
	/**
	 * Constructor
	 *
	 * @access      protected
	 * @param       object  $subject The object to observe
	 * @param       array   $config  An array that holds the plugin configuration
	 * @since       
	 */
	public function __construct(& $subject, $config)
	{
		parent::__construct($subject, $config);
		$this->loadLanguage('plg_content_pagebreakmyjspace', JPATH_ADMINISTRATOR);
	}

	/**
	 * @param	string	The context of the content being passed to the plugin. (since 1.6 only)
	 * @param	object	The article object.  Note $article->text is also available
	 * @param	object	The article params
	 * @param	int		The 'page' number
	 *
	 * @return	void
	 * @since	
	 */
	 
	public function onBeforeDisplayContent(&$row, &$params, $page = 0) // J!1.5
	{
		if (version_compare(JVERSION, '1.6.0', 'ge'))
			return;

		$context = JRequest::getCmd('option').'.'.JRequest::getCmd('view');
		$index_range = $this->params->get('index_range_flexicontent', 0);

		if (JRequest::getVar("isflexicontent") == 'yes' && $context == 'com_content.article' && $index_range == 32) { // option=com_flexicontent&view=item || option=com_content&view=item (rewriten)
			$context = 'com_flexicontent.article';
			$this->plgContentPagebreakMyjspace_fct($context, $row, $params, $page, $index_range);
		}
	}

	public function onContentBeforeDisplay($context, &$row, &$params, $page = 0) // J!1.6+
	{
		if (version_compare(JVERSION, '1.6.0', 'lt'))
			return;

		$index_range = $this->params->get('index_range_flexicontent', 0);

		if ($context == 'com_k2.latest') // to make it work like 'itemlit'
			$context = 'com_k2.itemlist';
		else if (JRequest::getVar("isflexicontent") == 'yes' && $context == 'com_content.article' && $index_range == 32) { // option=com_flexicontent&view=item || option=com_content&view=item (rewriten)
			$context = 'com_flexicontent.article';
			$this->plgContentPagebreakMyjspace_fct($context, $row, $params, $page, $index_range);
		}
	}

	public function onContentPrepare($context, &$row, &$params, $page = 0) // J!1.6+
	{
		if (version_compare(JVERSION, '1.6.0', 'lt'))
			return;

		// Get Plugin info
		$index_range = $this->params->get('index_range_myjspace', 1) + $this->params->get('index_range_content', 0) + $this->params->get('index_range_k2itemlist', 0) + $this->params->get('index_range_k2_item', 0) + $this->params->get('index_range_zoo_item', 0);

		if ($context == 'com_k2.latest') // to make it work like 'itemlit'
			$context = 'com_k2.itemlist';

		if ((($index_range & 1) && $context == 'com_content.myjspace') || (($index_range & 2) && $context == 'com_content.article') || (($index_range & 4) && $context == 'com_k2.itemlist') || (($index_range & 8) && $context == 'com_k2.item') || (($index_range & 16) && $context == 'com_zoo.element.textarea'))
			$this->plgContentPagebreakMyjspace_fct($context, $row, $params, $page, $index_range);
	}

	public function onPrepareContent(&$row, &$params, $page = 0) // J!1.5
	{
		if (version_compare(JVERSION, '1.6.0', 'ge'))
			return;

		$index_range = $this->params->get('index_range_myjspace', 1) + $this->params->get('index_range_content', 0) + $this->params->get('index_range_k2itemlist', 0) + $this->params->get('index_range_k2_item', 0) + $this->params->get('index_range_zoo_item', 0);

		$context = JRequest::getCmd('option').'.'.JRequest::getCmd('view');

		if ($context == 'com_myjspace.see')
			$context = 'com_content.myjspace';
		else if ($context == 'com_zoo.item')
			$context = 'com_zoo.element.textarea';

		if ((($index_range & 1) && $context == 'com_content.myjspace') || (($index_range & 2) && $context == 'com_content.article') || (($index_range & 4) && $context == 'com_k2.itemlist') || (($index_range & 8) && $context == 'com_k2.item') || (($index_range & 16) && $context == 'com_zoo.element.textarea'))
			$this->plgContentPagebreakMyjspace_fct($context, $row, $params, $page, $index_range);
	}
	
	protected function plgContentPagebreakMyjspace_fct($context = null, &$row, &$params, $page = 0, $index_range = 1)
	{
		$document = JFactory::getDocument();
		$config = new JConfig();

		// Expression to search for
		$regex = '#<hr([^>]*?)class=(\"|\')system-pagebreak(\"|\')([^>]*?)\/*>#iU';
		$regex_img = '#<img([^>]*?)>#iU';

		// Get Plugin/component param
		$print = JRequest::getBool('print');
		$view = JRequest::getCmd('view');
		$showall = JRequest::getVar('showall');
		$page = JRequest::getVar('limitstart');
		$idmyjsp = JRequest::getVar('idmyjsp', 0);
		$index_style = $this->params->get('index_style', 0);

		// Double check some options :)
		if ($showall == '' && $page == '') {
			$showall = $this->params->get('default_showall', 0);
			$page = 0;
		} else if ($showall == 1 && $page == '') {
			$page = 0;
		} else {
			$showall = 0;
		}

		if ($page < 0) // Workaround (page 0 => -1) to compatible with AseSEF (no difference between '' & 0 else)
			$page = 0;

		if (!$this->params->get('enabled', 1))
			$print = true;

		if ($context == 'com_k2.itemlist') {
			$row->pagebreak_text = $row->text;
			$row->text = $this->k2_text($row->id); // Temporary replace the text
			$showall = 0;
			$print = false;
		}

		// Simple performance check to determine whether bot should process further (if no page break = nothing to do for the break!)
		if (!$row->text)
			return true;
		if ((strpos($row->text, 'class="system-pagebreak') === false && strpos($row->text, 'class=\'system-pagebreak') === false))
			$found_break = false;
		else
			$found_break = true;

		// Find all instances of plugin and put in $matches
		$matches = array();
		preg_match_all($regex, $row->text, $matches, PREG_SET_ORDER);
		
		// Browser page title & HTML description with suffix if pagebreak title (Not for front-page or Zoo)
		if ((!$this->plgIsFronPage() && $context != 'com_zoo.element.textarea' && $found_break == false && $this->params->get('add_title_single', 0)) || (!$this->plgIsFronPage() && $context != 'com_zoo.element.textarea' && $this->params->get('title_suffix', 1) && version_compare(JVERSION, '1.6.0', 'ge'))) { // For J!1.6+ only
			if (@$matches[$page-1][2]) {
				$attrs = JUtility::parseAttributes($matches[$page-1][0]);
				if (isset($attrs['title'])) {
					$titre_suffix = $attrs['title'];
				} else {
					$titre_suffix = JText::sprintf('PLG_CONTENT_PAGEBREAKMYJSPACE_PAGE_NUM', ($page + 1));
				}

				if (isset($row->params)) {
					$params = new JRegistry();
					$params->loadString($row->params);
				} else {
					$params = null;
				}
			} else if ($showall) { // First page or showall or no pagebreak
				$titre_suffix = JText::_('PLG_CONTENT_PAGEBREAKMYJSPACE_ALL_PAGES');
			} else {
				$titre_suffix = null;
			}

			if (isset($row->page_title)) // If page as browser title
				$titre_page = $row->page_title;
			else if (isset($row->title)) // Use content title
				$titre_page = $row->title;
			else
				$titre_page = '';

			if ($titre_page && ($context == 'com_content.myjspace' || $context == 'com_content.article')) { // Works only for J! Article content or BS MyJspace page
				if (isset($params) && $params)
					$params->set('page_title', JText::sprintf('PLG_CONTENT_PAGEBREAKMYJSPACE_PAGETITLE', $titre_page, $titre_suffix));
				else
					$row->page_title = JText::sprintf('PLG_CONTENT_PAGEBREAKMYJSPACE_PAGETITLE', $titre_page, $titre_suffix);
			}

			// HTML description, if exists, suffixed
			if (isset($row->metadesc) && $row->metadesc != '')
				$row->metadesc = JText::sprintf('PLG_CONTENT_PAGEBREAKMYJSPACE_PAGETITLE', $row->metadesc, $titre_suffix);
		}

		if ($found_break == false) { // No need to do more :) !
			if ($context == 'com_k2.itemlist')
				$row->text = $row->pagebreak_text;

			if ($this->params->get('add_canonical_single', 0) == 1) // Add canonical
				$this->add_canonical($document, $context, $row, $config, $showall);

			return true;
		}

		if ($print) {
			$row->text = preg_replace($regex, '<br />', $row->text);
			return true;
		}

		$item_text = 0;
		if ($context == 'com_zoo.element.textarea') { // BS ZOO
			$item_text = crc32($row->text);
			if ($idmyjsp != 0 && $item_text != $idmyjsp) {
				$page = 0;
				if ($this->params->get('default_showall', 0) != 1)
					$showall = 0;
			}
		}

		// BS CSS for template with no pagebreak style, same a default Joomla template
		if ($this->params->get('use_css', 1)) {
			if (version_compare(JVERSION, '1.6.0', 'ge'))
				$document->addStyleSheet('plugins/content/pagebreakmyjspace/assets/css/pagebreakmyjspace.min.css');
			else
				$document->addStyleSheet('plugins/content/assets/css/pagebreakmyjspace.min.css');
		}

		// Title for the first page with a pagebreak if no text before
		$title_page1 = '';
		if ($this->params->get('usefirstpagebreak', 1)) {
			$pattern_page1 = '#^<hr ([^>]*?)class=(\"|\')system-pagebreak(\"|\')#';
			if ($context == 'com_content.myjspace')
				$pattern_page1 = '#^<div class="myjspace-prefix"></div><div class="myjspace-content"></div><hr ([^>]*?)class=(\"|\')system-pagebreak(\"|\')#';

			if (preg_match($pattern_page1, $row->text)) { // Text start with pagebreak for title
				$row->text = preg_replace($regex, '', $row->text, 1);
				$match = (array) JUtility::parseAttributes($matches[0][0]);
				if (isset($match['alt'])) {
					$title_page1 = stripslashes($match['alt']);
				} elseif (isset($match['title'])) {
					$title_page1 = stripslashes($match['title']);
				}
				unset($matches[0]);
			}
		}

		$del_img_n = intval($this->params->get('del_img_n', 0));
		if ($page >= $del_img_n && $del_img_n != 0)
			$row->text = preg_replace($regex_img, '', $row->text);

		if ($showall && $this->params->get('showall', 1)) { // Show all
			$this->add_canonical($document, $context, $row, $config, 1);

			$hasToc = ($context == 'com_k2.itemlist' || $context == 'com_k2.item') ? 1 : $this->params->get('multipage_toc', 1); // If k2 => only index = no J! pagination
			if ($hasToc) {
				// Display TOC
				$page = 1;
				$this->plgContentCreateTOC($params, $row, $matches, $page, $index_range, $item_text, $context, $title_page1, $showall);
			} else {
				$row->toc = '';
			}
			$row->text = preg_replace($regex, '<br/>', $row->text);

			// K2 item 'TOC' display emulation
			if ($context == 'com_k2.item') {
				if (!(isset($row->metadesc) && $row->metadesc != ''))
					$row->metadesc = $this->k2_metadesc($row);

				if (strstr($row->text, '{K2Splitter}')) { // Introtext may be due to <hr id="system-readmore" /> for example
					@list($introtext, $fulltext) = explode('{K2Splitter}', $row->text, 2);
					if (strlen($fulltext) != 0)
						$row->text = $row->toc.'<div class="itemIntroText">'.$introtext.'</div>'.$fulltext;
					else
						$row->text = $row->toc.$row->text;
				} else
					$row->text = $row->toc.$row->text;
			}
			// ZOO 'TOC' display emulation
			if ($context == 'com_zoo.element.textarea') {
				$row->text = $row->toc.$row->text;
			}

			return true;
		}

		// split the text around the plugin
		$text = preg_split($regex, $row->text);

		// count the number of pages
		$n = count($text);

		$row->pagebreaktitle = (isset($row->title)) ? $row->title : '';
		// we have found at least one plugin, therefore at least 2 pages
		if ($n > 1) {
			// Get plugin parameters
			$hasToc = ($context == 'com_k2.itemlist' || $context == 'com_k2.item') ? 1 : $this->params->get('multipage_toc', 1); // If k2 => only index = no J! pagination

			// reset the text, we already hold it in the $text array
			$row->text = '';

			// Canonical
			$this->add_canonical($document, $context, $row, $config, 1);

			if (is_numeric($index_style)) {

				// display TOC
				if ($hasToc) {
					$this->plgContentCreateTOC($params, $row, $matches, $page, $index_range, $item_text, $context, $title_page1, $showall);
				} else {
					$row->toc = '';
				}

				$pageNav = new JPagination($n, $page, 1);

				// page counter
				$pagenavcounter = $this->params->get('pagenavcounter', 'hide');
				if ($pagenavcounter != 'hide' && $pagenavcounter != 'arrows' && $pagenavcounter != '0') { // test 0 for previous version
					$row->text .= '<div class="pagenavcounter myjsp-counter" style="float:'.$pagenavcounter.'" >';
					$row->text .= $pageNav->getPagesCounter();
					$row->text .= '</div>';
				}

				// page text
				if (isset($text[$page])) { // BS
					$text[$page] = str_replace("<hr id=\"\"system-readmore\"\" />", "", $text[$page]);
					$row->text .= $text[$page];
				}

				if ($index_style != 3 && $index_style != 4) {
					if ($this->params->get('class_pagination', '') == '') {
						if (version_compare(JVERSION, '3.0.0', 'ge')) // Style J! 3.0 ...
							$row->text .= '<div class="pager">';
						else
							$row->text .= '<div class="pagination">'; // J1.5 & 2.5
					} else {
						$row->text .= '<div class="'.$this->params->get('class_pagination', '').'">';
					}
					$row->text .= '<br />';
				}

				// add navigation between pages to bottom of text
				if ($hasToc && $context != 'com_k2.itemlist') {				
					if ($index_style == 3 || $index_style == 4) {
						$toc_tmp = $row->toc;
						$row->toc = '';
					} else
						$toc_tmp = '';

					$row->text .= $this->plgContentCreateNavigation($row, $page, $n, $item_text, $context, $toc_tmp);				
				}

				// page links shown at bottom of page if TOC disabled
				if (!$hasToc) {
					$row->text .= $pageNav->getPagesLinks();
				}

				if ($index_style != 3 && $index_style != 4) {
					$row->text .= '</div><br />';
				}

				// K2 'TOC' display emulation
				// For $context == 'com_k2.itemlist' only show the first page ... not very useful option
				if ($context == 'com_k2.item') {
					if (!(isset($row->metadesc) && $row->metadesc != ''))
						$row->metadesc = $this->k2_metadesc($row);

					if ($this->params->get('hide_K2_itemimage', 1) == 1 && $page > 0) // Not into plugin parameters yet (avoid to much parameters ..)
						$document->addStyleDeclaration('.itemImageBlock {display:none;}');

					if (strstr($row->text, '{K2Splitter}')) {// Introtext may due to <hr id="system-readmore" /> for example
						if (($page+1) == $n) { // last page
							$row->text = $row->toc.str_replace('{K2Splitter}', '', $row->text);
						} else { // for the first page
							@list($introtext, $fulltext) = explode('{K2Splitter}', $row->text);
							$row->text = $row->toc.'<div class="itemIntroText">'.$introtext.'</div>'.$fulltext;
						}
					} else
						$row->text = $row->toc.$row->text;
				}

				if ($context == 'com_k2.itemlist') {
					$row->text = $row->toc.$row->pagebreak_text;

					$text = preg_split($regex, $row->text); // Only one page is some pagebreak if no introtext
					if (isset($text[0]))
						$row->text = $text[0];
					else
						$row->text = '';
				}

				// ZOO 'TOC' display emulation
				if ($context == 'com_zoo.element.textarea') {
					$row->text = $row->toc.$row->text;
				}

				if ($index_style == 4) { // Case double drop-down. Have distinct id
					$toc_tmp = str_replace('mjsp-menu-select', 'mjsp-menu-select1', $toc_tmp);
					$row->text = $this->plgContentCreateNavigation($row, $page, $n, $item_text, $context, $toc_tmp, 'myjsp-prev-next1').$row->text;
				}

			} else { // Sliders or tabs
				$t[] = $text[0];
				if (version_compare(JVERSION, '3.2.0', 'ge'))
					$t[] = (string) JHtml::_($index_style.'.start', 'article'.$row->id.'-'.$index_style);
				else
					$t[] = (string) JHtml::_($index_style.'.start');

				foreach ($text as $key => $subtext) {
					if ($key >= 1) {
						$match = $matches[$key - 1];
						$match = (array) JUtility::parseAttributes($match[0]);
						if (isset($match['alt'])) {
							$title = stripslashes($match['alt']);
						} elseif (isset($match['title'])) {
							$title = stripslashes($match['title']);
						} else {
							if ($key == 1) {
								if ($this->params->get('article_first_url', 2) == 1)
									$title = $this->params->get('article_first_url_text', '');		
								else
									$title = isset($row->title) ? $row->title : $this->params->get('article_first_url_text', '');
							} else
								$title = JText::sprintf('PLG_CONTENT_PAGEBREAKMYJSPACE_PAGE_NUM', $key+1);
						}
						if (version_compare(JVERSION, '3.2.0', 'ge'))
							$t[] = (string) JHtml::_($index_style.'.panel', $title, 'article'.$row->id.'-'.$index_style.$key);
						else
							$t[] = (string) JHtml::_($index_style.'.panel', $title, 'basic-details'.$key);
					}
					$t[] = (string) $subtext;
				}
				$t[] = (string) JHtml::_($index_style.'.end');
				$row->text = implode(' ', $t);
			}
		}

		return true;
	}

	protected function k2_metadesc(&$row)
	{
		$metadesc = preg_replace("#{(.*?)}(.*?){/(.*?)}#s", '', $row->introtext.' '.$row->fulltext);
		$metadesc = @strip_tags(trim($metadesc));	
		$find = array("/\r|\n/", "/\t/", "/\s\s+/");
		$replace = array(" ", " ", " ");
		$metadesc = preg_replace($find, $replace, $metadesc);
		$metadesc = substr($metadesc, 0, 150);
		$metadesc = htmlentities($metadesc, ENT_QUOTES, 'utf-8');
		return $metadesc;
	}

	// Retrieve K2 text
	protected function k2_text($id = 0)
	{
	  	$db	= JFactory::getDBO();
		$query = "SELECT `fulltext`, `introtext` FROM `#__k2_items` WHERE `id` = ".$db->Quote($id);
		$db->setQuery($query);
		$row = $db->loadObject();

		$result = $row->fulltext;
		if (!$result)
			$result = $row->introtext;

		return $result;
	}

	protected function my_index($context = null , &$row = null)
	{
		$url = '';

		if ($context == 'com_k2.itemlist' || $context == 'com_k2.item') {
			return 'index.php?option=com_k2&view=item&id='.$row->id.':'.$row->alias;
		}

		if ($this->plgIsFronPage()) {
			$option = JRequest::getCmd('option');
			$view = JRequest::getCmd('view');
			$layout = JRequest::getCmd('layout');
			$id = JRequest::getCmd('id');
			$pagename = JRequest::getCmd('pagename');
			$Itemid = JRequest::getInt('Itemid');

			$url = "index.php?option=".$option;
			if ($view)
				$url .= "&view=".$view;
			if ($layout)
				$url .= "&layout=".$layout;
			if ($id)
				$url .= "&id=".$id;
			if ($pagename)
				$url .= "&pagename=".$pagename;
			if ($Itemid)
				$url .= "&Itemid=".$Itemid;
		}

		if ($context == 'com_flexicontent.article')
			$url = str_replace('option=com_content', 'option=com_flexicontent', $url);

		return $url;		
	}

	protected function plgIsFronPage()
	{
		$app = JFactory::getApplication();
		$menu = $app->getMenu();
		if ($menu->getActive() == $menu->getDefault())
			$frontend = true;
		else
			$frontend = false;

		return $frontend;
	}

	protected function plgContentCreateTOC(&$params, &$row, &$matches, &$page, $index_range, $idmyjsp = 0, $context = 'com_content.article', $title_page1 = '', $showall = 1)
	{
		// Menu Style BS
		$index_style = $this->params->get('index_style', 0);

		if ($index_range == 0 || $index_style == 99)
			return;

		$row->toc = '';

		// BS Heading
		if ($this->params->get('article_first_url', 2) == 0)
			$heading = '';
		else if ($title_page1 != '')
			$heading = $title_page1;
		else if ($this->params->get('article_first_url', 2) == 1)
			$heading = $this->params->get('article_first_url_text', '');		
		else
			$heading = isset($row->title) ? $row->title : $this->params->get('article_first_url_text', '');

		// BS Param
		$Itemid = JRequest::getInt('Itemid', 0);
		$show_firstpagenum = $this->params->get('show_firstpagenum', 0);

		// TOC header.
		if ($index_style == 0)
			$menu_id = "article-index";
		else if ($index_style == 1) {
			$menu_id = "mjsp-menu";
			$row->toc .= '
<!--[if IE 7]>
<style type="text/css">
#mjsp-menu li {
	position: static;
}
#mjsp-menu ul li ul {
	top: auto;
/*	position: static; */
 	margin: auto;
}
</style>
<![endif]-->
';
		} else { // == 2
			$menu_id = "mjsp-menu-select";

			$document = JFactory::getDocument();
			$js_content = '

function navigateTo(sel, target) {
    var url = sel.options[sel.selectedIndex].value;
    window[target].location.href = url;
}
';
			$document->addScriptDeclaration($js_content);			
		}

		// Position: inherit, left or right position
		$index_position = $this->params->get('index_position', 'inherit');
		if ($index_position != 'inherit' && $index_style != 3 && $index_style != 4) {
			$menu_style = ' style="float:'.$index_position.'"';
		} else {
			if (version_compare(JVERSION, '3.0.0', 'ge') && $this->params->get('use_css', 1) != 1)
				$menu_style = '	class="pull-right article-index"';
			else
				$menu_style = '';
		}

		if ($index_style == 3 || $index_style == 4)
			$menu_style2 = ' class="enligne"';
		else
			$menu_style2 = '';

		$row->toc .= '<div id="'.$menu_id.'"'.$menu_style.$menu_style2.">\n";

		// Index text
		$headingtext = $this->params->get('article_index_text', 'Index'); // BS
		if ($this->params->get('article_index', 1) == 0) { 
			$headingtext = '';
		} else if ($this->params->get('article_index', 1) == 2) {
			if (isset($row->category_title)) // Article | Personal Pages
				$headingtext = $row->category_title;
			if (isset($row->category) && isset($row->category->name)) // K2
				$headingtext = $row->category->name;
		}

		// TOC first Page link
		$class = ($page === 0 && $showall === 0) ? 'toclink active' : 'toclink';

		// BS
		if ($index_style == 0) {
			if ($this->params->get('article_index', 1) != 0)
				$row->toc .='<h3>'.$headingtext.'</h3>';
		} else if ($index_style == 1) {
			$row->toc .= "<ul style=\"list-style-type:none\">\n<li><div class=\"".$this->params->get('class_style1', 'readmore')."\"><p><a href=\"#\">".$headingtext."</a></p></div>\n";
		} else { // == 2
			$row->toc .= '<select onchange="navigateTo(this, \'window\');">'."\n";
		}
		if ($index_style == 0 || $index_style == 1) {
			if (version_compare(JVERSION, '3.0.0', 'ge') && $this->params->get('use_css', 1) != 1)
				$row->toc .= '<ul class="nav nav-tabs nav-stacked">'."\n";
			else
				$row->toc .= "<ul>\n";
		}

		// If == 2 no index title

		if ($idmyjsp != 0)
			$id_txt = '&idmyjsp='.$idmyjsp;
		else
			$id_txt = '';

		$url_index = $this->my_index($context, $row);
		$Itemid_str = ($Itemid > 0) ? '&Itemid='.$Itemid : '';
		// First page URL
		if ($show_firstpagenum == 0) {
			if ($context == 'com_content.article')
				$link = JRoute::_(ContentHelperRoute::getArticleRoute($row->slug, $row->catid));
			else if ($this->params->get('sh404sef_handler', 0) == 1)
				$link = JRoute::_($url_index.$Itemid_str.$id_txt.'&limit=1');
			else
				$link = JRoute::_($url_index.$Itemid_str.$id_txt);
		} else {
			if ($context == 'com_content.article')
				$link = JRoute::_(ContentHelperRoute::getArticleRoute($row->slug, $row->catid).'&limitstart=');
			else
				$link = JRoute::_($url_index.$Itemid_str.'&limitstart=-1'.$id_txt);
		}

		// url clean-up
		$link = str_replace(array('?showall=1&amp;', '?showall=1', '&amp;showall=1'), array("?", '', ''), $link);
		$link = str_replace('&amp;limitstart=', '', $link); // Delete the limitstart= just added to re-set limitstart
			
		if ($this->params->get('article_first_url', 2) != 0) {
			if ($index_style == 0 || $index_style == 1)
				$row->toc .= '<li><a href="'.$link.'" class="'.$class.'">'.$heading."</a></li>\n";
			else // == 2
				$row->toc .='<option value="'.$link.'">'.$heading.'</option>'."\n";
		}

		$i = 2;

		foreach ($matches as $bot) {
//	BS
			if ($context == 'com_content.article')
				$link = JRoute::_(ContentHelperRoute::getArticleRoute($row->slug, $row->catid).'&limitstart='.($i - 1));
			else if ($this->params->get('sh404sef_handler', 0) == 1)
				$link = JRoute::_($url_index.$Itemid_str.'&limit=1&limitstart='.($i-1)).$id_txt;
			else
				$link = JRoute::_($url_index.$Itemid_str.'&limitstart='.($i-1)).$id_txt;

			if (@$bot[0]) {
				$attrs2 = JUtility::parseAttributes($bot[0]);

				if (@$attrs2['alt']) {
					$title = stripslashes($attrs2['alt']);
				} elseif (@$attrs2['title']) {
					$title = stripslashes($attrs2['title']);
				} else {
					$title = JText::sprintf('PLG_CONTENT_PAGEBREAKMYJSPACE_PAGE_NUM', $i);
				}
			} else {
				$title = JText::sprintf('PLG_CONTENT_PAGEBREAKMYJSPACE_PAGE_NUM', $i);
			}
			$class = ($page == $i-1) ? 'toclink active' : 'toclink';

			// url clean-up
			$link = str_replace(array('?showall=1&amp;', '?showall=1', '&amp;showall=1'), array("?", '', ''), $link);

			if ($index_style == 0 || $index_style == 1)
				$row->toc .= "<li>\n".'<a href="'.$link.'" class="'.$class.'">'.$title."</a>\n</li>\n";
			else {
				if ($showall == 0 && (($i-1) == $page || ($page == 0 && $i == 0)))
					$row->toc .= '<option value="'.$link.'" selected="selected">'.$title."</option>\n";
				else
					$row->toc .= '<option value="'.$link.'">'.$title."</option>\n";
			}

			$i++;
		}

		if ($this->params->get('showall', 1) == 1 && $index_style != 3 && $index_style != 4) { // BS maj valeur par defaut et test
			if ($context == 'com_content.article')
				$link = JRoute::_(ContentHelperRoute::getArticleRoute($row->slug, $row->catid).'&showall=1&limitstart=');
			else
				$link = JRoute::_($url_index.$Itemid_str.'&showall=1&limitstart='.$id_txt);

			// url clean-up
			$link = str_replace('&amp;limitstart=', '', $link); // Delete the limitstart= just added to re-set limitstart

			$class = ($showall == 1) ? 'toclink active' : 'toclink';

			if ($index_style == 0 || $index_style == 1)
				$row->toc .= "<li>\n".'<a href="'.$link.'" class="'.$class.'">'.JText::_('PLG_CONTENT_PAGEBREAKMYJSPACE_ALL_PAGES')."</a>\n</li>\n";
			else if ($showall == 1)
				$row->toc .= '<option value="'.$link.'" selected="selected">'.JText::_('PLG_CONTENT_PAGEBREAKMYJSPACE_ALL_PAGES')."</option>\n";
			else
				$row->toc .= '<option value="'.$link.'">'.JText::_('PLG_CONTENT_PAGEBREAKMYJSPACE_ALL_PAGES')."</option>\n";
		}

		if ($index_style == 0) { // BS
			$row->toc .= "</ul></div>\n";
		} else if ($index_style == 1) {
			$row->toc .= "</ul></li></ul></div>\n";
		} else {
			$row->toc .= "</select>\n</div>\n";
		}
	}

	// Navigation Prev/Next arrows & link header for prev&next
	protected function plgContentCreateNavigation(&$row, $page, $n , $idmyjsp = 0, $context = 'com_content.article', $toc_tmp = '', $class = "myjsp-prev-next")
	{
		$document = JFactory::getDocument();
		$img_prev = $this->params->get('img_prev', '');
		$img_next = $this->params->get('img_next', '');
		$Itemid = JRequest::getInt('Itemid', 0);
		$Itemid_str = ($Itemid > 0) ? '&Itemid='.$Itemid : '';

		$arrow_position = $this->params->get('arrow_position', 'inherit');

		$url_index = $this->my_index($context, $row);

		if ($idmyjsp != 0)
			$id_txt = '&idmyjsp='.$idmyjsp;
		else
			$id_txt = '';

		$uri = JURI::getInstance();
		$racine = $uri->toString(array('scheme', 'host', 'port'));

		// Next >>
		if ($page < $n-1) {
			$page_next = $page + 1;

			if ($context == 'com_content.article')
				$link_next = JRoute::_(ContentHelperRoute::getArticleRoute($row->slug, $row->catid).'&limitstart='.($page_next));
			else if ($this->params->get('sh404sef_handler', 0) == 1)
				$link_next = JRoute::_($url_index.$Itemid_str.'&limit=1&limitstart='.($page_next).$id_txt);
			else
				$link_next = JRoute::_($url_index.$Itemid_str.'&limitstart='.($page_next).$id_txt);

			if ($img_next == '')
				$next = '<a href="'.$link_next.'">'.JText::_('JNEXT').' '.JText::_('PLG_CONTENT_PAGEBREAKMYJSPACE_PAGE_NEXT').'</a>';
			else
				$next = '<a href="'.$link_next.'"><img src="'.$img_next.'" alt="" ></a>';

			$document->addHeadLink($racine.$link_next, 'next', 'rel');
		} else {
			$class_next = ' class="disabled"';
			if ($img_next == '')
				$next = JText::_('JNEXT');
			else
				$next = '<img src="'.$img_next.'" class="opaque" alt="" >';
		}

		// << Prev
		if ($page > 0) {
			$page_prev = $page - 1 == 0 ? "" : $page - 1;

			if ($context == 'com_content.article')
				$link_prev = JRoute::_(ContentHelperRoute::getArticleRoute($row->slug, $row->catid).'&limitstart='.($page_prev));
			else if ($this->params->get('sh404sef_handler', 0) == 1)
				$link_prev = JRoute::_($url_index.$Itemid_str.'&limit=1&limitstart='.($page_prev).$id_txt);
			else
				$link_prev = JRoute::_($url_index.$Itemid_str.'&limitstart='.($page_prev).$id_txt);

			if ($img_prev == '')
				$prev = '<a href="'.$link_prev.'">'.JText::_('PLG_CONTENT_PAGEBREAKMYJSPACE_PAGE_PREV').' '.JText::_('JPREV').'</a>';
			else
				$prev = '<a href="'.$link_prev.'"><img src="'.$img_prev.'" alt="" ></a>';

			$document->addHeadLink($racine.$link_prev, 'prev', 'rel');
		} else {
			$class_prev = ' class="disabled"';
			if ($img_prev == '')
				$prev = JText::_('JPREV');
			else
				$prev = '<img src="'.$img_prev.'" class="opaque" alt="" >';
		}

		if ($arrow_position == 'none') // No arrow
			return '';
		
		if ($toc_tmp == '')
			$inter_arrow = ' ';
		else
			$inter_arrow = $toc_tmp;

		if ($this->params->get('pagenavcounter', 'hide') == 'arrows')
			$page_num = '<span class="myjsp-counter"> '.($page+1).'/'.$n.' </span>';
		else
			$page_num = '';

		$return = '<div class="'.$class.'" style="text-align:'.$arrow_position.';">'."\n".'<span class="myjsp-prev">'.$prev.'</span>'.$inter_arrow.$page_num.'<span class="myjsp-next">'.$next."</span>\n</div>";

		return $return;		
	}

	// Not for front page, zoo & flexicontent
	protected function add_canonical($document, $context, $row, $config, $showall = 1)
	{
		if ($this->plgIsFronPage() || $context == 'com_zoo.element.textarea' || $context == 'com_flexicontent.article ')
			return;

		if ($context == 'com_content.article') {
			$referer = ContentHelperRoute::getArticleRoute($row->slug, $row->catid);
			if ($showall)
				$referer .= '&showall=1';
			$current = JRoute::_($referer);
		} else {
			if ($config->sef == 0)
				$referer = JRoute::_('index.php');
			else
				$referer = JURI::current();

			// To have an unique url whatever is the menu or call !
			$app = JFactory::getApplication();
			$router = $app->getRouter();
			$u = JURI::getInstance($referer);
			$routingArray = $router->parse($u);

			/// Set correct component & view
			$routingArray['option'] = JRequest::getCmd('option');
			$routingArray['view'] = JRequest::getCmd('view');

			unset($routingArray['Itemid']); // will be re-set at the end to be sure to have it at the end
			unset($routingArray['limitstart']); // J!1.5
			if ($showall)
				$routingArray['showall'] = '1'; // To reference the full page (if pagebreak into the text);
			$routingArray['Itemid'] = $this->get_first_menu_itemid($context);

			$current = JRoute::_('index.php?'.JURI::getInstance()->buildQuery($routingArray));
			$current = str_replace(array('&Itemid=0', '?Itemid=0'), array('', ''), $current);
		}

		if (strpos($current, '.html?') > 0) { // cut extra param after .html
			$current_tab = explode('.html?', $current);
			$current = $current_tab[0].'.html';
			if ($showall)
				$current .= '?showall=1';
		}

		// Remove all already existing 'canonical' ... Mainly for J!3
		foreach ($document->_links as $k => $array) {
			if ($array['relation'] == 'canonical') {
				unset($document->_links[$k]);
			}
		}

		// Absolute url
		$uri = JURI::getInstance();
		$current = $uri->toString(array('scheme', 'host', 'port')).$current;
		$document->addHeadLink($current, 'canonical', 'rel'); // Canonical link		
	}

	// Get the first menu Itemid to have always the same one for the content from any call location
	protected function get_first_menu_itemid($context = '') {

		if ($context == 'com_content.myjspace')
			$component = 'com_myjspace';
		else {
			$component = explode('.', $context);
			$component = $component[0];
		}

		$app = JFactory::getApplication();
		$menu = $app->getMenu();

		if ($menu) {
			$menu_items = $menu->getItems('component', $component);
			if (count($menu_items) >= 1) {
				foreach ($menu_items as $i => $v) {
					$query = isset($menu_items[$i]->query) ? $menu_items[$i]->query : null;
					if ($query && isset($query['view']) && JRequest::getCmd('view') == $query['view'])
						return $menu_items[$i]->id;
				}
			}
			return 0;
		} else
			return 0;
	}	

} // End of class plgContentPagebreakMyjspace

?>
