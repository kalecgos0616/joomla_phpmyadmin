<?php
/**
* @version $Id: myjspace.php $
* @version		2.0.3 20/10/2012
* @package		com_myjspace
* @author       Bernard Saulmé
* @copyright	Copyright (C) 2012 Bernard Saulmé
* @license      GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
*/

// No direct access
defined('_JEXEC') or die;

class MyjspaceHelper
{
	/**
	 * Configure the Linkbar.
	 *
	 * @param	string	The name of the active view.
	 */
	public static function addSubmenu($vName = 'myjspace')
	{
        JSubMenuHelper::addEntry(JText::_('COM_MYJSPACE_HOME'), 'index.php?option=com_myjspace');
        JSubMenuHelper::addEntry(JText::_('COM_MYJSPACE_LINKS'), 'index.php?option=com_myjspace&view=url');
        JSubMenuHelper::addEntry(JText::_('COM_MYJSPACE_PAGES'), 'index.php?option=com_myjspace&view=pages');
		if (version_compare(JVERSION, '1.6.0', 'ge'))
			JSubMenuHelper::addEntry(JText::_('COM_MYJSPACE_CATEGORIES'), 'index.php?option=com_categories&extension=com_myjspace');
        JSubMenuHelper::addEntry(JText::_('COM_MYJSPACE_TOOLS'), 'index.php?option=com_myjspace&view=tools');
        JSubMenuHelper::addEntry(JText::_('COM_MYJSPACE_HELP'), 'index.php?option=com_myjspace&view=help');
	}

	/**
	 * Gets a list of the actions that can be performed.
	 *
	 * @param	int		The category ID.
	 * @return	JObject
	 */
	public static function getActions($categoryId = 0)
	{

	}
}
