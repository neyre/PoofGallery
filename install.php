<?php
if(isset($_POST['username'])){
  require 'lib_fatfree/base.php';
  F3::config('config.cfg');

  $username = F3::get('POST.username');
  $salt     = sha1(mcrypt_create_iv(16));
  $password = sha1(F3::get('POST.password').$salt);
  $prefix   = F3::get('dbprefix');

  $con = mysql_connect(F3::get('dbhost'),F3::get('dbuser'),F3::get('dbpass'));
  if (!$con){
    die('Could not connect to database server.');
  }

  $db = mysql_select_db(F3::get('dbname'), $con);
  if (!$db)
    if (!mysql_query("CREATE DATABASE ".F3::get('dbname'),$con))
      die('Could not connect to database or create database.');

  $q = mysql_query("
      CREATE TABLE IF NOT EXISTS `".$prefix."items` (
        `id` varchar(40) NOT NULL,
        `displayorder` bigint(18) DEFAULT NULL,
        `album` tinyint(1) NOT NULL DEFAULT '0',
        `parent` varchar(40) NOT NULL DEFAULT '0',
        `title` varchar(256) DEFAULT NULL,
        `albumcover` varchar(40) DEFAULT NULL,
        `albumcoverparent` varchar(40) NOT NULL DEFAULT '0',
        `starred` tinyint(1) NOT NULL DEFAULT '0',
        `creator` varchar(40) NOT NULL,
        `published` tinyint(1) NOT NULL DEFAULT '0',
        PRIMARY KEY (`id`)
      ) ENGINE=InnoDB DEFAULT CHARSET=latin1;
    ");

  if(!$q)
    die('Could not create item table');

  $q = mysql_query("
      CREATE TABLE IF NOT EXISTS `".$prefix."users` (
        `username` varchar(40) NOT NULL,
        `password` varchar(128) NOT NULL,
        `access` tinyint(1) NOT NULL,
        `salt` varchar(40) NOT NULL,
        UNIQUE KEY `username` (`username`)
      ) ENGINE=InnoDB DEFAULT CHARSET=latin1;
    ");

  if(!$q)
    die('Could not create user table');

  $q = mysql_query("CREATE VIEW `".$prefix."albumlist`
        AS SELECT
        a.id AS id,
        a.title AS title,
        b.title AS parent0,
        c.title AS parent1
        FROM pg_items AS a
        LEFT JOIN pg_items AS b
        ON a.parent=b.id
        LEFT JOIN pg_items AS c
        ON b.parent=c.id
        WHERE a.album=1
        ORDER BY c.title,b.title,a.title");

  if(!$q)
    die('Could not create album list view');

  $q = mysql_query("
      INSERT INTO `".$prefix."users` (`username`, `password`, `access`, `salt`) VALUES
      ('$username', '$password', 8, '$salt')
      ");

  if(!$q)
    die('Could not create admin user.');

  $q = mysql_query("CREATE TABLE IF NOT EXISTS `".$prefix."sessions` (
    `id` varchar(40) NOT NULL,
    `username` varchar(40) NOT NULL,
    `access` int(11) NOT NULL,
    `start` int(12) NOT NULL,
    `expires` int(12) NOT NULL
  ) ENGINE=InnoDB DEFAULT CHARSET=latin1;");

  if(!$q)
    die('Could not create sessions table');

  $success = true;
}
?>

<h1>Poof Gallery Installation</h1>
<h3>Step One: Config</h3>
Rename the "config.cfg.sample" file as "config.cfg".  Edit the file with values that represent your setup.
Rename the ".htaccess.sample" file as ".htaccess".  If the gallery is not at your web root, change "/index.php" to the relative location of the index.php file on your server.

<h3>Step Two: Generate Database</h3>
<form method='post' action='install.php'>
  Admin User Username: <input name=username>
  Admin User Password: <input name=password>
  <input type=submit value='Create Database'>
</form>

<?php if(isset($success)){ ?>
  <h5>Database Successfully Generated</h5>
<?php } ?>

<h3>Step 3: Delete This File</h3>
This "install.php" file contains code to overwrite your database.  Delete it after you are done installing the site.
