<?php

// PoofGallery
// Item (Photos & Albums) Class
//
// The contents of this file are subject to the terms of the GNU General
// Public License Version 3.0. You may not use this file except in
// compliance with the license. Any of the license terms and conditions
// can be waived if you get permission from the copyright holder.
//
// Copyright 2012 Nick Eyre
// Nick Eyre - nick@nickeyre.com
//
// Version 2.0.0

class Item{

  // Show Album (GET)
  // Shows album items in Template.
  // Global variables set from database lookups are cached
  static function viewAlbum($partial=false){
    F3::set('partial',$partial);

    // Retrieve Values from Cache if Cached
    $cacheVariables = array('albums','photos','breadcrumbs','pagetitle','albumcover','hasmoreresults');
    if(F3::cached(F3::get('partial').F3::get('PARAMS.album'))){
      $data = F3::get(F3::get('partial').F3::get('PARAMS.album'));
      foreach($cacheVariables as $var)
        F3::set($var,$data[$var]);
    }

    // If Not Cached, Generate Values
    else{
      // Get Album Details & Contents
      $album = self::getCurrentAlbum();
      $items = $album->afind(array('parent=:id AND published=1'.(F3::get('partial')?' AND starred=1':''),array(':id'=>F3::get('PARAMS.album'))),'displayorder');
      self::splitResults($items);

      // Should "Show More" Button Be Shown on Partial Page?
      if(F3::get('partial')){
        $album->def('count','COUNT(id)');
        $album->load(array('parent=:id AND published=1 AND starred=0',array(':id'=>F3::get('PARAMS.album'))),'displayorder');
        if($album->count)
          F3::set('hasmoreresults',true);
      }

      // Save in Cache
      $cacheData = array();
      foreach($cacheVariables as $var)
        $cacheData[$var] = F3::get($var);
      F3::set(F3::get('partial').F3::get('PARAMS.album'),$cacheData,true);
    }

    // Show Total Item Count in Footer (if Config Allows)
    if(F3::get('showtotals')){
      $item = new Axon(F3::get('dbprefix').'items');
      $item->def('count','COUNT(id)');
      $item->load('published=1 AND album=0');
      $photos = $item->count;
      $item->load('album=1');
      $albums = $item->count;
      F3::set('itemcount',$photos.' Photos in '.$albums.' Albums.');
    }

    echo Template::serve('header.htm');
    echo Template::serve('gallery.htm');
    echo Template::serve('footer.htm');
  }

  // Show Partial Album (GET)
  // Shows starred album items in Template.
  static function viewAlbumPartial(){
    self::viewAlbum(true);
  }

  // Show Album (Organize Mode) (GET)
  // Shows all contained items in Organize Mode
  static function viewAlbumOrganize(){
    // Get Album Details & Contents
    $album = self::getCurrentAlbum();
    $items = $album->afind(array('parent=:id',array(':id'=>F3::get('PARAMS.album'))),'displayorder');
    self::splitResults($items);

    // Get List of Albums (Possible Item Move Targets)
    $albums = new Axon(F3::get('dbprefix').'albumlist');
    $albums = $albums->afind();
    array_unshift($albums,array('id'=>0,'title'=>'Root Album','parent1'=>0,'parent0'=>0));
    F3::set('albumlist',$albums);

    echo Template::serve('header.htm');
    echo Template::serve('organize.htm');
    echo Template::serve('footer.htm');
  }

  // Show Album (Upload Mode) (GET)
  // Shows Album Upload Screen in Template
  static function viewAlbumUpload(){
    // Get Album Details
    self::getCurrentAlbum();

    F3::set('upload',true); // Include Upload JS Scripts
    echo Template::serve('header.htm');
    echo Template::serve('upload.htm');
    echo Template::serve('footer.htm');
  }

  // Create Album (POST)
  // Returns Nothing if Success, 404 if Data Validation Fails
  static function createAlbum(){
    // Validate Input
    if(strlen(F3::get('POST.name')) < 1 || (strlen(F3::get('POST.parent')) != 1 && strlen(F3::get('POST.parent')) != 40))
      F3::error(404);

    // Create Album
    $item = self::createItem();
    $item->parent = F3::get('POST.parent');
    $item->title  = F3::get('POST.name');
    $item->album  = 1;
    $item->published = 1;
    $item->starred = 1;
    $item->save();

    // Clear Parent Cache so it shows up
    self::clearCache($item->parent);
  }

