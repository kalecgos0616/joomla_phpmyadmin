<?php // no direct access
defined('_JEXEC') or die('Restricted access');

$app =& JFactory::getApplication();
JLoader::register('JAddons', JPATH_THEMES.DS.$app->getTemplate().DS.'read_tpl_params.php');

$componentheading  = JAddons::getTplParams()->get('componentheading');
?>

<?php JHTML::_('stylesheet', 'poll_bars.css', 'components/com_poll/assets/'); ?>

<form action="index.php" method="post" name="poll" id="poll">
<?php if ($this->params->get( 'show_page_title', 1)) : ?>
<div class="componentheading<?php echo $this->escape($this->params->get('pageclass_sfx')); ?>">
	<span class="left"></span><?php echo "<" . $componentheading . ">" . $this->escape($this->params->get('page_title')) . "</" . $componentheading . ">"; ?><span class="right"></span>
</div>
<?php endif; ?>
<div class="contentpane<?php echo $this->escape($this->params->get('pageclass_sfx')); ?>">
	<label for="id">
		<?php echo JText::_('Select Poll'); ?>
		<?php echo $this->lists['polls']; ?>
	</label>
</div>
<div class="contentpane<?php echo $this->escape($this->params->get('pageclass_sfx')); ?>">
<?php echo $this->loadTemplate('graph'); ?>
</div>
</form>
