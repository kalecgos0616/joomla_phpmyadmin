<?php
/**
* @version $Id: default.php $
* @version		2.4.2 06/08/2014
* @package		com_myjspace
* @author       Bernard Saulmé
* @copyright	Copyright (C) 2010-2011-2012-2014 Bernard Saulmé
* @license      GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die;
if (!defined('DS')) define('DS', DIRECTORY_SEPARATOR);

$document = JFactory::getDocument();
$document->addStyleSheet(JURI::root(true).'/components/com_myjspace/assets/myjspace.min.css');
?>
<div class="myjspace myjsp-w-100">
	<form action="<?php echo JRoute::_('index.php'); ?>" method="post" name="adminForm" id="adminForm">
		<fieldset class="adminform">
			<legend><?php echo JText::_('COM_MYJSPACE_FOLDERNAME');?></legend>
<?php if ($this->link_folder == 1 || $this->link_folder_print == 1) { ?>
			<?php echo JText::_('COM_MYJSPACE_FOLDERNAMEINFO');?>
			<br />
			<table class="admintable">
				<tr>
					<td class="key">
						<label><?php echo  JText::_('COM_MYJSPACE_FOLDERNAME'); ?></label>
					</td>
					<td>
						<input type="text" name="mjs_foldername" value="<?php echo $this->link; ?>" />
<?php
						if (stristr($this->link, 'myjspace') !== false)
							echo JText::_('COM_MYJSPACE_FOLDERNAME_KO1');
						if ($this->link_folder == 1 &&!is_writable(JPATH_ROOT.DS.$this->link))
							echo JText::_('COM_MYJSPACE_FOLDERNAME_KO2');
						else if ($this->nb_index_ko > 0)
							echo JText::sprintf('COM_MYJSPACE_ADMIN_INDEX_FORMAT_KO', 'index.php?option=com_myjspace&amp;task=adm_create_folder&'.myjsp_getFormToken().'=1');
?>
					</td>
				</tr>
<?php
	if ($this->link_folder == 1) { ?>
				<tr>
					<td class="key">
						<label><?php echo JText::_('COM_MYJSPACE_FOLDERNAME_KEEP'); ?></label>
					</td>
					<td>
						<input type="checkbox" name="keep" value="1" />
					</td>
				</tr>
			<?php } ?>
			</table>
<?php } else {
		echo JText::_('COM_MYJSPACE_FOLDERNAME_NOTACTIVATED');
	}
?>
			<input name="option" type="hidden" value="com_myjspace" />
			<input name="task" type="hidden" value="adm_ren_folder" />
			<?php echo JHTML::_('form.token'); ?>
		</fieldset>
		<br />
	</form>
</div>
