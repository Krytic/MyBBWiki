<?php

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

define('IN_MYBB', 1);
require_once "global.php";

$lang->load('wiki');

if(!$db->table_exists('wiki')) {
	error($lang->wiki_oops);
}

// automagic self updating $templatelist.
$templatelist =	"";
$dir = new DirectoryIterator(MYBB_ROOT . 'inc/plugins/wiki/templates');
foreach($dir as $file)
{
	if(!$file->isDot() && !$file->isDir() && pathinfo($file->getFilename(), PATHINFO_EXTENSION) == 'html')
	{
		$templatelist .= "wiki_" . $file->getBasename('.html') . ",";
	}
}

// and manually add in the remaining templates that are not in the templates dir
$templatelist .= "codebuttons";

$plugins->run_hooks('wiki_start');

// loading permissions from cache
$permissions = $cache->read('wiki_permissions');
$permissions = $permissions["gid_{$mybb->user['usergroup']}"];

$query = $db->write_query(sprintf("SELECT * FROM `%swiki_settings`", TABLE_PREFIX));

while($row = $db->fetch_array($query))
{
	// would love to do this via cache too... maybe in a future release
	$settings[$row['name']] = $row['value'];
}

if(!$settings['wiki_enable'] || !isset($settings['wiki_enable']))
{
	error($lang->wiki_oops);
}

add_breadcrumb($lang->wiki, "wiki.php");

// Okay, page has been set up. Now we determine what the user is trying to do.

if(function_exists('myalerts_is_installed') && myalerts_is_installed()) {

	// terribly inefficient but it will be made more efficient soon.
	$query = $db->write_query(sprintf("SELECT * FROM `%swiki`", TABLE_PREFIX));
	$wiki = $db->fetch_array($query);

	$un = '';
	if(in_array($mybb->user['uid'], explode(',', $wiki['watching'])))
	{
		$un = "un";
		$lang->wiki_watch = $lang->wiki_unwatch;
	}
	eval("\$watch_bit = \"".$templates->get("wiki_watch_button")."\";");
}

$category_desc = ""; // define it up here so it doesn't need to be defined in numerous places.

