<?php
                               
$settings['controller']=compact('node_id','node_type','length','filter');
$settings['view']=compact('container_class','single_class','thumbnail_alias','html5','link');
$settings_defaults['controller']=array(
    'node_id'=>0,
    'node_type'=>'node',
    'length'=>4,
    'filter'=>'mime:#image#i;',
   );
$settings_defaults['view']=array(
    'container_class'=>'element_container',
    'single_class'=>'element_photo',
    'thumbnail_alias'=>'thumbnail',
    'html5'=>false,
    'link'=>'0',
);
// fill the settings with the defaults where required
$settings['controller']=$settings['controller']+$settings_defaults['controller'];
$settings['view']=$settings['view']+$settings_defaults['view'];

// request the photos
$photos = $this->requestAction(
            array('admin'=>false,'plugin'=>'Multiattach','controller'=>'Multiattach','action'=>'getLatest'),
            array('named' => $settings['controller'])
        );

$htmlTagPhoto=($settings['view']['html5']=='1')?'figure':'div';
?>
<div class="<?php echo $settings['view']['container_class']; ?>">
<?php
foreach ($photos as $photo) {
    $photo_url=array(
        'plugin'=>'Multiattach',
	'controller'=>'Multiattach',
	'action'=>'displayFile', 
	'admin'=>false,
	'filename'=>$photo["Multiattach"]['filename']
	);
    $thumb=$this->Html->url($photo_url+array('dimension'=>$settings['view']['thumbnail_alias']));
    $rel=NULL;
    switch($settings['view']['link']){
        default:
            if(substr($settings['view']['link'],0,1)=="/")
                $pre_link=$this->base.$settings['view']['link'];
            else
                $pre_link=$settings['view']['link'];
            
            $link=$pre_link;
            break;
        case '0':
            $link=NULL;
            break;
        case 'lightbox':
            $rel='rel="lightbox[element]"';
            $link=$this->Html->url($photo_url+array('dimension'=>'screen'));
            break;
        case 'photo':
            $link=$this->Html->url($photo_url+array('dimension'=>'normal'));
            break;
        case 'node':
            $link=$this->Html->url(array('plugin'=>'nodes','controller'=>'nodes','action'=>'view',$photo["Multiattach"]['node_id']));
            break;
    }
    
    ?>
    <<?php echo $htmlTagPhoto; ?> class="<?php echo $settings['view']['single_class'] ?>">
        <?php if($link!=NULL){ ?><a <?php echo $rel; ?> href="<?php echo $link; ?>"><?php } ?>
            <img src="<?php echo $thumb;?>" alt="<?php echo Configure::read('Site.title'); ?>" >
        <?php if($link!=NULL){ ?></a><?php } ?>
    </<?php echo $htmlTagPhoto; ?>>
    <?php
}
?>
</div>

