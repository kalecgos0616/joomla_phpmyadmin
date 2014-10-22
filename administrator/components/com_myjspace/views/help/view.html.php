<?php
/**
* @version $Id: view.html.php $
* @version		2.4.1 14/07/2014
* @package		com_myjspace
* @author       Bernard Saulmé
* @copyright	Copyright (C) 2010-2011-2012-2013-2014 Bernard Saulmé
* @license      GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die;
if (!defined('DS')) define('DS', DIRECTORY_SEPARATOR);

require_once JPATH_COMPONENT_SITE.DS.'helpers'.DS.'legacy.php';

jimport('joomla.application.component.view');

class MyjspaceViewHelp extends JViewLegacy
{
	/**
	 * display method of BSbanner view
	 * @return void
	 **/
	function display($tpl = null)
	{	
		require_once JPATH_COMPONENT_SITE.DS.'helpers'.DS.'user.php';
		require_once JPATH_COMPONENT_SITE.DS.'helpers'.DS.'util.php';
		require_once JPATH_COMPONENT_SITE.DS.'helpers'.DS.'user_event.php';
		require_once JPATH_COMPONENT_SITE.DS.'helpers'.DS.'util_legacy.php';
	
		// Menu bar
		JToolBarHelper::title(JText::_('COM_MYJSPACE_HOME').JText::_('COM_MYJSPACE_2POINTS').JText::_('COM_MYJSPACE_HELP'), 'help_header.png');

		// Content

		// Config
		$pparams = JComponentHelper::getParams('com_myjspace');
		$file_max_size = $pparams->get('file_max_size', 307200);
		$editor_selection = $pparams->get('editor_selection', 'myjsp');
		$nb_max_page = $pparams->get('nb_max_page', 1);
		$link_folder = $pparams->get('link_folder', 1);
		$nb_max_page = $pparams->get('nb_max_page', 1);
		$model_pagename = $pparams->get('model_pagename', '');
		
		// Page root folder
		$dirname = JPATH_ROOT.DS.BSHelperUser::getFoldername();
		if (is_writable($dirname))
			$iswritable = 1;
		else
			$iswritable = 0;
		
		// Check model page
		list($error_model, $warning_model) = BSUserEvent::model_pagename_valid();
		
		// Check page index format (=version)
		$nb_index_ko = BSHelperUser::CheckVersionIndexPage();

		// Env. report
		$report = configuration_report();
		
		// BS MyJspace config report
		$report .= ' [quote]';
		$report .= '[b]Editor selection:[/b] '.$editor_selection;
		$report .= ' | [b]Index Format:[/b] ';
		if ($nb_index_ko == 0)
			$report .= ' ok';
		else
			$report .= ' ko';

		$report .= ' | [b]Link as folder:[/b] ';			
		if ($link_folder == 1)
			$report .= ' yes';
		else
			$report .= ' no';
	
		$report .= ' | [b]Root Page dir:[/b] '.BSHelperUser::getFoldername();
		
		$report .= ' | [b]Root Page dir writable:[/b] ';
		if ($iswritable == 1)
			$report .= ' ok';
		else
			$report .= ' ko';
			
		$report .= ' | [b]Max. pages per user:[/b] '.$nb_max_page;
		
		if ($model_pagename) {
			$report .= ' | [b]Model page(s):[/b]'.$model_pagename;
			if ($error_model != '')
				$report .= ' | [b]Model page(s) check:[/b]'.$error_model;
		}
		$report .= '[/quote][confidential]';
		$report .= '[b]Nb. pages:[/b] '.BSHelperUser::myjsp_count_nb_page();
		$report .= ' | [b]Nb. users:[/b] '.BSHelperUser::myjsp_count_nb_user();
		$report .= '[/confidential]';		

				
		// ACL J!1.6+ (user.pages) Migration to Myjspace 2.0.0
		if (version_compare(JVERSION, '1.6.0', 'ge')) {
			$query = "SELECT COUNT(`rules`) FROM `#__assets` WHERE `title` = 'com_myjspace' AND `name` = 'com_myjspace' AND `rules` LIKE '%user.pages%'";
			$db	= JFactory::getDBO();
			$db->setQuery($query);
			$db->query();
			$count = $db->loadResult();
		}
		if (!isset($count))
			$count = 0;

		if ($count == 0)
			$acl_rules_2000 = false;
		else
			$acl_rules_2000 = true;

		// GD
		if (function_exists("gd_info"))
			$gd_support = true;
		else
			$gd_support = false;
	
		// Check the max. page per user (configuration) compared to the real usage
		$nb_max_page_per_user = BSHelperUser::myjsp_max_page_per_user();
		
		// If templates usage configurated, the plugin system_myjsptemplateset need to be installed & enabled
		$myjsptemplateset = 1;
		if (version_compare(JVERSION, '3.2.0', 'ge') && trim($pparams->get('template_list', '')) != '') {
			$myjsptemplateset = isset_cmp('myjsptemplateset', 'plugin');
		}	

		$this->assignRef('file_max_size', $file_max_size);
		$this->assignRef('iswritable', $iswritable);
		$this->assignRef('link_folder', $link_folder);
		$this->assignRef('editor_selection', $editor_selection);
		$this->assignRef('nb_index_ko', $nb_index_ko);
		$this->assignRef('report', $report);
		$this->assignRef('nb_max_page', $nb_max_page);
		$this->assignRef('nb_max_page_per_user', $nb_max_page_per_user);
		$this->assignRef('acl_rules_2000', $acl_rules_2000);
		$this->assignRef('gd_support', $gd_support);
		$this->assignRef('error_model', $error_model);
		$this->assignRef('warning_model', $warning_model);
		$this->assignRef('myjsptemplateset', $myjsptemplateset);

		parent::display($tpl);
	}
}

?>
