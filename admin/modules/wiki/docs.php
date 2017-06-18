<?php

// Disallow direct access to this file for security reasons
if(!defined("IN_MYBB"))
{
	die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

$lang->load('wiki');

$page->add_breadcrumb_item($lang->wiki_docs, 'index.php?module=wiki-docs');

$page->output_header($lang->wiki_docs);

$sub_tabs['wiki_docs'] = array(
	'title'			=> $lang->wiki_docs,
	'link'			=> 'index.php?module=wiki-docs',
	'description'	=> $lang->wiki_docs_description
	);

$inc = 3;

$dir = new DirectoryIterator(MYBB_ROOT . 'inc/plugins/wiki/docs');
require_once MYBB_ROOT.'inc/plugins/wiki/markdown/markdown.php';

foreach($dir as $file)
{
	if(!$file->isDot() && !$file->isDir() && pathinfo($file->getFilename(), PATHINFO_EXTENSION) == 'md')
	{
		$content = file_get_contents($file->getPathname());

		$regex = '#<meta>(.*?)</meta>#';
		preg_match_all($regex, $content, $matches);

		$cfg = explode('|', $matches[1][0]);
		$config = array();

		foreach($cfg as $key => $val)
		{
			$arr = explode(':', $val);
			$config[$arr[0]] = $arr[1];
		}

		if($config['title'] == 'Home') {
			continue; // this is the home page
		}

		$inc++;
		$sub_tabs['wiki_docs_' . $config['code']] = array(
			'title'			=> $config['title'],
			'link'			=> 'index.php?module=wiki-docs&amp;page=' . $config['code'],
			'description'	=> $config['desc']
			);
	}
}

$table = new Table;

if(isset($mybb->input['page']) && $mybb->input['page'] != 'credits') {
	$code = strval($mybb->input['page']);
	$suffix = '_' . $code;

	$content = file_get_contents(MYBB_ROOT . 'inc/plugins/wiki/docs/' . $code . ".md");

	$regex = '#<meta>(.*?)</meta>#';
	preg_match_all($regex, $content, $matches);

	$content = str_replace($matches[1][0], '', $content);

	$cfg = explode('|', $matches[1][0]);
	$config = array();

	foreach($cfg as $key => $val)
	{
		$arr = explode(':', $val);
		$config[$arr[0]] = $arr[1];
	}

	$table->construct_cell(Markdown($content));
	$table->construct_row();

	$tableTitle = $config['title'];
}
else if(isset($mybb->input['page']) && $mybb->input['page'] == 'credits')
{
	// Credits page
	$suffix = '_3';

	$credits = array(
		"euantor" => array(
			"site"		=>	"http://euantor.com",
			"reason"	=>	"General pieces of code"),
		"Pirata Nervo" => array(
			"site"		=>	"http://forums.mybb-plugins.com",
			"reason"	=>	"Base code for the Admin CP Module"),
		"Michel Fortin" => array(
			"site"		=>	"http://michelf.ca/home",
			"reason"	=>	"Markdown Parser"),
		"pavemen" => array(
			"site"		=>	"http://pavementsucks.com",
			"reason"	=>	"WOL Code"),
		"King Louis (Jones)" => array(
			"site"		=>	"http://jonesboard.de",
			"reason"	=>	"General advice and pieces of code"),
		);

	foreach($credits as $person => $data)
	{
		$table->construct_cell("<a href=\"{$data['site']}\">{$person}</a>", array('class' => 'align_center', 'width' => '50%'));
		$table->construct_cell($data['reason'], array('class' => 'align_center', 'width' => '50%'));
		$table->construct_row();
	}

	$tableTitle = $lang->wiki_docs;
}
else {
	$suffix = '';

	$content = file_get_contents(MYBB_ROOT . 'inc/plugins/wiki/docs/home.md');

	$regex = '#<meta>(.*?)</meta>#';
	preg_match_all($regex, $content, $matches);

	$content = str_replace($matches[1][0], '', $content);

	$cfg = explode('|', $matches[1][0]);
	$config = array();

	foreach($cfg as $key => $val)
	{
		$arr = explode(':', $val);
		$config[$arr[0]] = $arr[1];
	}

	$table->construct_cell(Markdown($content));
	$table->construct_row();

	$tableTitle = $lang->wiki_docs;
}

$sub_tabs['wiki_docs_3'] = array(
	'title' 		=>	$lang->wiki_credits,
	'link'			=>	'index.php?module=wiki-docs&amp;page=credits',
	'description'	=>	$lang->wiki_credits_desc
);

$page->output_nav_tabs($sub_tabs, 'wiki_docs' . $suffix);

if(isset($tableTitle)) {
	$table->output($tableTitle);
}

$page->output_footer();

?>