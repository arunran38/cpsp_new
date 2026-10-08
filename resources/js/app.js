import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

document.addEventListener('DOMContentLoaded', () => {
	document.querySelectorAll('form').forEach(form => {
		const dateFrom = form.querySelector('input[type="date"][name="date_from"]');
		const dateTo = form.querySelector('input[type="date"][name="date_to"]');

		if (!dateFrom || !dateTo) return;

		const validateDateRange = () => {
			if (dateFrom.value) {
				dateTo.min = dateFrom.value;
			} else {
				dateTo.removeAttribute('min');
			}

			dateTo.setCustomValidity(
				dateFrom.value && dateTo.value && dateTo.value < dateFrom.value
					? 'Date To must be on or after Date From.'
					: ''
			);
		};

		dateFrom.addEventListener('input', validateDateRange);
		dateFrom.addEventListener('change', validateDateRange);
		dateTo.addEventListener('input', validateDateRange);
		dateTo.addEventListener('change', validateDateRange);
		validateDateRange();
	});
});
