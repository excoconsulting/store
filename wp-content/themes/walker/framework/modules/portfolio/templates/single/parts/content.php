<div class="edgtf-content-item edgtf-portfolio-info-item">
    <h3 itemprop="name" class="edgtf-portfolio-item-title entry-title"><?php the_title(); ?></h3>
    <div class="edgtf-portfolio-content">
        <?php the_content(); ?>
    </div>
    <div class="edgtf-portfolio-item-author">
        <span class="edgtf-portfolio-author-label"><?php esc_html_e('Illustration by:','walker'); ?></span>
        <span itemprop="author" class="edgtf-portfolio-author-name"><?php the_author_link(); ?></span>
    </div>
</div>