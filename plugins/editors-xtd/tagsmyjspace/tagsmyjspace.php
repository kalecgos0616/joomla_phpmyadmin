<?php
/**
* @version $Id: tagsmyjspace.php $
* @version		2.4.0 01/06/2014
* @package		plg_tagsmyjspace
* @author       Bernard Saulmé
* @copyright	Copyright (C) 2012-2013-2014 Bernard Saulmé
* @license      GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
*/

// no direct access
defined('_JEXEC') or die;

jimport('joomla.html.parameter'); // >= J1.6

/**
 * Editor Tags MyJspace buton
 *
 */
class plgButtonTagsMyjspace extends JPlugin
{
	/**
	 * Constructor
	 */
	public function __construct(&$subject, $config)
	{
		parent::__construct($subject, $config);
		$this->loadLanguage();
	}

	/**
	 * Display the button
	 *
	 * @return array A two element array of (imageName, textToInsert)
	 */
	public function onDisplay($name)
	{
		$component_name = JRequest::getVar('option', '');
		$pparams = JComponentHelper::getParams('com_myjspace');

		$button = new JObject;

		if ($component_name == 'com_myjspace' && $pparams->get('allow_user_content_var', 1) == 1) {
			JHtml::_('behavior.modal');

			$app = JFactory::getApplication();
			$template = $app->getTemplate();
			$document = JFactory::getDocument();

			if (version_compare(JVERSION, '1.6.0', 'lt')) {
				$class_tagmyjspace = 'readmore';
			} else if (version_compare(JVERSION, '3.0.0', 'lt')) {
				$class_tagmyjspace = 'readmore';
			} else {
				$class_tagmyjspace = $this->params->get('class_tagmyjspace', 'tagsmyjspace');
				$button->set('class', 'btn');
			}

			if ($class_tagmyjspace == 'tagsmyjspace')
				$document->addStyleSheet('plugins/editors-xtd/tagsmyjspace/tagsmyjspace.css');

			if (version_compare(JVERSION, '1.6.0', 'lt'))
				JPlugin::loadLanguage('plg_editors-xtd_tagsmyjspace', JPATH_ADMINISTRATOR);

			$link = 'index.php?option=com_myjspace&amp;view=edit&amp;layout=tags&amp;tmpl=component&amp;e_name='.$name;

			$button->set('modal', true);
			$button->set('link', $link);
			$button->set('text', JText::_('PLG_EDITORSXTD_MYJSPACE_BUTTON_TAGS'));
			$button->set('name', $class_tagmyjspace);
			$button->set('options', "{handler: 'iframe', size: {x: 400, y: 200}}");
		}

		return $button;
	}
}

?>
