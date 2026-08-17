/**
 * Quick smoke test — 5 virtual users, ~30 seconds.
 * Safe to run against production during low-traffic hours.
 *
 * Run: k6 run tools/load-tests/01-smoke.js
 */

import http from 'k6/http';
import { check, sleep } from 'k6';
import { BASE_URL, pageUrl, randomProductUrl, DEFAULT_THRESHOLDS } from './lib/config.js';

export const options = {
  vus: 5,
  duration: '30s',
  thresholds: DEFAULT_THRESHOLDS,
  tags: { test: 'smoke' },
};

export default function () {
  const urls = [
    pageUrl('products'),
    randomProductUrl(),
    pageUrl('about'),
  ];
  const target = urls[Math.floor(Math.random() * urls.length)];

  const res = http.get(target, {
    tags: { name: 'smoke_page' },
    timeout: '30s',
  });

  check(res, {
    'status is 200': (r) => r.status === 200,
    'body not empty': (r) => r.body && r.body.length > 500,
    'no server error page': (r) => !r.body.includes('A system error occurred'),
  });

  sleep(Math.random() * 2 + 0.5);
}

export function handleSummary(data) {
  return {
    stdout: formatSummary('SMOKE TEST', data),
  };
}

function formatSummary(label, data) {
  const m = data.metrics;
  const failed = m.http_req_failed?.values?.rate ?? 0;
  const p95 = m.http_req_duration?.values?.['p(95)'] ?? 0;
  const reqs = m.http_reqs?.values?.count ?? 0;

  return [
    '',
    `=== ${label} ===`,
    `Target: ${BASE_URL}`,
    `Requests: ${reqs}`,
    `Failed rate: ${(failed * 100).toFixed(2)}%`,
    `p95 duration: ${p95.toFixed(0)} ms`,
    failed < 0.05 && p95 < 5000 ? 'Result: PASS' : 'Result: REVIEW (slow or errors)',
    '',
  ].join('\n');
}
