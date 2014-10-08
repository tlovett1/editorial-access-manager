=== Editorial Access Manager ===
Contributors: tlovett1
Donate link: http://www.taylorlovett.com
Tags: editorial access management, user roles, user capabilities, role management, user permissions, administrator permissions
Requires at least: 3.6
Tested up to: 4.0
Stable tag: 0.2.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Allow for granular editorial access control for all post types in WordPress

== Description ==

A simple plugin to let you control who has access to what posts. By default in WordPress, we can create users
and assign them to roles. Roles are automatically assigned certain capabilities. See the codex article for a list of
[Roles and Capabilities](http://codex.wordpress.org/Roles_and_Capabilities). Sometimes default roles are not enough,
and we have one-off situations. Editorial Access Manager lets you set which users or roles have access to specific
posts. Perhaps you have a user who is a Contributor, but you want them to have access to edit one specific page? This
plugin can help you.

= Configuration Overview =

There are no overarching settings for this plugin. Simply go to the edit post screen in the WordPress admin and
configure access settings in the "Editorial Access Manager" meta box in the sidebar.

= Managing Access by Roles =

In the "Editorial Access Manager" meta box, enable custom access management by "Roles". Once enabled, the post can only be
edited by users that fall into those roles. However, no matter what, the Administrator role can always edit any post.
This if for safety reasons. You can also only use roles that have the "edit_posts" capability; therefore "Subscriber" by
default cannot be used.

= Managing Access by Users =

In the "Editorial Access Manager" meta box, enable custom access management by "Users". Once enabled, the post can only be
edited by designated users. However, no matter what, any administrator can edit any post. This if for safety reasons.
You can also only use users that have the "edit_others_posts" capability; therefore "Subscriber" users by default
cannot be used.

Fork the plugin on [Github](http://github.com/tlovett1/editorial-access-manager)

== Installation ==

1. Upload and activate the plugin.
1. Browse to any post in the WordPress admin and navigate to the "Editorial Access Management" meta box in
the sidebar.

== Changelog ==

= 0.2.0 =
* Add Italian language support. Props [@marcochiesi](https://github.com/marcochiesi)
* Add post table column to show editorial access. Props [@marcochiesi](https://github.com/marcochiesi)
* Remove source file for minified CSS. Props [@marcochiesi](https://github.com/marcochiesi)
* Tweak capability handling. Props [@marcochiesi](https://github.com/marcochiesi)
* Add proper bower.json
* Cleanup messy JavaScript
* Remove unit testing of unnecessary WP versions

= 0.1.1 =
* Properly revoke access, if necessary, for users that have edit_page but not edit_post.

= 0.1.0 =
* Plugin release
