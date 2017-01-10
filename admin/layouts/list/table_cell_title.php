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
$view = $displayData['view'];

$option = $view->get('state')->get('drad.option');
$item_controller = DradUtilitiesInflect::singularize($view->getName());

$user		= JFactory::getUser();
$userId		= $user->get('id');

$canEdit    = $user->authorise('core.edit', $option);
$canCheckin = $user->authorise('core.admin', 'com_checkin') || $item->checked_out == $userId || $item->checked_out == 0;
$canChange  = $user->authorise('core.edit.state', $option) && $canCheckin;
?>

<td>
  <?php if ($item->checked_out) : ?>
    <?php echo JHtml::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, $view->getName() . '.', $canCheckin); ?>
  <?php endif; ?>
  <?php if ($canEdit || $canEditOwn) : ?>
    <a href="<?php echo JRoute::_('index.php?option=' . $option . '&task=' . $item_controller . '.edit&id=' . $item->id ); ?>">
      <?php echo $this->escape($item->title); ?></a>
  <?php else : ?>
    <?php echo $this->escape($item->title); ?>
  <?php endif; ?>
  <span class="small">
    <?php echo JText::sprintf('JGLOBAL_LIST_ALIAS', $this->escape($item->alias)); ?>
  </span>
</td>