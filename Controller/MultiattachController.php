<?php

App::uses('MultiattachAppController', 'Multiattach.Controller');
App::uses('Sanitize', 'Utility');
App::uses('HttpSocket', 'Network/Http');
App::uses('ConnectionManager','Model');

/**
 * Multiattach Controller
 *
 * PHP version 5
 *
 * @category Controller
 * @version  0.1
 * @author   Elias Coronado <coso.del.cosito@gmail.com>
 * @license  http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link     http://www.github.com/ecoreng
 */
class MultiattachController extends MultiattachAppController {

	public $name = 'Multiattach';

	public $uses = array('Setting','Multiattach.Multiattach');
	
	public function beforeFilter(){
		$this->Auth->allow('displayFile');
		parent::beforeFilter();	
		
	}
	
        
	/*
         * Upload selected files
         */
	protected function _uploadFiles($formdata, $itemId = null) {
        // http://www.jamesfairhurst.co.uk/posts/view/uploading_files_and_images_with_cakephp
		// setup dir names absolute and relative
		$folder_url = APP . 'files' . DS;
		$rel_url = 'files' ;
		
		// create the folder if it does not exist
		if(!is_dir($folder_url)) {
			mkdir($folder_url);
		}
			
		// if itemId is set create an item folder
		if($itemId) {
			// set new absolute folder
			
			$folder_url = APP . 'files' . DS .$itemId; 
			// set new relative folder
			$rel_url = 'files' . DS .$itemId;
			// create directory
			if(!is_dir($folder_url)) {
				mkdir($folder_url);
			}
		}
		
		// list of allowed file types
		// gif, jpg, bmp, png, pdf, txt
		// http://filext.com/file-extension/PNG
		$allowed = array(
			'image/gif',
			'image/x-xbitmap',
			'image/gi_',
			'image/jpeg',
			'image/pjpeg',
			'image/jpg',
			'image/jp_',
			'application/jpg',
			'application/x-jpg',
			'image/pjpeg',
			'image/pipeg',
			'image/vnd.swiftview-jpeg',
			'image/x-xbitmap',
			'image/png',
			'application/png',
			'application/x-png',
			'application/pdf',
			'application/x-pdf',
			'application/acrobat',
			'applications/vnd.pdf',
			'text/pdf',
			'text/x-pdf',
			'image/bmp',
			'image/x-bmp',
			'image/x-bitmap',
			'image/x-xbitmap',
			'image/x-win-bitmap',
			'image/x-windows-bmp',
			'image/ms-bmp',
			'image/x-ms-bmp',
			'application/bmp',
			'application/x-bmp',
			'application/x-win-bitmap',
			'image/bmp',
			'image/x-bmp',
			'image/x-bitmap',
			'image/x-xbitmap',
			'image/x-win-bitmap',
			'image/x-windows-bmp',
			'image/ms-bmp',
			'image/x-ms-bmp',
			'application/bmp',
			'application/x-bmp',
			'application/x-win-bitmap',
			'text/plain','application/txt','browser/internal','text/anytext','widetext/plain','widetext/paragraph',
			);
		
		// loop through and deal with the files
		foreach($formdata["name"] as $key => $file) {
			// replace spaces with underscores
			$filenameO = str_replace(' ', '_', $formdata['name'][$key]);
			$filename = md5(str_replace(' ', '_', $formdata['name'][$key]));
			// assume filetype is false
			$typeOK = false;
			// check filetype is ok
			foreach($allowed as $type) {
				if($type == $formdata['type'][$key]) {
					$typeOK = true;
					break;
				}
			}
			// if file type ok upload the file
			if($typeOK) {
				$now="";
				// switch based on error code
				switch($formdata['error'][$key]) {
					case 0:
						// check filename already exists
						if(!file_exists($folder_url . DS . $filename)) {
							// create full filename
							$full_url = $folder_url . DS . $filename;
							$url = $rel_url . DS . $filename;
							// upload the file
							$success = move_uploaded_file($formdata['tmp_name'][$key], $full_url);
						} else {
							// create unique filename and upload file
							ini_set('date.timezone', 'America/Los_Angeles');
							$now = date('Y-m-d-His');
							$full_url = $folder_url . DS . $now.$filename;
							$url = $rel_url . DS . $now.$filename;
							$success = move_uploaded_file($formdata['tmp_name'][$key], $full_url);
						}
						// if upload was successful
						if($success) {
							// save the url of the file
							$result['urls'][] = $url;
							$result['urlO'][] = $itemId.'-'.$now.$filenameO;
							$result['mime'][] = $formdata['type'][$key];
						} else {
							$result['errors'][] = "Error uploaded $filename. Please try again.";
						}
						break;
					case 3:
						// an error occured
						$result['errors'][] = "Error uploading $filename. Please try again.";
						break;
					default:
						// an error occured
						$result['errors'][] = "System error uploading $filename. Contact webmaster.";
						break;
				}
			} elseif($formdata['error'][$key] == 4) {
				// no file was selected for upload
				$result['errors'][] = "No file Selected";
			} else {
				// unacceptable file type
				$result['errors'][] = "$filename cannot be uploaded.";
			}
		}
	return $result;
	}

