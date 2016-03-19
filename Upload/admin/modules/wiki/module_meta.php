<?php

// Disallow direct access to this file for security reasons
if(!defined("IN_MYBB"))
{
	die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

function wiki_meta()
{
	global $page, $lang, $plugins;

	$lang->load('wiki');

	$sub_menu = array();

	$sub_menu['5'] = array("id" => "home", "title" => $lang->wiki, "link" => "index.php?module=wiki-home");
	$sub_menu['10'] = array("id" => "articles", "title" => $lang->nav_articles, "link" => "index.php?module=wiki-articles");
	$sub_menu['15'] = array("id" => "categories", "title" => $lang->nav_cat, "link" => "index.php?module=wiki-categories");
	$sub_menu['20'] = array("id" => "import", "title" => $lang->nav_import, "link" => "index.php?module=wiki-import");
	$sub_menu['25'] = array("id" => "perms", "title" => $lang->nav_perms, "link" => "index.php?module=wiki-perms");
	$sub_menu['30'] = array("id" => "settings", "title" => $lang->nav_settings, "link" => "index.php?module=wiki-settings");
	$sub_menu['35'] = array("id" => "plugin", "title" => $lang->nav_plugin_pane, "link" => "index.php?module=config-plugins#mybbwiki");
	$sub_menu['40'] = array("id" => "templates", "title" => $lang->nav_templates, "link" => "index.php?module=wiki-templates");
	$sub_menu['45'] = array("id" => "upgrades", "title" => $lang->nav_upgrades, "link" => "index.php?module=wiki-upgrades");
	$sub_menu['150'] = array("id" => "docs", "title" => $lang->nav_docs, "link" => "index.php?module=wiki-docs");

	$sub_menu = $plugins->run_hooks("admin_wiki_menu", $sub_menu);

	$page->add_menu_item($lang->wiki, "wiki", "index.php?module=wiki", 60, $sub_menu);

	return true;
}

function wiki_action_handler($action)
{
	global $page, $lang, $plugins;

	$page->active_module = "wiki";

	$actions = array(
		'home' => array('active' => 'home', 'file' => 'home.php'),
		'articles' => array('active' => 'articles', 'file' => 'articles.php'),
		'categories' => array('active' => 'categories', 'file' => 'categories.php'),
		'import' => array('active' => 'import', 'file' => 'import.php'),
		'perms' => array('active' => 'perms', 'file' => 'perms.php'),
		'settings' => array('active' => 'settings', 'file' => 'settings.php'),
		'docs' => array('active' => 'docs', 'file' => 'docs.php'),
		'templates' => array('active' => 'templates', 'file' => 'templates.php'),
		'upgrades' => array('active' => 'upgrades', 'file' => 'upgrades.php')
	);

	$actions = $plugins->run_hooks("admin_wiki_action_handler", $actions);

	if(!isset($actions[$action]))
	{
		$page->active_action = "home";
		return "home.php";
	}
	else
	{
		$page->active_action = $actions[$action]['active'];
		return $actions[$action]['file'];
	}
}

function wiki_admin_permissions()
{
	global $lang, $plugins;

	$admin_permissions = array(
		"articles"		=> $lang->can_manage_articles,
		"categories"	=> $lang->wiki_can_manage_categories,
		"import"		=> $lang->wiki_can_manage_imports,
		"permissions"	=> $lang->wiki_can_manage_perms,
		"settings"		=> $lang->wiki_can_manage_settings,
		"docs"			=> $lang->wiki_can_manage_docs,
		"upgrades"		=> $lang->wiki_can_upgrade
	);

	$admin_permissions = $plugins->run_hooks("admin_wiki_permissions", $admin_permissions);

	return array("name" => $lang->wiki, "permissions" => $admin_permissions, "disporder" => 60);
}
?>