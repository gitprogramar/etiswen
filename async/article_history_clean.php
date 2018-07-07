<html><head><meta name="robots" content="noindex, nofollow"></head><body></body></html>
<?php
/**
 * @author    Federico Liva <mail@federicoliva.info>
 * @copyright Copyright (C)2015 Federico Liva. All rights reserved.
 * @license   GNU General Public License, version 2 or later
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('max_execution_time', 0);

define('DB_HOST', 'mysql.hostinger.com.ar');      // Set the database host
define('DB_USER', 'u383829915_prog');      // Set the database user
define('DB_PASS', '');      // Set the database password
define('DB_NAME', 'u383829915_prog');      // Set the database name
define('DB_PREFIX', 'prog_');    // Set the tables prefix

$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($db->connect_errno)
{
	die('Failed to connect to MySQL: (' . $db->connect_errno . ') ' . $db->connect_error);
}

//
// STEP 1
//
// As first thing, drop the rows af ucm tables.

foreach (['ucm_base', 'ucm_content', 'ucm_history'] as $table)
{
	if (!$db->query('TRUNCATE TABLE ' . DB_PREFIX . $table))
	{
		die('Cannot truncate ' . DB_PREFIX . $table . ' table: (' . $db->errno . ') ' . $db->error);
	}
}

//
// STEP 2
//
// Get the list of content types.
//

if ($result = $db->query('SELECT * FROM ' . DB_PREFIX . 'content_types'))
{
	while ($type = $result->fetch_assoc())
	{
		$content_types[$type['type_alias']] = $type['type_id'];
	}
}
else
{
	die('Cannot retrieve content types: (' . $db->errno . ') ' . $db->error);
}

//
// STEP 3
//
// Get the list of languages.
//

if ($result = $db->query('SELECT * FROM ' . DB_PREFIX . 'languages'))
{
	$languages['*'] = $languages[''] = 0; // ucm_base wants zero for star or empty language code

	while ($type = $result->fetch_assoc())
	{
		$languages[$type['lang_code']] = $type['lang_id'];
	}
}
else
{
	die('Cannot retrieve languages: (' . $db->errno . ') ' . $db->error);
}

//
// STEP 4
//
// Build the list of all articles.

if ($result = $db->query('SELECT * FROM ' . DB_PREFIX . 'content'))
{
	$i = 1;

	while ($article = $result->fetch_assoc())
	{
		// Check for content types
		if (empty($content_types['com_content.article']))
		{
			die('Content type id not found for com_content.article');
		}

		// Check for languages
		if (empty($languages))
		{
			die('Languages are not loaded');
		}

		$ucm_content_row = [
			'core_content_id'          => $i++,
			'core_type_alias'          => 'com_content.article',
			'core_title'               => $article['title'],
			'core_alias'               => $article['alias'],
			'core_body'                => $article['introtext'],
			'core_state'               => $article['state'],
			'core_checked_out_time'    => $article['checked_out_time'],
			'core_checked_out_user_id' => $article['checked_out'],
			'core_access'              => $article['access'],
			'core_params'              => $article['attribs'],
			'core_featured'            => $article['featured'],
			'core_metadata'            => $article['metadata'],
			'core_created_user_id'     => $article['created_by'],
			'core_created_by_alias'    => $article['created_by_alias'],
			'core_created_time'        => $article['created'],
			'core_modified_user_id'    => $article['modified_by'],
			'core_modified_time'       => $article['modified'],
			'core_language'            => $languages[$article['language']],
			'core_publish_up'          => $article['publish_up'],
			'core_publish_down'        => $article['publish_down'],
			'core_content_item_id'     => $article['id'],
			'asset_id'                 => $article['asset_id'],
			'core_images'              => $article['images'],
			'core_urls'                => $article['urls'],
			'core_hits'                => $article['hits'],
			'core_version'             => $article['version'],
			'core_ordering'            => $article['ordering'],
			'core_metakey'             => $article['metakey'],
			'core_catid'               => $article['catid'],
			'core_xreference'          => $article['xreference'],
			'core_type_id'             => $content_types['com_content.article']
		];

		// Insert into ucm_content
		if (!$db->query(insert_query($ucm_content_row, 'ucm_content')))
		{
			die('Cannot insert into ucm_content table: (' . $db->errno . ') ' . $db->error);
		}

		$ucm_base_row = [
			'ucm_id'          => $ucm_content_row['core_content_id'],
			'ucm_item_id'     => $ucm_content_row['core_content_item_id'],
			'ucm_type_id'     => $ucm_content_row['core_type_id'],
			'ucm_language_id' => $ucm_content_row['core_language']
		];

		// Insert into ucm_base
		if (!$db->query(insert_query($ucm_base_row, 'ucm_base')))
		{
			die('Cannot insert into ucm_base table: (' . $db->errno . ') ' . $db->error);
		}

		$contentitem_tag_map_updates = ['core_content_id' => $ucm_content_row['core_content_id']];

		// Insert into ucm_base
		if (!$db->query(update_query($contentitem_tag_map_updates, 'contentitem_tag_map', 'content_item_id = ' . $ucm_content_row['core_content_item_id'])))
		{
			die('Cannot update contentitem_tag_map table: (' . $db->errno . ') ' . $db->error);
		}

		echo 'UCM rebuilt for article ' . $ucm_content_row['core_content_item_id'] . '<br/>';

		flush();
	}

	$result->close();
}
else
{
	die('Cannot retrieve articles data: (' . $db->errno . ') ' . $db->error);
}

echo '<br/>UCM tables successfully rebuilt!';

/**
 * Build an INSERT query for an associative array.
 *
 * @param array  $array      The array to be processed.
 * @param string $table_name The name of the table where will be do the INSERT.
 *
 * @return string
 */
function insert_query($array, $table_name)
{
	$columns = $values = [];

	foreach ($array as $column => $value)
	{
		$columns[] = '`' . addslashes($column) . '`';
		$values[]  = '\'' . addslashes($value) . '\'';
	}

	$columns_list = implode(', ', $columns);
	$values_list  = implode(', ', $values);

	return 'INSERT INTO ' . DB_PREFIX . $table_name . ' (' . $columns_list . ') VALUES (' . $values_list . ')';
}

/**
 * Build an UPDATE query for an associative array.
 *
 * @param array  $array      The array to be processed.
 * @param string $table_name The name of the table where will be do the UPDATE.
 *
 * @return string
 */
function update_query($array, $table_name, $where)
{
	$updates = [];

	foreach ($array as $column => $value)
	{
		$updates[] = '`' . addslashes($column) . '` = \'' . addslashes($value) . '\'';
	}

	$updates_list = implode(', ', $updates);

	return 'UPDATE ' . DB_PREFIX . $table_name . ' SET ' . $updates_list . ' WHERE ' . $where;
}