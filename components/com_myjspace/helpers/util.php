<?php
/**
* @version $Id: util.php $
* @version		2.4.1 14/07/2014
* @package		com_myjspace
* @author       Bernard Saulmé
* @copyright	Copyright (C) 2010-2011-2012-2013-2014 Bernard Saulmé
* @license      GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
*/

// Pas d'accès direct
defined('_JEXEC') or die;
if (!defined('DS')) define('DS', DIRECTORY_SEPARATOR);

// Dir size or number of files (0 size, 1 = number)
function dir_size($folder = '', $allowed_types = array('*'), $forbiden_files = array('.', '..', 'index.html', 'index.htm', 'index.php'))
{
	$oldfolder = getcwd();
	if (!@chdir($folder))
		return array(0, 0);

	$size = 0;
	$nb = 0;

	$dir = @opendir('.');
	while (false !== ($File = @readdir($dir))) {
		$path_parts = strtolower(pathinfo($File, PATHINFO_EXTENSION));
		if (!@is_dir($File) && !in_array(strtolower($File), $forbiden_files) && ($allowed_types[0] == '*' || in_array($path_parts, $allowed_types))) {
			$size += filesize($File);
			$nb += 1;
		}
	}
	@closedir($dir);
	@chdir($oldfolder);

	return array($nb, $size);
}


// Resize image to size max $ResizeSizeX * ResizeSizeY
function resize_image($uploadedfile = '', $ResizeSizeX = 0, $ResizeSizeY = 0, $ActualFileName = '')
{
	if ($ResizeSizeX <= 0 && $ResizeSizeY <= 0) // Nothing to do !
		return false;

	if (!function_exists("gd_info"))
		return false;
			
	$bigint = 1000000;
	try
	{
		list($Originalwidth, $Originalheight, $image_type) = @getimagesize($uploadedfile); // get current image size
		switch ($image_type) {
			case 1: $src = imagecreatefromgif($uploadedfile); break;
			case 2: $src = imagecreatefromjpeg($uploadedfile); break;
			case 3: $src = imagecreatefrompng($uploadedfile); break;
			default: return false; break;
		}

		// Overwrite 0 = unlimited !
		if ($ResizeSizeX == 0)
			$ResizeSizeX = $bigint;
		if ($ResizeSizeY == 0)
			$ResizeSizeY = $bigint;

		if  ($Originalwidth <= $ResizeSizeX && $Originalheight <= $ResizeSizeY)
			return false; // Too small, dont resize !
			
		if ($Originalwidth > $ResizeSizeX)
			$ratioX = $ResizeSizeX/$Originalwidth;
		else
			$ratioX = $bigint;
		if ($Originalheight > $ResizeSizeY)
			$ratioY = $ResizeSizeY/$Originalheight;
		else
			$ratioY = $bigint;
		$ratio = min($ratioX, $ratioY);

		$ResizeSizeX = intval($ratio * $Originalwidth);
		$ResizeSizeY = intval($ratio * $Originalheight);
		
		$tmp = imagecreatetruecolor($ResizeSizeX, $ResizeSizeY);													// create new image with calculated dimensions	
		imagecopyresampled($tmp, $src, 0, 0, 0, 0, $ResizeSizeX, $ResizeSizeY, $Originalwidth, $Originalheight);	// resize the image and copy it into $tmp image	
		switch ($image_type) {
			case 1: imagegif($tmp, $ActualFileName); break;
			case 2: imagejpeg($tmp, $ActualFileName, 85); break;
			case 3: imagepng($tmp, $ActualFileName, 3); break;
			default: return false; break;
		}
		
		imagedestroy($src);
		imagedestroy($tmp); // Note: PHP will clean up the temp file it created when the request has completed.				
	}
	catch(Exception $e) 
	{
		echo $e;
		return false;
	}
	return true;
}


// Directory list of files
// $rep folder, $allowed_types : $allowed tab of file types (* all), $forbiden_files : tab forbidden file 
function list_file_dir($rep = '', $allowed_types = null,  $sort = 0, $forbiden_files = array('.', '..', '.htaccess'), $forbiden_types = array('htm', 'html', 'php')) {
	
	$tab_retour = array();

	if ($rep == '')
		return $tab_retour;
		
	$oldfolder = getcwd();
	if (!@chdir($rep))
		return $tab_retour;
	
	if ($dir = @opendir('.')) {
		while (false !== ($File = @readdir($dir))) {
			$path_parts = strtolower(pathinfo($File, PATHINFO_EXTENSION));
			if (!@is_dir($File) && ($allowed_types[0] == '*' || in_array($path_parts, $allowed_types)) && !in_array($path_parts, $forbiden_types) && !in_array(strtolower($File), $forbiden_files)) {
				$tab_retour[] = $File;
			}
		}
		@closedir($dir);
	}
	
	@chdir($oldfolder);
	
	if ($sort == 1)
		sort($tab_retour);
		
	return $tab_retour;
}


