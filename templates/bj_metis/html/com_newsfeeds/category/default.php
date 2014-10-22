<?php // no direct access
defined('_JEXEC') or die('Restricted access'); 
$app =& JFactory::getApplication();
JLoader::register('JAddons', JPATH_THEMES.DS.$app->getTemplate().DS.'read_tpl_params.php');

$componentheading  = JAddons::getTplParams()->get('componentheading');
?>
<?php if ( $this->params->get( 'show_page_title', 1 ) ) : ?>
	<div class="componentheading<?php echo $this->escape($this->params->get('pageclass_sfx')); ?>"><span class="left"></span><?php echo "<" . $componentheading . ">" . $this->escape($this->params->get('page_title')) . "</" . $componentheading . ">"; ?><span class="right"></span></div>
<?php endif; ?>

<table width="100%" cellpadding="4" cellspacing="0" border="0" align="center" class="contentpane<?php echo $this->escape($this->params->get('pageclass_sfx')); ?>">
<?php if ( @$this->image || @$this->category->description ) : ?>
<tr>
	<td valign="top" class="contentdescription<?php echo $this->escape($this->params->get('pageclass_sfx')); ?>">
	<?php
		if ( isset($this->image) ) :  echo $this->image; endif;
		echo $this->category->description;
	?>
	</td>
</tr>
<?php endif; ?>
<tr>
	<td width="60%" colspan="2">
	<?php echo $this->loadTemplate('items'); ?>
	</td>
</tr>
</table>
