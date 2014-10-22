<?php
defined('_JEXEC') or die('Restricted access');

function modChrome_jacket($module, &$params, &$attribs)
{ ?>
		<div class="module<?php echo $params->get('moduleclass_sfx'); ?>">

			    <div class="module_hat"><?php if ($module->showtitle != 0) : ?><h3><?php echo $module->title; ?></h3><?php endif; ?></div>	
                <div class="module_jacket">
				   <br style="clear:both;" /><div class="module_content"><?php echo $module->content; ?></div><br style="clear:both;" />
				</div>
				<div class="module_tail"></div>
		</div>
	<?php
}
?>