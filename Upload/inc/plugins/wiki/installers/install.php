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
				watching TEXT(65535),
				protected INT DEFAULT '0',
				lastauthor TEXT(255),
				lastauthorid INT(8),
				notepad TEXT(255),
				category INT(10),
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
				title TEXT(255),
				description TEXT(255)
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
				value TEXT(255)
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

	private function insertCSS()
	{
		global $db;
		require_once(MYBB_ADMIN_DIR . "inc/functions_themes.php");

		// Add stylesheet to the master template so it becomes inherited.
		$stylesheet = @file_get_contents(MYBB_ROOT.'inc/plugins/wiki/templates/stylesheets/wiki.css');
		$wiki_stylesheet = array(
			'name' => 'wiki.css',
			'tid' => '1',
			'stylesheet' => $db->escape_string($stylesheet),
			'cachefile' => 'wiki.css',
			'lastmodified' => TIME_NOW,
			'attachedto' => 'wiki.php'
			);
		$db->insert_query('themestylesheets', $wiki_stylesheet);
		cache_stylesheet(1, "wiki.css", $stylesheet);
		update_theme_stylesheet_list("1");
	}

	private function insertSettings()
	{
		global $db;

		$insert_array = array(
			'name'        => 'wiki_enable',
			'title'            => 'Power Switch',
			'optionscode'    => 'onoff',
			'value'        => '1',
			);
		$db->insert_query('wiki_settings', $insert_array);

		$insert_array = array(
			'name'        => 'wiki_parse_smileys',
			'title'            => 'Parse Smilies?',
			'optionscode'    => 'yesno',
			'value'        => 0,
			);
		$db->insert_query('wiki_settings', $insert_array);

		$insert_array = array(
			'name'        => 'wiki_mybbparser',
			'title'            => 'Use the MyBB Parser?',
			'optionscode'    => 'yesno',
			'value'        => 1,
			);
		$db->insert_query('wiki_settings', $insert_array);

		$insert_array = array(
			'name'        => 'wiki_markdown',
			'title'            => 'Use Markdown Parser?',
			'optionscode'    => 'yesno',
			'value'        => 1,
			);
		$db->insert_query('wiki_settings', $insert_array);

		$insert_array = array(
			'name'        => 'wiki_mycode_editor',
			'title'            => 'Clickable MyCode editor',
			'optionscode'    => 'yesno',
			'value'        => '1',
			);
		$db->insert_query('wiki_settings', $insert_array);

		$insert_array = array(
			'name'        => 'wiki_parse_html',
			'title'            => 'Parse HTML?',
			'optionscode'    => 'yesno',
			'value'        => 0,
			);
		$db->insert_query('wiki_settings', $insert_array);

		$insert_array = array(
			'name'        => 'wiki_export_allowed',
			'title'            => 'Exporting Enabled?',
			'optionscode'    => 'yesno',
			'value'        => 1,
			);
		$db->insert_query('wiki_settings', $insert_array);
	}

	private function handleMyAlerts()
	{
		global $db, $cache;
		if (class_exists('MybbStuff_MyAlerts_AlertTypeManager'))
		{
			$alertTypeManager = MybbStuff_MyAlerts_AlertTypeManager::getInstance();

			if (!$alertTypeManager)
			{
				$alertTypeManager = MybbStuff_MyAlerts_AlertTypeManager::createInstance($db, $cache);
			}

			$alertType = new MybbStuff_MyAlerts_Entity_AlertType();
			$alertType->setCode('mybb_wiki_alert_code'); // The codename for your alert type. Can be any unique string.
			$alertType->setEnabled(true);
			$alertType->setCanBeUserDisabled(true);

			$alertTypeManager->add($alertType);
		}
	}

	public function go()
	{
		global $db, $cache;

		if(function_exists('wiki_is_installed') && wiki_is_installed()) {
			return false;
		}

		$this->buildTables();
		$this->insertSettings();
		rebuild_settings();

		$this->insertCSS();

		$this->handleMyAlerts();

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

		$db->write_query("INSERT INTO " . TABLE_PREFIX . "wiki_categories(title, description) VALUES('Meta', 'The default Category')");

		return true;
	}
}

?>