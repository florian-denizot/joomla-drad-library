<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  lib_drad
 *
 * @copyright   Copyright (C) 2015 Florian Denizot. All rights reserved.
 * @license     LTBD
 */

defined('_JEXEC') or die;

$view = $displayData['view'];
?>

<?php if (!empty( $view->sidebar)) : ?>
  <div id="j-main-container" class="span10">
<?php else : ?>
	<div id="j-main-container">
<?php endif;?>