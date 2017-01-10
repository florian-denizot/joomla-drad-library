<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  lib_drad
 *
 * @copyright   Copyright (C) 2015 Florian Denizot. All rights reserved.
 * @license     LTBD
 */

defined( '_JEXEC' ) or die;

abstract class DradAdminModelList extends JModelList
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
	 * The context to use for the associations table
	 *
	 * @var     string
	 */
	protected $associationContext = '';
  
	
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
			'access', 'a.access', 'access_level',
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
      
      if(JLanguageAssociations::isEnabled())
      {
        $defaultConfig['filter_fields'][] = 'association';
        $defaultConfig['filter_fields'][] = 'a.association';
        
        if($drad_config->associationcontext)
        {
          $this->associationContext = (String)$drad_config->associationcontext;
        }
        else
        {
          $this->associationContext = $this->option.'.'.DradUtilitiesInflect::singularize($this->name);
        }
      }
		}
		
		if ($drad_config->tag == "true")
		{
      $this->manageTag = true;
			$defaultConfig['filter_fields'][] = 'tag';
		}
    
    if($drad_config->table)
    {
      $this->tableName = $drad_config->table;
    }
    else  
    {
      throw new Exception(JText::_('LIB_DRAD_ERROR_MESSAGE_NO_TABLE_NAME'), 1002);
    }
    
		
		if (!empty($config['filter_fields']))
		{
			$config['filter_fields'] = array_merge($defaultConfig['filter_fields'], $config['filter_fields']);
		}
		else 
		{
			$config = $defaultConfig;
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
	 * Build a list of authors
	 *
	 * @return  JDatabaseQuery
	 */
	public function getAuthors()
	{
		// Create a new query object.
		$db = $this->getDbo();
		$query = $db->getQuery(true);

		// Construct the query
		$query->select('u.id AS value, u.name AS text')
			->from('#__users AS u')
			->join('INNER', $this->tableName . ' AS c ON c.created_by = u.id')
			->group('u.id, u.name')
			->order('u.name');

		// Setup the query
		$db->setQuery($query);

		// Return the result
		return $db->loadObjectList();
	}
  
  /**
	 * Method to get a list of items.
	 * Overridden to add a check for access levels.
	 *
	 * @return  mixed  An array of data items on success, false on failure.
	 */
	public function getItems()
	{
		$items = parent::getItems();

		if (JFactory::getApplication()->isSite())
		{
			$user = JFactory::getUser();
			$groups = $user->getAuthorisedViewLevels();

			for ($x = 0, $count = count($items); $x < $count; $x++)
			{
				// Check the access level. Remove articles the user shouldn't see
				if (!in_array($items[$x]->access, $groups))
				{
					unset($items[$x]);
				}
			}
		}

		return $items;
	}
	
	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return  JDatabaseQuery
	 */
	protected function getListQuery()
	{
		// Create a new query object.
		$db = $this->getDbo();
		$query = $db->getQuery(true);
		$user = JFactory::getUser();

		// Select the required fields from the table.
		$query->select(
			$this->getState(
				'list.select',
				'a.id, a.title, a.alias, a. description, a.checked_out, a.checked_out_time' .
				', a.published, a.access, a.created_time, a.created_by, a.ordering'
			)
		);
		$query->from($this->tableName . ' AS a');

		// Join over the users for the checked out user.
		$query->select('uc.name AS editor')
			->join('LEFT', '#__users AS uc ON uc.id=a.checked_out');

		// Join over the asset groups.
		$query->select('ag.title AS access_level')
			->join('LEFT', '#__viewlevels AS ag ON ag.id = a.access');

		// Join over the users for the author.
		$query->select('ua.name AS author_name')
			->join('LEFT', '#__users AS ua ON ua.id = a.created_by');

		if ($this->manageLanguage())
		{
			// Join over the language
			$query->select('l.title AS language_title, l.image AS language_image, a.language')
				->join('LEFT', $db->quoteName('#__languages') . ' AS l ON l.lang_code = a.language');

			// Join over the associations.
			if (JLanguageAssociations::isEnabled())
			{
				$query->select('COUNT(asso2.id)>1 as association')
					->join('LEFT', '#__associations AS asso ON asso.id = a.id AND asso.context=' . $db->quote($this->associationContext))
					->join('LEFT', '#__associations AS asso2 ON asso2.key = asso.key')
					->group('a.id, l.title, uc.name, ag.title, ua.name');
			}
		}

		// Filter by access level.
		if ($access = $this->getState('filter.access'))
		{
			$query->where('a.access = ' . (int) $access);
		}

		// Implement View Level Access
		if (!$user->authorise('core.admin'))
		{
			$groups = implode(',', $user->getAuthorisedViewLevels());
			$query->where('a.access IN (' . $groups . ')');
		}

		// Filter by published state
		$published = $this->getState('filter.published');

		if (is_numeric($published))
		{
			$query->where('a.published = ' . (int) $published);
		}
		elseif ($published === '')
		{
			$query->where('(a.published = 0 OR a.published = 1)');
		}

		// Filter by author
		$authorId = $this->getState('filter.author_id');

		if (is_numeric($authorId))
		{
			$type = $this->getState('filter.author_id.include', true) ? '= ' : '<>';
			$query->where('a.created_by ' . $type . (int) $authorId);
		}

		// Filter by search in title.
		$search = $this->getState('filter.search');

		if (!empty($search))
		{
			if (stripos($search, 'id:') === 0)
			{
				$query->where('a.id = ' . (int) substr($search, 3));
			}
			elseif (stripos($search, 'author:') === 0)
			{
				$search = $db->quote('%' . $db->escape(substr($search, 7), true) . '%');
				$query->where('(ua.name LIKE ' . $search . ' OR ua.username LIKE ' . $search . ')');
			}
			else
			{
				$search = $db->quote('%' . str_replace(' ', '%', $db->escape(trim($search), true) . '%'));
				$query->where('(a.title LIKE ' . $search . ' OR a.alias LIKE ' . $search . ')');
			}
		}

		if ($this->manageLanguage())
		{
			// Filter on the language.
			if ($language = $this->getState('filter.language'))
			{
				$query->where('a.language = ' . $db->quote($language));
			}
		}

    if ($this->manageTag())
		{
      // Filter by a single tag.
      $tagId = $this->getState('filter.tag');

      if (is_numeric($tagId))
      {
        $query->where($db->quoteName('tagmap.tag_id') . ' = ' . (int) $tagId)
          ->join(
            'LEFT', $db->quoteName('#__contentitem_tag_map', 'tagmap')
            . ' ON ' . $db->quoteName('tagmap.content_item_id') . ' = ' . $db->quoteName('a.id')
            . ' AND ' . $db->quoteName('tagmap.type_alias') . ' = ' . $db->quote($this->context)
          );
      }
    }

		// Add the list ordering clause.
		$orderCol = $this->state->get('list.ordering', 'a.id');
		$orderDirn = $this->state->get('list.direction', 'asc');

		// SQL server change
		if ($this->manageLanguage())
		{
			if ($orderCol == 'language')
			{
				$orderCol = 'l.title';
			}
		}

		if ($orderCol == 'access_level')
		{
			$orderCol = 'ag.title';
		}

		$query->order($db->escape($orderCol . ' ' . $orderDirn));

		return $query;
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
		$id .= ':' . $this->getState('filter.search');
		$id .= ':' . $this->getState('filter.access');
		$id .= ':' . $this->getState('filter.published');
		$id .= ':' . $this->getState('filter.author_id');
		if ($this->manageLanguage())
		{
			$id .= ':' . $this->getState('filter.language');
		}

		return parent::getStoreId($id);
	}
  
  /*
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @param   string  $ordering   An optional ordering field.
	 * @param   string  $direction  An optional direction (asc|desc).
	 *
	 * @return  void
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		$app = JFactory::getApplication();

		$context = $this->context;
    
    $this->state->set('drad.option', $this->option);
		
		// Adjust the context to support modal layouts.
		if ($layout = $app->input->get('layout'))
		{
			$this->context .= '.' . $layout;
		}

		$search = $this->getUserStateFromRequest($context . '.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		$access = $this->getUserStateFromRequest($context . '.filter.access', 'filter_access');
		$this->setState('filter.access', $access);

		$authorId = $app->getUserStateFromRequest($context . '.filter.author_id', 'filter_author_id');
		$this->setState('filter.author_id', $authorId);

		$published = $this->getUserStateFromRequest($context . '.filter.published', 'filter_published', '');
		$this->setState('filter.published', $published);
    
		if ($this->manageLanguage())
		{
			$language = $this->getUserStateFromRequest($context . '.filter.language', 'filter_language', '');
			$this->setState('filter.language', $language);
		}

		if ($this->manageTag())
		{
			$tag = $this->getUserStateFromRequest($context . '.filter.tag', 'filter_tag', '');
			$this->setState('filter.tag', $tag);
		}

		// List state information.
		parent::populateState($ordering, $direction);

		if (JLanguageAssociations::isEnabled() && $this->manageLanguage())
		{
			// Force a language
			$forcedLanguage = $app->input->get('forcedLanguage');

			if (!empty($forcedLanguage))
			{
				$this->setState('filter.language', $forcedLanguage);
				$this->setState('filter.forcedLanguage', $forcedLanguage);
			}
		}
	}
}