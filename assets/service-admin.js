/**
 * Admin JavaScript for Service CPT
 * Handles dynamic field loading when service type is selected
 */

(function() {
    'use strict';

    document.addEventListener('DOMContentLoaded', function() {
        // JSON toggle functionality
        var toggleJsonBtn = document.getElementById('rae-toggle-json');
        var jsonPreview = document.getElementById('rae-json-preview');
        
        if (toggleJsonBtn && jsonPreview) {
            toggleJsonBtn.addEventListener('click', function() {
                if (jsonPreview.style.display === 'none') {
                    jsonPreview.style.display = 'block';
                    toggleJsonBtn.textContent = raeServiceAdmin.hideJsonText;
                } else {
                    jsonPreview.style.display = 'none';
                    toggleJsonBtn.textContent = raeServiceAdmin.showJsonText;
                }
            });
        }

        // Load service fields button
        var loadFieldsBtn = document.getElementById('rae-load-service-fields');
        var serviceSelect = document.getElementById('rae_service_type');
        var fieldsContainer = document.getElementById('rae-service-fields-container');
        
        if (loadFieldsBtn && serviceSelect && fieldsContainer) {
            loadFieldsBtn.addEventListener('click', function() {
                var serviceSlug = serviceSelect.value;
                
                if (!serviceSlug) {
                    alert(raeServiceAdmin.selectServiceText);
                    return;
                }

                // Show loading state
                loadFieldsBtn.disabled = true;
                loadFieldsBtn.textContent = raeServiceAdmin.loadingText;
                
                // Make AJAX request
                fetch(raeServiceAdmin.ajaxUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: new URLSearchParams({
                        action: 'rae_load_service_fields',
                        nonce: raeServiceAdmin.nonce,
                        service_slug: serviceSlug
                    })
                })
                .then(function(response) {
                    return response.json();
                })
                .then(function(data) {
                    if (data.success) {
                        fieldsContainer.innerHTML = data.data.html;
                    } else {
                        alert(data.data.message || raeServiceAdmin.errorText);
                    }
                })
                .catch(function(error) {
                    console.error('Error loading service fields:', error);
                    alert(raeServiceAdmin.errorText);
                })
                .finally(function() {
                    loadFieldsBtn.disabled = false;
                    loadFieldsBtn.textContent = raeServiceAdmin.loadFieldsText;
                });
            });

            // Auto-load fields when service type changes (optional enhancement)
            serviceSelect.addEventListener('change', function() {
                var serviceSlug = this.value;
                
                if (serviceSlug && loadFieldsBtn) {
                    // You can optionally auto-trigger the load here
                    // loadFieldsBtn.click();
                }
            });
        }
    });

})();
