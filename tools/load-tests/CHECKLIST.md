# Load Test Checklist — SouthDev Home Depot

Use this after running k6 tests or manual concurrent checkout tests.

## Before testing

- [ ] Prefer **low-traffic hours** (early morning) for production tests
- [ ] Note current **Hostinger plan** and resource usage baseline
- [ ] Confirm **product IDs** exist (`PRODUCT_IDS=1,2,3` env var)
- [ ] For checkout tests: product has **50+ stock**

## Automated tests (k6)

```powershell
# Install k6 once
winget install k6 --source winget

# Quick smoke (5 users, 30s) — safest for production
.\tools\load-tests\run.ps1 smoke

# Browse ramp to 50 users (~5 min)
.\tools\load-tests\run.ps1 browse

# Find breaking point (up to 100 VUs) — use carefully
.\tools\load-tests\run.ps1 stress

# Local cart flow (localhost only, needs demo user)
.\tools\load-tests\run.ps1 local-cart
```

Custom target:

```powershell
.\tools\load-tests\run.ps1 smoke -BaseUrl "http://localhost/southdev-home-depot"
k6 run -e BASE_URL=https://southdevhomedepotdavao.com -e PRODUCT_IDS=1,2,3 tools/load-tests/02-browse-50.js
```

## Pass criteria

| Test | Failed requests | p95 response |
|------|-----------------|--------------|
| Smoke | < 5% | < 5s |
| Browse 50 | < 2% | < 3s |
| Stress | < 10% | < 8s |

## Manual checkout test (10 users)

Production checkout cannot be automated easily (reCAPTCHA on login).

1. Create **10 test customer accounts**
2. Add same product to each cart (stock ≥ 50)
3. Click **Place Order** within 10 seconds on all accounts
4. Verify in admin/staff panel:
   - [ ] Order count matches successful checkouts
   - [ ] Stock reduced correctly (no negative inventory)
   - [ ] No duplicate charges / payment errors

## During test — watch hPanel

- [ ] CPU usage
- [ ] Memory usage
- [ ] Entry processes / PHP workers
- [ ] MySQL — no "Too many connections" in error logs

## After test — record results

| Date | Test | Max VUs | Failed % | p95 (ms) | Notes |
|------|------|---------|----------|----------|-------|
|      |      |         |          |          |       |

## If tests fail

1. **503 / 502 errors** → hosting limit hit; reduce concurrent users or upgrade plan
2. **Slow p95 (>5s)** → enable OPcache, add DB indexes, cache product listing
3. **Stock mismatch after checkout** → review `Inventory::reserveQuantity` and order transaction flow

## Safety

- Do **not** run stress test (100 VUs) repeatedly on production — Hostinger may throttle or suspend
- Ramp up gradually: smoke → browse → stress only if browse passes
- Stop immediately if real customers report slowness
