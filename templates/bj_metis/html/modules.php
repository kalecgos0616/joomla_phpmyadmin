<?php 

  function modChrome_mercury( $module, &$params, &$attribs ) 
  {
    $suffix = $params->get( 'moduleclass_sfx' );
	//echo ($suffix);
	if(!strpos($suffix,'nostyle')){
		
		echo '<div class="moduletable ' . $suffix . '" ><div class="boder-top"></div><div class="display-content">';
	 
		if ($module->showtitle) 
		{
		  $title = $module->title;
		  
		  $icon = '';
		  $pos = strpos($suffix,'typo-');
		  if($pos >= 0){
			$i = 0;
			for($i = $pos + 5; $i < strlen($suffix); $i++){
				if($suffix[$i] == ' '){
					break;
				}
			}
			$icon = '<span class="bjmod-icon '.substr($suffix,$pos,$i - $pos).'"></span>';
		  }
		  echo '<' . strtolower($attribs['heading']) . '>' . str_replace(" ","&nbsp;",$title) .'</' . strtolower($attribs['heading']) . '><div class="clearer"></div>';
		}
		
		$mod_content = $module->content;
		/*
		// find any image in content
		
		preg_match_all('@<img.+src="(.*)".*>@Uims', $mod_content, $matches);
		for($i = 0; $i < count($matches[1]); $i++) {
			$img_old = $matches[0][$i]; // image tag
			// check if image has width and height attributes
			if(!strpos(strtolower($img_old),"height")){
				$link = $matches[1][$i];
				try{
					list($width, $height) = @getimagesize($link);
					// now add width and height attribute to <img/> tag
					$img_new = str_replace("<img","<img height=\"".$height."px\" width=\"".$width."px\"",$img_old);
					// now replace the old content
					$mod_content = str_replace($img_old,$img_new,$mod_content);
				}
				catch(Exception $e){
					// so image is not valid, do nothing
				}
			}
		}*/
		
		
		echo $mod_content;
	 
		echo '</div></div><div class="clear"></div>';
	} else {
		/*$mod_content = $module->content;
		$title = '';
		if ($module->showtitle) 
		{
		  $title = '<' . strtolower($attribs['heading']) . '>'.$module->title.'</' . strtolower($attribs['heading']) . '>';
		}*/
		//echo '<div class="nostyle">'.$title.$mod_content.'</div>';
	}
  }
  
  function modChrome_bj_default( $module, &$params, &$attribs ) 
  {
    $suffix = $params->get( 'moduleclass_sfx' );
	$mod_content = $module->content;
	if(!strpos($suffix,'nostyle')){
		$bjmod_color = '';
		$bjmod_style = '';
		if(preg_match('/\s?(bjmod\-color\-\w*)\s?/',$suffix,$matches)){
			$bjmod_color = trim($matches[0]);
		}
		if($bjmod_color != '') $bjmod_color = substr($bjmod_color,12 - strlen($bjmod_color));
		
		if(preg_match('/\s?(bjmod\-style\-\w*)\s?/',$suffix,$matches)){
			$bjmod_style = trim($matches[0]);
		}
		
		echo '<div class="moduletable ' . $suffix . ($bjmod_style.$bjmod_color != ''?(' ' . $bjmod_style . '-' . $bjmod_color):'') . '" >';
	 
		if ($module->showtitle) 
		{
		  $title = $module->title;
		  
		  $icon = '';
		  $pos = strpos($suffix,'typo-');
		  if($pos && $pos >= 0){
			$i = 0;
			for($i = $pos + 5; $i < strlen($suffix); $i++){
				if($suffix[$i] == ' '){
					break;
				}
			}
			$icon = '<span class="bjmod-head-icon '.substr($suffix,$pos,$i - $pos).'"></span>';
		  }
		  echo '<h3>'. $icon . '<span class="bjmod-head-text">' . str_replace(" ","&nbsp;",$title) .'</span></h3>';
		}
		
		echo '<div class="bjmod-content">'.$mod_content.'</div>';
	 
		echo '</div>';
	} else {
		modChrome_xhtml( $module, $params, $attribs );
	}
  }
?>
