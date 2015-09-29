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

$sub_tabs['wiki_docs_2'] = array(
	'title'			=> $lang->wiki_docs_2,
	'link'			=> 'index.php?module=wiki-docs&amp;page=2',
	'description'	=> $lang->wiki_ext_desc
);

$sub_tabs['wiki_docs_3'] = array(
	'title'			=> $lang->wiki_credits,
	'link'			=> 'index.php?module=wiki-docs&amp;page=3',
	'description'	=> $lang->wiki_credits_desc
);

$table = new Table;
if(!$mybb->input['page'])
{
	// Main page
	$page->output_nav_tabs($sub_tabs, 'wiki_docs');
	$table->construct_cell($lang->wiki_intro_body, array('colspan' => 2, 'class' => 'align_center'));
	$table->construct_row();
	$table->construct_cell($lang->wiki_intro_par1, array('class' => 'align_center', 'width' => '50%'));
	$table->construct_cell($lang->sprintf($lang->wiki_intro_par2, WIKI_VERSION), array('class' => 'align_center', 'width' => '50%'));
	$table->construct_row();
	$table->output($lang->wiki_docs);
}
elseif($mybb->input['page'] == 2)
{
	// Extensibility page
	$page->output_nav_tabs($sub_tabs, 'wiki_docs_2');
	$table->construct_cell($lang->wiki_ext_head, array('class' => 'align_center'));
	$table->construct_row();
	$table->construct_cell($lang->wiki_ext_par1 . "<br /><br />" . $lang->wiki_ext_par2 . "<br /><br />" . $lang->wiki_ext_par3);
	$table->construct_row();
	$table->output($lang->wiki_docs);
}
elseif($mybb->input['page'] == 3)
{
	// Credits page
	$page->output_nav_tabs($sub_tabs, 'wiki_docs_3');

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

	$table->output($lang->wiki_docs);
}

$page->output_footer();

?>