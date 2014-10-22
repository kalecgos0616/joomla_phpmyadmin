<?php
/**
 * @version		$Id: blog_item.php 21651 2011-06-23 05:31:49Z chdemko $
 * @package		Joomla.Site
 * @subpackage	com_content
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

// Create a shortcut for params.
$params = &$this->item->params;
$canEdit	= $this->item->params->get('access-edit');
JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');
JHtml::_('behavior.tooltip');
JHtml::core();

?>
<?php if ($this->item->state == 0) : ?>

<div class="system-unpublished">
  <?php endif; ?>
  <?php if ($params->get('show_title')) : ?>
  <div class="contentheading">
	<div class="tl_time"><?php echo '<h2>'.JHtml::_('date',$this->item->created, "d").'</h2>'.JHtml::_('date',$this->item->created, "M"); ?></div>
	<?php if ($params->get('show_print_icon') || $params->get('show_email_icon') || $canEdit) : ?>
		<div class="tl_tool">
			<ul class="actions">
				<?php if ($params->get('show_print_icon')) : ?>
				<li class="print-icon">
					<?php echo JHtml::_('icon.print_popup', $this->item, $params); ?>
				</li>
				<?php endif; ?>
				<?php if ($params->get('show_email_icon')) : ?>
				<li class="email-icon">
					<?php echo JHtml::_('icon.email', $this->item, $params); ?>
				</li>
				<?php endif; ?>
		
				<?php if ($canEdit) : ?>
				<li class="edit-icon">
					<?php echo JHtml::_('icon.edit', $this->item, $params); ?>
				</li>
				<?php endif; ?>
			</ul>
		</div>
	<?php endif; ?>
    <?php if ($params->get('link_titles') && $params->get('access-view')) : ?>
    <a href="<?php echo JRoute::_(ContentHelperRoute::getArticleRoute($this->item->slug, $this->item->catid)); ?>"> <?php echo $this->escape($this->item->title); ?></a>
    <?php else : ?>
    <?php echo $this->escape($this->item->title); ?>
    <?php endif; ?>
  
  
  <?php endif; ?>
  <?php if (!$params->get('show_intro')) : ?>
  <?php echo $this->item->event->afterDisplayTitle; ?>
  <?php endif; ?>
  <?php echo $this->item->event->beforeDisplayContent; ?>
  <?php // to do not that elegant would be nice to group the params ?>
  <?php if (($params->get('show_author')) or ($params->get('show_category')) or ($params->get('show_create_date')) or ($params->get('show_modify_date')) or ($params->get('show_publish_date')) or ($params->get('show_parent_category')) or ($params->get('show_hits'))) : ?>
  <p class="article-info byline">
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
      <?php if (($params->get('show_author')) or ($params->get('show_category')) or ($params->get('show_create_date')) or ($params->get('show_modify_date')) or ($params->get('show_publish_date')) or ($params->get('show_parent_category')) or ($params->get('show_hits'))) :?>
    </p>
  
  <?php endif; ?>
	  <div class="introtext">
	  <?php echo $this->item->introtext; ?>
	  </div>
  </div>
  
  <?php if ($this->item->state == 0) : ?>
</div>
<?php endif; ?>
<div class="item-separator"></div>
<?php echo $this->item->event->afterDisplayContent; ?> 