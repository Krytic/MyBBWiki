<?php

// Disallow direct access to this file for security reasons
if(!defined("IN_MYBB"))
{
	die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

$lang->load('wiki');

$page->add_breadcrumb_item($lang->wiki_categories, 'index.php?module=wiki-categories');

$page->output_header($lang->wiki_categories);

$sub_tabs['wiki_categories'] = array(
	'title'			=> $lang->wiki_categories,
	'link'			=> 'index.php?module=wiki-categories',
	'description'	=> $lang->wiki_categories_description
	);

$sub_tabs['wiki_categories_add'] = array(
	'title'			=> $lang->wiki_categories_add,
	'link'			=> 'index.php?module=wiki-categories&action=add',
	'description'	=> $lang->wiki_categories_add_description
	);

$table = new Table;

if(!$mybb->input['action'])
{
	$page->output_nav_tabs($sub_tabs, 'wiki_categories');

	$table->construct_header($lang->wiki_category_name, array('width' => '70%', 'class' => 'align_center'));
	$table->construct_header($lang->wiki_category_options, array('width' => '30%', 'class' => 'align_center', 'colspan' => 2));

	$categories = $db->write_query("SELECT * FROM `" . TABLE_PREFIX . "wiki_categories`");

	if($db->num_rows($categories) > 0)
	{
		while($category = $db->fetch_array($categories))
		{
			$options_construct_edit = "<a href=\"index.php?module=wiki-categories&amp;action=edit&amp;id={$category['cid']}&amp;my_post_key={$mybb->post_code}&amp;name={$category['title']}\">{$lang->wiki_cat_edit}</a>";
			$options_construct = "<a href=\"index.php?module=wiki-categories&amp;action=delete&amp;id={$category['cid']}&amp;my_post_key={$mybb->post_code}&amp;name={$category['title']}\">{$lang->wiki_cat_delete}</a>";

			$table->construct_cell($category['title'], array('class' => 'align_center'));
			$table->construct_cell($options_construct_edit, array('class' => 'align_center'));
			$table->construct_cell($options_construct, array('class' => 'align_center'));
			$table->construct_row();
		}
	}
	else
	{
		$table->construct_cell($lang->wiki_cat_none, array('class' => 'align_center', 'colspan' => '2'));
		$table->construct_row();
	}

	$table->output($lang->wiki_categories);
}
elseif($mybb->input['action'] == 'delete')
{
	if(!isset($mybb->input['my_post_key']) || $mybb->post_code != $mybb->input['my_post_key'])
	{
		$mybb->request_method = "get";
		flash_message($lang->wiki_error_pc, 'error');
		admin_redirect("index.php?module=wiki-categories");
	}
	else
	{
		$id = $db->escape_string($mybb->input['id']);
		$query = $db->write_query("DELETE FROM `" . TABLE_PREFIX . "wiki_categories` WHERE `cid`='" . $id . "'");
		if(!$query)
		{
			flash_message($lang->wiki_error, 'error');
			admin_redirect("index.php?module=wiki-categories");
		}
		else
		{
			log_admin_action($mybb->input['name']);
			flash_message($lang->wiki_cat_deleted_success, 'success');
			admin_redirect("index.php?module=wiki-categories");
		}
	}
}
elseif($mybb->input['action'] == 'add')
{
	$page->output_nav_tabs($sub_tabs, 'wiki_categories_add');

	if($mybb->request_method != 'post')
	{
		$form = new Form('', 'POST', '', 0, '', false, '');
		$form_container = new FormContainer($lang->wiki_categories_add);
		$form_container->output_row($lang->wiki_cat_title, $lang->wiki_cat_title_desc, $form->generate_text_box('cat_title', "", array('id' => 'title')), 'title');
		$form_container->output_row($lang->wiki_cat_desc, $lang->wiki_cat_desc_desc, $form->generate_text_box('cat_desc', "", array('id' => 'desc')), 'desc');
		$form_container->end();
		$buttons = array();
		$buttons[] = $form->generate_submit_button($lang->wiki_commit);
		$form->output_submit_wrapper($buttons);
		$form->end();
	}
	else
	{
		$name = $db->escape_string($mybb->input['cat_title']);
		$desc = $db->escape_string($mybb->input['cat_desc']);

		$query = $db->write_query("INSERT INTO " . TABLE_PREFIX . "wiki_categories(title, description) VALUES('{$name}', '{$desc}')");

		if(!$query)
		{
			flash_message($lang->wiki_error, 'error');
			admin_redirect("index.php?module=wiki-categories");
		}
		else
		{
			$message = $lang->sprintf($lang->wiki_cat_success, $mybb->input['cat_title']);
			flash_message($message, 'success');
			admin_redirect("index.php?module=wiki-categories");
		}
	}
}
elseif($mybb->input['action'] == 'edit')
{
	if(!isset($mybb->input['id'])) {
		$mybb->request_method = "get";
		flash_message($lang->wiki_no_id, 'error');
		admin_redirect("index.php?module=wiki-categories");
	}

	$id = intval($mybb->input['id']);

	if($mybb->request_method != 'post')
	{
		$query = $db->write_query(sprintf("SELECT * FROM `%swiki_categories` WHERE `cid`='" . $id . "'", TABLE_PREFIX));
		$category = $db->fetch_array($query);

		$form = new Form('', 'POST', '', 0, '', false, '');
		$form_container = new FormContainer($lang->wiki_categories_edit);
		$form_container->output_row($lang->wiki_cat_title, $lang->wiki_cat_title_desc, $form->generate_text_box('cat_title', $category['title'], array('id' => 'title')), 'title');
		$form_container->output_row($lang->wiki_cat_desc, $lang->wiki_cat_desc_desc, $form->generate_text_box('cat_desc', $category['description'], array('id' => 'desc')), 'desc');
		$form_container->end();
		$buttons = array();
		$buttons[] = $form->generate_submit_button($lang->wiki_commit);
		$form->output_submit_wrapper($buttons);
		$form->end();
	}
	else
	{
		if(!isset($mybb->input['my_post_key']) || $mybb->post_code != $mybb->input['my_post_key'])
		{
			$mybb->request_method = "get";
			flash_message($lang->wiki_error_pc, 'error');
			admin_redirect("index.php?module=wiki-categories");
		}
		else
		{
			$title = $db->escape_string($mybb->input['cat_title']);
			$desc = $db->escape_string($mybb->input['cat_desc']);

			$query = $db->write_query(sprintf("UPDATE `%swiki_categories` SET `title`='{$title}', `description`='{$desc}' WHERE `cid`='" . $id . "'", TABLE_PREFIX));
			if(!$query)
			{
				flash_message($lang->wiki_error, 'error');
				admin_redirect("index.php?module=wiki-categories");
			}
			else
			{
				log_admin_action($mybb->input['name']);
				flash_message($lang->wiki_cat_edited_success, 'success');
				admin_redirect("index.php?module=wiki-categories");
			}
		}
	}
}

$page->output_footer();

?>