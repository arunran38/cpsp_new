{{-- Include Animate.css for smooth animations --}}
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />

{{-- FLASH MESSAGE TOASTS --}}
@if(session('success') || session('error') || session('warning') || session('info') || $errors->any())
    <script>
        document.addEventListener('DOMContentLoaded', function () {

            function showToast(icon, title, message, bgColor, timer = 4000) {
                Swal.fire({
                    icon: icon,
                    title: title,
                    text: message,
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: timer,
                    timerProgressBar: true,
                    background: bgColor,
                    color: '#ffffff',
                    iconColor: '#ffffff',
                    customClass: {
                        popup: 'rounded-2xl shadow-2xl px-4 py-3',
                        title: 'font-semibold text-lg',
                        timerProgressBar: 'bg-white/70'
                    },
                    showClass: {
                        popup: 'animate__animated animate__fadeInRight'
                    },
                    hideClass: {
                        popup: 'animate__animated animate__fadeOutRight'
                    }
                });
            }

            @if(session('success'))
                showToast(
                    'success',
                    'Success',
                    "{!! addslashes(session('success')) !!}",
                    'linear-gradient(135deg, #22c55e, #16a34a)',
                    4000
                );
            @endif

            @if(session('error'))
                showToast(
                    'error',
                    'Error ❌',
                    "{!! addslashes(session('error')) !!}",
                    'linear-gradient(135deg, #ef4444, #b91c1c)',
                    5000
                );
            @endif

            @if(session('warning'))
                showToast(
                    'warning',
                    'Warning ⚠️',
                    "{!! addslashes(session('warning')) !!}",
                    'linear-gradient(135deg, #f59e0b, #d97706)',
                    5000
                );
            @endif

            @if(session('info'))
                showToast(
                    'info',
                    'Info ℹ️',
                    "{!! addslashes(session('info')) !!}",
                    'linear-gradient(135deg, #3b82f6, #1d4ed8)',
                    4000
                );
            @endif

            {{-- Handle Validation Errors bag --}}
            @if($errors->any())
                @foreach($errors->messages() as $field => $messages)
                    @foreach($messages as $error)
                        @if($field !== 'receipt_no' && !str_contains(strtolower($error), 'file no') )
                            showToast(
                                'error',
                                'Validation Error',
                                "{!! addslashes($error) !!}",
                                'linear-gradient(135deg, #ef4444, #b91c1c)',
                                6000
                            );
                        @endif
                    @endforeach
                @endforeach
            @endif

                });
    </script>
@endif


{{-- GLOBAL CONFIRM INTERCEPTOR --}}
<script>
    document.addEventListener('click', function (e) {

        var el = e.target.closest('[onclick*="return confirm"]');

        if (el) {
            e.preventDefault();
            e.stopPropagation();

            var onclickAttr = el.getAttribute('onclick');
            var match = onclickAttr.match(/return\s+confirm\s*\(\s*['"](.*?)['"]\s*\)/);
            var message = (match && match[1])
                ? match[1]
                : 'Are you sure you want to perform this action?';

            Swal.fire({
                title: 'Are you sure?',
                text: message,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, proceed',
                cancelButtonText: 'Cancel',
                reverseButtons: true,

                background: 'linear-gradient(135deg, #fff7ed, #ffedd5)',
                color: '#1e293b',

                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#64748b',

                customClass: {
                    popup: 'rounded-2xl shadow-2xl border border-orange-200',
                    title: 'text-lg font-semibold',
                    confirmButton: 'rounded-xl px-5 py-2 font-bold',
                    cancelButton: 'rounded-xl px-5 py-2 font-bold'
                },

                showClass: {
                    popup: 'animate__animated animate__zoomIn'
                },
                hideClass: {
                    popup: 'animate__animated animate__zoomOut'
                }

            }).then((result) => {
                if (result.isConfirmed) {
                    var form = el.closest('form');

                    if (form) {
                        form.submit();
                    } else if (el.tagName.toLowerCase() === 'a' && el.href) {
                        window.location.href = el.href;
                    }
                }
            });
        }

    }, true);
</script>