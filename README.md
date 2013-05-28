# Croogo: Multiattach Plugin

**Multiattach** is a free, open source, Attachment manager for nodes in the Croogo CMS for PHP, released under [MIT License].

It uses jQuery and ajax for the UI.

## Requirements
  * Croogo 1.5 or higher
  * HTML5 support
  * Javascript support
  * jQuery (included in Croogo 1.5)

## Installation

#### Web based installer

  * Upload the .zip file through Croogo's extension manager.

#### Manual installation

  * Extract the file. Upload the content to your Croogo installation in the ./app/Plugins/Multiattach directory.
  * visit Croogo's extension system to "activate" the plugin.

#### Create a "files" folder

  * Create a folder called "files" in your app folder, set the proper permissions to be able to upload files, this is done internally if apache has the rights to do it, but if you see an error, just create it manually.

## How to use

  * Create a node of any type.
  * Add the content as usual
  * Save it
  * Edit it
  * A new tab that says Attachments with (hopefully) a 0 (zero) will appear (this represents the number of attachments for the current node)
  * Click the tab
  * At the bottom of the page there are 2 buttons, Upload and Attach from URL; these will open a pop up.
  * [Upload] takes any number of files (restricted internally by php by its filesize) and uploads them
  * [Attach from URL] takes a URL, parses it and saves the information.
  * Edit the fields that you feel necesary and save.
  * The new attachments will appear in the Attachments tab.
  * Save the node.

## What is it useful for?
  * Galleries
  * Project display pages
  * Video section
  * Links section
  * Downloads section
  * Ecommerce

## Features
  * It stores the files (and the thumbnails) hashed out of the public folder (useful if you want to develop nodes with "private" content.
  * Internal image resizer soon with thumbnail size editor.
  * Upload multiple files at a time (requires html5 enabled browser).
  * Parses website information to suggest its contents, and the information is editable.
  * The default website parser reads meta tags (open graph, twitter and regular ones), heading tags, p and img tags to look for information. 
  * Parsed websites are stored in JSON inside the database.
  * Website parsers can be added (if you want to attach Vimeo videos, just get a Vimeo datasource and with little changes it can be done)
  * Plugin activation also creates database

## Limitations

  * It supports just JPG, GIF, PNG and BMP for images, [pdf, txt] will be uploaded but no preview will be shown
  * Currently it supports only Youtube for videos and any other website will be parsed from its meta tags. Other websites can be easily added by just creating the datasource and naming it appropriately.
  * Currently it supports 3 sizes of thumbnails, others can be hardcoded or you can wait for the feature to be developed (or better yet, develop it :D)
  * Currently the description for the attachment can just be edited at creation time
  * Currently the attachments cant be sorted
  * Currently Images from parsed websites cant be changed
 

## To do

  * Attachments on node creation
  * Edit descriptions once they are uploaded
  * Edit the allowed mime types without hardcoding them
  * Use of datasources properly (i think right now they are used just as regular php objects), take advantage of caching (?)
  * Document methods in controllers
  * Edit how file routes are displayed
  * Proper image detection (mime types hardcoded right now)
  * Cleanup code
  * ~~Retrieve attachments easily~~
  * Thumbnail resizer needs to be optional (external)
  * Attach Galleries or Single pics from Facebook
  * Attach from dropbox
  * Attach from Google drive
  * Manually creating a "files" folder and setting permissions instruction.
  * "Private" files optional
  * For non private files, attach to the default attachment model
  * File picker for previously uploaded attachments.
  * Check the transparent PNG error.
  * ~~Auth error (https://groups.google.com/d/msg/croogo/F00CZBvmf7g/OdFFmEQsoCEJ)~~
  * * Thumbnail sizes editable
  * * Reroute cache (thumbnails) folder to default cakephp cache folder

## How to retrieve Attachments

Use the helper

The Multiattach array is already "linked" to the node model, so use the set method to set the attachments in a helper var
then you can use one of two methods that work similar:
  * $this->Multiattach->filter(array('key'=>'regex to compare value')) : Useful for getting file attachments (compare mime types, filenames, etc.)
  * $this->Multiattach->filterWebContent(array('key'=>'regex to compare value parsed from web')) : Useful for getting web attachments as it compares values parsed from the web (url, player url, title, description, any other.. depends on the datasource)

Get attached videos from youtube:

```
        $this->Multiattach->set($node["Multiattach"]);
        $youtubeVids=$this->Multiattach->filterWebContent(array('player'=>'/youtube.com/i'));
        foreach($youtubeVids as $ytv) {
         ?><iframe src="<?php echo $ytv["Multiattach"]["content"]["player"];?>"></iframe><?php
        }
```

Get the attached images:

```
        $this->Multiattach->set($node["Multiattach"]);
        $images=$this->Multiattach->filter(array('mime'=>'#image#i'));
        foreach($images as $image) {
           $link=$this->Html->url(array(
 														'plugin'=>'Multiattach',
															'controller'=>'Multiattach',
															'action'=>'displayFile', 
															'admin'=>false,
															'dimension'=>'normal',
															'filename'=>$image["Multiattach"]['filename']
														));
              ?><img src="<?php echo $link; ?>" alt="attached image" /><?php
        }
```

## Extend its use

### How to add more thumbnail sizes:

  * Open Multiattach controller (Plugins/Multiattach/Controller/MultiattachController.php)
  * Look for the _getDimension method
  * Go to the switch part of the method
  * Lets say you need a thumbnail called custom, with size 300px width, and 150px height, do the following:

```
switch($dimension){
    case 'thumb':
        // Width: 150px, height: proportional
        $size=array(150);
        break;
    case 'square-thumb':
        // Square resize
        $size=array(100,100);
        break;
    case 'normal':
        // Do not resize
        $size=array(0,0);
        break;
    case 'custom':
        $size=array(300,150);
        break;
    default:
        $size=array(0,0,1);
        break;
}
```
   * .. Or implement this event to return a array with 2 elements (width,height):

```
$size=Croogo::dispatchEvent('Controller.Multiattach.getDimension', $this, array('dimension' => $dimension));
```

### How to add a datasource:

  * Lets say you are implementing a Vimeo datasource
  * Create a datasource as usual
  * Implement any of this methods: findByURL($url), findById($url), find($url)
  * Return an array with the information you want to save, BUT make sure you return the following keys: title, description, image, player (the player url, see [Open graph](http://ogp.me/)'s video specs)
  * Name it appropietly: VimeoCom will have a higher hierarchy than Vimeo, but any will work.
  * Notice that if you create a YoutubeCom and a Youtube, the first will be picked, so try to be as specific as you can (dont add www, but if a subdomain is required DO add it, e.g. DmvCaGov for http://dmv.ca.gov)
  * Notice that .co.uk domains (and similar) need to be named completely, this is a bug, but the fixing of it would require much more logic or hard coding every single tld, so please name your datasources fully.

### How to upload different file types

  * Open Multiattach controller (Plugins/Multiattach/Controller/MultiattachController.php)
  * Look for the _uploadFiles method 
  * Look for the $allowed = array(...
  * Add the mime type to the list

### How to upload bigger files

  * Edit the post_max_size setting in your php.ini
  * Edit the upload_max_filesize setting in your php.ini

## Feedback

  * Feedback is always welcome, this is my first (and hopefully not last) Croogo plugin

## Sources

  * This plugin uses part of this code: http://www.jamesfairhurst.co.uk/posts/view/uploading_files_and_images_with_cakephp
  * This plugin uses most of this code: https://gist.github.com/bchapuis/1562272
  * This plugin uses this code: http://github.com/edap/cakePHP-youtube-datasource
 
