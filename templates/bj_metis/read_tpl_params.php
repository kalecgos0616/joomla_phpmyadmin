<?php defined('_JEXEC') or die('Restricted access');
/* Thanks to mbiker (Joomla Forum: http://forum.joomla.org/viewtopic.php?f=466&t=517953) */

class JAddons{
   
   static public function getTplParams(){
      $app =& JFactory::getApplication();
      $cont = null;
      $ini   = JPATH_THEMES.DS.$app->getTemplate().DS.'params.ini';
      $xml   = JPATH_THEMES.DS.$app->getTemplate().DS.'templateDetails.xml';
      jimport('joomla.filesystem.file');
      if (JFile::exists($ini)) {
         $cont = JFile::read($ini);
      } else {
         $cont = null;
      }
      return new JParameter($cont, $xml, $app->getTemplate());      
   }
}

?>