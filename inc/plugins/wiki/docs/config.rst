Configuration
===============

Extensibility
===================

MyBB Wiki is designed with extensibility in mind. Every aspect of it has hooks for other plugins to extend it, it has its own templates, and is localised in various languages. You can create plugins for it like any other MyBB Plugin - I recommend using Euan Torano's `Hook Finder <https://github.com/euantorano/MyBB-Hook-Finder>`_ as a guide to what hooks are available.

Config
=======

The Wiki configuration is located in the Admin CP, in the *Wiki* module. What follows is a list of all the sub modules, and their functions.

All Articles
-------------

*All Articles* gives you a list of every article that has been created on the Wiki, and allows you to delete, or protect them. You may also create new articles here.

Categories
-----------

Articles on the Wiki are divided into Categories, which act like boards for the wiki articles. This module allows you to edit, create, and delete existing modules.

**Warning!** At the moment, deleting categories that have articles in them is not supported. Although there is no mechanism *preventing* you from doing so, the articles are not reassigned a new category, and this can cause issues. This will be remedied in a version coming out soon.

Article Import
---------------

This allows you to import wiki articles from other wikis / a backup of this wiki, *provided* that the XML format follows the MyBB Wiki specification (which is very simple).

Permissions
------------

Permissions allows you to configure the actions users can take. By default, every member group has the permission to View, Create, and Edit articles.

Settings
---------

Power Switch
	The global switch for the wiki - controls whether it is on or off.

Parse Smilies?
	Allows you to set whether smilies should be transformed - if :) should be turned into the png image, for example.

Use the MyBB Parser?
	Allows you to specify whether MyCode should be parsed - if [b]test[/b] should render as **test**.

Use Markdown Parser?
	Allows you to specify whether the Markdown parser should be used - if \**test** should render as **test**. Note that enabling both this and the previous option causes MyCode to be parsed before Markdown.

Clickable MyCode Editor
	Enables you to enable the default MyBB Post editor.

Parse HTML?
	Enables MyBB's HTML Parser. Recommended setting is **No**.

Exporting Enabled?
	Allows users with the "Export" permission to export things. This redundancy is because the Export command basically dumps the entire Wiki database table into an XML file, so it should be used by trusted users only.

View in Plugin Manager
----------------------------

Opens Config > Plugins.

Templates
-----------

Enables you to define replacable strings in articles. For instance, if you have "Search String" set to "foo" and "Replacement" set to "bar", every instance of "{{foo}}" in articles is replaced with "bar". The replacement value is NOT SANITIZED, so you can enter arbitrary HTML into it. This is a pretty simple system at the moment, and will hopefully be updated later to add more exciting possibilities.

Upgrades
---------

Enables you to run upgraders, that upgrade the Wiki to new versions. Where possible, all upgrade scripts will be run automatically. MyBB Wiki does not, however, save backups of wiki articles for you. This must be done with the Import/Export function inside your Admin CP's Wiki module and on the article list before an upgrade or reinstall. As much as possible is saved in this process.

Upgrades also reset your settings and categories. Exported articles have the category in them and it has not (yet) been tested how the articles react with a non-existent category, but it should be only a minor issue.

Documentation
--------------

Redirects you here ;-)