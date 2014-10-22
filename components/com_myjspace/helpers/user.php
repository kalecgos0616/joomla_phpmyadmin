<?php
/**
* @version $Id: user.php $
* @version		2.4.0 23/05/2014
* @package		com_myjspace
* @author       Bernard Saulmé
* @copyright	Copyright (C) 2010-2011-2012-2013-2014 Bernard Saulmé
* @license      GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die;
if (!defined('DS')) define('DS', DIRECTORY_SEPARATOR);

// Component Helper
jimport('joomla.application.component.helper');
jimport('joomla.filter.filteroutput');

// -----------------------------------------------------------------------------

class JTableMyjspace extends JTable // For J!3.1 tags usage (for the moment)
{
	var $id = 0; // V 2.0.0 - Page id
	var $userid = 0; // User page id
	var $catid = 0; // V 2.0.1 - Category id
	var $modified_by = 0; // V 2.0.1 - modified by: user id
	var $access = 0; // V 2.0.1 - shared page access: group id
	var $title = null; // V 2.2.0 - Page title
	var $pagename = null; // Page name (= title alias)
	var $content = null; // Page content
	var $blockEdit = 0; // Is the page locked by admin for owner editing (0:no, 1: yes edit suspended, 2: lock = edit & admin suspended)
	var $blockView = 1; // See view access; 0:lock, 1:public, 2:registered
	var $foldername = null; // Page folder name
	var $create_date = null;
	var $last_access_date = null;
	var $last_update_date = null;
	var $last_access_ip = 0; // crc32b last access IP value
	var $hits = 0; // Hits number
	var $publish_up = null;
	var $publish_down = null;
	var $metakey = null; // Page description
	var $template = null; // J! template
	var $language = '*'; // Language
	var $index_format_version = 10.0; // V 2.3.4
	var $newTags = null; // V2.2.0 - J!3.1.4 tags
	protected $tagsHelper = null; // V2.2.0 - J!3.1 tags

	function __construct(&$db) {
		parent::__construct('#__myjspace', 'id', $db);

		if (version_compare(JVERSION, '3.1.4', 'ge')) {
			$this->tagsHelper = new JHelperTags();
			$this->tagsHelper->typeAlias = 'com_myjspace.see';		
		}
	}

	public function store($updateNulls = false) {
		if (version_compare(JVERSION, '3.1.4', 'ge')) {
			$this->tagsHelper->preStoreProcess($this);
//			$result = parent::store($updateNulls);
			return $this->tagsHelper->postStoreProcess($this);
		}
	}

	// Delete uucm_ content & tags
	// pk : primary key
	public function delete($pk = null) {
		if ($this->id == 0)
			$this->id = $pk;
//		$result = parent::delete($pk);
		$result = $this->tagsHelper->unTagItem($pk, $this);
		return $result && $this->tagsHelper->deleteTagData($this, $pk);
	}

	// Get object (param) values and set to the current object
	// $user_page : BSHelperUser object (user page infos)
	public function get_row_BSHelperUser($user_page = null) {
		foreach($user_page as $key => $value) {
//			if (isset($user_page->$key))
				$this->$key = $user_page->$key;
		}
	}

}

// -----------------------------------------------------------------------------

class BSHelperUser
{
	var $id = 0; // V 2.0.0
	var $userid = 0;
	var $catid = 0; // V 2.0.1
	var $modified_by = 0; // V 2.0.1
	var $access = 0; // V 2.0.1
	var $title = null; // // V 2.2.0 - Page title
	var $pagename = null; // Page name (= title alias)
	var $content = null;
	var $blockEdit = 0; // Is the page locked by admin for owner editing (0:no, 1: yes edit suspended, 2: lock = edit & admin suspended)
	var $blockView = 1; // V 2.0.2 = 1 (0:lock, 1:public, 2:registered)
	var $foldername = null;
	var $create_date = null;
	var $last_access_date = null;
	var $last_update_date = null;
	var $last_access_ip = 0; // crc32b value
	var $hits = 0;
	var $publish_up = null;
	var $publish_down = null;
	var $metakey = null;
	var $template = null; // J! template
	var $language = '*';
	var $index_format_version = 10.0; // V 2.3.4
	var $metadata = null; // V 2.2.0

// Constructor
	function bshelperuser() {
		$this->foldername = self::getFoldername();
	}

// Error
	function setError($msg = '') {
		$pparams = JComponentHelper::getParams('com_myjspace');
		if ($pparams->get('debug', 0)) {
			JError::raiseWarning(500, $msg);
		}
	}

// DB : Page new create 'empty' content for the current user
//		return the page id
	function createPage($pagename = '', $catid = 0) {
	  	$db	= JFactory::getDBO();
		$query = "INSERT INTO `#__myjspace` (`userid`, `title`, `pagename`, `content`, `blockEdit`, `blockView`, `metakey`, `template`, `catid`, `modified_by`, `access`) VALUES (".$db->Quote(intval($this->userid)).", ".$db->Quote($this->title).", ".$db->Quote($pagename).",'', '0', '1', '', '', ".$db->Quote(intval($catid)).", ".$db->Quote(intval($this->userid)).", '0')";
		$db->setQuery($query);
		$db->query();
		$this->id = $db->insertid();

		if ($error = $db->getErrorMsg()) {
			$this->setError($error);
			return false;
		}

		return $this->id;
	}

// DB : Set conf parameter : pagename, blockView, blockEdit, publish_up, publish_down ... (for a page id) for a page
	function SetConfPage($choice = 255) {
		$choice = intval($choice);
	  	$db	= JFactory::getDBO();

		$query = 'UPDATE `#__myjspace` SET ';
		$query .= '`last_update_date` = CURRENT_TIMESTAMP'.',';
		if ($choice & 1) {
			$query .= ' `title` = '.$db->Quote($this->title).',';
			$query .= ' `pagename` = '.$db->Quote($this->pagename).',';
		}
		if ($choice & 2)
			$query .= ' `blockView` = '.$db->Quote(intval($this->blockView)).',';
		if ($choice & 4)
			$query .= ' `blockEdit` = '.$db->Quote(intval($this->blockEdit)).',';
		if ($choice & 8)
			$query .= ' `publish_up` = '.$db->Quote($this->publish_up).',';
		if ($choice & 16)
			$query .= ' `publish_down` = '.$db->Quote($this->publish_down).',';
		if ($choice & 32)
			$query .= ' `metakey` = '.$db->Quote($this->metakey).',';
		if ($choice & 64)		
			$query .= ' `template` = '.$db->Quote($this->template).',';
		if ($choice & 128)		
			$query .= ' `catid` = '.$db->Quote(intval($this->catid)).',';
		if ($choice & 256)		
			$query .= ' `userid` = '.$db->Quote(intval($this->userid)).',';
		if ($choice & 512)		
			$query .= ' `access` = '.$db->Quote(intval($this->access)).',';
		if ($choice & 1024)		
			$query .= ' `modified_by` = '.$db->Quote(intval($this->modified_by)).',';
		if ($choice & 2048)
			$query .= ' `language` = '.$db->Quote($this->language).',';

		$query = substr($query, 0, -1); // remove the last comma ...
		$query .= ' WHERE `id` = '.$db->Quote(intval($this->id));

		$db->setQuery($query);
		if ($db->query())
			return 1;

		if ($error = $db->getErrorMsg()) {
			$this->setError($error);
			return false;
		}

		return 0;
	}

// DB & FS : Delete page & folder content
	function deletePage($link_folder = 1, $forced = 1) {
		$filedir = JPATH_SITE.DS.$this->foldername.DS.$this->pagename;

		// Important :-)
		if ($this->pagename == '' || ($this->foldername == '' && $link_folder == 1))
		   return 0;

		if ($link_folder == 1) {
			$oldfolder = getcwd();
			if (!@chdir($filedir))
				return 0;		

			// Delete all files in the folder
			$projectsListIgnore = array('.', '..'); // safety
			$handle = @opendir('.');
			while (false !== ($file = @readdir($handle))) {
				if (!@is_dir($file) && !in_array($file, $projectsListIgnore)) {
					if ($forced == 0 && $file != 'index.php')
						return 0;
				
					if ($file != 'index.php' && !@unlink($file)) {
						@chdir($oldfolder);
						return 0;
					}
				}
			}
			if (!@unlink('index.php')) {
				@chdir($oldfolder);
				return 0;
			}

			@closedir($handle);
			@chdir(JPATH_SITE.DS.$this->foldername);

			if (!(@rmdir($filedir) || @rename($filedir, JPATH_SITE.DS.$this->foldername.DS.'#garbage'))) {
				@chdir($oldfolder);	
				return 0;
			}
		}

		$db	= JFactory::getDBO();
		$query = "DELETE FROM `#__myjspace` WHERE `id` = ".$db->Quote(intval($this->id));
		$db->setQuery($query);
		if ($db->query()) {
			if ($link_folder == 1)
				@chdir($oldfolder);

			return 1;
		}

		if ($error = $db->getErrorMsg()) {
			$this->setError($error);
			return 0;
		}

		if ($link_folder == 1)
			@chdir($oldfolder);
		return 0;
	}
	
// DB : Load all user page info (with content)
// $this->id or $this->pagename need to be set before call
// Choice=0 use id, choix=1 use pagename
// getcontent_bs = 1 to load the content

  	function loadPageInfo($choix = 0, $getcontent_bs = 1) {

		$this->userid = 0;
		$this->title = null;
		$this->content = null;
		$this->blockEdit = 0;
		$this->blockView = 1;
		$this->create_date = null;
		$this->last_access_date = null;
		$this->last_update_date = null;
		$this->last_access_ip = 0;
		$this->hits = 0;
		$this->publish_up = null;
		$this->publish_down = null;
		$this->metakey = null;
		$this->template = null;	
		$this->catid = 0;
		$this->access = 0;
		$this->modified_by = 0;
		$this->language = '*';

		if (($this->id > 0 && $choix == 0) || ($this->pagename != '' && $choix ==1)) {	
		  	$db	= JFactory::getDBO();
			$result_set	= null;

			if ($choix == 1)
				$where = "WHERE BINARY `pagename` = ".$db->Quote($this->pagename);
			else
				$where = "WHERE `id` = ".$db->Quote(intval($this->id));

			$query = "SELECT `id`, `userid`, `title`, `pagename`, `blockEdit`, `blockView`";
			if ($getcontent_bs == 1)
				$query .= ",`content`";
			$query .= ",`create_date`, `last_update_date`, `last_access_date`, `last_access_ip`, `hits`, `publish_up`, `publish_down`, `metakey`, `template`, `catid`, `access`, `modified_by`, `language` FROM `#__myjspace` ".$where;

			$db->setQuery($query, null, 1);
			$result_set = $db->loadObjectList();

			$this->id = 0;
			$this->pagename = null;
			if (isset($result_set[0])) {
				foreach($result_set[0] as $key => $value) {
					$this->$key = $result_set[0]->$key;
				}
				return 1;
			}
		}
		return 0;	
	}
	
// DB : Load user info (without content)
  	function loadPageInfoOnly($choix = 0) {
		$this->loadPageInfo($choix, 0);
	}

// DB : Update content (= personal page)
	function updateUserContent() {
	  	$db	= JFactory::getDBO();
		$query = "UPDATE `#__myjspace` SET `content` = ".$db->Quote($this->content).",`modified_by` = ".$db->Quote($this->modified_by).", `last_update_date` = CURRENT_TIMESTAMP WHERE `id` = ".$db->Quote(intval($this->id));
		$db->setQuery($query);
		if ($db->query())
			return 1;

		if ($error = $db->getErrorMsg()) {
			$this->setError($error);
			return false;
		}

		return 0;
	}
	
// DB : Update Date and hit for the last access if not same ip addr compare to the last (too simple but efficient)
	function updateLastAccess($last_access_ip = '') {
	  	$db	= JFactory::getDBO();
		$query = "UPDATE `#__myjspace` SET `last_access_date` = CURRENT_TIMESTAMP, `last_access_ip` = ".$db->Quote($last_access_ip).", `hits` = `hits` + 1 WHERE `id` = ".$db->Quote(intval($this->id))." AND `last_access_ip` <> ".$db->Quote($last_access_ip);
		$db->setQuery($query);
		if ($db->query())
			return 1;

		if ($error = $db->getErrorMsg()) {
			$this->setError($error);
			return false;
		}

		return 0;
	}

// DB : Reset Hits & Update Date
	function ResetLastAccess() {
	  	$db	= JFactory::getDBO();
		$query = "UPDATE `#__myjspace` SET `last_access_date` = '0000-00-00 00:00:00', `last_access_ip` = '0', `hits` = 0 WHERE `id` = ".$db->Quote(intval($this->id));
		$db->setQuery($query);
		if ($db->query())
			return 1;

		if ($error = $db->getErrorMsg()) {
			$this->setError($error);
			return false;
		}

		return 0;
	}
	
// DB : Check if pagename already exists by name, return id
	public static function ifExistPageName($pagename = '') {
	  	$db	= JFactory::getDBO();
		$query = "SELECT `id` FROM `#__myjspace` WHERE BINARY `pagename` = ".$db->Quote($pagename);
		$db->setQuery($query);
		return $db->loadResult();
	}

//	 DB :  check if this page (id) folder is accessible for me (upload)
// 	if false return null if true return the page folder
	public static function is_my_rep($id = 0, $is_admin = false) {

		// Safety controls
		$pparams = JComponentHelper::getParams('com_myjspace');
		if ($pparams->get('link_folder', 1) == 0)
			return null;

		if ($id) {
			$user_page = New BSHelperUser();

			$user_page->id = $id;
			$user_page->loadPageInfoOnly();

			if ($is_admin == false) { /// If no admin check to be sure : only my pages
				$user = JFactory::getuser(); // Check if user exists & connected
				if ($user->id != $user_page->userid) // Check for only my page
					return null;
			}

			if ($user_page->pagename == '')
				return null;
			else
				return $user_page->foldername.'/'.$user_page->pagename.'/';
		}
		return null;
	}
	
// DB : Select a specific content by Page id
	function GetContentPageId($id = 0) {
	  	$db	= JFactory::getDBO();
		$query = "SELECT `content` FROM `#__myjspace` WHERE `id` = ".$db->Quote($id);
		$db->setQuery($query);
		return $db->loadResult();
	}

// DB : Get the list of page id, pagename, title for a specific user
//		If id specified, select only the concerned page (if owned by the user)
// 		If array() $access specified include share pages for the user list group
	function GetListPageId($userid = 0, $id = 0, $catid = 0, $access = null) {
		if (version_compare(JVERSION, '1.6.0', 'lt'))
			$catid = 0;

	  	$db	= JFactory::getDBO();
		$query = "SELECT `id`, `pagename`, `title` FROM `#__myjspace` WHERE ";
		if ($access == null)
			$query .= " `userid` = ".$db->Quote($userid);
		else
			$query .= " ( `userid` = ".$db->Quote($userid)." OR `access` IN (".implode(',', $access).") )";
		if ($id > 0)
			$query .= " AND `id` = ".$db->Quote($id);
		if ($catid > 0)
			$query .= " AND `catid` = ".$db->Quote($catid);
		$db->setQuery($query);
		return $db->loadAssocList();
	}

// DB : Count the number of pages for a category for a user
// $this->userid need to be set before call
	public function CountUserPageCategory($catid = 0) {
	  	$db	= JFactory::getDBO();
		$query  = "SELECT COUNT(*) FROM `#__myjspace` WHERE `userid` = ".$db->Quote($this->userid)." AND `catid` = ".$db->Quote($catid);
		$db->setQuery($query);
		return $db->loadResult();
	}

// DB : Get categories list
	public static function GetCategories($published = null, $language = null) {

		if (version_compare(JVERSION, '1.6.0', 'lt'))
			return array();

	  	$db	= JFactory::getDBO();
		$query  = "SELECT a.id AS value, a.title AS text, a.level, a.published FROM `#__categories` AS a";
		$query .= " LEFT JOIN `#__categories` AS b ON a.lft > b.lft";
		$query .= " AND a.rgt < b.rgt";
		$query .= " WHERE ( a.extension = 'com_myjspace' )";
		if ($published != null)
			$query .= " AND a.published = ".$db->Quote($published);
		else
			$query .= " AND a.published IN ( 0, 1 )";

		if ($language != null)
			$query .= "AND a.language IN ('*',".$db->Quote($language).")";

		$query .= " GROUP BY a.id, a.title, a.level, a.lft, a.rgt, a.extension, a.parent_id, a.published";
		$query .= " ORDER BY a.lft, a.level ASC";
		$db->setQuery($query);

		return $db->loadAssocList();
	}
	
// DB : Get category label for a specific category
	public static function GetCategoryLabel($catid = 0) {

		if ($catid == 0 || version_compare(JVERSION, '1.6.0', 'lt'))
			return '';

	  	$db	= JFactory::getDBO();
		$query  = "SELECT `title` FROM `#__categories` WHERE extension = 'com_myjspace' AND `id` = ".$db->Quote($catid);
		$db->setQuery($query);
		return $db->loadResult();
	}

// DB : Get Categories label into an indexed array, $cat = tab of categories
	public static function GetCategoriesLabel($cat = null) {
		if ($cat == null)
			$cat = self::GetCategories(1);

		$nb_cat = count($cat);
		$cat_index = array();
		for ($i = 0 ; $i < $nb_cat ; $i++) {
			$cat_index[$cat[$i]['value']] = $cat[$i]['text'];
		}

		return $cat_index;
	}

// DB Get Categories Id into an array	
	public static function GetCategoriesId($published = null, $language = null, $categories = false) {
		if ($categories == false)
			$categories = self::GetCategories($published, $language);

		$nb_cat = count($categories);
		$catid_list = array();
		$catid_list[] = 0;
		for ($i = 0 ; $i < $nb_cat ; $i++) {
			$catid_list[] = $categories[$i]['value'];
		}

		return $catid_list;	
	}

// DB : Get user page(s) URL: Page URL if one & page list URL if more than one
//		if only one : can choose if display link as folder
// return : tab of pages id, url

	public static function GetUserUrl($userid = 0, $link_folder_print = 0, $Itemid = 0, $xhtml = true, $force_list = 0) {

        require_once JPATH_ROOT.DS.'components'.DS.'com_myjspace'.DS.'helpers'.DS.'util.php';

		$url = '';
		$user_page = New BSHelperUser();
		$list_page_tab = $user_page->GetListPageId($userid);
		$nb_page = count($list_page_tab);

		if ($nb_page > 1 || $force_list == 1) {
			$Itemid = get_menu_itemid('index.php?option=com_myjspace&view=pages', $Itemid);
			if ($Itemid != 0)
				$Itemid_string = '&Itemid='.$Itemid;
			else
				$Itemid_string = '';

			$url = Jroute::_('index.php?option=com_myjspace&view=pages&uid='.$userid.$Itemid_string, $xhtml);
		} else if ($nb_page == 1) {
			$Itemid = get_menu_itemid('index.php?option=com_myjspace&view=see', $Itemid);
			$Itemid = get_menu_itemid('index.php?option=com_myjspace&view=see&id=&pagename=', $Itemid);
			if ($Itemid != 0)
				$Itemid_string = '&Itemid='.$Itemid;
			else
				$Itemid_string = '';

			if ($link_folder_print == 1)
				$url = JURI::base(true).'/'.self::getFoldername().'/'.$list_page_tab[0]['pagename'].'/';
			else
				$url = Jroute::_('index.php?option=com_myjspace&view=see&id='.$list_page_tab[0]['id'].$Itemid_string, $xhtml);
		}

		return (array($list_page_tab, $url));
	}

// DB : Get a new free pagename with a number as suffix
//		$prefix : pagename prefix, accepts tags: "#username", "#name", "#userid", "#category", "#catid"
//		$fin : max number of try to find a name (10000 !)
	function GetPagenameFree($prefix = "#username", $user = null, $catid = 0, $fin = 10000) {

		// Replace the prefix tags & prefix cleaning
		if ($user != null) { // User
			$search = array("#username", "#name", "#userid");
			$replace = array($user->username, $user->name, $user->id);
			$prefix = str_replace($search, $replace, $prefix);
		}
		$pparams = JComponentHelper::getParams('com_myjspace');	
		$catid = ($catid > 0) ? $catid : $pparams->get('default_catid', 0);
		if ($catid > 0 && version_compare(JVERSION, '1.6.0', 'ge')) { // Category
			$search = array("#category", "#catid");
			$replace = array(self::GetCategoryLabel($catid), $catid);
		} else {
			$search = array("#category", "#catid");
			$replace = array('', '');
		}
		$prefix_title = str_replace($search, $replace, $prefix);
		$prefix = self::stringURLSafe($prefix_title);

	  	$db	= JFactory::getDBO();
		if (version_compare(JVERSION, '1.6.0', 'ge'))
			$searchEscaped = "(".$db->Quote('^'.$db->escape($prefix, true).'[0-9]*$') . ")";
		else
			$searchEscaped = "(".$db->Quote('^'.$db->getEscaped($prefix, true).'[0-9]*$') . ")";
		$query = "SELECT `pagename` FROM `#__myjspace` WHERE BINARY `pagename` RLIKE ".$searchEscaped;
		$db->setQuery($query);
		$list_pages = $db->loadAssocList();	
		$nb_list = count($list_pages);

		// If no page with this $prefix use the prefix as pagename else find a suffix
		if ($nb_list != 0) {
			// To do not have suffix = 1 if a pagename = $prefix exists
			$debut = 1;
			for ($j = 0; $j < $nb_list; $j++) {
				if ($list_pages[$j]['pagename'] == $prefix) {
					$debut = 2;
					break;
				}
			}

			for ($i = $debut; $i <= $fin; $i++) {
				$ok = true;
				for ($j = 0; $j < $nb_list; $j++) {
					if ($list_pages[$j]['pagename'] == $prefix.$i) {
						$ok = false;
						break;
					}
				}

				if ($ok == true) {
					if (self::stringURLSafe($prefix_title.$i) == $prefix.$i)
						return $prefix_title.$i;
					else
						return $prefix.$i;
				}
			}
		}

		// No page with the prefix ($nb_list == 0) or too many existing pages with numbers ... choose it yourself
		return $prefix_title;
	}

// DB : List of all username (if $resultmode = 1 add metakey)
//		or count the number of line for the same criteria
	public static function loadPagename($triemode = -1, $affmax = 0, $blocked = 0, $publish = 0, $content = 0, $check_search = null, $scontent = '', $resultmode = 0, $limitstart = 0, $count = false, $catid = 0, $language = null, $extra_query = null) {

	  	$db	= JFactory::getDBO();
		// Safety
		$resultmode = intval($resultmode);
		if ($resultmode < 0 || $resultmode > 8191)
			$resultmode = 1;
		if ($affmax < 0)
			return null;

		if ($count == true)
			$query = "SELECT COUNT(*)";
		else {
			// Columns to 'display'
			$query = "SELECT `id`, `userid`, `title`, `pagename`, `blockView`"; // id(username) = 1, pagename = 2 for display (search)

			if ($resultmode & 4)
				$query .= ", `metakey`";
			if ($resultmode & 8)
				$query .= ", `create_date`";
			if ($resultmode & 16)
				$query .= ", `last_update_date`";
			if ($resultmode & 32)
				$query .= ", `hits`";
			// 64 for image (search)
			if ($resultmode & 128)
				$query .= ", `catid`";
			if ($resultmode & 256)
				$query .= ", `content`";
			if ($resultmode & 512)
				$query .= ", LENGTH(`content`) AS `size`";
			// 1024 blockview
			if ($resultmode & 2048) // language
				$query .= ", `language`";
			if ($resultmode & 4096) // Share group
				$query .= ", `access`";
		}

		$query .= " FROM `#__myjspace` WHERE 1=1";
		
		// Criteria

		if ($blocked)
			$query .= " AND `blockView` != 0";

		if ($publish)
			$query .= " AND `publish_up` < CURRENT_TIMESTAMP AND (`publish_down` >= CURRENT_TIMESTAMP OR `publish_down` = '0000-00-00 00:00:00')";

		if ($content == 1)
			$query .= " AND `content` != ''";

		if ($content == -1)
			$query .= " AND `content` = ''";

		if ($language)
			$query .= " AND `language` IN ('*',".$db->Quote($language).")";

		if (is_array($catid)) {
			$nb_catid = count($catid); // Values need to be 'quoted' into the array !
			for ($i = 0; $i < $nb_catid; $i++)
				$catid[$i] = $db->Quote($catid[$i]);
			$catid_list = implode(',', $catid);
			$query .= " AND `catid` IN (".$catid_list.")";
		} else if ($catid != 0) {
			$query .= " AND `catid` = ".$db->Quote($catid);
		}

		if ($check_search != null && count($check_search) > 0 && $scontent != '') {
			$query .= " AND ( 1=0 ";

			$pparams = JComponentHelper::getParams('com_myjspace');
			if ($pparams->get('search_html', 1)) // Search into html content
				$scontent = htmlentities($scontent,ENT_QUOTES, 'UTF-8');

			$tab_scontent = explode (' ', $scontent);
			if (count($tab_scontent >= 1)) {
				$scontent = '';
				foreach ($tab_scontent as $word) {
					if (version_compare(JVERSION, '1.6.0', 'ge'))
						$scontent .= '%'.$db->escape($word, true);
					else
						$scontent .= '%'.$db->getEscaped($word, true);
				}
				$scontent .= '%';
			}

			if (isset($check_search['name']))
				$query .= " OR `pagename` LIKE ".$db->Quote($scontent, false);

			if (isset($check_search['description']))
				$query .= " OR `metakey` LIKE ".$db->Quote($scontent, false);

			if (isset($check_search['content']))
				$query .= " OR `content` LIKE ".$db->Quote($scontent, false);

			$query .= " ) ";
		}	

		// Extra query
		if ($extra_query)
			$query .= $extra_query;

		// Sort order
		if ($count == false) {
			if (is_numeric($triemode)) {
				if ($triemode == 0)
					$query .= " ORDER BY `pagename` ASC";
				else if ($triemode == 1)
					$query .= " ORDER BY `pagename` DESC";
				else if ($triemode == 2)
					$query .= " ORDER BY RAND()";
				else if ($triemode == 3)
					$query .= " ORDER BY `create_date` DESC";
				else if ($triemode == 4)
					$query .= " ORDER BY `last_update_date` DESC";
				else if ($triemode == 5)
					$query .= " ORDER BY `hits` DESC";
			}

			if (is_string($triemode))
				$query .= " ORDER BY ".$triemode;
		}

		// Query
		$db->setQuery($query, $limitstart, $affmax);

		if ($count == true)
			$row = $db->loadResult();
		else
			$row = $db->loadAssocList();

		return $row;
	}

// DB : count the total number of pages
	public static function myjsp_count_nb_page() {
	  	$db	= JFactory::getDBO();
		$query = "SELECT COUNT(*) FROM `#__myjspace`";
		$db->setQuery($query);
		$db->query();
		return $db->loadResult();
	}

// DB : Count the number of distinct users
	public static function myjsp_count_nb_user() {
	  	$db	= JFactory::getDBO();
		$query = "SELECT COUNT(DISTINCT `userid`) FROM `#__myjspace`";
		$db->setQuery($query);
		$db->query();
		return $db->loadResult();
	}
	
// DB : Find the max page per user
	public static function myjsp_max_page_per_user() {
	  	$db	= JFactory::getDBO();
		$query = "SELECT MAX(`mycol`) FROM (SELECT COUNT(`userid`) AS mycol FROM `#__myjspace` GROUP BY `userid`) AS compteur";
		$db->setQuery($query);
		$db->query();
		return $db->loadResult();
	}

// DB : Find pages association J!3.0.3+
	public static function getAssociations($id = 0)	{
		$associations = array();

		if (version_compare(JVERSION, '3.0.3', 'lt'))
			return $associations;

		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->from('#__myjspace as c');
		$query->innerJoin('#__associations as a ON a.id = c.id AND a.context='.$db->quote('com_myjspace.item'));
		$query->innerJoin('#__associations as a2 ON a.key = a2.key');
		$query->innerJoin('#__myjspace as c2 ON a2.id = c2.id');
		$query->where('c.id ='.(int)$id);
		$select = array('c2.language', 'c2.id', 'c2.pagename');
		$query->select($select);
		$db->setQuery($query);
		$contactitems = $db->loadObjectList('language');

		foreach ($contactitems as $tag => $item) {
			$associations[$tag] = $item;
		}

		return $associations;
	}

// DB : Set pages association ($associations = page id list) J!3.0.3 +
	public static function setAssociations($associations) {

		if (version_compare(JVERSION, '3.0.3', 'lt'))
			return false;

		foreach ($associations as $tag => $id) { // clean
			$associations[$tag] = intval($id);
			if ($associations[$tag] <= 0)
				unset($associations[$tag]);
		}

		if (count($associations) <= 0)
			return false;

		// Deleting old association for these items
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->delete('#__associations');
		$query->where('context='.$db->quote('com_myjspace.item'));
		$query->where('id IN ('.implode(',', $associations).')');
		$db->setQuery($query);
		$db->execute();

		if ($error = $db->getErrorMsg()) {
			$this->setError($error);
			return false;
		}

		// Adding new association for these items
		if (count($associations) > 1) {
			$key = md5(json_encode($associations));
			$query->clear();
			$query->insert('#__associations');
			foreach ($associations as $tag => $id) {
				$query->values($id.','.$db->quote('com_myjspace.item').','.$db->quote($key));
			}
			$db->setQuery($query);
			$db->execute();

			if ($error = $db->getErrorMsg()) {
				$this->setError($error);
				return false;
			}
		}

		return true;
	}	

// DB : Return the list of available languages (tag + label), including * for all
	public static function get_language_list() {
		$language_list = array();
		if (version_compare(JVERSION, '1.6.0', 'lt'))
			return $language_list;

		$db	= JFactory::getDBO();
		$query = "SELECT `lang_code`, `title_native`, `sef` FROM `#__languages` ORDER BY `lang_code` ASC";
		$db->setQuery($query);
		$db->query();
		$language_db = $db->loadObjectList();

		$language_list[0] = new stdClass();
		$language_list[0]->lang_code = '*';
		$language_list[0]->title_native = '*';
		$language_list[0]->sef = '';

		$language_list = array_merge($language_list, $language_db);

		return $language_list;
	}

// DB : Return the language label
	public static function get_language_native_label($lang_code = '*') {
		if (version_compare(JVERSION, '1.6.0', 'lt'))
			return '*';

		$db	= JFactory::getDBO();
		$query = "SELECT `title_native` FROM `#__languages` WHERE `lang_code` = ".$db->Quote($lang_code);
		$db->setQuery($query, null, 1);
		$result = $db->loadResult();
		if ($result)
			return $result;
		else
			return '*';
	}

// FS : Page Create Folder & file to redirect	
	function CreateDirFilePage($pagename = '', $choix = 1, $id = 0) {

		$filedir = JPATH_SITE.DS.$this->foldername.DS.$pagename;
		$link = JURI::root();
		$link = substr($link, 0, -1); // delete the last '/'

		$url_parse = parse_url($link);
		if ($url_parse != null)
			$path = $url_parse['path'];
		else
			return 0;

		if ($id != 0)
			$userid = $id;
		else
			$userid = $this->id;

		if ($choix == 1)
			$content_id = 'pagename='.$pagename;
		else
			$content_id = 'id='.$userid;

$content = "<?php
// com_myjspace
// Format:".$this->index_format_version."
// Pagename:".$pagename."
// id:".$userid."
//
define('_JEXEC', 1);
define('DS', DIRECTORY_SEPARATOR);
define('JPATH_BASE', '".str_replace('\\', '\\\\', JPATH_SITE)."');

if (!@file_exists(JPATH_BASE.DS.'includes'.DS.'defines.php')) {
	echo \"<html><body>Please ask to your administrator to re-create all pages index files (using BS MyJspace back-end tools)</body></html>\";
	exit;
}

require_once(JPATH_BASE.DS.'includes'.DS.'defines.php');
require_once(JPATH_BASE.DS.'includes'.DS.'framework.php');

if (!@file_exists(JPATH_BASE.DS.DS.'components'.DS.'com_myjspace'.DS.'helpers'.DS.'user.php')) {
	echo \"<html><body>Component BS MyJspace is requested</body></html>\";
	exit;
}
	
\$app = JFactory::getApplication('site');
\$app->initialise();

\$menu = \$app->getMenu();
\$defaultMenu = \$menu->getDefault();
\$itemid = \$defaultMenu->id;
\$itemid = get_menu_itemid2('index.php?option=com_myjspace&view=see', \$itemid);
\$itemid = get_menu_itemid2('index.php?option=com_myjspace&view=see&id=&pagename=', \$itemid);

\$url_tmp = \"index.php?option=com_myjspace&view=see&".$content_id."\";
if (\$itemid != 0)
	\$url_tmp .= '&Itemid='.\$itemid;

\$s = empty(\$_SERVER['HTTPS']) ? '': (\$_SERVER['HTTPS'] == 'on') ? 's' : '';
\$url = 'http'.\$s.'://'.\$_SERVER['HTTP_HOST'].'".$path."'.str_replace(JURI::base(true), '', JRoute::_(\$url_tmp, false));

if (!headers_sent())
	header(\"location: \$url\");
echo \"<html><body><a href=\".\$url.\">".$pagename."</a><script type=\\\"text/javascript\\\">window.location.href='\".\$url.\"'</script></body></html>\";

function get_menu_itemid2(\$url = '', \$default = 0) {
	\$app = JFactory::getApplication();
	\$menu = \$app->getMenu();
	\$menu_items = \$menu->getItems('link', \$url);

	if (count(\$menu_items) >= 1)
		return \$menu_items[0]->id;

	return \$default;
}
?>
";
		// Folder (may already exist)
		@mkdir($filedir, 0755);

		// File index.php
		$file = $filedir.DS.'index.php';
		$handle = @fopen($file, "w");
		if ($handle) {
			@fwrite($handle, $content);
			@chmod($file, 0755);
			return 1;
		}

		return 0;
	}

	// Retrieve the version info the index file
	function VersionIndexPage($pagename = '') {

		$file_index = JPATH_SITE.DS.$this->foldername.DS.$pagename.DS.'index.php';
		$contenu = @fread(fopen($file_index, "r"), 80); 
		$sortie = null;
		preg_match('#// Format:(.*)\n#Us', $contenu, $sortie);

		if (isset($sortie[1]))
			$version = trim($sortie[1]);
		else
			$version = 0;

		return $version;
	}

	// Check the number of index page with NOT the actual version for all pages or inly the oldest
	public static function CheckVersionIndexPage($only_oldest = true) {
		$nb_index_ko = -1;
		$pparams = JComponentHelper::getParams('com_myjspace');
		if ($pparams->get('link_folder', 1) == 1) {
			$user_page = New BSHelperUser();
			$user_page->foldername = self::getFoldername();

			if ($only_oldest == true)
				$query = "SELECT `pagename` FROM `#__myjspace` WHERE `create_date` IN ( SELECT MIN(`create_date`) FROM `#__myjspace` )";
			else
				$query = "SELECT `pagename` FROM `#__myjspace`";

		  	$db	= JFactory::getDBO();
			$db->setQuery($query);
			$username_list = $db->loadAssocList();

			$nb_page = count($username_list);
			$nb_index_ko = 0;
			if ($nb_page > 0) {
				for ($i = 0; $i < $nb_page; $i++) {
					if ((int)$user_page->VersionIndexPage($username_list[$i]['pagename']) != (int)$user_page->index_format_version)
						$nb_index_ko = $nb_index_ko + 1;
				}
			}
		}
		return $nb_index_ko;
	}

// FOLDERNAME

// CFG : Get foldername
	public static function getFoldername() {
		$pparams = JComponentHelper::getParams('com_myjspace');
		$foldername = $pparams->get('foldername', 'myjsp');
		return $foldername;
	}

// FS : test if the 'real' foldername exist
	public static function ifExistFoldername($foldername = '') {
		$oldfolder = getcwd();
		@chdir(JPATH_SITE);
		$retour = @is_dir($foldername);
		@chdir($oldfolder);
		return($retour);
	}

// FS & CFG : create or update page ROOT folder name
	function updateFoldername($foldername = '', $link_folder = 1, $keep = 0, $url = 'index.php') {
	    require_once JPATH_COMPONENT_SITE.DS.'helpers'.DS.'user_event.php';
	    require_once JPATH_COMPONENT_SITE.DS.'helpers'.DS.'version.php';

		// Check
		if ($foldername == '')
			return 0;

		if ($link_folder == 1) {
			// Rename (or create + chmod) folder or move sub-folders on file system too
			if ($this->foldername != $foldername && self::ifExistFoldername(JPATH_SITE.DS.$foldername) && BSUserEvent::adm_rename_folders(JPATH_SITE.DS.$this->foldername, JPATH_SITE.DS.$foldername)) { // rename = move in one existing
				if ($keep == 0)
					@rmdir(JPATH_SITE.DS.$this->foldername);
			} else if ($keep == 1 && @mkdir(JPATH_SITE.DS.$foldername) && @chmod(JPATH_SITE.DS.$foldername, 0755) && BSUserEvent::adm_rename_folders(JPATH_SITE.DS.$this->foldername, JPATH_SITE.DS.$foldername)) { // Create a new one and move
				// rien :-)
			} else if ($keep == 0 && !@rename(JPATH_SITE.DS.$this->foldername, JPATH_SITE.DS.$foldername)) { // if error try to create
				if (!@mkdir(JPATH_SITE.DS.$foldername) || !@chmod(JPATH_SITE.DS.$foldername, 0755))
					return 0;
			} // => rename folder ok

			// Index file
			$file = JPATH_SITE.DS.$foldername.DS.'index.html';
			if (substr($url, 0, 9) == 'index.php')
				$url = JURI::root().$url;
			$content = "<html><head><meta http-equiv=\"refresh\" content=\"0; URL=$url\"></head><body><script type=\"text/javascript\">window.location=\"$url\"</script></body></html>";
			$handle = @fopen($file, "w");
			if ($handle) {
				@fwrite($handle, $content);
				@chmod($file, 0755);
			}
		}

	    if ($this->foldername != $foldername) {
			$pparams = JComponentHelper::getParams('com_myjspace');
			$pparams->set('foldername', $foldername);
			BS_Helper_version::save_parameters('com_myjspace');
		}
		return 1;
	}

// Check foldername characters	
	public static function checkFoldername($foldername = '', $allowed = '#^[a-zA-Z0-9/_]+$#') {
		if (preg_match($allowed, $foldername))
			return 1;
		return 0;
	}

// Provide alias for page name compatible with url
	public static function stringURLSafe($string) {

		$pparams = JComponentHelper::getParams('com_myjspace');
		$separ = $pparams->get('url_separ_word', '_'); // hidden option for the moment
	
		// Remove any '_' ($separ) from the string since they will be used as concatenate
		$str = str_replace($separ, ' ', $string);

		// Language transliteration
		if (version_compare(JVERSION, '1.6.0', 'lt')) {
			$lang = JFactory::getLanguage();
			$str = $lang->transliterate($str); // Transliterate. Not used for J!1.6+ because lowercase the string in J!1.6+ ...
		} else {
			if (!class_exists('JLanguageTransliterate')) // < J!3
				include_once JPATH_ROOT.DS.'libraries'.DS.'joomla'.DS.'language'.DS.'latin_transliterate.php';
			$str = JLanguageTransliterate::utf8_latin_to_ascii($str); // Transliterate, this new version ok and not language dependant
		}

		// Trim white spaces at beginning and end of alias
		$str = trim($str);

		// Remove any duplicate white-space, and ensure all characters are alphanumeric
		$rule = '/(\s|[^A-Za-z0-9'."\\".$separ.'])+/';
		$str = preg_replace($rule, $separ, $str);

		// Trim '_' ($separ) at beginning and end of alias
		$str = trim($str, $separ);

		return $str;
	}

// PAGE CONTENT fct

// Substitute #tags with they contents
// Reserved words: #userid, #name, #username, #title, #pagename, #id, #access, #acces_edit', #lastupdate, #lastaccess, #createdate, #fileslist, #hits ... and a specific one #bsmyjspace :-)
// pos = 0 for page content, 1 for prefix, 2 for suffix
	function traite_prefsuf($atraiter = '', $user = null, $page_increment = 0, $date_fmt = 'Y-m-d H:i:s', $chaine_files = '', $Itemid = 0, $top_bottom = false) {

		if ($atraiter == null || $atraiter == '')
			return '';

        require_once JPATH_ROOT.DS.'components'.DS.'com_myjspace'.DS.'helpers'.DS.'util_acl.php';
		$pparams = JComponentHelper::getParams('com_myjspace');

		// 'Complex' tag: myjsp iframe
		if ($top_bottom && $pparams->get('allow_tag_myjsp_iframe', 1) >= 1 || $pparams->get('allow_tag_myjsp_iframe', 1) == 1) {
			// Tag {myjsp iframe URL}
			$chaine_iframe = '<iframe src="$1" id="myjsp-iframe" frameborder="0" ></iframe>';
			$atraiter = preg_replace('!{myjsp iframe (.+)\}!isU', $chaine_iframe, $atraiter);
		}

		// 'Complex' tag: myjsp include
		if ($top_bottom && $pparams->get('allow_tag_myjsp_include', 1) >= 1 || $top_bottom && $pparams->get('allow_tag_myjsp_include', 1) == 1) {
			// Tag {myjsp include URL} (only the first url will be taking into account: to be used once per page + head + foot)
			if (preg_match('!{myjsp include (.+)\}!isU', $atraiter, $sortie)) {
				if (count($sortie) >= 2) {
					$fichier_sortie = @file_get_contents(trim($sortie[1]));
					preg_match('#<body(.*)>(.*)</body>#Us', $fichier_sortie, $tab_sortie);
					if (count($tab_sortie) >= 3)
						$atraiter = preg_replace('!{myjsp include (.+)\}!isU', '<div>'.$tab_sortie[2].'</div>', $atraiter);
				}
			}
		}

		// CB
		$chaine_cb = '<iframe src="'.Jroute::_('index.php?option=com_comprofiler&task=userProfile&user='.$user->id.'&tmpl=component').'" id="cbprofile" frameborder="0" ></iframe>';
		// Joomsocial
		$chaine_jsocial_profile = '<iframe src="'.Jroute::_('index.php?option=com_community&view=profile&userid='.$user->id.'&tmpl=component').'" id="jomsocial-profile" frameborder="0" ></iframe>';
		$chaine_jsocial_photos  = '<iframe src="'.Jroute::_('index.php?option=com_community&view=photos&task=myphotos&userid='.$user->id.'&tmpl=component').'" id="jomsocial-photos" frameborder="0" ></iframe>';
		// MyJspace string
		$chaine_bsmyjspace = '<span class="bsfooter"><a href="'.Jroute::_('index.php?option=com_myjspace&amp;view=myjspace').'">BS MyJspace</a></span>';
		// Reserved words to replace
		$search  = array('#userid', '#name', '#username', '#id', '#title', '#pagename', '#access', '#lastupdate', '#lastaccess', '#createdate', '#description', '#category', '#bsmyjspace', '#fileslist', '#cbprofile','#jomsocial-profile','#jomsocial-photos');
		$replace = array($user->id,
						$user->name,
						$user->username,
						$this->id,
						$this->title,
						$this->pagename,
						get_assetgroup_label($this->blockView),
						date($date_fmt, strtotime($this->last_update_date)),
						date($date_fmt, strtotime($this->last_access_date)),
						date($date_fmt, strtotime($this->create_date)),
						$this->metakey,
						self::GetCategoryLabel($this->catid),
						$chaine_bsmyjspace,
						$chaine_files,
						$chaine_cb,
						$chaine_jsocial_profile,
						$chaine_jsocial_photos);

		if ($pparams->get('share_page', 0) != 0) {
			$search[] = '#shareedit';
			$replace[] = get_assetgroup_label($this->access);

			$table = JUser::getTable();
			if ($table->load($this->modified_by)) { // Test if user exist before to retrieve info
				$modified_by = JFactory::getUser($this->modified_by);
			} else { // User no no exist any more !
				$modified_by = new stdClass();
				$modified_by->username = ' ';
			}
			$search[] = '#modifiedby';
			$replace[] = $modified_by->username;
		}

		if ($pparams->get('language_filter', 0) != 0) {
			$search[] = '#language';
			$replace[] = self::get_language_native_label($this->language);
		}

		if ($page_increment == 1) {
			$search[] = '#hits';
			$replace[] = $this->hits;
		}

		// Replace
		$atraiter = str_replace($search, $replace, $atraiter);

		return $atraiter;
	}

	// Function to have 'API' for component & plugins
	
	// Return the user pagename content if exist (with all tags replaced)
	public static function mjsp_exist_page_content($id = null, $pagebreak = 0, $Itemid = 0) {
		$retour = '';

		// User & component
		$pparams = JComponentHelper::getParams('com_myjspace');
		$user_actual = JFactory::getuser();

		// Personal page info
		if (intval($id) != 0) {
			$user_page = New BSHelperUser(); // For simple call from outside
			$user_page->id = $id;
			$user_page->loadPageInfo();
		} else if (isset($id->id)) {
			$user_page = $id;
		} else
			return '';

		$user = JFactory::getuser($user_page->userid);

        // Content & complete with prefix & suffix and replacing # tags
		$page_increment = $pparams->get('page_increment', 1);

        // Content
		$uploadadmin = $pparams->get('uploadadmin', 1);
		$uploadimg = $pparams->get('uploadimg', 1);
		$tag_mysp_file_separ = $pparams->get('tag_mysp_file_separ', ' ');
		$chaine_files = '';
		if ($uploadadmin == 1 && $uploadimg == 1) { // May be add optional in the future
			require_once JPATH_ROOT.DS.'components'.DS.'com_myjspace'.DS.'helpers'.DS.'util.php';
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
			$return = $uri->toString();
			if ($pparams->get('url_login_redirect', '')) 
				$url = $pparams->get('url_login_redirect', '');
			else {
				if (version_compare(JVERSION, '1.6.0', 'ge'))
					$url = 'index.php?option=com_users&view=login';
				else
					$url = 'index.php?option=com_user&view=login';
				$url .= '&return='.base64_encode($return); // To redirect to the originally call page
				$url = Jroute::_($url, false);
			}
 
			if ($user_actual->id != 0)// if not registered
				$content = preg_replace('!\[register\](.+)\[/register\]!isU', '$1', $content);
			else // If registered
				$content = preg_replace('!\[register\](.+)\[/register\]!isU', JText::sprintf('COM_MYJSPACE_REGISTER', $url), $content);		
		}			

		$prefix = '';
		$suffix = '';

		// Force default dates
		if ($pparams->get('publish_mode',2) == 0) { // do not take into account the dates
			$user_page->publish_up = '0000-00-00 00:00:00';
			$user_page->publish_down = '0000-00-00 00:00:00';
		}
		if ($user_page->publish_down == '0000-00-00 00:00:00')
			$user_page->publish_down = date('Y-m-d 00:00:00',strtotime("+1 day"));		

		// Specific context
		$aujourdhui = time();
		if ($user_page->blockView == null) {
//			$content = JText::_('COM_MYJSPACE_PAGENOTFOUND');
			$content = '';
		} else if ($user_page->blockView == 0 && $user_actual->id != $user_page->userid) {
//			$content = JText::_('COM_MYJSPACE_PAGEBLOCK');
			$content = '';
		} else if ($user_page->blockView == 2 && $user_actual->username == "") {
//        require_once JPATH_ROOT.DS.'components'.DS.'com_myjspace'.DS.'helpers'.DS.'util_acl.php';
//			$content = JText::sprintf('COM_MYJSPACE_PAGERESERVED', get_assetgroup_label($user_page->blockView));
			$content = '';
		} else if ($user_page->content == null) {
//			$content = JText::_('COM_MYJSPACE_PAGEEMPTY');
			$content = '';
		} else if (strtotime($user_page->publish_up) > $aujourdhui || strtotime($user_page->publish_down) <= $aujourdhui) {
//			$content = JText::_('COM_MYJSPACE_PAGEUNPLUBLISHED');
			$content = '';
		} else {
			// Top and bottom
			if ($pparams->get('page_prefix', ''))
				$prefix = '<span class="top_myjspace">'.$user_page->traite_prefsuf($pparams->get('page_prefix', ''), $user, $page_increment, JText::_('COM_MYJSPACE_DATE_FORMAT'), $chaine_files, $Itemid, true).'</span><br />';
			if ($pparams->get('page_suffix', '#bsmyjspace'))
				$suffix = '<span class="bottom_myjspace">'.$user_page->traite_prefsuf($pparams->get('page_suffix', '#bsmyjspace'), $user, $page_increment, JText::_('COM_MYJSPACE_DATE_FORMAT'), $chaine_files, $Itemid, true).'</span><br />';
		}			

		if ($pagebreak == 0) {
			$regex = '#<hr([^>]*?)class=(\"|\')system-pagebreak(\"|\')([^>]*?)\/*>#iU';
			$content = preg_replace( $regex, '<br />', $content);
		}

		if ($content)
			$retour = '<div class="myjspace-prefix">'.$prefix.'</div><div class="myjspace-content"></div>'.$content.'<div class="myjspace-suffix">'.$suffix.'</div>';

		return $retour;
	}

}
		
?>
