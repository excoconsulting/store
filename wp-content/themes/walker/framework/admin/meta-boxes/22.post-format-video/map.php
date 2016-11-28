<?php

/*** Video Post Format ***/

$video_post_format_meta_box = walker_edge_add_meta_box(
	array(
		'scope' =>	array('post'),
		'title' => 'Video Post Format',
		'name' 	=> 'post_format_video_meta'
	)
);

walker_edge_add_meta_box_field(
	array(
		'name'        => 'edgtf_video_type_meta',
		'type'        => 'select',
		'label'       => 'Video Type',
		'description' => 'Choose video type',
		'parent'      => $video_post_format_meta_box,
		'default_value' => 'youtube',
		'options'     => array(
			'youtube' => 'Youtube',
			'vimeo' => 'Vimeo',
			'self' => 'Self Hosted'
		),
		'args' => array(
		'dependence' => true,
		'hide' => array(
			'youtube' => '#edgtf_edgtf_video_self_hosted_container',
			'vimeo' => '#edgtf_edgtf_video_self_hosted_container',
			'self' => '#edgtf_edgtf_video_embedded_container'
		),
		'show' => array(
			'youtube' => '#edgtf_edgtf_video_embedded_container',
			'vimeo' => '#edgtf_edgtf_video_embedded_container',
			'self' => '#edgtf_edgtf_video_self_hosted_container')
	)
	)
);

$edgtf_video_embedded_container = walker_edge_add_admin_container(
	array(
		'parent' => $video_post_format_meta_box,
		'name' => 'edgtf_video_embedded_container',
		'hidden_property' => 'edgtf_video_type_meta',
		'hidden_value' => 'self'
	)
);

$edgtf_video_self_hosted_container = walker_edge_add_admin_container(
	array(
		'parent' => $video_post_format_meta_box,
		'name' => 'edgtf_video_self_hosted_container',
		'hidden_property' => 'edgtf_video_type_meta',
		'hidden_values' => array('youtube', 'vimeo')
	)
);



walker_edge_add_meta_box_field(
	array(
		'name'        => 'edgtf_post_video_id_meta',
		'type'        => 'text',
		'label'       => 'Video ID',
		'description' => 'Enter Video ID',
		'parent'      => $edgtf_video_embedded_container,

	)
);


walker_edge_add_meta_box_field(
	array(
		'name'        => 'edgtf_post_video_image_meta',
		'type'        => 'image',
		'label'       => 'Video Image',
		'description' => 'Upload video image',
		'parent'      => $edgtf_video_self_hosted_container,

	)
);

walker_edge_add_meta_box_field(
	array(
		'name'        => 'edgtf_post_video_webm_link_meta',
		'type'        => 'text',
		'label'       => 'Video WEBM',
		'description' => 'Enter video URL for WEBM format',
		'parent'      => $edgtf_video_self_hosted_container,

	)
);

walker_edge_add_meta_box_field(
	array(
		'name'        => 'edgtf_post_video_mp4_link_meta',
		'type'        => 'text',
		'label'       => 'Video MP4',
		'description' => 'Enter video URL for MP4 format',
		'parent'      => $edgtf_video_self_hosted_container,

	)
);

walker_edge_add_meta_box_field(
	array(
		'name'        => 'edgtf_post_video_ogv_link_meta',
		'type'        => 'text',
		'label'       => 'Video OGV',
		'description' => 'Enter video URL for OGV format',
		'parent'      => $edgtf_video_self_hosted_container,

	)
);