  // Recieve Upload (POST)
  // Process Photo Upload
  static function recieveUpload(){
    set_time_limit(0);
    $results = array();

    // For Each File
    for($i=0;$i<(count($_FILES['files']['name']));$i++){
      // If No Errors and File is JPEG
      if($_FILES['files']['error'][$i] == 0 && in_array(self::getExtension($_FILES['files']['name'][$i]),array('jpg','jpeg'))){

        // Create Item
        $item = self::createItem();
        $item->parent = F3::get('PARAMS.album');
        $item->album  = 0;

        // Get File Name Numbers to set as Order so that Uploaded Files will be Ordered by File Name
        $order = '';
        for($j=0; $j < strlen($_FILES['files']['name'][$i]); $j++)
          if(is_numeric($_FILES['files']['name'][$i][$j]))
            $order .= $_FILES['files']['name'][$i][$j];
        $item->displayorder = substr($order,0,18); // Limit Integer Size

        // Create Status Array
        $status = array();
        $status['name'] = $_FILES['files']['name'][$i];
        $status['size'] = $_FILES['files']['size'][$i];

        // Move File, Save in Database, Push to Status Array
        if(!move_uploaded_file($_FILES['files']['tmp_name'][$i], F3::get('mediauploadpath').$item->id.'.jpg'))
          die('Permission Error');

        // Create Resizer Object (Include Path Requires a Leading Slash on Windows)
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN')
          include('lib_phpthumb/ThumbLib.inc.php');
        else
          include('lib_phpthumb/ThumbLib.inc.php');
        $resizeroptions = array('resizeUp' => false, 'jpegQuality' => 80, 'preserveAlpha' => false);
        try{
          $img = PhpThumbFactory::create(F3::get('mediauploadpath').$item->id.'.jpg', $resizeroptions);
        }catch (Exception $e){}

        // Resize Screen Images Without Cropping
        $dims = $img->getCurrentDimensions();
        if($dims['width'] < $dims['height'])
          $img->resize(F3::get('screensize.1'),F3::get('screensize.1')); // Portrait
        else
          $img->resize(F3::get('screensize.0'),F3::get('screensize.0')); // Landscape
        $img->save(F3::get('mediauploadpath').$item->id.'-screen.jpg','JPG');

        // Crop Resize Thumbnails & Save
        $img->adaptiveResize(F3::get('thumbsize.0'),F3::get('thumbsize.1'));
        $img->save(F3::get('mediauploadpath').$item->id.'-thumb.jpg','JPG');

        // POST Uploads to Another Server
        if(F3::get('postupload')){
          $post_data['dir'] = F3::get('postdir');
          $post_data['secret'] = F3::get('postsecret');
          $post_data['count'] = 3;
          $post_data['file0'] = '@'.dirname(__DIR__).'/'.F3::get('mediauploadpath').$item->id.'-thumb.jpg';
          $post_data['file1'] = '@'.dirname(__DIR__).'/'.F3::get('mediauploadpath').$item->id.'-screen.jpg';
          $post_data['file2'] = '@'.dirname(__DIR__).'/'.F3::get('mediauploadpath').$item->id.'.jpg';
          $ch = curl_init();

          curl_setopt($ch, CURLOPT_URL, F3::get('posturl'));
          curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
          curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
          curl_setopt($ch, CURLOPT_VERBOSE, 1);   // Debug
          $out = curl_exec($ch);

          unlink(F3::get('mediauploadpath').$item->id.'-thumb.jpg');
          unlink(F3::get('mediauploadpath').$item->id.'-screen.jpg');
          unlink(F3::get('mediauploadpath').$item->id.'.jpg');
        }

        // Save in Database
        $item->save();
        array_push($results,$status);
      }
      // Return Status Array
      echo json_encode($results);
    }
  }

