# MyBB Wiki Plugin (Beta)

MyBB Wiki is a simple, yet functioning, wiki system for users. It integrates with MyBB nicely, and all the relevant information except templates (settings, permissions, etc) is contained in it's own Admin CP module.

## Extensibility

#### Theming

MyBB Wiki contains it's own template set, inserted into your themes. It uses default MyBB classes, with a few inline elements that will be removed in a later release. You can theme it just like you'd theme any other element of MyBB.

#### Hooking

Hooks are inserted in various parts of the plugin, all of them are prefixed with "wiki_" so you can easily identify them if you generate a list using something like euantor's [MyBB Hook Finder](https://github.com/euantorano/MyBB-Hook-Finder).

#### Languages

There are currently no translations of the Wiki, but I will happily accept any contributions.

#### Upgrades

Where possible, upgraders will be run automatically. We do not, however, save backups of wiki articles for you. This must be done with the Import/Export function inside your Admin CP's Wiki module before an upgrade or reinstall. As much as possible is saved in this process.

Upgrades also reset your settings and categories. Exported articles have the category in them and it has not (yet) been tested how the articles react with a non-existant category, but it should be only a minor issue.

## Usage

MyBB Wiki comes with it's own self-contained Markdown Parser, which is from [Michel Fortin](michelf.ca/projects/php-markdown/classic/). If you're not familiar with the syntax, I highly recommend you read the [DaringFireball Guide](daringfireball.net/projects/markdown/syntax).

MyBB Wiki also inserts two new MyCodes into your forum. They are used like so: `[[aid]]` and `[wiki=aid]` where `aid` is the aid of the Wiki Article you wish to link. This is the number that follows `id=` in the URL of a wiki page.

A link is not added pointing to the wiki, you must add that yourself. :)

### A note about Settings

The setting "Use Markdown Parser" is actually a choice. You can either choose to enable the Markdown parser (and disable the MyCode parser) or enable the MyCode parser (and enable the Markdown parser). So selecting "Yes" on this setting will enable Markdown and disable MyCode, and vice versa. The reason for this is that Markdown and MyCode are incompatible so you've to either choose one or the other. Note that this will not disable the standard MyBB Parser, you will just be unable to use MyCode. Some of the settings hinge on each other - for instance, with MyCode disabled, the editor will not appear (for obvious reasons), with Markdown enabled, the settings to parse HTML (etc) will be ignored.

## License

[Licensed under Creative Commons Attribution NonCommercial ShareAlike 3.0 Unported.](creativecommons.org/licenses/by-nc-sa/3.0/)
The license for the Markdown parser is available at `Upload/inc/plugins/wiki/markdown-license.txt`.

**This plugin is not affiliated with or related to Stefan-T's plugin of the same name.**

## Contributions

Please submit pull requests against the **master** branch.