// Replace some BBcode with HTML equivalent
function bs_bbcode(&$text = null, $width = null, $height = null)
{
  if ($text == null)
	return null;
	
  // Workaround Test due to php bug : #45488 'preg_replace failed when the $subject parameter length exceeds 100009'
  // Test to 92160 (90 ko) to keep margin to all replace due to several call to preg_replace
  if (strlen($text) > 92160)
	return $text;

 // [img]
 $taille_tmp = '';
 if ($width != null)
	$taille_tmp .= ' width="'.$width.'" ';
 if ($height != null)
	$taille_tmp .= ' height="'.$height.'" ';
 $text = preg_replace('!\[img\](.+)\[/img\]!isU', '<a href="$1" rel="lightbox[group]"><img src="$1" '.$taille_tmp.' alt="" /></a>', $text);
 $text = preg_replace('!\[img=(.+)x(.+)\](.+)\[/img\]!isU', '<a href="$3" rel="lightbox[group]"><img width="$1" height="$2" src="$3" alt="" /></a>', $text);
 $text = preg_replace('!\[img size=(.+)x(.+)\](.+)\[/img\]!isU', '<a href="$3" rel="lightbox[group]"><img width="$1" height="$2" src="$3" alt="" /></a>', $text);
 $text = preg_replace('!\[img width=(.+) height=(.+)\](.+)\[/img\]!isU', '<a href="$3" rel="lightbox[group]"><img width="$1" height="$2" src="$3" alt="" /></a>', $text);

 // [url]
 $text = preg_replace('!\[url\](.+)\[/url\]!isU', '<a href="$1" target="_blank">$1</a>', $text);
 $text = preg_replace('!\[url=([^\]]+)\](.+)\[/url\]!isU', '<a href="$1" target="_blank">$2</a>', $text);
  
 return($text);
}


// Send an Email
function send_mail($from = '', $to = '', $subject = '', $body = '') {

	$mailer = JFactory::getMailer();
	$config = JFactory::getConfig();

	if ($from == '') { // Default as server configuration
		if (version_compare(JVERSION, '3.0.0', 'ge'))
			$sender = array($config->get('config.mailfrom'), $config->get('config.fromname'));
		else
			$sender = array($config->getValue('config.mailfrom'), $config->getValue('config.fromname'));
	} else {
		$sender = explode(',', $from);
		if (count($sender) == 1)
			$sender[1] = 'Admin';
	}
	$mailer->setSender($sender);

	if ($to == '') { // Default as server configuration
		if (version_compare(JVERSION, '3.0.0', 'ge'))
			$recipient = array($config->get('config.mailfrom'));
		else
			$recipient = array($config->getValue('config.mailfrom'));
	} else {
		$recipient = explode(',', $to);
	}		
	$mailer->addRecipient($recipient);

	if ($subject == '') {
		if (version_compare(JVERSION, '3.0.0', 'ge'))
			$subject = JURI::base().' - '.$config->get('config.sitename');
		else
			$subject = JURI::base().' - '.$config->getValue('config.sitename');
	}
	$mailer->setSubject($subject);
	
	$mailer->setBody($body);
	
	$send = $mailer->Send();

	return $send;
}


// Convert Kb, Mb, Gb to Bytes
function convertBytes($value = 0) {
    if (is_numeric($value)) {
        return $value;
    } else {
        $value_length = strlen($value);
        $qty = substr($value, 0, $value_length - 1);
        $unit = strtolower(substr($value, $value_length - 1));
        switch ($unit) {
            case 'k':
                $qty *= 1024;
                break;
            case 'm':
                $qty *= 1048576;
                break;
            case 'g':
                $qty *= 1073741824;
                break;
        }
        return $qty;
    }
}


