{{-- Include Animate.css for smooth animations --}}
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />

{{-- Ensure SweetAlert2 JS is loaded --}}
<script>
    if (typeof Swal === 'undefined') {
        document.write('<script src="{{ asset('vendor/sweetalert/sweetalert.all.js') }}"><\/script>');
    }
</script>

<style>
    /* Premium SweetAlert2 Custom Styling */
    .cpsp-swal-popup {
        border-radius: 1.25rem !important;
        box-shadow: 0 25px 50px -12px rgba(15, 23, 42, 0.25), 0 0 0 1px rgba(226, 232, 240, 0.8) !important;
        padding: 1.75rem 1.5rem !important;
        font-family: 'Inter', system-ui, -apple-system, sans-serif !important;
        background: #ffffff !important;
    }
    .cpsp-swal-title {
        font-size: 1.25rem !important;
        font-weight: 800 !important;
        color: #0f172a !important;
        padding-top: 0.5rem !important;
        margin-bottom: 0.5rem !important;
    }
    .cpsp-swal-html {
        font-size: 0.925rem !important;
        color: #475569 !important;
        line-height: 1.55 !important;
    }
    .cpsp-swal-confirm-btn {
        border-radius: 0.75rem !important;
        font-size: 0.875rem !important;
        font-weight: 700 !important;
        padding: 0.65rem 1.35rem !important;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -2px rgba(0, 0, 0, 0.1) !important;
        transition: all 0.2s ease !important;
    }
    .cpsp-swal-confirm-btn:hover {
        transform: translateY(-1px) !important;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.15) !important;
    }
    .cpsp-swal-cancel-btn {
        border-radius: 0.75rem !important;
        font-size: 0.875rem !important;
        font-weight: 700 !important;
        padding: 0.65rem 1.35rem !important;
        background: #f1f5f9 !important;
        color: #475569 !important;
        border: 1px solid #e2e8f0 !important;
        transition: all 0.2s ease !important;
    }
    .cpsp-swal-cancel-btn:hover {
        background: #e2e8f0 !important;
        color: #1e293b !important;
    }
    /* Toast styles */
    .cpsp-toast-popup {
        border-radius: 1rem !important;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.2), 0 8px 10px -6px rgba(0, 0, 0, 0.2) !important;
        padding: 0.75rem 1.25rem !important;
        font-family: 'Inter', system-ui, -apple-system, sans-serif !important;
    }
    .cpsp-toast-title {
        font-size: 0.9rem !important;
        font-weight: 700 !important;
        margin: 0 !important;
    }
</style>

