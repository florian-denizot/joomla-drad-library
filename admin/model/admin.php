<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  lib_drad
 *
 * @copyright   Copyright (C) 2015 Florian Denizot. All rights reserved.
 * @license     LTBD
 */

defined( '_JEXEC' ) or die;

use Joomla\Registry\Registry;

abstract class DradAdminModelAdmin extends JModelAdmin
{
  /*
   * The name of field to display to select associations
   * 
   * @var   string 
   */
  protected $association_field_name = null;
  
  /**
	 * The context to use for the associations table
	 *
	 * @var     string
	 */
	protected $associationsContext = '';
  
  /*
   * The name of the table
   * 
   * @var   string 
   */
  protected $table_name = null;
  
  /*
	 * @var boolean	Determines whether we have to manage languages for this element or not
	 */
	private $manageLanguage = false;
  
  
  /**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 */
	public function __construct($config = array())
	{
    // Guess the JText message prefix. Defaults to the option_name
		if (!isset($config['text_prefix']))
		{
			$config['text_prefix'] = strtoupper($this->option . '_'. $this->name);
		}
    
    if($this->drad_element)
    {
      $drad_config = DradUtilitiesConfig::load($this->drad_element);
    }
    else
    {
      throw new Exception(JText::_('LIB_DRAD_ERROR_MESSAGE_NO_ELEMENT'), 1003);
    }
    
    if($drad_config->language == "true" && JLanguageAssociations::isEnabled())
    {
      $this->manageLanguage = true;
      
      if($drad_config->associationfield)
      {
        $this->association_field_name = (String)$drad_config->associationfield;
      }
      else
      {
        throw new Exception(JText::_('LIB_DRAD_ERROR_MESSAGE_NO_ASSOC_FIELD'), 1004);
      }
      
      if($drad_config->associationcontext)
      {
        $this->associationsContext = (String)$drad_config->associationcontext;
      }
      else
      {
        $this->associationsContext = $this->option.'.'.$this->name;
      }
    }
    
    if($drad_config->table)
      {
        
        $this->table_name = (String)$drad_config->table;
      }
      else
      {
        throw new Exception(JText::_('LIB_DRAD_ERROR_MESSAGE_NO_TABLE_NAME'), 1005);
      }
      
    
    parent::__construct($config);
  }
  
  /**
	 * Method to test whether a record can be deleted.
	 *
	 * @param   object  $record  A record object.
	 *
	 * @return  boolean  True if allowed to delete the record. Defaults to the permission set in the component.
	 */
	protected function canDelete($record)
	{
		if (!empty($record->id))
		{
			if ($record->state != -2)
			{
				return false;
			}
			$user = JFactory::getUser();
      
			return $user->authorise('core.delete', $this->option . '.' . $this->name . '.' . (int) $record->id);
		}

		return false;
	}
  
  
  /**
	 * Method to test whether a record can have its state edited.
	 *
	 * @param   object  $record  A record object.
	 *
	 * @return  boolean  True if allowed to change the state of the record. Defaults to the permission set in the component.
	 */
	protected function canEditState($record)
	{
		$user = JFactory::getUser();

		// Check for existing item.
		if (!empty($record->id))
		{
			return $user->authorise('core.edit.state', $this->option . '.' . $this->name . '.' . (int) $record->id);
		}
		// Default to component settings if neither article nor category known.
		else
		{
			return parent::canEditState($this->option);
		}
	}
  
  /**
	 * Auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @return  void
	 */
	protected function populateState()
	{
		$app = JFactory::getApplication('administrator');

		// Load the User state.
		$pk = $app->input->getInt('id');
		$this->setState($this->name . '.id', $pk);

		// Load the parameters.
		$params = JComponentHelper::getParams($this->option);
		$this->setState('params', $params);
    
    $this->setState('drad.option', $this->option);
	}
	
	/**
	 * Method to get a character.
	 *
	 * @param   integer  $pk  An optional id of the object to get, otherwise the id from the model state is used.
	 *
	 * @return  mixed    Character data object on success, false on failure.
	 */
	public function getItem($pk = null)
	{    
		if ($item = parent::getItem($pk))
		{      
			// Convert the metadata field to an array.
			$registry = new Registry;
			$registry->loadString($item->metadata);
			$item->metadata = $registry->toArray();

			// Convert the created and modified dates to local user time for display in the form.
			$tz = new DateTimeZone(JFactory::getApplication()->get('offset'));

			if ((int) $item->created_time)
			{
				$date = new JDate($item->created_time);
				$date->setTimezone($tz);
				$item->created_time = $date->toSql(true);
			}
			else
			{
				$item->created_time = null;
			}

			if ((int) $item->modified_time)
			{
				$date = new JDate($item->modified_time);
				$date->setTimezone($tz);
				$item->modified_time = $date->toSql(true);
			}
			else
			{
				$item->modified_time = null;
			}
		}
    
    // Load associated items
		$assoc = JLanguageAssociations::isEnabled();

		if ($assoc && $this->manageLanguage)
		{
			$item->associations = array();

			if ($item->id != null)
			{
				$associations = JLanguageAssociations::getAssociations($this->option, $this->table_name, $this->associationsContext, $item->id, 'id', 'alias', null);
        
				foreach ($associations as $tag => $association)
				{
					$slug = explode(':', $association->id);
          $item->associations[$tag] = $slug[0];
				}
			}
    }
		
		return $item;
	}
	
