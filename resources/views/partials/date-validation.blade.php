{{-- Reusable Global Date Validation & Date-Range Picker Utility --}}
<script>
(function() {
    const TODAY_STR = '{{ now()->timezone(config("app.timezone", "Asia/Kolkata"))->format("Y-m-d") }}';
    const DATE_REGEX = /^\d{4}-\d{2}-\d{2}$/;
    const PAIR_DEFINITIONS = [
        { from: 'date_from', to: 'date_to' },
        { from: 'from_date', to: 'to_date' },
        { from: 'start_date', to: 'end_date' },
        { from: 'district_from_date', to: 'district_to_date' }
    ];
    const ERROR_INPUT_CLASSES = ['border-rose-500', 'ring-1', 'ring-rose-500/30', 'focus:border-rose-500', 'focus:ring-rose-500/20'];
    const pairRegistry = new WeakMap();

    function getFieldLabel(input, fallback) {
        if (!input) return fallback || 'Date';
        if (input.dataset.dateLabel) return input.dataset.dateLabel;
        const name = ((input.getAttribute('name') || input.id || '')).toLowerCase();
        if (name.includes('district_from')) return 'District From Date';
        if (name.includes('district_to')) return 'District To Date';
        if (name.includes('start')) return 'Start Date';
        if (name.includes('end')) return 'End Date';
        if (name.includes('from')) return 'From Date';
        if (name.includes('to')) return 'To Date';
        return fallback || 'Date';
    }

    function allowFuture(input) {
        return input.hasAttribute('data-allow-future') || input.getAttribute('data-allow-future') === 'true';
    }

    function isValidDateString(value) {
        if (!value || !DATE_REGEX.test(value)) return false;
        const [y, m, d] = value.split('-').map(Number);
        const dt = new Date(Date.UTC(y, m - 1, d));
        return dt.getUTCFullYear() === y && (dt.getUTCMonth() + 1) === m && dt.getUTCDate() === d;
    }

    function getErrorContainer(input) {
        if (!input) return null;

        const form = input.closest('form');
        const container = input.closest('[data-date-container]') || form;

        if (input.dataset.dateErrorId) {
            const el = document.getElementById(input.dataset.dateErrorId);
            if (el) return el;
        }
        if (form && form.dataset.dateErrorId) {
            const el = document.getElementById(form.dataset.dateErrorId);
            if (el) return el;
        }
        if (container) {
            const rangeError = container.querySelector('#dateRangeError, .dateRangeError, .date-range-error');
            if (rangeError) return rangeError;
        }

        const errorId = (input.id || input.name || 'date_input') + '_error';
        let errorEl = document.getElementById(errorId);
        if (!errorEl && input.parentNode) {
            errorEl = document.createElement('p');
            errorEl.id = errorId;
            errorEl.className = 'date-validation-error text-xs font-semibold text-rose-600 mt-1 hidden';
            errorEl.setAttribute('aria-live', 'polite');
            if (input.nextSibling) {
                input.parentNode.insertBefore(errorEl, input.nextSibling);
            } else {
                input.parentNode.appendChild(errorEl);
            }
        }
        return errorEl;
    }

    function markInputInvalid(input, isInvalid) {
        if (!input) return;
        if (isInvalid) {
            input.classList.add(...ERROR_INPUT_CLASSES);
            input.setAttribute('aria-invalid', 'true');
        } else {
            input.classList.remove(...ERROR_INPUT_CLASSES);
            input.removeAttribute('aria-invalid');
        }
    }

    function setErrorMessage(errorEl, message) {
        if (!errorEl) return;
        if (message) {
            errorEl.textContent = message;
            errorEl.classList.remove('hidden');
            errorEl.style.display = 'block';
        } else {
            errorEl.textContent = '';
            errorEl.classList.add('hidden');
            errorEl.style.display = 'none';
        }
    }

    function syncToMin(fromInput, toInput) {
        const fromVal = fromInput.value;
        if (fromVal && isValidDateString(fromVal) && (allowFuture(fromInput) || fromVal <= TODAY_STR)) {
            toInput.setAttribute('min', fromVal);
        } else {
            toInput.removeAttribute('min');
        }
    }

    function validatePair(fromInput, toInput, options = {}) {
        if (!fromInput || !toInput) return true;
        const { clearConflictingTo = false, preserveNotice = false, checkBadInput = true } = options;

        if (!allowFuture(fromInput)) {
            fromInput.setAttribute('max', TODAY_STR);
        }
        if (!allowFuture(toInput)) {
            toInput.setAttribute('max', TODAY_STR);
        }

        const state = pairRegistry.get(fromInput) || { notice: '' };
        pairRegistry.set(fromInput, state);
        pairRegistry.set(toInput, state);

        if (!preserveNotice) {
            state.notice = '';
        }

        const fromVal = fromInput.value;
        const toVal = toInput.value;
        const fromLabel = getFieldLabel(fromInput, 'From Date');
        const toLabel = getFieldLabel(toInput, 'To Date');

        let fromError = '';
        let toError = '';

        // 1. Validate From Date (manual invalid date or future date)
        if (checkBadInput && fromInput.validity && fromInput.validity.badInput) {
            fromError = `Please enter a valid ${fromLabel}.`;
        } else if (fromVal && !isValidDateString(fromVal)) {
            fromError = `Please enter a valid ${fromLabel}.`;
        } else if (!allowFuture(fromInput) && fromVal && fromVal > TODAY_STR) {
            fromError = `${fromLabel} cannot be a future date.`;
        }

        // Keep To Date min synchronized with valid From Date (never lock From Date max to To Date)
        syncToMin(fromInput, toInput);

        // 2. Validate To Date (manual invalid date, future date, or earlier than From Date)
        if (checkBadInput && toInput.validity && toInput.validity.badInput) {
            toError = `Please enter a valid ${toLabel}.`;
        } else if (toVal && !isValidDateString(toVal)) {
            toError = `Please enter a valid ${toLabel}.`;
        } else if (!allowFuture(toInput) && toVal && toVal > TODAY_STR) {
            toError = `${toLabel} cannot be a future date.`;
        } else if (!fromError && fromVal && toVal && toVal < fromVal) {
            if (clearConflictingTo) {
                state.isSyncing = true;
                try {
                    toInput.value = '';
                    try {
                        toInput.dispatchEvent(new Event('input', { bubbles: true }));
                    } catch (err) {}
                } finally {
                    state.isSyncing = false;
                }
                state.notice = `${toLabel} was cleared because it cannot be earlier than ${fromLabel}.`;
            } else {
                toError = `${toLabel} must be equal to or later than ${fromLabel}.`;
            }
        }

        markInputInvalid(fromInput, Boolean(fromError));
        markInputInvalid(toInput, Boolean(toError));

        const fromErrEl = getErrorContainer(fromInput);
        const toErrEl = getErrorContainer(toInput);
        const displayMsg = fromError || toError || state.notice || '';

        if (fromErrEl && toErrEl && fromErrEl === toErrEl) {
            setErrorMessage(fromErrEl, displayMsg);
        } else {
            setErrorMessage(fromErrEl, fromError);
            setErrorMessage(toErrEl, toError || state.notice || '');
        }

        return !fromError && !toError;
    }

    function validateSingleInput(input, checkBadInput = true) {
        if (!input) return true;
        if (!allowFuture(input)) {
            input.setAttribute('max', TODAY_STR);
        }

        const label = getFieldLabel(input, 'Date');
        let error = '';

        if (checkBadInput && input.validity && input.validity.badInput) {
            error = `Please enter a valid ${label}.`;
        } else if (input.value && !isValidDateString(input.value)) {
            error = `Please enter a valid ${label}.`;
        } else if (!allowFuture(input) && input.value && input.value > TODAY_STR) {
            error = `${label} cannot be a future date.`;
        }

        markInputInvalid(input, Boolean(error));
        setErrorMessage(getErrorContainer(input), error);
        return !error;
    }

    function setupDateRangePair(fromInput, toInput) {
        if (!fromInput || !toInput) return;
        if (fromInput.dataset.dateRangeInit === 'true' && toInput.dataset.dateRangeInit === 'true') return;

        fromInput.dataset.dateRangeInit = 'true';
        toInput.dataset.dateRangeInit = 'true';

        const state = { isSyncing: false, notice: '', fromInput, toInput };
        pairRegistry.set(fromInput, state);
        pairRegistry.set(toInput, state);

        if (!allowFuture(fromInput)) {
            fromInput.setAttribute('max', TODAY_STR);
        }
        if (!allowFuture(toInput)) {
            toInput.setAttribute('max', TODAY_STR);
        }

        syncToMin(fromInput, toInput);
        if (fromInput.value || toInput.value) {
            validatePair(fromInput, toInput, { clearConflictingTo: false });
        }

        const handleFromEvent = (isChange, checkBadInput) => {
            if (state.isSyncing) return;
            validatePair(fromInput, toInput, { clearConflictingTo: isChange, checkBadInput });
        };

        const handleToEvent = (checkBadInput) => {
            if (state.isSyncing) return;
            validatePair(fromInput, toInput, { clearConflictingTo: false, checkBadInput });
        };

        // Capture phase ensures validation and min/max sync run before page change handlers
        fromInput.addEventListener('change', () => handleFromEvent(true, true), true);
        fromInput.addEventListener('input', () => handleFromEvent(false, false), true);
        fromInput.addEventListener('blur', () => handleFromEvent(false, true), true);

        toInput.addEventListener('change', () => handleToEvent(true), true);
        toInput.addEventListener('input', () => handleToEvent(false), true);
        toInput.addEventListener('blur', () => handleToEvent(true), true);
    }

    function setupSingleDateInput(input) {
        if (!input || input.dataset.singleDateInit === 'true') return;
        input.dataset.singleDateInit = 'true';

        if (!allowFuture(input)) {
            input.setAttribute('max', TODAY_STR);
        }

        input.addEventListener('change', () => validateSingleInput(input, true), true);
        input.addEventListener('input', () => validateSingleInput(input, false), true);
        input.addEventListener('blur', () => validateSingleInput(input, true), true);
    }

    function findPairsInContainer(container) {
        const pairs = [];
        const pairedInputs = new Set();

        PAIR_DEFINITIONS.forEach(def => {
            const fromInput = container.querySelector(`input[name="${def.from}"], input#${def.from}`);
            const toInput = container.querySelector(`input[name="${def.to}"], input#${def.to}`);
            if (fromInput && toInput && !pairedInputs.has(fromInput) && !pairedInputs.has(toInput)) {
                pairs.push([fromInput, toInput]);
                pairedInputs.add(fromInput);
                pairedInputs.add(toInput);
            }
        });

        const customFrom = container.querySelector('input[data-date-from], input[data-range="from"]');
        const customTo = container.querySelector('input[data-date-to], input[data-range="to"]');
        if (customFrom && customTo && !pairedInputs.has(customFrom) && !pairedInputs.has(customTo)) {
            pairs.push([customFrom, customTo]);
            pairedInputs.add(customFrom);
            pairedInputs.add(customTo);
        }

        return { pairs, pairedInputs };
    }

    function scanAndInit(root = document) {
        if (!root || !root.querySelectorAll) return;

        const containers = Array.from(root.querySelectorAll('form, [data-date-container]'));
        const processedInputs = new Set();

        containers.forEach(container => {
            const { pairs } = findPairsInContainer(container);
            pairs.forEach(([fromInput, toInput]) => {
                if (!processedInputs.has(fromInput) && !processedInputs.has(toInput)) {
                    setupDateRangePair(fromInput, toInput);
                    processedInputs.add(fromInput);
                    processedInputs.add(toInput);
                }
            });
        });

        // Fallback across arbitrary wrappers
        PAIR_DEFINITIONS.forEach(def => {
            const allFrom = Array.from(root.querySelectorAll(`input[name="${def.from}"], input#${def.from}`));
            const allTo = Array.from(root.querySelectorAll(`input[name="${def.to}"], input#${def.to}`));

            allFrom.forEach((fromEl, idx) => {
                const toEl = allTo[idx];
                if (toEl && !processedInputs.has(fromEl) && !processedInputs.has(toEl)) {
                    setupDateRangePair(fromEl, toEl);
                    processedInputs.add(fromEl);
                    processedInputs.add(toEl);
                }
            });
        });

        const allDateInputs = root.querySelectorAll('input[type="date"]');
        allDateInputs.forEach(input => {
            if (!processedInputs.has(input) && !pairRegistry.has(input)) {
                setupSingleDateInput(input);
            }
        });
    }

    function validateDateRangeContainer(container = document) {
        const target = container || document;
        if (!target || !target.querySelectorAll) return true;

        scanAndInit(target);

        let isValid = true;
        let firstInvalid = null;
        const { pairs, pairedInputs } = findPairsInContainer(target);

        pairs.forEach(([fromInput, toInput]) => {
            if (!validatePair(fromInput, toInput, { clearConflictingTo: false, preserveNotice: true })) {
                isValid = false;
                if (!firstInvalid) {
                    firstInvalid = fromInput.getAttribute('aria-invalid') === 'true' ? fromInput : toInput;
                }
            }
        });

        const allDateInputs = target.querySelectorAll('input[type="date"]');
        allDateInputs.forEach(input => {
            if (!pairedInputs.has(input) && !pairRegistry.has(input)) {
                if (!validateSingleInput(input)) {
                    isValid = false;
                    if (!firstInvalid) firstInvalid = input;
                }
            }
        });

        if (!isValid && firstInvalid && typeof firstInvalid.focus === 'function') {
            firstInvalid.focus();
        }

        return isValid;
    }

    // Guard on every form submit (runs in capture phase before any form submit handlers)
    document.addEventListener('submit', function(e) {
        const form = e.target;
        if (!form || !form.querySelectorAll) return;

        if (!validateDateRangeContainer(form)) {
            e.preventDefault();
            e.stopPropagation();
            if (typeof e.stopImmediatePropagation === 'function') {
                e.stopImmediatePropagation();
            }
            return false;
        }
    }, true);

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => scanAndInit(document));
    } else {
        scanAndInit(document);
    }

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

    window.initDateRangePicker = setupDateRangePair;
    window.initGlobalDatePickers = scanAndInit;
    window.validateDateRange = validateDateRangeContainer;
    window.TODAY_DATE_STR = TODAY_STR;
})();
</script>
