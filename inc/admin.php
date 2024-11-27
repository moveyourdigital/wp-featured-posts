<?php
/**
 * WordPress Admin UI
 *
 * @package featured-posts
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
add_action( 'admin_head', __NAMESPACE__ . '\\admin_head' );
add_action( 'admin_enqueue_scripts', __NAMESPACE__ . '\\admin_enqueue_scripts' );
add_filter( 'manage_post_posts_columns', __NAMESPACE__ . '\\manage_post_posts_columns' );
add_action( 'manage_posts_custom_column', __NAMESPACE__ . '\\manage_posts_custom_column', 10, 2 );
add_action( 'wp_ajax_featured_star_toggle', __NAMESPACE__ . '\\wp_ajax_featured_star_toggle' );
add_action( 'pre_get_posts', __NAMESPACE__ . '\\pre_get_posts' );
add_filter( 'views_edit-post', __NAMESPACE__ . '\\views_edit_post' );

/**
 * Inject styles in the admin head
 */
function admin_head() {
	?>
	<style>
		.column-featured {
			width: 35px;
		}

		.column-featured a {
			font-size: 17px;
			cursor: pointer;
		}

		.column-featured .active {
			color: #E8A317;
		}
	</style>
	<?php
}

/**
 * Fires when enqueuing scripts for all admin pages.
 *
 * @param string $hook The page hook.
 */
function admin_enqueue_scripts( $hook ) {
	if ( in_array( $hook, array( 'edit.php' ), true ) ) {
		wp_enqueue_script( 'featured-star', plugin_uri( 'js/featured-star.js' ), array( 'jquery' ), plugin_version(), true );
	}
}

/**
 * Filters the columns displayed in the Posts list table for a specific post type.
 *
 * @param array $columns The columns from the table.
 */
function manage_post_posts_columns( array $columns ) {
	$cb = $columns['cb'];
	unset( $columns['cb'] );

	return array_merge(
		array(
			'cb'       => $cb,
			'featured' => '',
		),
		$columns
	);
}

/**
 * Ajax handler for featured star toggle.
 *
 * @param string $column The column name.
 * @param int    $post_id The row post ID.
 */
function manage_posts_custom_column( $column, $post_id ) {
	switch ( $column ) {
		case 'featured':
			$result = get_post_meta( $post_id, '_is_featured', true );
			$active = '1' === $result;
			?>
			<a data-post-id="<?php echo esc_attr( $post_id ); ?>" data-nonce="<?php echo esc_attr( wp_create_nonce( 'featured_star_toggle' ) ); ?>" class="<?php echo $active ? 'active' : ''; ?>">
				<?php echo ( $active ) ? '★' : '☆'; ?>
			</a>
				<?php
			break;
	}
}

/**
 * Ajax handler for featured star toggle.
 *
 * @param string $action Action to perform.
 */
function wp_ajax_featured_star_toggle( $action ) {
	if ( empty( $action ) ) {
		$action = 'featured_star_toggle';
	}

	if ( isset( $_POST['post_ID'] ) ) {
		$post_id = (int) $_POST['post_ID'];
	}

	if ( ! isset( $_POST['_wpnonce'] ) ) {
		wp_die( -1 );
	}

	$_wpnonce = sanitize_key( $_POST['_wpnonce'] );

	if ( ! wp_verify_nonce( $_wpnonce, $action ) ) {
		wp_die( -1 );
	}

	$active = isset( $_POST['active'] ) && 'true' === $_POST['active'] ? 1 : 0;
	$post   = get_post( $post_id );

	if ( ! $post ) {
		wp_die( -1 );
	}

	if ( ! current_user_can( 'edit_post', $post ) ) {
		wp_die( -1 );
	}

	$active  = apply_filters( 'post_feature_update', $active, $post_id, $post );
	$success = update_post_meta( $post_id, '_is_featured', $active ? '1' : '0' );

	if ( ! $success ) {
		wp_die( -1 );
	}

	wp_die( esc_html( $active ) );
}

/**
 * Filters posts when featured view
 *
 * @param WP_Query $query current query.
 * @return void
 */
function pre_get_posts( $query ) {
	// phpcs:ignore
	$is_featured_view = is_admin() && $query->is_main_query() && isset( $_REQUEST['all_featured'] ) && '1' === $_REQUEST['all_featured'];

	if ( $is_featured_view ) {
		$query->set( 'meta_key', '_is_featured' );
		$query->set( 'meta_value', '1' );
	}
}

/**
 * Filters posts when featured view
 *
 * @param array $views current views.
 * @return array
 */
function views_edit_post( $views ) {
	// phpcs:ignore
	$is_featured_view = array_key_exists( 'all_featured', $_REQUEST ) && '1' === $_REQUEST['all_featured'];

	$query = new \WP_Query(
		array(
			'post_type'  => 'post',
			// phpcs:ignore
			'meta_key'   => '_is_featured',
			// phpcs:ignore
			'meta_value' => '1',
		)
	);

	$count = $query->found_posts;

	return array_merge(
		array(
			'all'      => $views['all'],
			'featured' => sprintf(
				'<a %s href="%s">%s <span class="count">(%s)</span></a>',
				( $is_featured_view ? 'class="current"' : '' ),
				esc_url(
					admin_url( 'edit.php?post_type=post&all_featured=1' )
				),
				esc_html( __( 'Featured', 'featured-posts' ) ),
				$count
			),
		),
		array_slice( $views, 1 )
	);
}
