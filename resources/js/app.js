import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

document.addEventListener('DOMContentLoaded', () => {
    // Standardize any plain text asterisks in labels to red spans
    const highlightMandatoryAsterisks = () => {
        document.querySelectorAll('label').forEach(label => {
            Array.from(label.childNodes).forEach(node => {
                if (node.nodeType === Node.TEXT_NODE && node.nodeValue && node.nodeValue.includes('*')) {
                    const parts = node.nodeValue.split('*');
                    if (parts.length > 1) {
                        const fragment = document.createDocumentFragment();
                        parts.forEach((part, index) => {
                            if (part) fragment.appendChild(document.createTextNode(part));
                            if (index < parts.length - 1) {
                                const star = document.createElement('span');
                                star.className = 'required-mark text-red-600 font-bold ml-1';
                                star.style.cssText = 'color: #dc2626 !important; font-weight: 700; margin-left: 0.25rem;';
                                star.textContent = '*';
                                fragment.appendChild(star);
                            }
                        });
                        label.replaceChild(fragment, node);
                    }
                }
            });
        });
    };

    highlightMandatoryAsterisks();
    // Observe dynamic elements (e.g. Alpine.js modal/step transitions)
    const observer = new MutationObserver(() => {
        highlightMandatoryAsterisks();
    });
    observer.observe(document.body, { childList: true, subtree: true });

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
