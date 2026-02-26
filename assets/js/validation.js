/**
 * SouthDev Home Depot – Form Validation Utilities
 * Client-side validation with inline error display
 */

(function () {
    'use strict';

    var Validator = {
        email: function (v) { return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v); },
        phone: function (v) { return /^(09|\+639)\d{9}$/.test(v.replace(/\s/g, '')); },
        required: function (v) { return v !== null && v !== undefined && v.toString().trim() !== ''; },
        minLength: function (v, n) { return v.length >= n; },
        maxLength: function (v, n) { return v.length <= n; },
        numeric: function (v) { return !isNaN(parseFloat(v)) && isFinite(v); },
        match: function (a, b) { return a === b; },
        password: function (v) { return v.length >= 8; }
    };

    /**
     * Validate a form against a rules object.
     * @param {string} formId
     * @param {Object} rules  – { fieldName: [{ type, value?, message }] }
     * @returns {boolean}
     */
    function validateForm(formId, rules) {
        var form = document.getElementById(formId);
        if (!form) return false;

        var valid = true;

        /* Clear previous errors */
        form.querySelectorAll('.field-error').forEach(function (el) { el.remove(); });
        form.querySelectorAll('.is-invalid').forEach(function (el) { el.classList.remove('is-invalid'); });

        Object.keys(rules).forEach(function (field) {
            var input = form.querySelector('[name="' + field + '"]');
            if (!input) return;

            var fieldRules = rules[field];
            for (var i = 0; i < fieldRules.length; i++) {
                var rule = fieldRules[i];
                var ok = true;
                var msg = '';

                switch (rule.type) {
                    case 'required':
                        ok = Validator.required(input.value);
                        msg = rule.message || field.replace(/_/g, ' ') + ' is required';
                        break;
                    case 'email':
                        ok = !input.value || Validator.email(input.value);
                        msg = rule.message || 'Please enter a valid email address';
                        break;
                    case 'phone':
                        ok = !input.value || Validator.phone(input.value);
                        msg = rule.message || 'Please enter a valid PH phone number';
                        break;
                    case 'minLength':
                        ok = !input.value || Validator.minLength(input.value, rule.value);
                        msg = rule.message || 'Minimum ' + rule.value + ' characters required';
                        break;
                    case 'maxLength':
                        ok = Validator.maxLength(input.value, rule.value);
                        msg = rule.message || 'Maximum ' + rule.value + ' characters allowed';
                        break;
                    case 'match':
                        var other = form.querySelector('[name="' + rule.value + '"]');
                        ok = other && Validator.match(input.value, other.value);
                        msg = rule.message || 'Fields do not match';
                        break;
                    case 'password':
                        ok = !input.value || Validator.password(input.value);
                        msg = rule.message || 'Password must be at least 8 characters';
                        break;
                    case 'numeric':
                        ok = !input.value || Validator.numeric(input.value);
                        msg = rule.message || 'Please enter a valid number';
                        break;
                }

                if (!ok) {
                    valid = false;
                    input.classList.add('is-invalid');
                    var errEl = document.createElement('span');
                    errEl.className = 'field-error';
                    errEl.style.cssText = 'color:var(--danger);font-size:12px;margin-top:4px;display:block;font-weight:500;';
                    errEl.textContent = msg;
                    input.parentNode.appendChild(errEl);
                    break; /* show first error per field */
                }
            }
        });

        if (!valid) {
            var firstErr = form.querySelector('.is-invalid');
            if (firstErr) firstErr.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }

        return valid;
    }

    /* ===== Live validation on blur ===== */
    document.addEventListener('focusout', function (e) {
        var el = e.target;
        if (el.classList && el.classList.contains('is-invalid') && el.value.trim()) {
            el.classList.remove('is-invalid');
            var err = el.parentNode.querySelector('.field-error');
            if (err) err.remove();
        }
    });

    /* Expose */
    window.Validator = Validator;
    window.validateForm = validateForm;

})();
