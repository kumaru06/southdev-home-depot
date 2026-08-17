/**
 * Authenticated cart flow — LOCAL ONLY (localhost).
 *
 * Production login requires reCAPTCHA, so this test targets local XAMPP
 * where reCAPTCHA is usually disabled when keys are empty.
 *
 * Prerequisites:
 *   1. Local site running: http://localhost/southdev-home-depot
 *   2. Test user exists (see tools/insert_demo_user.php)
 *   3. Product ID with stock (default: 1)
 *
 * Run:
 *   k6 run -e BASE_URL=http://localhost/southdev-home-depot tools/load-tests/04-local-cart.js
 *   k6 run -e BASE_URL=http://localhost/southdev-home-depot -e TEST_EMAIL=demo@example.com -e TEST_PASSWORD=Demo1234 tools/load-tests/04-local-cart.js
 */

import http from 'k6/http';
import { check, sleep } from 'k6';
import { BASE_URL, INDEX, productUrl } from './lib/config.js';

const TEST_EMAIL = __ENV.TEST_EMAIL || 'inventory@demo.local';
const TEST_PASSWORD = __ENV.TEST_PASSWORD || 'Demo@1234';
const PRODUCT_ID = __ENV.PRODUCT_ID || '1';

export const options = {
  vus: 10,
  duration: '1m',
  thresholds: {
    http_req_failed: ['rate<0.10'],
    checks: ['rate>0.80'],
  },
  tags: { test: 'local-cart' },
};

function extractCsrf(html) {
  const match = html.match(/name="csrf_token"\s+value="([^"]+)"/);
  return match ? match[1] : null;
}

export default function () {
  const jar = http.cookieJar();

  // 1. Load products page to get session + CSRF
  const warm = http.get(`${INDEX}?url=products`, { jar, timeout: '30s' });
  if (warm.status !== 200) {
    sleep(1);
    return;
  }

  const csrf = extractCsrf(warm.body);
  if (!csrf) {
    sleep(1);
    return;
  }

  // 2. Login (AJAX-style — skips full page redirect)
  const loginRes = http.post(
    `${INDEX}?url=login`,
    {
      csrf_token: csrf,
      email: TEST_EMAIL,
      password: TEST_PASSWORD,
    },
    {
      jar,
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
        Accept: 'application/json',
        'Content-Type': 'application/x-www-form-urlencoded',
      },
      timeout: '30s',
      tags: { name: 'login' },
    }
  );

  const loginOk = check(loginRes, {
    'login response ok': (r) => r.status === 200,
    'login success json': (r) => {
      try {
        const data = JSON.parse(r.body);
        return data.success === true;
      } catch {
        return false;
      }
    },
  });

  if (!loginOk) {
    sleep(1);
    return;
  }

  // 3. Fresh CSRF after login (session may rotate token)
  const cartPage = http.get(`${INDEX}?url=cart`, { jar, timeout: '30s' });
  const csrf2 = extractCsrf(cartPage.body) || csrf;

  // 4. Add to cart
  const addRes = http.post(
    `${INDEX}?url=cart/add`,
    {
      csrf_token: csrf2,
      product_id: PRODUCT_ID,
      quantity: '1',
    },
    {
      jar,
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
        Accept: 'application/json',
        'Content-Type': 'application/x-www-form-urlencoded',
      },
      timeout: '30s',
      tags: { name: 'cart_add' },
    }
  );

  check(addRes, {
    'add to cart 200': (r) => r.status === 200,
    'add to cart success': (r) => {
      try {
        return JSON.parse(r.body).success === true;
      } catch {
        return false;
      }
    },
  });

  // 5. View cart
  const viewCart = http.get(`${INDEX}?url=cart`, {
    jar,
    timeout: '30s',
    tags: { name: 'cart_view' },
  });
  check(viewCart, { 'cart page 200': (r) => r.status === 200 });

  // 6. Product detail (browse while logged in)
  http.get(productUrl(PRODUCT_ID), { jar, timeout: '30s', tags: { name: 'product_detail' } });

  sleep(Math.random() * 2 + 1);
}

export function handleSummary(data) {
  return {
    stdout: [
      '',
      '=== LOCAL CART TEST ===',
      `Target: ${BASE_URL}`,
      `Test user: ${TEST_EMAIL}`,
      `Product ID: ${PRODUCT_ID}`,
      '',
      'Note: Only works on localhost where reCAPTCHA is disabled.',
      'If login checks fail, verify demo user credentials.',
      '',
    ].join('\n'),
  };
}
