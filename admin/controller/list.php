<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  lib_drad
 *
 * @copyright   Copyright (C) 2015 Florian Denizot. All rights reserved.
 * @license     LTBD
 */

defined( '_JEXEC' ) or die;

abstract class DradAdminControllerList extends JControllerAdmin
{
  /**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 */
  public function __construct($config = array())
	{
		parent::__construct($config);
    $this->text_prefix = strtoupper($this->option . '_'. $this->view_list);
  }
}

