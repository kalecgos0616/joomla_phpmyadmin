<?php
// com_myjspace
// Format:10
// Pagename:admin
// id:1
//
define('_JEXEC', 1);
define('DS', DIRECTORY_SEPARATOR);
define('JPATH_BASE', '/home/bear/joomla_phpmyadmin');

if (!@file_exists(JPATH_BASE.DS.'includes'.DS.'defines.php')) {
	echo "<html><body>Please ask to your administrator to re-create all pages index files (using BS MyJspace back-end tools)</body></html>";
	exit;
}

require_once(JPATH_BASE.DS.'includes'.DS.'defines.php');
require_once(JPATH_BASE.DS.'includes'.DS.'framework.php');

if (!@file_exists(JPATH_BASE.DS.DS.'components'.DS.'com_myjspace'.DS.'helpers'.DS.'user.php')) {
	echo "<html><body>Component BS MyJspace is requested</body></html>";
	exit;
}
	
$app = JFactory::getApplication('site');
$app->initialise();

$menu = $app->getMenu();
$defaultMenu = $menu->getDefault();
$itemid = $defaultMenu->id;
$itemid = get_menu_itemid2('index.php?option=com_myjspace&view=see', $itemid);
$itemid = get_menu_itemid2('index.php?option=com_myjspace&view=see&id=&pagename=', $itemid);

$url_tmp = "index.php?option=com_myjspace&view=see&pagename=admin";
if ($itemid != 0)
	$url_tmp .= '&Itemid='.$itemid;

$s = empty($_SERVER['HTTPS']) ? '': ($_SERVER['HTTPS'] == 'on') ? 's' : '';
$url = 'http'.$s.'://'.$_SERVER['HTTP_HOST'].''.str_replace(JURI::base(true), '', JRoute::_($url_tmp, false));

if (!headers_sent())
	header("location: $url");
echo "<html><body><a href=".$url.">admin</a><script type=\"text/javascript\">window.location.href='".$url."'</script></body></html>";

function get_menu_itemid2($url = '', $default = 0) {
	$app = JFactory::getApplication();
	$menu = $app->getMenu();
	$menu_items = $menu->getItems('link', $url);

	if (count($menu_items) >= 1)
		return $menu_items[0]->id;

	return $default;
}
?>
