<?php
/**
 * Plugin Name:        Template Jump
 * Plugin URI:         https://github.com/littleroomstudio/template-jump
 * GitHub Plugin URI:  https://github.com/littleroomstudio/template-jump
 * Description:        Adds an Edit Template link to the WordPress admin bar for block themes.
 * Version:            1.0.0
 * Author:             Jason Cosper
 * Author URI:         https://github.com/boogah
 * License:            GPL-3.0-or-later
 * License URI:        https://www.gnu.org/licenses/gpl-3.0.html
 * Requires PHP:       8.3
 * Requires at least:  6.8
 * Text Domain:        template-jump
 *
 * @package Template_Jump
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Adds the "Edit Template" link to the admin bar under the "Edit" node.
 *
 * @param WP_Admin_Bar $wp_admin_bar The WP_Admin_Bar instance.
 */
function template_jump_add_edit_link( $wp_admin_bar ) {
	// Only run on the front end for authenticated users with appropriate capabilities.
	if ( is_admin() || ! current_user_can( 'edit_theme_options' ) ) {
		return;
	}

	// Ensure the current theme is a block theme (FSE).
	if ( ! wp_is_block_theme() ) {
		return;
	}

	// Support all singular post types as requested (Pages, Posts, CPTs).
	if ( ! is_singular() ) {
		return;
	}

	// Retrieve the active block template ID.
	// WordPress sets this global during the template rendering process in block themes.
	global $_wp_current_template_id;

	// If the active template ID is not set, we cannot link directly to it.
	if ( empty( $_wp_current_template_id ) ) {
		return;
	}

	// Construct the direct link to the template in the Site Editor.
	// Including 'canvas=edit' ensures it opens the editor directly rather than the list.
	$edit_template_url = add_query_arg(
		array(
			'postType' => 'wp_template',
			'postId'   => $_wp_current_template_id,
			'canvas'   => 'edit',
		),
		admin_url( 'site-editor.php' )
	);

	// Add the node as a child of the existing "edit" node (which handles "Edit Post", "Edit Page", etc.).
	$wp_admin_bar->add_node(
		array(
			'id'     => 'edit-active-template',
			'parent' => 'edit',
			'title'  => __( 'Edit Template', 'template-jump' ),
			'href'   => $edit_template_url,
		)
	);
}

// Hook into admin_bar_menu with a late priority to ensure the parent "edit" node already exists.
add_action( 'admin_bar_menu', 'template_jump_add_edit_link', 100 );
