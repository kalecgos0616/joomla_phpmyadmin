<?php
/**
* @version $Id: myjspace.php $
* @version		2.0.3 21/10/2012
* @package		plg_quickiconmyjspace
* @author       Bernard Saulmé
* @copyright	Copyright (C) 2012 Bernard Saulmé
* @license      GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
*/

defined('_JEXEC') or die;
if (!defined('DS')) define('DS', DIRECTORY_SEPARATOR);

class plgQuickiconMyjspace extends JPlugin {

	public function __construct(& $subject, $config)
	{
		// Do no load if MyJspace is not installed and with the icon
		if (!file_exists(JPATH_ROOT.DS.'administrator'.DS.'components'.DS.'com_myjspace'.DS.'images'.DS.'myjspace.png')) 
			return false;

		$this->loadLanguage('plg_quickicon_myjspace.sys', JPATH_ADMINISTRATOR);

		parent::__construct($subject, $config);
	}
	
	/**
	 * Display MyJspace backend icon in Joomla 2.5+
	 *
	 * @param string $context
	 */
	public function onGetIcons($context)
	{
		if ($context != $this->params->get('context', 'mod_quickicon') || !JFactory::getUser()->authorise('core.manage', 'com_myjspace')) {
			return;
		}

		if (version_compare(JVERSION, '3.0', 'ge')) {
			$document = JFactory::getDocument();
			$document->addStyleSheet(JURI::root() . 'plugins/quickicon/myjspace/myjspace.css');
		
			return array(array(
				'link' => JRoute::_('index.php?option=com_myjspace'),
				'image' => 'myjspace',
				'text' => JText::_('PLG_QUICKICON_MYJSPACE_LABEL'),
				'id' => 'plg_quickicon_myjspace'
			));
		} else {
			return array(array(
				'link' => JRoute::_('index.php?option=com_myjspace'),
				'image' => 'myjspace/../../../administrator/components/com_myjspace/images/myjspace.png',
				'text' => JText::_('PLG_QUICKICON_MYJSPACE_LABEL'),
				'id' => 'plg_quickicon_myjspace'
			));
		}

	}
}

?>
