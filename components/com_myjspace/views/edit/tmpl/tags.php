<?php
/**
* @version $Id: tags.php $
* @version		2.4.2 06/08/2014
* @package		com_myjspace
* @author       Bernard Saulmé
* @copyright	Copyright (C) 2012-2013-2014 Bernard Saulmé
* @license      GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die;
if (!defined('DS')) define('DS', DIRECTORY_SEPARATOR);

$document = JFactory::getDocument();
$document->addStyleSheet(JURI::root(true).'/components/com_myjspace/assets/myjspace.min.css');
if (version_compare(JVERSION, '1.6.0', 'ge'))
	$document->addStyleSheet(JURI::root(true).'/media/system/css/adminlist.css');

// Js code
$js = "
function insertTags(valeur_choix) {
	valeur_choix += ' ';
	window.parent.jInsertEditorText(valeur_choix, '".$this->e_name."');
	window.parent.SqueezeBox.close();
	return false;
}
	";

// Tags list
$tag_list = array('#userid', '#name', '#username', '#title', '#pagename', '#id', '#access', '#lastupdate', '#lastaccess', '#createdate', '#description', '#category', '#fileslist');
$tag_label = array(JText::_('COM_MYJSPACE_TAG_USERID'), 
					JText::_('COM_MYJSPACE_TAG_NAME'),
					JText::_('COM_MYJSPACE_TAG_USERNAME'),
					JText::_('COM_MYJSPACE_TAG_TITLE'),
					JText::_('COM_MYJSPACE_TAG_PAGENAME'),
					JText::_('COM_MYJSPACE_TAG_ID'),
					JText::_('COM_MYJSPACE_TAG_ACCESS'),
					JText::_('COM_MYJSPACE_TAG_LASTUPDATE'),
					JText::_('COM_MYJSPACE_TAG_LASTACCESS'),
					JText::_('COM_MYJSPACE_TAG_CREATEDATE'),
					JText::_('COM_MYJSPACE_TAG_DESCRIPTION'),
					JText::_('COM_MYJSPACE_TAG_CATEGORY'),
					JText::_('COM_MYJSPACE_TAG_FILESLIST'));

if ($this->share_page != 0) {
	$tag_list[] = '#shareedit';
	$tag_label[] = JText::_('COM_MYJSPACE_TAG_SHAREEDIT');
	$tag_list[] = '#modifiedby';
	$tag_label[] = JText::_('COM_MYJSPACE_TAG_MODIFIEDBY');
}

if ($this->language_filter != 0) {
	$tag_list[] = '#language';
	$tag_label[] = JText::_('COM_MYJSPACE_TAG_LANGUAGE');
}
		
if (@file_exists(JPATH_ROOT.DS.'components'.DS.'com_comprofiler')) { // Add CB
	$tag_list[] = '#cbprofile';
	$tag_label[] = JText::_('COM_MYJSPACE_TAG_CBPROFILE');
}

if (@file_exists(JPATH_ROOT.DS.'components'.DS.'com_community')) { // Add Jomsocial
	$tag_list[] = '#jomsocial-profile';
	$tag_label[] = JText::_('COM_MYJSPACE_TAG_JOOMSOCIALPROFILE');
	$tag_list[] = '#jomsocial-photos';
	$tag_label[] = JText::_('COM_MYJSPACE_TAG_JOOMSOCIALPHOTOS');
}

if ($this->allow_tag_myjsp_iframe == 1) { // Allow Tag myjsp iframe
	$tag_list[] = '{myjsp iframe URL}';
	$tag_label[] = JText::_('COM_MYJSPACE_TAG_MYJSP_IFRAME');
}

if ($this->allow_tag_myjsp_include == 1) {  // Allow Tag myjsp include
	$tag_list[] = '{myjsp include URL}';
	$tag_label[] = JText::_('COM_MYJSPACE_TAG_MYJSP_INCLUDE');
}

if (version_compare(JVERSION, '1.6.0', 'ge')) {
	$document->addScript('media/system/js/mootools-core.js');
	$document->addScript('media/system/js/core.js');
}
$document->addScript('media/system/js/caption.js');
$document->addScriptDeclaration($js);

?>
<div class="myjspace">
	<fieldset class="addtags front">
		<table class="adminlist">
		<tbody>
<?php
	for($i=0; $i < sizeof($tag_list); $i++) {
		$pos = $i%2;
		echo '<tr class="row'.$pos.'"><td>';
		echo '<a class="pointer" href="#" onclick="insertTags(\''.$tag_list[$i].'\');">'.$tag_label[$i]."</a>\n"; 
		echo '</td></tr>';
    } 
?>
		</tbody>
		</table>
	</fieldset>
</div>
