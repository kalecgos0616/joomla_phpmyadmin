<?php
/**
* @version $Id: view.feed.php $
* @version		2.4.0 03/06/2014
* @package		com_myjspace
* @author       Bernard Saulmé
* @copyright	Copyright (C) 2010-2011-2012-2013-2014 Bernard Saulmé
* @license      GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
*/

defined('_JEXEC') or die;
if (!defined('DS')) define('DS', DIRECTORY_SEPARATOR);

class MyjspaceViewSearch extends JViewLegacy
{

	function display($tpl = null)
	{
        require_once JPATH_COMPONENT_SITE.DS.'helpers'.DS.'user.php';

		// Config
		$pparams = JComponentHelper::getParams('com_myjspace');
	  	$user = JFactory::getuser();
		$app = JFactory::getApplication();
		$document = JFactory::getDocument();
		$params = $app->getParams();

		$rss_feed = intval($params->get('rss_feed', 50));
		if ($rss_feed <= 0)
			return;

		// Param
		$aff_sort = JRequest::getInt('sort', $params->get('sort', 4)); // Sort order
		$svalue = JRequest::getVar('svalue', $params->get('svalue', '')); // Search key for search content value
		$catid = JRequest::getInt('catid', $params->get('catid', 0)); // Catid		
		$check_search = JRequest::getVar('check_search', $params->get('check_search', array('name', 'content', 'description')));
		foreach ($check_search as $i => $value) {
			$check_search_asso[$value] = '1';
		}

		// Language
		$language_filter = $pparams->get('language_filter', 0);

		if ($language_filter > 0) { // Filter by language
			$lang = JFactory::getLanguage();
			$language = $lang->getTag();
		} else
			$language = '';

		// Categories
		$categories = BSHelperUser::GetCategories(1);
		$categories_label = BSHelperUser::GetCategoriesLabel($categories);

		// Limit
		$limit = intval($params->get('search_max_line', 100));
		$limitstart = JRequest::getInt('limitstart', 0); 

		// Authorization & search
		if ($limit >= 0)
			$result = BSHelperUser::loadPagename($aff_sort, $limit, 1, 1, 1, $check_search_asso, $svalue, 4+16+128, $limitstart, false, $catid, $language);
		else
			$result = array();

		// The Feed itself
		$document->link = JRoute::_('index.php?option=com_myjspace&view=search&format=feed');
		$document->title = JText::_('COM_MYJSPACE_TITLEFEED');		

		$nb = min(count($result), $rss_feed);
		for ($i = 0; $i < $nb; ++$i) {
			$item = new JFeedItem();
			$item->title = $result[$i]['title'];

			$item->link = Jroute::_('index.php?option=com_myjspace&view=see&pagename='.$result[$i]['pagename']);
			$item->date = date('D, d M Y H:i:s +0000', strtotime($result[$i]['last_update_date']));

			if (count($categories) > 0 && isset($categories_label[$result[$i]['catid']]))
				$item->category = $categories_label[$result[$i]['catid']];
			else
				$item->category = '-';

			$table = JUser::getTable();
			if ($table->load($result[$i]['userid'])) { // Test if user exists before retrieving info
				$user = JFactory::getUser($result[$i]['userid']);
			} else { // User does no exist any more !
				$user = new stdClass();
				$user->username = '';
			}
			$item->author = $user->username;

			$item->description = '<div class="feed-description">'.$result[$i]['metakey'].'</div>';

			$document->addItem($item);
		}
	}
}

?>
