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

$document = JFactory::getDocument();
$document->addStyleSheet('components/com_myjspace/assets/myjspace.min.css');
?>
<h2><?php echo JText::_('COM_MYJSPACE_TITLEDELETE'); ?></h2>
<div class="myjspace">
	<br />
	<fieldset class="adminform front">
	<legend><?php echo  JText::_('COM_MYJSPACE_AREYOUSURE'); ?></legend>
		<form method="post" action="<?php echo JRoute::_('index.php'); ?>">
			<input name="option" type="hidden" value="com_myjspace" />
			<input name="task" type="hidden" value="del_page" />
			<input name="Itemid" type="hidden" value="<?php echo $this->Itemid; ?>" />
			<input name="id" type="hidden" value="<?php echo $this->user_page->id; ?>" />
			<input type="submit" class="button btn mjp-config" value="<?php echo JText::_('COM_MYJSPACE_DELETE'); ?>" />
			<?php echo JHTML::_('form.token'); ?>
		</form>
	</fieldset>
</div>
