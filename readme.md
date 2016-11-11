# MyBB Wiki

MyBB Wiki is a simple, yet powerful wiki for the [MyBB Forum Software](http://www.mybb.com).

## Extensibility

#### Theming

MyBB Wiki contains it's own template set, inserted into your themes. It uses default MyBB classes, with a few inline elements that will be removed in a later release. You can theme it just like you'd theme any other element of MyBB.

#### Hooking

Hooks are inserted in various parts of the plugin, all of them are prefixed with "wiki_" so you can easily identify them if you generate a list using something like Euan's [MyBB Hook Finder](https://github.com/euantorano/MyBB-Hook-Finder).

#### Languages

Here is a complete list of translations available for MyBB Wiki:

> English
> Persian
> Polish

#### Upgrades

Where possible, all upgrade scripts will be run automatically. MyBB Wiki does not, however, save backups of wiki articles for you. This must be done with the Import/Export function inside your Admin CP's Wiki module and on the article list before an upgrade or reinstall. As much as possible is saved in this process.

Upgrades also reset your settings and categories. Exported articles have the category in them and it has not (yet) been tested how the articles react with a non-existent category, but it should be only a minor issue.

## Usage

MyBB Wiki comes with it's own self-contained Markdown Parser, which is from [Michel Fortin](michelf.ca/projects/php-markdown/classic/). If you're not familiar with the syntax, I highly recommend you read the [DaringFireball Guide](daringfireball.net/projects/markdown/syntax).

MyBB Wiki also inserts two new MyCodes into your forum. They are used like so: `[[aid]]` and `[wiki=aid]` where `aid` is the aid of the Wiki Article you wish to link. This is the number that follows `id=` in the URL of a wiki page.

A link is not added pointing to the wiki, you must add that yourself. :)

## License

[Original Code is licensed under Creative Commons Attribution NonCommercial ShareAlike 3.0 Unported.](creativecommons.org/licenses/by-nc-sa/3.0/)
Some code has been used from other people and this is noted where applicable.
The license for the Markdown parser is available at `Upload/inc/plugins/wiki/markdown-license.txt`.

**This plugin is not affiliated with or related to Stefan-T's plugin of the same name.**

## Contributions

Please submit pull requests against the **master** branch.
**master** is considered to be bleeding edge, and the tagged releases here or on the MyBB Mods site are stable.