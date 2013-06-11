<?php

$settings['controller']=compact('node_id','node_type','length','filter');
$settings['view']=compact('container_class','single_class');
$settings_defaults['controller']=array(
    'node_id'=>0,
    'node_type'=>'node',
    'length'=>1,
    'filter'=>'content[video]:#youtube.com#i;',
    );
$settings_defaults['view']=array(
    'container_class'=>'element_container',
    'single_class'=>'element_video',
);
// fill the settings with the defaults where required
$settings['controller']=$settings['controller']+$settings_defaults['controller'];
$settings['view']=$settings['view']+$settings_defaults['view'];

// request the videos
$videos = $this->requestAction(
            array(
                'admin'=>false,
                'plugin'=>'Multiattach',
                'controller'=>'Multiattach',
                'action'=>'getLatest'
                ),
            array('named' => $settings['controller'])
        );
?>
<div class="<?php echo $settings['view']['container_class']; ?>">
<?php
foreach ($videos as $video) {
    $video=$video["Multiattach"];
    $video['content']=json_decode($video['content'],true);
    ?>
    <div class="<?php echo $settings['view']['single_class'] ?>">
        <iframe src="<?php echo $video["content"]["player"];?>"></iframe>
    </div>
    <?php
}
?>
</div>