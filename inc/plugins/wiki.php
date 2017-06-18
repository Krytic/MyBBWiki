<?php

/**
 * MyBB Wiki
 *
 * Adds a functioning Wiki to your MyBB Forum
 *
 * @package mybbwiki.zip
 * @author  Krytic
 * @license creativecommons.org/licenses/by-nc-sa/3.0/ Creative Commons BY-NC-SA 3.0 Unported
 * @version 1.0
 */

if (!defined('IN_MYBB'))
{
	die('Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.');
}

define("WIKI_VERSION", "1.2.0"); // cheeky placement means that we're able to access this constant from anywhere.

$plugins->add_hook('global_start', 'wiki_global_start');
$plugins->add_hook('fetch_wol_activity_end', 'wiki_fetch_wol');
$plugins->add_hook('build_friendly_wol_location_end', 'wiki_build_friendly');
$plugins->add_hook('parse_message', 'wiki_parse_mycode');
$plugins->add_hook('admin_user_groups_add_commit_end', 'wiki_admin_user_groups_add_commit_end');
$plugins->add_hook('admin_user_groups_delete_commit_end', 'wiki_admin_user_groups_delete_commit_end');

/**
 * Setting up "handlers" - abstractions of important functionality. Like our custom permission system
 */

require_once 'wiki/handlers/PermissionHandler.php';

$permission = PermissionHandler::singleton();

/**
 * Generic plugin functions.
 */

function wiki_info()
{
	$append = "";

	if(wiki_is_installed())
	{
		$append .= "<br />";
		$append .= " <a href=\"http://mybbwiki.readthedocs.io/en/latest/\"><strong>[Documentation]</strong></a>";
	}

	return array(
		'name'          =>  'MyBB Wiki',
		'description'   =>  "Adds a simple Wiki to your MyBB Forum.{$append}",
		'website'       =>  'https://github.com/Krytic/MyBB-Wiki" id="mybbwiki',
		'author'        =>  'Adamas',
		'authorsite'    =>  'https://github.com/Krytic/MyBB-Wiki',
		'version'       =>  WIKI_VERSION,
		'guid'          =>  '', // no longer available on MyBB Mods site
		'compatibility' =>  '18*',
		);
}

function wiki_install()
{
	global $db, $cache;

	// Lessen the file size.
	require_once 'wiki/installers/install.php';
	$installer = new WikiInstaller;

	$installer->go();

}

function wiki_is_installed()
{
	global $db;

	return $db->table_exists('wiki');
}

function wiki_uninstall()
{
	global $db, $cache;

	// Template Deletion
	$db->delete_query("templategroups", "title = 'Wiki'");

	// Table deletion
	$db->drop_table('wiki');
	$db->drop_table('wiki_edits');
	$db->drop_table('wiki_categories');
	$db->drop_table('wiki_perms');
	$db->drop_table('wiki_settings');
	$db->drop_table('wiki_templates');

	// Clear caches
	$db->delete_query("datacache", 'title="wiki_articles"');
	$db->delete_query("datacache", 'title="wiki_permissions"');

	require_once(MYBB_ADMIN_DIR."inc/functions_themes.php");

	// Stylesheet Deletion
	$query = $db->simple_select("themes", "tid");
	while($tid = $db->fetch_field($query, "tid"))
	{
		$css_file = MYBB_ROOT."cache/themes/theme{$tid}/wiki.css";
		if(file_exists($css_file))
			unlink($css_file);
	}

	update_theme_stylesheet_list("1");

	if (class_exists('MybbStuff_MyAlerts_AlertTypeManager')) {
		$alertTypeManager = MybbStuff_MyAlerts_AlertTypeManager::getInstance();

		if (!$alertTypeManager) {
			$alertTypeManager = MybbStuff_MyAlerts_AlertTypeManager::createInstance($db, $cache);
		}

		$alertTypeManager->deleteByCode('mybb_wiki_alert_code');
	}
}

