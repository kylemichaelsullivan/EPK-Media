<?php
/**
 * Render EPK Media Tab: Video.
 *
 * @since 1.0.0
 */

$slug = 'video';
$plural = 'videos';
$mime_type = 'video';
$Media = ucwords($mime_type);
$media_library_url = '/wp-admin/upload.php';
?>

<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="epk-media-form" id="<?php echo "epk-$plural"; ?>">
	<?php $q = new WP_Query(array(
		'post_type'      => 'attachment',
		'post_status'    => 'inherit',  # ABSOLUTELY REQUIRED!
		'posts_per_page' => -1,
		'post_mime_type' => $mime_type, # ABSOLUTELY REQUIRED!
		'orderby'        => array(
			'menu_order' => 'ASC',
			'name'       => 'ASC',
		),
	));

	if( $q->have_posts() ) : $selected = array(); $unselected = array(); ?>
		<?php wp_nonce_field("update_epk_media_nonce", 'nonce_field'); # stores nonce to verify submission ?>
		<?php echo '<input type="hidden" name="action" value="update_epk_media">'; # stores PHP processing function ?>
		<?php printf('<input type="hidden" name="epk_media_type" value="%s" />', $mime_type); # stores mime type ?>

		<?php while( $q->have_posts() ) : $q->the_post();
			$id = get_the_ID();
			get_field( 'render', $id ) ? $selected[] = $id : $unselected[] = $id;
		endwhile; ?>

		<?php printf('<div class="epk-medias-container epk-medias-container-selected %1$s-container" id="selected-%1$s">', $plural); ?>
			<?php echo '<input type="hidden" name="epk-media" id="epk-media" />'; # stores JSON of selected media ?>
			<?php if(! count($selected)) : ?>
				<?php printf('<p class="instructions no-media">Please add %1$s from the box below.</p>', $plural); ?>

			<?php else : ?>
				<?php printf('<p class="instructions">The %s in this box will be rendered on the homepage in this order.</p>', $plural); ?>

				<?php foreach($selected as $id) :
					$p = get_post($id);
					$title = get_the_title($id);
					$img = get_site_url() . "/wp-includes/images/media/$slug.png";
					$src = wp_get_attachment_url($id);
					$menu_order = $p->menu_order;
					$href = "$media_library_url?item=$id";
					?>
					<?php printf('<div class="epk-media-container %1$s-container" data-id="%2$s" data-order="%3$s" id="selected-%2$s">', $slug, $id, $menu_order ); ?>
						<?php printf( '<img src="%s" alt="%s" />', $img, $title ); ?>
						
						<div class="epk-media-meta">
							<?php printf( '<p>Title: %s</p>', $title ); ?>
							<?php printf( '<p>URL: <a href="%1$s" title="See this %2$s">%1$s</a></p>', $src, $Media ); ?>
							<?php printf( '<p>Order: %s', $menu_order ); ?>
								<?php printf( '<p>Edit: <a href="%s" title="Edit this %s">%s</a></p>', $href, $Media, $id ); ?>
						</div>

						<div class=<?php echo "epk-media-controls"; ?>>
							<div class="reorder-buttons">
								<button type="button" class="move-up" title="Move Up">&#8593;</button>
								<button type="button" class="move-down" title="Move Down">&#8595;</button>
							</div>

							<div class="toggle-render checkbox-wrapper">
								<input type="checkbox" class="checkbox" title="Toggle Render" checked>
							</div>
						</div>
					</div>
				<?php endforeach; ?>
			<?php endif; ?>
		</div>

		<?php if(count($unselected)) : ?>
			<?php printf('<div class="epk-medias-container epk-medias-container-unselected %1$s-container" id="unselected-%1$s">', $plural); ?>
				<p class="instructions">These <?php echo $plural; ?> are not displaying on the homepage, but they can by clicking the desired <?php echo $mime_type; ?>.</p>
				<div class='epk-media-grid'>
					<?php foreach($unselected as $id) :
						$title = get_the_title($id);
						$src = wp_get_attachment_url($id);
						$menu_order = get_post_field('menu_order', $id) ?? 0;
	
						printf( '<div class="epk-media-single default-media" title="Add %1$s" data-src="%2$s" data-order="%3$s" id="unselected-%4$s"><div class="default-media-img"></div><div class="default-media-title">%1$s</div></div>', $title, $src, $menu_order, $id );
					endforeach; ?>
				</div>
			</div>
		<?php endif; ?>
		
		<div class="buttons">
			<button type="button" title="Reset">Reset</button>
			<button type="submit" name="submit" title="Save">Save</button>
		</div>
	</div>

	<?php else : ?>
		<?php printf('<p class="no-media">Please try adding a %s to the <a href="%s" title="Media Library">Media Library</a>.</p>', $slug, $media_library_url); ?>
	<?php endif; ?>
	<?php wp_reset_postdata(); ?>
</form>