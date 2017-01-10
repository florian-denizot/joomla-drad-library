<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  lib_drad
 *
 * @copyright   Copyright (C) 2015 Florian Denizot. All rights reserved.
 * @license     LTBD
 */

defined('_JEXEC') or die;

class DradAdminViewItem extends JViewLegacy
{
	protected $form;

	protected $item;

	protected $state;
  
  protected $option;

	/**
	 * Display the view.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void
	 */
	public function display($tpl = null)
	{
		$this->form = $this->get('Form');    
		$this->item = $this->get('Item');
		$this->state = $this->get('State');
    $this->option = $this->state->get('drad.option');
    
		$this->canDo = JHelperContent::getActions($this->option, $this->getName(), $this->item->id);
    
		$input = JFactory::getApplication()->input;

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseError(500, implode("\n", $errors));

			return false;
		}

		$input->set('hidemainmenu', true);

		$this->addToolbar();
		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 */
	protected function addToolbar()
	{
		$user = JFactory::getUser();
		$userId = $user->get('id');

		$isNew = ($this->item->id == 0);
		$checkedOut = !($this->item->checked_out == 0 || $this->item->checked_out == $userId);


		// Need to load the menu language file as mod_menu hasn't been loaded yet.
		$lang = JFactory::getLanguage();
		$lang->load($this->option, JPATH_BASE, null, false, true)
		|| $lang->load($this->option, JPATH_ADMINISTRATOR . '/components/' . $this->option, null, false, true);

		// Load the element helper.
		require_once JPATH_COMPONENT . '/helpers/' . substr($this->option, 4) . '.php';

		// Get the results for each action.
		$canDo = $this->canDo;

		$title = JText::_(strtoupper($this->option . '_' . $this->getName()) . ($isNew?'_ADD':'_EDIT') . '_TITLE');

		// Prepare the toolbar.
		JToolbarHelper::title($title, $this->getName() . ' ' . $this->getName() . '-' . ($isNew?'add ':'edit ') . ($isNew?'add':'edit'));

		// For new records, check the create permission.
		if ($isNew && $user->authorise($this->option . '.' . $this->getName(), 'core.create'))
		{
			JToolbarHelper::apply($this->getName() . '.apply');
			JToolbarHelper::save($this->getName() . '.save');
			JToolbarHelper::save2new($this->getName() . '.save2new');
		}
    
		// If not checked out, can save the item.
		elseif (!$checkedOut && ($canDo->get('core.edit') || ($canDo->get('core.edit.own') && $this->item->created_by == $userId)))
		{
			JToolbarHelper::apply($this->getName() . '.apply');
			JToolbarHelper::save($this->getName() . '.save');

			if ($canDo->get('core.create'))
			{
				JToolbarHelper::save2new($this->getName() . '.save2new');
			}
		}

		// If an existing item, can save to a copy.
		if (!$isNew && $canDo->get('core.create'))
		{
			JToolbarHelper::save2copy($this->getName() . '.save2copy');
		}

		JToolbarHelper::cancel($this->getName() . '.cancel');		

		JToolbarHelper::divider();
	}
}
