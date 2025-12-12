/**
 * Frontend Form Handler
 * Pure vanilla JavaScript - no jQuery
 */

(function() {
    'use strict';

    // Wait for DOM to be ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initForm);
    } else {
        initForm();
    }

    function initForm() {
        const form = document.querySelector('[data-rae-form]');
        if (!form) return;

        form.addEventListener('submit', handleSubmit);
    }

    async function handleSubmit(event) {
        event.preventDefault();

        const form = event.target;
        const emailInput = form.querySelector('input[name="email"]');
        const messageEl = form.querySelector('[data-rae-message]');
        const loadingEl = form.querySelector('[data-rae-loading]');
        const submitBtn = form.querySelector('.rae-submit-button');

        const email = emailInput.value.trim();

        // Validate email
        if (!email) {
            showMessage(messageEl, raeConfig.messages.required, 'error');
            return;
        }

        if (!isValidEmail(email)) {
            showMessage(messageEl, raeConfig.messages.invalid, 'error');
            return;
        }

        // Show loading state
        submitBtn.disabled = true;
        loadingEl.style.display = 'block';
        messageEl.style.display = 'none';

        try {
            // Make request to REST API
            const response = await fetch(raeConfig.apiUrl + 'rae/v1/subscribe', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    email: email,
                    additional_data: {}
                })
            });

            const data = await response.json();

            if (response.ok && data.success) {
                showMessage(messageEl, data.message || raeConfig.messages.success, 'success');
                emailInput.value = '';
            } else {
                showMessage(messageEl, data.message || raeConfig.messages.error, 'error');
            }
        } catch (error) {
            showMessage(messageEl, raeConfig.messages.error, 'error');
        } finally {
            submitBtn.disabled = false;
            loadingEl.style.display = 'none';
        }
    }

    function showMessage(element, message, type) {
        element.textContent = message;
        element.className = 'rae-message rae-message--' + type;
        element.style.display = 'block';

        // Auto-hide after 5 seconds
        setTimeout(() => {
            element.style.display = 'none';
        }, 5000);
    }

    function isValidEmail(email) {
        const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return regex.test(email);
    }
})();
