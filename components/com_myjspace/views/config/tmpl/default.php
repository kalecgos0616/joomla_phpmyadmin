<?php
/**
* @version $Id: default.php $
* @version		2.4.2 06/08/2014
* @package		com_myjspace
* @author       Bernard Saulmé
* @copyright	Copyright (C) 2010-2011-2012-2013-2014 Bernard Saulmé
* @license      GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die;
if (!defined('DS')) define('DS', DIRECTORY_SEPARATOR);

JHTML::_('behavior.modal', 'a.modal_association');
$document = JFactory::getDocument();
$document->addStyleSheet('components/com_myjspace/assets/myjspace.min.css');
if (version_compare(JVERSION, '1.6.0', 'ge'))
	$document->addScript('media/system/js/mootools-more.js');
if (count($this->associations) > 0)
	$document->addScript('components/com_myjspace/assets/association.js');

if ($this->publish_mode == 2)
	JHTML::_('behavior.calendar');
?>

<h2><?php echo JText::_('COM_MYJSPACE_TITLECONFIG'); ?></h2>
<div class="myjspace">
	<br />
<?php if ($this->user_page->blockEdit != 2 && $this->alert_root_page == 0) { ?> 
		<form action="<?php echo JRoute::_('index.php'); ?>" method="post">
		<fieldset class="adminform front">
		<legend><?php echo JText::_('COM_MYJSPACE_LABELUSERDETAILS'); ?></legend>
			<table class="admintable">
<?php if ($this->show_link_admin == 1) { ?>
				<tr>
					<td class="key">
						<label><?php echo  JText::_('COM_MYJSPACE_PAGELINK'); ?></label>
					</td>
					<td>
						<a href="<?php echo $this->link; ?>"><?php echo $this->link; ?></a>
					</td>
				</tr>
<?php } ?>
				<tr>
					<td class="key">
						<label><?php echo JText::_('COM_MYJSPACE_TITLENAME'); ?></label>
					</td>
					<td>
					<?php if ($this->pagename_username == 1) { ?>
						<?php echo $this->user_page->pagename; ?>
						<input type="hidden" name="mjs_pagename" id="mjs_pagename"  value="<?php echo $this->user_page->title; ?>" /> <?php echo $this->msg_tmp; ?>
					<?php } else { ?>
						<input type="text" name="mjs_pagename" id="mjs_pagename" class="inputbox" size="40" value="<?php echo $this->user_page->title; ?>" /> <?php echo $this->msg_tmp; ?>
					<?php } ?>
					</td>
				</tr>
				<tr>
					<td class="key">
						<label><?php echo JText::_('COM_MYJSPACE_LABELUSERNAME'); ?></label>
					</td>
					<td>
						<?php echo $this->username; ?>
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
<?php
				$model_page_list_count = count($this->model_page_list);
				if ($this->msg_tmp != '' && $model_page_list_count >= 2) { // If several (2 pages + text_to_choixe) model page list
?>
				<tr>
					<td class="key">
						<label><?php echo JText::_('COM_MYJSPACE_TITLEMODEL'); ?></label>
					</td>
					<td>
						<select name="mjs_model_page" id="mjs_model_page" size="1">
<?php
							foreach ($this->model_page_list as $key => $value) {
								echo '<option value="'.$key.'">'.$this->model_page_list[$key]['text']."</option>\n";	
							}
?>							
						</select>
					</td>
				</tr>
<?php			}	?>
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
<?php
	if ($this->page_increment == 1) {
?>
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
	}
	if ($this->publish_mode == 2) {
		$prefix_publish = (version_compare(JVERSION, '1.6.0', 'ge')) ? 'jform_' : '';
?>
				<tr>
					<td class="key">
						<label><?php echo JText::_('COM_MYJSPACE_LABELPUBLISHUP'); ?></label>
					</td>
					<td>
						<?php echo JHTML::_('calendar', $this->user_page->publish_up, $prefix_publish."publish_up", $prefix_publish."publish_up", JText::_('COM_MYJSPACE_DATE_CALENDAR2'), null).' '.$this->img_publish_up; ?>
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
				if ($this->share_page != 0) {
?>
				<tr>
					<td class="key">
						<label><?php echo JText::_('COM_MYJSPACE_TITLESHAREEDIT'); ?></label>
					</td>
					<td>
<?php
					if ($this->share_page == 2) {
						echo "<select name=\"mjs_share\" id=\"mjs_share\" size=\"1\">\n";
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
						echo "</select>\n";
					} else
						echo get_assetgroup_label($this->user_page->access);
?>
					</td>
				</tr>
<?php
				}
				if ($this->share_page != 0 && $this->user_page->access > 0) {
?>
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

	$categories_count = count($this->categories);
	if ($categories_count > 0) {
?>
				<tr>
					<td class="key">
						<label><?php echo JText::_('COM_MYJSPACE_LABELCATEGORY'); ?></label>
					</td>
					<td>
<?php 				if ($this->select_category == 1) { ?>
						<select name="mjs_categories" id="mjs_categories" size="1">
<?php
							for ($i = 0; $i < $categories_count; $i++) {
								if ($this->categories[$i]['value'] == $this->user_page->catid || $this->categories[$i]['value'] == $this->catid)
									echo '<option value="'.$this->categories[$i]['value'].'" selected="selected">'.'&nbsp;'.str_repeat('- ',$this->categories[$i]['level']).$this->categories[$i]['text']."</option>\n";
								else
									echo '<option value="'.$this->categories[$i]['value'].'">'.'&nbsp;'.str_repeat('- ',$this->categories[$i]['level']).$this->categories[$i]['text']."</option>\n";
							}
?>
						</select>
<?php
					} else {
						for ($i = 0; $i < $categories_count; $i++) {
							if ($this->categories[$i]['value'] == $this->user_page->catid || $this->categories[$i]['value'] == $this->catid) {
								echo $this->categories[$i]['text'];
?>								<input name="mjs_categories" type="hidden" value="<?php echo $this->categories[$i]['value']; ?>" />  <?php
							}
						}					
					}
?>					
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
						<option value="">-</option>
						<?php
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
	if ($this->user_mode_view == 1) {
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
			<input name="Itemid" type="hidden" value="<?php echo $this->Itemid_config; ?>" />
			<input name="option" type="hidden" value="com_myjspace" />
			<input name="task" type="hidden" value="save_config" />
			<input name="id" type="hidden" value="<?php echo $this->user_page->id; ?>" />
			<input type="submit" class="button btn mjp-config" value="<?php echo JText::_('COM_MYJSPACE_SAVE'); ?>" />
			<?php echo JHTML::_('form.token'); ?>
		</fieldset>
		</form>
<?php if ($this->msg_tmp == '') { ?>
	<fieldset class="adminform front">
		<legend><?php echo JText::_('COM_MYJSPACE_TITLEACTION') ?></legend>
		<table class="noborder width100" ><tr>
		<td>
<?php 	if (version_compare(JVERSION, '1.6.0', 'lt') || (version_compare(JVERSION, '1.6.0', 'ge') && JFactory::getUser()->authorise('user.edit', 'com_myjspace'))) { ?>
			<form method="post" action="<?php echo Jroute::_('index.php?option=com_myjspace&view=edit&id='.$this->user_page->id.'&Itemid='.$this->Itemid_edit); ?>">
				<input type="submit" class="button btn mjp-config" value="<?php echo JText::_('COM_MYJSPACE_TITLEEDIT1'); ?>" />
			</form>
<?php	} ?>
		</td>
		<td>
<?php 	if (version_compare(JVERSION, '1.6.0', 'lt') || (version_compare(JVERSION, '1.6.0', 'ge') && JFactory::getUser()->authorise('user.see', 'com_myjspace'))) { ?>
			<form method="post" action="<?php echo Jroute::_('index.php?option=com_myjspace&view=see&id='.$this->user_page->id.'&Itemid='.$this->Itemid_see); ?>">
				<input type="submit" class="button btn mjp-config" value="<?php echo JText::_('COM_MYJSPACE_TITLESEE1'); ?>" />
			</form>
<?php	} ?>
		</td>
		<td>
<?php	if (version_compare(JVERSION, '1.6.0', 'lt') || (version_compare(JVERSION, '1.6.0', 'ge') && JFactory::getUser()->authorise('user.delete', 'com_myjspace'))) { ?>
			<form method="post" action="<?php echo Jroute::_('index.php?option=com_myjspace&view=delete&id='.$this->user_page->id.'&Itemid='.$this->Itemid_delete); ?>">
				<input type="submit" class="button btn mjp-config" value="<?php echo JText::_('COM_MYJSPACE_DELETE'); ?>" />
			</form>
<?php	} ?>
		</td>
		<td>
<?php	if ($this->nb_max_page > 1 && (version_compare(JVERSION, '1.6.0', 'lt') || (version_compare(JVERSION, '1.6.0', 'ge') && JFactory::getUser()->authorise('user.pages', 'com_myjspace')) )) { ?>
			<form method="post" action="<?php echo Jroute::_('index.php?option=com_myjspace&view=pages'.$this->catid_url.'&Itemid='.$this->Itemid_pages); ?>">
				<input type="submit" class="button btn mjp-config" value="<?php echo JText::_('COM_MYJSPACE_NEW'); ?>" />
			</form>
<?php	} ?>
		</td>
		</tr></table>
	</fieldset>
<?php
		}
		if ($this->uploadadmin && ($this->uploadimg > 0 || $this->uploadmedia > 0)) {
?>
	<fieldset class="adminform front">
		<legend><?php echo JText::_('COM_MYJSPACE_UPLOADTITLE') ?></legend>
		<form method="post" action="<?php echo JRoute::_('index.php'); ?>" enctype="multipart/form-data" >
			<input name="Itemid" type="hidden" value="<?php echo $this->Itemid_config; ?>" />
			<input name="option" type="hidden" value="com_myjspace" />
			<input name="task" type="hidden" value="upload_file" />
			<input name="id" type="hidden" value="<?php echo $this->user_page->id; ?>" />
			<input type="file" name="upload_file" />
			<input type="hidden" name="MAX_FILE_SIZE" value="<?php echo $this->file_max_size; ?>" />

			<input type="submit" class="button btn mjp-config" value="<?php echo JText::_('COM_MYJSPACE_UPLOADUPLOAD') ?>" onclick="document.getElementById('progress_div').style.visibility='visible';" />
			<?php echo JHTML::_('form.token'); ?>
			<div id="progress_div" class="progress_div"><img src="components/com_myjspace/assets/progress.gif" alt="..." /></div>
		</form>

		<form  method="post" action="<?php echo JRoute::_('index.php'); ?>" >
			<input name="Itemid" type="hidden" value="<?php echo $this->Itemid_config; ?>" />
			<input name="option" type="hidden" value="com_myjspace" />
			<input name="task" type="hidden" value="delete_file" />
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
			<div>&nbsp;</div>
		</form>
	</fieldset>
<?php
		}
   } else if ($this->alert_root_page == 1)
 		echo JText::_('COM_MYJSPACE_ALERTYOURADMIN');  
	else if ($this->user_page->blockEdit == 1)
		echo JText::_('COM_MYJSPACE_EDITBLOCKED');
   else
		echo JText::_('COM_MYJSPACE_EDITLOCKED');

	if ($this->display_myjspace_ref == 1) {
 ?>
 	<div class="bsfooter">
		<a href="<?php echo Jroute::_('index.php?option=com_myjspace&view=myjspace&Itemid='.$this->Itemid); ?>">BS MyJspace</a>
	</div>
 <?php } ?>
 
</div>