	/**
	 * Method to get the row form.
	 *
	 * @param   array    $data      Data for the form.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  mixed    A JForm object on success, false on failure
	 */
	public function getForm($data = array(), $loadData = true)
	{
		$jinput = JFactory::getApplication()->input;

		// Get the form.
		$form = $this->loadForm($this->option . '.' . $this->name, $this->name, array('control' => 'jform', 'load_data' => $loadData));

		if (empty($form))
		{
			return false;
		}

		$user = JFactory::getUser();

		if (!$user->authorise('core.edit.state', $this->option))
		{
			// Disable fields for display.
			$form->setFieldAttribute('ordering', 'disabled', 'true');
			$form->setFieldAttribute('published', 'disabled', 'true');

			// Disable fields while saving.
			// The controller has already verified this is a record you can edit.
			$form->setFieldAttribute('ordering', 'filter', 'unset');
			$form->setFieldAttribute('published', 'filter', 'unset');
		}

		return $form;
	}
	
	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return  mixed  The data for the form.
	 */
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$data = JFactory::getApplication()->getUserState($this->option . '.edit.' . $this->name . '.data', array());

		if (empty($data))
		{
			$data = $this->getItem();
		}
    
    // If there are params fieldsets in the form it will fail with a registry object
    if (isset($data->params) && $data->params instanceof Registry)
    {
      $data->params = $data->params->toArray();
    }

		$this->preprocessData($this->option . '.' . $this->name, $data);

		return $data;
	}
  
  /**
	 * Method to save the form data.
	 *
	 * @param   array  $data  The form data.
	 *
	 * @return  boolean       True on success.
	 */
	public function save($data)
	{
    $filter  = JFilterInput::getInstance();
    
		if (isset($data['metadata']) && isset($data['metadata']['author']))
		{
			$data['metadata']['author'] = $filter->clean($data['metadata']['author'], 'TRIM');
		}
    
    $input = JFactory::getApplication()->input;

		// Alter the title for save as copy
		if ($input->get('task') == 'save2copy')
		{
			$origTable = clone $this->getTable();
			$origTable->load($input->getInt('id'));

			if ($data['title'] == $origTable->title)
			{
				list($title, $alias) = $this->generateNewTitle($data['alias'], $data['title']);
				$data['title'] = $title;
				$data['alias'] = $alias;
			}
			else
			{
				if ($data['alias'] == $origTable->alias)
				{
					$data['alias'] = '';
				}
			}
			$data['published'] = 0;
		}
    
    if (parent::save($data))
		{
      if(!$this->postSaveHook($data))
      {
        return false;
      }
      
      return true;
    }
    
    return false;
  }
  
  /**
   * Hook for extensions that need to perform operation after the record has 
   * been saved 
   * 
   * @param   array   $data  The form data
   * 
   * @return  boolean        True on success
   */
  protected function postSaveHook($data)
  {
    return true;
  }       
  
  
  /**
	 * Auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @param   JForm   $form   The form object
	 * @param   array   $data   The data to be merged into the form object
	 * @param   string  $group  The plugin group to be executed
	 *
	 * @return  void
	 */
	protected function preprocessForm(JForm $form, $data, $group = 'content')
	{
		// Association items
		$assoc = JLanguageAssociations::isEnabled();

		if ($assoc && $this->manageLanguage)
		{
			$languages = JLanguageHelper::getLanguages('lang_code');
			$addform = new SimpleXMLElement('<form />');
			$fields = $addform->addChild('fields');
			$fields->addAttribute('name', 'associations');
			$fieldset = $fields->addChild('fieldset');
			$fieldset->addAttribute('name', 'item_associations');
			//$fieldset->addAttribute('description', $this->text_prefix . '_ASSOCIATIONS_FIELDSET_DESC');
			$add = false;

			foreach ($languages as $tag => $language)
			{
				if (empty($data->language) || $tag != $data->language)
				{
					$add = true;
					$field = $fieldset->addChild('field');
					$field->addAttribute('name', $tag);
					$field->addAttribute('type', $this->association_field_name);
					$field->addAttribute('language', $tag);
					$field->addAttribute('label', $language->title);
					$field->addAttribute('translate_label', 'false');
					$field->addAttribute('edit', 'false');
					$field->addAttribute('clear', 'true');
				}
			}

			if ($add)
			{
				$form->load($addform, false);
			}
		}

		parent::preprocessForm($form, $data, $group);
	}
    
  /**
	 * Method to change the title & alias.
	 *
	 * @param   integer  $parent_id  The id of the parent.
	 * @param   string   $alias      The alias.
	 * @param   string   $title      The title.
	 *
	 * @return  array     Contains the modified title and alias.
	 */
	protected function generateNewTitle($alias, $title, $catid = null)
	{
    if(!$catid)
    {
      // Alter the title & alias
      $table = $this->getTable();

      while ($table->load(array('alias' => $alias)))
      {
        $title = JString::increment($title);
        $alias = JString::increment($alias, 'dash');
      }

      return array($title, $alias);
    }
    else
    {
      return parent::generateNewTitle($catid, $alias, $title);
    }
	}
}

