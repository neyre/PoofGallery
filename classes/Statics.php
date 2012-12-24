<?php

// PoofGallery
// Static Content (CSS, JS, Help Page, etc.) Class
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

class Statics{

  // CSS (GET)
  // Outputs the CSS for the Site
  static function css(){
    $files = array('bootstrap.css','style.css','jquery.fileupload.css');
    Web::minify(__DIR__.'/../views/static/',$files);
  }

  // Core Javascript (GET)
  // Outputs the Core Javascript for the Site
  static function jsCore(){
    self::loadFiles(array('jquery.min.js','jquery-ui-1.9.2.custom.min.js','bootstrap.min.js'));
  }

  // Upload Javascript (GET)
  // Outputs the Javascript for file Uploads
  static function jsUpload(){
    self::loadFiles(array('jquery.fileupload.tmpl.min.js','jquery.fileupload.min.js','jquery.fileupload.ui.js'));
  }

  // Javascript File(s) (GET)
  // Outputs Javascript Files with names that are passed aong
  static function jsFile(){
    $files = explode(',',F3::get('PARAMS.file'));
    foreach($files as $i=>$file)
      $files[$i] = 'pg.'.$file.'.js';
    echo 'window.onload = function () {';
    self::loadFiles($files);
    echo '}';
  }

  // Static File(s) (GET)
  // Outputs Static Files
  static function staticFile(){
    self::loadFiles(F3::get('PARAMS.file'));
  }

  // Help Page (GET)
  // Shows the Program Help Page
  static function help(){
    F3::set('pagetitle','Help');
    echo Template::serve('header.htm');
    self::loadFiles('help.htm');
    echo Template::serve('footer.htm');
  }

  // Helper Function to Output Combined Files from Views/Static Folder
  // Input: String or Array of File Name(s)
  static function loadFiles($input){
    if(!is_array($input))
      $input = array($input);
    foreach($input as $item)
      echo Template::serve('/static/'.$item);
  }
}
?>