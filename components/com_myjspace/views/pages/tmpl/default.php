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

$document = JFactory::getDocument();

if (version_compare(JVERSION, '1.6.0', 'ge'))
	$document->addScript('media/system/js/core.js');

JHTML::_('behavior.tooltip');

$document->addStyleSheet('components/com_myjspace/assets/myjspace.min.css');

if ($this->search_image_effect_list == 1) {
	$document->addStyleSheet('components/com_myjspace/assets/lytebox/lytebox.css');
	$document->addScript('components/com_myjspace/assets/lytebox/lytebox.js');
}

if ($this->search_page_title)
	echo '<h2>'.$this->search_page_title.'</h2>';
?>
<div class="myjspace">
	<form action="<?php echo JRoute::_('index.php'); ?>" method="post" name="adminForm" id="adminForm">
<?php
if ($this->aff_select) { // Search selector to print
?>
		<fieldset>
		<legend><?php echo JText::_('COM_MYJSPACE_TITLEPAGES') ?><?php if ($this->url_rss_feed != '') {echo '&nbsp;&nbsp;'.$this->url_rss_feed;} ?></legend>
<?php
if ($this->search_sort_use == 1 && $this->search_labels == 0) {
		echo '&nbsp;'.JText::_('COM_MYJSPACE_SEARCHSORT').' <select name="sort" id="mjs_sort" size="1" onchange="this.form.submit()">';
		$sort_list = array(JText::_('COM_MYJSPACE_SEARCHSORT0'),JText::_('COM_MYJSPACE_SEARCHSORT1'),JText::_('COM_MYJSPACE_SEARCHSORT2'),JText::_('COM_MYJSPACE_SEARCHSORT3'),JText::_('COM_MYJSPACE_SEARCHSORT4'),JText::_('COM_MYJSPACE_SEARCHSORT5'));
		$nb = count($sort_list);
		for ($i = 0; $i < $nb ; ++$i) {
			if ($i == $this->aff_sort)
				echo '<option selected="selected" value="'.$i.'">&nbsp;'.$sort_list[$i].'&nbsp;</option>';
			else
				echo '<option value="'.$i.'">&nbsp;'.$sort_list[$i].'&nbsp;</option>';
		}
		echo '</select>';
}

$categories_count = count($this->categories);

if ($this->search_advanced_criteria == 1) {
?>
			<?php echo JText::_('COM_MYJSPACE_SEARCHSEARCHPNAME') ?><input type="checkbox" name="check_search[]" <?php if (isset($this->check_search_asso['name'])) echo 'checked="checked"'; ?> value="name" onchange="this.form.submit()" />
			<?php echo JText::_('COM_MYJSPACE_SEARCHSEARCHCONTENT') ?><input type="checkbox" name="check_search[]" <?php if (isset($this->check_search_asso['content'])) echo 'checked="checked"'; ?> value="content" onchange="this.form.submit()" />
			<?php echo JText::_('COM_MYJSPACE_SEARCHSEARCHDESCRIPTION') ?><input type="checkbox" name="check_search[]" <?php if (isset($this->check_search_asso['description'])) echo 'checked="checked"'; ?> value="description" onchange="this.form.submit()" />
<?php
	
	if ($categories_count > 0) {
		echo '&nbsp;'.JText::_('COM_MYJSPACE_LABELCATEGORY');
?>
				<select name="catid" id="catid" size="1">
				<option value="0">&nbsp;-</option>
<?php
				for ($i = 0; $i < $categories_count; $i++) {
					if ($this->categories[$i]['value'] == $this->catid)
						echo '<option value="'.$this->categories[$i]['value'].'" selected="selected">'.'&nbsp;'.str_repeat('- ',$this->categories[$i]['level']).$this->categories[$i]['text']."</option>\n";
					else
						echo '<option value="'.$this->categories[$i]['value'].'">'.'&nbsp;'.str_repeat('- ',$this->categories[$i]['level']).$this->categories[$i]['text']."</option>\n";
				}
?>
				</select>
<?php
	}
 }
?>
				<input type="text" name="svalue" id="svalue" class="inputbox" size="10" value="<?php echo $this->svalue; ?>" />
				<input type="submit" id="bouton" name="bouton" value="<?php echo JText::_('COM_MYJSPACE_SEARCH'); ?>" class="button btn mjp-config" />
<?php	
		if ($this->search_pagination == 1)
			echo '<div class="list-footer"><span class="limit">'.$this->pagination->getLimitBox().'</span> '.$this->pagination->getPagesLinks().'</div>';
?>
	</fieldset>
<?php
} else {
	if ($this->search_pagination == 1) {
		echo '<fieldset class="adminform front">';
		echo '<legend>'.JText::_('COM_MYJSPACE_TITLESEARCH').'&nbsp;&nbsp;'.$this->url_rss_feed.'</legend>';
		echo '<div class="list-footer"><span class="limit">'.$this->pagination->getLimitBox().'</span> '.$this->pagination->getPagesLinks().'</div>';
		echo '</fieldset>';
	} else if ($this->url_rss_feed != '')
		echo '<div class="mjp-rss-feed">'.$this->url_rss_feed.'</div>';
}?>
	<fieldset>
	<div class="myjspace_result_search">
