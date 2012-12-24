<?php

// PoofGallery
// 
// The contents of this file are subject to the terms of the GNU General
// Public License Version 3.0. You may not use this file except in
// compliance with the license. Any of the license terms and conditions
// can be waived if you get permission from the copyright holder.
// 
// Copyright 2012 Nick Eyre
// Nick Eyre - nick@nickeyre.com
// 
// Version 1.0.0

// INCLUDE FRAMEWORK
require __DIR__.'/lib_fatfree/base.php';

// INITIALIZE F3 APPLICATION, SET PARAMETERS, CONNECT TO DATABASES
F3::config(__DIR__.'/config.cfg'); // Locate Config File
F3::set('CACHE','folder='.__DIR__.'/cache/'); // Setup Folder Cache
F3::set('SYNC',31536000); // How often to check for DB structure changes (1 year cache)
F3::set('DEBUG',0); // Disable Debugging
F3::set('UI',__DIR__.'/views/'); // Set View Location
F3::set('TEMP',__DIR__.'/temp/'); // Set Temp Location
F3::set('AUTOLOAD',__DIR__.'/classes/'); // Set Location for Classes
F3::set('DB',new DB('mysql:host='.F3::get('dbhost').';dbname='.F3::get('dbname'),F3::get('dbuser'),F3::get('dbpass')));
$cacheStatic = 86400; // Time to Cache Static Pages

// UNCOMMENT TO DEBUG
// F3::set('SYNC',0);
// F3::set('DEBUG',2);
// F3::set('CACHE',FALSE);
// $cacheStatic = 0;

// PUBLIC ROUTES
F3::route('GET  /',                'Item::viewAlbumPartial');
F3::route('GET  /@album',          'Item::viewAlbumPartial');
F3::route('GET  /@album/all',      'Item::viewAlbum');
F3::route('GET  /img/@id',         'Item::downloadImage');
F3::route('GET  /img/@id/@size',   'Item::viewImage');
F3::route('POST /login',           'User::login');
F3::route('GET  /logout',          'User::logout');

// UPLOADER ROUTES
if(F3::get('SESSION.userAccessLevel') > 1){
  F3::route('GET  /@album/upload',   'Item::viewAlbumUpload');
  F3::route('POST /@album/upload',   'Item::recieveUpload');
  F3::route('POST /users/password',  'User::updatePassword');
}

// ARRANGER ROUTES
if(F3::get('SESSION.userAccessLevel') > 4){
  F3::route('GET  /@album/organize', 'Item::viewAlbumOrganize');
  F3::route('POST /album/new',       'Item::createAlbum');
  F3::route('POST /album/rename',    'Item::renameAlbum');
  F3::route('POST /@album/organize', 'Item::updateAlbumOrganization');
  F3::route('POST /move',            'Item::moveItems');
  F3::route('POST /delete',          'Item::deleteItems');
}

// ADMIN ROUTES
if(F3::get('SESSION.userAccessLevel') > 7){
  F3::route('GET  /users',           'User::view');
  F3::route('POST /users/new',       'User::create');
  F3::route('POST /users/update',    'User::update');
  F3::route('POST /users/delete',    'User::delete');
  F3::route('GET  /clearcache',      'clearCache');
}

// STATIC ROUTES (Cache for 1 Day)
F3::route('GET /static/css',       'Statics::css',        $cacheStatic);
F3::route('GET /static/js/core',   'Statics::jsCore',     $cacheStatic);
F3::route('GET /static/js/upload', 'Statics::jsUpload',   $cacheStatic);
F3::route('GET /static/js/@file',  'Statics::jsFile',     $cacheStatic);
F3::route('GET /static/@file',     'Statics::staticFile', $cacheStatic);
F3::route('GET /help',             'Statics::help',       $cacheStatic);

// CHECK FOR INSTALL FILE
if(file_exists('install.php') && F3::get('DEBUG') == 0)
  echo 'ERROR: install.php file exists. This is a major security hole. Delete install.php file to secure this site.';

// RUN F3 APPLICATION
F3::run();

// CLEAR CACHE (FOR DEBUGGING)
function clearCache(){
  foreach(glob('../cache/'.'*.*') as $v)
    unlink($v);
  foreach(glob('../temp/'.'*.*') as $v)
    unlink($v);
  echo 'The Cache has been Cleared';
}
  
?>