        /**
         * admin_add_web
         * Attach website from url
         * @param string $node
         */
	public function admin_add_web($node=''){
		$this->components[]='Session';
		$this->layout = 'admin_popup';
		
		if($this->request->is('post')){
			switch($this->request->data["Multiattach"]["step"]){
				case 1:
					// Datasource lookup algorithm
					// Get the parsed url's domain
					// Depending on the number of segments separated by . will do the following 
					// segments: domain
					// 2: youtube.com
					// 3: youtube.co.uk | dmv.ca.gov | ca.gov.ar
					// 4: webiso.andrew.cmu.edu 
					//
					// will look for the following datasources in order from left (most priority) to right (least prority):
					// YoutubeCom, Youtube   (it will favor the YoutubeCom)
					// YoutubeCoUk, CoUk | DmvCaGov, CaGov | CaGoVar, GovAr
					// WebisoAndrewCmuEdu, CmuEdu
					//
					// if everything else fails, the fallback is DefaultWebsite datasource which will try its best
					// to parse the website correctly using opengraph tags, meta tags and/or written data.
					// 
					// .co.uk users will have to live with the CoUk error for the sake of simplicity, sorry guys
				
					$url2parse=Sanitize::clean($this->request->data["Multiattach"]["url2parse"]);
					$datasources=App::objects('Multiattach.Model/Datasource');

					$index=array_search("DefaultWebsite",$datasources);
					unset($datasources[$index]);
					
					$url2parseNS=str_replace("www.",'',$url2parse);
					$url_parts=parse_url($url2parseNS);
					$host=explode(".",strtolower($url_parts['host']));
										
					if(isset($host)) {
						switch(count($host)){
							case "0":
							case "1":
									$busca=false;
								break;
							case "2":
									$busca=array(implode("_",$host),$host[0]);
								break;
							default:
									$busca=array(implode("_",$host),$host[count($host)-2]."_".$host[count($host)-1]);
								break;
						}
						if ($busca !== false) {
							$b0=array_search(Inflector::camelize($busca[0]),$datasources);
							$index=($b0 === FALSE)?array_search(Inflector::camelize($busca[1]),$datasources):$b0;
							if ($index !== false) {
								$dstl=($datasources[$index]);
							} else {
								$dstl="DefaultWebsite";	
							}
						} else {
							$dstl="DefaultWebsite";	
						}
						App::uses($dstl, 'Multiattach.Model/Datasource');
						$ds = new $dstl();
						
						if(method_exists($ds,'findByURL')) {
							$dataArr=$ds->findByURL($url2parse);
						} elseif (method_exists($ds,'findById')) {
							$dataArr=$ds->findById($url2parse);
						} else {
							$dataArr=$ds->find($url2parse);
						}
						if (method_exists($ds,'formatData')) {
							// Implement this in your datasources, so we can pick what we need
							$dataArr=$ds->formatData($dataArr);
							// We need an array with keys:
							// title, description, image, player (all optional)
						}
						$dataArr['url']=$url2parse;
						$this->set('attachmentData',$dataArr);
						//$dataArr=json_encode($dataArr);
						
						// cachear response en tmp/cache/model, buscar metodo original
						// Modelar correctamente el datasource .. $this->Webattach (modelo virtual) ??
						$this->render('Multiattach/WebAttachmentEdit');
					}
					$this->Session->setFlash(__('That is not a valid URL or couldnt parse it.'));
					

					break;
				case 2:
					$dataArray=json_decode($this->request->data["Multiattach"]["data"],true);
					$dataArray["description"]=$this->request->data["Multiattach"]["description"];
					$dataArray["title"]=$this->request->data["Multiattach"]["title"];
					$dataJson=json_encode($dataArray);
					
					$this->Multiattach->create();
					$this->Multiattach->set(array('node_id'=>$node,'filename'=>$dataArray['url'],'comment'=>$dataArray['title'],'mime'=>'application/json','content'=>$dataJson ));
					$this->Multiattach->save();
					$this->render('Multiattach/attachmentReady');
					break;	
			}
		}
		
	}
        /**
         * admin_add
         * Upload files and link them to $node
         * @param int $node
         */
	public function admin_add($node=''){
		$this->helpers[] = "Html";
		$this->components[]='Session';
		
		$this->layout = 'admin_popup';
		if($node=='')
			$node=0;
		if($this->request->is('post')){
			
			switch($this->request->data["Multiattach"]["step"]){
				case '1':
				$fileOK=$this->_uploadFiles($this->request->params['form']['uploads'], $node);
				if(array_key_exists('urls', $fileOK)) {
					if(array_key_exists('errors', $fileOK)){
						$this->Session->setFlash(__('There were some errors in the process of uploading'));
					}
					$attach=array();
					foreach ($fileOK['urls'] as $key => $elemento) {
						$this->Multiattach->create();
						$this->Multiattach->set(array('node_id'=>$node,'real_filename'=>$elemento,'filename'=>$fileOK['urlO'][$key],'mime'=>$fileOK['mime'][$key] ));
						$this->Multiattach->save();
						$attach[]=$this->Multiattach->id;
					}
					$this->Multiattach->recursive=-1;
					$subidos=$this->Multiattach->find('all', array(
						'conditions' => array(
							"Multiattach.id" => $attach
						)
					));
					 $this->set(array('Multiattach'=>$subidos));
					 $this->render('Multiattach/editAttachment');
				} else {
					$this->Session->setFlash(__('Could not upload any file'));
				}
				break;
			case '2':
					foreach($this->request->data["comment"] as $idCom => $texto){
						$att=$this->Multiattach->findById($idCom);
						$this->Multiattach->set($att);	
						$this->Multiattach->saveField('comment',$texto);
					}
					$this->render('Multiattach/attachmentReady');
				break;
			}
		}
		
	}
	/**
         * _getDimension
         * Get $dimension string and try to get a number from that, returns an array with (height,width)
         * @param type $dimension
         * @return array
         */
	protected function _getDimension ($dimension) {
		// This is supposed to trigger an event that we can implement
		// into a plugin to manage thumbnail sizes without having to
		// hardcode them. I havent testet though but its planned for 
		// a future version
		
		$size=Croogo::dispatchEvent('Controller.Multiattach.getDimension', $this, array('dimension' => $dimension));	
		$size=$size->result;
		if ( $size===NULL || $size===false ){
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
				default:
					$size=array(0,0,1);
					break;
			}
		}
		return $size;
	}
	/**
         * _isImage
         * Returns if filetype is an image comparing the mime type to known values
         * @param type $mime
         * @return boolean
         */
	protected function _isImage($mime){
			if(
			   strpos($mime,'image')	!== FALSE ||
			   strpos($mime,'png')  	!== FALSE ||
			   strpos($mime,'jpg')  	!== FALSE ||
			   strpos($mime,'gif')  	!== FALSE ||
			   strpos($mime,'bmp')  	!== FALSE ||
			   strpos($mime,'bitmap') !== FALSE
			   )
			return true;
		else
			return false;
	}
	
        /**
         * _resizeImage
         * Resizes the image given in $filename to $size (array[width,height]), returns the filename of the resized image
         * @param string $filename
         * @param array $size
         * @return string
         */
	protected function _resizeImage($filename,$size){
	$cacheDir = 'files'.DS.'cache'; 
	
    	//https://gist.github.com/bchapuis/1562272
	
		$path=$filename;
		$dst_w=(int)(isset($size[0]))?$size[0]:NULL;
		$dst_h=(int)(isset($size[1]))?$size[1]:NULL;
		$mult=$dst_w+$dst_h; // if $mult=0 then it means no resizing;
		
        $types = array(1 => "gif", "jpeg", "png", "swf", "psd", "wbmp"); // used to determine image type 
         
        $fullpath = APP; 
     
        $url = $fullpath.$path; 
        
        list($w, $h, $type) = getimagesize($url);
        $r = $w / $h;
		if($dst_w != NULL || $dst_h!= NULL){
			$dst_w=(int)(!isset($size[0]))?$dst_h*$r:$size[0];
			$dst_h=(int)(!isset($size[1]))?$dst_w/$r:$size[1];
		} else {
			$dst_w=(int)$w;
			$dst_h=(int)$h;
		}
		$dst_r = $dst_w / $dst_h;
        
        if ($r > $dst_r) {
            $src_w = $h * $dst_r;
            $src_h = $h;
            $src_x = ($w - $src_w) / 2;
            $src_y = 0;
        } else {
            $src_w = $w;
            $src_h = $w / $dst_r;
            $src_x = 0;
            $src_y = ($h - $src_h) / 2;
        }
		if(!is_dir(APP . $cacheDir) || !file_exists(APP . $cacheDir)) {
			mkdir(APP.$cacheDir);	
		}
        $relfile = $cacheDir.DS.(int)$dst_w.'x'.(int)$dst_h.'_'.basename($path); 
        $cachefile = $fullpath.$relfile;
         
		 
        if (file_exists($cachefile)) {
            if (@filemtime($cachefile) >= @filemtime($url)) {
                $cached = true;
            } else {
                $cached = false;
            }
        } else { 
            $cached = false; 
        } 
         
        if (!$cached) { 
            $image = call_user_func('imagecreatefrom'.$types[$type], $url); 
            if (function_exists("imagecreatetruecolor")) {
                $temp = imagecreatetruecolor($dst_w, $dst_h); 
                imagecopyresampled($temp, $image, 0, 0, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h); 
            } else { 
                $temp = imagecreate ($dst_w, $dst_h); 
                imagecopyresized($temp, $image, 0, 0, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h); 
            } 
            call_user_func("image".$types[$type], $temp, $cachefile); 
            imagedestroy($image);
            imagedestroy($temp);
        }
        return $cachefile;
    
	}
        /**
         * displayFile
         * Returns the file, it gets it from outside the webroot, and sets it for download if its not an image
         * @param string $filename
         * @param string $dimension
         * @return file
         * @throws NotFoundException
         */
	public function displayFile($filename,$dimension='normal'){
		$size=$this->_getDimension($dimension);
		$filename=Sanitize::clean($filename);
		$archivo=$this->Multiattach->findByFilename($filename);
		$isImage=$this->_isImage($archivo['Multiattach']['mime']);
		if  (   isset($size[2]) ||
				( !$isImage && strtolower($dimension) != 'normal' )
			 ){
			// Something bad happened with the dimension parameter, someone linked it wrong 
			// (e.g. text files cant have thumbnail size) or the event is not returning the 
			// dimension correctly, so we redirect the client to the normal dimension image. 
			// SEO friendly 302 redirect (moved permanently)
			$this->redirect(array(
						'plugin'=>'Multiattach',
						'controller'=>'Multiattach',
						'action'=>'displayFile', 
						'admin'=>false,
						'dimension'=>'normal',
						'filename'=>$filename
						),array('status' => 302));
		}
		
		
		$ext=explode('.',$filename);
		$ext=$ext[(count($ext)-1)];
		
		if(count($archivo)>0){	
			$this->response->type($archivo['Multiattach']['mime']);
			$this->response->cache('-1 minute', '+2 days');
			if($isImage)
				{
					$img=$this->_resizeImage($archivo['Multiattach']['real_filename'],$size);
					$this->response->file($img,array('download' => false, 'name' =>$filename));
					$this->response->body($img);
				}
			else
				$this->response->file($archivo['Multiattach']['real_filename'],array('download' => true, 'name' =>$filename));
						
			return $this->response;
		} else {
			throw new NotFoundException();
		}
	}
	
        /**
         * admin_AjaxGetAttachmentJson
         * get attachments from node $node_id and return json information
         * @param int $node_id
         */
	public function admin_AjaxGetAttachmentJson($node_id) {
		
		$attachments=$this->Multiattach->find('all',array('recursive'=>-1,'conditions'=>array('node_id'=>$node_id)));
		$this->set('multiattachments',$attachments);
		
		$this->render('Multiattach/admin_ajax_get_attachment_json','json/admin');
	}
	
        /**
         * admin_AjaxKillAttachmentJson
         * Deletes the attachments via ajax, return json status
         * @param type $attachment
         * @param type $node
         */
	public function admin_AjaxKillAttachmentJson($attachment, $node){
		$attachment=Sanitize::paranoid($attachment);
		$node=Sanitize::paranoid($node);
		$attaM=$this->Multiattach->find('first',array('recursive'=>-1,'conditions'=>array('id'=>$attachment,'node_id'=>$node)));
		if (isset($attaM["Multiattach"]["real_filename"]) && $attaM["Multiattach"]["real_filename"] != "") {
			$file=APP.DS.$attaM["Multiattach"]["real_filename"];
			$status=unlink($file)?1:0;
		} else {
			$status=1;	
		}
		$status.=$this->Multiattach->delete($attaM["Multiattach"]["id"])?1:0;
		$status=array('status'=>$status);
		$this->set('status',$status);
		$this->render('Multiattach/admin_ajax_kill_attachment_json','json/admin');	
	}
	
        /**
         * admin_PostCommentAttachmentJson
         * Sets the comment for an attachment
         */
	public function admin_PostCommentAttachmentJson(){
		$id=(int)Sanitize::paranoid($_GET['pk']);
		$value=Sanitize::paranoid($_GET['value'],array(' ','@','_','+','-','$','%','#','!','?','.',',','(',')','+'));
		$this->Multiattach->read(null, $id);
		$this->Multiattach->set('comment',$value);
		$this->Multiattach->save();
		$status=array('status'=>1,'newValue'=>$value);
		$this->set('status',$status);
		$this->render('Multiattach/admin_post_comment_attachment_json', 'json/admin');
	}
}
