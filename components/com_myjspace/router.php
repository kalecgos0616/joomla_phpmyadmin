<?php
/**
* @version $Id: router.php $
* @version		2.3.4 15/04/2014
* @package		com_myjspace
* @author       Bernard Saulmé
* @copyright	Copyright (C) 2010-2011-2012-2013-2014 Bernard Saulmé
* @license      GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die;
if (!defined('DS')) define('DS', DIRECTORY_SEPARATOR);

/**
 * @param	array	A named array
 * @return	array
 */
function MyjspaceBuildRoute(&$query = null)
{
	$segments = array();

	if (isset($query['view'])) {
		$view = $query['view'];
		if ($view != 'see') {
			$segments[] = $view;
		} else { // 'see' & valid itemid for the view
			$itemid = (isset($query['Itemid'])) ? $query['Itemid'] : 0;
			require_once JPATH_ROOT.DS.'components'.DS.'com_myjspace'.DS.'helpers'.DS.'util.php';
			$itemid = get_menu_itemid('index.php?option=com_myjspace&view=see', $itemid, 0, false); // valid itemid for the view

			if ($itemid == 0)
				return $segments; // We need only valid itemid for this view

			$query['Itemid'] = $itemid;
		}
		unset($query['view']);
	} else
		return $segments; // We need a view

	if (isset($query['pagename'])) {
		$segments[] = $query['pagename'];
		unset($query['pagename']);
		unset($query['id']);
	} else if (isset($query['id'])) {
		$segments[] = $query['id'];
		unset($query['id']);
	}

	if (isset($query['uid'])) {
		$segments[] = $query['uid'];
		unset($query['uid']);
	}

	return $segments;
}

/**
 * @param	array	A named array
 * @param	array
 *
 * Formats:
 *
 * index.php?/menualias/alias
 * index.php?/menualias/pages/uid
 * index.php?/menualias/id
 * index.php?/menualias/pagename
 */
function MyjspaceParseRoute($segments = null)
{
	$vars = array();

	//Get the active menu item.
	$app = JFactory::getApplication();
	$menu = $app->getMenu();
	$item = $menu->getActive();

	$count = count($segments);

	// Standard routing for pages. If we don't pick up an Itemid then we get the view from the segments
	// the first segment is the view and the last segment is the id of the page or pagename.
	if (!isset($item)) {
		$vars['view'] = $segments[0];
		$segment = $segments[$count - 1];
		if (is_numeric($segment))
			$vars['id'] = $segment;
		else
			$vars['pagename'] = $segment;

		return $vars;
	} else {
		if ($segments[0] == 'search') {
			$vars['view'] = 'search';
			return $vars;
		}

		if ($count && $segments[0] == 'pages') { // View pages
			$vars['view'] = 'pages';
			$count--;
			$segment = array_shift($segments);

			if (is_numeric($segment))
				$vars['uid'] = $segment;
		}

		if ($count == 1 && $segments[0] == 'see') {
			$count--;
			$vars['view'] = 'see';
			$segment = array_shift($segments);
			if (is_numeric($segment))
				$vars['id'] = $segment;
			else
				$vars['pagename'] = $segment;
		}

		if ($count > 1) {
			$count--;
			$segment = array_shift($segments);
			$vars['view'] = $segment;
		}

		if ($count) { // For call view, url via page view for example
			$count--;
			if (!array_key_exists('view', $vars))
				$vars['view'] = 'see';
			$segment = array_shift($segments);
			if (is_numeric($segment))
				$vars['id'] = $segment;
			else
				$vars['pagename'] = $segment;
		}
	}

	return $vars;
}

?>
