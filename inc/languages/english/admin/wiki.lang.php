<?php

/* module_meta stuff */
$l['wiki'] = "Wiki";
$l['wiki_can_manage_articles'] = "Can manage Wiki Articles";
$l['wiki_can_manage_categories'] = "Can manage Wiki Categories";
$l['wiki_can_manage_imports'] = "Can import Wiki Articles";
$l['wiki_can_manage_perms'] = "Can manage Wiki Permissions";
$l['wiki_can_manage_settings'] = "Can manage Wiki settings";
$l['wiki_can_manage_docs'] = "Can read the Wiki Documentation";
$l['wiki_can_manage_templates'] = "Can manage wiki templates";
$l['wiki_can_upgrade'] = "Can run the Upgraders";

$l['wiki_nav_home'] = "Home";
$l['wiki_nav_articles'] = "All Articles";
$l['wiki_nav_docs'] = "Documentation";
$l['wiki_nav_updates'] = "Updates";
$l['wiki_nav_categories'] = "Categories";
$l['wiki_nav_import'] = "Article Import";
$l['wiki_nav_perms'] = "Permissions";
$l['wiki_nav_settings'] = "Settings";
$l['wiki_nav_plugin'] = "View in Plugin Manager";
$l['wiki_nav_templates'] = "Templates";
$l['wiki_nav_upgrades'] = "Upgrades";
/* articles.php stuff */
$l['wiki_articles'] = "Wiki Articles";
$l['wiki_articles_description'] = "A listing of all the wiki articles on your forum.";
$l['wiki_articles_name'] = "Article name";
$l['wiki_articles_options'] = "Actions";
$l['wiki_no_articles'] = "No Articles to speak of!";
$l['wiki_delete'] = "Delete";
$l['wiki_deleted_success'] = "Article Successfully Deleted";
$l['wiki_protected_success'] = "Article Successfully Protected";
$l['wiki_protect'] = "Protect";
$l['wiki_unprotect'] = "Unprotect";
$l['wiki_unprotected_success'] = "Article Successfully Unprotected";
$l['wiki_articles_new'] = "New Article";
$l['wiki_articles_new_description'] = "Create a new article in your wiki. Make it long, short, anywhere in between.";
$l['wiki_new_title'] = "Title";
$l['wiki_new_title_desc'] = "Enter a name for this article.";
$l['wiki_new_content'] = "Content";
$l['wiki_new_content_desc'] = "Enter the content of this article.";
$l['wiki_new_protect'] = "Protect Article?";
$l['wiki_new_protect_desc'] = "If yes, the article will be protected when created.";
$l['wiki_new_cat'] = "Category";
$l['wiki_new_cat_desc'] = "Which category should this article be under?";
$l['wiki_new_done'] = "The article was successfully created.";
/* docs.php stuff */
$l['wiki_docs'] = "Documentation";
$l['wiki_docs_description'] = "How to manage and run the MyBB Wiki on your forum.";
$l['wiki_intro_body'] = "<h1>Welcome to MyBB Wiki!</h1>Thank you for downloading and installing it, it means a lot to me, as it is my first plugin of substance.";
$l['wiki_intro_par1'] = "<h2>About</h2>The Wiki Plugins I have seen are usually outdated and/or buggy. That's why I wanted to build this, a simple, extensible wiki with a solid and reliable backend. MyBB Wiki is my view at a wiki. It is very simple without the bells and whistles. It sports MyCode, Article Protection and a dedicated Admin CP Module, from which you can manage articles posted, add categories and read Documentation.<br /><br />The Wiki is located at thiswebsite.com/wiki.php - where thiswebsite.com is your forum's url.";
$l['wiki_intro_par2'] = "<h2>Updates</h2>For news about updates, you can check out the <a href=\"https://github.com/Krytic/MyBBWiki\">GitHub</a> repository or check out the <a href=\"http://community.mybb.com/thread-137015.html\">MyBB Community Thread</a>.<br /><br />You are running version {1}.";
$l['wiki_docs_2'] = "Extensibility";
$l['wiki_ext_desc'] = "How to extend the Wiki System with your own plugins.";
$l['wiki_ext_head'] = "<h1>Extensibility</h1>";
$l['wiki_ext_par1'] = "MyBB Wiki has designed with extensibility in mind. That means that any plugin that works on MyBB will also work on your Wiki, no questions asked. All developed plugins must use the correct hooks, however.";
$l['wiki_ext_par2'] = "If you know how to develop a MyBB Plugin, you can already start developing for MyBB Wiki. Just create your standard MyBB plugin and use the special Wiki hooks, which are (usually!) prefixed with wiki_. Place this into your plugins directory as standard, activate, and enjoy.";
$l['wiki_ext_par3'] = "<strong>That's it!</strong><br />In addition to plugins, you can easily customize templates, the style (we use the MyBB standard classes with a few inline styles defined) and the settings, the latter of which will be slowly moved into the dedicated Wiki Module in your Admin CP soon.";
$l['wiki_credits'] = "Credits";
$l['wiki_credits_desc'] = "I didn't do this all by myself. Here you can find the people who helped out along the way (or who I borrowed bits of code off), along with links to their sites.";
$l['wiki_credits_info'] = "<h1>Credits</h1>";
$l['wiki_credits_info'] .= "<a href=\"http://euantor.com\">euantor</a> (General pieces of code)<br />";
$l['wiki_credits_info'] .= "<a href=\"http://forums.mybb-plugins.com\">Pirata Nervo</a> (Base code for the admin module)<br />";
$l['wiki_credits_info'] .= "<a href=\"http://michelf.ca/home/\">Michel Fortin</a> (Markdown Parser)";
/* categories.php stuff */
$l['wiki_categories'] = "Categories";
$l['wiki_categories_description'] = "Manage your categories. A default one, the <em>Meta</em> category is already set.";
$l['wiki_category_name'] = "Category Name";
$l['wiki_category_options'] = "Options";
$l['wiki_cat_delete'] = "Delete";
$l['wiki_cat_edit'] = "Edit";
$l['wiki_cat_deleted_success'] = "Category successfully deleted.";
$l['wiki_cat_edited_success'] = "Category successfully edited.";
$l['wiki_cat_none'] = "No categories specified.";
$l['wiki_categories_edit'] = "Edit Category";
$l['wiki_categories_add'] = "Add a Category";
$l['wiki_categories_add_description'] = "Add extra categories here - one for cats, another for MacBooks, it's your choice.";
$l['wiki_commit'] = "Commit";
$l['wiki_cat_title'] = "Category Title";
$l['wiki_cat_title_desc'] = "What will you be calling this category?";
$l['wiki_cat_desc'] = "Description";
$l['wiki_cat_desc_desc'] = "Enter a short description of this category.";
$l['wiki_cat_success'] = "{1} has been added as a category.";
/* import.php stuff */
$l['wiki_import'] = "Article Import";
$l['wiki_import_description'] = "Import articles that you have exported to an XML file.";
$l['wiki_xml_file'] = "XML Location";
$l['wiki_xml_file_desc'] = "Enter the location of the XML File to be imported.";
$l['wiki_invalid_file'] = "That file was not valid, you must upload an XML file.";
$l['wiki_import_error'] = "An error occurred attempting to import this XML file.";
$l['wiki_import_success'] = "The XML file has successfully been imported.";
/* perms.php stuff */
$l['wiki_perms'] = "Permissions";
$l['wiki_perms_description'] = "Add, remove or grant permissions to users.";
$l['wiki_perm_group'] = "Group";
$l['wiki_perm_can_view'] = "View";
$l['wiki_perm_can_create'] = "Create";
$l['wiki_perm_can_edit'] = "Edit";
$l['wiki_perm_can_protect'] = "Protect";
$l['wiki_perm_can_export'] = "Export";
$l['wiki_perm_update_yes'] = "The permissions have successfully been updated.";
/* settings.php stuff */
$l['wiki_settings'] = "Wiki Settings";
$l['wiki_settings_alt'] = "Settings";
$l['wiki_settings_description'] = "Configure the Wiki. Change it to fit your needs exactly.";
$l['wiki_setting_title'] = "Title";
$l['wiki_setting_givevalue'] = "Value";
$l['wiki_settings_success'] = "Settings successfully updated.";
/* templates.php stuff */
$l['wiki_templates_name'] = "Name";
$l['wiki_templates_search'] = "Search Pattern";
$l['wiki_templates_replace'] = "Replacement";
$l['wiki_no_templates'] = "There are no templates defined on your wiki.";
$l['wiki_templates'] = "Templates";
$l['wiki_templates_new'] = "New Template";
$l['wiki_templates_description'] = "Here you can define templates to use in your wiki. Templates are strings that transform into more complex designs on parsing; for instance, {{foo}} might be transformed into &lt;strong&gt;bar&lt;/strong&gt;.";
$l['wiki_templates_new_description'] = "Create a new template on your wiki.";
$l['wiki_new_template_title'] = "Template Title";
$l['wiki_new_template_title_desc'] = "Internal use only, not shown to non-admins.";
$l['wiki_new_template_search'] = "Search String";
$l['wiki_new_template_search_desc'] = "The {{ and }} is added automatically - you do not need to add it here. ie a search pattern of \"foo\" would need to be written as {{foo}} to be parsed. A search pattern of \"{{foo}}\" would need to be written as {{{{foo}}}} to be parsed.";
$l['wiki_new_template_replace'] = "Replacement";
$l['wiki_new_template_replace_desc'] = "This value is NOT SANITIZED so that you can enter HTML etc here.";
$l['wiki_new_template_done'] = "Your new template has been created successfully.";
/* upgrades.php stuff */
$l['wiki_upgrades'] = "Upgrades";
$l['wiki_upgrades_description'] = "Run the various upgraders to the Wiki";
$l['wiki_upgrades_to'] = "To";
$l['wiki_upgrades_run'] = "Run";
$l['wiki_upgrade_desc'] = "Description";
$l['wiki_upgrade_yes'] = "The upgrade has successfully been run";
$l['wiki_upgrade_no'] = "The upgrade failed: {1}";
$l['wiki_upgrades_none'] = "There are no upgrades available";
/* errors */
$l['wiki_error'] = "An error occured while trying to process your request, please try again later.";
$l['wiki_error_pc'] = "Post Codes did not match!";
$l['wiki_no_id'] = "You must provide an ID of an article to edit!";
/* misc */
$l['wiki_creator'] = "Created by Adamas.";
$l['wiki_wip'] = "<h1>WIP</h1>";
$l['wiki_description'] = "Thank you for downloading the MyBB Wiki plugin.";
$l['wiki_home'] = "Welcome to MyBB Wiki";
$l['wiki_new_version'] = "New version ({1}) available!";
$l['wiki_new_release_download'] = "Download on GitHub";
$l['wiki_welcome'] = "<h1>Wiki Admin Control Panel</h1>";
$l['wiki_total_articles'] = "<h2>{1} article{2}</h2>";
$l['wiki_total_cats'] = "<h2>{1} categor{2}</h2>";
$l['wiki_updates'] = "<strong>MyBB Wiki is fully up to date</strong>";
$l['wiki_updates_header'] = "Updates";
$l['wiki_updater_required'] = "Upgrader Required";
$l['wiki_updater_not_required'] = "Upgrader not Required";
$l['wiki_check_for_updates'] = "<h2><a href=\"index.php?module=wiki&action=version_check\">Check for updates</a></h2>";
$l['wiki_version'] = "<h2>Version {1}</h2>";
$l['wiki_welcome_message'] = "Welcome to MyBB Wiki!";

?>