<script>
    (function () {
        // Initialize Global Toast Engine
        window.Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 4500,
            timerProgressBar: true,
            customClass: {
                popup: 'cpsp-toast-popup',
                title: 'cpsp-toast-title'
            },
            showClass: {
                popup: 'animate__animated animate__fadeInRight animate__faster'
            },
            hideClass: {
                popup: 'animate__animated animate__fadeOutRight animate__faster'
            },
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer);
                toast.addEventListener('mouseleave', Swal.resumeTimer);
            }
        });

        // Global Helper: showToast(type, message, title, timer)
        window.showToast = function (type, message, title = '', timer = 4500) {
            let bg, icon, defaultTitle;

            switch (type) {
                case 'success':
                    bg = 'linear-gradient(135deg, #059669 0%, #10b981 100%)';
                    icon = 'success';
                    defaultTitle = 'Success';
                    break;
                case 'error':
                case 'danger':
                    bg = 'linear-gradient(135deg, #e11d48 0%, #f43f5e 100%)';
                    icon = 'error';
                    defaultTitle = 'Error';
                    break;
                case 'warning':
                    bg = 'linear-gradient(135deg, #d97706 0%, #f59e0b 100%)';
                    icon = 'warning';
                    defaultTitle = 'Warning';
                    break;
                case 'info':
                default:
                    bg = 'linear-gradient(135deg, #3b82f6 0%, #6366f1 100%)';
                    icon = 'info';
                    defaultTitle = 'Notice';
                    break;
            }

            return window.Toast.fire({
                icon: icon,
                title: title || defaultTitle,
                text: message,
                background: bg,
                color: '#ffffff',
                iconColor: '#ffffff',
                timer: timer
            });
        };

        // Global Helper: confirmDialog(options)
        window.confirmDialog = function (options) {
            const isDanger = options.danger !== false;
            return Swal.fire({
                title: options.title || (isDanger ? 'Confirm Action' : 'Are you sure?'),
                text: options.text || 'Do you want to proceed with this action?',
                html: options.html,
                icon: options.icon || (isDanger ? 'warning' : 'question'),
                showCancelButton: true,
                confirmButtonText: options.confirmText || (isDanger ? 'Yes, proceed' : 'Continue'),
                cancelButtonText: options.cancelText || 'Cancel',
                confirmButtonColor: options.confirmColor || (isDanger ? '#e11d48' : '#4f46e5'),
                cancelButtonColor: options.cancelColor || '#64748b',
                reverseButtons: true,
                customClass: {
                    popup: 'cpsp-swal-popup',
                    title: 'cpsp-swal-title',
                    htmlContainer: 'cpsp-swal-html',
                    confirmButton: 'cpsp-swal-confirm-btn',
                    cancelButton: 'cpsp-swal-cancel-btn'
                },
                showClass: {
                    popup: 'animate__animated animate__zoomIn animate__faster'
                },
                hideClass: {
                    popup: 'animate__animated animate__zoomOut animate__faster'
                }
            }).then((result) => result.isConfirmed);
        };

        // Universal alert() override to replace browser alerts with modern SweetAlert
        window.nativeAlert = window.alert;
        window.alert = function (message) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Notice',
                    html: typeof message === 'object' ? JSON.stringify(message) : message,
                    icon: 'info',
                    confirmButtonColor: '#4f46e5',
                    confirmButtonText: 'OK',
                    customClass: {
                        popup: 'cpsp-swal-popup',
                        title: 'cpsp-swal-title',
                        htmlContainer: 'cpsp-swal-html',
                        confirmButton: 'cpsp-swal-confirm-btn'
                    }
                });
            } else {
                console.warn('Alert:', message);
            }
        };

        // Universal Confirmation Interceptor for Forms (onsubmit="return confirm(...)")
        document.addEventListener('submit', function (e) {
            const form = e.target;
            if (!form || form.tagName !== 'FORM') return;

            if (form.dataset.swalConfirmed === 'true') {
                form.removeAttribute('data-swal-confirmed');
                return;
            }

            const onsubmitAttr = form.getAttribute('onsubmit') || '';
            const dataConfirm = form.getAttribute('data-confirm') || form.getAttribute('data-message');

            let message = null;
            if (dataConfirm) {
                message = dataConfirm;
            } else if (onsubmitAttr && onsubmitAttr.includes('confirm(')) {
                const match = onsubmitAttr.match(/confirm\s*\(\s*['"`](.*?)['"`]\s*\)/);
                message = match ? match[1] : 'Are you sure you want to proceed?';
            }

            if (message) {
                e.preventDefault();
                e.stopImmediatePropagation();

                const isDelete = /delete|remove|revoke|destroy|permanently|warning/i.test(message) ||
                                 /delete|destroy/i.test(form.action || '');

                Swal.fire({
                    title: isDelete ? 'Confirm Action' : 'Are you sure?',
                    text: message,
                    icon: isDelete ? 'warning' : 'question',
                    showCancelButton: true,
                    confirmButtonText: isDelete ? 'Yes, proceed' : 'Continue',
                    cancelButtonText: 'Cancel',
                    confirmButtonColor: isDelete ? '#e11d48' : '#4f46e5',
                    cancelButtonColor: '#64748b',
                    reverseButtons: true,
                    customClass: {
                        popup: 'cpsp-swal-popup',
                        title: 'cpsp-swal-title',
                        htmlContainer: 'cpsp-swal-html',
                        confirmButton: 'cpsp-swal-confirm-btn',
                        cancelButton: 'cpsp-swal-cancel-btn'
                    },
                    showClass: {
                        popup: 'animate__animated animate__zoomIn animate__faster'
                    },
                    hideClass: {
                        popup: 'animate__animated animate__zoomOut animate__faster'
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.dataset.swalConfirmed = 'true';
                        const origOnsubmit = form.onsubmit;
                        form.onsubmit = null;
                        form.submit();
                        setTimeout(() => { form.onsubmit = origOnsubmit; }, 100);
                    }
                });
            }
        }, true);

        // Universal Confirmation Interceptor for Elements (onclick="return confirm(...)")
        document.addEventListener('click', function (e) {
            const el = e.target.closest('[onclick*="confirm("], [data-confirm]');
            if (!el) return;

            if (el.dataset.swalConfirmed === 'true') {
                el.removeAttribute('data-swal-confirmed');
                return;
            }

            const onclickAttr = el.getAttribute('onclick') || '';
            const dataConfirm = el.getAttribute('data-confirm');

            let message = null;
            if (dataConfirm) {
                message = dataConfirm;
            } else if (onclickAttr && onclickAttr.includes('confirm(')) {
                const match = onclickAttr.match(/confirm\s*\(\s*['"`](.*?)['"`]\s*\)/);
                message = match ? match[1] : 'Are you sure you want to proceed?';
            }

            if (message) {
                e.preventDefault();
                e.stopImmediatePropagation();

                const isDelete = /delete|remove|revoke|destroy|permanently|warning/i.test(message);

                Swal.fire({
                    title: isDelete ? 'Confirm Action' : 'Are you sure?',
                    text: message,
                    icon: isDelete ? 'warning' : 'question',
                    showCancelButton: true,
                    confirmButtonText: isDelete ? 'Yes, proceed' : 'Continue',
                    cancelButtonText: 'Cancel',
                    confirmButtonColor: isDelete ? '#e11d48' : '#4f46e5',
                    cancelButtonColor: '#64748b',
                    reverseButtons: true,
                    customClass: {
                        popup: 'cpsp-swal-popup',
                        title: 'cpsp-swal-title',
                        htmlContainer: 'cpsp-swal-html',
                        confirmButton: 'cpsp-swal-confirm-btn',
                        cancelButton: 'cpsp-swal-cancel-btn'
                    },
                    showClass: {
                        popup: 'animate__animated animate__zoomIn animate__faster'
                    },
                    hideClass: {
                        popup: 'animate__animated animate__zoomOut animate__faster'
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        el.dataset.swalConfirmed = 'true';
                        const form = el.closest('form');
                        if (form && el.type === 'submit') {
                            const origOnclick = el.onclick;
                            el.onclick = null;
                            if (el.name) {
                                const input = document.createElement('input');
                                input.type = 'hidden';
                                input.name = el.name;
                                input.value = el.value;
                                form.appendChild(input);
                            }
                            form.submit();
                            setTimeout(() => { el.onclick = origOnclick; }, 100);
                        } else if (el.tagName.toLowerCase() === 'a' && el.href) {
                            window.location.href = el.href;
                        } else {
                            const origOnclick = el.onclick;
                            el.onclick = null;
                            el.click();
                            setTimeout(() => { el.onclick = origOnclick; }, 100);
                        }
                    }
                });
            }
        }, true);

        // Flash Message Toasts (Session Data)
        document.addEventListener('DOMContentLoaded', function () {
            @if(session('success'))
                window.showToast('success', "{!! addslashes(session('success')) !!}", 'Success');
            @endif

            @if(session('error'))
                window.showToast('error', "{!! addslashes(session('error')) !!}", 'Error');
            @endif

            @if(session('warning'))
                window.showToast('warning', "{!! addslashes(session('warning')) !!}", 'Warning');
            @endif

            @if(session('info'))
                window.showToast('info', "{!! addslashes(session('info')) !!}", 'Info');
            @endif

            {{-- Handle Validation Errors --}}
            @if($errors->any())
                @foreach($errors->messages() as $field => $messages)
                    @foreach($messages as $error)
                        @if($field !== 'receipt_no' && !str_contains(strtolower($error), 'file no'))
                            window.showToast('error', "{!! addslashes($error) !!}", 'Validation Error', 5000);
                        @endif
                    @endforeach
                @endforeach
            @endif
        });
    })();
</script>
