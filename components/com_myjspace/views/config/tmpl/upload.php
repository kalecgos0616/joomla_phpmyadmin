<?php
/**
* @version $Id: upload.php $
* @version		2.4.2 25/08/2014
* @package		com_myjspace
* @author       Bernard Saulmé
* @copyright	Copyright (C) 2013-2014 Bernard Saulmé
* @license      GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die;
if (!defined('DS')) define('DS', DIRECTORY_SEPARATOR);

$document = JFactory::getDocument();

// Do not use the website template (frontend)
if (JFactory::getApplication()->isSite() && version_compare(JVERSION, '3.2.0', 'lt')) {
	if (version_compare(JVERSION, '1.6.0', 'ge'))
		$template = 'atomic';
	else
		$template = 'ja_purity';
	$app = JFactory::getApplication()->setTemplate($template);
}

// Editor skin usage for myjsp
if ($this->editor_selection == 'myjsp')
	$document->addStyleSheet(JURI::root(true).'/plugins/editors/myjsp/tiny_mce/skins/'.$this->skin.'/skin.min.css');

$document->addStyleSheet(JURI::root(true).'/components/com_myjspace/assets/myjspace.min.css');

if (@file_exists(JPATH_COMPONENT_SITE.DS.'assets'.DS.'myjsp_editor_'.$this->editor_selection.'.js'))
	$document->addScript(JURI::root(true).'/components/com_myjspace/assets/myjsp_editor_'.$this->editor_selection.'.js');
else
	$document->addScript(JURI::root(true).'/components/com_myjspace/assets/myjsp_editor_none.js');
$document->addScript(JURI::root(true).'/components/com_myjspace/assets/myjsp_editor.js');
$document->addScriptDeclaration("\n".'page_url = "'.$this->user_page->foldername.'/'.$this->user_page->pagename.'/'.'";');
?>

<div class="myjsp-upload">
<?php if ($this->user_page->blockEdit != 2 && $this->alert_root_page == 0) {
		if ($this->uploadadmin && ($this->uploadimg > 0 || $this->uploadmedia > 0)) {
//			if ($this->type != 'undefined' || $this->uploadmedia != 1) {
			if ($this->type != 'undefined') {
?>
	<fieldset class="mce-fieldset">
		<form  method="post" onsubmit="ClosePluginPopup(page_url+this.select_file.value); return false;" action="<?php echo str_replace('&', '&amp;', JFactory::getURI()->toString()); ?>" >
			<select name="select_file" id="select_file" size="1">
				<option value="" selected="selected"><?php echo JText::_('COM_MYJSPACE_UPLOADCHOOSE') ?></option>
				<?php
					$nb = count($this->tab_list_file);	
					for ($i = 0 ; $i < $nb ; ++$i) {
						$chaine_tmp = $this->tab_list_file[$i];
						if (strlen($chaine_tmp) > 25)
							$chaine_tmp = substr($chaine_tmp, 0 , 25).'...';
						echo '<option value="'.utf8_encode($this->tab_list_file[$i]).'">'.utf8_encode($chaine_tmp)."</option>\n";
					}
				?>
			</select>
			<input type="submit" value="<?php echo JText::_('COM_MYJSPACE_UPLOADCHOOSE0') ?>" class="button btn mjp-config" />
		</form>		
	</fieldset>
<?php		}	?>

	<fieldset class="mce-fieldset">
		<form method="post" onsubmit="PluginReturnUrl(page_url+basename(this.upload_file.value)); return true;" action="<?php echo JRoute::_('index.php'); ?>" enctype="multipart/form-data" >
			<input name="option" type="hidden" value="com_myjspace" />
			<input name="task" type="hidden" value="upload_file" />
			<input name="layout" type="hidden" value="upload" />
			<input name="id" type="hidden" value="<?php echo $this->user_page->id; ?>" />
			<input type="file" id="upload_file" name="upload_file" />
			<input type="hidden" name="MAX_FILE_SIZE" value="<?php echo $this->file_max_size; ?>" />
			<input type="submit" class="button btn mjp-config" value="<?php echo JText::_('COM_MYJSPACE_UPLOADUPLOAD') ?>" onclick="document.getElementById('progress_div').style.visibility='visible';" />
			<?php echo JHTML::_('form.token'); ?>
			<div id="progress_div" class="progress_div"><img src="components/com_myjspace/assets/progress.gif" alt="..." /></div>
		</form>
	</fieldset>

	<fieldset class="mce-fieldset">
		<form method="post" action="<?php echo JRoute::_('index.php'); ?>" >
			<input name="option" type="hidden" value="com_myjspace" />
			<input name="task" type="hidden" value="delete_file" />
			<input name="layout" type="hidden" value="upload" />
			<input name="id" type="hidden" value="<?php echo $this->user_page->id; ?>" />
			<select name="delete_file" id="delete_file" size="1">
				<option value="" selected="selected"><?php echo JText::_('COM_MYJSPACE_UPLOADCHOOSE') ?></option>
				<?php
					$nb = count($this->tab_list_file);	
					for ($i = 0 ; $i < $nb ; ++$i) {
						$chaine_tmp = $this->tab_list_file[$i];
						if (strlen($chaine_tmp) > 25)
							$chaine_tmp = substr($chaine_tmp, 0 , 25).'...';
						echo '<option value="'.utf8_encode($this->tab_list_file[$i]).'">'.utf8_encode($chaine_tmp)."</option>\n";
					}
				?>
			</select>
			<input type="submit" value="<?php echo JText::_('COM_MYJSPACE_UPLOADDELETE') ?>" class="button btn mjp-config" />
			<?php echo JHTML::_('form.token'); ?>
		</form>
	</fieldset>
</div>
<?php
		}
   }
?>
