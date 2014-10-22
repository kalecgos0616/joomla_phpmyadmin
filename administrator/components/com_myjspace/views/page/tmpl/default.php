<?php
/**
* @version $Id: default.php $
* @version		2.4.1 12/07/2014
* @package		com_myjspace
* @author       Bernard Saulmé
* @copyright	Copyright (C) 2010-2011-2012-2013-2014 Bernard Saulmé
* @license      GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die;
if (!defined('DS')) define('DS', DIRECTORY_SEPARATOR);
		
JHTML::_('behavior.modal', 'a.modal_jform_created_by');
$document = JFactory::getDocument();
$document->addStyleSheet(JURI::root(true).'/components/com_myjspace/assets/myjspace.min.css');

if (version_compare(JVERSION, '1.6.0', 'ge'))
	$document->addScript(JURI::root(true).'/media/system/js/mootools-more.js');
if (count($this->associations) > 0)
	$document->addScript(JURI::root(true).'/components/com_myjspace/assets/association.js');
	
if ($this->publish_mode == 2)
	JHTML::_('behavior.calendar');

?>
<div class="myjspace">
	<form action="<?php echo JRoute::_('index.php'); ?>" method="post" name="adminForm" id="adminForm">
	<div class="col myjsp-w-45 fltlft">

		<fieldset class="adminform">
		<legend><?php echo JText::_('COM_MYJSPACE_LABELUSERDETAILS'); ?></legend>
			<table class="admintable">
				<tr>
					<td class="key">
						<label><?php echo JText::_('COM_MYJSPACE_PAGELINK'); ?></label>
					</td>
					<td>
						<a href="<?php echo $this->link ?>" target="_blank"><?php echo $this->link ?></a>
					</td>
				</tr>
				<tr>
					<td class="key">
						<label><?php echo JText::_('COM_MYJSPACE_TITLENAME'); ?></label>
					</td>
					<td>
						<input type="text" name="mjs_pagename" id="mjs_pagename" class="inputbox" size="40" value="<?php echo $this->user_page->title; ?>" />
					</td>
				</tr>
				<tr>
					<td class="key">
						<label><?php echo JText::_('COM_MYJSPACE_LABELPAGEID'); ?></label>
					</td>
					<td>
						<?php echo $this->user_page->id; ?>
					</td>
				</tr>
				<tr>
					<td class="key">
<?php if (version_compare(JVERSION, '1.6.0', 'ge')) { ?>
						<label><?php echo  JText::_('COM_MYJSPACE_LABELNAME'); ?></label>
<?php } else { ?>
						<label><?php echo  JText::_('COM_MYJSPACE_LABELUSERNAME'); ?></label>
<?php } ?>
					</td>
					<td>
						<div class="input-append">
<?php if (version_compare(JVERSION, '1.6.0', 'ge')) { ?>
							<input type="text" name="mjs_username2" id="mjs_username2" class="inputbox" size="40" value="<?php echo $this->username; ?>" disabled="disabled" />
<?php } else { ?>
							<input type="text" name="mjs_username" id="mjs_username" class="inputbox" size="40" value="<?php echo $this->username; ?>" />
<?php } ?>
							<input type="hidden" name="mjs_userid" id="mjs_userid" value="0" />
<?php if (version_compare(JVERSION, '1.6.0', 'ge') && version_compare(JVERSION, '3.0.0', 'lt')) { ?>
							<div class="button2-left">
								<div class="blank">
									<a class="modal_jform_created_by" title="Select User" href="index.php?option=com_users&amp;view=users&amp;layout=modal&amp;tmpl=component&amp;field=jform_created_by" rel="{handler: 'iframe', size: {x: 800, y: 500}}"><?php echo  JText::_('COM_MYJSPACE_LABELSELECTUSER'); ?></a>
								</div>
							</div>
<?php } else if (version_compare(JVERSION, '1.6.0', 'ge') && version_compare(JVERSION, '3.0.0', 'ge')) { ?>
							<a class="modal btn modal_association" title="Select User" href="index.php?option=com_users&amp;view=users&amp;layout=modal&amp;tmpl=component&amp;field=jform_created_by" rel="{handler: 'iframe', size: {x: 800, y: 500}}"><?php echo  JText::_('COM_MYJSPACE_LABELSELECTUSER'); ?></a>
<?php } ?>
						</div>
					</td>
				</tr>
				<tr>
					<td class="key">
						<label><?php echo JText::_('COM_MYJSPACE_LABELMETAKEY'); ?></label>
					</td>
					<td>
						<input type="text" name="mjs_metakey" id="mjs_metakey" class="inputbox" size="40" value="<?php echo $this->user_page->metakey; ?>" />
					</td>
				</tr>
				<tr>
					<td class="key">
						<label><?php echo JText::_('COM_MYJSPACE_LABELCREATIONDATE'); ?></label>
					</td>
					<td>
						<?php echo $this->user_page->create_date; ?>
					</td>
				</tr>
				<tr>
					<td class="key">
						<label><?php echo JText::_('COM_MYJSPACE_LABELLASTUPDATEDATE'); ?></label>
					</td>
					<td>
						<?php echo $this->user_page->last_update_date; ?>
					</td>
				</tr>
				<tr>
					<td class="key">
						<label><?php echo JText::_('COM_MYJSPACE_LABELLASTACCESSDATE'); ?></label>
					</td>
					<td>
						<?php echo $this->user_page->last_access_date; ?>
					</td>
				</tr>
				<tr>
					<td class="key">
						<label><?php echo JText::_('COM_MYJSPACE_LABELHITS'); ?></label>
					</td>
					<td>
						<?php echo $this->user_page->hits;
							if ($this->user_page->hits > 0) { ?>
						&nbsp;<input name="reset_hits" type="submit" class="button btn" value="<?php echo JText::_('COM_MYJSPACE_LABELHITSRESET'); ?>" onclick="document.getElementById('resethits').value='yes';this.form.submit();" />
						<input name="resethits" id="resethits" type="hidden" value="no" />
						<?php } ?>
					</td>
				</tr>
<?php				
	if ($this->publish_mode != 0) {
		$prefix_publish = (version_compare(JVERSION, '1.6.0', 'ge')) ? 'jform_' : '';
?>
				<tr>
					<td class="key">
						<label><?php echo JText::_('COM_MYJSPACE_LABELPUBLISHUP'); ?></label>
					</td>
					<td>
						<?php echo JHTML::_('calendar', $this->user_page->publish_up, $prefix_publish."publish_up", $prefix_publish."publish_up", JText::_('COM_MYJSPACE_DATE_CALENDAR2'), null).' '.$this->img_publish_up;; ?>
					</td>
				</tr>
				<tr>
					<td class="key">
						<label><?php echo JText::_('COM_MYJSPACE_LABELPUBLISHDOWN'); ?></label>
					</td>
					<td>
						<?php echo JHTML::_('calendar', $this->user_page->publish_down, $prefix_publish."publish_down", $prefix_publish."publish_down", JText::_('COM_MYJSPACE_DATE_CALENDAR2'), null).' '.$this->img_publish_down; ?>
					</td>
				</tr>						
<?php
	}
?>
				<tr>
					<td class="key">
						<label><?php echo JText::_('COM_MYJSPACE_TITLEMODEEDIT'); ?></label>
					</td>
					<td>
						<select name="mjs_mode_edit" id="mjs_mode_edit" size="1">
							<option value="0" <?php if ($this->user_page->blockEdit == 0) echo " selected='selected'"; ?> ><?php echo JText::_('COM_MYJSPACE_TITLEMODEEDIT0') ?></option>
							<option value="1" <?php if ($this->user_page->blockEdit == 1) echo " selected='selected'"; ?> ><?php echo JText::_('COM_MYJSPACE_TITLEMODEEDIT1') ?></option>
							<option value="2" <?php if ($this->user_page->blockEdit == 2) echo " selected='selected'"; ?> ><?php echo JText::_('COM_MYJSPACE_TITLEMODEEDIT2') ?></option>						</select>
					</td>
				</tr>
<?php
				if ($this->group_list) {
?>
				<tr>
					<td class="key">
						<label><?php echo JText::_('COM_MYJSPACE_TITLESHAREEDIT'); ?></label>
					</td>
					<td>
						<select name="mjs_share" id="mjs_share" size="1">
<?php
						if ($this->user_page->access == 0)
							echo "<option value=\"0\" selected=\"selected\">&nbsp;-</option>\n";
						else
							echo "<option value=\"0\">&nbsp;-</option>\n";

						foreach ($this->group_list as $value) {
							if ($value->value != 1) {
								if ($value->value == $this->user_page->access)
									echo '<option value="'.$value->value.'" selected="selected">'.'&nbsp;'.$value->text."</option>\n";
								else
									echo '<option value="'.$value->value.'">'.'&nbsp;'.$value->text."</option>\n";
							}
						}
?>
						</select>
					</td>
				</tr>
				<tr>
					<td class="key">
						<label><?php echo JText::_('COM_MYJSPACE_TITLEUPDATENAME'); ?></label>
					</td>
					<td>
						<?php echo $this->modified_by; ?>
					</td>
				</tr>
<?php
				}
?>				
				<tr>
					<td class="key">
						<label><?php echo JText::_('COM_MYJSPACE_TITLEMODEVIEW'); ?></label>
					</td>
					<td>
						<select name="mjs_mode_view" id="mjs_mode_view" size="1">
<?php
						foreach ($this->blockview_list as $value) {
							if ($value->value == $this->user_page->blockView)
								echo '<option value="'.$value->value.'" selected="selected">'.'&nbsp;'.$value->text."</option>\n";
							else
								echo '<option value="'.$value->value.'">'.'&nbsp;'.$value->text."</option>\n";
						}
?>
						</select>
					</td>
				</tr>
<?php
	$categories_count = count($this->categories);
	if ($categories_count > 0) {
?>
				<tr>
					<td class="key">
						<label><?php echo JText::_('COM_MYJSPACE_LABELCATEGORY'); ?></label>
					</td>
					<td>
						<select name="mjs_categories" id="mjs_categories" size="1">
<?php
							for ($i = 0; $i < $categories_count; $i++) {
								if ($this->categories[$i]['value'] == $this->user_page->catid)
									echo '<option value="'.$this->categories[$i]['value'].'" selected="selected">'.'&nbsp;'.str_repeat('- ',$this->categories[$i]['level']).$this->categories[$i]['text']."</option>\n";
								else
									echo '<option value="'.$this->categories[$i]['value'].'">'.'&nbsp;'.str_repeat('- ',$this->categories[$i]['level']).$this->categories[$i]['text']."</option>\n";
							}
?>
						</select>
					</td>
				</tr>
<?php
	}

	$language_list_count = count($this->language_list);
	if ($language_list_count > 0) {
?>
				<tr>
					<td class="key">
						<label><?php echo JText::_('COM_MYJSPACE_LABELLANGUAGE'); ?></label>
					</td>
					<td>
						<select name="mjs_language" id="mjs_language" size="1">
<?php
							for ($i = 0; $i < $language_list_count; $i++) {
								if ($this->language_list[$i]->lang_code == $this->user_page->language)
									echo '<option value="'.$this->language_list[$i]->lang_code.'" selected="selected">'.'&nbsp;'.$this->language_list[$i]->lang_code."</option>\n";
								else
									echo '<option value="'.$this->language_list[$i]->lang_code.'">'.'&nbsp;'.$this->language_list[$i]->lang_code."</option>\n";
							}
?>
						</select>
					</td>
				</tr>		
<?php
		if (count($this->associations) > 0) {
			foreach ($this->associations as $tag => $pagename) {
?>
				<tr>
					<td class="key">
						<label><?php echo JText::_( 'COM_MYJSPACE_LABELASSOCIATION'); ?></label>
					</td>
					<td>
						<div class="input-append">
							<input type="text" class="input-medium" id="jform_associations_<?php echo $tag; ?>_name" value="<?php echo $pagename; ?>" disabled="disabled" size="35" />
								<a class="modal btn modal_association myjspbtn" title="<?php echo JText::_('COM_MYJSPACE_LABELASSOCIATION'); ?>" href="index.php?option=com_myjspace&amp;view=pages&amp;layout=modal&amp;tmpl=component&amp;field=pagename&amp;association=<?php echo $tag; ?>" rel="{handler: 'iframe', size: {x: 800, y: 500}}"><i class="icon-file"></i> <?php echo $tag; ?></a>
							<input type="hidden" id="jform_associations_<?php echo $tag; ?>_id" name="associations[<?php echo $tag; ?>]" value="<?php echo BSHelperUser::ifExistPageName($pagename); ?>" />
						</div>
					</td>
				</tr>
<?php
			}
		}
	}
	if ($this->form) {
?>
			<tr>
				<td class="key">
					<?php echo $this->form->getLabel('tags', 'metadata'); ?>
				</td>
				<td>
					<div class="input-append">
						<?php echo $this->form->getInput('tags', 'metadata', $this->tags).'<br />'; ?>
					</div>
				</td>
			</tr>
<?php
	}

	if (count($this->tab_template)) {
?>
				<tr>
					<td class="key">
						<label><?php echo JText::_('COM_MYJSPACE_TEMPLATE'); ?></label>
					</td>
					<td>
						<select name="mjs_template" id="mjs_template" size="1">
						<?php
						if ('' == $this->user_page->template)
							echo "<option value=\"\" selected=\"selected\">-</option>\n";
						else
							echo "<option value=\"\">-</option>\n";

						foreach ($this->tab_template as $key => $value) {
							if ($key == $this->user_page->template)
								echo '<option value="'.$key.'" selected="selected">'.$value."</option>\n";
							else
								echo '<option value="'.$key.'">'.$value."</option>\n";
						}
						?>
						</select>
					</td>
				</tr>
<?php
	}

	if ($this->link_folder == 1 && ($this->uploadimg > 0 || $this->uploadmedia > 0)) {
?>
				<tr>
					<td class="key">
						<label><?php echo JText::_('COM_MYJSPACE_LABELUSAGE0'); ?></label>
					</td>
					<td>
						<?php echo JText::sprintf('COM_MYJSPACE_LABELUSAGE1', $this->page_size, $this->dir_max_size, $this->page_number); ?>
					</td>
				</tr>
				<tr>
					<td class="key">
						<label><?php echo JText::_('COM_MYJSPACE_LABELUSAGE2'); ?></label>
					</td>
					<td>
						<?php echo $this->file_img_size; ?>
					</td>
				</tr>
<?php
	}	
?>				
			</table>
			
			<input type="hidden" name="id" id="id" value="<?php echo $this->user_page->id; ?>" />
			<input name="option" id="option" type="hidden" value="com_myjspace" />
			<input name="task" type="hidden" value="adm_save_page" /><br />
			<input name="view" id="view" type="hidden" value="page" />
			<?php echo JHTML::_('form.token'); ?>
		</fieldset>
	</div>
	
	<div class="col myjsp-w-55 fltlft">
		<fieldset class="adminform">
		<legend><?php echo JText::_('COM_MYJSPACE_PAGE'); ?></legend>
<?php
	$editor = JFactory::getEditor($this->editor_selection);
	echo $editor->display('mjs_content', $this->user_page->content, $this->edit_x, $this->edit_y, null, null, $this->editor_button);
?>
	<br />
		</fieldset>
	</div>

	</form>

<?php
	if ($this->uploadadmin && ($this->uploadimg > 0 || $this->uploadmedia > 0)) {
?>
	<div class="col myjsp-w-100 fltlft">
	<fieldset class="adminform ">
		<legend><?php echo JText::_('COM_MYJSPACE_UPLOADTITLE') ?></legend>
		<form method="post" action="<?php echo JRoute::_('index.php'); ?>" enctype="multipart/form-data" >
			<input name="option" type="hidden" value="com_myjspace" />
			<input name="task" type="hidden" value="upload_file" />
			<input type="hidden" name="id" value="<?php echo $this->user_page->id; ?>" />
			<input type="file" name="upload_file" />
			<input type="hidden" name="MAX_FILE_SIZE" value="<?php echo $this->file_max_size; ?>" />
			<input type="submit" class="button btn mjp-config" value="<?php echo JText::_('COM_MYJSPACE_UPLOADUPLOAD') ?>" onclick="document.getElementById('progress_div').style.visibility='visible';" />
			<?php echo JHTML::_('form.token'); ?>
			<div id="progress_div" style="visibility: hidden;"><img src="<?php echo str_replace('/administrator', '', JURI::root()); ?>components/com_myjspace/assets/progress.gif" alt="wait..." style="padding-top: 5px;" /></div>
		</form>
		<form  method="post" action="<?php echo JRoute::_('index.php'); ?>" >
			<input name="option" type="hidden" value="com_myjspace" />
			<input name="task" type="hidden" value="delete_file" />
			<input type="hidden" name="id" value="<?php echo $this->user_page->id; ?>" />
			<select name="delete_file" id="delete_file" size="1">
				<option value="" selected="selected"><?php echo JText::_('COM_MYJSPACE_UPLOADCHOOSE') ?></option>
				<?php
					$nb = count($this->tab_list_file);
					for ($i = 0 ; $i < $nb ; ++$i) {
						$chaine_tmp = $this->tab_list_file[$i];
						if (strlen($chaine_tmp) > 25)
							$chaine_tmp = substr($chaine_tmp, 0 , 25).'...';
						echo '<option value="'.$this->tab_list_file[$i].'">'.$chaine_tmp."</option>\n";
					}
				?>
			</select>
			<input type="submit" value="<?php echo JText::_('COM_MYJSPACE_UPLOADDELETE') ?>" class="button btn mjp-config" />
			<?php echo JHTML::_('form.token'); ?>
			<div>&nbsp;</div>			
		</form>
	</fieldset>
	</div>
<?php
		}
?>	

	<div class="clr"></div>
</div>
