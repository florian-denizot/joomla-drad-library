<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  lib_drad
 *
 * @copyright   Copyright (C) 2015 Florian Denizot. All rights reserved.
 * @license     LTBD
 */

defined( '_JEXEC' ) or die;

abstract class DradAdminTableTable extends JTable
{  
  /**
	 * Ensure params and metadata are encoded in json in the bind method
	 *
	 * @var    array
	 */
	protected $_jsonEncode = array('params', 'metadata');
  
  /**
	 * Ensure params and metadata are encoded in json in the bind method
	 *
	 * @var    String
	 */
	protected $text_prefix = '';
  
  /**
	 * The name of the section linked to this table in the DRAD config file
	 *
	 * @var    String
	 */
	protected $drad_element = '';
  
  /**
	 * Constructor
	 *
	 * @param   JDatabaseDriver  $db  A database connector object
	 */
	public function __construct($db)
	{    
    if($this->drad_element)
    {
      $drad_config = DradUtilitiesConfig::load($this->drad_element);
    }
    else
    {
      return false;
    }
    
    if($drad_config->table)
    {
      $tableName = (string)$drad_config->table;
    }
    
		parent::__construct($tableName, 'id', $db);
	}
  
  /**
	 * Overloaded check function
	 *
	 * @return  boolean  True on success, false on failure
	 */
	public function check()
	{
		// check for valid title
		if (trim($this->title) == '')
		{
			$this->setError(JText::_($this->text_prefix . '_WARNING_PROVIDE_VALID_NAME'));

			return false;
		}

		// Generate a valid alias
		$this->generateAlias();
    
		/*
		 * Clean up keywords -- eliminate extra spaces between phrases
		 * and cr (\r) and lf (\n) characters from string.
		 * Only process if not empty.
 		 */
		if (!empty($this->metakey))
		{
			// Array of characters to remove.
			$bad_characters = array("\n", "\r", "\"", "<", ">");
      
			// Remove bad characters.
			$after_clean = JString::str_ireplace($bad_characters, "", $this->metakey);

			// Create array using commas as delimiter.
			$keys = explode(',', $after_clean);
			$clean_keys = array();

			foreach ($keys as $key)
			{
				// Ignore blank keywords.
				if (trim($key))
				{
					$clean_keys[] = trim($key);
				}
			}

			// Put array back together delimited by ", "
			$this->metakey = implode(", ", $clean_keys);
		}

		// Clean up description -- eliminate quotes and <> brackets
		if (!empty($this->metadesc))
		{
			// Only process if not empty
			$bad_characters = array("\"", "<", ">");
			$this->metadesc = JString::str_ireplace($bad_characters, "", $this->metadesc);
		}
    
		return true;
	}

  /**
	 * Stores a contact.
	 *
	 * @param   boolean  $updateNulls  True to update fields even if they are null.
	 *
	 * @return  boolean  True on success, false on failure.
	 */
	public function store($updateNulls = false)
	{
    $this->onBeforeStore();

		$date	= JFactory::getDate();
		$user	= JFactory::getUser();

		$this->modified_time = $date->toSql();

		if ($this->id)
		{
			// Existing item
			$this->modified_by	= $user->get('id');
		}
		else
		{
			// New item. A contact created and created_by field can be set by the user,
			// so we don't touch either of these if they are set.
			if (!(int) $this->created_time)
			{
				$this->created_time = $date->toSql();
			}

			if (empty($this->created_by))
			{
				$this->created_by = $user->get('id');
			}
		}

		// Set xreference to empty string if not set
		if (!$this->xreference)
		{
			$this->xreference = '';
		}
    
    // Verify that the alias is unique
		$table = clone($this);
    
    if ($table->load(array('alias' => $this->alias)) && ($table->id != $this->id || $this->id == 0))
		{
			$this->setError(JText::_($this->text_prefix . '_ERROR_UNIQUE_ALIAS'));

			return false;
		}

		return parent::store($updateNulls);
	}
  
  protected function onBeforeStore()
  {
    return true;
  }
  
  /**
	 * Generate a valid alias from title / date.
	 * Remains public to be able to check for duplicated alias before saving
	 *
	 * @return  string
	 */
	public function generateAlias()
	{
		if (empty($this->alias))
		{
			$this->alias = $this->title;
		}

		$this->alias = JApplication::stringURLSafe($this->alias);

		if (trim(str_replace('-', '', $this->alias)) == '')
		{
			$this->alias = JFactory::getDate()->format("Y-m-d-H-i-s");
		}

		return $this->alias;
	}
}

