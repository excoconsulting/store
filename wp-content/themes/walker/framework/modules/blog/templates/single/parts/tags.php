<?php if(walker_edge_options()->getOptionValue('blog_single_tags') == 'yes' && has_tag()){ ?>
    <div class="edgtf-single-tags-holder">
        <div class="edgtf-tags">
            <?php the_tags('', '', ''); ?>
        </div>
    </div>
<?php } ?>