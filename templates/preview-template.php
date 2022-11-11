<?php

// TODO - Add code here to determine the appropriate preview URL to redirect to.
// This should be similar to what we do to construct the URL for the menu.
// $preview_api_url = ...

$preview_id = $_GET['preview_id'];
$revisions = wp_get_post_revisions( $post_id );
print_r($revisions);
?>

<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta http-equiv="X-UA-Compatible" content="ie=edge">
	<title>Decoupled Preview</title>

	<!-- <script>
		<?php
			// if ( $preview_api_url ) {
			// 	// Redirecting via JS because the page headers have already been set by the time we get into this template so PHP wont redirect.
			// 	echo 'window.location.replace("'. $preview_api_url .'");';
			// }
		?>
	</script> -->
</head>

<body>
        <h1>Custom Preview</h1>
        Preview ID: <?php echo $preview_id ?>
</body>

<?php wp_footer(); ?>

</html>
