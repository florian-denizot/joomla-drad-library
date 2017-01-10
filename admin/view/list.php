<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  lib_drad
 *
 * @copyright   Copyright (C) 2015 Florian Denizot. All rights reserved.
 * @license     LTBD
 */

defined( '_JEXEC' ) or die;

abstract class DradAdminViewList extends JViewLegacy
{
  protected $items;

	protected $pagination;

	protected $state;  

  abstract protected function addSubmenu();
  
	/**
	 * Display the view.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise an Error object.
	 */
	public function display($tpl = null)
	{
		$this->items		= $this->get('Items');
		$this->pagination	= $this->get('Pagination');
		$this->state		= $this->get('State');
		$this->filterForm    = $this->get('FilterForm');
		$this->activeFilters = $this->get('ActiveFilters');
		
		// Hook to add submenu
    $this->addSubmenu();

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseError(500, implode("\n", $errors));

			return false;
		}
		
		// We don't need toolbar in the modal window.
		if ($this->getLayout() !== 'modal')
		{
			$this->addToolbar();
			$this->sidebar = JHtmlSidebar::render();
		}
		
		parent::display($tpl);
	}
	
	
	/**
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 */
	protected function addToolbar()
	{
		$user  = JFactory::getUser();
		$option = $this->state->get('drad.option');
		$asset_name = $option . '.' .DradUtilitiesInflect::singularize($this->getName());
		$controller_item = DradUtilitiesInflect::singularize($this->getName());
		$controller_list = $this->getName();
		
		// Get the toolbar object instance
		$bar = JToolBar::getInstance('toolbar');

		JToolbarHelper::title(JText::_(strtoupper($option . '_' . $this->getName()) . '_TITLE'), 'stack ' . $this->getName());

		if ($user->authorise('core.create', $asset_name))
		{
			JToolbarHelper::addNew($controller_item . '.add');
		}

		if (($user->authorise('core.edit', $asset_name)) 
						|| ($user->authorise('core.edit.own', $option)))
		{
			JToolbarHelper::editList($controller_item . '.edit');
		}

		if ($user->authorise('core.edit.state', $asset_name))
		{
			JToolbarHelper::publish($controller_list . '.publish', 'JTOOLBAR_PUBLISH', true);
			JToolbarHelper::unpublish($controller_list . '.unpublish', 'JTOOLBAR_UNPUBLISH', true);
			JToolbarHelper::archiveList($controller_list . '.archive');
			JToolbarHelper::checkin($controller_list . '.checkin');
		}

		// Add a batch button
		if ($user->authorise('core.create', $asset_name)
			&& $user->authorise('core.edit', $asset_name)
			&& $user->authorise('core.edit.state', $asset_name))
		{

			$title = JText::_('JTOOLBAR_BATCH');

			// Instantiate a new JLayoutFile instance and render the batch button
			$layout = new JLayoutFile('joomla.toolbar.batch');

			$dhtml = $layout->render(array('title' => $title));
			$bar->appendButton('Custom', $dhtml, 'batch');
		}

		if ($this->state->get('filter.published') == -2 && $user->authorise('core.delete', $asset_name))
		{
			JToolbarHelper::deleteList('', $controller_list . '.delete', 'JTOOLBAR_EMPTY_TRASH');
		}
		elseif ($user->authorise('core.edit.state', $asset_name))
		{
			JToolbarHelper::trash($controller_list . '.trash');
		}

		if ($user->authorise('core.admin', $option) || $user->authorise('core.options', $option))
		{
			JToolbarHelper::preferences($option);
		}
	}

	/**
	 * Returns an array of fields the table can be sorted by
	 *
	 * @return  array  Array containing the field name to sort by as the key and display text as value
	 */
	protected function getSortFields()
	{
		$sortFields =  array(
      'a.id'          => JText::_('JGRID_HEADING_ID'),
			'a.ordering'    => JText::_('JGRID_HEADING_ORDERING'),
			'a.title'       => JText::_('JGLOBAL_TITLE'),
      'a.published'   => JText::_('JSTATUS'),
			'access_level'  => JText::_('JGRID_HEADING_ACCESS'),
			'a.created_by'  => JText::_('JAUTHOR')
		);
    
    if($this->getModel()->manageLanguage())
    {
      $sortFields['a.language'] = JText::_('JGRID_HEADING_LANGUAGE');
    }
    
    return $sortFields;
	}
}