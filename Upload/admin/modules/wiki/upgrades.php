<?php

// Disallow direct access to this file for security reasons
if(!defined("IN_MYBB"))
{
	die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

$lang->load('wiki');

$page->add_breadcrumb_item($lang->wiki_upgrades, 'index.php?module=wiki-docs');

$page->output_header($lang->wiki_upgrades);

$sub_tabs['wiki_upgrades'] = array(
	'title'			=> $lang->wiki_upgrades,
	'link'			=> 'index.php?module=wiki-upgrades',
	'description'	=> $lang->wiki_upgrades_description
	);

$page->output_nav_tabs($sub_tabs, 'wiki_upgrades');

$table = new Table;
$table->construct_header($lang->wiki_upgrades_to, array('class' => 'align_center', 'width' => '20%'));
$table->construct_header($lang->wiki_upgrade_desc, array('class' => 'align_center', 'width' => '60%'));
$table->construct_header($lang->wiki_upgrades_run, array('class' => 'align_center', 'width' => '20%'));
$table->construct_row();

$dir = new DirectoryIterator(MYBB_ROOT . 'inc/plugins/wiki/upgraders');

if(isset($mybb->input['upgrade'])) {
	$mybb->input['upgrade'] = strval($mybb->input['upgrade']);
	require_once MYBB_ROOT . 'inc/plugins/wiki/upgraders/' . $mybb->input['upgrade'] . '.php';
	$class = $mybb->input['upgrade'];
	$upgrader = new $class;

	$result = $upgrader->run();

	if($result) {
		flash_message($lang->wiki_upgrade_yes, 'success');
		admin_redirect('index.php?module=wiki-upgrades');
	}

	flash_message($lang->sprintf($lang->wiki_upgrade_no, $upgrader->error()), 'error');
	admin_redirect('index.php?module=wiki-upgrades');
}

$count = 0;

foreach($dir as $file) {
	if(!$file->isDot() && !$file->isDir() && pathinfo($file->getFilename(), PATHINFO_EXTENSION) == 'php') {
		require_once $file->getPathname();
		$class = pathinfo($file->getFilename(), PATHINFO_FILENAME);
		$upgrader = new $class;

		$info = $upgrader->info();

		$table->construct_cell('v' . $info['version'], array('class' => 'align_center'));
		$table->construct_cell($info['desc'], array('class' => 'align_center'));
		$table->construct_cell("<a href=\"index.php?module=wiki-upgrades&amp;upgrade={$class}\">Run</a>", array('class' => 'align_center'));
		$table->construct_row();

		$count++;
	}
}

if($count === 0) {
	$table->construct_cell($lang->wiki_upgrades_none, array('class' => 'align_center', 'colspan' => 3));
	$table->construct_row();
}

$table->output($lang->wiki_upgrades);