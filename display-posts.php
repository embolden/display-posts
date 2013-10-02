<?php
/**
 * Plugin Name: Display Posts
 * Plugin URI:
 * Description: A shortcode to display posts
 * Author: Integrity
 * Author URI: http://www.integritystl.com
 * Version: 1.0.2
 */

/**
 * Returns the featured topics selected for the page
 */
class Display_Posts {
	function __construct() {
		add_shortcode( 'display_posts', array( $this, 'output_shortcode' ) );
	}

	function output_shortcode( $atts ) {
		$defaults = array(
			'post_type'      => '',
			'per_page'       => 3,
			'category'       => '',
		);
		extract( shortcode_atts( $defaults, $atts, 'display_posts' ) );

		$paged = ( get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1 );

		$query_args = array(
			'post_type'      => $post_type,
			'posts_per_page' => $per_page,
			'paged'          => $paged,
			'cat'            => get_cat_ID( $category ),
		);

		$query = new WP_Query( $query_args );
		$output = apply_filters( 'display_posts_before_posts', '' );
		while( $query->have_posts() ) : $query->the_post();
			$output .= apply_filters( 'display_posts_output_post', $value = $this->output_post(), $query );
		endwhile;
		wp_reset_postdata();

		$output .= apply_filters( 'display_posts_after_posts', $this->add_pagination( $query ) );

		return $output;
	}

	/**
	 *
	 */
	private function output_post() {
		ob_start();
		?>
		<div class="entry-excerpt">
			<div class="row">
				<div class="span2">
					<?php $this->post_thumbnail(); ?>
				</div>
				<div class="span10">
					<header class="entry-header">
						<h3><a href="<?php the_permalink(); ?>" title="the_title_attr();"><?php the_title(); ?></a></h3>
					</header>
					<div class="featured-content">
						<?php the_excerpt(); ?>
					</div>
				</div>
			</div>
		</div><!-- .entry-excerpt -->
		<?php
		$output = ob_get_contents();
		ob_end_clean();
		return $output;
	}

	/**
	 *
	 */
	private function post_thumbnail() {
		if( has_post_thumbnail( get_the_ID() ) ) {
			the_post_thumbnail( get_the_ID() );
		}else {
			echo '<img src="' . get_template_directory_uri() . '/images/placeholder.png' . '" alt="" class="attachment-post-thumbnail attachment-post-thumbnail-placeholder wp-post-image">';
		}
	}

	/**
	 *
	 */
	private function add_pagination( $query ) {
		//http://codex.wordpress.org/Function_Reference/paginate_links#Examples
		$big = 999999999; // need an unlikely integer

		$links = paginate_links( array(
			'base' => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
			'format' => '?paged=%#%',
			'current' => max( 1, get_query_var('paged') ),
			'total' => $query->max_num_pages,
			'type' => 'array',
		) );

		if( empty( $links ) )
			return;

		ob_start(); ?>
		<div class="pagination">
			<ul>
		<?php foreach( $links as $link ) : ?>
			<li><?php echo $link; ?></li>
		<?php endforeach; ?>
			</ul>
		</div>
		<?php

		$output = ob_get_contents();
		ob_end_clean();

		return $output;
	}
}
new Display_Posts();