<?php
	// Table list
	echo "<table class=\"mjsp_search_tab adminlist table-striped noborder width100\">\n";
	$separ_l = '<td>';
	$separ_lt = '<th class="title">';
	$separ_l_img = "<td class=\"mjsp_search_img\">";
	$separ_r = '</td>';
	$separ_rt = '</th>';

	$nb = count($this->result);
	for ($i = 0; $i < $nb ; ++$i) {
		// Set & transform content to be displayed
		$aff = $this->transform_fields($this, $i);

		if ($this->search_labels == 1 && $i == 0) {
			echo "<tr>\n";
			echo $separ_lt.' '.$separ_rt;
			if ($aff->image)
				echo $separ_lt.' '.$separ_rt;
			else if ($this->search_aff_add & 64)
				echo $separ_lt.' '.$separ_rt;
			if ($aff->pagename && !$this->search_sort_use)
				echo $separ_lt.JText::_('COM_MYJSPACE_TITLENAME').$separ_rt;
			if ($aff->pagename && $this->search_sort_use)
				echo $separ_lt.JHTML::_('grid.sort', JText::_('COM_MYJSPACE_TITLENAME'), 'pagename', @$this->lists['order_Dir'], @$this->lists['order']).$separ_rt;
			if ($aff->username && !$this->search_sort_use)
				echo $separ_lt.JText::_('COM_MYJSPACE_LABELUSERNAME').$separ_rt;
			if ($aff->username && $this->search_sort_use)
				echo $separ_lt.JHTML::_('grid.sort', JText::_('COM_MYJSPACE_LABELUSERNAME'), 'userid', @$this->lists['order_Dir'], @$this->lists['order']).$separ_rt;
			if ($aff->category !== false && !$this->search_sort_use && $categories_count > 0)
				echo $separ_lt.JText::_('COM_MYJSPACE_LABELCATEGORY').$separ_rt;
			if ($aff->category !== false && $this->search_sort_use && $categories_count > 0)
				echo $separ_lt.JHTML::_('grid.sort', JText::_('COM_MYJSPACE_LABELCATEGORY'), 'catid', @$this->lists['order_Dir'], @$this->lists['order']).$separ_rt;
			if ($aff->description && !$this->search_sort_use)
				echo $separ_lt.JText::_('COM_MYJSPACE_LABELMETAKEY').$separ_rt;
			if ($aff->description && $this->search_sort_use)
				echo $separ_lt.JHTML::_('grid.sort', JText::_('COM_MYJSPACE_LABELMETAKEY'), 'metakey', @$this->lists['order_Dir'], @$this->lists['order']).$separ_rt;
			if ($aff->create_date && !$this->search_sort_use)
				echo $separ_lt.JText::_('COM_MYJSPACE_LABELCREATIONDATE').$separ_rt;
			if ($aff->create_date && $this->search_sort_use)
				echo $separ_lt.JHTML::_('grid.sort', JText::_('COM_MYJSPACE_LABELCREATIONDATE'), 'create_date', @$this->lists['order_Dir'], @$this->lists['order']).$separ_rt;
			if ($aff->update_date && !$this->search_sort_use)
				echo $separ_lt.JText::_('COM_MYJSPACE_LABELLASTUPDATEDATE').$separ_rt;
			if ($aff->update_date && $this->search_sort_use)
				echo $separ_lt.JHTML::_('grid.sort', JText::_('COM_MYJSPACE_LABELLASTUPDATEDATE'), 'last_update_date', @$this->lists['order_Dir'], @$this->lists['order']).$separ_rt;
			if ($aff->hits !== false && !$this->search_sort_use)
				echo $separ_lt.JText::_('COM_MYJSPACE_LABELHITS').$separ_rt;
			if ($aff->hits !== false && $this->search_sort_use)
				echo $separ_lt.JHTML::_('grid.sort', JText::_('COM_MYJSPACE_LABELHITS'), 'hits', @$this->lists['order_Dir'], @$this->lists['order']).$separ_rt;
			if ($aff->content) // No short order on content
				echo $separ_lt.JText::_('COM_MYJSPACE_LABELCONTENT').$separ_rt;
			if ($aff->size && !$this->search_sort_use)
				echo $separ_lt.JText::_('COM_MYJSPACE_LABELSIZE').$separ_rt;
			if ($aff->size && $this->search_sort_use)
				echo $separ_lt.JHTML::_('grid.sort', JText::_('COM_MYJSPACE_LABELSIZE'), 'size', @$this->lists['order_Dir'], @$this->lists['order']).$separ_rt;
			if ($aff->language && !$this->search_sort_use)
				echo $separ_lt.JText::_('COM_MYJSPACE_LABELLANGUAGE').$separ_rt;
			if ($aff->language && $this->search_sort_use)
				echo $separ_lt.JHTML::_('grid.sort', JText::_('COM_MYJSPACE_LABELLANGUAGE'), 'language', @$this->lists['order_Dir'], @$this->lists['order']).$separ_rt;
			if ($aff->blockview && !$this->search_sort_use)
				echo $separ_lt.JText::_('COM_MYJSPACE_TITLEMODEVIEW').$separ_rt;
			if ($aff->blockview && $this->search_sort_use)
				echo $separ_lt.JHTML::_('grid.sort', JText::_('COM_MYJSPACE_TITLEMODEVIEW'), 'blockView', @$this->lists['order_Dir'], @$this->lists['order']).$separ_rt;
			echo "\n</tr>\n";
		}
		$n = $i%2;
		echo "<tr class=\"row$n\">\n";

		echo $separ_l_img.$aff->select.$separ_r;
		if ($aff->image)
			echo $separ_l_img.$aff->image.$separ_r;
		else if ($this->search_aff_add & 64)
			echo $separ_l_img.' '.$separ_r;
		if ($aff->pagename)
			echo $separ_l.'<a href="'.$aff->page_url.'">'.$aff->title.'</a>'.$aff->share_page.$separ_r;
		if ($aff->username)
			echo $separ_l.$aff->username.$separ_r;
		if ($aff->category !== false && $categories_count > 0)
			echo $separ_l.$aff->category.$separ_r;
		if ($aff->description)
			echo $separ_l.$aff->description.$separ_r;
		if ($aff->create_date)
			echo $separ_l.$aff->create_date.$separ_r;
		if ($aff->update_date)
			echo $separ_l.$aff->update_date.$separ_r;
		if ($aff->hits !== false)
			echo $separ_l.$aff->hits.$separ_r;
		if ($aff->content)
			echo $separ_l.$aff->content.$separ_r;
		if ($aff->size)
			echo $separ_l.$aff->size.$separ_r;
		if ($aff->language)
			echo $separ_l.$aff->language.$separ_r;
		if ($aff->blockview)
			echo $separ_l.$aff->blockview.$separ_r;

		echo "\n</tr>\n";
	}

	echo "</table>\n";
	echo "<br />\n";
