/**
 * Move the Decoupled Preview button under the post edit header.
 *
 * @file
 *
 * @package wp-decoupled-preview\Decoupled_Preview_Settings
 */

window.addEventListener(
	"load",
	() => {
    let editorChecks = 0;

    // Ensure that block editor preview button exists, and if so, modify it.
    const checkPreview = () => {
      const previewBlock = document.querySelector( ".block-editor-post-preview__dropdown" );
      const decoupledPreviewBtn = document.getElementById( 'wp-admin-bar-decoupled-preview' );
      // Remove the old Preview button.
      if (previewBlock) {
        previewBlock.removeChild( previewBlock.querySelector( 'button' ) );
        // Add Decoupled Preview Button into the same Preview container.
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
            document.querySelector( '.interface-interface-skeleton__sidebar' ).style.zIndex = 0;
          }
        );
        clearInterval(checkPreviewInterval);
      }
      // Limit the number of checks for the preview button.
      else {
        editorChecks++;
        if (editorChecks > 24) {
          clearInterval(checkPreviewInterval);
        }
      }
    }
    const checkPreviewInterval = setInterval(checkPreview, 200);
	}
);
