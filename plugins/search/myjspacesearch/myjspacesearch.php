<?php
/**
* @version $Id: myspacesearch.php $
* @version		2.3.3 20/03/2014
* @package		plg_search_myjspace
* @author       Bernard Saulmé
* @copyright	Copyright (C) 2010-2011-2012-2013-2014 Bernard Saulmé
* @license      GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
*/
defined('_JEXEC') or die;
if (!defined('DS')) define('DS', DIRECTORY_SEPARATOR);

jimport('joomla.plugin.plugin');
jimport('joomla.html.parameter');

class plgSearchMyjspacesearch extends JPlugin
{
	// Add language
	public function __construct(& $subject, $config)
	{
		parent::__construct($subject, $config);
		$this->loadLanguage('plg_search_myjspacesearch', JPATH_ADMINISTRATOR);
	}

	// Search function J!1.5
	function onSearch($text, $phrase = '', $ordering = '', $areas = null) {
		return($this->onContentSearch($text, $phrase, $ordering, $areas));
	}

	// Search Areas function J!1.5
	function onSearchAreas() {
		return($this->onContentSearchAreas());
	}

	// Function to return an array of search areas.
	function onContentSearchAreas() {
		static $areas = array();

		// BS Myjspace component ACL
		if (version_compare(JVERSION, '1.6.0', 'ge') && ($this->params->get('use_com_acl', 0) && !JFactory::getUser()->authorise('user.search', 'com_myjspace'))) 
			return array();
		
		if (empty($areas)) {
			$areas['myjspace'] = JText::_('PLG_MYJSPACESEARCH_PAGE');
		}
		return $areas;
	}

	// Search function
	function onContentSearch($text, $phrase = '', $ordering = '', $areas = null) {
		
		$db = JFactory::getDBO();
		$user_actual = JFactory::getuser();
		$pparams = JComponentHelper::getParams('com_myjspace');

		// BS MyJspace component not installed
		if (!file_exists(JPATH_ROOT.DS.'components'.DS.'com_myjspace'.DS.'myjspace.php'))
			return array();
		
		// BS Myjspace component ACL
		if (version_compare(JVERSION, '1.6.0', 'ge') && ($this->params->get('use_com_acl', 0) && !JFactory::getUser()->authorise('user.search', 'com_myjspace'))) 
			return array();

		require_once JPATH_ROOT.DS.'components'.DS.'com_myjspace'.DS.'helpers'.DS.'util.php';

		// If the array is not correct, return it:
		if (is_array($areas)) {
			if (!array_intersect($areas, array_keys($this->onContentSearchAreas()))) {
				return array();
			}
		}

		// Define the parameters
		$limit = $this->params->get('search_limit', 50);
		$contentLimit = $this->params->get('content_limit', 150);
		// URL display mode
		$param_url_mode = $this->params->get('param_url_mode', 0);
		$language_filter = $pparams->get('language_filter', 0); // Filter by language

		if ($language_filter > 0) { // filter by language
			$lang = JFactory::getLanguage();
			$language = $lang->getTag();
		} else
			$language = '';

		// Cleaning searching terms
		$text = trim($text);

		// Return empty array when nothing was filled in
		if ($text == '') {
			return array();
		}

		// Search for direct characters or for html equivalent for text with accent
		if ($this->params->get('search_html', 1))
			$text = htmlentities($text,ENT_QUOTES,'UTF-8');
			
		// Search
		$wheres = array();
		switch ($phrase) {

			// Search exact
			case 'exact' :
				if (version_compare(JVERSION, '1.6.0', 'ge'))
					$text = $db->Quote('%'.$db->escape($text, true).'%', false);
				else
					$text = $db->Quote('%'.$db->getEscaped($text, true).'%', false);
				$wheres2 = array();
				$wheres2 [] = '`title` LIKE ' . $text . ' OR `content` LIKE ' . $text;
				$where = '(' . implode(') OR (', $wheres2) . ')';
				break;

			// Search all or any
			case 'all' :
			case 'any' :

			// Set default
			default :
				$words = explode(' ', $text);
				$wheres = array();
				foreach ($words as $word) {
					if (version_compare(JVERSION, '1.6.0', 'ge'))
						$word = $db->Quote('%'.$db->escape($word, true).'%', false);
					else
						$word = $db->Quote('%'.$db->getEscaped($word, true).'%', false);
					$wheres2 = array();
					$wheres2 [] = '`title` LIKE ' . $word . ' OR `content` LIKE ' . $word;
					$wheres [] = implode(' OR ', $wheres2);
				}
				$where = '(' . implode(($phrase == 'all' ? ') AND (' : ') OR ('), $wheres) . ')';
				break;
		}

		if ($language)
			$where .= " AND `language` IN ('*',".$db->Quote($language).")";

		// Ordering of the results
		switch ($ordering) {

			// Oldest first
			case 'oldest' :
				$order = '`create_date` ASC';
				break;

			// Popular first
			case 'popular' :
				$order = '`hits` ASC, `create_date` DESC';
				break;

			// Newest first
			case 'newest' :
				$order = '`create_date` DESC';
				break;

			// Alphabetic, ascending
			case 'alpha' :
			// Default setting: hit, create_date descending
			default :
				$order = '`hits` ASC, `create_date` DESC';
		}

		$query = "SELECT title, pagename, `content` AS text, `create_date` AS created, blockView FROM `#__myjspace` WHERE `blockView` != 0 AND `content` != '' AND
				`publish_up` < NOW() AND (`publish_down` >= NOW() OR `publish_down` = '0000-00-00 00:00:00')
				AND {$where} ORDER BY {$order}";

		// Query
		$db->setQuery($query, 0, $limit);
		$rows = $db->loadObjectList();

		// Search for folder
		if ($param_url_mode == 1) {
			$repertoire = $pparams->get('foldername', 'myjsp') . '/';
			$id_itemid = '?Itemid=';
		} else {
			$repertoire = 'index.php?option=com_myjspace&view=see&pagename=';
			$id_itemid = '&Itemid=';
		}
		
		// Itemid
		$itemid = $this->params->get('forced_itemid', '');
		if ($itemid == '') {
			if (($itemid = JRequest::getInt('Itemid', 0)) == 0) { // If not into the parameter
				$itemid = JSite::getMenu()->getDefault()->id; // Get the default menu value
			}
		}
		$itemid = get_menu_itemid('index.php?option=com_myjspace&view=see', $itemid);

		// If ACL filter for display this page
		$user_mode_view_acl = $pparams->get('user_mode_view_acl', 0);
		if (version_compare(JVERSION, '1.6.0', 'ge') && $user_mode_view_acl == 1)
			$access = $user_actual->getAuthorisedViewLevels();
		else
			$access = array();		

		foreach ($rows as $key => $row) {

			$rows[$key]->section = JText::_('PLG_MYJSPACESEARCH_PAGE');	
			$rows[$key]->href = Jroute::_($repertoire . $row->pagename . $id_itemid. $itemid);
			$rows[$key]->browsernav = '2';

			if ($row->blockView >= 2 && (($user_mode_view_acl == 0 && $user_actual->id <= 0) || ($user_mode_view_acl == 1 && !in_array($row->blockView, $access)))) {
				$rows[$key]->text = JText::_('PLG_MYJSPACESEARCH_PAGE_NOREACHABLE');
			} else {
				$rows[$key]->text = clean_html_text($row->text, $contentLimit, $user_actual->id);
			}

		}

		// Return the search results in an array	
		return $rows;
	}

}

?>