?>
<?php
if (($this->uid > 0 && $this->uid == $this->user->id) || ($this->uid == 0 && $this->user->id > 0)) {
?>
	<span class="mjp-all-button">
<?php
		if (version_compare(JVERSION, '1.6.0', 'lt') || (version_compare(JVERSION, '1.6.0', 'ge') && JFactory::getUser()->authorise('user.config', 'com_myjspace'))) {
?>
			<input name="bt_config" type="submit" class="button btn mjp-list" value="<?php echo JText::_('COM_MYJSPACE_TITLECONFIG1'); ?>" onclick="if (document.getElementById('boxchecked').value==0){alert('<?php echo JText::_( 'COM_MYJSPACE_PAGELIST_ALERT'); ?>');}else{document.getElementById('Itemid').value='<?php echo $this->Itemid_config; ?>';document.getElementById('view').value='config';this.form.submit();}" />
<?php	}
		if (version_compare(JVERSION, '1.6.0', 'lt') || (version_compare(JVERSION, '1.6.0', 'ge') && JFactory::getUser()->authorise('user.edit', 'com_myjspace'))) {
?>
			<input name="bt_edit" type="submit" class="button btn mjp-list" value="<?php echo JText::_('COM_MYJSPACE_TITLEEDIT1'); ?>" onclick="if (document.getElementById('boxchecked').value==0){alert('<?php echo JText::_( 'COM_MYJSPACE_PAGELIST_ALERT'); ?>');}else{document.getElementById('Itemid').value='<?php echo $this->Itemid_edit; ?>';document.getElementById('view').value='edit';this.form.submit();}" />
<?php	}
		if (version_compare(JVERSION, '1.6.0', 'lt') || (version_compare(JVERSION, '1.6.0', 'ge') && JFactory::getUser()->authorise('user.see', 'com_myjspace'))) {
?>
			<input name="bt_see" type="submit" class="button btn mjp-list" value="<?php echo JText::_('COM_MYJSPACE_TITLESEE1'); ?>" onclick="if (document.getElementById('boxchecked').value==0){alert('<?php echo JText::_( 'COM_MYJSPACE_PAGELIST_ALERT'); ?>');}else{document.getElementById('Itemid').value='<?php echo $this->Itemid_see; ?>';document.getElementById('view').value='see';this.form.submit();}" />
<?php	}
		if (version_compare(JVERSION, '1.6.0', 'lt') || (version_compare(JVERSION, '1.6.0', 'ge') && JFactory::getUser()->authorise('user.delete', 'com_myjspace'))) {
?>
			<input name="bt_delete" type="submit" class="button btn mjp-list" value="<?php echo JText::_('COM_MYJSPACE_DELETE'); ?>" onclick="if (document.getElementById('boxchecked').value==0){alert('<?php echo JText::_( 'COM_MYJSPACE_PAGELIST_ALERT'); ?>');}else{document.getElementById('Itemid').value='<?php echo $this->Itemid_delete; ?>';document.getElementById('view').value='delete';this.form.submit();}" />
<?php	}
		if ($this->total < $this->nb_max_page && (version_compare(JVERSION, '1.6.0', 'lt') || (version_compare(JVERSION, '1.6.0', 'ge') && (($this->new_page_rview == 'edit' && JFactory::getUser()->authorise('user.edit', 'com_myjspace')) || ($this->new_page_rview == 'config' && JFactory::getUser()->authorise('user.config', 'com_myjspace')))))) {
?>
			<input name="bt_new" type="submit" class="button btn mjp-list" value="<?php echo JText::_('COM_MYJSPACE_CREATEPAGE'); ?>" onclick="document.getElementById('Itemid').value='<?php echo $this->Itemid_config; ?>';document.getElementById('view').value='<?php echo $this->new_page_rview; ?>';document.getElementById('id').value='-1';this.form.submit();" />
<?php }
		if ($this->total < $this->nb_max_page && (version_compare(JVERSION, '1.6.0', 'lt') || (version_compare(JVERSION, '1.6.0', 'ge') && (($this->copy_page_rview == 'edit' && JFactory::getUser()->authorise('user.edit', 'com_myjspace')) || ($this->copy_page_rview == 'config' && JFactory::getUser()->authorise('user.config', 'com_myjspace')))))) {
?>
			<input name="bt_copy" type="submit" class="button btn mjp-list" value="<?php echo JText::_('COM_MYJSPACE_COPY'); ?>" onclick="if (document.getElementById('boxchecked').value==0){alert('<?php echo JText::_( 'COM_MYJSPACE_PAGELIST_ALERT'); ?>');}else{document.getElementById('Itemid').value='<?php echo $this->Itemid_edit; ?>';document.getElementById('view').value='<?php echo $this->copy_page_rview; ?>';document.getElementById('id').value='-1';this.form.submit();}" />
<?php	} ?>
	</span>
<?php } ?>

	</div>
	</fieldset>

	<input type="hidden" name="option" value="com_myjspace" />
	<input type="hidden" name="view" id="view" value="pages" />
	<input type="hidden" name="id" id="id" value="0" />
	<input type="hidden" name="catid" id="catid" value="<?php echo $this->catid; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="uid" value="<?php echo $this->uid; ?>" />
	<input type="hidden" name="Itemid" id="Itemid" value="<?php echo $this->Itemid; ?>" />
	<input type="hidden" name="boxchecked" id="boxchecked"  value="0" />
	<input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['order_Dir']; ?>" />
</form>
</div>
