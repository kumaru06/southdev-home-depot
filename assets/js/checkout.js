/**
 * SouthDev Home Depot – Checkout JavaScript
 * Davao City barangay selector, form validation, payment toggle
 */

(function () {
    'use strict';

    /* ── Davao City Barangays (sorted alphabetically) ── */
    var DAVAO_BARANGAYS = [
        'Acacia', 'Agdao', 'Alambre', 'Alejandro Navarro (Linoan)',
        'Alfonso Angliongto Sr.', 'Angalan', 'Atan-Awe',
        'Baganihan', 'Bago Aplaya', 'Bago Gallera', 'Bago Oshiro',
        'Baguio', 'Balengaeng', 'Baliok', 'Bangkal', 'Bangkas Heights',
        'Bantol', 'Baracatan', 'Bato', 'Bayabas',
        'Biao Escuela', 'Biao Guianga', 'Biao Joaquin',
        'Bucana', 'Buhangin', 'Bunawan',
        'Cabantian', 'Calinan', 'Callawa', 'Camansi', 'Carmen',
        'Catalunan Grande', 'Catalunan Pequeño', 'Catigan', 'Cawayan',
        'Centro (Poblacion)', 'Colosas', 'Communal', 'Crossing Bayabas',
        'Dacudao', 'Dalag', 'Daliao', 'Daliaon Plantation', 'Datu Salumay',
        'Dominga', 'Dumoy',
        'Eden',
        'Fatima (Benedicto)',
        'Gov. Paciano Bangoy', 'Gov. Vicente Duterte',
        'Guadalupe', 'Gumalang', 'Gumitan',
        'Ilang', 'Indangan',
        'Kap. Tomas Monteverde Sr.', 'Kilate',
        'Lacson', 'Lamanan', 'Langub', 'Lapu-Lapu', 'Lasang',
        'Leon Garcia Sr.', 'Lizada', 'Los Amigos', 'Lubogan', 'Lumiad',
        'Ma-a', 'Mabuhay', 'Magsaysay', 'Magtuod', 'Mahayag',
        'Malabog', 'Malagos', 'Malaguli', 'Mandug', 'Mapula',
        'Marapangi', 'Marilog', 'Matina Aplaya', 'Matina Biao',
        'Matina Crossing', 'Matina Pangi', 'Megkawayan', 'Mintal',
        'Mudiang', 'Mulig',
        'New Carmen', 'New Valencia',
        'Pampanga', 'Panacan', 'Pangyan', 'Paradise Embak',
        'Rafael Castillo', 'Riverside',
        'Saban', 'Salapawan', 'Salmonan', 'Saloy',
        'San Antonio', 'San Isidro (Bajada)', 'San Rafael', 'Sasa',
        'Sibulan', 'Sirawan', 'Sirib', 'Sto. Niño', 'Subasta', 'Sumimao',
        'Tacunan', 'Tagakpan', 'Tagluno', 'Talandang', 'Talisay',
        'Talomo', 'Tamayong', 'Tamugan', 'Tawan-Tawan',
        'Tibuloy', 'Tibungco', 'Tigatto', 'Tikalon', 'Toril',
        'Tugbok', 'Tungakalan',
        'Ulas',
        'Vicente Hizon Sr.',
        'Waan', 'Wangan', 'Wines'
    ];

    document.addEventListener('DOMContentLoaded', function () {
        var form = document.getElementById('checkout-form');

        /* Populate barangay dropdown */
        initBarangays();

        if (form) {
            form.addEventListener('submit', function (e) {
                if (!validateCheckout()) {
                    e.preventDefault();
                    return;
                }
                /* Combine street + barangay into hidden shipping_address */
                combineAddress();
            });
        }

        /* Payment Method Toggle */
        document.querySelectorAll('input[name="payment_method"]').forEach(function (radio) {
            radio.addEventListener('change', function () {
                /* Toggle active class on options */
                document.querySelectorAll('.co-pay-opt').forEach(function (opt) {
                    opt.classList.remove('active');
                });
                this.closest('.co-pay-opt').classList.add('active');
            });
        });

        /* Auto-format phone number */
        var phoneInput = document.getElementById('contact_phone');
        if (phoneInput) {
            phoneInput.addEventListener('input', function () {
                this.value = this.value.replace(/[^\d+\-() ]/g, '');
            });
        }

        /* Card details are collected on the payment gateway page, not here */
    });

    /* ── Barangay Dropdown ── */
    function initBarangays() {
        var select = document.getElementById('shipping_barangay');
        if (!select) return;

        var html = '<option value="">Select Barangay</option>';
        DAVAO_BARANGAYS.forEach(function (b) {
            html += '<option value="' + b + '">' + b + '</option>';
        });
        select.innerHTML = html;
    }

    /* ── Combine address fields before submit ── */
    function combineAddress() {
        var brgy   = (document.getElementById('shipping_barangay') || {}).value || '';
        var street = (document.getElementById('street_address') || {}).value || '';
        var hidden = document.getElementById('shipping_address_hidden');
        if (!hidden) return;

        var parts = [];
        if (street.trim()) parts.push(street.trim());
        if (brgy) parts.push('Brgy. ' + brgy);
        parts.push('Davao City');

        hidden.value = parts.join(', ');
    }

    /* ── Validation helpers ── */
    function markInvalid(el, message) {
        if (!el) return;
        el.classList.add('is-invalid');
        var existing = el.parentNode.querySelector('.field-error');
        if (existing) existing.remove();
        var err = document.createElement('span');
        err.className = 'field-error';
        err.style.cssText = 'color:var(--danger);font-size:12px;margin-top:4px;display:block;';
        err.textContent = message || 'Invalid field';
        el.parentNode.appendChild(err);
    }

    function validateCheckout() {
        var required = ['shipping_barangay', 'street_address', 'contact_phone'];
        var valid = true;

        /* Clear previous errors */
        document.querySelectorAll('.form-control.is-invalid').forEach(function (el) {
            el.classList.remove('is-invalid');
        });
        document.querySelectorAll('.field-error').forEach(function (el) { el.remove(); });

        required.forEach(function (fieldName) {
            var input = document.getElementById(fieldName);
            if (input && !input.value.trim()) {
                markInvalid(input, 'This field is required');
                valid = false;
            }
        });

        /* Phone format check */
        var phone = document.getElementById('contact_phone');
        if (phone && phone.value.trim()) {
            var digits = phone.value.replace(/\D/g, '');
            if (digits.length < 10 || digits.length > 13) {
                markInvalid(phone, 'Enter a valid phone number');
                valid = false;
            }
        }

        /* Check payment method selected */
        var checkedPayment = document.querySelector('input[name="payment_method"]:checked');
        if (!checkedPayment) {
            if (typeof showNotification === 'function') showNotification('Please select a payment method', 'warning');
            valid = false;
        }

        /* Card validation when Card is selected */
        if (checkedPayment && checkedPayment.value === 'card') {
            var cardNumber = document.getElementById('card_number');
            var cardName   = document.getElementById('card_name');
            var cardExpiry = document.getElementById('card_expiry');
            var cardCvc    = document.getElementById('card_cvc');

            if (cardNumber) {
                var cDigits = (cardNumber.value || '').replace(/\D/g, '');
                if (cDigits.length !== 16) {
                    markInvalid(cardNumber, 'Card number must be 16 digits');
                    valid = false;
                }
            }

            if (cardName) {
                var nameVal = (cardName.value || '').trim();
                if (!nameVal || !/^[A-Za-z\s\-']+$/.test(nameVal)) {
                    markInvalid(cardName, 'Enter cardholder name (letters only)');
                    valid = false;
                }
            }

            if (cardExpiry) {
                var expRaw = (cardExpiry.value || '').replace(/\D/g, '');
                if (expRaw.length !== 4) {
                    markInvalid(cardExpiry, 'Expiry must be MM/YY');
                    valid = false;
                } else {
                    var mm = parseInt(expRaw.slice(0, 2), 10);
                    var yy = parseInt(expRaw.slice(2), 10);
                    if (isNaN(mm) || mm < 1 || mm > 12) {
                        markInvalid(cardExpiry, 'Invalid expiry month');
                        valid = false;
                    } else {
                        var now = new Date();
                        var year = 2000 + yy;
                        var expDate = new Date(year, mm, 0); // last day of month
                        if (expDate < now) {
                            markInvalid(cardExpiry, 'Card has expired');
                            valid = false;
                        }
                    }
                }
            }

            if (cardCvc) {
                var cvc = (cardCvc.value || '').replace(/\D/g, '');
                if (cvc.length < 3 || cvc.length > 4) {
                    markInvalid(cardCvc, 'CVC must be 3 or 4 digits');
                    valid = false;
                }
            }
        }

        if (!valid) {
            if (typeof showNotification === 'function') showNotification('Please complete all required fields', 'error');
            var firstErr = document.querySelector('.is-invalid');
            if (firstErr) firstErr.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }

        return valid;
    }

    window.validateCheckout = validateCheckout;

})();
