<?php
/**
* @version $Id: view.html.php $
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

class MyjspaceViewSearch extends JViewLegacy
{
	function display($tpl = null)
	{
		// Config
		$app = JFactory::getApplication();
		$document = JFactory::getDocument();

		// Component
		$pparams = JComponentHelper::getParams('com_myjspace');

		// Prepare display
		BSHelperViewSearch::pre_display($this);

        // Web page title
		BSHelperViewSearch::title($this);

		// Breadcrumbs
		$pathway = $app->getPathway();
		$pathway->addItem(JText::_('COM_MYJSPACE_TITLESEARCH'), '');
		if (JRequest::getVar('layout', '') == 'sitemap') {
			parent::display('sitemap');
			exit;
		}

		parent::display($tpl);
	}

	// Transform the page data to data to be displayed (options dependant)
	protected function transform_fields($inst, $i = 0) {
		$aff = BSHelperViewSearch::transform_fields($inst, $i);
		return $aff;
	}
}
?>
