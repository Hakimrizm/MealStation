# 🧪 TEST CASES - Estimation Time Countdown Feature

## Test Environment Setup

**Prerequisites**:
- Backend running: `http://localhost:8000`
- Frontend running with updated files
- 2 browser instances (Tenant & Customer)
- Network monitor open (optional)

---

## TEST SUITE 1: Tenant Setting Estimation

### TC-1.1: Set Estimation Time - Valid Input
```
Scenario: Tenant sets 15 minutes estimation for new order

Steps:
1. Login as TENANT
2. Open "Beranda" tab
3. See new order with status "Perlu Konfirmasi"
4. Click order card → Opens action sheet
5. Click "Approve & Proses"
6. Prompt appears: "Berapa menit estimasi penyajian?"
7. Input: 15
8. Click OK

Expected Result:
✓ Order status changes to "Sedang Dimasak"
✓ Timer displays: "Est: 00:15:00"
✓ Timer starts counting down
✓ Toast appears: "Pesanan diproses!"

Pass Criteria: All ✓
```

### TC-1.2: Set Estimation Time - Edge Case (1 minute)
```
Scenario: Tenant sets very short estimation

Steps:
1. Repeat TC-1.1 but input: 1
2. Verify timer shows "Est: 00:01:00"
3. Wait ~1 minute

Expected Result:
✓ Timer counts: 00:01:00 → 00:00:59 → ... → 00:00:00
✓ After 0 seconds: "Siap Disajikan!" (green)

Pass Criteria: All ✓
```

### TC-1.3: Set Estimation Time - Edge Case (60+ minutes)
```
Scenario: Tenant sets long estimation

Steps:
1. Repeat TC-1.1 but input: 120 (2 hours)

Expected Result:
✓ Timer displays: "Est: 02:00:00"
✓ Counts down correctly with HH:MM:SS format
✓ Format never exceeds 00 for hours (if set correctly)

Pass Criteria: All ✓
```

### TC-1.4: Set Estimation - Cancel Action
```
Scenario: Tenant opens action sheet but cancels

Steps:
1. Click order → Opens action sheet
2. Click "Tutup / Batal"
3. Sheet closes

Expected Result:
✓ Order remains unchanged
✓ No timer starts
✓ Status stays "Perlu Konfirmasi"

Pass Criteria: All ✓
```

---

## TEST SUITE 2: Tenant Monitor Estimation

### TC-2.1: View Estimation in Beranda
```
Scenario: Tenant views active orders with timers

Setup:
- 3 orders in "Sedang Dimasak" with different timers

Steps:
1. Login as TENANT
2. Open "Beranda"
3. Apply filter "Proses"
4. Observe order cards

Expected Result:
✓ Each card shows: "Est: HH:MM:SS"
✓ All timers count down simultaneously
✓ Timers are independent (don't affect each other)
✓ 1 second = 1 second countdown (accurate)

Pass Criteria: All ✓
```

### TC-2.2: View Estimation in Riwayat & Rekap
```
Scenario: Tenant views history with in-process orders

Steps:
1. Login as TENANT
2. Go to "Riwayat" tab
3. Scroll up (above "Pesanan Selesai")
4. See section "Sedang Diproses"

Expected Result:
✓ Section shows active orders
✓ Each order displays: "#ID | Date | Items | Price | Status + Timer"
✓ Status pill: "Diproses" (orange)
✓ Timer displays: "⏱️ HH:MM:SS"
✓ Timers count down smoothly

Pass Criteria: All ✓
```

### TC-2.3: Real-Time Synchronization in Tenant View
```
Scenario: Verify server polling syncs data

Steps:
1. Open tenant.f7 (Beranda) with orders in "Proses"
2. Note timer value at T=0
3. Wait 10-12 seconds
4. Note timer value at T=10

Expected Result:
✓ Countdown smooth (10 seconds should pass ~10 seconds)
✓ Timer decremented ~10 seconds
✓ No jumps or skips
✓ Accurate to within ±1 second

Note: Server polling happens every 10 seconds
Pass Criteria: Countdown within ±1 second accuracy
```

---

## TEST SUITE 3: Customer Monitor Estimation

### TC-3.1: View Estimation in Riwayat Transaksi
```
Scenario: Customer sees active order with timer

Setup:
- Tenant has approved order + set estimation

Steps:
1. Login as CUSTOMER
2. Open "Riwayat Transaksi"
3. Find "Berjalan" orders

Expected Result:
✓ Order card displays:
  - Status badge: "Diproses" (blue)
  - Timer: "⏱️ HH:MM:SS" (purple)
✓ Timer starts from estimated time
✓ Timer counts down every second

Pass Criteria: All ✓
```

