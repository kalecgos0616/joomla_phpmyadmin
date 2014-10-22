<?php
/**
* @version $Id: script.myjspace.php $
* @version		2.3.4 15/04/2014
* @package		com_myjspace
* @author       Bernard Saulmé
* @copyright	Copyright (C) 2012-2013 Bernard Saulmé
* @license      GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
*/
 
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die;
if (!defined('DS')) define('DS', DIRECTORY_SEPARATOR);
define('__ROOTINSTALL__', dirname(__FILE__));
		
class com_myjspaceInstallerScript
{
	public function __construct($installer)
	{
		$this->installer = $installer;
	}
 
	/* Method for postinstall */
	public function postflight($type, $parent) {

		// Installation & migrations
		require_once __ROOTINSTALL__.DS.'install.myjspace.php';
		jimport('joomla.access.rules');

		$db	= JFactory::getDBO();
		
		// J! >= 1.6 ACL
		$query = "SELECT COUNT(*) FROM `#__assets` WHERE `title` = 'com_myjspace' AND `name` = 'com_myjspace' AND `rules` LIKE '%user.%'";
		$db->setQuery($query);
		$db->query();
		$count = $db->loadResult();	
		if (!isset($count))
			return false;
			
		if ($count == 0) { // No Rules => Store the default ACL rules into the database
			$defaultRules = array(
				'core.admin' => array(),
				'core.manage' => array(),
				'user.config' => array('2' => 1),
				'user.delete' => array('2' => 1),
				'user.edit' => array('2' => 1),
				'user.myjspace' => array('1' => 1, '2' => 1),
				'user.search' => array('1' => 1, '2' => 1),
				'user.see' => array('1' => 1, '2' => 1),
				'user.pages' => array('1' => 1, '2' => 1)
			);

			if (version_compare(JVERSION, '2.5.6', 'ge'))
				$rules	= new JAccessRules($defaultRules);
			else
				$rules	= new JRules($defaultRules);

			$asset	= JTable::getInstance('asset');

			if (!$asset->loadByName('com_myjspace')) {
				$root = JTable::getInstance('asset');
				$root->loadByName('root.1');
				$asset->name = 'com_myjspace';
				$asset->title = 'com_myjspace';
				$asset->setLocation($root->id, 'last-child');
			}
			$asset->rules = (string)$rules;
			
	        if (! $asset->check() || ! $asset->store()) { 
                $this->setError($asset->getError());
				return false;
			}
		} 

		// Migration to Myjspace 2.0.0 from older version with ACL but not with 'user.pages'
		$query = "SELECT COUNT(`rules`) FROM `#__assets` WHERE `title` = 'com_myjspace' AND `name` = 'com_myjspace' AND `rules` LIKE '%user.pages%'";
		$db->setQuery($query);
		$db->query();
		$count = $db->loadResult();	
		if (!isset($count))
			return false;

		if ($count == 0) {
			echo "Added the new ACL rules (since 2.0.0) 'user.pages' (for pages list) to allow users to use it.<br/>";

			$asset	= JTable::getInstance('asset');
			if (!$asset->loadByName('com_myjspace')) {
				return false;
			}
			$new_rules = '"user.pages":{"1":1,"2":1}';
			$asset->rules = str_replace('}}', '},'.$new_rules.'}', $asset->rules);

	        if (! $asset->check() || ! $asset->store()) { 
                $this->setError($asset->getError());
				return false;
			}
		}

		// Tags for J!3.1 add MyJspace content_type
		if (version_compare(JVERSION, '3.1.4', 'ge')) {
			$query = "SELECT COUNT(`type_alias`) FROM `#__content_types` WHERE `type_alias` in ('com_myjspace.see', 'com_myjspace.category')";
			$db->setQuery($query);
			$db->query();
			$count = $db->loadResult();	
			if (!isset($count))
				return false;

			if ($count == 0) {
				$query = "INSERT INTO `#__content_types` (`type_title`, `type_alias`, `table`, `rules`, `field_mappings`, `router`) VALUES ";
				$query .= "('MyJspace Page', 'com_myjspace.see', '{\"special\":{\"dbtable\":\"#__myjspace\",\"key\":\"id\",\"type\":\"Content\",\"prefix\":\"JTable\",\"config\":\"array()\"},\"common\":{\"dbtable\":\"#__ucm_content\",\"key\":\"ucm_id\",\"type\":\"Corecontent\",\"prefix\":\"JTable\",\"config\":\"array()\"}}', '', '{\"common\":{\"core_content_item_id\":\"id\",\"core_title\":\"title\",\"core_state\":\"blockView\",\"core_alias\":\"pagename\",\"core_created_time\":\"created_date\",\"core_modified_time\":\"last_update_date\",\"core_body\":\"content\", \"core_hits\":\"hits\", \"core_publish_up\":\"publish_up\",\"core_publish_down\":\"publish_down\",\"core_access\":\"blockView\", \"core_params\":\"null\", \"core_featured\":\"null\", \"core_metadata\":\"metadata\", \"core_language\":\"language\", \"core_images\":\"null\", \"core_urls\":\"null\", \"core_version\":\"null\", \"core_ordering\":\"null\", \"core_metakey\":\"metakey\", \"core_metadesc\":\"null\", \"core_catid\":\"catid\", \"core_xreference\":\"null\", \"asset_id\":\"null\"}, \"special\": {}}', 'MyJspaceHelperRoute::getMyJspaceRoute'),";
				$query .= "('MyJspace Category', 'com_myjspace.category', '{\"special\":{\"dbtable\":\"#__categories\",\"key\":\"id\",\"type\":\"Category\",\"prefix\":\"JTable\",\"config\":\"array()\"},\"common\":{\"dbtable\":\"#__ucm_content\",\"key\":\"ucm_id\",\"type\":\"Corecontent\",\"prefix\":\"JTable\",\"config\":\"array()\"}}', '', '{\"common\":{\"core_content_item_id\":\"id\",\"core_title\":\"title\",\"core_state\":\"published\",\"core_alias\":\"alias\",\"core_created_time\":\"created_time\",\"core_modified_time\":\"modified_time\",\"core_body\":\"description\", \"core_hits\":\"hits\",\"core_publish_up\":\"null\",\"core_publish_down\":\"null\",\"core_access\":\"access\", \"core_params\":\"params\", \"core_featured\":\"null\", \"core_metadata\":\"metadata\", \"core_language\":\"language\", \"core_images\":\"null\", \"core_urls\":\"null\", \"core_version\":\"version\", \"core_ordering\":\"null\", \"core_metakey\":\"metakey\", \"core_metadesc\":\"metadesc\", \"core_catid\":\"parent_id\", \"core_xreference\":\"null\", \"asset_id\":\"asset_id\"}, \"special\": {\"parent_id\":\"parent_id\",\"lft\":\"lft\",\"rgt\":\"rgt\",\"level\":\"level\",\"path\":\"path\",\"extension\":\"extension\",\"note\":\"note\"}}', 'ContentHelperRoute::getCategoryRoute')";
				$db->setQuery($query);
				$db->query();
			}
		}
		return true;
	}


