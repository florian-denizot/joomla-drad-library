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

$user		= JFactory::getUser();
$userId		= $user->get('id');

$canCheckin = $user->authorise('core.admin', 'com_checkin') || $item->checked_out == $userId || $item->checked_out == 0;
$canChange  = $user->authorise('core.edit.state', $view->get('state')->get('drad.option')) && $canCheckin;
?>

 <td class="center">
  <?php echo JHtml::_('jgrid.published', $item->published, $i, $view->getName() . '.', $canChange); ?>
</td>