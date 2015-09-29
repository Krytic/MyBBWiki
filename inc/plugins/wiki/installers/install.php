<?php

class WikiInstaller
{
	private function buildTables()
	{
		global $db;

		$collation = $db->build_create_table_collation();

		if (!$db->table_exists('wiki'))
		{
			$db->write_query(sprintf("CREATE TABLE %swiki(
				id INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
				authors TEXT(255) NOT NULL,
				title TEXT(255),
				content TEXT,
				protected INT DEFAULT '0',
				lastauthor TEXT(255) DEFAULT '',
				lastauthorid INT(8),
				notepad TEXT(255) DEFAULT '',
				category TEXT(255),
				original TEXT
				) ENGINE=MyISAM{$collation};", TABLE_PREFIX));
		}
		if (!$db->table_exists('wiki_edits'))
		{
			$db->write_query(sprintf("CREATE TABLE %swiki_edits(
				eid INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
				aid INT(8) NOT NULL,
				author INT(8) NOT NULL,
				revision TEXT
				) ENGINE=MyISAM{$collation};", TABLE_PREFIX));
		}
		if (!$db->table_exists('wiki_categories'))
		{
			$db->write_query(sprintf("CREATE TABLE %swiki_categories(
				cid INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
				title TEXT(255)
				) ENGINE=MyISAM{$collation};", TABLE_PREFIX));
		}
		if (!$db->table_exists('wiki_perms'))
		{
			$db->write_query(sprintf("CREATE TABLE %swiki_perms(
				gid INT(10) NOT NULL PRIMARY KEY,
				can_view BOOLEAN NOT NULL DEFAULT '1',
				can_create BOOLEAN NOT NULL DEFAULT '1',
				can_edit BOOLEAN NOT NULL DEFAULT '1',
				can_protect BOOLEAN NOT NULL DEFAULT '0',
				can_export BOOLEAN NOT NULL DEFAULT '0'
				) ENGINE=MyISAM{$collation};", TABLE_PREFIX));
		}
		if (!$db->table_exists('wiki_settings'))
		{
			$db->write_query(sprintf("CREATE TABLE %swiki_settings(
				sid INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
				name TEXT(255) NOT NULL,
				title TEXT(255) NOT NULL,
				optionscode TEXT(255) NOT NULL,
				value TEXT(255) DEFAULT ''
				) ENGINE=MyISAM{$collation};", TABLE_PREFIX));
		}
		if (!$db->table_exists('wiki_templates'))
		{
			$db->write_query(sprintf("CREATE TABLE %swiki_templates(
				tid INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
				name TEXT(255) NOT NULL,
				search TEXT(255) NOT NULL,
				`replace` TEXT NOT NULL
				) ENGINE=MyISAM{$collation};", TABLE_PREFIX));
		}

		unset($collation);
	}

	private function insertSettings()
	{
		global $db;

		$insert_array = array(
			'sid'            => 'NULL',
			'name'        => 'wiki_enable',
			'title'            => 'Power Switch',
			'optionscode'    => 'onoff',
			'value'        => '1',
			);
		$db->insert_query('wiki_settings', $insert_array);

		$insert_array = array(
			'sid'            => 'NULL',
			'name'        => 'wiki_parse_smileys',
			'title'            => 'Parse Smilies?',
			'optionscode'    => 'yesno',
			'value'        => 0,
			);
		$db->insert_query('wiki_settings', $insert_array);

		$insert_array = array(
			'sid'            => 'NULL',
			'name'        => 'wiki_mybbparser',
			'title'            => 'Use the MyBB Parser?',
			'optionscode'    => 'yesno',
			'value'        => 1,
			);
		$db->insert_query('wiki_settings', $insert_array);

		$insert_array = array(
			'sid'            => 'NULL',
			'name'        => 'wiki_markdown',
			'title'            => 'Use Markdown Parser?',
			'optionscode'    => 'yesno',
			'value'        => 1,
			);
		$db->insert_query('wiki_settings', $insert_array);

		$insert_array = array(
			'sid'            => 'NULL',
			'name'        => 'wiki_mycode_editor',
			'title'            => 'Clickable MyCode editor',
			'optionscode'    => 'yesno',
			'value'        => '1',
			);
		$db->insert_query('wiki_settings', $insert_array);

		$insert_array = array(
			'sid'            => 'NULL',
			'name'        => 'wiki_parse_html',
			'title'            => 'Parse HTML?',
			'optionscode'    => 'yesno',
			'value'        => 0,
			);
		$db->insert_query('wiki_settings', $insert_array);
	}

	public function go()
	{
		global $db, $cache;

		$this->buildTables();
		$this->insertSettings();
		rebuild_settings();

		$query = $db->write_query("SELECT * FROM `" . TABLE_PREFIX . "usergroups`");
		$cache_arr = array();

		while($group = $db->fetch_array($query))
		{
		// Set the default permissions
			$db->write_query("INSERT INTO " . TABLE_PREFIX . "wiki_perms(`gid`,`can_view`,`can_create`,`can_edit`,`can_protect`,`can_export`) VALUES('" . $group['gid'] . "','1','1','1','0','0')");

		// and cache them
			$cache_arr['gid_' . $group['gid']] = array(
				'can_view'		=>	1,
				'can_create'	=>	1,
				'can_edit'		=>	1,
				'can_protect'	=>	0,
				'can_export'	=>	0
				);
		}

		$cache->update('wiki_permissions', $cache_arr);

		$db->write_query("INSERT INTO " . TABLE_PREFIX . "wiki_categories(title) VALUES('Meta')");
	}
}

?>