<script>
    window.adminFormValidation = function () {
        return {
            formErrors: @json($errors->getMessages()),
            designation: @json(old('designation', '')),

            clearError(event) {
                const target = event.target || event;
                const name = target.name;
                if (!name) {
                    return;
                }

                if (this.formErrors[name]) {
                    delete this.formErrors[name];
                }
            },

            async validateField(event) {
                const target = event.target || event;
                const name = target.name;
                if (!name) {
                    return true;
                }

                let value = target.type === 'checkbox' ? target.checked : String(target.value || '').trim();
                let error = null;

                if (target.required && value === '') {
                    error = 'This field is required.';
                }

                if (!error && target.type === 'email' && value !== '') {
                    const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    if (!emailPattern.test(value)) {
                        error = 'Enter a valid email address.';
                    }
                }

                if (!error && name === 'password' && value !== '' && value.length < 3) {
                    error = 'Password must be at least 3 characters.';
                }

                if (!error && name === 'other_designation' && this.designation === 'Others' && value === '') {
                    error = 'Please specify the designation.';
                }

                if (!error && name === 'unit_code' && value !== '') {
                    const exists = await this.remoteCheck('{{ route('admin.units.checkCode') }}', { unit_code: value });
                    if (exists) {
                        error = 'This unit code is already in use.';
                    }
                }

                if (!error && name === 'seat_name' && value !== '') {
                    const exists = await this.remoteCheck('{{ route('admin.seats.checkName') }}', { seat_name: value });
                    if (exists) {
                        error = 'This seat name is already in use.';
                    }
                }

                if (!error && name === 'email' && value !== '') {
                    const exists = await this.remoteCheck('{{ route('users.checkUnique') }}', { field: 'email', value });
                    if (exists) {
                        error = 'This email address is already registered.';
                    }
                }

                if (!error && name === 'pen' && value !== '') {
                    const exists = await this.remoteCheck('{{ route('users.checkUnique') }}', { field: 'pen', value });
                    if (exists) {
                        error = 'This PEN is already registered.';
                    }
                }

                if (error) {
                    this.formErrors[name] = error;
                    return false;
                }

                if (this.formErrors[name]) {
                    delete this.formErrors[name];
                }

                return true;
            },

            async remoteCheck(url, payload) {
                try {
                    const response = await fetch(url, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            Accept: 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        },
                        body: JSON.stringify(payload),
                    });

                    if (!response.ok) {
                        return false;
                    }

                    const data = await response.json();
                    return data.exists === true;
                } catch (error) {
                    return false;
                }
            },

            async validateForm(form) {
                this.formErrors = { ...this.formErrors };
                let isValid = true;

                const fields = Array.from(form.querySelectorAll('input, select, textarea'));
                for (const field of fields) {
                    if (!field.name || field.disabled || field.type === 'hidden' || field.type === 'file') {
                        continue;
                    }

                    const fieldValid = await this.validateField({ target: field });
                    if (!fieldValid) {
                        isValid = false;
                    }
                }

                const unitIds = form.querySelectorAll('input[name="unit_ids[]"]');
                if (form.querySelector('[name="unit_ids[]"]') && unitIds.length === 0) {
                    this.formErrors.unit_ids = 'At least one unit must be assigned.';
                    isValid = false;
                } else if (unitIds.length > 0) {
                    delete this.formErrors.unit_ids;
                }

                return isValid;
            },

            async submitForm(event) {
                event.preventDefault();
                const form = event.target.closest('form') || event.target;
                if (!form) {
                    return;
                }

                const isValid = await this.validateForm(form);
                if (isValid) {
                    form.submit();
                }
            }
        };
    };
</script>
