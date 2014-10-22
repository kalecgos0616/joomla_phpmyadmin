<?php
/**
 * @version		$Id: blog.php 20960 2011-03-12 14:14:00Z chdemko $
 * @package		Joomla.Site
 * @subpackage	com_content
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT.'/helpers');

?>

<div class="blog<?php echo $this->pageclass_sfx;?> bj-category-list">
  
  <div class="contentpaneopen" style="margin:0px;">
    <?php $leadingcount=0 ; ?>
    <?php if (!empty($this->lead_items)) : ?>
    <div class="items-leading">
      <?php foreach ($this->lead_items as &$item) : ?>
      <div class="leading-<?php echo $leadingcount; ?><?php echo $item->state == 0 ? ' system-unpublished' : null; ?> tl_dong6">
        <?php
				$this->item = &$item;
				echo $this->loadTemplate('item');
			?>
      </div>
      <?php
			$leadingcount++;
		?>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
    <?php
	$introcount=(count($this->intro_items));
	$counter=0;
?>
    <?php if (!empty($this->intro_items)) : ?>
    <?php foreach ($this->intro_items as $key => &$item) : ?>
    <?php
		$key= ($key-$leadingcount)+1;
		$rowcount=( ((int)$key-1) %	(int) $this->columns) +1;
		$row = $counter / $this->columns ;

		if ($rowcount==1) : ?>
    <div class="items-row cols-<?php echo (int) $this->columns;?> <?php echo 'row-'.$row ; ?>">
      <?php endif; ?>
      <div class="item column-<?php echo $rowcount;?><?php echo $item->state == 0 ? ' system-unpublished' : null; ?>">
        <?php
			$this->item = &$item;
			echo $this->loadTemplate('item');
		?>
      </div>
      <?php $counter++; ?>
      <?php if (($rowcount == $this->columns) or ($counter ==$introcount)): ?>
      <span class="row-separator"></span> </div>
    <?php endif; ?>
    <?php endforeach; ?>
    <?php endif; ?>
    <?php if (!empty($this->children[$this->category->id])&& $this->maxLevel != 0) : ?>
    <div class="cat-children">
      <h3> <?php echo JTEXT::_('JGLOBAL_SUBCATEGORIES'); ?> </h3>
      <?php echo $this->loadTemplate('children'); ?> </div>
    <?php endif; ?>
    <?php if (($this->params->def('show_pagination', 1) == 1  || ($this->params->get('show_pagination') == 2)) && ($this->pagination->get('pages.total') > 1)) : ?>
    <div class="pagination" align="center">
      <?php echo $this->pagination->getPagesLinks(); ?> </div>
    <?php  endif; ?>
  </div>
</div>
