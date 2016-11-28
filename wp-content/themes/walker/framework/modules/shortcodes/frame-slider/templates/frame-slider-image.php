<?php if ($link != '') { ?>
	<a href="<?php echo esc_url($link); ?>" target="<?php echo esc_attr($target) ?>">
<?php } if ($image != '') { ?>
	<?php echo wp_get_attachment_image($image,'full'); ?>
<?php } if ($link != '') { ?>
	</a>
<?php } ?>