if(!$mybb->input['action'])
{
	$query = $db->write_query(sprintf("SELECT * FROM `%swiki`", TABLE_PREFIX));
	$wiki_articles = $db->num_rows($query);

	$category_index = array();
	$query2 = $db->write_query(sprintf("SELECT * FROM `%swiki_categories`", TABLE_PREFIX));

	while($cat = $db->fetch_array($query2)) {
		$category_index[$cat['cid']] = $cat;
	}

	if($wiki_articles > 0)
	{
		while($wiki = $db->fetch_array($query))
		{
			$wiki['lastauthor'] = build_profile_link($wiki['lastauthor'], $wiki['lastauthorid']);
			$wiki['category_url'] = $wiki['category'];
			$wiki['category'] = $category_index[$wiki['category']]['title'];
			eval("\$wikilist .= \"".$templates->get("wiki_wikilist")."\";");
		}
	}
	else
	{
		if($mybb->user['uid'])
		{
			$error_message = $lang->wiki_noarticles;
		}
		else
		{
			$error_message = $lang->wiki_noarticles_guest;
		}

		eval("\$wikilist = \"".$templates->get("wiki_wikilist_none")."\";");
	}

	if($permissions['can_export'])
	{
		eval("\$exportbit = \"".$templates->get("wiki_exportbit")."\";");
	}

	$query = $db->write_query(sprintf("SELECT * FROM `%swiki_categories`", TABLE_PREFIX));

	$category_bit = "";
	while($category = $db->fetch_array($query)) {
		eval("\$category_bit .= \"".$templates->get("wiki_category_bit")."\";");
	}

	$plugins->run_hooks("wiki_pre_eval_page");
	eval("\$page = \"".$templates->get("wiki_page")."\";");

	output_page($page);
}
elseif($mybb->input['action'] == 'view')
{
	if(!$permissions['can_view'])
	{
		error_no_permission();
	}

	if(!isset($mybb->input['id']))
	{
		error($lang->wiki_pickarticle);
	}

	$id = (int) $mybb->input['id'];
	$query = $db->write_query(sprintf("SELECT * FROM `%swiki` WHERE `id`='{$id}'", TABLE_PREFIX));

	if($db->num_rows($query) == 0)
	{
		error($lang->wiki_doesntexist);
	}

	$wiki = $db->fetch_array($query);

	$protectedbit = $protect_opt = '';

	if($wiki['protected'])
	{
		eval("\$protectedbit = \"".$templates->get("wiki_protectedbit")."\";");
	}
	
	if($permissions['can_protect'] && !$wiki['protected'])
	{
		eval("\$protect_opt = \"".$templates->get("wiki_protect")."\";");
	}

	$query2 = $db->write_query(sprintf("SELECT title FROM `%swiki_categories` WHERE cid=" . $wiki['category'] . " LIMIT 1", TABLE_PREFIX));

	add_breadcrumb($db->fetch_array($query2)['title'], $url='wiki.php?action=categories&cid=' . $wiki['category']);
	add_breadcrumb($wiki['title']);

	if($settings['wiki_mybbparser'])
	{
		require_once MYBB_ROOT.'inc/class_parser.php';

		$parser = new postParser;

		$use_mycode = 1;

		if($settings['wiki_markdown'])
		{
			$use_mycode = 0;
		}

		$options = array(
			"allow_html"		=>	(int)$settings['wiki_parse_html'],
			"filter_badwords"	=>	1,
			"allow_mycode"		=>	$use_mycode,
			"allow_smileys"		=>	(int)$settings['wiki_parse_smileys'],
			"nl2br"				=>	1,
			"me_username"		=>	0,
			"allow_imgcode"		=>	$use_mycode);

		$wiki['content'] = $parser->parse_message($wiki['content'], $options);
	}

	if($settings['wiki_markdown'])
	{
		require_once MYBB_ROOT.'inc/plugins/wiki/markdown/parsedown.php';

		$Parsedown = new Parsedown();
		$wiki['content'] = str_replace("&gt;", ">", $wiki['content']); // TODO: Hacky fix, and exploitable
		$wiki['content'] = $Parsedown->text($wiki['content']);

	}

	require_once MYBB_ROOT.'inc/plugins/wiki/toc/Toc.php';

	$toc = new ashtaev\Toc($wiki['content']);

	$wiki_toc = $toc->getToc();
	$wiki['content'] = $toc->getPost();

	$template_list = $db->write_query(sprintf("SELECT * FROM `%swiki_templates`", TABLE_PREFIX));

	$db->write_query(sprintf("UPDATE %swiki SET views=views+1 WHERE id=" . $wiki['id'], TABLE_PREFIX));

	if($db->num_rows($template_list) > 0)
	{
		while($template = $db->fetch_array($template_list))
		{
			$wiki['content'] = str_replace("{{{$template['search']}}}", $template['replace'], $wiki['content']);
		}
	}

	$talk_bit = "";
	if($settings['wiki_talk_enabled']) {
		eval("\$talk_bit = \"" . $templates->get("wiki_article_talk_bit") . "\";");
	}

	$plugins->run_hooks("wiki_view");

	eval("\$page = \"".$templates->get("wiki_article")."\";");

	output_page($page);
}
elseif($mybb->input['action'] == 'talk')
{
	if(!$permissions['can_view'])
	{
		error_no_permission();
	}

	if(!isset($mybb->input['id']))
	{
		error($lang->wiki_pickarticle);
	}

	if(!$settings['wiki_talk_enabled'])
	{
		error($lang->wiki_talk_disabled);
	}


	$id = (int) $mybb->input['id'];
	$query = $db->write_query(sprintf("SELECT * FROM `%swiki` WHERE `id`='{$id}'", TABLE_PREFIX));

	if($db->num_rows($query) == 0)
	{
		error($lang->wiki_doesntexist);
	}

	$wiki = $db->fetch_array($query);

	if($wiki['protected'])
	{
		eval("\$protectedbit = \"".$templates->get("wiki_protectedbit")."\";");
	}



	add_breadcrumb($wiki['title']);

	if($settings['wiki_mybbparser'])
	{
		require_once MYBB_ROOT.'inc/class_parser.php';

		$parser = new postParser;

		$use_mycode = 1;

		if($settings['wiki_markdown'])
		{
			$use_mycode = 0;
		}

		$options = array(
			"allow_html"		=>	(int)$settings['wiki_parse_html'],
			"filter_badwords"	=>	1,
			"allow_mycode"		=>	$use_mycode,
			"allow_smileys"		=>	(int)$settings['wiki_parse_smileys'],
			"nl2br"				=>	1,
			"me_username"		=>	0,
			"allow_imgcode"		=>	$use_mycode);

		$wiki['notepad'] = $parser->parse_message($wiki['notepad'], $options);
	}

	if($settings['wiki_markdown'])
	{
		require_once MYBB_ROOT.'inc/plugins/wiki/markdown/parsedown.php';

		$Parsedown = new Parsedown();
		$wiki['notepad'] = str_replace("&gt;", ">", $wiki['notepad']); // TODO: Hacky fix, and exploitable
		$wiki['notepad'] = $Parsedown->text($wiki['notepad']);
	}

	preg_match_all("/(signoff:[0-9]*)/", $wiki['notepad'], $matches);

	foreach($matches[0] as $signoff) {
		if($signoff == '') {
			continue;
		}

		$tmp = preg_match_all("/[0-9]*/", $signoff, $match);

		$uid = $match[0][8];

		$user = get_user($uid);

		$user['formatted_username'] = format_name($user['username'], $user['usergroup']);

		$wiki['notepad'] = str_replace("[signoff:{$uid}]", "- " . $user['formatted_username'], $wiki['notepad']);

	}

	$wiki['notes'] = $wiki['notepad']; // backwards compatibility, will be removed in a future commit

	$plugins->run_hooks("wiki_view_notes");

	$talk_bit = "";

	if($settings['wiki_talk_enabled']) {
		eval("\$talk_bit = \"" . $templates->get("wiki_article_talk_bit") . "\";");
	}

	eval("\$page = \"".$templates->get("wiki_notes")."\";");

	output_page($page);
}
elseif($mybb->input['action'] == 'edit')
{
	if(!$permissions['can_edit'])
	{
		error_no_permission();
	}

	if(!isset($mybb->input['id']))
	{
		error($lang->wiki_pickarticle);
	}



	$id = (int) $mybb->input['id'];
	$info = $db->write_query(sprintf("SELECT * FROM `%swiki` WHERE `id`='{$id}'", TABLE_PREFIX));
	$article = $db->fetch_array($info);
	$wiki = $article; // backwards compatibility

	if($mybb->request_method != "post")
	{
		add_breadcrumb($lang->sprintf($lang->wiki_editing, $article['title']));

		if($db->num_rows($info) == 0)
		{
			error($lang->wiki_doesntexist);
		}

		if($article['protected'] && !$mybb->usergroup['canmodcp'])
		{
			// Moderators can always edit protected articles.
			error($lang->wiki_protected);
		}

		$existing_content = $article['content'];

		$codebuttons = '';

		if($settings['wiki_mycode_editor'] && !$settings['wiki_markdown'])
		{
			require_once 'inc/functions.php';
			$codebuttons = build_mycode_inserter();
		}

		$plugins->run_hooks("wiki_edit_form");

		$talk_bit = "";
		if($settings['wiki_talk_enabled']) {
			eval("\$talk_bit = \"" . $templates->get("wiki_article_talk_bit") . "\";");
		}

		eval("\$page = \"".$templates->get("wiki_article_edit")."\";");

		output_page($page);
	}
	else
	{
		// First, we should validate the incoming post request... no CSRF please...
		verify_post_check($mybb->input['my_post_key']);

		// Update the article.
		$id = (int) $mybb->input['id'];

		$authors = $article['authors'];

		$authors_array = explode(",", $authors);

		foreach($authors_array as $uid)
		{
			if($contributed)
			{
				break;
			}
			else
			{
				if($uid == $mybb->user['uid'])
				{
					$contributed = true;
				}
			}
		}

		if(!$contributed)
		{
			// Concatenate the author onto our string.
			$authors .= ",{$mybb->user['uid']}";
		}

		// And now the magic begins.

		$plugins->run_hooks("wiki_edit_commit");

		$message = $db->escape_string($mybb->input['message']);
		$id = (int) $mybb->input['id'];
		$notes = $db->escape_string($mybb->input['notes']);

		$notes = str_replace("~~~~", "[signoff:{$mybb->user['uid']}]", $notes);

		// We should have a condition in here so that if someone @s someone, it sends them a notification.

		$sql = $db->write_query(
			sprintf(
				"UPDATE `%swiki`", TABLE_PREFIX) . "SET `content`='{$message}', `authors`='{$authors}', `lastauthor`='{$mybb->user['username']}', `lastauthorid`='{$mybb->user['uid']}', `notepad`='{$notes}'
			WHERE `id`='{$id}'");


		$sql = $db->write_query(sprintf("INSERT INTO %swiki_edits", TABLE_PREFIX) . "(`aid`,`author`,`revision`) VALUES('{$id}','{$mybb->user['uid']}','{$message}')");

		if(class_exists('MybbStuff_MyAlerts_AlertTypeManager'))
		{
			$user_array = explode(',', $wiki['watching']);
			$alertType = MybbStuff_MyAlerts_AlertTypeManager::getInstance()->getByCode('mybb_wiki_alert_code');

			if ($alertType != null && $alertType->getEnabled()) {
				$alerts = array();
				foreach($user_array as $user)
				{
					$alert = new MybbStuff_MyAlerts_Entity_Alert($user, $alertType, $id);
					$alert->setExtraDetails(array('title' => $wiki['title']));

					$alerts[] = $alert;
				}

				MybbStuff_MyAlerts_AlertManager::getInstance()->addAlerts($alerts);
			}
		}

		if(!$sql)
		{
			error($lang->wiki_notupdated);
		}
		else
		{
			// All done. :-)

			$id = (int) $mybb->input['id'];
			redirect("wiki.php?action=view&id=$id", $lang->wiki_edited);
		}
	}
}
elseif($mybb->input['action'] == 'new')
{
	if(!$permissions['can_create'])
	{
		error_no_permission();
	}

	if($mybb->request_method != "post")
	{
		if($errors)
		{
			$errors = inline_error($errors);
			$title = htmlspecialchars_uni($mybb->input['wiki_title']);
			$message = htmlspecialchars_uni($mybb->input['message']);
		}

		add_breadcrumb($lang->wiki_new);

		if($settings['wiki_mycode_editor'] && !$settings['wiki_markdown'])
		{
			require_once 'inc/functions.php';
			$codebuttons = build_mycode_inserter();
		}
		else
		{
			$codebuttons = '';
		}

		$category_select = '<select name="category" id="category" height="3">';

		$cats = $db->write_query(sprintf("SELECT * FROM `%swiki_categories`", TABLE_PREFIX));

		while ($category = $db->fetch_array($cats))
		{
			$selected = '';

			if($category['cid'] == $mybb->input['category'])
			{
				$selected = ' selected="selected"';
			}

			eval("\$categories .= \"".$templates->get("wiki_category_select_item")."\";");
		}

		eval("\$category_select = \"".$templates->get("wiki_category_select_list")."\";");

		eval("\$page = \"".$templates->get("wiki_new_article")."\";");
		output_page($page);
	}
	else
	{
		// First, we should validate the incoming post request... no CSRF please...
		verify_post_check($mybb->input['my_post_key']);

		// Check for errors...

		if(!$mybb->input['category'])
		{
			$errors[] = $lang->wiki_nocategory;
		}
		if(!$mybb->input['message'])
		{
			$errors[] = $lang->wiki_nomessage;
		}
		if(!$mybb->input['wiki_title'])
		{
			$errors[] = $lang->wiki_notitle;
		}

		if($errors)
		{
			$mybb->input['action'] = 'new';
		}
		else
		{
			// No errors? Cool.
			$title = $db->escape_string($mybb->input['wiki_title']);
			$message = $db->escape_string($mybb->input['message']);
			$category = (int)$mybb->input['category'];



			$plugins->run_hooks("wiki_new_commit");

			$sql = $db->write_query(
				sprintf(
					"INSERT INTO %swiki(authors,title,content,lastauthor,lastauthorid,category,original)
					VALUES('{$mybb->user['uid']}','{$title}','{$message}','{$mybb->user['username']}','{$mybb->user['uid']}',{$category},'{$message}')",
					TABLE_PREFIX
				)
			);

			$updates = $cache->read('wiki_articles');
			$updates[$db->insert_id()] = $title;

			$cache->update('wiki_articles', $updates);

			if(!$sql)
			{
				error($lang->wiki_notposted);
			}
			else
			{
				redirect("wiki.php", $lang->wiki_posted);
			}
		}
	}
}
elseif($mybb->input['action'] == 'protect')
{
	if(!$permissions['can_protect'])
	{
		error_no_permission();
	}
	else
	{
		$plugins->run_hooks("wiki_pre_protect");

		$id = (int) $mybb->input['id'];
		$sql = $db->write_query(sprintf("UPDATE `%swiki` SET `protected`='1' WHERE `id`='{$id}'", TABLE_PREFIX));

		if(!$sql)
		{
			error($lang->wiki_notprotected);
		}
		else
		{
			$id = (int) $mybb->input['id'];
			redirect("wiki.php?action=view&id=$id", $lang->wiki_wasprotected);
		}
	}
}
elseif($mybb->input['action'] == 'categories')
{
	if(!isset($mybb->input['cid']))
	{
		error($lang->wiki_nocat);
	}

	$cid = (int)$mybb->input['cid'];

	$category_index = array();
	$query = $db->write_query(sprintf("SELECT * FROM `%swiki` WHERE `category`='{$cid}'", TABLE_PREFIX));
	$sql = $db->write_query(sprintf("SELECT * FROM `%swiki_categories`", TABLE_PREFIX));

	while($cat = $db->fetch_array($sql)) {
		$category_index[$cat['cid']] = $cat;
	}

	$wikilist = "";

	$category_desc = $lang->sprintf($lang->wiki_now_viewing, $category_index[$cid]['title'], $category_index[$cid]['description']);
	eval("\$category_desc = \"".$templates->get("wiki_category_desc")."\";");

	if($db->num_rows($query) > 0)
	{
		while($article = $db->fetch_array($query))
		{
			$wiki = $article;
			$wiki['lastauthor'] = build_profile_link($wiki['lastauthor'], $wiki['lastauthorid']);
			$wiki['category_url'] = $wiki['category'];
			$wiki['category'] = $category_index[$wiki['category']]['title'];
			eval("\$wikilist .= \"".$templates->get("wiki_wikilist")."\";");
		}
	}
	else
	{
		$error_message = $lang->wiki_noarticlescat;
		eval("\$wikilist = \"".$templates->get("wiki_wikilist_none")."\";");
	}


	add_breadcrumb($lang->wiki_filter, "wiki.php?action=categories&cid=" . $mybb->input['cid']);

	if($permissions['can_export'])
	{
		eval("\$exportbit = \"".$templates->get("wiki_exportbit")."\";");
	}

	$category_bit = "";
	foreach($category_index as $cid => $category) {
		eval("\$category_bit .= \"".$templates->get("wiki_category_bit")."\";");
	}

	$plugins->run_hooks("wiki_pre_eval_page");
	eval("\$page = \"".$templates->get("wiki_page")."\";");

	output_page($page);
}
elseif($mybb->input['action'] == 'export')
{
	if($permissions['can_export'] && $settings['wiki_export_allowed'])
	{
		$xml = "<?xml version=\"1.0\" ?>\n";
		$xml .= "<wiki>\n";

		$sql = $db->write_query(sprintf("SELECT * FROM `%swiki`", TABLE_PREFIX));

		while($article = $db->fetch_array($sql))
		{

			// Generate a nice format for the xml.
			$xml .= "	<article>\n";

			foreach($article as $key => $value) {
				$xml .= "		<{$key}>{$value}</{$key}>\n";
			}

			$xml .= "	</article>\n";
		}

		$xml .= "</wiki>";

		$timestamp = date('d-m-Y-H-i-s');

		if($db->num_rows($sql) > 0) {
			// Send our headers...
			header("Content-type: text/xml");
			header('Content-Disposition: attachment; filename="wikiarticles-' . $timestamp . '.xml"'); // This allows us to offer it as a downloadable file.

			// And output the page.
			echo $xml;
		}
		else {
			error($lang->wiki_no_articles);
		}
	}
	else
	{
		error_no_permission();
	}
}
elseif($mybb->input['action'] == "diff_list")
{
	$aid = (int)$mybb->input['aid'];

	$query = sprintf("SELECT * FROM `%swiki` WHERE `id`='{$aid}'", TABLE_PREFIX);
	$query = $db->write_query($query);
	$article = $db->fetch_array($query);
	$wiki = $article; // backwards compatibility

	$query = sprintf("SELECT * FROM `%swiki_edits` WHERE `aid`='{$aid}'", TABLE_PREFIX);
	$query = $db->write_query($query);

	if($db->num_rows($query) == 0)
	{
		error($lang->wiki_invalid_article);
	}


	$revision_list_bits = "";

	while($edit = $db->fetch_array($query)) {
		$author = get_user($edit['author']);
		$edit['title'] = $author['username'];
		eval("\$revision_list_bits .= \"".$templates->get("wiki_revision_list_bit")."\";");
	}

	$talk_bit = "";
	if($settings['wiki_talk_enabled']) {
		eval("\$talk_bit = \"" . $templates->get("wiki_article_talk_bit") . "\";");
	}

	eval("\$page = \"".$templates->get("wiki_revision_list")."\";");

	output_page($page);
}
elseif($mybb->input['action'] == "diff")
{
	$aid = (int)$mybb->input['aid'];

	$query = sprintf("SELECT * FROM `%swiki` WHERE `id`='{$aid}'", TABLE_PREFIX);
	$query = $db->write_query($query);

	if(isset($mybb->input['eid']))
	{
		$eid = (int)$mybb->input['eid'];
		$edit_query = sprintf("SELECT * FROM `%swiki_edits` WHERE `aid`='{$aid}' AND `eid`='{$eid}';", TABLE_PREFIX);
		$edit_query = $db->write_query($edit_query);

		if($db->num_rows($edit_query) == 0)
		{
			error($lang->wiki_invalid_article);
		}

		$article_edits = $db->fetch_array($edit_query);
	}

	if($db->num_rows($query) == 0)
	{
		error($lang->wiki_invalid_article);
	}

	$article = $db->fetch_array($query);
	$wiki = $article; // this is for backwards compatibility

	if(isset($article_edits))
	{
		$article['original'] = $article_edits['revision'];
	}

	require_once MYBB_ROOT . "inc/3rdparty/diff/Diff.php";
	require_once MYBB_ROOT . "inc/3rdparty/diff/Diff/Renderer.php";
	require_once  MYBB_ROOT . "inc/3rdparty/diff/Diff/Renderer/Inline.php";

	$diff = new Horde_Text_Diff('auto', array(array($article['original']), array($article['content'])));
	$renderer = new Horde_Text_Diff_Renderer_inline();

	$diff_report = $renderer->render($diff);

	if($article['original'] == $article['content'])
	{
		$diff_report = $lang->wiki_identical;
	}

	add_breadcrumb($lang->wiki_revisions, "wiki.php");
	add_breadcrumb($article['title'], "wiki.php?action=diff&amp;aid={$aid}");

	$talk_bit = "";
	if($settings['wiki_talk_enabled']) {
		eval("\$talk_bit = \"" . $templates->get("wiki_article_talk_bit") . "\";");
	}

	eval("\$page = \"".$templates->get("wiki_revision_article")."\";");

	output_page($page);
}
elseif($mybb->input['action'] == 'contributors')
{
	// Save resources... rather than possibly query the db in get_user, we grab all users, assign them
	// to an array,and go through that when we need to. Slightly inefficient for huge numbers of members
	// but when a new method comes about I will use that,
	$query = $db->write_query(sprintf("SELECT * FROM `%susers`", TABLE_PREFIX));

	$users = array();
	while($row = $db->fetch_array($query))
	{
		$users[$row['uid']] = array(
			'username' => $row['username'],
			'usergroup' => $row['usergroup'],
			'displaygroup' => $row['displaygroup']
		);
	}
	$db->free_result($query);

	$id = (int)$mybb->input['id'];
	$query = $db->write_query(sprintf("SELECT * FROM `%swiki` WHERE `id`='{$id}'", TABLE_PREFIX));

	if($db->num_rows($query) == 0)
	{
		error($lang->wiki_invalid_article);
	}

	$article = $db->fetch_array($query);
	$wiki = $article; // backwards compatibility

	$contributors = explode(",", $article['authors']);

	$authors = '';
	foreach($contributors as $uid)
	{
		$author = format_name($users[$uid]['username'], $users[$uid]['usergroup'], $users[$uid]['displaygroup']);
		$author = build_profile_link($author, $uid);

		eval("\$authors .= \"".$templates->get("wiki_contributor_bit")."\";");
	}

	$lang->wiki_contributors = $lang->sprintf($lang->wiki_contributors, $article['title']);

	$talk_bit = "";
	if($settings['wiki_talk_enabled']) {
		eval("\$talk_bit = \"" . $templates->get("wiki_article_talk_bit") . "\";");
	}

	eval("\$page = \"".$templates->get("wiki_contributors")."\";");

	output_page($page);
}
elseif($mybb->input['action'] == 'watch')
{
	if(!function_exists('myalerts_is_installed') || !myalerts_is_installed())
	{
		error($lang->wiki_myalerts_not_installed);
	}

	$id = (int)$mybb->input['id'];

	$query = $db->write_query(sprintf("SELECT watching FROM `%swiki` WHERE `id`='{$id}'", TABLE_PREFIX));

	if($db->num_rows($query) === 0)
	{
		error($lang->wiki_doesntexist);
	}

	$wiki = $db->fetch_array($query);

	$plugins->run_hooks('wiki_watch_start');

	if($wiki['watching'] != null)
	{
		$watching = explode(',',$wiki['watching']);

		if(in_array($mybb->user['uid'], $watching)) {
			error($lang->wiki_already_watching);
		}
	}

	if($wiki['watching'] == null)
	{
		$wiki['watching']= $mybb->user['uid'];
	}
	else
	{
		$wiki['watching'] .= ",{$mybb->user['uid']}";
	}

	$db->write_query(sprintf("UPDATE `%swiki` SET `watching`='{$wiki['watching']}' WHERE `id`='{$id}'", TABLE_PREFIX));

	$plugins->run_hooks('wiki_watch_end');

	redirect("wiki.php", $lang->wiki_now_watching);
}
elseif($mybb->input['action'] == 'unwatch')
{
	if(!function_exists('myalerts_is_installed') || !myalerts_is_installed())
	{
		error($lang->wiki_myalerts_not_installed);
	}

	$id = (int)$mybb->input['id'];

	$query = $db->write_query(sprintf("SELECT watching FROM `%swiki` WHERE `id`='{$id}'", TABLE_PREFIX));

	if($db->num_rows($query) === 0)
	{
		error($lang->wiki_doesntexist);
	}

	$wiki = $db->fetch_array($query);

	$plugins->run_hooks('wiki_unwatch_start');

	if($wiki['watching'] != null)
	{
		$watching = explode(',',$wiki['watching']);

		if(!in_array($mybb->user['uid'], $watching)) {
			error($lang->wiki_not_already_watching);
		}

		$pointer = array_search($mybb->user['uid'], $watching);

		unset($watching[$pointer]);

		$watching = implode(',', $watching);

	}

	$db->write_query(sprintf("UPDATE `%swiki` SET `watching`='{$watching}' WHERE `id`='{$id}'", TABLE_PREFIX));

	$plugins->run_hooks('wiki_unwatch_end');

	redirect("wiki.php", $lang->wiki_now_not_watching);
}

?>