// Convert Bytes to Kb, Mb, Gb, Tb
function convertSize($bytes = 0)
{
    $types = array(JText::_('COM_MYJSPACE_UNIT_B'), JText::_('COM_MYJSPACE_UNIT_KB'), JText::_('COM_MYJSPACE_UNIT_MB'), JText::_('COM_MYJSPACE_UNIT_GB'), JText::_('COM_MYJSPACE_UNIT_TB')); // ( 'B', 'KB', 'MB', 'GB', 'TB' );

    for ($i = 0; $bytes >= 1024 && $i < (count($types)-1); $bytes /= 1024, $i++);

    return (round($bytes, 1)." ".$types[$i]);
}


// Check if the date is valid for the provided format, and return it to the format 'Y-m-d H:i:s' to save into the DB
// If KO, return 'now' except if date = ''
function valid_date($date_tmp = '', $date_fmt = 'Y-m-d H:i:s') {

	if ($date_tmp == '')
		return '';

	if (version_compare(PHP_VERSION, '5.3.0') >= 0) { // Using PHP >= 5.3.0 ?
		if ($date = DateTime::createFromFormat($date_fmt, $date_tmp))
			$madata = $date->format('Y-m-d H:i:s'); // DB format
		else
			$madata = date('Y-m-d H:i:s'); // 'now'
	} else { // Anything better for old version ? :-)
		$date_tmp = str_replace('/', '-', $date_tmp);
		$madata = date('Y-m-d H:i:s', strtotime($date_tmp));
	}
	
	// Double Check
	if ($madata == '1970-01-01' || $madata == '1970-01-01 00:00:00' || $madata == '0000-00-00' || $madata == '0000-00-00 00:00:00')
		$madata = '';
		
	return $madata;
}


// Return date converted. Keep the empty empty
function html_date_empty($my_date = null, $format = 'Y-m-d H:i:s') {

	if ($my_date == '0000-00-00 00:00:00' || $my_date == null)
		$my_date = '';
	else
		$my_date = date($format, strtotime($my_date));
			
	return $my_date;
}			


// Check is the image exists, if 'yes' return the HTML code to display it, else null
// $mode = 0 => Image Display
// $mode = 1 => Display a link on image to pre-display with Lytebox
// $mode = 2 => Page redirection
function exist_image_html($img_dir = null, $img_dir_prefix = JPATH_SITE, $class = 'img_preview', $mode = 0, $title = '', $img_name = 'preview.jpg', $def_img_name = '', $search_image_type = 2, $html = '', $video = 1, $url = '') {

	$retour = null;

	if ($search_image_type > 1) // Image exists into html ?
		if ($search_image_type == 5)
			$image_html = str_img_src($html, 2, $video);
		else
			$image_html = str_img_src($html, 1, $video);
	else
		$image_html = false;

	if ($img_dir != null && $search_image_type < 4 && @file_exists($img_dir_prefix.DS.$img_dir.DS.$img_name)) // Image file exists ?
		$image_file = $img_dir.'/'.$img_name;
	else
		$image_file = false;

	// default image
	if ($def_img_name != '')
		$image_url = $def_img_name;
	else
		$image_url = false;
		
	// Choice
	if ($search_image_type == 1) {
		if ($image_file)
			$image_url = $image_file;
	} else if ($search_image_type == 2) {
		if ($image_file)
			$image_url = $image_file;
		else if ($image_html)
			$image_url = $image_html;
	} else if ($search_image_type == 3) {
		if ($image_html)
			$image_url = $image_html;
		else if ($image_file)
			$image_url = $image_file;
	} else if ($search_image_type == 4 || $search_image_type == 5) {
		if ($image_html)
			$image_url = $image_html;
	} else {
		$image_url = false;
	}
		
	if ($image_url) {
		$alt = basename($image_url);
		if ($title == '')
			$title = $alt;

			if ($mode == 0)
				$retour = '<img src="'.$image_url.'" class="'.$class.'" title="'.$title.'" alt="'.$alt.'" />';
			else if ($mode == 1)
				$retour = '<a href="'.$image_url.'" class="lytebox" data-title="'.$title.'"><img src="'.$image_url.'" class="'.$class.'" title="'.$title.'" alt="'.$alt.'" /></a>';
			else
				$retour = '<a href="'.$url.'" ><img src="'.$image_url.'" class="'.$class.'" title="'.$title.'" alt="'.$alt.'" /></a>';
	}
	
	return $retour;
}


