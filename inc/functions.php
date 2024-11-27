<?php
/**
 * Pluggable functions
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

if ( ! function_exists( 'is_featured' ) ) :
	/**
	 * Determines whether a post is marked as featured post.
	 *
	 * Featured posts can be queried using the `_is_featured` query meta key. If
	 * the post ID is not given, then The Loop ID for the current post will be used.
	 *
	 * @since 0.1.0
	 *
	 * @param int $post_id Optional. Post ID. Default is the ID of the global `$post`.
	 * @return bool Whether post is featured.
	 */
	function is_featured( int $post_id = 0 ) {
		if ( ! $post_id ) {
			$post_id = get_the_ID();
		}

		$is_featured = get_post_meta( $post_id, '_is_featured', true );

		return apply_filters( 'is_featured', $is_featured, $post_id );
	}
endif;
