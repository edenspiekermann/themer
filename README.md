# Themer

**Themer** is a Tumblr template parser for local development written in PHP. This library is inspired by, and uses code from, [Thimble][thimble] by [mwunsch][mwunsch]. It's goals are simple: make setup a complete breeze; be able to parse all possible [tumblr.com][tumblr] scenarios; and enable easy, seamless customizations to theme processing.

Contributions, suggestions, comments and forks are needed and will be welcomed happily.

## Features

Right now, Themer supports these Tumblr template features:

* Posts
* Tags
* Meta Data (custom variables)
* Localizatoins (English only for now)

...and much more will come as it happens.

## Quick setup

Install Symfony's YAML library:

    $ [sudo] pear install pear.symfony-project.com/YAML

Create an index.php that looks like this in your server:

    <?php
    
    require 'path/to/themer/themer.php';
    Themer::$theme_file = 'my_theme.html'; // the default is 'theme.html';
    Themer::run(__DIR__);

Let your server know how to treat this directory. A simple `.htaccess` example for Apache would look like this:

    RewriteEngine on
    RewriteBase /
    
    # Standard rewrite
    RewriteCond $1 !^(index\.php)
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^(.*)$ ./index.php/$1 [L,QSA]
    
    # MAMP needs to know where you system php is for PEAR libraries
    php_value include_path ".:/usr/lib/php"
    
    # Logging is really optional but recommended so you can `tail -f` it 
    # if you would like to help in develop the project
    php_flag  log_errors on
    php_value error_log  /path/to/project/themer.log

Point your server to your project directory and go...

## TODO

* CLI interface for initializing a project directory to work with Themer
* Notes: because there is no great API for notes, we'll have to set up some defaults and distribute them with the library for now.
* Reblog Attribution: see notes
* Sources: see notes
* Improve localizations
* '-X, --no-teen-posts' (this will make sense later)

## License

Themer is Copyright © 2011 Braden Schaeffer. It is free software, and may be redistributed under the terms specified in the LICENSE file.

[tumblr]: http://tumblr.com/
[Thimble]: https://github.com/mwunsch/thimble
[mwunsch]: https://github.com/mwunsch