{{-- Reusable Global Date Validation & Date-Range Picker Utility --}}
<script>
(function() {
    const TODAY_STR = '{{ now()->timezone(config("app.timezone", "Asia/Kolkata"))->format("Y-m-d") }}';

    function getErrorContainer(input, customErrorId) {
        if (customErrorId) {
            const el = document.getElementById(customErrorId);
            if (el) return el;
        }

        const form = input.closest('form') || input.closest('[data-date-container]') || input.parentElement;
        if (form) {
            const rangeError = form.querySelector('#dateRangeError, .dateRangeError, .date-range-error');
            if (rangeError) return rangeError;
        }

        const errorId = (input.id || input.name || 'date_input') + '_error';
        let errorEl = document.getElementById(errorId);
        if (!errorEl && input.parentNode) {
            errorEl = document.createElement('p');
            errorEl.id = errorId;
            errorEl.className = 'date-validation-error text-xs font-semibold text-rose-600 mt-1';
            errorEl.setAttribute('aria-live', 'polite');
            if (input.nextSibling) {
                input.parentNode.insertBefore(errorEl, input.nextSibling);
            } else {
                input.parentNode.appendChild(errorEl);
            }
        }
        return errorEl;
    }

    function showDateError(input, message) {
        input.classList.add('border-rose-500', 'focus:border-rose-500', 'focus:ring-rose-500/20');
        input.classList.remove('border-slate-200', 'border-slate-300', 'focus:border-indigo-500');
        const errorEl = getErrorContainer(input);
        if (errorEl) {
            errorEl.textContent = message;
            errorEl.classList.remove('hidden');
            errorEl.style.display = 'block';
        }
    }

    function clearDateError(input) {
        input.classList.remove('border-rose-500', 'focus:border-rose-500', 'focus:ring-rose-500/20');
        const errorEl = getErrorContainer(input);
        if (errorEl) {
            errorEl.textContent = '';
            errorEl.classList.add('hidden');
            errorEl.style.display = 'none';
        }
    }

    function setupDateRangePair(fromInput, toInput) {
        if (!fromInput || !toInput) return;
        if (fromInput.dataset.dateRangeInit === 'true' && toInput.dataset.dateRangeInit === 'true') return;

        fromInput.dataset.dateRangeInit = 'true';
        toInput.dataset.dateRangeInit = 'true';

        const allowFutureFrom = fromInput.hasAttribute('data-allow-future') || fromInput.getAttribute('data-allow-future') === 'true';
        const allowFutureTo = toInput.hasAttribute('data-allow-future') || toInput.getAttribute('data-allow-future') === 'true';

        // 1. Enforce max to today if future dates are not allowed
        if (!allowFutureFrom && !fromInput.getAttribute('max')) {
            fromInput.setAttribute('max', TODAY_STR);
        }
        if (!allowFutureTo && !toInput.getAttribute('max')) {
            toInput.setAttribute('max', TODAY_STR);
        }

        // 2. Initial synchronization of min/max attributes
        const syncMinMax = () => {
            const fromVal = fromInput.value;
            const toVal = toInput.value;

            if (fromVal) {
                toInput.min = fromVal;
            } else {
                toInput.removeAttribute('min');
            }

            if (toVal) {
                if (!allowFutureFrom) {
                    fromInput.max = toVal < TODAY_STR ? toVal : TODAY_STR;
                } else {
                    fromInput.max = toVal;
                }
            } else if (!allowFutureFrom) {
                fromInput.max = TODAY_STR;
            } else {
                fromInput.removeAttribute('max');
            }
        };

        syncMinMax();

        let isSyncing = false;

        // 3. From Input Event Listener
        const handleFromChange = (e) => {
            if (isSyncing) return;
            isSyncing = true;
            try {
                const fromVal = fromInput.value;
                const toVal = toInput.value;

                // Restrict future date
                if (!allowFutureFrom && fromVal && fromVal > TODAY_STR) {
                    showDateError(fromInput, 'Date cannot be in the future.');
                    fromInput.value = TODAY_STR;
                    try {
                        fromInput.dispatchEvent(new Event('input', { bubbles: true }));
                    } catch (err) {}
                    syncMinMax();
                    return;
                }

                clearDateError(fromInput);

                // Range conflict handling
                if (fromVal && toVal && toVal < fromVal) {
                    toInput.value = '';
                    try {
                        toInput.dispatchEvent(new Event('input', { bubbles: true }));
                    } catch (err) {}
                    showDateError(toInput, 'Date To cannot be earlier than Date From.');
                } else {
                    clearDateError(toInput);
                }

                syncMinMax();
            } finally {
                isSyncing = false;
            }
        };

        // 4. To Input Event Listener
        const handleToChange = (e) => {
            if (isSyncing) return;
            isSyncing = true;
            try {
                const fromVal = fromInput.value;
                const toVal = toInput.value;

                // Restrict future date
                if (!allowFutureTo && toVal && toVal > TODAY_STR) {
                    showDateError(toInput, 'Date cannot be in the future.');
                    toInput.value = TODAY_STR;
                    try {
                        toInput.dispatchEvent(new Event('input', { bubbles: true }));
                    } catch (err) {}
                    syncMinMax();
                    return;
                }

                clearDateError(toInput);

                // Range conflict handling
                if (fromVal && toVal && fromVal > toVal) {
                    fromInput.value = '';
                    try {
                        fromInput.dispatchEvent(new Event('input', { bubbles: true }));
                    } catch (err) {}
                    showDateError(fromInput, 'Date From cannot be later than Date To.');
                } else {
                    clearDateError(fromInput);
                }

                syncMinMax();
            } finally {
                isSyncing = false;
            }
        };

        fromInput.addEventListener('change', handleFromChange);
        fromInput.addEventListener('input', handleFromChange);
        toInput.addEventListener('change', handleToChange);
        toInput.addEventListener('input', handleToChange);
    }

    function setupSingleDateInput(input) {
        if (!input || input.dataset.singleDateInit === 'true') return;
        input.dataset.singleDateInit = 'true';

        const allowFuture = input.hasAttribute('data-allow-future') || input.getAttribute('data-allow-future') === 'true';

        if (!allowFuture && !input.getAttribute('max')) {
            input.setAttribute('max', TODAY_STR);
        }

        const handleInput = () => {
            if (!allowFuture && input.value && input.value > TODAY_STR) {
                showDateError(input, 'Date cannot be in the future.');
                input.value = TODAY_STR;
                try {
                    input.dispatchEvent(new Event('input', { bubbles: true }));
                } catch (err) {}
            } else {
                clearDateError(input);
            }
        };

        input.addEventListener('change', handleInput);
        input.addEventListener('input', handleInput);
    }

    function scanAndInit(root = document) {
        // Pairs to find within forms or containers
        const pairDefinitions = [
            { from: 'date_from', to: 'date_to' },
            { from: 'from_date', to: 'to_date' },
            { from: 'start_date', to: 'end_date' }
        ];

        const forms = root.querySelectorAll ? Array.from(root.querySelectorAll('form, [data-date-container]')) : [];
        const processedInputs = new Set();

        forms.forEach(form => {
            pairDefinitions.forEach(pair => {
                const fromInput = form.querySelector(`input[name="${pair.from}"], input#${pair.from}`);
                const toInput = form.querySelector(`input[name="${pair.to}"], input#${pair.to}`);

                if (fromInput && toInput) {
                    setupDateRangePair(fromInput, toInput);
                    processedInputs.add(fromInput);
                    processedInputs.add(toInput);
                }
            });

            // Check custom data attributes
            const customFrom = form.querySelector('input[data-date-from], [data-range="from"]');
            const customTo = form.querySelector('input[data-date-to], [data-range="to"]');
            if (customFrom && customTo) {
                setupDateRangePair(customFrom, customTo);
                processedInputs.add(customFrom);
                processedInputs.add(customTo);
            }
        });

        // Global fallback for pairs across custom wrappers
        if (root.querySelectorAll) {
            pairDefinitions.forEach(pair => {
                const allFrom = Array.from(root.querySelectorAll(`input[name="${pair.from}"], input#${pair.from}`));
                const allTo = Array.from(root.querySelectorAll(`input[name="${pair.to}"], input#${pair.to}`));

                allFrom.forEach((fromEl, idx) => {
                    if (!processedInputs.has(fromEl)) {
                        const toEl = allTo[idx];
                        if (toEl && !processedInputs.has(toEl)) {
                            setupDateRangePair(fromEl, toEl);
                            processedInputs.add(fromEl);
                            processedInputs.add(toEl);
                        }
                    }
                });
            });
        }

        // Initialize all remaining standalone date inputs
        const allDateInputs = root.querySelectorAll ? root.querySelectorAll('input[type="date"]') : [];
        allDateInputs.forEach(input => {
            if (!processedInputs.has(input)) {
                setupSingleDateInput(input);
            }
        });
    }

    // Guard on form submit
    document.addEventListener('submit', function(e) {
        const form = e.target;
        if (!form || !form.querySelectorAll) return;

        const dateInputs = form.querySelectorAll('input[type="date"]');
        let hasError = false;

        dateInputs.forEach(input => {
            const allowFuture = input.hasAttribute('data-allow-future') || input.getAttribute('data-allow-future') === 'true';
            if (!allowFuture && input.value && input.value > TODAY_STR) {
                showDateError(input, 'Date cannot be in the future.');
                hasError = true;
                input.focus();
            }
        });

        const pairDefinitions = [
            { from: 'date_from', to: 'date_to' },
            { from: 'from_date', to: 'to_date' },
            { from: 'start_date', to: 'end_date' }
        ];

        pairDefinitions.forEach(pair => {
            const from = form.querySelector(`input[name="${pair.from}"], input#${pair.from}`);
            const to = form.querySelector(`input[name="${pair.to}"], input#${pair.to}`);
            if (from && to && from.value && to.value && from.value > to.value) {
                showDateError(to, 'Date To cannot be earlier than Date From.');
                hasError = true;
                to.focus();
            }
        });

        if (hasError) {
            e.preventDefault();
            e.stopPropagation();
            return false;
        }
    }, true);

    // Initial run on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => scanAndInit(document));
    } else {
        scanAndInit(document);
    }

    // Auto-scan on dynamic DOM additions (e.g. modals, live search updates)
    if (window.MutationObserver) {
        let mutationTimer = null;
        const observer = new MutationObserver(() => {
            clearTimeout(mutationTimer);
            mutationTimer = setTimeout(() => {
                scanAndInit(document);
            }, 100);
        });
        if (document.body) {
            observer.observe(document.body, { childList: true, subtree: true });
        } else {
            document.addEventListener('DOMContentLoaded', () => {
                observer.observe(document.body, { childList: true, subtree: true });
            });
        }
    }

    // Expose utility globally
    window.initDateRangePicker = setupDateRangePair;
    window.initGlobalDatePickers = scanAndInit;
    window.TODAY_DATE_STR = TODAY_STR;
})();
</script>
