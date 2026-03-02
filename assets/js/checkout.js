/**
 * SouthDev Home Depot – Checkout JavaScript
 * Form validation, payment method toggle, CSRF
 */

(function () {
    'use strict';

    var PSGC = {
        provincesUrl: 'https://psgc.gitlab.io/api/provinces.json',
        citiesByProvince: function (provinceCode) {
            return 'https://psgc.gitlab.io/api/provinces/' + encodeURIComponent(provinceCode) + '/cities-municipalities.json';
        }
    };

    // Minimal fallback list (in case API is blocked/offline)
    var FALLBACK = {
        provinces: [
            { code: 'PH-FB-CARAGA', name: 'Caraga' }
        ],
        cities: {
            'PH-FB-CARAGA': [
                { code: 'PH-FB-BUTUAN', name: 'Butuan City' },
                { code: 'PH-FB-BISLIG', name: 'Bislig City' },
                { code: 'PH-FB-SURIGAO', name: 'Surigao City' }
            ]
        }
    };

    document.addEventListener('DOMContentLoaded', function () {
        var form = document.getElementById('checkout-form');

        // Province → City dropdowns
        initProvinceCity();

        if (form) {
            form.addEventListener('submit', function (e) {
                if (!validateCheckout()) {
                    e.preventDefault();
                }
            });
        }

        /* Payment Method Toggle */
        document.querySelectorAll('input[name="payment_method"]').forEach(function (radio) {
            radio.addEventListener('change', function () {
                document.querySelectorAll('.payment-details').forEach(function (el) {
                    el.style.display = 'none';
                });
                var target = document.getElementById(this.value + '-details');
                if (target) {
                    target.style.display = 'block';
                    target.style.animation = 'pageFadeIn .3s ease';
                }
            });
        });

        /* Auto-format phone number */
        var phoneInput = document.getElementById('contact_phone');
        if (phoneInput) {
            phoneInput.addEventListener('input', function () {
                this.value = this.value.replace(/[^\d+\-() ]/g, '');
            });
        }

        /* Card inputs formatting and validation */
        var cardNumberInput = document.getElementById('card_number');
        var cardNameInput = document.getElementById('card_name');
        var cardExpiryInput = document.getElementById('card_expiry');
        var cardCvcInput = document.getElementById('card_cvc');

        if (cardNumberInput) {
            cardNumberInput.addEventListener('input', function () {
                var digits = this.value.replace(/\D/g, '').slice(0,16);
                var parts = digits.match(/.{1,4}/g);
                this.value = parts ? parts.join(' ') : digits;
            });
        }

        if (cardNameInput) {
            cardNameInput.addEventListener('input', function () {
                // allow letters, spaces, hyphen and apostrophe
                this.value = this.value.replace(/[^A-Za-z\s\-\']/g, '');
            });
        }

        if (cardExpiryInput) {
            cardExpiryInput.addEventListener('input', function () {
                var digits = this.value.replace(/\D/g, '').slice(0,4);
                if (digits.length >= 3) {
                    this.value = digits.slice(0,2) + ' / ' + digits.slice(2);
                } else if (digits.length >= 1) {
                    this.value = digits;
                } else {
                    this.value = '';
                }
            });
        }

        if (cardCvcInput) {
            cardCvcInput.addEventListener('input', function () {
                this.value = this.value.replace(/\D/g, '').slice(0,3);
            });
        }
    });

    function initProvinceCity() {
        var provinceSelect = document.getElementById('shipping_state');
        var citySelect = document.getElementById('shipping_city');
        if (!provinceSelect || !citySelect) return;

        setOptions(provinceSelect, [{ value: '', label: 'Select Province' }], true);
        setOptions(citySelect, [{ value: '', label: 'Select City' }], true);
        citySelect.disabled = true;

        loadProvinces(provinceSelect).then(function () {
            // no-op
        });

        provinceSelect.addEventListener('change', function () {
            var selectedOption = provinceSelect.options[provinceSelect.selectedIndex];
            var code = selectedOption ? selectedOption.getAttribute('data-code') : '';
            var hasSelection = !!provinceSelect.value;
            setOptions(citySelect, [{ value: '', label: hasSelection ? 'Loading…' : 'Select City' }], true);
            citySelect.disabled = !hasSelection;
            if (!code) return;

            loadCitiesForProvince(code, citySelect);
        });
    }

    function loadProvinces(selectEl) {
        return fetchJson(PSGC.provincesUrl)
            .then(function (provinces) {
                if (!Array.isArray(provinces) || !provinces.length) throw new Error('No provinces');
                provinces.sort(function (a, b) {
                    return String(a.name).localeCompare(String(b.name));
                });
                var options = [{ value: '', label: 'Select Province', dataCode: '' }].concat(
                    provinces.map(function (p) { return { value: p.name, label: p.name, dataCode: p.code }; })
                );
                setOptions(selectEl, options, true);
            })
            .catch(function () {
                var options = [{ value: '', label: 'Select Province', dataCode: '' }].concat(
                    FALLBACK.provinces.map(function (p) { return { value: p.name, label: p.name, dataCode: p.code }; })
                );
                setOptions(selectEl, options, true);
            });
    }

    function loadCitiesForProvince(provinceCode, citySelect) {
        fetchJson(PSGC.citiesByProvince(provinceCode))
            .then(function (cities) {
                if (!Array.isArray(cities) || !cities.length) throw new Error('No cities');
                cities.sort(function (a, b) {
                    return String(a.name).localeCompare(String(b.name));
                });
                var options = [{ value: '', label: 'Select City' }].concat(
                    cities.map(function (c) { return { value: c.name, label: c.name }; })
                );
                setOptions(citySelect, options, true);
                citySelect.disabled = false;
            })
            .catch(function () {
                var fallbackCities = FALLBACK.cities[provinceCode] || [];
                var options = [{ value: '', label: 'Select City' }].concat(
                    fallbackCities.map(function (c) { return { value: c.name, label: c.name }; })
                );
                setOptions(citySelect, options, true);
                citySelect.disabled = false;
            });
    }

    function setOptions(selectEl, options, keepValueIfPossible) {
        var prev = keepValueIfPossible ? selectEl.value : '';
        selectEl.innerHTML = '';
        options.forEach(function (opt) {
            var o = document.createElement('option');
            o.value = opt.value;
            o.textContent = opt.label;
            if (opt.dataCode) o.setAttribute('data-code', opt.dataCode);
            selectEl.appendChild(o);
        });
        if (keepValueIfPossible && prev) {
            selectEl.value = prev;
        }
    }

    function fetchJson(url) {
        return fetch(url, { method: 'GET' })
            .then(function (r) {
                if (!r.ok) throw new Error('HTTP ' + r.status);
                return r.json();
            });
    }

    function markInvalid(el, message) {
        if (!el) return;
        el.classList.add('is-invalid');
        // remove existing message if present
        var existing = el.parentNode.querySelector('.field-error');
        if (existing) existing.remove();
        var err = document.createElement('span');
        err.className = 'field-error';
        err.style.cssText = 'color:var(--danger);font-size:12px;margin-top:4px;display:block;';
        err.textContent = message || 'Invalid field';
        el.parentNode.appendChild(err);
    }

    function validateCheckout() {
        var required = ['shipping_address', 'shipping_state', 'shipping_city', 'contact_phone'];
        var valid = true;

        /* Clear previous errors */
        document.querySelectorAll('.form-control.is-invalid').forEach(function (el) {
            el.classList.remove('is-invalid');
        });
        document.querySelectorAll('.field-error').forEach(function (el) { el.remove(); });

        required.forEach(function (fieldName) {
            var input = document.getElementById(fieldName);
            if (input && !input.value.trim()) {
                input.classList.add('is-invalid');
                var err = document.createElement('span');
                err.className = 'field-error';
                err.style.cssText = 'color:var(--danger);font-size:12px;margin-top:4px;display:block;';
                err.textContent = 'This field is required';
                input.parentNode.appendChild(err);
                valid = false;
            }
        });

        /* Check payment method selected */
        var checkedPayment = document.querySelector('input[name="payment_method"]:checked');
        if (!checkedPayment) {
            showNotification('Please select a payment method', 'warning');
            valid = false;
        }

        // Additional validation when Card is selected
        if (checkedPayment && checkedPayment.value === 'card') {
            var cardNumber = document.getElementById('card_number');
            var cardName = document.getElementById('card_name');
            var cardExpiry = document.getElementById('card_expiry');
            var cardCvc = document.getElementById('card_cvc');

            if (cardNumber) {
                var digits = (cardNumber.value || '').replace(/\D/g, '');
                if (digits.length !== 16) {
                    markInvalid(cardNumber, 'Card number must be 16 digits');
                    valid = false;
                }
            }

            if (cardName) {
                var nameVal = (cardName.value || '').trim();
                if (!/^[A-Za-z\s\-']+$/.test(nameVal)) {
                    markInvalid(cardName, 'Name must contain only letters');
                    valid = false;
                }
            }

            if (cardExpiry) {
                var expRaw = (cardExpiry.value || '').replace(/\D/g, '');
                if (expRaw.length !== 4) {
                    markInvalid(cardExpiry, 'Expiry must be MMYY');
                    valid = false;
                } else {
                    var mm = parseInt(expRaw.slice(0,2), 10);
                    var yy = parseInt(expRaw.slice(2), 10);
                    if (isNaN(mm) || mm < 1 || mm > 12) {
                        markInvalid(cardExpiry, 'Invalid expiry month');
                        valid = false;
                    } else {
                        // simple future-date check (assume 2000+YY)
                        var now = new Date();
                        var year = 2000 + yy;
                        var expDate = new Date(year, mm - 1, 1);
                        // set to last day of month
                        expDate.setMonth(expDate.getMonth() + 1);
                        expDate.setDate(0);
                        if (expDate < now) {
                            markInvalid(cardExpiry, 'Card expired');
                            valid = false;
                        }
                    }
                }
            }

            if (cardCvc) {
                var cvc = (cardCvc.value || '').replace(/\D/g, '');
                if (cvc.length !== 3) {
                    markInvalid(cardCvc, 'Security code must be 3 digits');
                    valid = false;
                }
            }
        }

        if (!valid) {
            showNotification('Please complete all required fields', 'error');
            /* Scroll to first error */
            var firstErr = document.querySelector('.is-invalid');
            if (firstErr) firstErr.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }

        return valid;
    }

    window.validateCheckout = validateCheckout;

})();
