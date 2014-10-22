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
$document->addStyleSheet('components/com_myjspace/assets/myjspace.min.css');
if ($this->add_lightbox == 1) {
	$document->addStyleSheet('components/com_myjspace/assets/lytebox/lytebox.css');
	$document->addScript('components/com_myjspace/assets/lytebox/lytebox.js');
}
?>

<div class="myjspace-see" <?php if ($this->css_background) echo 'style="'.$this->css_background.'"'; ?>>
<?php 
	if ($this->show_tags == 1 && !empty($this->contenu->tags))
		echo $this->contenu->tagLayout->render($this->contenu->tags->itemTags);

	if ($this->edit_icon)
		echo $this->edit_icon;

	if ($this->allow_plugin > 1)
		echo $this->contenu->event->afterDisplayTitle . $this->contenu->event->beforeDisplayContent;
	echo $this->contenu->toc . $this->contenu->text;
	if ($this->allow_plugin > 1)
		echo $this->contenu->event->afterDisplayContent;
?>
</div>