### TC-3.2: Timer Countdown Smooth
```
Scenario: Verify customer timer counts smoothly

Setup:
- 5 seconds into active countdown

Steps:
1. Observe timer display
2. Count 5 seconds mentally
3. Check timer reduced by ~5 seconds

Expected Result:
✓ 5 real seconds = 5 timer seconds
✓ No jumps or skips
✓ Smooth progression

Pass Criteria: Accurate countdown
```

### TC-3.3: Timer Completes - Ready Message
```
Scenario: Wait for timer to reach 00:00:00

Setup:
- Active order with short estimation (e.g., 2 minutes)

Steps:
1. Monitor timer
2. Wait until timer reaches 00:00:00
3. Observe status change

Expected Result:
✓ Timer changes to: "⏱️ Siap Disajikan!" (green)
✓ Status: "Siap Disajikan!"
✓ UI color changes to green (#34c759)

Pass Criteria: All ✓
```

---

## TEST SUITE 4: Real-Time Synchronization

### TC-4.1: Tenant & Customer Sync - Initial State
```
Scenario: Verify initial sync between tenant and customer

Setup:
- 2 instances ready (Tenant + Customer)
- Order NOT yet in process

Steps:
1. Tenant: Click "Approve & Proses" → Input 10 minutes
2. Immediately (0-2 seconds): Customer opens "Riwayat Transaksi"

Expected Result:
✓ Both see timer: "⏱️ 00:10:00" (or ~00:09:58-59)
✓ Difference ≤ 1-2 seconds maximum
✓ Both timers count down together

Pass Criteria: Sync within 2 seconds
```

### TC-4.2: Tenant Update, Customer Sees It
```
Scenario: Tenant changes order while customer watching

Setup:
- Both instances showing same order in process (10 min remaining)
- Customer watching the timer

Steps:
1. Tenant window: Wait 3 seconds
2. Tenant: Click order → Update status
3. Tenant: Check timer
4. Customer window: Wait for polling (10 seconds)
5. Customer: Check if sync

Expected Result:
✓ Customer timer eventually syncs with tenant
✓ Within 10 seconds (polling interval)
✓ Both showing same remaining time

Pass Criteria: Sync within polling interval (10s)
```

### TC-4.3: Multiple Orders Sync
```
Scenario: Multiple active orders stay synced

Setup:
- 3 active orders with different timers
- Both tenant & customer windows

Steps:
1. Note all timers in both windows
2. Wait 5 seconds
3. Check all timers have decreased by ~5 seconds
4. Check timers still in sync between windows

Expected Result:
✓ All timers count independently
✓ All remain synced between tenant & customer
✓ Countdown smooth and accurate

Pass Criteria: All ✓
```

---

## TEST SUITE 5: Edge Cases

### TC-5.1: Page Refresh During Countdown
```
Scenario: User refreshes page while timer active

Steps:
1. Tenant: Set estimation, observe timer
2. Wait 3 seconds
3. Refresh browser (F5)
4. Wait for page load

Expected Result:
✓ Timer resumes from server data
✓ Countdown continues correctly
✓ No data loss

Pass Criteria: All ✓
```

### TC-5.2: Browser Background (Out of Focus)
```
Scenario: Timer continues in background tab

Steps:
1. Open order with timer (10 min remaining)
2. Note time: 00:10:00
3. Switch to another tab
4. Wait 30 seconds (real time)
5. Switch back to MealStation tab
6. Check timer

Expected Result:
✓ Timer shows ~00:09:30 (or within ±2 seconds)
✓ Countdown doesn't stop
✓ Updates resume smoothly

Pass Criteria: Timer accurate to ±2 seconds
```

### TC-5.3: Slow Network Connection
```
Scenario: Verify countdown works with poor network

Setup:
- Use browser DevTools → Throttle to "Slow 3G"

Steps:
1. Set estimation with throttled network
2. Observe timer
3. Wait 15 seconds
4. Disable throttle
5. Wait 10 more seconds

Expected Result:
✓ Countdown still smooth (local update)
✓ Polling delayed but eventually syncs
✓ No "Error" messages
✓ Timer accurate after sync

Pass Criteria: Robust to slow network
```

### TC-5.4: Fast Forward Time (System Clock)
```
Scenario: Test with system clock changed

Steps:
1. Start order: 00:05:00
2. System clock: +2 minutes ahead
3. Refresh page
4. Check timer

Expected Result:
✓ Timer shows ~00:03:00 (adjusted to real time)
✓ Not stuck at 00:05:00
✓ Server data is source of truth

Pass Criteria: Timer syncs with server time
```

---

## TEST SUITE 6: UI/UX Behavior

