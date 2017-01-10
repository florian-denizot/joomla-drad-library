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
  <?php if ($item->language == '*'):?>
    <?php echo JText::alt('JALL', 'language'); ?>
  <?php else:?>
    <?php echo $item->language_title ? JHtml::_('image', 'mod_languages/' . $item->language_image . '.gif', $item->language_title, array('title' => $item->language_title), true) . '&nbsp;' . $this->escape($item->language_title) : JText::_('JUNDEFINED'); ?>
  <?php endif;?>
</td>