/**
 * Stress ramp — gradually increase load until failure or max VUs.
 * Default max: 100 VUs. Stop early if Hostinger throttles.
 *
 * Run: k6 run tools/load-tests/03-stress-ramp.js
 * Custom max: k6 run -e MAX_VUS=75 tools/load-tests/03-stress-ramp.js
 */

import http from 'k6/http';
import { check, sleep } from 'k6';
import { BASE_URL, pageUrl, randomProductUrl } from './lib/config.js';

const MAX_VUS = parseInt(__ENV.MAX_VUS || '100', 10);

export const options = {
  stages: [
    { duration: '1m', target: 20 },
    { duration: '1m', target: 40 },
    { duration: '1m', target: 60 },
    { duration: '1m', target: MAX_VUS },
    { duration: '2m', target: MAX_VUS },
    { duration: '1m', target: 0 },
  ],
  thresholds: {
    http_req_failed: ['rate<0.10'],
    http_req_duration: ['p(95)<8000'],
  },
  tags: { test: 'stress-ramp' },
};

export default function () {
  const pick = Math.random();
  let url;
  let tag;

  if (pick < 0.6) {
    url = pageUrl('products');
    tag = 'products_list';
  } else if (pick < 0.9) {
    url = randomProductUrl();
    tag = 'product_detail';
  } else {
    url = pageUrl('');
    tag = 'home';
  }

  const res = http.get(url, { tags: { name: tag }, timeout: '45s' });

  check(res, {
    'status ok (200 or 304)': (r) => r.status === 200 || r.status === 304,
    'not 503': (r) => r.status !== 503,
    'not 502': (r) => r.status !== 502,
  });

  sleep(Math.random() * 1.5 + 0.3);
}

export function handleSummary(data) {
  const m = data.metrics;
  const failed = m.http_req_failed?.values?.rate ?? 0;
  const p95 = m.http_req_duration?.values?.['p(95)'] ?? 0;
  const reqs = m.http_reqs?.values?.count ?? 0;
  const vusMax = m.vus_max?.values?.max ?? MAX_VUS;

  let advice = 'System handled load well at this level.';
  if (failed >= 0.05) advice = 'High error rate — reduce concurrent users or upgrade hosting.';
  else if (p95 >= 5000) advice = 'Slow responses — consider caching or VPS upgrade.';

  return {
    stdout: [
      '',
      '=== STRESS RAMP TEST ===',
      `Target: ${BASE_URL}`,
      `Max VUs: ${vusMax}`,
      `Total requests: ${reqs}`,
      `Failed rate: ${(failed * 100).toFixed(2)}%`,
      `p95: ${p95.toFixed(0)} ms`,
      `Advice: ${advice}`,
      '',
    ].join('\n'),
  };
}
