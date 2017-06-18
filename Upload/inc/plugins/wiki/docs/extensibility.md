<meta>title:Extensibility|code:extensibility|desc:This document describes the extensibility features present in MyBB Wiki.</meta>

# Extensibility

## Theming

MyBB Wiki contains it's own template set, inserted into your themes. It uses default MyBB classes, with a few inline elements that will be removed in a later release. You can theme it just like you'd theme any other element of MyBB.

The Wiki Revisions are themed by modifying the css selector ".diff-report del" and ".diff-report ins" - these are demonstrated in the file wiki.css.

## Hooking

Hooks are inserted in various parts of the plugin, all of them are prefixed with "wiki_" so you can easily identify them if you generate a list using something like Euan's [MyBB Hook Finder](https://github.com/euantorano/MyBB-Hook-Finder).

## Languages

Here is a complete list of languages available for MyBB Wiki. They are included in the latest releases; you just need to switch your board to using them:

- English
- Persian
- Polish