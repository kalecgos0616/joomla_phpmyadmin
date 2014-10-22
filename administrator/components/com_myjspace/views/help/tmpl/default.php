<?php
/**
* @version $Id: default.php $
* @version		2.4.1 14/07/2014
* @package		com_myjspace
* @author       Bernard Saulmé
* @copyright	Copyright (C) 2010-2011-2012-2013-2014 Bernard Saulmé
* @license      GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die;
if (!defined('DS')) define('DS', DIRECTORY_SEPARATOR);

$document = JFactory::getDocument();
$document->addStyleSheet(JURI::root(true).'/components/com_myjspace/assets/myjspace.min.css');
?>
<div class="myjspace myjsp-w-100">
<fieldset class="adminform">
<legend><?php echo JText::_('COM_MYJSPACE_ADMIN_SOME_HELP'); ?></legend>
<div><strong><?php echo JText::_('COM_MYJSPACE_ADMIN_INFO_0'); ?></strong></div>
<ul>
<?php
	$span_fin_ko = 'style="color:red">';

	$span_fin = '>';
	if (ini_get('file_uploads') != 1)
		$span_fin = $span_fin_ko;
	echo '<li>PHP file_uploads = <span '.$span_fin.ini_get('file_uploads')."</span></li>\n";

	echo '<li>PHP upload_tmp_dir = '.ini_get('upload_tmp_dir')."</li>\n";

	$span_fin = '>';
	if (convertBytes(ini_get('upload_max_filesize')) < $this->file_max_size)
		$span_fin = $span_fin_ko;
	echo '<li>PHP upload_max_filesize = <span '.$span_fin.convertBytes(ini_get('upload_max_filesize')).'</span> ('.JText::_('COM_MYJSPACE_ADMIN_SUPERIOR').' '.$this->file_max_size.' : '. JText::_('COM_MYJSPACE_LABELUSAGE2') . ")</li>\n";

	$span_fin = '>';
	if (convertBytes(ini_get('post_max_size')) < $this->file_max_size)
		$span_fin = $span_fin_ko;
	echo '<li>PHP post_max_size = <span '.$span_fin.convertBytes(ini_get('post_max_size')).'</span> ('.JText::_('COM_MYJSPACE_ADMIN_SUPERIOR').' '.$this->file_max_size.' : '. JText::_('COM_MYJSPACE_LABELUSAGE2') . ")</li>\n";

/*
   	file_uploads = On/Off permet d'autoriser ou non l'envoi de fichiers.
	upload_tmp_dir = répertoire permet de définir le répertoire temporaire permettant d'accueillir le fichier uploadé.
	upload_max_filesize = 2M permet de définir la taille maximale autorisée pour le fichier. 
		Si cette limite est dépassée, le serveur enverra un code d'erreur.
	post_max_size indique la taille maximale des données envoyées par un formulaire. 
		Cette directive prime sur upload_max_filesize, il faut donc s'assurer d'avoir post_max_size supérieure à upload_max_filesize 
*/
?>
</ul>
<div><strong><?php echo JText::_('COM_MYJSPACE_ADMIN_INFO_OTHER'); ?></strong></div>
<ul>
<?php
	// Editor
	echo '<li>';
	$check_editor = check_editor_selection($this->editor_selection);
	$span_fin = '> ';
	if ($check_editor == false)
		$span_fin = $span_fin_ko;
	echo JText::_('COM_MYJSPACE_ADMIN_EDITOR').' <span '.$span_fin.$this->editor_selection.'</span>';		
	if ($check_editor == false) // Use the Joomla default editor
		echo JText::_('COM_MYJSPACE_ADMIN_EDITOR_SELECTION');
	echo '</li>';
	
	if ($this->link_folder == 1) {
		// Root folder
		echo '<li>'.JText::_('COM_MYJSPACE_ADMIN_ISWRITABLE');
		if ($this->iswritable)
			echo JText::_('COM_MYJSPACE_ADMIN_FOLDER_OK');
		else
			echo JText::_('COM_MYJSPACE_ADMIN_FOLDER_KO');
		echo "</li>\n";
				
		// Index
		if ($this->nb_index_ko >= 0) {
			echo '<li>';
			if ($this->nb_index_ko == 0)
				echo JText::_('COM_MYJSPACE_ADMIN_INDEX_FORMAT_OK');
			else
				echo JText::sprintf('COM_MYJSPACE_ADMIN_INDEX_FORMAT_KO', 'index.php?option=com_myjspace&amp;task=adm_create_folder&'.myjsp_getFormToken().'=1');
			echo '</li>';
		}
	}
	
	// ACL 2.0.0+ for J1.6+
	if (version_compare(JVERSION, '1.6.0', 'ge') && $this->nb_max_page > 1 && $this->acl_rules_2000 == false)
		echo '<li>'.JText::_('COM_MYJSPACE_ADMIN_ACL_MSG').'</li>';	
	
	// GD
	echo '<li>'.JText::_('COM_MYJSPACE_ADMIN_GD');
	if ($this->gd_support == true)
		echo JText::_('COM_MYJSPACE_ADMIN_OK');
	else
		echo JText::_('COM_MYJSPACE_ADMIN_KO').JText::_('COM_MYJSPACE_LABELUSAGE4');
	echo "</li>\n";

	// Max. page(s) par user (config) and real
	if ($this->nb_max_page_per_user > $this->nb_max_page) {
		echo '<li>'.JText::sprintf('COM_MYJSPACE_NB_MAX_PAGE_PER_USER', $this->nb_max_page_per_user, $this->nb_max_page).'</li>';
	}
	
	// Model checks
	if ($this->error_model != '' || $this->warning_model != '') {
		echo '<li>'.JText::_('COM_MYJSPACE_TITLEMODEL').JText::_('COM_MYJSPACE_2POINTS');
		if ($this->error_model != '')
			echo '<span style="color:red">'.$this->error_model.'</span>';
		if ($this->warning_model != '')
			echo ' <span style="color:orange">'.$this->warning_model.'</span>';
		echo '</li>';
	}

	// At least one 'see' view
	echo "<li>".JText::_('COM_MYJSPACE_ATLEASTONEMENUSEE0');
	if (get_menu_itemid('index.php?option=com_myjspace&view=see') != 0)
		echo JText::_('COM_MYJSPACE_ADMIN_OK');
	else
		echo JText::_('COM_MYJSPACE_ATLEASTONEMENUSEE1');
	echo "</li>";

	// If templates usage configurated, the plugin system_myjsptemplateset need to be installed & enabled
	if (!$this->myjsptemplateset) {
		echo "<li>\n".JText::_('COM_MYJSPACE_PLUGINTEMPLATESET')."\n</li>\n";
	}
?>
</ul>
</fieldset>

<fieldset class="adminform">
<legend><?php echo JText::_('COM_MYJSPACE_ADMIN_REPORT'); ?></legend>
	<div><a href="#" id="link_sel_all" onclick="document.getElementById('report').select()"><?php echo JText::_('COM_MYJSPACE_ADMIN_REPORT_SELECT'); ?></a></div>
	<textarea id="report" name="report" style="width:100%; height:120px;"><?php echo htmlspecialchars($this->report, ENT_COMPAT, 'UTF-8'); ?></textarea>
</fieldset>

<br />
</div>
