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

$option = $view->get('state')->get('drad.option');

?>

<form action="<?php echo JRoute::_('index.php?option='. $option .'&view=' . $view->getName()); ?>" method="post" name="adminForm" id="adminForm">