  // Rename Album (POST)
  // Returns Nothing if Success, 404 if Data Validation Fails
  static function renameAlbum(){
    // Validate
    if(strlen(F3::get('POST.name')) < 1) F3::error(404);

    // Load Database Entry
    $item = new Axon(F3::get('dbprefix').'items');
    $item->load(array('id=:id',array(':id'=>F3::get('POST.id'))));
    if($item->dry()) F3::error(404);

    // Rename Album & Save
    $item->title = F3::get('POST.name');
    $item->save();
  }

  // Update Album Organization (POST)
  // Returns Nothing
  static function updateAlbumOrganization(){
    // Create a Database Handle
    $item = new Axon(F3::get('dbprefix').'items');

    // For each item sent in the request, update its database entry
    foreach(F3::get('POST.items') as $i){
      // Load from Database
      $item->load(array('id=:id',array(':id'=>$i['id'])));
      $changes = false;

      if(!$item->dry()){
        // For Images, Update Starred & Published Status
        if(!$item->album){
          $star = $i['star'] === 'true'?1:0;
          $pub  = $i['pub']  === 'true'?1:0;
          if($item->starred != $star){
            $item->starred = $star;
            $changes = true;
          }
          if($item->published != $pub){
            $item->published = $pub;
            $changes = true;
          }
        }

        // Update Order if Order has Changed
        if($item->displayorder != $i['order']){
          $item->displayorder = $i['order'];
          $changes = true;
        }

        // If Changes were made, Save Changes
        if($changes) $item->save();
      }
    }

    // Update Album Cover
    if(F3::get('POST.albumcover')){
      // Load New Cover in Database
      $item->load(array('id=:id AND published=1',array(':id'=>F3::get('POST.albumcover'))));
      $parent = $item->parent;

      if($item->album){
        $albumcover = $item->albumcover;
        $albumcoverparent = $item->id;
      }else{
        $albumcover = $item->id;
        $albumcoverparent = 0;
      }

      // Load Album in Database, Update Cover if Changed & Clear Cached View of Parent Album
      if(!$item->dry()){
        $item->load(array('id=:id AND album=1',array(':id'=>$parent)));
        if($item->albumcover != $albumcover || $item->albumcoverparent != $albumcoverparent){
          $item->albumcover = $albumcover;
          $item->albumcoverparent = $albumcoverparent;
          $item->save();
          self::clearCache($item->parent);
        }
      }
    }

    // Clear Cached View of Album
    self::clearCache(F3::get('POST.album'));
  }

  // Move Items to a New Album (POST)
  // Returns Nothing
  static function moveItems(){
    // Validate Target
    if(strlen(F3::get('POST.target')) != 40 && F3::get('POST.target') != 0) F3::error(404);

    // Loop Through Items
    $photo = new Axon(F3::get('dbprefix').'items');
    foreach(F3::get('POST.selected') as $item){
      if(strlen($item) == 40){
        // Move Item If Not Moving To Itself
        if($item != F3::get('POST.target')){
          $photo->load(array('id=:id',array(':id'=>$item)));
          $photo->parent = F3::get('POST.target');
          $photo->save();
        }

        // Clear anywhere that item is a cover
        $photo->load(array('albumcover=:id',array(':id'=>$item)));
        while(!$photo->dry()){
          $photo->albumcover = 0;
          $photo->save();
          $photo->skip();
        }
      }
    }
  }

  // Delete Items and move to Deleted Album (POST)
  // Returns Nothing
  static function deleteItems(){
    foreach(F3::get('POST.selected') as $id)
      self::deleteItem($id);
  }

  // Helper Function: Delete Current Item
  // Calls itself to recur through albums
  static function deleteItem($id){
    // Grab Item from Database
    $item = new Axon(F3::get('dbprefix').'items');
    $item->load(array('id=:id',array(':id'=>$id)));
    if($item->dry()) return false;

    // If Album, Clear Cache & Recur Through Album Contents
    if($item->album){
      self::clearCache($item->id);
      $child = new Axon(F3::get('dbprefix').'items');
      $child->load(array('parent=:id',array(':id'=>$id)));
      while(!$child->dry()){
        self::deleteItem($child->id);
        $child->skip();
      }

    // If Photo, Clear Thumbnails & Move Photo from Photos to Deleted Folder
    }else{
      self::clearCache($item->id);
      if(file_exists(F3::get('mediauploadpath').$item->id.'.jpg'))
        rename(F3::get('mediauploadpath').$item->id.'.jpg',F3::get('mediauploadpath').'deleted/'.$item->id.'.jpg');
    }

    // Remove from Database
    $item->erase();
  }

