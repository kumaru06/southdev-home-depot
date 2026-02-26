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
            var code = provinceSelect.value;
            setOptions(citySelect, [{ value: '', label: code ? 'Loading…' : 'Select City' }], true);
            citySelect.disabled = !code;
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
                var options = [{ value: '', label: 'Select Province' }].concat(
                    provinces.map(function (p) { return { value: p.code, label: p.name }; })
                );
                setOptions(selectEl, options, true);
            })
            .catch(function () {
                var options = [{ value: '', label: 'Select Province' }].concat(
                    FALLBACK.provinces.map(function (p) { return { value: p.code, label: p.name }; })
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
