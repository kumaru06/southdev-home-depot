/**
 * Browse load test — ramp to 50 concurrent users browsing products.
 * Read-only public pages only (safe for production with caution).
 *
 * Run: k6 run tools/load-tests/02-browse-50.js
 */

import http from 'k6/http';
import { check, sleep } from 'k6';
import {
  BASE_URL,
  pageUrl,
  randomProductUrl,
  randomPublicPageUrl,
  STRICT_THRESHOLDS,
} from './lib/config.js';

export const options = {
  stages: [
    { duration: '30s', target: 10 },
    { duration: '1m', target: 30 },
    { duration: '1m', target: 50 },
    { duration: '2m', target: 50 },
    { duration: '30s', target: 0 },
  ],
  thresholds: STRICT_THRESHOLDS,
  tags: { test: 'browse-50' },
};

export default function () {
  // Realistic mix: list -> detail -> another page
  const listRes = http.get(pageUrl('products'), {
    tags: { name: 'products_list' },
    timeout: '30s',
  });
  check(listRes, {
    'products list 200': (r) => r.status === 200,
  });

  sleep(Math.random() + 0.5);

  const detailRes = http.get(randomProductUrl(), {
    tags: { name: 'product_detail' },
    timeout: '30s',
  });
  check(detailRes, {
    'product detail 200': (r) => r.status === 200,
    'has product page marker': (r) =>
      r.body.includes('pd-page') || r.body.includes('product'),
  });

  sleep(Math.random() + 0.5);

  if (Math.random() > 0.5) {
    const extraRes = http.get(randomPublicPageUrl(), {
      tags: { name: 'public_page' },
      timeout: '30s',
    });
    check(extraRes, { 'public page 200': (r) => r.status === 200 });
  }

  sleep(Math.random() * 2 + 1);
}

export function handleSummary(data) {
  const m = data.metrics;
  const failed = m.http_req_failed?.values?.rate ?? 0;
  const p95 = m.http_req_duration?.values?.['p(95)'] ?? 0;
  const p99 = m.http_req_duration?.values?.['p(99)'] ?? 0;
  const reqs = m.http_reqs?.values?.count ?? 0;

  return {
    stdout: [
      '',
      '=== BROWSE 50 TEST ===',
      `Target: ${BASE_URL}`,
      `Total requests: ${reqs}`,
      `Failed rate: ${(failed * 100).toFixed(2)}%`,
      `p95: ${p95.toFixed(0)} ms`,
      `p99: ${p99.toFixed(0)} ms`,
      '',
      'Pass criteria: failed < 2%, p95 < 3000ms',
      failed < 0.02 && p95 < 3000 ? 'Result: PASS' : 'Result: NEEDS ATTENTION',
      '',
    ].join('\n'),
  };
}
