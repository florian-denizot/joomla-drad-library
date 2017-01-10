<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  lib_drad
 *
 * @copyright   Copyright (C) 2016 Florian Denizot. All rights reserved.
 * @license     LTBD
 */

defined( '_JEXEC' ) or die;

abstract class DradSiteModelList extends JModelList
{
  /*
	 * @var boolean	Determines whether we have to manage languages for this element or not
	 */
	private $manageLanguage = false;
	
	/*
	 * @var boolean	Determines whether we have to manage tags for this element or not
	 */
	private $manageTag = false;
	
  /*
	 * @var string	Name of the table the model get its data from
	 */
	protected $tableName = '';
  
  /**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 */
	public function __construct($config = array())
	{
    $defaultConfig['filter_fields'] = array(
			'id', 'a.id',
			'title', 'a.title',
			'alias', 'a.alias',
			'checked_out', 'a.checked_out',
			'checked_out_time', 'a.checked_out_time',
			'published', 'a.published',
			'created_on', 'a.create_on',
			'created_by', 'a.created_by', 'author_id',
			'ordering', 'a.ordering',
		);
    
    if($this->drad_element)
    {
      $drad_config = DradUtilitiesConfig::load($this->drad_element);
    }
    else
    {
      throw new Exception(JText::_('LIB_DRAD_ERROR_MESSAGE_NO_ELEMENT'), 1001);
    }
    
		if ($drad_config->language == "true")
		{
      $this->manageLanguage = true;
      $defaultConfig['filter_fields'][] = 'language';
			$defaultConfig['filter_fields'][] = 'a.language';
		}
		
		if ($drad_config->tag == "true")
		{
      $this->manageTag = true;
			$defaultConfig['filter_fields'][] = 'filter_tag';
		}
    
    if($drad_config->table)
    {
      $this->tableName = $drad_config->table;
    }
    else  
    {
      throw new Exception(JText::_('LIB_DRAD_ERROR_MESSAGE_NO_TABLE_NAME'), 1002);
    }
    
		parent::__construct($config);
	}
  
  public function manageLanguage()
  {
    return $this->manageLanguage;
  }
  
  public function manageTag()
  {
    return $this->manageTag;
  }
  
  /**
	 * Method to auto-populate the model state.
	 *
	 * This method should only be called once per instantiation and is designed
	 * to be called on the first call to the getState() method unless the model
	 * configuration flag to ignore the request is set.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @param   string  $ordering   An optional ordering field.
	 * @param   string  $direction  An optional direction (asc|desc).
	 *
	 * @return  void
	 */
	protected function populateState($ordering = 'ordering', $direction = 'ASC')
	{
		$app = JFactory::getApplication();

		// List state information
		$limit = $app->input->get('limit', $app->get('list_limit', 0), 'uint');
		$this->setState('list.limit', $limit);

		$limitstart = $app->input->get('limitstart', 0, 'uint');
		$this->setState('list.start', $limitstart);
    
    if($this->manageTag)
    {
      $tag = $app->input->get('filter_tag', 0, 'uint');
      $this->setState('filter.tag', $tag);
    }

		$orderCol = $app->input->get('filter_order', 'a.ordering');

		if (!in_array($orderCol, $this->filter_fields))
		{
			$orderCol = 'a.ordering';
		}

		$this->setState('list.ordering', $orderCol);

		$listOrder = $app->input->get('filter_order_Dir', 'ASC');

		if (!in_array(strtoupper($listOrder), array('ASC', 'DESC', '')))
		{
			$listOrder = 'ASC';
		}

		$this->setState('list.direction', $listOrder);
    
    if ($this->manageTag())
		{
      $tagFilter = $app->input->get('filter_tag', 0, 'uint');
      $this->setState('filter.tag', $tagFilter);
    }

		$params = $app->getParams();
		$this->setState('params', $params);
		$user = JFactory::getUser();

		if ((!$user->authorise('core.edit.state', $this->option)) && (!$user->authorise('core.edit', $this->option)))
		{
			// Filter on published for those who do not have edit or edit.state rights.
			$this->setState('filter.published', 1);
		}
    
    if ($this->manageLanguage())
		{
      $this->setState('filter.language', JLanguageMultilang::isEnabled());
    }

		$this->setState('layout', $app->input->getString('layout'));
	}
  
  
  /**
	 * Method to get a store id based on model configuration state.
	 *
	 * This is necessary because the model is used by the component and
	 * different modules that might need different sets of data or different
	 * ordering requirements.
	 *
	 * @param   string  $id  A prefix for the store id.
	 *
	 * @return  string  A store id.
	 */
	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id .= ':' . serialize($this->getState('filter.published'));
		$id .= ':' . $this->getState('filter.access');

		return parent::getStoreId($id);
	}
  
  
  /**
	 * Build an SQL query to load the list data.
	 *
	 * @return  JDatabaseQuery
	 */
	protected function getListQuery()
	{
    // Get the current user for authorisation checks
		$user = JFactory::getUser();
   
		// Create a new query object.
		$db = $this->getDbo();
		$query = $db->getQuery(true);

		// Select the required fields from the table.
		$query->select(
			$this->getState(
				'list.select',
				'a.id, a.title, a.alias, a. description, a.checked_out, a.checked_out_time' .
				', a.published, a.access, a.created_time, a.created_by, a.ordering'
			)
		);
		$query->from($this->tableName . ' AS a');
    
    // Filter by access level.
		if ($access = $this->getState('filter.access'))
		{
			$groups = implode(',', $user->getAuthorisedViewLevels());
			$query->where('a.access IN (' . $groups . ')')
				->where('c.access IN (' . $groups . ')');
		}

		// Filter by published state
		$published = $this->getState('filter.published');

		if (is_numeric($published))
		{
			$query->where('a.published = ' . (int) $published);
		}
		elseif (is_array($published))
		{
			$published = ArrayHelper::toInteger($published);
			$published = implode(',', $published);

			$query->where('a.published IN (' . $published . ')');
		}
    
    // Filter by language
    if ($this->manageLanguage())
		{
      if ($this->getState('filter.language'))
      {
        $query->where('a.language in (' . $db->quote(JFactory::getLanguage()->getTag()) . ',' . $db->quote('*') . ')');
      }
    }
    
    // Filter by a single tag.
    if($this->manageTag)
    {
      $tagId = $this->getState('filter.tag');

      if (!empty($tagId) && is_numeric($tagId))
      {
        $query->where($db->quoteName('tagmap.tag_id') . ' = ' . (int) $tagId)
          ->join(
            'LEFT', $db->quoteName('#__contentitem_tag_map', 'tagmap')
            . ' ON ' . $db->quoteName('tagmap.content_item_id') . ' = ' . $db->quoteName('a.id')
            . ' AND ' . $db->quoteName('tagmap.type_alias') . ' = ' . $db->quote('com_content.article')
          );
      }
    }
    
		// Add the list ordering clause.
		$query->order($this->getState('list.ordering', 'a.ordering') . ' ' . $this->getState('list.direction', 'ASC'));

		return $query;
  }
  
  /**
	 * Method to get a list of articles.
	 *
	 * Overriden to inject convert the attribs field into a JParameter object.
	 *
	 * @return  mixed  An array of objects on success, false on failure.
	 */
	public function getItems()
	{
    return parent::getItems();
  }
}