### TC-6.1: Filter Orders While Timer Running
```
Scenario: Change filter on tenant beranda

Setup:
- 5 orders, 2 with status "Proses" + timers

Steps:
1. Tenant: Show all filters
2. Change filter: "Semua" → "Proses"
3. Observe timers
4. Change filter back: "Proses" → "Semua"
5. Change filter: "Proses" again

Expected Result:
✓ Timers visible only when filter shows them
✓ Timers continue counting when hidden
✓ Display accurate when filter shows them again
✓ No data loss during filter changes

Pass Criteria: All ✓
```

### TC-6.2: Search While Timer Running
```
Scenario: Search orders with timers active

Steps:
1. Tenant: Type customer name in search
2. Results show 1 order with active timer
3. Observe timer counts down
4. Clear search

Expected Result:
✓ Timers continue updating in filtered results
✓ Search doesn't interrupt countdown
✓ Timer resume after search clear

Pass Criteria: All ✓
```

### TC-6.3: Status Update During Countdown
```
Scenario: Tenant marks order "Done" while timer active

Steps:
1. Open order with active timer (00:08:00)
2. Change status to "Done"
3. Confirm status change
4. Order disappears from active list

Expected Result:
✓ Order removed from "Sedang Dimasak"
✓ Appears in "Selesai"
✓ No more timer display
✓ No errors in console

Pass Criteria: All ✓
```

---

## TEST SUITE 7: Error Handling

### TC-7.1: No Estimation Set
```
Scenario: Order in "Proses" but no estimation_time

Steps:
1. Backend: Manually set order status="process" but estimation_time=NULL
2. Tenant: Refresh page

Expected Result:
✓ Order shows: "Est: Belum diatur"
✓ No timer display
✓ No JavaScript errors in console

Pass Criteria: Graceful handling
```

### TC-7.2: Invalid Timestamp
```
Scenario: Corrupted estimation_time data

Steps:
1. Backend: Set estimation_time to invalid value
2. Frontend: Load page

Expected Result:
✓ No JavaScript errors
✓ Fallback display or error message
✓ Page still functional

Pass Criteria: No crash, graceful error handling
```

### TC-7.3: API Unreachable
```
Scenario: Server down during polling

Setup:
- Throttle network or stop backend server

Steps:
1. Order with active timer
2. Wait 10+ seconds (polling interval)
3. Check console
4. Restart backend/network

Expected Result:
✓ Local countdown continues (1 second updates)
✓ Error in console but no popup
✓ Auto-recovers when backend available

Pass Criteria: Resilient to API failures
```

---

## TEST SUITE 8: Performance

### TC-8.1: Multiple Timers Performance
```
Scenario: Many active orders (stress test)

Setup:
- 20+ orders all in "Proses" with timers

Steps:
1. Load page with many timers
2. Monitor CPU/RAM in DevTools
3. Wait 30 seconds
4. Check UI responsiveness

Expected Result:
✓ UI remains responsive
✓ No significant CPU spike
✓ Timers accurate
✓ No memory leak (RAM stable)

Pass Criteria: Smooth performance
```

### TC-8.2: Long Running Session
```
Scenario: Session runs for extended time (30+ minutes)

Steps:
1. Load page with active timers
2. Leave running for 30+ minutes
3. Monitor for memory leaks
4. Check timer accuracy

Expected Result:
✓ No crash after 30 minutes
✓ Timers still accurate
✓ No significant memory increase
✓ Page still responsive

Pass Criteria: No memory leaks, stable performance
```

---

## Test Execution Report Template

```markdown
# Test Execution Report
Date: [DATE]
Tested By: [NAME]
Browser: [Chrome/Firefox/Safari] v[VERSION]
Backend: Running ✓/✗
Frontend: Updated ✓/✗

## Summary
Total Tests: [X]
Passed: [X] ✓
Failed: [X] ✗
Blocked: [X] ⚠️

## Failed Tests (if any)
- TC-X.X: [Issue Description]
  - Expected: [Expected Result]
  - Actual: [Actual Result]
  - Screenshot: [Attach]
  - Console Log: [Attach]

## Notes
- [Any observations]
- [Performance notes]
- [Browser-specific issues]

## Sign-Off
- [x] Ready for Production
- [ ] Needs Fixes (see Failed Tests)
```

---

## Quick Test (5 Minutes)

For quick smoke testing:

1. **Tenant Sets Estimation** (2 min)
   - Set 2-minute estimate
   - Verify timer shows: `⏱️ 00:02:00`

2. **Customer Sees Timer** (2 min)
   - Open riwayat-transaksi
   - Verify same timer: `⏱️ 00:02:00`
   - Both should be within 1-2 seconds difference

3. **Wait for Completion** (1 min)
   - Wait for timer to reach `00:00:00`
   - Verify both show: `⏱️ Siap Disajikan!` (green)

**Result**: If all above pass → FEATURE WORKS ✓

---

**Version**: 1.0  
**Date**: 26 April 2026
