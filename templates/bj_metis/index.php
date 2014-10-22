<?php
// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );
error_reporting(0);
/* === Get Template parameters ================================== */
$template_color = $this->params->get("templateColor","blue");

$_align = $this->params->get("textalign","LTR");
$_logoPath = $this->params->get("logoPath","");
$_logoLink = $this->params->get("logoLink","/");
$_logoSlogan = $this->params->get("logoSlogan","BJ Metis");

$_tempWidth = $this->params->get("templateWidth",'980px');
$_tempLeftCol= $this->params->get("leftWidth",'645px');
$_tempRightCol = $this->params->get("rightWidth",'310px');

$_usersALayout = $this->params->get("usersALayout",'0');
$_usersBLayout = $this->params->get("usersBLayout",'0');
$_usersCLayout = $this->params->get("usersCLayout",'0');

$_user1w = $this->params->get("user1w",'33%');
$_user2w = $this->params->get("user2w",'33%');
$_user3w = $this->params->get("user3w",'33%');
$_user4w = $this->params->get("user4w",'32%');
$_user5w = $this->params->get("user5w",'32%');
$_user6w = $this->params->get("user6w",'32%');
$_user7w = $this->params->get("user7w",'32%');
$_user8w = $this->params->get("user8w",'32%');
$_user9w = $this->params->get("user9w",'32%');

$_column = $this->params->get("columns",'left,main,right');
$gototop = $this->params->get("gototop",'1');

$comt = JRequest::getVar('option','');
$color = JRequest::getVar('changecolor',''); // use whatever name you want instead of changecolor
switch($color){
	case 'blue': $template_color = 'blue';break;
}

$itemId = JRequest::getVar('Itemid',0);
switch($itemId){
	case 122: $template_color = 'blue';break;
}

$itemId = JRequest::getVar('Itemid','');
$pathway_text = $this->params->get("pathway_text","");

/* === End parameters =========================================== */
$template_name = 'bj_metis';
define( '_TEMPLATE_PATH', JPATH_BASE . DS . 'templates' . DS .$template_name);
define('_TEMPLATE_URL',JURI::base().'templates/'.$template_name);
//include_once(_TEMPLATE_URL . '/func/mobile.php');

