<?php
/**
 * Custom template tags for this theme.
 *
 * Eventually, some of the functionality here could be replaced by core features.
 *
 * @package Some
 */

/**
 * Prints HTML with meta information for the current post-date/time and author.
 */
function some_posted_on() {
	
	$time_string = '<time class="entry-date published updated" datetime="%1$s">%2$s</time>';
	if ( get_the_time( 'U' ) !== get_the_modified_time( 'U' ) ) {
		$time_string = '<time class="entry-date published" datetime="%1$s">%2$s</time><time class="updated" datetime="%3$s">%4$s</time>';
	}

	$time_string = sprintf( $time_string,
		esc_attr( get_the_date( 'c' ) ),
		esc_html( get_the_date() ),
		esc_attr( get_the_modified_date( 'c' ) ),
		esc_html( get_the_modified_date() )
	);

	$posted_on = sprintf(
		esc_html_x( 'Posted on %s', 'post date', 'some' ),
		'<a href="' . esc_url( get_permalink() ) . '" rel="bookmark">' . $time_string . '</a>'
	);

	$byline = sprintf(
		esc_html_x( 'by %s', 'post author', 'some' ),
		'<span class="author vcard"><a class="url fn n" href="' . esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ) . '">' . esc_html( get_the_author() ) . '</a></span>'
	);

	echo '<span class="posted-on">' . $posted_on . '</span><span class="byline"> ' . $byline . '</span>'; // WPCS: XSS OK.

}

/**
 * This template tag is meant to replace template tags like `the_category()`, `the_terms()`, etc.  These core 
 * WordPress template tags don't offer proper translation and RTL support without having to write a lot of 
 * messy code within the theme's templates.  This is why theme developers often have to resort to custom 
 * functions to handle this (even the default WordPress themes do this). Particularly, the core functions 
 * don't allow for theme developers to add the terms as placeholders in the accompanying text (ex: "Posted in %s"). 
 * This funcion is a wrapper for the WordPress `get_the_terms_list()` function.  It uses that to build a 
 * better post terms list.
 *
 * @author  Justin Tadlock
 * @link    https://github.com/justintadlock/hybrid-core/blob/2.0/functions/template-post.php
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 *
 * @since   1.0.0
 * @param   array   $args
 * @return  string
 */
function some_get_post_terms( $args = array() ) {

	$html = '';

	$defaults = array(
		'post_id'    => get_the_ID(),
		'taxonomy'   => 'category',
		'text'       => '%s',
		'before'     => '',
		'after'      => '',
		'items_wrap' => '<span %s>%s</span>',
		/* Translators: Separates tags, categories, etc. when displaying a post. */
		'sep'        => esc_html_x( ', ', 'taxonomy terms separator', 'some' )
	);

	$args = wp_parse_args( $args, $defaults );

	$terms = get_the_term_list( $args['post_id'], $args['taxonomy'], '', $args['sep'], '' );

	if ( !empty( $terms ) ) {
		$html .= $args['before'];
		$html .= sprintf( $args['items_wrap'], 'class="entry-terms ' . $args['taxonomy'] . '"', sprintf( $args['text'], $terms ) );
		$html .= $args['after'];
	}

	return $html;
}

/**
 * Outputs a post's taxonomy terms.
 *
 * @since  1.0.0
 * @access public
 * @param  array   $args
 * @return void
 */
function some_post_terms( $args = array() ) {
	echo some_get_post_terms( $args );
}

/**
 * Displays the optional custom logo.
 *
 * Does nothing if the custom logo is not available.
 *
 */
function some_the_custom_logo() {
	
	if ( function_exists( 'the_custom_logo' ) ) {
		the_custom_logo();
	}
	
}

/**
 * Displays an optional post thumbnail.
 *
 * Wraps the post thumbnail in an anchor element on index views, or a div
 * element when on single views.
 *
 * @since Some 1.0.1
 */
function some_post_thumbnail( $post_thumbnail = null ) {
	
	if ( post_password_required() || is_attachment() || ! has_post_thumbnail() ) {
		return;
	}
	
	// Set default size.
	if ( null === $post_thumbnail ) {
		$post_thumbnail = 'post-thumbnail';
	}

	if ( is_singular() ) :
	?>

		<div class="post-thumbnail">
			<?php the_post_thumbnail( esc_attr( $post_thumbnail ) ); ?>
		</div><!-- .post-thumbnail -->

	<?php else : ?>

		<a class="post-thumbnail" href="<?php the_permalink(); ?>">
			<?php the_post_thumbnail( esc_attr( $post_thumbnail ), array( 'alt' => the_title_attribute( 'echo=0' ) ) ); ?>
		</a>

	<?php endif; // End is_singular()
}

/**
 * Returns true if a blog has more than 1 category.
 *
 * @return bool
 */
function some_categorized_blog() {
	if ( false === ( $all_the_cool_cats = get_transient( 'some_categories' ) ) ) {
		// Create an array of all the categories that are attached to posts.
		$all_the_cool_cats = get_categories( array(
			'fields'     => 'ids',
			'hide_empty' => 1,
			// We only need to know if there is more than one category.
			'number'     => 2,
		) );

		// Count the number of categories that are attached to the posts.
		$all_the_cool_cats = count( $all_the_cool_cats );

		set_transient( 'some_categories', $all_the_cool_cats );
	}

	if ( $all_the_cool_cats > 1 ) {
		// This blog has more than 1 category so some_categorized_blog should return true.
		return true;
	} else {
		// This blog has only 1 category so some_categorized_blog should return false.
		return false;
	}
}

/**
 * Flush out the transients used in some_categorized_blog.
 */
function some_category_transient_flusher() {
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	// Like, beat it. Dig?
	delete_transient( 'some_categories' );
}
add_action( 'edit_category', 'some_category_transient_flusher' );
add_action( 'save_post',     'some_category_transient_flusher' );
