<?php
/**
* @version $Id: view.php $
* @version		2.4.0 03/06/2014
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

require_once JPATH_COMPONENT_SITE.DS.'helpers'.DS.'search_fct.php';

class MyjspaceViewPages extends JViewLegacy
{
	function display($tpl = null)
	{
		require_once JPATH_COMPONENT_SITE.DS.'helpers'.DS.'user.php';
	
		// Config
		$user = JFactory::getuser();
		$db	= JFactory::getDBO();
		$app = JFactory::getApplication();

		// Component options
		$pparams = JComponentHelper::getParams('com_myjspace');
		$new_page_rview = $pparams->get('new_page_rview', 'config');
		$copy_page_rview = $pparams->get('copy_page_rview', 'config');
		$nb_max_page = $pparams->get('nb_max_page', 1);
		$share_page = $pparams->get('share_page', 0);

		// View param
		$lview = JRequest::getCmd('lview', 'see');
		$uid = JRequest::getInt('uid', 0);

		// Only 'my' (or uid) pages)
		if ($share_page != 0 && ($lview == 'edit' || $lview == 'see') && $uid <= 0 && $user->id != 0) { // List with shared pages with me
			$extra_query = " AND (`userid` = ".$db->Quote($user->id)." OR `access` IN (".implode(',', $user->getAuthorisedViewLevels())."))";
		} else {
			if ($uid > 0)
				$extra_query = ' AND `userid` = '.$uid;
			else
				$extra_query = ' AND `userid` = '.$db->Quote($user->id);
		}

		// Prepare display. If catid != 0 catid add criteria is added into the query into BSHelperViewSearch::pre_display()
		$total = BSHelperViewSearch::pre_display($this, 0, 5805, 0, 0, 0, 0, 0, $extra_query);

		// Breadcrumbs
		$sub_title = '';
		if ($lview == 'config')
			$sub_title = JText::_('COM_MYJSPACE_TITLECONFIG1');
		else if ($lview == 'edit')
			$sub_title = JText::_('COM_MYJSPACE_TITLEEDIT1');
		else if ($lview == 'delete')
			$sub_title = JText::_('COM_MYJSPACE_DELETE');
		else
			$sub_title = JText::_('COM_MYJSPACE_TITLESEE1');

		$pathway = $app->getPathway();
		$pathway->addItem($sub_title, '');

		// Assign
		$this->assignRef('uid',	$uid);
		$this->assignRef('lview', $lview);
		$this->assignRef('total', $total);
		$this->assignRef('nb_max_page', $nb_max_page);
		$this->assignRef('new_page_rview', $new_page_rview);
		$this->assignRef('copy_page_rview', $copy_page_rview);

		parent::display($tpl);
	}

	// Transform the page data to data to be displayed (options dependant)
	protected function transform_fields($inst, $i = 0) {
		$aff = BSHelperViewSearch::transform_fields($inst, $i);
		return $aff;
	}
}

?>
