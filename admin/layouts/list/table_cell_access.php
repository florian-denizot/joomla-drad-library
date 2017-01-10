<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  lib_drad
 *
 * @copyright   Copyright (C) 2015 Florian Denizot. All rights reserved.
 * @license     LTBD
 */

defined('_JEXEC') or die;

$item = $displayData['item'];
$view = $displayData['view'];
?>

<td class="small hidden-phone">
  <?php echo $view->escape($item->access_level); ?>
</td>