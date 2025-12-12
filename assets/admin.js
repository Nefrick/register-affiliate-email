/**
 * Admin Panel Scripts
 * Pure vanilla JavaScript - no jQuery
 */

(function() {
    'use strict';

    // Wait for DOM to be ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    function init() {
        initMediaUploader();
    }

    function initMediaUploader() {
        const uploadBtn = document.querySelector('.rae-upload-image');
        const removeBtn = document.querySelector('.rae-remove-image');
        const inputField = document.getElementById('rae_background_image');
        const previewContainer = document.querySelector('.rae-image-preview');

        if (!uploadBtn) return;

        // Upload button click
        uploadBtn.addEventListener('click', function(e) {
            e.preventDefault();

            // Create WordPress media frame
            const frame = wp.media({
                title: 'Select Background Image',
                button: {
                    text: 'Use this image'
                },
                multiple: false,
                library: {
                    type: 'image'
                }
            });

            // When image is selected
            frame.on('select', function() {
                const attachment = frame.state().get('selection').first().toJSON();
                
                // Set input value
                inputField.value = attachment.url;

                // Update preview
                previewContainer.innerHTML = '<img src="' + attachment.url + '" style="max-width: 300px; height: auto;" />';
                
                // Show remove button
                removeBtn.style.display = 'inline-block';
            });

            // Open media frame
            frame.open();
        });

        // Remove button click
        if (removeBtn) {
            removeBtn.addEventListener('click', function(e) {
                e.preventDefault();
                
                // Clear input
                inputField.value = '';
                
                // Clear preview
                previewContainer.innerHTML = '';
                
                // Hide remove button
                removeBtn.style.display = 'none';
            });
        }
    }
})();
