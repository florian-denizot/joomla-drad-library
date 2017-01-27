<?php

defined('JPATH_PLATFORM') or die;

/**
 * Utitlity class for associations in multilang
 */
class DradLanguageAssociations extends JLanguageAssociations
{
	/**
	 * Get an array containing only the ids of the associated items.
	 *
	 * @param   string   $extension   The name of the component.
	 * @param   string   $tablename   The name of the table.
	 * @param   string   $context     The context
	 * @param   integer  $id          The primary key value.
	 * @param   string   $pk          The name of the primary key in the given $table.
	 * @param   string   $aliasField  If the table has an alias field set it here. Null to not use it
	 * @param   string   $catField    If the table has a catid field set it here. Null to not use it
	 *
	 * @return  array                The associated items
	 *
	 * @throws  Exception
	 */
	public static function getAssociationIds($extension, $tablename, $context, $id, $pk = 'id', $aliasField = 'alias', $catField = 'catid')
	{
		$associations = parent::getAssociations($extension, $tablename, $context, $id, $pk, $aliasField, $catField);
	
    $associationIds[] = $id;
    foreach ($associations as $tag => $association)
    {
      $slug = explode(':', $association->{$pk});
      $associationIds[] = $slug[0];
    }
    
    return $associationIds;
  }
}
