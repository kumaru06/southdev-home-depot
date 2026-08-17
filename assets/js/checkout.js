/**
 * SouthDev Home Depot – Checkout JavaScript
 * Davao City barangay selector, delivery fee, form validation, payment toggle
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

    function getDeliveryConfig() {
        return window.DELIVERY_FEE_CONFIG || {
            subtotal: 0,
            free_threshold: 10000,
            fees: { near: 300, mid: 400, far: 500 },
            zones: {}
        };
    }

    function normalizeBarangay(name) {
        return String(name || '')
            .toLowerCase()
            .replace(/^brgy\.?\s*/i, '')
            .replace(/\s+/g, ' ')
            .trim();
    }

    function resolveZone(barangay) {
        var cfg = getDeliveryConfig();
        var key = normalizeBarangay(barangay);
        if (!key) return 'mid';
        if (cfg.zones && cfg.zones[key]) return cfg.zones[key];
        var zones = cfg.zones || {};
        for (var z in zones) {
            if (!Object.prototype.hasOwnProperty.call(zones, z)) continue;
            if (key.indexOf(z) !== -1 || z.indexOf(key) !== -1) return zones[z];
        }
        return 'mid';
    }

    function formatPeso(amount) {
        return '₱' + Number(amount).toLocaleString('en-PH', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

    function setProgress(percent) {
        var bar = document.getElementById('co-delivery-progress');
        if (!bar) return;
        var pct = Math.max(0, Math.min(100, percent));
        bar.style.width = pct + '%';
    }

    function updateDeliveryFee() {
        var cfg = getDeliveryConfig();
        var brgyEl = document.getElementById('shipping_barangay');
        var feeEl = document.getElementById('co-delivery-fee');
        var zoneEl = document.getElementById('co-delivery-zone');
        var totalEl = document.getElementById('co-grand-total');
        var banner = document.getElementById('co-delivery-hint');
        var titleEl = document.getElementById('co-delivery-hint-title');
        var amountEl = document.getElementById('co-delivery-hint-amount');
        var textEl = document.getElementById('co-delivery-hint-text');
        if (!feeEl || !totalEl) return;

        var subtotal = Number(cfg.subtotal || 0);
        var threshold = Number(cfg.free_threshold || 10000);
        var remaining = Math.max(0, threshold - subtotal);
        var progress = threshold > 0 ? (subtotal / threshold) * 100 : 0;
        var barangay = brgyEl ? brgyEl.value : '';

        function setBanner(state, title, amount, copy) {
            if (banner) banner.setAttribute('data-state', state);
            if (titleEl) titleEl.textContent = title;
            if (amountEl) amountEl.textContent = amount;
            if (textEl) textEl.textContent = copy;
            setProgress(state === 'free' ? 100 : progress);
        }

        if (subtotal >= threshold) {
            feeEl.textContent = 'FREE';
            feeEl.className = 'co-free';
            if (zoneEl) {
                zoneEl.hidden = true;
                zoneEl.textContent = '';
                zoneEl.removeAttribute('data-zone');
            }
            totalEl.textContent = formatPeso(subtotal);
            setBanner('free', 'Free delivery unlocked', formatPeso(threshold) + '+', 'Your order qualifies for free Davao delivery.');
            return;
        }

        if (!barangay) {
            feeEl.textContent = 'Select barangay';
            feeEl.className = '';
            if (zoneEl) {
                zoneEl.hidden = true;
                zoneEl.textContent = '';
                zoneEl.removeAttribute('data-zone');
            }
            totalEl.textContent = formatPeso(subtotal);
            setBanner(
                'idle',
                'Free delivery',
                formatPeso(threshold) + '+',
                'Add ' + formatPeso(remaining) + ' more for free delivery. Choose a barangay to see your fee.'
            );
            return;
        }

        var zone = resolveZone(barangay);
        var fees = cfg.fees || { near: 300, mid: 400, far: 500 };
        var fee = Number(fees[zone] != null ? fees[zone] : fees.mid);
        var zoneLabel = zone.charAt(0).toUpperCase() + zone.slice(1);

        feeEl.textContent = formatPeso(fee);
        feeEl.className = '';
        if (zoneEl) {
            zoneEl.hidden = false;
            zoneEl.textContent = zoneLabel;
            zoneEl.setAttribute('data-zone', zone);
        }
        totalEl.textContent = formatPeso(subtotal + fee);
        setBanner(
            'zone',
            zoneLabel + ' zone',
            formatPeso(fee) + ' fee',
            'Add ' + formatPeso(remaining) + ' more to unlock free delivery.'
        );
    }

    document.addEventListener('DOMContentLoaded', function () {
        var form = document.getElementById('checkout-form');

        initBarangays();
        updateDeliveryFee();

        var brgySelect = document.getElementById('shipping_barangay');
        if (brgySelect) {
            brgySelect.addEventListener('change', updateDeliveryFee);
        }

        var useSavedBtn = document.getElementById('useSavedAddr');
        if (useSavedBtn) {
            useSavedBtn.addEventListener('click', function () {
                var address = this.dataset.address || '';
                var zip     = this.dataset.zip     || '8000';
                var phone   = this.dataset.phone   || '';

                var streetEl = document.getElementById('street_address');
                if (streetEl && address) {
                    streetEl.value = address;
                    streetEl.classList.remove('is-invalid');
                }

                var zipEl = document.getElementById('shipping_zip');
                if (zipEl && zip) zipEl.value = zip;

                var phoneEl = document.getElementById('contact_phone');
                if (phoneEl && phone) {
                    phoneEl.value = phone;
                    phoneEl.classList.remove('is-invalid');
                }

                var brgySelectEl = document.getElementById('shipping_barangay');
                if (brgySelectEl && address) {
                    var normalized = address.toLowerCase();
                    for (var i = 0; i < DAVAO_BARANGAYS.length; i++) {
                        if (normalized.indexOf(DAVAO_BARANGAYS[i].toLowerCase()) !== -1) {
                            brgySelectEl.value = DAVAO_BARANGAYS[i];
                            brgySelectEl.classList.remove('is-invalid');
                            break;
                        }
                    }
                }

                updateDeliveryFee();

                this.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="width:13px;height:13px;"><polyline points="20 6 9 17 4 12"></polyline></svg> Applied';
                this.disabled = true;
                this.style.opacity = '0.7';
            });
        }

        if (form) {
            form.addEventListener('submit', function (e) {
                if (!validateCheckout()) {
                    e.preventDefault();
                    return;
                }
                combineAddress();
            });
        }

        document.querySelectorAll('input[name="payment_method"]').forEach(function (radio) {
            radio.addEventListener('change', function () {
                document.querySelectorAll('.co-pay-opt').forEach(function (opt) {
                    opt.classList.remove('active');
                });
                this.closest('.co-pay-opt').classList.add('active');
            });
        });

        var phoneInput = document.getElementById('contact_phone');
        if (phoneInput) {
            phoneInput.addEventListener('input', function () {
                this.value = this.value.replace(/[^\d+\-() ]/g, '');
            });
        }
    });

    function initBarangays() {
        var select = document.getElementById('shipping_barangay');
        if (!select) return;

        var html = '<option value="">Select Barangay</option>';
        DAVAO_BARANGAYS.forEach(function (b) {
            html += '<option value="' + b + '">' + b + '</option>';
        });
        select.innerHTML = html;
    }

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

        var phone = document.getElementById('contact_phone');
        if (phone && phone.value.trim()) {
            var digits = phone.value.replace(/\D/g, '');
            if (digits.length < 10 || digits.length > 13) {
                markInvalid(phone, 'Enter a valid phone number');
                valid = false;
            }
        }

        var checkedPayment = document.querySelector('input[name="payment_method"]:checked');
        if (!checkedPayment) {
            if (typeof showNotification === 'function') showNotification('Please select a payment method', 'warning');
            valid = false;
        }

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
                        var expDate = new Date(year, mm, 0);
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
    window.updateDeliveryFee = updateDeliveryFee;

})();
