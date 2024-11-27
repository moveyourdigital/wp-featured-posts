<?php
/**
 * Add support for WP-GraphQL
 *
 * @package         Featured_Posts
 */

/*
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.
This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.
You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

namespace Featured_Posts;

/**#& hooks */
add_action( 'graphql_register_types', __NAMESPACE__ . '\\graphql_register_types' );
add_filter( 'graphql_post_object_connection_query_args', __NAMESPACE__ . '\\graphql_post_object_connection_query_args', 10, 3 );

/**
 * Support is featured in WP-GraphQL
 */
function graphql_register_types() {
	\register_graphql_fields(
		'Post',
		array(
			'isFeatured' => array(
				'description' => __( 'Whether this post is featured', 'featured-posts' ),
				'type'        => 'boolean',
				'resolve'     => function ( $post ) {
					// phpcs:ignore
					$post_id = $post->databaseId;
					$is_featured = get_post_meta( $post_id, '_is_featured', true );

					return '1' === $is_featured;
				},
			),
		),
	);

	\register_graphql_fields(
		'RootQueryToPostConnectionWhereArgs',
		array(
			'isFeatured' => array(
				'description' => __( 'Only posts that are marked as featured', 'featured-posts' ),
				'type'        => 'boolean',
			),
		)
	);
}


/**
 * Handle post query when asked for featured posts.
 *
 * @param array $query_args The query args.
 * @param mixed $source Something.
 * @param array $args Other args.
 */
function graphql_post_object_connection_query_args(
	$query_args,
	$source,
	$args
) {

	$isset_is_featured = isset( $args['where']['isFeatured'] );

	if ( $isset_is_featured ) {
		$is_featured = $args['where']['isFeatured'];

		if ( $is_featured ) {
			// phpcs:ignore
			$query_args['meta_query'] = array(
				array(
					'key'     => '_is_featured',
					'value'   => '1',
					'compare' => '=',
				),
			);
		} else {
			// phpcs:ignore
			$query_args['meta_query'] = array(
				'relation' => 'OR',
				array(
					'key'     => '_is_featured',
					'value'   => '0',
					'compare' => '=',
				),
				array(
					'key'     => '_is_featured',
					'compare' => 'NOT EXISTS',
				),
			);
		}
	}

	return $query_args;
}
