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

if (version_compare(JVERSION, '1.6.0', 'ge') && $this->separ <= 1)
	$document->addScript('media/system/js/core.js');

JHTML::_('behavior.tooltip');

$document->addStyleSheet('components/com_myjspace/assets/myjspace.min.css');

if ($this->search_image_effect_list == 1) {
	$document->addStyleSheet('components/com_myjspace/assets/lytebox/lytebox.css');
	$document->addScript('components/com_myjspace/assets/lytebox/lytebox.js');
}

if ($this->separ > 1) {
	$document->addStyleSheet('components/com_myjspace/assets/myjsp_blocks.min.css');
	$document->addStyleDeclaration($this->style_str);
	echo $this->chaine_ie;
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
		<legend><?php echo JText::_('COM_MYJSPACE_TITLESEARCH') ?><?php if ($this->url_rss_feed != '') {echo '&nbsp;&nbsp;'.$this->url_rss_feed;} ?></legend>
<?php
if ($this->search_sort_use == 1 && ($this->search_labels == 0 || $this->separ > 1)) {
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
	if ($this->separ == 0) { // tab list
		echo "<table class=\"mjsp_search_tab adminlist table-striped noborder width100\">\n";
		$separ_l = '<td>';
		$separ_lt = '<th class="title">';
		$separ_l_img = "<td class=\"mjsp_search_img\">";
		$separ_r = '</td>';
		$separ_rt = '</th>';
	} else if ($this->separ == 1) { // raw
		$separ_l = '<span class="mjsp_search_row_field">';
		$separ_lt = $separ_l;
		$separ_l_img = '<span class="mjsp_search_row_field">';
		$separ_r = '</span> ';
		$separ_rt = $separ_r;
	} else if ($this->separ == 2) { // blocks
		echo "<div class=\"myjsp-blocks\" id=\"myjsp-blocks\">\n";
		$separ_l = ' ';
		$separ_l_img = '';
		$separ_r = "\n";
		$this->search_image_effect_list = 0;
	} else if ($this->separ == 3) { // Wall
		echo "<div class=\"myjsp-blocks2\">\n";
		$separ_l = ' ';
		$separ_r = "\n";
	} else {
		$separ_l = '';
		$separ_l_img = '';
		$separ_r = ' ';
	}

	$nb = count($this->result);
	for ($i = 0; $i < $nb ; ++$i) {
		// Set & transform content to be displayed
		$aff = $this->transform_fields($this, $i);

		if ($this->separ == 0 || $this->separ == 1) {	
			if ($this->separ <= 1 && $this->search_labels == 1 && $i == 0) {
				if ($this->separ == 0)
					echo "<tr>\n";
				else
					echo "<div>\n";
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
				if ($this->separ == 0)
					echo "\n</tr>\n";
				else
					echo "\n</div>\n";
			}
			if ($this->separ == 0) {
				$n = $i%2;
				echo "<tr class=\"row$n\">\n";
			}
			if ($this->separ == 1)
				echo '<div class="mjsp_search_row">';
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
		} else if ($this->separ == 2) {
			echo "<div class=\"icon\">\n";
	
			$title = "\n".$aff->title."\n";
			if ($aff->username)
				$title .= JText::_('COM_MYJSPACE_LABELUSERNAME').JText::_('COM_MYJSPACE_2POINTS').$aff->username."\n";
			if ($aff->create_date)
				$title .= JText::_('COM_MYJSPACE_LABELCREATIONDATE').JText::_('COM_MYJSPACE_2POINTS').$aff->create_date."\n";
			if ($aff->update_date)
				$title .= JText::_('COM_MYJSPACE_LABELLASTUPDATEDATE').JText::_('COM_MYJSPACE_2POINTS').$aff->update_date."\n";
			if($aff->share_page)
				$title .= JText::_('COM_MYJSPACE_TITLESHAREEDIT').JText::_('COM_MYJSPACE_2POINTS').JText::_('COM_MYJSPACE_YES')."\n";
			if ($aff->hits)
				$title .= JText::_('COM_MYJSPACE_LABELHITS').JText::_('COM_MYJSPACE_2POINTS').$aff->hits."\n";
			if ($aff->size)
				$title .= JText::_('COM_MYJSPACE_LABELSIZE').JText::_('COM_MYJSPACE_2POINTS').$aff->size."\n";
			if ($aff->language)
				$title .= JText::_('COM_MYJSPACE_LABELLANGUAGE').JText::_('COM_MYJSPACE_2POINTS').$aff->language."\n";
			if ($aff->blockView_alt)
				$title .= JText::_('COM_MYJSPACE_TITLEMODEVIEW').JText::_('COM_MYJSPACE_2POINTS').$aff->blockView_alt."\n";
			if ($aff->content)
				$title .= "\n".$aff->content."\n";
			$title .= "\n";

			echo "<a href=\"".$aff->page_url."\" title=\"".$title."\" >";
			echo '<span class="myjsp-pagename">'.$aff->title.'</span>';
			echo '<span class=".myjsp-spanimg">'.$aff->image.'</span>';
			if (($aff->description) && $aff->description != ' ')
				echo '<span class="myjsp-desc">'.$aff->description.'</span>';
			echo '<span>'.$aff->content.'</span>';
			echo "</a></div>\n";
		} else if ($this->separ == 3) {
			echo "<span class=\"grow pic\">\n";
			echo "<a href=\"".$aff->page_url."\">";

			$aff->title .= "\n";
			if ($aff->username)
				$aff->title .= JText::_('COM_MYJSPACE_LABELUSERNAME').JText::_('COM_MYJSPACE_2POINTS').$aff->username."\n";
			if ($aff->create_date)
				$aff->title .= JText::_('COM_MYJSPACE_LABELCREATIONDATE').JText::_('COM_MYJSPACE_2POINTS').$aff->create_date."\n";
			if ($aff->update_date)
				$aff->title .= JText::_('COM_MYJSPACE_LABELLASTUPDATEDATE').JText::_('COM_MYJSPACE_2POINTS').$aff->update_date."\n";
			if ($aff->hits)
				$aff->title .= JText::_('COM_MYJSPACE_LABELHITS').JText::_('COM_MYJSPACE_2POINTS').$aff->hits."\n";
			if (($aff->description) && $aff->description != ' ')
				$aff->title .= JText::_('COM_MYJSPACE_LABELMETAKEY').JText::_('COM_MYJSPACE_2POINTS').$aff->description."\n";
			if ($aff->size)
				$aff->title .= JText::_('COM_MYJSPACE_LABELSIZE').JText::_('COM_MYJSPACE_2POINTS').$aff->size."\n";
			if ($aff->language)
				$aff->title .= JText::_('COM_MYJSPACE_LABELLANGUAGE').JText::_('COM_MYJSPACE_2POINTS').$aff->language."\n";
			if ($aff->blockView_alt)
				$aff->title .= JText::_('COM_MYJSPACE_TITLEMODEVIEW').JText::_('COM_MYJSPACE_2POINTS').$aff->blockView_alt."\n";
			if ($aff->content)
				$aff->title .= "\n".$aff->content."\n";

			$aff->image = exist_image_html($aff->local_folder, JPATH_SITE, 'img_preview', 0, $aff->title, 'preview.jpg', $this->search_image_default, $this->search_image_type, $aff->text, $this->search_image_video, $aff->page_url);
			echo $aff->image;
			echo "</a></span>\n";		
		}

		if ($this->separ == 0)
			echo "\n</tr>\n";
		else if ($this->separ == 1)
			echo "\n</div>\n";
	}

	if ($this->separ == 0)
		echo "</table>\n";
	else if ($this->separ >= 2)
		echo "</div>\n";
?>
	</div>
	</fieldset>

	<input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['order_Dir']; ?>" />
</form>
</div>
