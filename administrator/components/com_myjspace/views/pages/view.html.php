<?php
/**
* @version $Id: view.html.php $
* @version		2.3.5 15/05/2014
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

class MyjspaceViewPages extends JViewLegacy
{
	function display($tpl = null)
	{
		require_once JPATH_COMPONENT_SITE.DS.'helpers'.DS.'user.php';
		require_once JPATH_COMPONENT_SITE.DS.'helpers'.DS.'util_acl.php';
	
		JToolBarHelper::title(JText::_('COM_MYJSPACE_HOME').JText::_('COM_MYJSPACE_2POINTS').JText::_('COM_MYJSPACE_PAGES'), 'user.png');
		JToolBarHelper::addNew();
		JToolBarHelper::editList();
		JToolBarHelper::deleteList();
		JToolBarHelper::divider();

		$db	= JFactory::getDBO();
		$app = JFactory::getApplication();
		$option = JRequest::getCmd('option');
		$association = JRequest::getVar('association', '');

		$pparams = JComponentHelper::getParams('com_myjspace');
		$share_page = $pparams->get('share_page', 0);
		
		// Language
		$language_filter = $pparams->get('language_filter', 0);
		if (version_compare(JVERSION, '3.0.3', 'lt'))
			$language_filter = min($language_filter, 1);

		if (version_compare(JVERSION, '1.6.0', 'ge'))
			$languages = JLanguageHelper::getLanguages('lang_code'); // languages list
		else
			$languages = null;

		$filter_order = $app->getUserStateFromRequest("$option.filter_order", 'filter_order', 'a.title', 'cmd');
		$filter_order_Dir = $app->getUserStateFromRequest("$option.filter_order_Dir", 'filter_order_Dir', '', 'word');
		
		$filter_type = $app->getUserStateFromRequest("$option.filter_type", 'filter_type', 0, 'int');
		$filter_logged = $app->getUserStateFromRequest("$option.filter_logged", 'filter_logged', -1, 'int');
		$search	= $app->getUserStateFromRequest("$option.search", 'search', '', 'string');
		if (strpos($search, '"') !== false)
			$search = str_replace(array('=', '<'), '', $search);
		$search = JString::strtolower($search);

		$limit = $app->getUserStateFromRequest('global.list.limit', 'limit', $app->getCfg('list_limit'), 'int');
		$limitstart = $app->getUserStateFromRequest($option.'.limitstart', 'limitstart', 0, 'int');

		$where = array();
		if (isset($search) && $search != '') {
			if (version_compare(JVERSION, '1.6.0', 'ge'))
				$searchEscaped = $db->Quote('%'.$db->escape($search, true).'%', false);
			else
				$searchEscaped = $db->Quote('%'.$db->getEscaped($search, true).'%', false);
			$where[] = 'a.title LIKE '.$searchEscaped.' OR b.username LIKE '.$searchEscaped;
		}
		
		// Filters
		if (isset($filter_type) && $filter_type != 0)
			$where[] = ' a.blockEdit = '.($filter_type-1).' ';
		
		if (isset($filter_logged) && $filter_logged > -1)
			$where[] = ' a.blockView = '.$filter_logged.' ';

		if ($association)
			$where[] = ' a.language = '.$db->Quote($association);
			
		$where = (count($where) ? ' WHERE ('.implode(') AND (', $where).')' : '');

		$query = 'SELECT COUNT(a.id)'
			. ' FROM `#__myjspace` AS a LEFT JOIN `#__users` b ON a.userid=b.id'
		. $where
		;
		$db->setQuery($query);
		$total = $db->loadResult();

		jimport('joomla.html.pagination');
		$pagination = new JPagination($total, $limitstart, $limit);
		
		$orderby = ' ORDER BY '.$filter_order.' '.$filter_order_Dir;

		if ($language_filter == 2) {
			$select_association = ', COUNT(asso2.id)>1 as association';
			$join_association = ' LEFT JOIN `#__associations` AS asso ON asso.id = a.id AND asso.context="com_myjspace.item" LEFT JOIN `#__associations` AS asso2 ON asso2.key = asso.key';
			$group_association = ' GROUP BY a.id';
		} else {
			$select_association = '';
			$join_association = '';
			$group_association = '';
		}

		$query = 'SELECT a.id, a.userid, a.title, a.blockEdit, a.blockView, b.username, b.block, a.hits, a.create_date, a.access, a.language, LENGTH(content) AS size'
			. $select_association
			. ' FROM `#__myjspace` AS a LEFT JOIN `#__users` b ON a.userid=b.id'
			. $join_association
			. $where
			. $group_association
			. $orderby
		;

		$db->setQuery($query, $pagination->limitstart, $pagination->limit);
		$rows = $db->loadObjectList();

		// Get list of Log Status for dropdown filter
		$types[] = JHTML::_('select.option', 0, '- '.JText::_('COM_MYJSPACE_TITLEMODEEDIT').' -');
		$types[] = JHTML::_('select.option', 1, JText::_('COM_MYJSPACE_TITLEMODEEDIT0'));
		$types[] = JHTML::_('select.option', 2, JText::_('COM_MYJSPACE_TITLEMODEEDIT1'));
		$lists['type'] = JHTML::_('select.genericlist', $types, 'filter_type', 'class="inputbox" size="1" onchange="document.adminForm.submit();"', 'value', 'text', "$filter_type");

		// Get list of Log Status for dropdown filter
		$logged[] = JHTML::_('select.option', -1, '- '.JText::_('COM_MYJSPACE_TITLEMODEVIEW').' -');
		$group_list = get_assetgroup_list();
		for ($i = 0 ; $i < count($group_list) ; $i++) {
			$logged[] = JHTML::_('select.option', $group_list[$i]->value, $group_list[$i]->text);
		}
		$lists['logged'] = JHTML::_('select.genericlist', $logged, 'filter_logged', 'class="inputbox" size="1" onchange="document.adminForm.submit();"', 'value', 'text', "$filter_logged");

		// Table ordering
		$lists['order_Dir']	= $filter_order_Dir;
		$lists['order']	= $filter_order;

		// Search filter
		$lists['search']= $search;

		// Assign
		$this->assignRef('lists', $lists);
		$this->assignRef('items', $rows);
		$this->assignRef('pagination', $pagination);
		$this->assignRef('share_page', $share_page);
		$this->assignRef('language_filter', $language_filter);
		$this->assignRef('association', $association);
		$this->assignRef('languages', $languages);

		parent::display($tpl);
	}
}

?>