// if we are in homepage?
$inHomepage = false;
///////////////////////////////////////////////////////////////
$Itemid = 0;
if ($Itemid == 1) {
	$inHomepage = true;
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $this->language; ?>" lang="<?php echo $this->language; ?>" >

<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<jdoc:include type="head" />
<?php
	$arrColumn = explode(',',$_column);
	$col1_count = 0;$col2_count = 0;$col3_count = 0;
	$left = 0;
	$right = 0;
	$headline = 0;
	$user1 = 0;$user2 = 0;$user3 = 0;$user4 = 0;$user5 = 0;$user6 = 0;$user7 = 0;$user8 = 0;$user9 = 0;

	$advert1 = 0;
	$toolbar = 0;
	$footer = 0;
	$pathway = 0;
	$search = 0;
	if($this->countModules('user1')) { $user1 = 1;$col1_count++;}
	if($this->countModules('user2')) { $user2 = 1;$col1_count++;}
	if($this->countModules('user3')) { $user3 = 1;$col1_count++;}
	
	if($this->countModules('user4')) { $user4 = 1;$col2_count++;}
	if($this->countModules('user5')) { $user5 = 1;$col2_count++;}	
	if($this->countModules('user6')) { $user6 = 1;$col2_count++;}
	
	if($this->countModules('user7')) { $user7 = 1;$col3_count++;}	
	if($this->countModules('user8')) { $user8 = 1;$col3_count++;}	
	if($this->countModules('user9')) { $user9 = 1;$col3_count++;}	
	
	if($this->countModules('left')) { $left = 1;}
	if($this->countModules('right')) { $right = 1;}
	if($this->countModules('advert1')) { $advert1 = 1;}	
	
	if($this->countModules('toolbar')){$toolbar=1;}
	
	if($this->countModules('headline')){$headline=1;}
	if($this->countModules('pathway')){$pathway=1;}
	if($this->countModules('footer')){$footer=1;}
	if($this->countModules('search')){$search=1;}
	
	if(sizeof($arrColumn)==3){
		$strCoumn = '';
		foreach($arrColumn as $key => $value){
			if($left==$value && $right!=$value){
				$strCoumn .= $value . ',';
			}elseif($right==$value && $left!=$value){
				$strCoumn .= $value . ',';
			}elseif($value == 'main'){
				$strCoumn .= $value . ',';
			}
		}
		$arrColumn = explode(',',$strCoumn);
		unset($arrColumn[sizeof($arrColumn)-1]);
	}
?>
<?php 
include_once(_TEMPLATE_PATH.'/func/mobile.php');
$check_browser = mobile_device_detect();
//$check_os = check_os();

$theme_name = 'theme1';
$file = JPATH_BASE.DS.'components'.DS.'com_bjthemes'.DS.'bj_themeloader.php';
$load_theme_result = '';//$check_browser[0];
if($load_theme_result){
	
	echo'Tempalte for mobile here';	
}elseif(!$load_theme_result){
	
	$moduleheading = $this->params->get("moduleheading","h3");
	$view = (isset($_GET['view']))?$_GET['view']:'';
?>
<script type="text/javascript" src="<?php echo _TEMPLATE_URL ?>/func/jquery.js"></script>

<link rel="stylesheet" type="text/css" href="<?php echo _TEMPLATE_URL ?>/css/reset.css" />
<link rel="stylesheet" type="text/css" href="<?php echo _TEMPLATE_URL ?>/css/blue.css" />

<link rel="stylesheet" type="text/css" href="<?php echo _TEMPLATE_URL ?>/css/custom.css" />
<link rel="stylesheet" type="text/css" href="<?php echo _TEMPLATE_URL ?>/css/base.css" />
<link rel="stylesheet" type="text/css" href="<?php echo _TEMPLATE_URL ?>/css/typography.css" />

<?php if($_align=='RTL'){
    echo('<link rel="stylesheet" type="text/css" href="'. _TEMPLATE_URL .'/css/rtl.css" />');
}?>
<!--[if IE 7]>
<link rel="stylesheet" type="text/css" href="<?php echo _TEMPLATE_URL ?>/css/ie7.css" />
<![endif]-->

<!--[if IE ]>
<script type="text/javascript" src="<?php echo _TEMPLATE_URL ?>/func/PIE.js"></script>

<![endif]-->

</head>
<!--style="background:url(<?php //echo _TEMPLATE_URL ?>/index2.jpg) no-repeat center top;"-->
<body >
<div class="tl_top">
	<div class="tl_top_p">    	
        <div class="tl_top_logo">
        	<?php if($_logoLink){?>
				<a href="<?php echo  $_logoLink;?>" title="<? echo $_logoSlogan;?>">
			<?php }?>
			<?php if($_logoPath == ''){
					switch($template_color){
						default: $src = _TEMPLATE_URL . '/images/logo.png';break;
					}
                        } else { $src = $_logoPath; }
			?>
			<img src="<?php echo  $src; ?>" alt="<? echo $_logoSlogan;?>"/>
			<?php if($_logoLink){?>	</a> <?php }?>
        </div>
        <div class="tl_top_menu"><jdoc:include type="modules" name="toolbar" style="xhtml" /></div> 
        <div class="clear"></div>
              
    </div>
</div>
<div class="clear"></div>
<?php if($advert1):?>
<div class="tl_banner">
    <div class="tl_page">
        <div class="tl_banner_a">
            <jdoc:include type="modules" name="advert1" />
        </div>
    </div>
</div>
<div class="clear"></div>
<!--tl_banner-->
<?php endif;?> 
<?php if($headline):?>
<div class="tl_page">
    <div class="tl_title_1" id="TL_Headline">
        <jdoc:include type="modules" name="headline" />
    </div>
    <!--tl_title_1-->
</div>
<?php endif;?> 

<div class="tl_page">
    <?php if($user1 || $user2 || $user3):?>
    <div class="tl_service">
    	<ul>
        	<?php if($user1):?><li class="column" id="user1"><jdoc:include type="modules" name="user1" style="xhtml" /></li><?php endif;?>
            <?php if($user2):?><li class="column" id="user2"><jdoc:include type="modules" name="user2" style="xhtml" /></li><?php endif;?>
            <?php if($user3):?><li class="column" id="user3"><jdoc:include type="modules" name="user3" style="xhtml" /></li><?php endif;?>
        </ul>
        <div class="clear"></div>
    </div>
    <?php endif;?>
    <?php if($user4 || $user5 || $user6):?>
    	<div class="tl_3column">
        	<ul>
            	<?php if($user4):?><li class="column" id="user4"><jdoc:include type="modules" name="user4" style="xhtml" /></li><?php endif;?>
                <?php if($user5):?><li class="column" id="user5"><jdoc:include type="modules" name="user5" style="xhtml" /></li><?php endif;?>
                <?php if($user6):?><li class="column" id="user6"><jdoc:include type="modules" name="user6" style="xhtml" /></li><?php endif;?>               
            </ul>
            <div class="clear"></div>
       	</div> 
   <?php endif;?>
    
    <div class="tl_cot_to" id="BJ_Main_Col">
    	<jdoc:include type="component" />
    </div>
    <!--tl_cot_to-->
    <?php if($right):?>
        <div class="tl_cot_nho" id="BJ_Right_Col">    	
             <jdoc:include type="modules" name="right" heading="<?php echo  $moduleheading;?>" style="mercury" />
        </div>
    <?php endif;?>
    <!--tl_cot_nho-->
    <div class="clear"></div>
    <?php if($pathway):?>
    	<div class="tl_here"><jdoc:include type="modules" name="pathway" style="xhtml" /></div>
    <?php endif;?>
</div>
<!--tl_page-->

<div class="tl_footer">
	<?php if($user7 || $user8 || $user9):?>
    	<div class="tl_page">
    		<?php if($user7):?>
            	<div class="tl_foo_c1 column" id="user7">
                	<jdoc:include type="modules" name="user7" style="xhtml" />
                </div>
                <!--tl_foo_c1-->
            <?php endif;?>
            <?php if($user8):?>
            	<div class="tl_foo_c2 column" id="user8">
	            	<jdoc:include type="modules" name="user8" style="xhtml" /> 
                </div>
                <!--tl_foo_c2-->
            <?php endif;?>
            <?php if($user9):?>
            	<div class="tl_foo_c3 column" id="user9">
	            	<jdoc:include type="modules" name="user9" style="xhtml" /> 
                </div>
                <!--tl_foo_c3-->
            <?php endif;?>
            <div class="clear"></div>
            <div class="tl_foot" style="background: none;"></div>
            <?php if($footer) {?>
            <jdoc:include type="modules" name="footer" style="xhtml" />
            <?php } else {
			include_once(_TEMPLATE_PATH.'/css/bottom.css.php'); } ?>
    	</div>
    <!--tl_page-->
    <?php endif;?>
    
</div>
<?php include_once(_TEMPLATE_PATH . DS . 'css' . DS . 'footer.css.php') ?>
<!--tl_footer-->
</body>

<?php }?>
</html>