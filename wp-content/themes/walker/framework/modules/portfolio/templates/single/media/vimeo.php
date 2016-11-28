<?php if($lightbox) : ?>
    <a itemprop="image" title="<?php echo esc_attr($video_title); ?>" href="<?php echo esc_url('http://vimeo.com/'.$media['video_id']); ?>" data-rel="prettyPhoto[single_pretty_photo]" class="edgtf-portfolio-video-lightbox">
        <div class="edgtf-portfolio-overlay">
            <i class="edgtf-portfolio-play-icon fa fa-play"></i>
        </div>
        <img itemprop="image" width="100%" src="<?php echo esc_url($lightbox_thumb); ?>" alt="<?php echo esc_attr($video_title); ?>"/>
    </a>

<?php else:  ?>
    <div class="edgtf-iframe-video-holder">
        <iframe class="edgtf-iframe-video" src="<?php echo esc_url($media['video_url']); ?>" width="500" height="281" frameborder="281" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>
    </div>
<?php endif; ?>
