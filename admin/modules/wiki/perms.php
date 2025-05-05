<?php

// Disallow direct access to this file for security reasons
if(!defined("IN_MYBB"))
{
	die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

$lang->load('wiki');

$page->add_breadcrumb_item($lang->wiki_perms, 'index.php?module=wiki-perms');

check_admin_permissions(array("module" => 'wiki',
                              "action" => 'permissions'));

$page->output_header($lang->wiki_perms);

$sub_tabs['wiki_perms'] = array(
	'title'			=> $lang->wiki_perms,
	'link'			=> 'index.php?module=wiki-perms',
	'description'	=> $lang->wiki_perms_description
);

$page->output_nav_tabs($sub_tabs, 'wiki_perms');

if($mybb->request_method == "post")
{
	$cache_array = array();

	$query = $db->write_query("SELECT * FROM `" . TABLE_PREFIX . "usergroups`");
	$cache_arr = array();

	while($group = $db->fetch_array($query))
	{
		if(!isset($mybb->input['perm'][$group['gid']]))
		{
			$mybb->input['perm'][$group['gid']] = array(
				'can_view'		=> 1,		// I don't know why it's expecting 1 instead of zero to indicate no permission
				'can_create'	=> 1,		// but it works, so let's go with it.
				'can_edit'		=> 1,
				'can_protect'	=> 1,
				'can_export'	=> 1
				);
		}
	}

	foreach($mybb->input['perm'] as $gid => $perms)
	{
		$row = array(
			"can_view"		=>	(in_array('can_view', $perms) ? 1 : 0),
			"can_create"	=>	(in_array('can_create', $perms) ? 1 : 0),
			"can_edit"		=>	(in_array('can_edit', $perms) ? 1 : 0),
			"can_protect"	=>	(in_array('can_protect', $perms) ? 1 : 0),
			"can_export"	=>	(in_array('can_export', $perms) ? 1 : 0)
			);

		$cache_array["gid_{$gid}"] = array(
			"can_view"		=>	(in_array('can_view', $perms) ? 1 : 0),
			"can_create"	=>	(in_array('can_create', $perms) ? 1 : 0),
			"can_edit"		=>	(in_array('can_edit', $perms) ? 1 : 0),
			"can_protect"	=>	(in_array('can_protect', $perms) ? 1 : 0),
			"can_export"	=>	(in_array('can_export', $perms) ? 1 : 0)
			);

		$gid = (int) $gid;
		$setstr = "";
		foreach($row as $k => $v) {
			$setstr .= "`{$k}`='{$v}', ";
		}
		$setstr = rtrim($setstr, ', ');
		$db->write_query(sprintf("UPDATE `%swiki_perms` SET {$setstr} WHERE `gid`='{$gid}'", TABLE_PREFIX));
	}

	$cache->update('wiki_permissions', $cache_array);

	flash_message($lang->wiki_perm_update_yes, 'success');
	admin_redirect('index.php?module=wiki-perms');
}

$form = new Form('', 'post');

$table = new Table;
$table->construct_header($lang->wiki_perm_group, array('class' => 'align_center'));
$table->construct_header($lang->wiki_perm_can_view, array('class' => 'align_center'));
$table->construct_header($lang->wiki_perm_can_create, array('class' => 'align_center'));
$table->construct_header($lang->wiki_perm_can_edit, array('class' => 'align_center'));
$table->construct_header($lang->wiki_perm_can_protect, array('class' => 'align_center'));
$table->construct_header($lang->wiki_perm_can_export, array('class' => 'align_center'));

$query = $db->write_query("SELECT * FROM `" . TABLE_PREFIX . "wiki_perms`");
$query2 = $db->write_query("SELECT * FROM `" . TABLE_PREFIX . "usergroups`");

while($row = $db->fetch_array($query2))
{
	$row_group[$row['gid']] = $row['title'];
}

while($group = $db->fetch_array($query))
{
	$table->construct_cell($row_group[$group['gid']]);
	$table->construct_cell(wiki_build_permission_checkbox($group['gid'], 'can_view', $group['can_view']), array('class' => 'align_center'));
	$table->construct_cell(wiki_build_permission_checkbox($group['gid'], 'can_create', $group['can_create']), array('class' => 'align_center'));
	$table->construct_cell(wiki_build_permission_checkbox($group['gid'], 'can_edit', $group['can_edit']), array('class' => 'align_center'));
	$table->construct_cell(wiki_build_permission_checkbox($group['gid'], 'can_protect', $group['can_protect']), array('class' => 'align_center'));
	$table->construct_cell(wiki_build_permission_checkbox($group['gid'], 'can_export', $group['can_export']), array('class' => 'align_center'));

	$table->construct_row();
}

$table->output($lang->wiki_perms);

$buttons[] = $form->generate_submit_button($lang->wiki_commit);
$form->output_submit_wrapper($buttons);
$form->end();

$page->output_footer();


// From King Louis/Jones' wiki plugin. :P
function wiki_build_permission_checkbox($gid, $field, $bool)
{
	return '<input type="checkbox" name="perm['.$gid.'][]" value="'.$field.'"'.($bool ? ' checked="checked"' : '').' />';
}

?>