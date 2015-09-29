<?php

define('IN_MYBB', 1);
require_once "global.php";

$lang->load('wiki');

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
	// Will also be fired if the wiki isn't installed/activated, causing a pretty error page instead of SQL errors.
	error($lang->oops);
}

add_breadcrumb($lang->wiki, "wiki.php");

// Okay, page has been set up. Now we determine what the user is trying to do.

if(!$mybb->input['action'])
{
	$query = $db->write_query(sprintf("SELECT * FROM `%swiki`", TABLE_PREFIX));
	$wiki_articles = $db->num_rows($query);

	if($wiki_articles > 0)
	{
		while($wiki = $db->fetch_array($query))
		{
			$wiki['lastauthor'] = build_profile_link($wiki['lastauthor'], $wiki['lastauthorid']);
			$wiki['category_url'] = urlencode($wiki['category']);
			eval("\$wikilist .= \"".$templates->get("wiki_wikilist")."\";");
		}
	}
	else
	{
		if($mybb->user['uid'])
		{
			$error_message = $lang->noarticles;
		}
		else
		{
			$error_message = $lang->noarticles_guest;
		}

		eval("\$wikilist = \"".$templates->get("wiki_wikilist_none")."\";");
	}

	if($permissions['can_export'])
	{
		eval("\$exportbit = \"".$templates->get("wiki_exportbit")."\";");
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
		error($lang->pickarticle);
	}

	$id = (int) $mybb->input['id'];
	$query = $db->write_query(sprintf("SELECT * FROM `%swiki` WHERE `id`='{$id}'", TABLE_PREFIX));

	if($db->num_rows($query) == 0)
	{
		error($lang->doesntexist);
	}

	$wiki = $db->fetch_array($query);

	if($wiki['protected'])
	{
		eval("\$protectedbit = \"".$templates->get("wiki_protectedbit")."\";");
	}

	if($permissions['can_protect'])
	{
		eval("\$protect_opt = \"".$templates->get("wiki_protect")."\";");
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

		$wiki['content'] = $parser->parse_message($wiki['content'], $options);
	}

	if($settings['wiki_markdown'])
	{
		require_once MYBB_ROOT.'inc/plugins/wiki/markdown/markdown.php';

		$wiki['content'] = Markdown($wiki['content']);
	}

	$template_list = $db->write_query(sprintf("SELECT * FROM `%swiki_templates`", TABLE_PREFIX));

	if($db->num_rows($template_list) > 0)
	{
		while($template = $db->fetch_array($template_list))
		{
			$wiki['content'] = str_replace("{{{$template['search']}}}", $template['replace'], $wiki['content']);
		}
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
		error($lang->pickarticle);
	}

	$id = (int) $mybb->input['id'];
	$query = $db->write_query(sprintf("SELECT * FROM `%swiki` WHERE `id`='{$id}'", TABLE_PREFIX));

	if($db->num_rows($query) == 0)
	{
		error($lang->doesntexist);
	}

	$wiki = $db->fetch_array($query);

	if($wiki['protected'])
	{
		eval("\$protectedbit = \"".$templates->get("wiki_protectedbit")."\";");
	}

	if($permissions['can_protect'])
	{
		eval("\$protect_opt = \"".$templates->get("wiki_protect")."\";");
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
		require_once MYBB_ROOT.'inc/plugins/wiki/markdown/markdown.php';

		$wiki['notepad'] = Markdown($wiki['notepad']);
	}

	preg_match_all("/(signoff:[0-9]*)/", $wiki['notepad'], $matches);

	foreach($matches[0] as $signoff) {
		if($signoff == '') {
			continue;
		}

		$tmp = preg_match_all("/[0-9]*/", $signoff, $match);

		$uid = $match[0][8];

		$user = get_user($uid);

		$wiki['notepad'] = str_replace("[signoff:{$uid}]", $user['username'], $wiki['notepad']);

	}

	$wiki['notes'] = $wiki['notepad']; // backwards compatibility, will be removed in a future commit

	$plugins->run_hooks("wiki_view_notes");

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
		error($lang->pickarticle);
	}

	$id = (int) $mybb->input['id'];
	$info = $db->write_query(sprintf("SELECT * FROM `%swiki` WHERE `id`='{$id}'", TABLE_PREFIX));
	$article = $db->fetch_array($info);

	if($mybb->request_method != "post")
	{
		add_breadcrumb($lang->sprintf($lang->editing, $article['title']));

		if($db->num_rows($info) == 0)
		{
			error($lang->doesntexist);
		}

		if($article['protected'] && !$mybb->usergroup['canmodcp'])
		{
			// Moderators can always edit protected articles.
			error($lang->protected);
		}

		$existing_content = $article['content'];

		$codebuttons = '';

		if($settings['wiki_mycode_editor'] && !$settings['wiki_markdown'])
		{
			require_once 'inc/functions.php';
			$codebuttons = build_mycode_inserter();
		}

		$plugins->run_hooks("wiki_edit_form");

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

		$sql = $db->write_query(
			sprintf(
				"UPDATE `%swiki`
				SET `content`='{$message}', `authors`='{$authors}', `lastauthor`='{$mybb->user['username']}', `lastauthorid`='{$mybb->user['uid']}', `notepad`='{$notes}'
				WHERE `id`='{$id}'", TABLE_PREFIX
				)
			);

		$sql = $db->write_query(sprintf("INSERT INTO %swiki_edits(`aid`,`author`,`revision`) VALUES('{$id}','{$mybb->user['uid']}','{$message}')", TABLE_PREFIX));

		if(!$sql)
		{
			error($lang->notupdated);
		}
		else
		{
			// All done. :-)

			$id = (int) $mybb->input['id'];
			header("Location: wiki.php?action=view&id=$id");
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

		add_breadcrumb($lang->new);

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

			if($category['title'] == $mybb->input['category'])
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
			$errors[] = $lang->nocategory;
		}
		if(!$mybb->input['message'])
		{
			$errors[] = $lang->nomessage;
		}
		if(!$mybb->input['wiki_title'])
		{
			$errors[] = $lang->notitle;
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
			$category = $db->escape_string($mybb->input['category']);



			$plugins->run_hooks("wiki_new_commit");

			$sql = $db->write_query(
				sprintf(
					"INSERT INTO %swiki(authors,title,content,lastauthor,lastauthorid,category,original)
					VALUES('{$mybb->user['uid']}','{$title}','{$message}','{$mybb->user['username']}','{$mybb->user['uid']}','{$category}','{$message}')",
					TABLE_PREFIX
				)
			);

			$updates = $cache->read('wiki_articles');
			$updates[$db->insert_id()] = $title;

			$cache->update('wiki_articles', $updates);

			if(!$sql)
			{
				error($lang->notposted);
			}
			else
			{
				redirect("wiki.php", $lang->posted);
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
			error($lang->notprotected);
		}
		else
		{
			$id = (int) $mybb->input['id'];
			redirect("wiki.php?action=view&id=$id", $lang->wasprotected);
		}
	}
}
elseif($mybb->input['action'] == 'categories')
{
	if(!isset($mybb->input['title']))
	{
		error($lang->nocat);
	}

	add_breadcrumb($lang->wiki_filter, "wiki.php?action=categories&title=" . $mybb->input['title']);
	add_breadcrumb($mybb->input['title'], "wiki.php?action=categories&title=" . $mybb->input['title']);

	$title = urldecode($mybb->input['title']);
	$title = $db->escape_string($title);
	$sql = $db->write_query(sprintf("SELECT * FROM `%swiki` WHERE `category`='{$title}'", TABLE_PREFIX));

	$wiki_articles = $db->num_rows($sql);

	if($wiki_articles > 0)
	{
		while($wiki = $db->fetch_array($sql))
		{
			$wiki['lastauthor'] = build_profile_link($wiki['lastauthor'], $wiki['lastauthorid']);
			$wiki['category_url'] = urlencode($wiki['category_url']);
			eval("\$wikilist .= \"".$templates->get("wiki_wikilist")."\";");
		}
	}
	else
	{
		$error_message = $lang->noarticlescat;
		eval("\$wikilist = \"".$templates->get("wiki_wikilist_none")."\";");
	}

	if($permissions['can_export'])
	{
		eval("\$exportbit = \"".$templates->get("wiki_exportbit")."\";");
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
			$xml .= "		<id>{$article['id']}</id>\n";
			$xml .= "		<title>{$article['title']}</title>\n";
			$xml .= "		<content>{$article['content']}</content>\n";
			$xml .= "		<category>{$article['category']}</category>\n";
			$xml .= "		<lastauthor>{$article['lastauthor']}</lastauthor>\n";
			$xml .= "		<lastauthorid>{$article['lastauthorid']}</lastauthorid>\n";
			$xml .= "		<protected>{$article['protected']}</protected>\n";
			$xml .= "		<authors>{$article['authors']}</authors>\n";
			$xml .= "	</article>\n";
		}

		$xml .= "</wiki>";

		// Send our headers...
		header("Content-type: text/xml");
		header('Content-Disposition: attachment; filename="wiki-exported-articles.xml"'); // This allows us to offer it as a downloadable file.

		// And output the page.
		echo $xml;
	}
	else
	{
		error_no_permission();
	}
}
elseif($mybb->input['action'] == "diff")
{
	$aid = (int)$mybb->input['aid'];

	$query = $db->write_query(sprintf("SELECT * FROM `%swiki` WHERE `id`='{$aid}'", TABLE_PREFIX));

	if($db->num_rows($query) == 0)
	{
		error($lang->invalid_article);
	}

	$article = $db->fetch_array($query);

	require_once MYBB_ROOT . "inc/3rdparty/diff/Diff.php";
	require_once MYBB_ROOT . "inc/3rdparty/diff/Diff/Renderer.php";
	require_once  MYBB_ROOT . "inc/3rdparty/diff/Diff/Renderer/Inline.php";

	$diff = new Horde_Text_Diff('auto', array(array($article['original']), array($article['content'])));
	$renderer = new Horde_Text_Diff_Renderer_inline();

	$diff_report = $renderer->render($diff);

	if($article['original'] == $article['content'])
	{
		$diff_report = $lang->identical;
	}

	add_breadcrumb($lang->wiki_revisions, "wiki.php");
	add_breadcrumb($article['title'], "wiki.php?action=diff&amp;aid={$aid}");

	eval("\$page = \"".$templates->get("wiki_revision_article")."\";");

	output_page($page);
}
elseif($mybb->input['action'] == 'category_listing')
{
	$query = $db->write_query(sprintf("SELECT * FROM `%swiki_categories`", TABLE_PREFIX));

	add_breadcrumb($lang->categories, "wiki.php?action=category_listing");

	$category_list = '';

	while($category = $db->fetch_array($query))
	{
		$url = urlencode($category['title']);
		eval("\$category_list .= \"".$templates->get("wiki_category_item")."\";");
	}

	eval("\$page = \"".$templates->get("wiki_category_list")."\";");

	output_page($page);
}
elseif($mybb->input['action'] == 'contributors')
{
	// Save resources... rather than possibly query the db in get_user, we grab all users, assign them to an array, and go through that when we need to.
	$query = $db->write_query(sprintf("SELECT * FROM `%susers`", TABLE_PREFIX));

	$users = array();
	while($row = $db->fetch_array($query))
	{
		$users[$row['uid']] = $row['username'];
	}
	$db->free_result($query);

	$id = (int)$mybb->input['id'];
	$query = $db->write_query(sprintf("SELECT * FROM `%swiki` WHERE `id`='{$id}'", TABLE_PREFIX));

	if($db->num_rows($query) == 0)
	{
		error($lang->invalid_article);
	}

	$article = $db->fetch_array($query);

	$contributors = explode(",", $article['authors']);

	$authors = '';
	foreach($contributors as $uid)
	{
		$author = build_profile_link($users[$uid], $uid);

		eval("\$authors .= \"".$templates->get("wiki_contributor_bit")."\";");
	}

	$lang->contributors = $lang->sprintf($lang->contributors, $article['title']);
	eval("\$page = \"".$templates->get("wiki_contributors")."\";");

	output_page($page);
}

?>