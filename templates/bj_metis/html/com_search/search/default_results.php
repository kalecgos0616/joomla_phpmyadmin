<?php
/**
 * @version		$Id: default_results.php 21321 2011-05-11 01:05:59Z dextercowley $
 * @package		Joomla.Site
 * @subpackage	com_search
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;
?>

<dl class="search-results search_results contentpaneopen <?php echo $this->pageclass_sfx; ?>">
  <?php foreach($this->results as $result) : ?>
  <fieldset>
    <dt class="result-title"> <span class="small"><?php echo $this->pagination->limitstart + $result->count.'. ';?></span>
      <?php if ($result->href) :?>
      <a class="result-link" href="<?php echo JRoute::_($result->href); ?>"<?php if ($result->browsernav == 1) :?> target="_blank"<?php endif;?>> <?php echo $this->escape($result->title);?> </a>
      <?php else:?>
      <?php echo $this->escape($result->title);?>
      <?php endif; ?>
    </dt>
    <?php if ($result->section) : ?>
    <dd class="result-category"> <span class="small result-section<?php echo $this->pageclass_sfx; ?>"> Category: <?php echo $this->escape($result->section); ?> </span> </dd>
    <?php endif; ?>
    <dd class="result-text bj-introtext"> <?php echo $result->text; ?> </dd>
  </fieldset>
  <?php endforeach; ?>
</dl>
<div class="pagination" align="center"> <?php echo $this->pagination->getPagesLinks(); ?> </div>
