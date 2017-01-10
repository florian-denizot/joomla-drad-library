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
$item = $displayData['item'];

$user		= JFactory::getUser();
$userId		= $user->get('id');


$listOrder	= $view->escape($view->get('state')->get('list.ordering'));
$listDirn	= $view->escape($view->get('state')->get('list.direction'));
$saveOrder 	= ($listOrder == 'a.ordering' && strtolower ($listDirn) == 'asc');

$canCheckin = $user->authorise('core.admin', 'com_checkin') || $item->checked_out == $userId || $item->checked_out == 0;
$canChange  = $user->authorise('core.edit.state', $view->get('state')->get('drad.option')) && $canCheckin;

?>

<td class="order nowrap center hidden-phone">
  <?php
  $iconClass = '';
  if (!$canChange)
  {
    $iconClass = ' inactive';
  }
  elseif (!$saveOrder)
  {
    $iconClass = ' inactive tip-top hasTooltip" title="' . JHtml::tooltipText('JORDERINGDISABLED');
  }
  ?>
  <span class="sortable-handler<?php echo $iconClass ?>">
    <i class="icon-menu"></i>
  </span>
  <?php if ($canChange && $saveOrder) : ?>
    <input type="text" style="display:none" name="order[]" size="5" value="<?php echo $orderkey + 1; ?>" />
  <?php endif; ?>
</td>