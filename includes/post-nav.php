<?php

function rsnpp_adjacent_post_template( $adjacent_post, $next_or_prev ) {
	if ( $next_or_prev !== 'previous' ) $next_or_prev = 'next';
	
	global $post;
	$the_real_post = $post;
	$post = $adjacent_post;
	setup_postdata( $adjacent_post );
	
	$excerpt = wp_trim_words( get_the_excerpt(), 13 );
	
	$post_type_obj = get_post_type_object( get_post_type() );
	$singular_term = empty($post_type_obj->labels->singular_name) ? ucwords($post_type_obj->label) : $post_type_obj->labels->singular_name;
	
	?>
	<div class="rsnpp-post-wrap">
		
		<a href="<?php echo esc_attr( get_permalink() ); ?>" title="Read more about: <?php echo esc_attr( get_the_title() ); ?>">
			
			<?php if ( $next_or_prev === 'previous' ) echo '<div class="rsnpp-arrow"><span class="rsnpp-symbol">&larr;</span> <span class="rsnpp-text">Previous '. esc_html($singular_term) .'</span></div>'; ?>
			
			<?php if ( $next_or_prev === 'next' ) echo '<div class="rsnpp-arrow"><span class="rsnpp-text">Next '. esc_html($singular_term) .'</span> <span class="rsnpp-symbol">&rarr;</span></div>'; ?>
			
			<div class="rsnpp-inner">
				
				<?php if ( has_post_thumbnail() ) { ?>
					<div class="rsnpp-thumbnail"><?php the_post_thumbnail( 'thumbnail' ); ?></div>
				<?php } ?>
				
				<div class="rsnpp-content">
					
					<div class="rsnpp-title"><?php the_title(); ?></div>
					
					<?php if ( $excerpt ) { ?>
						<div class="rsnpp-excerpt"><?php echo esc_html( $excerpt ); ?></div>
					<?php } ?>
					
					<?php if ( $time = get_the_time('U') ) { ?>
					<div class="rsnpp-date">Posted <?php echo date(get_option('date_format'), $time); ?></div>
					<?php } ?>
		
				</div>
			
			</div>
		
		</a>
	
	</div>
	<?php
	
	$post = $the_real_post;
	setup_postdata( $post );
}

add_action( 'rsnpp_adjacent_post_template', 'rsnpp_adjacent_post_template', 10, 2 );

function rsnpp_display_post_nav( $in_same_term = false, $excluded_terms = '', $taxonomy = 'category' ) {
	$previous_post = get_adjacent_post( $in_same_term, $excluded_terms, true, $taxonomy );
	$next_post = get_adjacent_post( $in_same_term, $excluded_terms, false, $taxonomy );
	
	if ( $next_post || $previous_post ) {
		?>
		<div class="rsnpp-post-nav">
			
			<?php if ( $previous_post ) { ?>
				<div class="rsnpp-adjacent-post rsnpp-previous">
					<?php do_action( 'rsnpp_adjacent_post_template', $previous_post, 'previous' ); ?>
				</div>
			<?php } ?>
			
			<?php if ( $next_post ) { ?>
				<div class="rsnpp-adjacent-post rsnpp-next">
					<?php do_action( 'rsnpp_adjacent_post_template', $next_post, 'next' ); ?>
				</div>
			<?php } ?>
		</div>
		<?php
	}
}

function rsnpp_add_next_previous_posts_nav_to_content( $content ) {
	remove_action( 'the_content', 'rsnpp_add_next_previous_posts_nav_to_content', 1100 );
	
	if ( is_singular() && get_post_type() != 'page' && get_post_type() != 'product' ) {
		$in_same_term = false;
		$excluded_terms = false;
		$taxonomy = false;
		
		// Get the most specific term assigned to the object (the term with the most parents)
		$term = rsnpp_get_most_specific_term();
		
		if ( $term ) {
			$in_same_term = true;
			$taxonomy = $term->taxonomy;
		}
		
		$in_same_term = apply_filters( 'rsnpp_in_same_term', $in_same_term );
		$excluded_terms = apply_filters( 'rsnpp_excluded_terms', $excluded_terms );
		$taxonomy = apply_filters( 'rsnpp_taxonomy', $taxonomy );
		
		ob_start();
		rsnpp_display_post_nav( $in_same_term, $excluded_terms, $taxonomy );
		$post_nav = ob_get_clean();
		
		$content = $content . "\n\n" . $post_nav;
	}
	
	add_action( 'the_content', 'rsnpp_add_next_previous_posts_nav_to_content', 1100 );
	
	return $content;
}

add_action( 'the_content', 'rsnpp_add_next_previous_posts_nav_to_content', 1100 );

/**
 * Get the most specific term for an object. Returns a term with the most parents of any assigned term. If multiple terms have the same parents, only one is returned.
 *
 * @param null $post_id
 * @param null $taxonomy
 *
 * @return false|WP_Term
 */
function rsnpp_get_most_specific_term( $post_id = null, $taxonomy = null ) {
	if ( $post_id === null ) $post_id = get_the_ID();
	
	
	if ( $taxonomy ) {
		$taxonomies = array( $taxonomy );
	}else{
		$taxonomies = array_keys( get_object_taxonomies( get_post_type( $post_id ), 'objects' ) );
	}
	
	if ( empty($taxonomies) ) return false;
	
	$highest_depth = -1;
	$most_specific_term = null;
	
	foreach( $taxonomies as $tax_key ) {
		$terms = get_the_terms( get_the_ID(), $tax_key );
		
		if ( $terms ) foreach( $terms as $term ) {
			$parent = $term->parent;
			$depth = 0;
			
			while ( $parent > 0 ) {
				$t = get_term_by( 'term_id', $parent, $term->taxonomy );
				
				if ( $t ) {
					$parent = $t->parent;
					$depth++;
				}
			}
			
			if ( $depth > $highest_depth ) {
				$highest_depth = $term;
				$most_specific_term = $term;
			}
		}
		
		return $most_specific_term;
	}
	
	return false;
}