<ul class="subsubsub">
	<li><a href="<?php echo add_query_arg( 'filter', 'subscriptions' ); ?>"<?php wc_subs_exporter_archives_quicklink_current( 'subscriptions' ); ?>><?php _e( 'Subscriptions', 'wc-subs-exporter' ); ?> <span class="count">(<?php wc_subs_exporter_archives_quicklink_count( 'subscriptions' ); ?>)</span></a></li>
</ul>
<br class="clear" />
<form action="" method="GET">
	<table class="widefat fixed media" cellspacing="0">
		<thead>

			<tr>
				<th scope="col" id="icon" class="manage-column column-icon"></th>
				<th scope="col" id="title" class="manage-column column-title"><?php _e( 'Filename', 'wc-subs-exporter' ); ?></th>
				<th scope="col" class="manage-column column-type"><?php _e( 'Type', 'wc-subs-exporter' ); ?></th>
				<th scope="col" class="manage-column column-author"><?php _e( 'Author', 'wc-subs-exporter' ); ?></th>
				<th scope="col" id="title" class="manage-column column-title"><?php _e( 'Date', 'wc-subs-exporter' ); ?></th>
			</tr>

		</thead>
		<tfoot>

			<tr>
				<th scope="col" class="manage-column column-icon"></th>
				<th scope="col" class="manage-column column-title"><?php _e( 'Filename', 'wc-subs-exporter' ); ?></th>
				<th scope="col" class="manage-column column-type"><?php _e( 'Type', 'wc-subs-exporter' ); ?></th>
				<th scope="col" class="manage-column column-author"><?php _e( 'Author', 'wc-subs-exporter' ); ?></th>
				<th scope="col" class="manage-column column-title"><?php _e( 'Date', 'wc-subs-exporter' ); ?></th>
			</tr>

		</tfoot>
		<tbody id="the-list">

<?php if ( $files ) { ?>
	<?php foreach( $files as $file ) { ?>
			<tr id="post-<?php echo $file->ID; ?>" class="author-self status-<?php echo $file->post_status; ?>" valign="top">
				<td class="column-icon media-icon">
					<?php echo $file->media_icon; ?>
				</td>
				<td class="post-title page-title column-title">
					<strong><a href="<?php echo $file->guid; ?>" class="row-title"><?php echo $file->post_title; ?></a></strong>
					<div class="row-actions">
						<span class="view"><a href="<?php echo get_edit_post_link( $file->ID ); ?>" title="<?php _e( 'Edit', 'wc-subs-exporter' ); ?>"><?php _e( 'Edit', 'wc-subs-exporter' ); ?></a></span> | 
						<span class="trash"><a href="<?php echo get_delete_post_link( $file->ID, '', true ); ?>" title="<?php _e( 'Delete Permanently', 'wc-subs-exporter' ); ?>"><?php _e( 'Delete', 'wc-subs-exporter' ); ?></a></span>
					</div>
				</td>
				<td class="title">
					<a href="<?php echo add_query_arg( 'filter', $file->export_type ); ?>"><?php echo $file->export_type_label; ?></a>
				</td>
				<td class="author column-author"><?php echo $file->post_author_name; ?></td>
				<td class="date column-date"><?php echo $file->post_date; ?></td>
			</tr>
	<?php } ?>
<?php } else { ?>
			<tr id="post-<?php echo $file->ID; ?>" class="author-self" valign="top">
				<td colspan="3" class="colspanchange"><?php _e( 'No past exports found.', 'wc-subs-exporter' ); ?></td>
			</tr>
<?php } ?>

		</tbody>
	</table>
</form>