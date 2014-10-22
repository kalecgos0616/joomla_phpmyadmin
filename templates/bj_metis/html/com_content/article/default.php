<?php
/**
 * @version		$Id: default.php 21518 2011-06-10 21:38:12Z chdemko $
 * @package		Joomla.Site
 * @subpackage	com_content
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers');

// Create shortcuts to some parameters.
$params		= $this->item->params;
$canEdit	= $this->item->params->get('access-edit');
$user		= JFactory::getUser();
?>

<div class="item-page<?php echo $this->pageclass_sfx?> tl_dong2">
  <?php if ($this->params->get('show_page_heading', 1)) : ?>
  <h2> <?php echo $this->escape($this->params->get('page_heading')); ?> </h2>
  <?php endif; ?>
  <div class="componentheading tl_manset">
    <h2>
      <?php $title = $this->escape($this->item->category_title); echo JText::sprintf( $title); ?>
    </h2>
  </div>
  <div class="contentpaneopen">
    <?php if ($params->get('show_title')) : ?>
    <div class="contentheading">
      <h1> <?php echo $this->escape($this->item->title); ?> </h1>
    </div>
    <?php endif; ?>
    <?php  if (!$params->get('show_intro')) :
	echo $this->item->event->afterDisplayTitle;
endif; ?>
    <?php echo $this->item->event->beforeDisplayContent; ?>
    <?php $useDefList = (($params->get('show_author')) OR ($params->get('show_category')) OR ($params->get('show_parent_category'))
	OR ($params->get('show_create_date')) OR ($params->get('show_modify_date')) OR ($params->get('show_publish_date'))
	OR ($params->get('show_hits'))); ?>
    <?php if ($useDefList) : ?>
    <div class="article-info byline">
      <?php endif; ?>
      <?php if ($params->get('show_author') && !empty($this->item->author )) : ?>
      <span class="createdby small">
      <?php $author = $this->item->created_by_alias ? $this->item->created_by_alias : $this->item->author; ?>
      <?php if (!empty($this->item->contactid) && $params->get('link_author') == true): ?>
      <?php
		$needle = 'index.php?option=com_contact&view=contact&id=' . $this->item->contactid;
		$item = JSite::getMenu()->getItems('link', $needle, true);
		$cntlink = !empty($item) ? $needle . '&Itemid=' . $item->id : $needle;
	?>
      <?php echo '<span>Written by </span>'. JText::sprintf(JHtml::_('link', JRoute::_($cntlink), $author)); ?>
      <?php else: ?>
      <?php echo '<span>Written by </span>'. JText::sprintf($author); ?>
      <?php endif; ?>
      </span>
      <?php endif; ?>
      <?php if ($params->get('show_create_date')) : ?>
      <span class="create createdate"> <?php echo '<span>Published on </span>'. JText::sprintf(JHtml::_('date',$this->item->created, JText::_('DATE_FORMAT_LC3'))); ?> </span>
      <?php endif; ?>
      <?php if ($params->get('show_modify_date')) : ?>
      <span class="modified createdate"> <?php echo '<span>Published on </span>'. JText::sprintf(JHtml::_('date',$this->item->modified, JText::_('DATE_FORMAT_LC3'))); ?> </span>
      <?php endif; ?>
      <?php if ($params->get('show_publish_date')) : ?>
      <span class="published createdate"> <?php echo '<span>Published on </span>'. JText::sprintf(JHtml::_('date',$this->item->publish_up, JText::_('DATE_FORMAT_LC3'))); ?> </span>
      <?php endif; ?>
      <div class="tl_tool">
      <?php if ($canEdit ||  $params->get('show_print_icon') || $params->get('show_email_icon')) : ?>
      <?php if (!$this->print) : ?>
      <?php if ($params->get('show_print_icon')) : ?>
      <span class="print-icon buttonheading"> <?php echo JHtml::_('icon.print_popup',  $this->item, $params); ?> </span>
      <?php endif; ?>
      <?php if ($params->get('show_email_icon')) : ?>
      <span class="email-icon buttonheading"> <?php echo JHtml::_('icon.email',  $this->item, $params); ?> </span>
      <?php endif; ?>
      <?php if ($canEdit) : ?>
      <span class="edit-icon buttonheading"> <?php echo JHtml::_('icon.edit', $this->item, $params); ?> </span>
      <?php endif; ?>
      <?php else : ?>
      <span class="buttonheading"> <?php echo JHtml::_('icon.print_screen',  $this->item, $params); ?> </span>
      <?php endif; ?>
      <?php endif; ?>
      </div>
      <?php if ($useDefList) : ?>
    </div>
    <?php endif; ?>
    <?php if ($params->get('access-view')):?>
    	<div class="tl_dong1_article"> <?php echo $this->item->text; ?> </div>
    <?php endif; ?> </div>
</div>