	/* Method to uninstall the component */
	function uninstall($parent) {

		$db	= JFactory::getDBO();
		$pparams = JComponentHelper::getParams('com_myjspace');

		if ($pparams->get('uninstall_tables', 0)) { // Drop tables & content

			if (version_compare(JVERSION, '3.1.4', 'ge')) { // Delete the J! Tags contents
				$query = "DELETE FROM `#__content_types` WHERE `type_alias` = 'com_myjspace.see'";
				$db->setQuery($query);
				$db->query();

				$query = "DELETE FROM `#__content_types` WHERE `type_alias` = 'com_myjspace.category'";
				$db->setQuery($query);
				$db->query();

				$query = "DELETE FROM `#__contentitem_tag_map` WHERE `type_alias` = 'com_myjspace.see'";
				$db->setQuery($query);
				$db->query();

				$query = "DELETE FROM `#__contentitem_tag_map` WHERE `type_alias` = 'com_myjspace.category'";
				$db->setQuery($query);
				$db->query();

				$query = "DELETE FROM `#__ucm_base` WHERE `ucm_id` IN (SELECT `core_content_id` FROM `#__ucm_content` WHERE `core_type_alias` = 'com_myjspace.see')";
				$db->setQuery($query);
				$db->query();

				$query = "DELETE FROM `#__ucm_content` WHERE `core_type_alias` = 'com_myjspace.see'";
				$db->setQuery($query);
				$db->query();

				echo "<p>Deleted the Joomla tags</p>";
			}
	
			$query = "DROP TABLE `#__myjspace`";
			$db->setQuery($query);
			$db->query();

			echo "<p>Dropped the pages table</p>";

			if ($pparams->get('link_folder', 1))
				echo "<p>Please drop <span style=\"color:red;\">manually</span> the pages root folder (files and sub-folfers) : /".$pparams->get('foldername', 'myjsp')."</p>";

		} else {
			echo "<p>Recreating a #__myjspace_cfg table and it's content to keep in 'mind' the root folder name ...<br /></p>";

			$foldername = $pparams->get('foldername', 'myjsp');

			$query = "CREATE TABLE IF NOT EXISTS `#__myjspace_cfg` ( `foldername` varchar(100) NOT NULL, PRIMARY KEY (`foldername`) ) ENGINE=MyISAM DEFAULT CHARSET=utf8";
			$db->setQuery($query);
			$db->query();

			$query = "DELETE FROM `#__myjspace_cfg`";
			$db->setQuery($query);
			$db->query();

			$query = "INSERT INTO `#__myjspace_cfg` (`foldername`) VALUES (".$db->Quote($pparams->get('foldername', 'myjsp')).");";
			$db->setQuery($query);
			$db->query();
?>
	<b><u>bye bye :-(</u></b><br /><br />
	BS MyJspace tables (with user's data) and folder were not deleted during this uninstall process<br />
	If you don't want theses: delete manually folder '<?php echo $foldername; ?>' and tables #__myjspace and #__myjspace_cfg<br />
	</p>
<?php			
		}
	}
}

?>
