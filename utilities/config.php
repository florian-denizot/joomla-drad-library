<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  lib_drad
 *
 * @copyright   Copyright (C) 2015 Florian Denizot. All rights reserved.
 * @license     LTBD
 */

class DradUtilitiesConfig {
  /*
   * Constructor.
	 *
	 * @param   string  $element  Name of the element to load in the DRAD xml config file
   */
  public static function load($element) 
  {
    $filePath = JPATH_COMPONENT_ADMINISTRATOR . '/drad.xml';
    
    $config = simplexml_load_file($filePath) or die("Error: Cannot load DRAD XML config file");
    
    $found = false;
    
    foreach($config->children() as $child)
    {
      if($child['name'] == $element)
      {
        $elementConfig = $child;
        $found = true; 
        break;
      }
    }
    
    if(!$found)
    {
      return false;
    }
    
    return $elementConfig;
  }
}
