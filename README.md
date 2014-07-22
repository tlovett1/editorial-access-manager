Editorial Access Manager
==============

Allow for granular editorial access control for all post types in WordPress

## Purpose

A simple plugin to let you control who has access to what posts. By default in WordPress, we can create users
and assign them to roles. Roles are automatically assigned certain capabilities. See the codex article for a list of
[Roles and Capabilities](http://codex.wordpress.org/Roles_and_Capabilities). Sometimes default roles are not enough,
and we have one-off situations. Editorial Access Manager lets you set which users or roles have access to specific
posts. Perhaps you have a user who is a Contributor, but you want them to have access to edit one specific page? This
plugin can help you.

## Installation

Install the plugin in WordPress, you can download a
[zip via Github](https://github.com/tlovett1/editorial-access-manager/archive/master.zip) and upload it using the WP
plugin uploader.

## Configuration

There are no overarching settings for this plugin. Simply go to the edit post screen in the WordPress admin and
configure access settings in the "Editorial Access Manager" meta box in the sidebar.

#### Managing Access by Roles
In the "Editorial Access Manager" meta box, enable custom access management by "Roles". Once enabled, the post can only be
edited by users that fall into those roles. However, no matter what, the Administrator role can always edit any post.
This if for safety reasons. You can also only use roles that have the "edit_posts" capability; therefore "Subscriber" by
default cannot be used.

#### Managing Access by Users
In the "Editorial Access Manager" meta box, enable custom access management by "Users". Once enabled, the post can only be
edited by designated users. However, no matter what, any administrator can edit any post. This if for safety reasons.
You can also only use users that have the "edit_others_posts" capability; therefore "Subscriber" users by default
cannot be used.

## Development

#### Setup
Follow the configuration instructions above to setup the plugin. I recommend developing the plugin locally in an
environment such as [Varying Vagrant Vagrants](https://github.com/Varying-Vagrant-Vagrants/VVV).

If you want to touch JavaScript or CSS, you will need to fire up [Grunt](http://gruntjs.com). Assuming you have
[npm](https://www.npmjs.org/) installed, you can setup and run Grunt like so:

First install Grunt:
```
npm install -g grunt-cli
```

Next install the node packages required by the plugin:
```
npm install
```

Finally, start Grunt watch. Whenever you edit JS or SCSS, the appropriate files will be compiled:
```
grunt watch
```

#### Testing
Within the terminal change directories to the plugin folder. Initialize your unit testing environment by running the
following command:

For VVV users:
```
bash bin/install-wp-tests.sh wordpress_test root root localhost latest
```

For VIP Quickstart users:
```
bash bin/install-wp-tests.sh wordpress_test root '' localhost latest
```

where:

* wordpress_test is the name of the test database (all data will be deleted!)
* root is the MySQL user name
* root is the MySQL user password (if you're running VVV). Blank if you're running VIP Quickstart.
* localhost is the MySQL server host
* latest is the WordPress version; could also be 3.7, 3.6.2 etc.

Run the plugin tests:
```
phpunit
```

#### Issues
If you identify any errors or have an idea for improving the plugin, please
[open an issue](https://github.com/tlovett1/editorial-access-manager/issues?state=open).