  // Helper Function: Get Current Album
  // Sets Album Details & Breadcrumb Info to Globals and Returns Database Handle
  static function getCurrentAlbum(){
    // Get Album Details
    $album = new Axon(F3::get('dbprefix').'items');
    if(strlen(F3::get('PARAMS.album')) != 40)
      F3::set('PARAMS.album','0');
    else{
      $album->load(array('id=:id AND album=1',array(':id'=>F3::get('PARAMS.album'))));
      if($album->dry())
        F3::set('PARAMS.album','0');
      F3::set('pagetitle',$album->title);
      F3::set('albumcover',$album->albumcover);
      F3::set('albumcoverparent',$album->albumcoverparent);
    }

    // Build Breadcrumbs
    $breadcrumbs = array();
    $breadcrumbs[F3::get('PARAMS.album')] = F3::get('pagetitle');
    if(isset($album->parent)){
      while($album->parent){
        $album->load(array('id=:id',array(':id'=>$album->parent)));
        $breadcrumbs[$album->id] = $album->title;
      }
      $breadcrumbs[0] = F3::get('sitetitle');
    }
    F3::set('breadcrumbs',array_reverse($breadcrumbs,true));

    // Return Database Handle
    return $album;
  }

  // Helper Function: Grab Extension from a File Name
  // Input: String, Output: String
  static function getExtension($file_name){
    $ext = explode('.', $file_name);
    $ext = array_pop($ext);
    return strtolower($ext);
  }

  // Helper Function: Create Item
  // Creates a New Item (Album or Photo) with a Unique Hashed ID. Returns Item Axon Object.
  static function createItem(){
    // Generate Random Hash ID and Check for Duplicate
    $item = new Axon(F3::get('dbprefix').'items');
    while(true){
      $id = sha1(mcrypt_create_iv(12,MCRYPT_DEV_URANDOM));
      $item->load(array('id=:id',array(':id'=>$id)));
      if($item->dry()) break;
    }

    // Create & Return New Item
    $item->reset();
    $item->id               = $id;
    $item->creator          = F3::get('username');
    $item->title            = '';
    $item->albumcover       = 0;
    $item->albumcoverparent = 0;
    $item->published        = 0;
    $item->starred          = 0;
    $item->displayorder     = time();
    return $item;
  }

  // Helper Function: Split a Results Array into Item Types (Album, Photo, Unpublished Photo)
  // Input: Results Array, Sets results to F3 Globals for display in a template.
  static function splitResults($items){
    // Split into Published and Unpublished
    $pubSplit = self::splitByField($items,'published');

    // Split Published into Albums & Photos
    if(isset($pubSplit[1])){
      $typeSplit = self::splitByField($pubSplit[1],'album');
      if(isset($typeSplit[1]))
        F3::set('albums',$typeSplit[1]);
      if(isset($typeSplit[0]))
      F3::set('photos',$typeSplit[0]);
    }

    // Split Unpublished by Creator
    if(isset($pubSplit[0])){
      $creatorSplit = self::splitByField($pubSplit[0],'creator');
      F3::set('unpublished',$creatorSplit);
    }
  }

  // Helper Function: Split a Results Array by Field
  // Input: Results Array, Field to Splity By. Output: Results Array Broken down into sub-arrays by the field
  static function splitByField($input,$field){
    $output = array();
    foreach($input as $item)
      $output[$item[$field]][] = $item;
    return $output;
  }

  // Clear Cached Items of Given ID
  static function clearCache($id){
    F3::clear($id);          // Album View (Full)
    F3::clear('1'.$id);      // Album View (Partial)
  }
}
?>
