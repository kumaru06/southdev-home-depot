/**
 * Shared config for SouthDev Home Depot k6 load tests.
 *
 * Override via environment:
 *   BASE_URL=https://southdevhomedepotdavao.com
 *   PRODUCT_IDS=1,2,3,4,5
 */

export const BASE_URL = (__ENV.BASE_URL || 'https://southdevhomedepotdavao.com').replace(/\/$/, '');

export const INDEX = `${BASE_URL}/index.php`;

export const PRODUCT_IDS = (__ENV.PRODUCT_IDS || '1,2,3,4,5')
  .split(',')
  .map((s) => s.trim())
  .filter(Boolean);

export const PUBLIC_PAGES = [
  'products',
  'about',
  'locations',
  'faqs',
  'featured-collections',
];

export function pageUrl(path) {
  return `${INDEX}?url=${path}`;
}

export function productUrl(id) {
  return `${INDEX}?url=products/${id}`;
}

export function randomProductUrl() {
  const id = PRODUCT_IDS[Math.floor(Math.random() * PRODUCT_IDS.length)];
  return productUrl(id);
}

export function randomPublicPageUrl() {
  const path = PUBLIC_PAGES[Math.floor(Math.random() * PUBLIC_PAGES.length)];
  return pageUrl(path);
}

export const DEFAULT_THRESHOLDS = {
  http_req_failed: ['rate<0.05'],
  http_req_duration: ['p(95)<5000'],
};

export const STRICT_THRESHOLDS = {
  http_req_failed: ['rate<0.02'],
  http_req_duration: ['p(95)<3000', 'p(99)<6000'],
};