/*	*
	* Occurrences of an html <img> element in a string and extracts the src if it finds it
    * Returns boolean false in case <img> element is not found.
	* @param    string  $html 	HTML string
	*           integer $mode   1:first one, 2: random
	*			integer video   0: no video else use image video for Youtube or Dailymotion
	* @return   mixed           The contents of the src attribute if image found into <img> or boolean false if no <img>
	*/

function str_img_src(&$html, $mode = 1, $video = 1) {

	$matches_all = array();
	if ($video) {
		// Dailymotion.com
		$nb_matches_videos = preg_match_all('/dailymotion\.com\/embed\/video\/([\w\-]+)/', $html, $matches_videos);
		if ($nb_matches_videos > 0 && is_array($matches_videos)) { // Preview url
			for ($i=0; $i < $nb_matches_videos; $i++)
				$matches_all[] = 'http://www.dailymotion.com/thumbnail/video/'.$matches_videos[1][$i];
		};

		// Youtube.com
		$nb_matches_videos = preg_match_all('/youtube\.com\/(watch\?v=)?(v\/)?(embed\/)?([\w\-]+)/', $html, $matches_videos);
		if ($nb_matches_videos > 0 && is_array($matches_videos)) { // Preview url
			for ($i=0; $i < $nb_matches_videos; $i++)
				$matches_all[] = 'http://img.youtube.com/vi/'.$matches_videos[4][$i].'/0.jpg';
		};
	}

	// Image(s)
	$nb_matches = preg_match_all('/<img.+src=[\'"]([^\'"]+)[\'"].*>/Ui', $html, $matches);

	// Merge video & image
	$matches_all = array_merge($matches_all, $matches[1]);
	$nb_matches = count($matches_all);

	$position = ($mode == 1) ? 0 : rand(0, $nb_matches-1);
	if ($nb_matches > 0 && is_array($matches_all) && !empty($matches_all))
		return $matches_all[$position];
	else
		return false;
}


// Check if an editor exists and is enabled
function check_editor_selection($editor_selection = 'myjsp') {

	if ($editor_selection == '-') // 'default' editor
		return true;

	$plugin = JPluginHelper::getPlugin('editors', $editor_selection);
	if (!$plugin)
		return false;
	
	return true;
}


