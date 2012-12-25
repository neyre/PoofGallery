<?php

// PoofGallery
// User Acconts & Authentication Class
// 
// The contents of this file are subject to the terms of the GNU General
// Public License Version 3.0. You may not use this file except in
// compliance with the license. Any of the license terms and conditions
// can be waived if you get permission from the copyright holder.
// 
// Copyright 2012 Nick Eyre
// Nick Eyre - nick@nickeyre.com
// 
// Version 1.0.1

class User{

  // Show User List (GET)
  // Shows List of All Users in Template
  static function view(){
    $users = new Axon(F3::get('dbprefix').'users');
    $users = $users->afind('','access,username');
    F3::set('users',$users);

    echo Template::serve('header.htm');
    echo Template::serve('users.htm');
    echo Template::serve('footer.htm');
  }

  // Create New User (POST)
  // Returns Nothing if Success, 404 if Failure
  static function create(){
    // Data Verificiation
    if(!strlen(F3::get('POST.name')) || !strlen(F3::get('POST.password')))
      F3::error(404);

    // Check for Duplicate Usernames
    $user = new Axon(F3::get('dbprefix').'users');
    $user->load(array('username=:id',array(':id'=>F3::get('POST.name'))));
    if(!$user->dry())
      F3::error(404);

    // Create User
    $user->reset();
    $user->username = F3::get('POST.name');
    $user->access   = F3::get('POST.access');
    self::setPassword($user);
    $user->save();
  }

  // Update User Record (POST)
  // Returns Nothing if Success, 404 if Fail
  static function update(){
    // Check that is not Current User
    if(!strlen(F3::get('POST.name')) || F3::get('POST.name') == F3::get('SESSION.username'))
      F3::error(404);

    // Load Existing User
    $user = new Axon(F3::get('dbprefix').'users');
    $user->load(array('username=:id',array(':id'=>F3::get('POST.name'))));
    if($user->dry())
      F3::error(404);

    // Update Information (Only Update Password if New Password Set)
    if(strlen(F3::get('POST.password')))
      self::setPassword($user);
    $user->access = F3::get('POST.access');
    $user->save();
  }

  // Update User Password (POST)
  // Returns Nothing if Success, 404 if Fail
  static function updatePassword(){
    // Ensure New Password Set
    if(!strlen(F3::get('POST.password')))
      F3::error(404);

    // Retrieve Record & Update Password
    $user = new Axon(F3::get('dbprefix').'users');
    $user->load(array('username=:id',array(':id'=>F3::get('SESSION.username'))));
    self::setPassword($user);
    $user->save();
  }

  // Delete User Record (POST)
  // Returns Nothing if Success, 404 if Fail
  static function delete(){
    // If Username Set and Not Current User
    if(strlen(F3::get('POST.name')) && F3::get('POST.name') != F3::get('SESSION.username')){
      // Load Existing User
      $user = new Axon(F3::get('dbprefix').'users');
      $user->load(array('username=:id',array(':id'=>F3::get('POST.name'))));

      // Remove User
      if(!$user->dry()){
        $user->erase();
      }else
        F3::error(404);
    }else{
      F3::error(404);
    }
  }

  // Log In User (POST)
  // Returns Nothing if Success, 404 if Fail
  static function login(){
    // Load Database Record
    $user = new Axon(F3::get('dbprefix').'users');
    $user->load(array('username=:un',array(':un'=>F3::get('POST.username'))));

    // Check Password
    if(sha1(F3::get('POST.password').$user->salt) != $user->password)
      F3::error(404);

    // Create Session
    F3::set('SESSION.username',$user->username);
    F3::set('SESSION.userAccessLevel',$user->access);
  }

  // Log Out Current User (GET)
  // Clears session & redirects User to Homepage Upon Complete
  static function logout(){
    F3::clear('SESSION');
    F3::reroute('/');
  }

  // Helper Function to Update Password & Salt
  // Input: Handle to User DB Object
  static function setPassword(&$user){
    $user->salt     = sha1(mcrypt_create_iv(16,MCRYPT_DEV_URANDOM));
    $user->password = sha1(F3::get('POST.password').$user->salt);
  }

}

?>