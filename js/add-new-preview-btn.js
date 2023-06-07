/**
 * Move the Decoupled Preview button under the post edit header.
 *
 * @file
 *
 * @package wp-decoupled-preview\Decoupled_Preview_Settings
 */

const addNewPreviewButton = () => {
	try {
		const previewBlock    	  = document.querySelector( ".block-editor-post-preview__dropdown" );
		const decoupledPreviewBtn = document.getElementById( 'wp-admin-bar-decoupled-preview' );
		// Remove the old Preview button.
		previewBlock.removeChild( previewBlock.querySelector( 'button' ) );
		// Add Decoupled Preview Button into the same Preview continer.
		previewBlock.appendChild( decoupledPreviewBtn );
		// Hide submenu items by default.
		const previewSubmenu = decoupledPreviewBtn.querySelector( ".ab-sub-wrapper" );
		previewSubmenu.classList.add( "hidden" );
		// Add event listener to toggle submenu items.
		decoupledPreviewBtn.addEventListener(
			"click",
			(e) => {
				wp.data.dispatch( 'core/editor' ).autosave();
				previewSubmenu.classList.toggle( "hidden" );
				previewSubmenu.classList.toggle( "components-popover__content" );
				// Change the edit post side bar z-index.
				document.querySelector( '.interface-interface-skeleton__sidebar' ).classList.toggle( 'interface-z-index-0' );
			}
		);
	} catch(err) {
		console.log("Error adding new preview button, retrying in 100ms")
		setTimeout(addNewPreviewButton, 100)
	}
}

window.addEventListener(
	"load",
	addNewPreviewButton
);
