<?php
/**
* @version $Id: default.php $
* @version		2.4.2 06/08/2014
* @package		com_myjspace
* @author       Bernard Saulmé
* @copyright	Copyright (C) 2010-2011-2013-2014 Bernard Saulmé
* @license      GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die;
if (!defined('DS')) define( 'DS', DIRECTORY_SEPARATOR);

$document = JFactory::getDocument();
$document->addStyleSheet('components/com_myjspace/assets/myjspace.min.css');
?>
<h2><?php echo JText::_('COM_MYJSPACE_TITLEEDIT'); ?></h2>
<div class="myjspace">
<?php
	if (!$this->msg) { 
?>
	<form method="post" name="adminForm" id="adminForm" action="<?php echo JRoute::_('index.php?option=com_myjspace&Itemid='.$this->Itemid); ?>">
		<div class="mjp-form-button">
			<input name="option" id="option" type="hidden" value="com_myjspace" />
			<input name="task" type="hidden" value="save" />
			<input name="view" id="view" type="hidden" value="edit" />
			<input name="id" id="id" type="hidden" value="<?php echo $this->user_page->id; ?>" />
			<input name="pagename" type="hidden" value="<?php echo $this->user_page->pagename; ?>" />
			<input name="return" type="hidden" value="<?php echo $this->return; ?>" />
			<input type="submit" class="btn btn-primary" value="<?php echo JText::_('COM_MYJSPACE_SAVE'); ?>" />
			<input type="reset" class="btn" value="<?php echo JText::_('COM_MYJSPACE_RESET'); ?>" />
			<?php echo JHTML::_('form.token'); ?>
		</div>
<?php
	$editor = JFactory::getEditor($this->editor_selection);
//	$editor = JEditor::getInstance($this->editor_selection);
	
	echo $editor->display('mjs_content', $this->user_page->content, $this->edit_x, $this->edit_y, null, null, $this->editor_button);
?>
	</form>
	<br />
<?php
	} else echo $this->msg; ?>
	
</div>
