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

$listOrder	= $view->escape($view->get('state')->get('list.ordering'));
$listDirn	= $view->escape($view->get('state')->get('list.direction'));
?>

<th width="1%" class="nowrap center">
  <?php echo JHtml::_('searchtools.sort', 'JSTATUS', 'a.published', $listDirn, $listOrder); ?>
</th>