// Generate configuration report
function configuration_report()
{
	require_once JPATH_COMPONENT_SITE.DS.'helpers'.DS.'version.php';
	
	// Some ideas from Kunena to post on BS MyJspace forum
	if (ini_get('safe_mode')) {
		$safe_mode = '[u]safe_mode:[/u] [color=#FF0000]On[/color]';
	} else {
		$safe_mode = '[u]safe_mode:[/u] Off';
	}
	
	// Config
	$db	= JFactory::getDBO();
	$query = "SELECT version() AS ver";
	$db->setQuery($query);
	$db->query();
	$mysqlsersion = $db->loadResult();

	$app = JFactory::getApplication();
	
	if ($app->getCfg('sef')) {
		$jconfig_sef = 'Enabled';
	} else {
		$jconfig_sef = 'Disabled';
	}
	if ($app->getCfg('sef_rewrite')) {
		$jconfig_sef_rewrite = 'Enabled';
	} else {
		$jconfig_sef_rewrite = 'Disabled';
	}
	if (function_exists("gd_info"))
		$gd_support = 'Yes';
	else
		$gd_support = 'No';

	if (@file_exists(JPATH_ROOT.'/.htaccess')) {
		$htaccess = 'Exists';
	} else {
		$htaccess = 'None';
	}

	$file = JPATH_ROOT.DS.'components'.DS.'com_myjspace'.DS.'helpers'.DS.'util.xml';
	if (version_compare(JVERSION, '1.6.0', 'ge') && @file_exists($file)) {
		libxml_use_internal_errors(true);
		$xml = @simplexml_load_file($file);

		if (isset($xml->extension)) {
			$liste = '';
			foreach ($xml->extension as $value){
				$liste .= $db->Quote((string)$value).',';
			}
			$liste = rtrim($liste, ',');

			$query = "SELECT `element`, `type`, `folder`, `manifest_cache`, `enabled` FROM `#__extensions` WHERE `element` IN (".$liste.")";
			$db->setQuery($query);
			$db->query();
			$myelement_tab = $db->loadAssocList();

			$nbmyelement_tab = count($myelement_tab);
			$myelement = '';
			for ($i = 0 ; $i < $nbmyelement_tab ; $i++) {
				if ($i > 0)
					$myelement .= ' | ';
				$data = json_decode($myelement_tab[$i]['manifest_cache'], true);
				$myelement .= $myelement_tab[$i]['element'].':'.$myelement_tab[$i]['folder'].':'.$myelement_tab[$i]['type'].' '.$data['version'].' '.$data['creationDate'].' '.$myelement_tab[$i]['enabled'];
			}
		}
	}

	$template = $app->getTemplate();
	$template_user = '';
	if (version_compare(JVERSION, '1.6.0', 'ge')) {
		$query = "SELECT `template` FROM `#__template_styles` WHERE `home` = 1 AND `template` <> ".$db->Quote($template);
		$db->setQuery($query);
		$db->query();
		$db_template = $db->loadRow();
		if ($db_template)
			$template_user = ' user:'.implode(',', $db_template);
	}

	if (isset($_SERVER['HTTP_USER_AGENT']))
		$http_user_agent = $_SERVER['HTTP_USER_AGENT'];
	else
		$http_user_agent = 'undefined';

	$sef = array();
	$sef['sh404sef'] = getExtensionVersion('com_sh404sef', 'sh404sef');
	$sef['joomsef'] = getExtensionVersion('com_joomsef', 'ARTIO JoomSEF');
	$sef['acesef'] = getExtensionVersion('com_acesef', 'AceSEF');
	foreach ($sef as $id=>$item) {
		if (empty($item)) unset ($sef[$id]);
	}

	$retour = '[confidential][b]Joomla! version:[/b] '.JVERSION.' [b]Platform:[/b] '.$_SERVER['SERVER_SOFTWARE'].' ('.$_SERVER['SERVER_NAME'].') [b]PHP version:[/b] '.phpversion().' | '.$safe_mode
			.' | [b]MySQL version:[/b] '.$mysqlsersion.' | [b]Base URL:[/b] '.JURI::root().'[/confidential]';
			
	$retour .= ' [quote][b]Joomla! SEF:[/b] '.$jconfig_sef.' | [b]Joomla! SEF rewrite:[/b] '.$jconfig_sef_rewrite.' | [b]htaccess:[/b] '.$htaccess.' | [b]GD: [/b] '.$gd_support
			.' | [b]PHP environment:[/b] [u]Max execution time:[/u] '.ini_get('max_execution_time').' seconds | [u]Max execution memory:[/u] '
			.ini_get('memory_limit').' | [u]Max file upload:[/u] '.ini_get('upload_max_filesize').' [/quote] [quote][b]Joomla default template:[/b] admin:'.$template.$template_user.' [/quote]';

	$retour .= ' [quote] [b]http_user_agent:[/b] '.$http_user_agent.'[/quote]';

	$retour .= '[confidential][b]BS MyJSpace version:[/b] '.BS_Helper_version::get_xml_item('com_myjspace', 'creationDate').' | '.BS_Helper_version::get_xml_item('com_myjspace', 'author').' | '.BS_Helper_version::get_xml_item('com_myjspace', 'version').' | '.BS_Helper_version::get_xml_item('com_myjspace', 'build');

	if (version_compare(JVERSION, '1.6.0', 'ge') && isset($myelement)) 
		$retour .= ' [quote][b]BS MyJSpace elements:[/b] '.$myelement.'[/quote]';

	if (!empty($sef)) $retour .= ' [quote][b]Extra (checked) SEF components:[/b] '.implode(' | ', $sef).' [/quote]';
	else $retour .= ' [quote][b]Extra (checked) SEF components:[/b] None [/quote]';

	$retour .= '[/confidential]';

	return $retour;
}


function getExtensionVersion($extension, $name) {
	$version = BS_Helper_version::get_xml_item($extension, 'version');
	return $version ? '[u]'.$name.'[/u] '.$version : '';
}


