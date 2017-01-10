<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  lib_drad
 *
 * @copyright   Copyright (C) 2016 Florian Denizot. All rights reserved.
 * @license     LTBD
 */

defined('_JEXEC') or die;

abstract class DradSiteViewList extends JViewLegacy
{
	/**
	 * @var    string  Default title to use for page title
	 * @since  3.2
	 */
	protected $defaultPageTitle = 'JGLOBAL_ARTICLES';


	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise an Error object.
	 */
	public function display($tpl = null)
	{
    $state  = $this->get('State');
		$items  = $this->get('Items');
    $this->filterForm   = $this->get('FilterForm');
    $this->pagination    = $this->get('Pagination');
    $this->activeFilters = $this->get('ActiveFilters');
    
    $app = JFactory::getApplication();

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			$app->enqueueMessage($errors, 'error');

			return false;
		}

		if ($items === false)
		{
			$app->enqueueMessage(JText::_('JGLOBAL_CATEGORY_NOT_FOUND'), 'error');

			return false;
		}
    
    $params = &$state->params;

		// Escape strings for HTML output
		$this->pageclass_sfx = htmlspecialchars($params->get('pageclass_sfx'), ENT_COMPAT, 'UTF-8');

		$this->params      = &$params;
		$this->items       = &$items;
    
		$this->prepareDocument();

		return parent::display($tpl);
	}

	/**
	 * Prepares the document
	 *
	 * @return  void
	 */
	protected function prepareDocument()
	{
		$app   = JFactory::getApplication();
		$menus = $app->getMenu();

		// Because the application sets a default page title, we need to get it from the menu item itself
		$menu = $menus->getActive();

		if ($menu)
		{
			$this->params->def('page_heading', $this->params->get('page_title', $menu->title));
		}
		else
		{
			$this->params->def('page_heading', JText::_($this->pageHeading));
		}

		$title = $this->params->get('page_title', '');

		if (empty($title))
		{
			$title = $app->get('sitename');
		}
		elseif ($app->get('sitename_pagetitles', 0) == 1)
		{
			$title = JText::sprintf('JPAGETITLE', $app->get('sitename'), $title);
		}
		elseif ($app->get('sitename_pagetitles', 0) == 2)
		{
			$title = JText::sprintf('JPAGETITLE', $title, $app->get('sitename'));
		}

		$this->document->setTitle($title);

		if ($this->params->get('menu-meta_description'))
		{
			$this->document->setDescription($this->params->get('menu-meta_description'));
		}

		if ($this->params->get('menu-meta_keywords'))
		{
			$this->document->setMetadata('keywords', $this->params->get('menu-meta_keywords'));
		}

		if ($this->params->get('robots'))
		{
			$this->document->setMetadata('robots', $this->params->get('robots'));
		}
	}
}
