<?php
/**
* @version $Id: default.php $ 
* @version		2.4.1 21/07/2014
* @package		com_myjspace
* @author       Bernard Saulmé
* @copyright	Copyright (C) 2010-2011-2012-2013-2014 Bernard Saulmé
* @license      GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die;

$document = JFactory::getDocument();
$document->addStyleSheet(JURI::root(true).'/components/com_myjspace/assets/myjspace.min.css');
?>
<div class="myjspace myjsp-w-100">
	<fieldset class="adminform">
		<legend><?php echo JText::_('COM_MYJSPACE_TITLE');?></legend>
		<div><img src="<?php echo JURI::root(); ?>administrator/components/com_myjspace/images/myjspace.png" alt="BS MyJSpace"/></div>
		<div><?php echo $this->version; ?></div>
	</fieldset>
	
<?php if ($this->newversion) { ?>
	<fieldset class="adminform">
		<legend><?php echo JText::_('COM_MYJSPACE_NEWVERSION');?></legend>
		<?php echo '<span style="color:orange">'.JText::_('COM_MYJSPACE_NEWVERSION').' </span>'.$this->newversion; ?>
	</fieldset>
<?php } ?>

	<fieldset class="adminform">
		<legend><?php echo JText::_('COM_MYJSPACE_STATISTICS');?></legend>
		
		<table class="admintable">
			<tr>
				<td class="key">
					<label><?php echo  JText::_('COM_MYJSPACE_NBPAGESTOTAL'); ?></label>
				</td>
				<td>
					<?php echo $this->nb_pages_total; ?>
				</td>
			</tr>
			<tr>
				<td class="key">
					<label><?php echo  JText::_('COM_MYJSPACE_NBDISTINCTUSERS'); ?></label>
				</td>
				<td>
					<?php echo $this->nb_distinct_users; ?>
				</td>
			</tr>
		</table>
		
	</fieldset>

<br />
</div>

