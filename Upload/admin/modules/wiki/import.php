<?php

// Disallow direct access to this file for security reasons
if(!defined("IN_MYBB"))
{
	die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

$lang->load('wiki');

$page->add_breadcrumb_item($lang->wiki_import, 'index.php?module=wiki-import');

$page->output_header($lang->wiki_import);

$sub_tabs['wiki_import'] = array(
	'title'			=> $lang->wiki_import,
	'link'			=> 'index.php?module=wiki-import',
	'description'	=> $lang->wiki_import_description
);

$page->output_nav_tabs($sub_tabs, 'wiki_import');

if($mybb->request_method != "post")
{
	$form = new Form('', 'POST', '', 1, '', false, '');
	$form_container = new FormContainer($lang->wiki_import);
	$form_container->output_row($lang->wiki_xml_file, $lang->wiki_xml_file_desc, $form->generate_file_upload_box('xml_file', array('id' => 'xml_file')));
	$form_container->end();
	$buttons = array();
	$buttons[] = $form->generate_submit_button($lang->wiki_commit);
	$form->output_submit_wrapper($buttons);
	$form->end();
}
else
{
	$info = pathinfo($_FILES['xml_file']['name']);
	$extension = $info['extension'];

	if($extension != 'xml')
	{
		flash_message($lang->wiki_invalid_file, 'error');
		admin_redirect('index.php?module=wiki-import');
	}
	else
	{
		if($_FILES['xml_file']['error'] == UPLOAD_ERR_OK && is_uploaded_file($_FILES['xml_file']['tmp_name']))
		{
			$string = file_get_contents($_FILES['xml_file']['tmp_name']);
			$xml = new SimpleXMLElement($string);

			foreach($xml->article as $article)
			{
				$query = "INSERT INTO " . TABLE_PREFIX . "wiki(`authors`,`title`,`content`,`protected`,`lastauthor`,`lastauthorid`,`category`,`original`) VALUES('" . $db->escape_string($article->authors) . "','" . $db->escape_string($article->title) . "','" . $db->escape_string($article->content) . "','" . $db->escape_string($article->protected) . "','" . $db->escape_string($article->lastauthor) . "','" . $db->escape_string($article->lastauthorid) . "','" . $db->escape_string($article->category) . "','" . $db->escape_string($article->original) . "')";
				$sql = $db->write_query($query);
				if($db->error_number() > 0)
				{
					flash_message($lang->wiki_import_error . " Error Code 1", 'error');
					admin_redirect('index.php?module=wiki-import');
				}
			}

			flash_message($lang->wiki_import_success, 'success');
			admin_redirect('index.php?module=wiki-import');
		}
		else
		{
			flash_message($lang->wiki_import_error  . " Error Code 3", 'error');
			admin_redirect('index.php?module=wiki-import');
		}
	}
}

$page->output_footer();

?>