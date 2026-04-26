# 🚀 QUICK START GUIDE - Estimation Time Feature

## ⚡ 5-Minute Setup

### 1. **Verify Backend Ready**
```bash
# Terminal 1: Backend
cd backend
php artisan serve
# Check: http://localhost:8000/api/tenant/orders (should work)
```

### 2. **Verify Database Migration**
```bash
# Check if estimation_time column exists
php artisan migrate
# or check directly
php artisan tinker
> DB::select('DESCRIBE orders')
# Should show: estimation_time | timestamp | YES | NO
```

### 3. **Update Frontend Files** ✅
Already done! These 3 files updated:
- `frontend/src/pages/tenant.f7`
- `frontend/src/pages/riwayat-transaksi.f7`
- `frontend/src/pages/tenant-riwayat.f7`

### 4. **Start Frontend**
```bash
# Terminal 2: Frontend
cd frontend
npm run dev
# Frontend should hot-reload
```

### 5. **Clear Cache & Test**
- Open browser DevTools → Application → Clear Site Data
- Refresh page (Ctrl+Shift+R)
- Done! ✅

---

## 🧪 Quick Test (3 Minutes)

### Test Setup
1. **Open 2 Browser Instances**
   - Window 1: Tenant login
   - Window 2: Customer login

### Test Steps

**Step 1: Customer Order**
- Customer: Click menu → Add to cart → Checkout
- Order should appear in Tenant's "Beranda" → "Baru" section

**Step 2: Tenant Approve**
- Tenant: Click order → "Approve & Proses"
- Input: `3` (3 minutes for quick test)
- Click OK

**Step 3: Verify Both Views**
- Tenant → "Beranda": Should show timer `Est: 00:03:00`
- Tenant → "Riwayat": Section "Sedang Diproses" shows same order with timer
- Customer → "Riwayat Transaksi": Should show timer `⏱️ 00:03:00`

**Step 4: Watch Countdown**
- Count 10 seconds manually
- Timer should decrease by ~10 seconds
- Difference between tenant & customer ≤ 2 seconds → ✅ PASS

**Step 5: Wait for Completion** (Optional, takes 3 minutes)
- Wait until timer reaches `00:00:00`
- Should show: `Siap Disajikan!` (green)
- Both views should show same message

---

## 📋 Checklist Before Production

- [ ] Backend running on port 8000
- [ ] Database migrated (`estimation_time` column exists)
- [ ] Frontend files updated (3 files)
- [ ] Browser cache cleared
- [ ] Quick test passed (timer visible in both views)
- [ ] Server polling works (timer syncs every 10 seconds)
- [ ] Multiple orders countdown independently
- [ ] Timer stops at "Siap Disajikan!" when zero

---

## 🔧 Troubleshooting Quick Fixes

| Issue | Solution |
|-------|----------|
| **Timer not visible** | 1. Refresh page (Ctrl+Shift+R)<br/>2. Check console for errors<br/>3. Verify API endpoint works |
| **Timer jumping around** | 1. Normal, happens during polling sync<br/>2. Wait 10 seconds for stabilization<br/>3. Check network in DevTools |
| **Tenant & Customer not synced** | 1. Wait 10 seconds for polling<br/>2. Refresh customer view<br/>3. Check server logs for API errors |
| **JavaScript errors in console** | 1. Verify files updated correctly<br/>2. Check calculateRemainingSeconds function exists<br/>3. Browser console → Network → check API calls |

---

## 📁 Files to Verify

### Backend (No changes needed)
- `app/Http/Controllers/OrderController.php` ✅ (Already supports estimation_time)
- `database/migrations/2026_04_16_124731_add_estimation_time_to_orders_table.php` ✅ (Already migrated)

### Frontend (Updated ✅)
- `frontend/src/pages/tenant.f7` ✅ UPDATED
- `frontend/src/pages/riwayat-transaksi.f7` ✅ UPDATED  
- `frontend/src/pages/tenant-riwayat.f7` ✅ UPDATED

### Documentation (Reference)
- `ESTIMATION_TIME_FEATURE.md` - User guide
- `IMPLEMENTATION_SUMMARY.md` - Technical details
- `TEST_CASES.md` - Full QA suite
- `QUICK_START_GUIDE.md` - This file

---

## 🎯 Key Features Summary

