<?php

// Disallow direct access to this file for security reasons
if(!defined("IN_MYBB"))
{
	die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

$lang->load('wiki');

$page->add_breadcrumb_item($lang->wiki_articles, 'index.php?module=wiki-articles');

$page->output_header($lang->wiki_articles);

$sub_tabs['wiki_articles'] = array(
	'title'			=> $lang->wiki_articles,
	'link'			=> 'index.php?module=wiki-articles',
	'description'	=> $lang->wiki_articles_description
);
$sub_tabs['wiki_articles_new'] = array(
	'title'			=> $lang->wiki_articles_new,
	'link'			=> 'index.php?module=wiki-articles&action=new',
	'description'	=> $lang->wiki_articles_new_description
);

if (!$mybb->input['action'])
{
	$page->output_nav_tabs($sub_tabs, 'wiki_articles');

	$table = new Table;
	$table->construct_header($lang->wiki_articles_name, array('width' => '70%'));
	$table->construct_header($lang->wiki_articles_options, array('width' => '30%', 'class' => 'align_center', 'colspan' => '2'));

	$articles = $db->write_query("SELECT * FROM `" . TABLE_PREFIX . "wiki`");

	if(!empty($articles))
	{
		if($db->num_rows($articles) != 0)
		{
			while($article = $db->fetch_array($articles))
			{
				$table->construct_cell($article['title'], array('class' => 'align_center'));

				$table->construct_cell("<a href=\"index.php?module=wiki-articles&amp;action=delete&amp;id={$article['id']}&amp;my_post_key={$mybb->post_code}&amp;name={$article['title']}\">{$lang->delete}</a>", array('class' => 'align_center'));
				if(!$article['protected'])
				{
					$table->construct_cell("<a href=\"index.php?module=wiki-articles&amp;action=protect&amp;id={$article['id']}&amp;my_post_key={$mybb->post_code}&amp;name={$article['title']}\">{$lang->protect}</a>", array('class' => 'align_center'));
				}
				else
				{
					$table->construct_cell("<a href=\"index.php?module=wiki-articles&amp;action=unprotect&amp;id={$article['id']}&amp;my_post_key={$mybb->post_code}&amp;name={$article['title']}\">{$lang->unprotect}</a>", array('class' => 'align_center'));
				}

				$table->construct_row();
			}
		}
		else
		{
			$table->construct_cell($lang->wiki_no_articles, array('colspan' => 2, 'class' => 'align_center'));
			$table->construct_row();
		}
	}
	else
	{
		$table->construct_cell($lang->wiki_no_articles, array('colspan' => 2, 'class' => 'align_center'));
		$table->construct_row();
	}

	$table->output($lang->wiki_articles);
}
elseif($mybb->input['action'] == 'delete')
{
	if(!isset($mybb->input['my_post_key']) || $mybb->post_code != $mybb->input['my_post_key'])
	{
		$mybb->request_method = "get";
		flash_message($lang->wiki_error_pc, 'error');
		admin_redirect("index.php?module=wiki-articles");
	}
	else
	{
		$id = $db->escape_string($mybb->input['id']);

		$articles = $cache->read("wiki_articles");
		unset($articles[$id]);
		 // MARKER
		$cache->update("wiki_articles", $articles);

		$query = $db->write_query("DELETE FROM `" . TABLE_PREFIX . "wiki` WHERE `id`='" . $id . "'");
		if(!$query)
		{
			flash_message($lang->wiki_error, 'error');
			admin_redirect("index.php?module=wiki-articles");
		}
		else
		{
			log_admin_action($mybb->input['name']);
			flash_message($lang->wiki_deleted_success, 'success');
			admin_redirect("index.php?module=wiki-articles");
		}
	}
}
elseif($mybb->input['action'] == 'protect')
{
	if(!isset($mybb->input['my_post_key']) || $mybb->post_code != $mybb->input['my_post_key'])
	{
		$mybb->request_method = "get";
		flash_message($lang->wiki_error_pc, 'error');
		admin_redirect("index.php?module=wiki-articles");
	}
	else
	{
		$sql = $db->write_query("UPDATE `" . TABLE_PREFIX . "wiki` SET `protected`='1' WHERE `id`='" . intval($mybb->input['id']) . "'");
		if(!$sql)
		{
			flash_message($lang->wiki_error, 'error');
			admin_redirect("index.php?module=wiki-articles");
		}
		else
		{
			log_admin_action($mybb->input['name']);
			flash_message($lang->wiki_protected_success, 'success');
			admin_redirect("index.php?module=wiki-articles");
		}
	}
}
elseif($mybb->input['action'] == 'unprotect')
{
	if(!isset($mybb->input['my_post_key']) || $mybb->post_code != $mybb->input['my_post_key'])
	{
		$mybb->request_method = "get";
		flash_message($lang->wiki_error_pc, 'error');
		admin_redirect("index.php?module=wiki-articles");
	}
	else
	{
		$sql = $db->write_query("UPDATE `" . TABLE_PREFIX . "wiki` SET `protected`='0' WHERE `id`='" . intval($mybb->input['id']) . "'");
		if(!$sql)
		{
			flash_message($lang->wiki_error, 'error');
			admin_redirect("index.php?module=wiki-articles");
		}
		else
		{
			log_admin_action($mybb->input['name']);
			flash_message($lang->wiki_unprotected_success, 'success');
			admin_redirect("index.php?module=wiki-articles");
		}
	}
}
elseif($mybb->input['action'] == 'new')
{
	if($mybb->request_method == "post")
	{
		$category = $mybb->input['category'];

		$protected = 0;
		if($mybb->input['protected'])
		{
			$protected = 1;
		}

		$arr = array(
			'authors' => intval($mybb->user['uid']),
			'title' => $db->escape_string($mybb->input['title']),
			'content' => $db->escape_string($mybb->input['message']),
			'original' => $db->escape_string($mybb->input['message']),
			'protected' => $protected,
			'lastauthor' => $db->escape_string($mybb->user['username']),
			'lastauthorid' => $db->escape_string($mybb->user['uid']),
			'category' => (int)$category
			);

		$db->insert_query('wiki', $arr);

		$updates = $cache->read('wiki_articles');
		$updates[$db->insert_id()] = $title;

		$cache->update('wiki_articles', $updates);

		flash_message($lang->wiki_new_done, 'success');
		admin_redirect('index.php?module=wiki-articles');
	}

	$page->output_nav_tabs($sub_tabs, 'wiki_articles_new');

	$form = new Form('', 'post');
	$form_container = new FormContainer($lang->wiki_articles_new);

	$form_container->output_row($lang->wiki_new_title, $lang->wiki_new_title_desc, $form->generate_text_box('title', '', array('id' => 'title')));
	$form_container->output_row($lang->wiki_new_content, $lang->wiki_new_content_desc, $form->generate_text_area('message', '', array('id' => 'message', 'style' => 'width: 100%; height: 300px;')));
	$form_container->output_row($lang->wiki_new_protect, $lang->wiki_new_protect_desc, $form->generate_yes_no_radio('protected', '1', true));

	$query = $db->simple_select('wiki_categories', '*');
	$options = array();
	while($row = $db->fetch_array($query))
	{
		$options[$row['cid']] = $row['title'];
	}

	$form_container->output_row($lang->wiki_new_cat, $lang->wiki_new_cat_desc, $form->generate_select_box('category', $options, '', array('id' => 'category', 'size' => '1')));

	$form_container->end();
	$buttons = array();
	$buttons[] = $form->generate_submit_button($lang->wiki_commit);
	$form->output_submit_wrapper($buttons);
	$form->end();
}

$page->output_footer();

?>