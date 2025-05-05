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

	$submenu_options = array('home', 'articles', 'categories', 'import', 'perms', 'settings', 'plugin', 'templates', 'upgrades');

	for ($i=0; $i < count($submenu_options) ; $i++) {
		$selected_option = $submenu_options[$i];

	 if(check_admin_permissions(array("module" => 'wiki',
                             				"action" => $selected_option),
															$error=false)) {

	 	$lang_string = 'wiki_nav_' . $selected_option;

	 	$sub_menu[(string) 5*($i+1)] = array("id" => $selected_option, "title" => $lang->$lang_string, "link" => "index.php?module=wiki-" . $selected_option);
	 }
	}

	$sub_menu['150'] = array("id" => "docs", "title" => $lang->wiki_nav_docs, "link" => "http://mybbwiki.readthedocs.io/en/latest/");

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
		"articles"		=> $lang->wiki_can_manage_articles,
		"categories"	=> $lang->wiki_can_manage_categories,
		"import"		=> $lang->wiki_can_manage_imports,
		"perms"	=> $lang->wiki_can_manage_perms,
		"settings"		=> $lang->wiki_can_manage_settings,
		"docs"			=> $lang->wiki_can_manage_docs,
		"upgrades"		=> $lang->wiki_can_upgrade,
		"templates" => $lang->wiki_can_manage_templates
	);

	$admin_permissions = $plugins->run_hooks("admin_wiki_permissions", $admin_permissions);

	return array("name" => $lang->wiki, "permissions" => $admin_permissions, "disporder" => 60);
}
?>