| Feature | Status | View |
|---------|--------|------|
| Set estimation time | ✅ | Tenant "Beranda" |
| Real-time countdown | ✅ | All views |
| Format HH:MM:SS | ✅ | 00:15:30 |
| Sync between views | ✅ | 10-sec polling |
| Complete message | ✅ | "Siap Disajikan!" |
| Multiple timers | ✅ | Independent |

---

## 📱 User Workflows

### Tenant Flow
```
1. See new order
   ↓
2. Click "Approve & Proses"
   ↓
3. Input estimation (e.g., 15 min)
   ↓
4. Timer starts: 00:15:00 → 00:14:59 → ...
   ↓
5. Monitor in "Beranda" or "Riwayat"
   ↓
6. When timer = 00:00:00 → "Siap Disajikan!"
   ↓
7. Mark "Selesai"
```

### Customer Flow
```
1. Order created
   ↓
2. Status: "Belum Bayar" → "Berjalan" (after payment)
   ↓
3. See timer in "Riwayat Transaksi"
   ↓
4. Watch countdown: 00:15:30 → 00:15:29 → ...
   ↓
5. When = 00:00:00 → "Siap Disajikan!" (green)
   ↓
6. Go get the order!
```

---

## 🔍 Code Review Checklist

### Functions Added
- ✅ `calculateRemainingSeconds(estimationTime)` - Calculates remaining time from timestamp
- ✅ `formatTimeFromSeconds(totalSeconds)` - Formats seconds to HH:MM:SS
- ✅ Polling intervals - 1 second (UI) + 10 second (server)

### Data Flow
- ✅ Backend sends: `"estimation_time": "2026-04-26 15:30:00"` (timestamp)
- ✅ Frontend calculates: Current time vs estimation time
- ✅ Display: `"00:15:30"` format

### UI Elements
- ✅ Tenant view: `Est: 00:15:30` (purple badge)
- ✅ Customer view: `⏱️ 00:15:30` (purple with emoji)
- ✅ Completion: `Siap Disajikan!` (green)

---

## 🚦 Deployment Readiness

### Pre-Deployment
- [x] Code reviewed
- [x] Test cases created
- [x] Documentation complete
- [x] Edge cases handled
- [x] Error handling added
- [x] Performance tested

### Deployment Steps
1. Pull latest code
2. Run `php artisan migrate` (if needed)
3. Clear Laravel cache: `php artisan cache:clear`
4. Restart backend service
5. Push frontend files
6. Clear browser cache on clients
7. Run quick test (3 minutes)
8. Monitor for 1 hour for issues

### Post-Deployment
- Monitor error logs
- Check user feedback
- Verify sync accuracy
- Monitor performance/resource usage

---

## 📞 Support & Escalation

### If Issue Occurs
1. **Check Browser Console** (F12 → Console)
   - Any JavaScript errors?
   - Any network errors (red)?

2. **Check Network Tab** (F12 → Network)
   - API calls happening?
   - Response status 200?

3. **Check Server Logs**
   ```bash
   tail -f backend/storage/logs/laravel.log
   ```

4. **Still broken?**
   - Verify files updated correctly
   - Verify database migration ran
   - Verify backend API accessible
   - Check timestamps in database

---

## 📊 Monitoring Metrics

After deployment, monitor:
- **API Response Time**: Should be < 100ms
- **Polling Success Rate**: Should be > 99%
- **Timer Accuracy**: Should be ±1 second
- **CPU Usage**: Should not spike with multiple timers
- **Memory Usage**: Should remain stable

---

## 🎓 Learning Resources

### To understand the feature:
- Read: `ESTIMATION_TIME_FEATURE.md` (user perspective)
- Read: `IMPLEMENTATION_SUMMARY.md` (technical details)
- Study: The 3 updated `.f7` files
- Review: `TEST_CASES.md` for edge cases

### To test it:
- Follow: Quick test above (3 minutes)
- Run: Test suite from `TEST_CASES.md`
- Monitor: Browser console + network tab

### To debug issues:
- Use: Browser DevTools (F12)
- Use: Laravel Tinker for database checks
- Use: Network monitor to see API calls
- Use: Server logs for backend errors

---

## ✅ Final Verification

Before going live, verify:

```javascript
// Run in browser console on each page:
typeof calculateRemainingSeconds === 'function'  // Should be true
typeof formatTimeFromSeconds === 'function'       // Should be true
```

If both return `true` → Files updated correctly ✅

---

**Quick Start Version**: 1.0  
**Date**: 26 April 2026  
**Status**: Ready to Deploy ✅

For detailed information, see `IMPLEMENTATION_SUMMARY.md`
