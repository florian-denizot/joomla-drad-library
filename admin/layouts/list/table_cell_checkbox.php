<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  lib_drad
 *
 * @copyright   Copyright (C) 2015 Florian Denizot. All rights reserved.
 * @license     LTBD
 */

defined('_JEXEC') or die;

$i = $displayData['i'];
$item = $displayData['item'];
?>

<td class="center">
  <?php echo JHtml::_('grid.id', $i, $item->id); ?>
</td>