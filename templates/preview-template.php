<?php
/**
 * Assemble necessary params for preview API route
 *
 * @package wp-decoupled-preview
 */

$preview_id      = isset( $_GET['preview_id'] ) ? sanitize_text_field( wp_unslash( $_GET['preview_id'] ) ) : false;
$preview_site_id = isset( $_GET['decoupled_preview_site'] ) ? sanitize_text_field( wp_unslash( $_GET['decoupled_preview_site'] ) ) : false;
$nonce           = isset( $_GET['preview_nonce'] ) ? sanitize_text_field( wp_unslash( $_GET['preview_nonce'] ) ) : false;

if ( ! wp_verify_nonce( $nonce, 'post_preview_' . $preview_id ) ) {
	wp_die( 'Unable to preview: invalid nonce' );
}

$preview_post      = get_post( $preview_id );
$revision          = wp_get_post_autosave( $preview_id, get_current_user_id() );
$preview_post_type = get_post_type( $preview_post );

$preview_helper = new Decoupled_Preview_Settings();
$preview_site   = $preview_helper->get_preview_site( $preview_site_id );

$redirect = "{$preview_site['url']}?secret={$preview_site['secret_string']}&uri={$preview_post->post_name}&id={$revision->ID}&content_type={$preview_post_type}";
?>

<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta http-equiv="X-UA-Compatible" content="ie=edge">
	<title>Decoupled Preview</title>

	<script>
		<?php
		if ( $preview_site['url'] ) {
			// Redirecting via JS because the page headers have already been set by
			// the time we get into this template so PHP wont redirect.
			echo 'window.location.replace("' . esc_url_raw( $redirect ) . '");';
		}
		?>
	</script>
</head>

<body>
	<h1>Redirecting to preview site...</h1>
</body>

<?php wp_footer(); ?>

</html>
