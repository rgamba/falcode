FALCODE
=======

What is FALCODE?
----------------

Falcode is a web development framework based on PHP, MySQL and Javascript.
It uses the MVC programming architecture and it's main goal is to run structured, maintainable and highly scalable web applications.

Requirementes
-------------
- PHP 5.2 or newer

Installation
------------
1. Just copy and paste all the files contained in a HTTP accessible directory.
2. Edit the .htaccess file in the root directory. In line 15, change "falcode/" to match your current installation. For example if your installation in in the root of your localhost or server (http://localhost) then this line should be: "RewriteRule ^(.*)$ index.php?_route_=$1?%{QUERY_STRING} [NC]".
3. Change the engine/conf/definition.php file, change APP_NAME constant to match your app name.
4. If you are going to use database access, change the engine/conf/config.php file, put your database credentials in the Database section of the file. Take a look at the whole file, there are some variables that you might want to change here.
5. Run the install/ directory, it will create the necessary tables and can optionally create the models, modules and CRUDs of your database automatically.
6. Your app should be ready. Try and access the root directory from HTTP. Take a look at the user guide and examples module.

User Guide
----------
Check out the user guide and documentation in the /documentation folder.