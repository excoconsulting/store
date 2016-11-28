<?php $sidebar = walker_edge_sidebar_layout(); ?>
<?php get_header(); ?>
<?php 

$blog_page_range = walker_edge_get_blog_page_range();
$max_number_of_pages = walker_edge_get_max_number_of_pages();

if ( get_query_var('paged') ) { $paged = get_query_var('paged'); }
elseif ( get_query_var('page') ) { $paged = get_query_var('page'); }
else { $paged = 1; }

$enable_search_page_sidebar = true;
if(walker_edge_options()->getOptionValue('enable_search_page_sidebar') === "no"){
	$enable_search_page_sidebar = false;
}
?>
<?php walker_edge_get_title(); ?>
	<div class="edgtf-container">
		<?php do_action('walker_edge_after_container_open'); ?>
		<div class="edgtf-container-inner clearfix">
			<div class="edgtf-container">
				<?php do_action('walker_edge_after_container_open'); ?>
				<div class="edgtf-container-inner">
					<?php if($enable_search_page_sidebar) { ?>
					<div class="edgtf-two-columns-75-25 edgtf-content-has-sidebar clearfix">
						<div class="edgtf-column1 edgtf-content-left-from-sidebar">
							<div class="edgtf-column-inner">
					<?php } ?>		
								<div class="edgtf-search-page-holder">
									<form action="<?php echo esc_url(home_url('/')); ?>" class="edgtf-search-page-form" method="get">
										<h2 class="edgtf-search-title"><?php esc_html_e('NEW SEARCH', 'walker'); ?></h2>
				                		<span class="edgtf-search-label"><?php esc_html_e("If you're not happy with the results, please do another search", "walker"); ?></span>
										<div class="edgtf-form-holder">
											<div class="edgtf-column-left">
												<input type="text"  name="s" class="edgtf-search-field" autocomplete="off" />
											</div>
											<div class="edgtf-column-right">
												<input type="submit" class="edgtf-search-submit" value="SEARCH" />
											</div>
										</div>
									</form>	
									<?php if(have_posts()) : while ( have_posts() ) : the_post(); ?>
										<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
											<div class="edgtf-post-content">
												<?php if ( has_post_thumbnail() ) { ?>
													<div class="edgtf-post-image">
														<a itemprop="url" href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>">
															<?php the_post_thumbnail(110); ?>
														</a>
													</div>
												<?php } else { ?>
													<div class="edgtf-post-image">
														<a itemprop="url" href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>">
															<img itemprop="image" src="<?php echo EDGE_ASSETS_ROOT.'/img/search_image.png'; ?>" alt="<?php esc_html__("Post Feature Image", "walker"); ?>" />
														</a>
													</div>
												<?php } ?>
												<div class="edgtf-post-title-area">
													<div class="edgtf-post-info">
														<?php walker_edge_post_info(array(
															'author' => 'yes',
															'date' => 'yes',
															'comments' => 'yes'
														)) ?>
													</div>
													<h3 itemprop="name" class="entry-title"><a itemprop="url" href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>"><?php the_title(); ?></a></h3>
													<?php
														$my_excerpt = get_the_excerpt();
														if ($my_excerpt != '') { ?>
															<p itemprop="description" class="edgtf-post-excerpt"><?php echo esc_html($my_excerpt); ?></p>
														<?php }
													?>
												</div>
											</div>
										</article>
									<?php endwhile; ?>
									<?php
										if(walker_edge_options()->getOptionValue('pagination') == 'yes') {
											walker_edge_pagination($max_number_of_pages, $blog_page_range, $paged);
										}
									?>
									<?php else: ?>
									<div class="entry">
										<p><?php esc_html_e('No posts were found.', 'walker'); ?></p>
									</div>
									<?php endif; ?>
								</div>
								<?php do_action('walker_edge_page_after_content'); ?>
					<?php if($enable_search_page_sidebar) { ?>			
							</div>
						</div>
						<div class="edgtf-column2">
							<?php get_sidebar(); ?>
						</div>
					</div>
					<?php } ?>
				<?php do_action('walker_edge_before_container_close'); ?>
				</div>
			</div>
		</div>
		<?php do_action('walker_edge_before_container_close'); ?>
	</div>
<?php get_footer(); ?>