function wiki_activate()
{
	global $db, $mybb;

	$q = $db->simple_select("templategroups", "COUNT(*) as count", "title = 'Wiki'");
	$c = $db->fetch_field($q, "count");
	$db->free_result($q);

	if($c < 1)
	{
		$ins = array(
			"prefix"	=> "wiki",
			"title"		=> "Wiki",
			);

		$db->insert_query("templategroups", $ins);
	}

	// Template insertion
	$dir = new DirectoryIterator(MYBB_ROOT . 'inc/plugins/wiki/templates');

	foreach($dir as $file)
	{
		if(!$file->isDot() && !$file->isDir() && pathinfo($file->getFilename(), PATHINFO_EXTENSION) == 'html')
		{
			// And so the fun begins.

			$ins = array(
				"tid"		=>	0,
				"title"		=>	"wiki_" . $file->getBasename('.html'),
				"template"	=>	$db->escape_string(file_get_contents($file->getPathname())),
				"sid"		=>	"-2",
				"version"	=>	$mybb->version + 1,
				"dateline"	=>	time(),
				);

			$db->insert_query("templates", $ins);
		}
	}

	require_once MYBB_ROOT."/inc/adminfunctions_templates.php";
	find_replace_templatesets("header", "#".preg_quote("{\$menu_portal}")."#i", "{\$menu_portal}{\$menu_wiki}");
}

function wiki_deactivate()
{
	global $db;

	$db->delete_query("templates", "title LIKE 'wiki_%' AND sid='-2'");

	require_once MYBB_ROOT."/inc/adminfunctions_templates.php";
	find_replace_templatesets("header", "#".preg_quote("{\$menu_wiki}")."#i", "");
}

// thank you to pavemen for the following two functions! :-)
function wiki_fetch_wol(&$user_activity)
{
	global $user, $mybb;

	//get the base filename
	$split_loc = explode(".php", $user_activity['location']);
	if($split_loc[0] == $user['location'])
	{
		$filename = '';
	}
	else
	{
		$filename = my_substr($split_loc[0], -my_strpos(strrev($split_loc[0]), "/"));
	}

	//get parameters of the URI
	if($split_loc[1])
	{
		$temp = explode("&amp;", my_substr($split_loc[1], 1));
		foreach($temp as $param)
		{
			$temp2 = explode("=", $param, 2);
			$temp2[0] = str_replace("amp;", '', $temp2[0]);
			$parameters[$temp2[0]] = $temp2[1];
		}
	}

	switch($filename)
	{
		case "wiki":
		if($parameters['action'] == 'new')
		{
			$user_activity['activity'] = "wiki_new";
		}
		elseif($parameters['action'] == 'edit')
		{
			$user_activity['activity'] = "wiki_edit";
		}
		elseif($parameters['action'] == 'protect')
		{
			$user_activity['activity'] = "wiki_protect";
		}
		elseif($parameters['action'] == 'categories')
		{
			$user_activity['activity'] = "wiki_categories";
		}
		elseif($parameters['action'] == 'export')
		{
			$user_activity['activity'] = "wiki_export";
		}
		elseif($parameters['action'] == 'diff')
		{
			$user_activity['activity'] = "wiki_diff";
		}
		elseif($parameters['action'] == 'contributors')
		{
			$user_activity['activity'] = "wiki_contributors";
		}
		else
		{
			$user_activity['activity'] = "wiki_view";
		}
		break;
	}

	return $user_activity;
}

function wiki_build_friendly(&$plugin_array)
{
	global $lang, $mybb;

	$lang->load('wiki');

	switch($plugin_array['user_activity']['activity'])
	{
		case "wiki_view":
		$plugin_array['location_name'] = $lang->viewing_wiki;
		break;
		case "wiki_new":
		$plugin_array['location_name'] = $lang->wiki_new;
		case "wiki_edit":
		break;
		$plugin_array['location_name'] = $lang->wiki_edit;
		break;
		case "wiki_protect":
		$plugin_array['location_name'] = $lang->wiki_protect;
		break;
		case "wiki_categories":
		$plugin_array['location_name'] = $lang->wiki_categories;
		break;
		case "wiki_export":
		$plugin_array['location_name'] = $lang->wiki_export;
		break;
		case "wiki_diff":
		$plugin_array['location_name'] = $lang->wiki_diff;
		break;
		case "wiki_category_listing":
		$plugin_array['location_name'] = $lang->wiki_category_listing;
		break;
		case "wiki_contributors":
		$plugin_array['location_name'] = $lang->wiki_wol_contributors;
		break;
	}

	return $plugin_array;
}

