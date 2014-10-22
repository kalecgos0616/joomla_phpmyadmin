<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $this->language; ?>" lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>" >
<head>
	<jdoc:include type="head" />
	<link rel="stylesheet" href="templates/system/css/general.css" type="text/css" />
	<link rel="stylesheet" href="templates/<?php echo $this->template; ?>/css/template.css" type="text/css" />
	<link rel="stylesheet" href="templates/<?php echo $this->template; ?>/css/<?php echo $this->params->get('contentstyle'); ?>.css" type="text/css" />
	<link rel="stylesheet" href="templates/<?php echo $this->template; ?>/css/<?php echo $this->params->get('topstyle'); ?>.css" type="text/css" />
	<link rel="stylesheet" href="templates/<?php echo $this->template; ?>/css/<?php echo $this->params->get('menustyle'); ?>.css" type="text/css" />
    <link rel="stylesheet" href="templates/<?php echo $this->template; ?>/css/<?php echo $this->params->get('leftwidth'); ?>_<?php echo $this->params->get('leftmodules'); ?>.css" type="text/css" />
    <?php if($this->params->get('leftwidth')=="left_240") {?>
        <link rel="stylesheet" href="templates/<?php echo $this->template; ?>/css/content_240_240.css" type="text/css" />
    <?php } else if($this->params->get('leftwidth')=="left_200") {?>
		<link rel="stylesheet" href="templates/<?php echo $this->template; ?>/css/content_200_200.css" type="text/css" />
     <?php } ?>
</head>

<body>

	<!-- Logo -->
	<div id="logo_001">

		<!-- Overlay -->
		<div id="overlay_001"></div>  

		<!-- Animation -->
		<div id="animation">
        	<img src="templates/<?php echo $this->template; ?>/images/anim_001.gif" border="0" alt="Animation" />
        </div>

	</div>

	<div id="mainframe">
	
    	<!-- TopMenu Module -->
		<div id="topmenu">
			<jdoc:include type="modules" name="posTopMenu" style="xhtml"/>
		</div>
		
		<br style="clear:left;" />
		
        <!-- Pathway Module -->
		<div id="pathway">
 			<jdoc:include type="modules" name="posPathway" style="xhtml"/>
		</div>
		
		<br style="clear:left;" />
		
        <!-- Left Modules -->
		<?php if($this->countModules('posLeft')) { ?>
        
        	<div id="leftcolumn">
				<jdoc:include type="modules" name="posLeft" style="jacket" />
			</div>
            
		<?php } ?>
		
        <!-- Dynamic Column Control  -->
		<?php if($this->countModules('posLeft')) { ?>
			<div id="maincolumn">
		<?php } else {?>
			<div id="maincolumn_off">
		<?php } ?>
        
		<blockquote style="clear:left;" />
	
    	<!-- Banner -->
		<div id="banner">
      		<jdoc:include type="modules" name="posBanner" style="xhtml"/>
		</div>
        
        <!-- Content and Components -->
		<div id="maincontent">

   			<jdoc:include type="message" />
			<jdoc:include type="component" />

		</div>

		</div>

	<br style="clear:both;" />
    
	</div>
    
	<!-- Bottom -->
	<div id="bottom"></div>
    
    <!-- Footer Module -->
	<?php if($this->countModules('posFooter')) { ?>
		<div id="footer">
     		<jdoc:include type="modules" name="posFooter" style="xhtml"/>
		</div>
	<?php } ?>

	<div id="Ads">
		<a href="http://tmp.hilliger-media.de" target="_blank"><img src="http://tmp.hilliger-media.de/images/banners/templateshop0001.gif" width="468" height="60" border="0" alt="Hilliger Media Shop" /></a>  
	</div>
	
	<div id="Coypright">
		Template "<strong><?php echo $this->template; ?></strong>" designed by <a href="http://www.hilliger-media.de" target="_blank">Hilliger Media</a> (Copyright &copy; 2011)<br />
	</div>
    
</body>
</html>