// check if valid item it for the url
// return default if provide (optionaly only_valid_default_return)
function get_menu_itemid($url = '', $default = 0, $catid = 0, $only_valid_default_return = true) {

	$app = JApplication::getInstance('site');
	$menu = $app->getMenu();

	if ($menu) {
		$menu_items = $menu->getItems('link', $url);
		
		if ($catid) {
			$values = array();
			foreach ($menu_items as $k => $v) { // take the last one
				$params = $menu->getParams($menu_items[$k]->id);
				if ($params->get('catid') == $catid) {
					$values[$k] = $menu_items[$k]->id;
				}
			}

			foreach ($values as $i => $v) { // If the default is included into the list
				if ($values[$i] == $default) {
					return $values[$i];
				}
			}

			if ($only_valid_default_return)
				return $default;
			else
				return 0;
		}
	} else 
		return 0;

	if (count($menu_items) >= 1) {
		foreach ($menu_items as $i => $v) { // If the default is included into the list
			if ($menu_items[$i]->id == $default && $menu_items[$i]->component == 'com_myjspace') {
				return $menu_items[$i]->id;
			}
		}
		if ($menu_items[$i]->component == 'com_myjspace')
			return $menu_items[$i]->id; // else take the last
	}

	if ($only_valid_default_return)
		return $default;
	else
		return 0;
}


// User IP Address ... may be to much complex for old servers config & proxy
function addr_ip() {
	if (isset($_SERVER)) {
		if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			$realip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		} elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
			$realip = $_SERVER['HTTP_CLIENT_IP'];
		} else {
			$realip = $_SERVER['REMOTE_ADDR'];
		}
	} else {
		if (getenv('HTTP_X_FORWARDED_FOR')) {
			$realip = getenv('HTTP_X_FORWARDED_FOR');
		} elseif (getenv('HTTP_CLIENT_IP')) {
			$realip = getenv('HTTP_CLIENT_IP');
		} else {
			$realip = getenv('REMOTE_ADDR');
		}
	}
	return $realip;
}


// Clean (delete html tag, #tags {myjsp ..})the html text for search display
// Only return a limit part of the content after clean-up
function clean_html_text($text = '', $contentLimit = 150, $uid = 0, $suffix = '...') {

	// Workaround for preg_replace & Perf improvement ... (shorter = faster), 90 ko (real limit is little bit bigger)
	$text = substr($text, 0, min(92160, 4 * $contentLimit)); // Perf improvement ... (shorter = faster)
	$text = str_replace('&nbsp;', ' ', $text);
	$text = strip_tags($text); // Html tags

	// Hide #Tags
	$search  = array('#userid', '#name', '#username', '#id', '#title', '#pagename', '#access', '#shareedit', '#lastupdate', '#lastaccess', '#createdate', '#description', '#category', '#bsmyjspace', '#modifiedby', '#language', '#fileslist', '#cbprofile', '#hits', '#jomsocial-profile', '#jomsocial-photos');
	$replace = array('...', '...', '...', '...', '...', '...', '...', '...', '...', '...', '...', '...', '...', '...', '...', '...', '...', '...', '...', '...', '...');
	$text = str_replace($search, $replace, $text);

	// Tag {myjsp ... }
	$text = preg_replace('!{myjsp (.+)\}!isU', '...', $text); // even if {} tags (deleted by search display function)

	// BBCode [register]
	if ($uid != 0) // if the user is registered
		$text = preg_replace('!\[register\](.+)\[/register\]!isU', '$1', $text);
	else // if not registered
		$text = preg_replace('!\[register\](.+)\[/register\]!isU', '...', $text); // Keep it secret :-)
	
	// Length
	$text = clean_text($text, $contentLimit, $suffix) ;

	return $text;
}


function clean_text($text = '', $contentLimit = 150, $suffix = '...') {

	if (strlen($text) <= $contentLimit)
		return $text;

	$text = substr($text, 0, $contentLimit - strlen($suffix)).$suffix;

	return $text;
}


// Delete message from the Joomla queue ($app->getMessageQueue() may be add to check if msg before calling)
function _killMessage($app, $error) {
        $appReflection = new ReflectionClass(get_class($app));
        $_messageQueue = $appReflection->getProperty('_messageQueue');
        $_messageQueue->setAccessible(true);
        $messages = $_messageQueue->getValue($app);

        foreach($messages as $key=>$message) {
                if ($message['message'] == $error) {
                        unset($messages[$key]);
                }
        }
        $_messageQueue->setValue($app,$messages);
}

// Is the component, module or plugin set (end enabled)
// For J1.6+, return 1 for J!1.5

function isset_cmp($element, $type) {
	if (version_compare(JVERSION, '1.6.0', 'lt'))
		return 1;
		
  	$db	= JFactory::getDBO();
	$query  = "SELECT COUNT(*) FROM `#__extensions` WHERE `element` = ".$db->Quote($element)." AND `type` = ".$db->Quote($type)." AND `enabled` = 1 ";
	$db->setQuery($query);
	return $db->loadResult();
}

?>