/*
 * MYCODE PARSING
 * The following functions control the MyCode parsing of the plugin.
 * If you edit them, you should have a firm grasp of regex.
 * All have callbacks to relevant functions, so for instance,
 * if the aid is provided, then it performs a callback to
 * wiki_do_mycode_with_id, which builds the relevant link from the cache,
 * by looking up the aid in the cache array.
 */
function wiki_parse_mycode(&$message)
{
	$message = preg_replace_callback("#\[\[(.*?)\]\]#si", "wiki_do_mycode_with_id", $message);
	$message = preg_replace_callback("#\[wiki=(.*?)\]#si", "wiki_do_mycode_with_id", $message);
}

function wiki_do_mycode_with_id($matches)
{
	global $cache, $lang;

	$articles = $cache->read('wiki_articles');

	$name = $articles[$matches[1]];

	return "<a href=\"wiki.php?action=view&id={$matches[1]}\" class=\"wiki_link\">Wiki: {$name}</a>";
}

/**
 * GLOBAL START
 * Builds a list of links for the wiki -- for instance WIKI_URL holds the base URL for the wiki wherever you are. loaded globally.
 * This needs updating at some point
 * Also handles MyAlerts formatting
 */
function wiki_global_start()
{
	global $mybb, $lang, $menu_wiki, $templates;
	$lang->load('wiki'); // Just in case

	if($mybb->settings['seourls'] == "yes" || ($mybb->settings['seourls'] == "auto" && isset($_SERVER['SEO_SUPPORT']) && $_SERVER['SEO_SUPPORT'] == 1))
	{
		define("WIKI_URL", "wiki.html");
		define("WIKI_VIEW", "wiki.html");
		define("WIKI_EDIT", "wiki.html");
		define("WIKI_NEW", "wiki.html");
		define("WIKI_PROTECT", "wiki.html");
		define("WIKI_CATEGORIES", "wiki.html");
		define("WIKI_EXPORT", "wiki.html");
		define("WIKI_DIFF", "wiki.html");
		define("WIKI_CATEGORY_LISTING", "wiki.html");
		define("WIKI_CONTRIBUTORS", "wiki.html");
	}
	else
	{
		define("WIKI_URL", "wiki.php");
		define("WIKI_VIEW", "wiki.php");
		define("WIKI_EDIT", "wiki.php");
		define("WIKI_NEW", "wiki.php");
		define("WIKI_PROTECT", "wiki.php");
		define("WIKI_CATEGORIES", "wiki.php");
		define("WIKI_EXPORT", "wiki.php");
		define("WIKI_DIFF", "wiki.php");
		define("WIKI_CATEGORY_LISTING", "wiki.php");
		define("WIKI_CONTRIBUTORS", "wiki.php");
	}

	if (class_exists('MybbStuff_MyAlerts_AlertFormatterManager')) {
		require_once MYBB_ROOT . "inc/plugins/wiki/WikiCustomAlertFormatter.php";
		$formatterManager = MybbStuff_MyAlerts_AlertFormatterManager::getInstance();

		if (!$formatterManager) {
			$formatterManager = MybbStuff_MyAlerts_AlertFormatterManager::createInstance($mybb, $lang);
		}

		$formatterManager->registerFormatter(
			new WikiCustomAlertFormatter($mybb, $lang, 'mybb_wiki_alert_code')
			);
	}

	eval("\$menu_wiki .= \"".$templates->get("wiki_menu_item")."\";");
}

function wiki_admin_user_groups_add_commit_end() {
	global $gid;

	$permission = PermissionHandler::singleton();
	$permission->register_group($gid);
}

function wiki_admin_user_groups_delete_commit_end() {
	global $usergroup;
	
	$permission = PermissionHandler::singleton();
	$permission->delete_group($usergroup['gid']);
}
