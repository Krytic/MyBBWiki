<?php

// Disallow direct access to this file for security reasons
if(!defined("IN_MYBB"))
{
	die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

$lang->load('wiki');

$page->add_breadcrumb_item($lang->wiki_articles, 'index.php?module=wiki-articles');

$page->output_header($lang->wiki_articles);

$sub_tabs['wiki'] = array(
	'title'			=> $lang->wiki,
	'link'			=> 'index.php?module=wiki',
	'description'	=> $lang->wiki_description
	);

if (!$mybb->input['action'])
{
	$page->output_nav_tabs($sub_tabs, 'wiki');

	$table = new Table;

	$articles = $db->query(sprintf("SELECT * FROM `%swiki`", TABLE_PREFIX));
	$cats = $db->query(sprintf("SELECT * FROM `%swiki_categories`", TABLE_PREFIX));

	$plural = "";
	$numarticles = $db->num_rows($articles);
	if($numarticles > 1 || $numarticles === 0)
	{
		$plural = "s";
	}

	$catsplural = "y";
	$numcats = $db->num_rows($cats);
	if($cats > 1 || $cats === 0)
	{
		$catsplural = "ies";
	}

	$table->construct_cell($lang->wiki_welcome, array("class" => "align_center", "colspan" => 2));
	$table->construct_row();
	$table->construct_cell($lang->sprintf($lang->wiki_total_articles, $numarticles, $plural), array("class" => "align_center", "style" => "width: 50%"));
	$table->construct_cell($lang->sprintf($lang->wiki_total_cats, $numcats, $catsplural), array("class" => "align_center", "style" => "width: 50%"));
	$table->construct_row();
	$table->construct_cell($lang->wiki_check_for_updates, array("class" => "align_center", "style" => "width: 50%"));
	$table->construct_cell($lang->sprintf($lang->wiki_version, WIKI_VERSION), array("class" => "align_center", "style" => "width: 50%"));
	$table->construct_row();

	$table->output($lang->wiki_home);
}
else if ($mybb->input['action'] == "version_check")
{
	$updateurl = 'https://api.github.com/repos/Krytic/MyBBWiki/releases/latest';

	$page->output_nav_tabs($sub_tabs, 'wiki');

	$table = new Table;

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Not ideal in the least but we're not passing sensitive data. See: http://unitstep.net/blog/2009/05/05/using-curl-in-php-to-access-https-ssltls-protected-sites/
	curl_setopt($ch, CURLOPT_USERAGENT, 'MyBBWiki by Krytic Update Checker. https://github.com/Krytic');
	curl_setopt($ch, CURLOPT_URL, $updateurl);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	$data = curl_exec($ch);
	curl_close($ch);

	$latest_release = json_decode($data, true);

	$latest_release['tag_name2'] = str_replace('v', '', $latest_release['tag_name']); // releases are formatted v1.0.0 or v1.5.7

	if(version_compare(WIKI_VERSION, $latest_release['tag_name2']) < 0 && !$latest_release['prerelease']) {
		$table->construct_header($lang->sprintf($lang->wiki_new_version, $latest_release['tag_name']), array("colspan" => 2));
		$table->construct_row();

		$table->construct_cell($latest_release['body']);
		$table->construct_cell("<a href=\"{$latest_release['html_url']}\">" . $lang->new_release_download . "</a>", array('class' => 'align_center'));
		$table->construct_row();
	}
	else {
		$table->construct_cell($lang->wiki_updates, array("class" => "align_center"));
		$table->construct_row();
	}

	$table->output($lang->wiki_updates_header);
}

$page->output_footer();

?>