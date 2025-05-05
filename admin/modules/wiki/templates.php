<?php

// Disallow direct access to this file for security reasons
if(!defined("IN_MYBB"))
{
	die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

$lang->load('wiki');

$page->add_breadcrumb_item($lang->wiki_templates, 'index.php?module=wiki-templates');

check_admin_permissions(array("module" => 'wiki',
                              "action" => 'templates'));

$page->output_header($lang->wiki_templates);

$sub_tabs['wiki_templates'] = array(
	'title'			=> $lang->wiki_templates,
	'link'			=> 'index.php?module=wiki-templates',
	'description'	=> $lang->wiki_templates_description
);
$sub_tabs['wiki_templates_new'] = array(
	'title'			=> $lang->wiki_templates_new,
	'link'			=> 'index.php?module=wiki-templates&action=new',
	'description'	=> $lang->wiki_templates_new_description
);

if (!$mybb->input['action'])
{
	$page->output_nav_tabs($sub_tabs, 'wiki_templates');

	$table = new Table;
	$table->construct_header($lang->wiki_templates_name, array('width' => '33%', 'class' => 'align_center'));
	$table->construct_header($lang->wiki_templates_search, array('width' => '33%', 'class' => 'align_center'));
	$table->construct_header($lang->wiki_templates_replace, array('width' => '33%', 'class' => 'align_center'));

	$templates = $db->write_query("SELECT * FROM `" . TABLE_PREFIX . "wiki_templates`");

	if(!empty($templates))
	{
		if($db->num_rows($templates) != 0)
		{
			while($template = $db->fetch_array($templates))
			{
				$table->construct_cell($template['name'], array('class' => 'align_center'));

				$table->construct_cell($template['search'], array('class' => 'align_center'));

				$table->construct_cell(htmlspecialchars_uni($template['replace']), array('class' => 'align_center'));

				$table->construct_row();
			}
		}
		else
		{
			$table->construct_cell($lang->wiki_no_templates, array('colspan' => 3, 'class' => 'align_center'));
			$table->construct_row();
		}
	}
	else
	{
		$table->construct_cell($lang->wiki_no_templates, array('colspan' => 3, 'class' => 'align_center'));
		$table->construct_row();
	}

	$table->output($lang->wiki_templates);
}
elseif($mybb->input['action'] == 'new')
{
	if($mybb->request_method == "post")
	{
		$arr = array(
			'name' => $db->escape_string($mybb->input['name']),
			'search' => $db->escape_string($mybb->input['search']),
			'replace' => $db->escape_string($mybb->input['replace']),
			);

		$db->insert_query('wiki_templates', $arr);

		flash_message($lang->wiki_new_template_done, 'success');
		admin_redirect('index.php?module=wiki-templates');
	}

	$page->output_nav_tabs($sub_tabs, 'wiki_templates_new');

	$form = new Form('', 'post');
	$form_container = new FormContainer($lang->wiki_templates_new);

	$form_container->output_row($lang->wiki_new_template_title, $lang->wiki_new_template_title_desc, $form->generate_text_box('name', '', array('id' => 'title')));
	$form_container->output_row($lang->wiki_new_template_search, $lang->wiki_new_template_search_desc, $form->generate_text_box('search', '', array('id' => 'search')));
	$form_container->output_row($lang->wiki_new_template_replace, $lang->wiki_new_template_replace_desc, $form->generate_text_area('replace', '', array('id' => 'replace', 'style' => 'width: 100%; height: 300px;')));

	$form_container->end();
	$buttons = array();
	$buttons[] = $form->generate_submit_button($lang->wiki_commit);
	$form->output_submit_wrapper($buttons);
	$form->end();
}

$page->output_footer();

?>