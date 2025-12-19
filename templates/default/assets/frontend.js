/**
 * Default form JS (moved from assets/frontend.js for template-based loading)
 */

(function() {
	'use strict';
	console.log('[RAE] default template JS loaded');

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
		const agreementCheckbox = form.querySelector('input[name="agreement"]');
		const honeypotInput = form.querySelector('input[name="website"]');
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

		// Validate agreement checkbox if present
		if (agreementCheckbox && !agreementCheckbox.checked) {
			showMessage(messageEl, raeConfig.messages.agreement || 'Please accept the agreement', 'error');
			return;
		}

		// Show loading state
		submitBtn.disabled = true;
        
		// Hide all form elements
		const formGroup = form.querySelector('.rae-form-group');
		const agreementEl = form.querySelector('.rae-agreement');
		const headingEl = form.querySelector('.rae-form-heading, .rae-fortune-form-heading');
		const subheadingEl = form.querySelector('.rae-form-subheading, .rae-fortune-form-subheading');
        
		if (formGroup) formGroup.style.display = 'none';
		if (agreementEl) agreementEl.style.display = 'none';
		if (headingEl) headingEl.style.display = 'none';
		if (subheadingEl) subheadingEl.style.display = 'none';
        
		// Show loading spinner
		loadingEl.style.display = 'flex';
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
					website: honeypotInput ? honeypotInput.value : '',
					additional_data: {}
				})
			});

			const data = await response.json();

			if (response.ok && data.success) {
				// Hide loading spinner
				if (loadingEl) loadingEl.style.display = 'none';
                
				// Show success message centered
				showSuccessMessage(messageEl, data.message || raeConfig.messages.success);
			} else {
				// Restore form on error
				const formGroup = form.querySelector('.rae-form-group');
				const agreementEl = form.querySelector('.rae-agreement');
				const headingEl = form.querySelector('.rae-form-heading, .rae-fortune-form-heading');
				const subheadingEl = form.querySelector('.rae-form-subheading, .rae-fortune-form-subheading');
                
				if (formGroup) formGroup.style.display = '';
				if (agreementEl) agreementEl.style.display = '';
				if (headingEl) headingEl.style.display = '';
				if (subheadingEl) subheadingEl.style.display = '';
                
				showMessage(messageEl, data.message || raeConfig.messages.error, 'error');
			}
		} catch (error) {
			// Restore form on error
			const formGroup = form.querySelector('.rae-form-group');
			const agreementEl = form.querySelector('.rae-agreement');
			const headingEl = form.querySelector('.rae-form-heading, .rae-fortune-form-heading');
			const subheadingEl = form.querySelector('.rae-form-subheading, .rae-fortune-form-subheading');
            
			if (formGroup) formGroup.style.display = '';
			if (agreementEl) agreementEl.style.display = '';
			if (headingEl) headingEl.style.display = '';
			if (subheadingEl) subheadingEl.style.display = '';
            
			showMessage(messageEl, raeConfig.messages.error, 'error');
		} finally {
			submitBtn.disabled = false;
			loadingEl.style.display = 'none';
		}
	}

	function showSuccessMessage(element, message) {
		element.innerHTML = message;
		element.className = 'rae-message rae-message--success rae-message--centered';
		element.style.display = 'block';
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
