<?php

// Disallow direct access to this file for security reasons
if(!defined("IN_MYBB"))
{
	die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

$lang->load('wiki');

$page->add_breadcrumb_item($lang->wiki_settings, 'index.php?module=wiki-settings');

check_admin_permissions(array("module" => 'wiki',
                              "action" => 'settings'));

$page->output_header($lang->wiki_settings);

$sub_tabs['wiki_settings'] = array(
	'title'			=> $lang->wiki_settings,
	'link'			=> 'index.php?module=wiki-settings',
	'description'	=> $lang->wiki_settings_description
);

$page->output_nav_tabs($sub_tabs, 'wiki_settings');

if($mybb->request_method == "post")
{
	foreach($mybb->input['setting'] as $name => $value)
	{
		$row = array(
			"value"	=>	$db->escape_string($value)
		);

		$db->update_query('wiki_settings', $row, "name=\"" . $name . "\"");
	}

	flash_message($lang->wiki_settings_success, 'success');
	admin_redirect('index.php?module=wiki-settings');
}

$table = new Table;
$form = new Form('', 'post');

$table->construct_header($lang->wiki_setting_title);
$table->construct_header($lang->wiki_setting_givevalue);

$query = $db->write_query("SELECT * FROM `" . TABLE_PREFIX . "wiki_settings`");

while($row = $db->fetch_array($query))
{
	$table->construct_cell($row['title']);

	$code = $row['optionscode'];

	if($code == 'text')
	{
		$setting_code = $form->generate_text_box('setting[' . $row['name'] . ']', $row['value'], array('id' => $row['name']));
	}
	elseif($code == 'yesno')
	{
		$setting_code = $form->generate_yes_no_radio('setting[' . $row['name'] . ']', $row['value'], true, array('id' => $row['name'].'_yes', 'class' => $row['name']), array('id' => $row['name'].'_no', 'class' => $row['name']));
	}
	elseif($code == 'onoff')
	{
		$setting_code = $form->generate_on_off_radio('setting[' . $row['name'] . ']', $row['value'], true, array('id' => 'on', 'class' => $row['name']), array('id' => 'off', 'class' => $row['name']));
	}
	elseif($code == 'textarea')
	{
		$setting_code = $form->generate_text_area('setting[' . $row['name'] . ']', $row['value'], array('id' => $row['name']));
	}

	$table->construct_cell($setting_code);

	$table->construct_row();
}

$buttons[] = $form->generate_submit_button($lang->wiki_commit);

$table->output($lang->wiki_settings_alt);

$form->output_submit_wrapper($buttons);

$page->output_footer();

?>