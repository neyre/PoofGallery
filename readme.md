# PoofGallery

*A dynamic gallery program designed to keep gallery management with multiple users easy and in control.*
*PoofGallery was developed in 2012 by Nick Eyre for [Team 254 Robotics](http://team254.com).*



## Table of Contents

* [System Requirements](#system-requirements)
* [Installation](#installation)
* [Users](#users)
* [Uploading Photos](#uploading-photos)
* [Photo and Album Organization](#photo-and-album-organization)
* [Organizing Albums](#organizing-albums)



## System Requirements

PoofGallery doesn't require much.  However, it uses the Fat-Free Framework and as such requires a modern verison of PHP.  The requirements are:

* PHP 5.3+
* MySQL (Other PDO-Supported Databases May Work but are Untested)



## Installation

PoofGallery installation is easy and pain-free.

1. Download this repository as [a ZIP File](https://github.com/nickeyre/PoofGallery/archive/master.zip).  Put the contents of the archive into your web host's directory.  *Note that the web root should be pointed to the "www" folder.  The files in the root directory of the archive should not be accessible from the web.*

2. Rename the "config.cfg.sample" file into "config.cfg".  Edit the file with values that represent your setup.  You'll need to create a database or use one you already have.  Rename the ".htaccess.sample" file as ".htaccess".  If the gallery is not at your web root, change "/index.php" to the relative location of the index.php file on your server.

3. Go to the install.php file.  Enter an admin username and password and click the button to generate the PoofGallery database.

4. Delete the install.php file.  This is critical.  Leaving the install.php file accessible is a serious security hole.



##Users

To use the PoofGallery back-end, a user must have an account.  There are several levels of account permissions in the software:

* **Upload Only** users can upload photos to any pre-existing album.  Note that upload only users cannot publish their own photos.
* **Upload &amp; Arrange** users can create, move, rename and delete albums and can upload, move, publish and delete photos.
* **Administrator** users can perform all actions on the website including managing user accounts.

Administrators manage user accounts through the User Administration Panel.  User accounts can be created, edited or deleted through the panel.  A user cannot delete his or her own account.

Any user can change his or her password from a gallery pageon the site.



## Uploading Photos

Any registered user can upload photos to any pre-existing album.  From a gallery page on the site, click the *Upload Photos Here* button at the top of the page.

Once on the upload page, add photos to your upload queue by either dragging them to the page or clicking the *Add files...* at the top of the page.  Add as many files as desired to the upload queue.

Once files are added, the upload queue will appear in a table below.  To start an individual upload, click the *Start* button next to an item in the queue.  To start all uploads, click the *Start Upload* button at the top of the page.  While the files are being uploaded, progress bars will be shown next to each file.  When the blue button next to a file disappears, the upload has completed.
      
Once an upload has been completed, the newly uploaded photos must be published by a user with *Upload &amp; Arrange* Permissions (see *Organizing Albums* below).



## Photo &amp; Album Organization

For uploaded photos to appear in the gallery, they must be published on the organize screen. First, some terminology for the organize screen:

* **Published Photos** can be viewed by anybody who comes to the site.
* **Highlighted Images** are marked by a <i class="icon-star"></i> on the organize screen.
* **Album Covers** are marked by a <i class="icon-book"></i> on the organize screen.  These albums or photos will show up as the default thumbnail image for the current album.
* **Unpublished Photos** cannot be viewed by anybody without *Upload &amp; Arrange* Permissions.  Newly uploaded photos are unpublished by default.

The concept of *Highlighted Images* is somewhat unique to PoofGallery.  By default, only the highlighted and published photos in an album will be visible to site visitors.  However, if there are published but non-highlighted images in a gallery, a visitor can click the *all images* link at the bottom of a page to view the remaining images. The purpose of this structure is to allow for galleries with large numbers of photos which have been taken by a number of photographers while allowing site administrators to highlight a set of select images which provide a good overview of the album as to prevent average visitors from digging through hundreds of photos.

It is also important to note that any uploaded images, regardless of whether they are published or unpublished, can be viewed and downloaded from their photo URLs.  However, all photo and album URLs are generated from 40-character random SHA-1 hashes, giving 2^160 (more than a trillion trillion trillion trillion) URL possibilites, so it is *extremely* unlikely that the URL will be guessed.  However, if you are storing incredibly sensitive photos as unpublished photos in your PoofGallery, know that they could *in theory* be found.



## Organizing Albums

Now that you've uploaded some photos to your PoofGallery, it's time to publish them so they can be enjoyed by the world.  To organize an album, click the *Organize Album* button at the top of that album's page.

The organization page for an album consists of a number of *tiles*, each of which represent a photo or sub-album within the current album.  The tiles are broken up into *sections*, one each for albums, published photos and unpublished photos from each contributing user.
        
Tiles for photos contain toggle buttons with the various actions available for that photo:</p>

* **Publish** a photo with the up button.  Unpublish it with the down button.
* **Star** a photo with the star button.  A yellow-highlighted button indicates that the item has been starred.
* Make an item the album **cover** with the cover button.  A green-highlighted button indicates that the item is already the cover.

Furthermore, the tiles within a section can be rearranged by *dragging and dropping* them into a new order.  Bam!</p>

To perform an action to a bunch of tiles at once, select them by clicking on their thumbnails and pick an action from the *Selected* menu.  Hint: there are keyboard shortcuts for select all (a) and deselct all (d).