<?php
/**
* @version $Id: view.html.php $ 
* @version		2.3.5 13/05/2014
* @package		com_myjspace
* @author       Bernard Saulmé
* @copyright	Copyright (C) 2011-2012-2013-2014 Bernard Saulmé
* @license      GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die;
if (!defined('DS')) define('DS', DIRECTORY_SEPARATOR);

require_once JPATH_COMPONENT_SITE.DS.'helpers'.DS.'legacy.php';

jimport('joomla.application.component.view');

class MyjspaceViewCreatepage extends JViewLegacy
{
	/**
	 * display method of BSbanner view
	 * @return void
	 **/
	function display($tpl = null)
	{
        require_once JPATH_COMPONENT_SITE.DS.'helpers'.DS.'user_event.php';
		
		// Menu bar
		if (version_compare(JVERSION, '1.6.0', 'ge'))
			$add_icon = 'article-add.png';
		else
			$add_icon = 'addedit.png';

		JToolBarHelper::title(JText::_('COM_MYJSPACE_HOME').JText::_('COM_MYJSPACE_2POINTS').JText::_('COM_MYJSPACE_CREATEPAGE'), $add_icon);
		JToolBarHelper::apply('adm_create_page');	
		JToolBarHelper::divider();

		// Build the script
		$script = array();
		$script[] = 'function jSelectUser_jform_created_by(id, title) {';
		$script[] = '	var old_id = document.getElementById("mjs_userid").value;';
		$script[] = '	if (old_id != id) {';
		$script[] = '		document.getElementById("mjs_username2").value = title;';
		$script[] = '		document.getElementById("mjs_userid").value = id;';
		$script[] = '	}';
		$script[] = '	SqueezeBox.close();';
		$script[] = '}';

		// Add the script to the document head
		JFactory::getDocument()->addScriptDeclaration(implode("\n", $script));
		
		$model_page_list = BSUserEvent::model_pagename_list(); // Model page list
		$this->assignRef('model_page_list', $model_page_list);
		
		parent::display($tpl);
	}
}

?>
