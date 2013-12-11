# Croogo: Multiattach Plugin

**Multiattach** is a free, open source, Attachment manager for nodes in the Croogo CMS for PHP, released under [MIT License].

It uses jQuery and ajax for the UI.

## Requirements
  * Croogo 1.5 or higher
  * HTML5 support
  * Javascript support
  * jQuery (included in Croogo 1.5)
  * X-editable (javascript inline editor, included from CDN)
  * jquery-sortable (included) (https://github.com/johnny/jquery-sortable)

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
  * Parsed websites info is stored in JSON inside the database.
  * Website parsers can be added (if you want to attach Vimeo videos, just get a Vimeo datasource and with little changes it can be done)
  * Plugin activation also creates database
  * Allowed mime types editor for file uploads
  * Elements to get the latest photos or video attachments from nodes
  

## Limitations

  * Currently it supports only Youtube for videos and any other website will be parsed from its meta tags. Other websites can be easily added by just creating the datasource and naming it appropriately.
  * Currently Images from parsed websites cant be changed
  * Only created Nodes can have attachments, cant attach at creation time.
 

## To do

  * Attachments on node creation
  * Use of datasources properly (i think right now they are used just as regular php objects), take advantage of caching (?)
  * Edit how file routes are displayed
  * Proper image detection
  * Thumbnail resizer needs to be optional (external)
  * Attach Galleries or Single pics from Facebook
  * Attach from dropbox
  * Attach from Google drive
  * "Private" files optional
  * For non private files, attach to the default attachment model
  * File picker for previously uploaded attachments.
  * * Thumbnail sizes editable
  * * Reroute cache (thumbnails) folder to default cakephp cache folder

## How to retrieve Attachments

Use the Elements: photo_grid, video_grid

Included there are 2 elements called photo_grid and video_grid that take some parameters to build a view of the lastest pictures or videos attached to a custom node_type. To use the elements insert these in to a block:
```
[element:photo_grid plugin="Multiattach" node_id="{0|<int>}" node_type="{<node-type>}"
length="{<int>}" container_class="{<string>}" single_class="{<string>}" html5="{1|0}" 
thumbnail_alias="{thumbnail-alias}" link="{<link>|0|node|photo}" filter="{<field>:<regex to match value>};"]
```
```
[element:video_grid plugin="Multiattach" node_id="{0|<int>}" node_type="{<node-type>}"
length="{<int>}" container_class="{<string>}" single_class="{<string>}"
filter="{content[<field>]:<regex to match value>};"]
```

  * node_id: (Default: 0) set the node ID from where you want to extract the latest photos, or use 0 (zero) to extract the lastest photos from all nodes OR..
  * node_type: (Default: node) node type from which you want to extract the lastest photos.
  * length: (Default:4 in photo_grid, 1 in video_grid) the MAX number of images to extract, if there arent enough imagen this number wont be reached.
  * container_class: (Default: element_container) the class for the container parent of all the images
  * single_class: (Default: element_photo in photo_grid, element_video in video_grid) the class for the container parent to a single &lt;img&gt; tag
  * html5: (Default: 1, exclusive of photo_grid) 1 to use the &lt;figure&gt; tag instead of &lt;div&gt;, 0 otherwise
  * thumbnail_alias: (Default: thumbnail, exclusive of photo_grid) the alias for the photo size (see below)
  * link: (Default: 0, exclusive for photo_grid) either 0 for no link, node to link to the node that contains that image, photo to link the original version of that file or a sting that contains a relative or absolute url (this last will option will use the same url for all the files).
  * filter: (Default: mime:#image#i; for photo_grid, content[video]:#youtube.com#i; for video_grid) its a parameter formed by the field you want to use to filter the cotnent, and the regex to validate its content. In the video_grid element its going to be more useful if you filter the content of the parsed website (i.e. youtube video) in the "content" field, but inside there are other fields, thats why you should use content[&lt;field&gt;]:&lt;regex to match value&gt;.

Examples of elements:

Create an element which extracts the 5 last photos of all photo-gallery type nodes, the container class will be "gallery" and every single picture should have the "photo-item" class, use &lt;figure&gt; for each picture instead of a &lt;div&gt;, and use the thumbnail alias: square-thumb for the thumbnails which are going to link to "/photo-gallery":

```
[element:photo_grid plugin="Multiattach" node_id="0" node_type="photo-gallery" length="5" container_class="gallery" single_class="photo-item" html5="1" thumbnail_alias="square-thumb" link="/photo-gallery"]
```

Get the last video (default value for length:1, not shown) from node type: video, get me the ones from youtube (default filter value, not shown), the container class will be "videos" and every single video will have a class labeled "video-item"
```
[element:video_grid  plugin="Multiattach" node_type="video" container_class="videos" single_class="video-item"]
```

Use the helper

The Multiattach array is already "linked" to the node model, so use the set method to set the attachments in a helper var
then you can use one of two methods that work similar:
  * $this->Multiattach->filter(array('key' => 'regex to compare value')) : Useful for getting file attachments (compare mime types, filenames, etc.)
  * $this->Multiattach->filterWebContent(array('key' => 'regex to compare value parsed from web')) : Useful for getting web attachments as it compares values parsed from the web (url, player url, title, description, any other.. depends on the datasource)

Get attached videos from youtube:

```
	$this->Helpers->load('Multiattach.Multiattach');
	$this->Multiattach->set($node["Multiattach"]);
	$youtubeVids = $this->Multiattach->filterWebContent(array('player' => '/youtube.com/i'));
	foreach ($youtubeVids as $ytv) {
		?><iframe src="<?php echo $ytv["Multiattach"]["content"]["player"];?>"></iframe><?php
	}
```

Get the attached images:

```
<?php
	$this->Helpers->load('Multiattach.Multiattach');
	$this->Multiattach->set($node["Multiattach"]);
	$images = $this->Multiattach->filter(array('mime'=>'#image#i'));
	$imageF = array(
		'plugin' => 'Multiattach',
		'controller' => 'Multiattach',
		'action' => 'displayFile', 
		'admin' => false,
        );
	foreach ($images as $image) {
		?><img src="<?php echo $this->Html->url($imageF + array('dimension' => 'main_slide', 'filename' => $image["Multiattach"]['filename']) ); ?>" alt="<?php echo $image["Multiattach"]['comment']; ?>" />
		<?php
	}
?>
```

## Extend its use

### How to add more thumbnail sizes:

  * Open the Settings / Multiattach page
  * add a line to the "filesizes available" field
  * Use the format {alias}: {width},{height}
  * One line per filesize alias
  * Example: ``square-thumb:150,150``

### How to add a datasource:

  * Lets say you are implementing a Vimeo datasource
  * Create a datasource as usual
  * Implement any of this methods: findByURL($url), findById($url), find($url)
  * Return an array with the information you want to save, BUT make sure you return the following keys: title, description, image, player (the player url, see [Open graph](http://ogp.me/)'s video specs)
  * Name it appropietly: VimeoCom will have a higher hierarchy than Vimeo, but any will work.
  * Notice that if you create a YoutubeCom and a Youtube, the first will be picked, so try to be as specific as you can (dont add www, but if a subdomain is required DO add it, e.g. DmvCaGov for http://dmv.ca.gov)
  * Notice that .co.uk domains (and similar) need to be named completely, this is a bug, but the fixing of it would require much more logic or hard coding every single tld, so please name your datasources fully.

### How to upload different file types

  * Edit Multiattach settings on the settings menu
  * Add your mime types in a new line
  * Save the config

### How to upload bigger files

  * Edit the post_max_size setting in your php.ini
  * Edit the upload_max_filesize setting in your php.ini

## Feedback

  * Feedback is always welcome, this is my first (and hopefully not last) Croogo plugin

## Sources

  * This plugin uses part of this code: http://www.jamesfairhurst.co.uk/posts/view/uploading_files_and_images_with_cakephp
  * This plugin uses most of this code: https://gist.github.com/bchapuis/1562272
  * This plugin uses this code: http://github.com/edap/cakePHP-youtube-datasource
 
