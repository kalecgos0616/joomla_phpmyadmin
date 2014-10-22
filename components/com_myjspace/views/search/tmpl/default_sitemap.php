<?php
/**
* @version $Id: default_sitemap.php $
* @version		2.3.4 15/04/2014
* @package		com_myjspace
* @author       Bernard Saulmé
* @copyright	Copyright (C) 2014 Bernard Saulmé
* @license      GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

header('Content-type: text/xml; charset=utf-8');
echo '<?xml version="1.0" encoding="UTF-8"?>';
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">

<?php
	$nb = count($this->result);
	
	$uri = JURI::getInstance();
	$httphost = $uri->toString(array('scheme', 'host', 'port'));
		
	for ($i = 0; $i < $nb ; ++$i) {
		echo "\t<url\n>";
		// url
		if ($this->link_folder_print)
			$aff_url = JURI::base(true).'/'.$this->foldername.'/'.$this->result[$i]['pagename'].'/';
		else
			$aff_url = Jroute::_('index.php?option=com_myjspace&view=see&pagename='.$this->result[$i]['pagename'].'&Itemid='.$this->Itemid_see);
		echo "\t\t<loc>".$httphost.$aff_url."</loc>\n";
		echo "\t\t<changefreq>monthly</changefreq>\n";
//		echo "\t\t<priority>0.5</priority>\n";	
		echo "\t</url\n>";
	}
 ?>

</urlset>
