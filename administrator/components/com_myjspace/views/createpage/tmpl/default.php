<?php
/**
* @version $Id: default.php $ 
* @version		2.4.0 01/06/2014
* @package		com_myjspace
* @author       Bernard Saulmé
* @copyright	Copyright (C) 2011-2012-2013-2014 Bernard Saulmé
* @license      GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die;

JHTML::_('behavior.modal', 'a.modal_jform_created_by');
$document = JFactory::getDocument();
$document->addStyleSheet(JURI::root(true).'/components/com_myjspace/assets/myjspace.min.css');
if (version_compare(JVERSION, '1.6.0', 'ge'))
	$document->addScript(JURI::root(true).'/media/system/js/mootools-more.js');

?>
<div class="myjspace myjsp-w-100">

		<form action="<?php echo JRoute::_('index.php'); ?>" method="post" name="adminForm" id="adminForm">
		<fieldset class="adminform">
		<legend><?php echo JText::_('COM_MYJSPACE_LABELUSERDETAILS'); ?></legend>
			<table class="admintable">
				<tr>
					<td class="key">
<?php if (version_compare(JVERSION, '1.6.0', 'ge')) { ?>
						<label><?php echo JText::_('COM_MYJSPACE_LABELNAME'); ?></label>
<?php } else { ?>
						<label><?php echo JText::_('COM_MYJSPACE_LABELUSERNAME'); ?></label>
<?php } ?>
					</td>
					<td>
						<div class="input-append">
<?php if (version_compare(JVERSION, '1.6.0', 'ge')) { ?>
							<input type="text" name="mjs_username2" id="mjs_username2" class="inputbox" size="40" value="" disabled="disabled" />
<?php } else { ?>
							<input type="text" name="mjs_username" id="mjs_username" class="inputbox" size="40" value="" />
<?php } ?>
							<input type="hidden" name="mjs_userid" id="mjs_userid" value="0" />
<?php if (version_compare(JVERSION, '1.6.0', 'ge') && version_compare(JVERSION, '3.0.0', 'lt')) { ?>
							<div class="button2-left">
								<div class="blank">
									<a class="modal_jform_created_by" title="<?php echo JText::_('COM_MYJSPACE_LABELUSERNAME'); ?>" href="index.php?option=com_users&amp;view=users&amp;layout=modal&amp;tmpl=component&amp;field=jform_created_by" rel="{handler: 'iframe', size: {x: 800, y: 500}}"><?php echo  JText::_('COM_MYJSPACE_LABELSELECTUSER'); ?></a>
								</div>
							</div>
<?php } else if (version_compare(JVERSION, '3.0.0', 'ge')) { ?>
							<a class="btn btn-primary modal_jform_created_by" title="<?php echo JText::_('COM_MYJSPACE_LABELSELECTUSER'); ?>" href="index.php?option=com_users&amp;view=users&amp;layout=modal&amp;tmpl=component&amp;field=jform_created_by" rel="{handler: 'iframe', size: {x: 800, y: 500}}"><i class="icon-user"></i></a>							
<?php } ?>
						</div>
					</td>
				</tr>
				<tr>
					<td class="key">
						<label><?php echo JText::_('COM_MYJSPACE_TITLENAME'); ?></label>
					</td>
					<td>
						<input type="text" name="mjs_pagename" id="mjs_pagename" class="inputbox" size="40" value="" />
					</td>
				</tr>
<?php
				$model_page_list_count = count($this->model_page_list);
				if ($model_page_list_count >= 2) { // If several (2 pages + text_to_choose) model page list
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
<?php				
				}
?>	
			</table>
 
			<input name="option" type="hidden" value="com_myjspace" />			
			<input name="task" type="hidden" value="adm_create_page" />
			<?php echo JHTML::_('form.token'); ?>
		</fieldset>
		</form>
</div>
