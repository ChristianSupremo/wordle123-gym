<?php
// attendance.php - QR / ID check-in and check-out
require_once 'config/db.php';
require_once 'includes/functions.php';
check_login();

$staff_id = $_SESSION['staff_id'] ?? null;
$result = null;
if (empty($_SESSION['attendance_csrf'])) {
    $_SESSION['attendance_csrf'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['attendance_csrf'], $_POST['csrf_token'] ?? '')) {
        http_response_code(403);
        exit('Invalid request token. Please reload the page and try again.');
    }

    // QR payload is "GYM-<MemberID>"; typing a plain numeric ID also works.
    $raw_member_id = trim($_POST['member_id'] ?? '');
    if (preg_match('/^(?:GYM[- ]?)?(\\d+)$/i', $raw_member_id, $matches)) {
        $member_id = (int) $matches[1];
    } else {
        $member_id = 0;
    }

    $stmt = $pdo->prepare("SELECT MemberID, FirstName, LastName FROM member WHERE MemberID = ?");
    $stmt->execute([$member_id]);
    $member = $member_id > 0 ? $stmt->fetch() : false;

    if (!$member) {
        $result = ['type' => 'denied', 'title' => 'Member not found', 'text' => 'Check the ID or scan the code again.'];
    } else {
        $name = $member['FirstName'] . ' ' . $member['LastName'];

        // Resolve the current membership from the member's latest membership start.
        $stmt = $pdo->prepare("SELECT ms.MembershipID, ms.Status, ms.EndDate, p.PlanName,
                                      DATEDIFF(ms.EndDate, CURDATE()) AS days_left
                               FROM membership ms LEFT JOIN plan p ON p.PlanID = ms.PlanID
                               WHERE ms.MemberID = ? ORDER BY ms.StartDate DESC, ms.MembershipID DESC LIMIT 1");
        $stmt->execute([$member_id]);
        $ms = $stmt->fetch();

        // Scanning an open visit checks the member out.
        $stmt = $pdo->prepare("SELECT AttendanceID FROM attendance
                               WHERE MemberID = ? AND CheckOut IS NULL AND DATE(CheckIn) = CURDATE()
                               ORDER BY CheckIn DESC LIMIT 1");
        $stmt->execute([$member_id]);
        $open = $stmt->fetch();

        if ($open) {
            $pdo->prepare("UPDATE attendance SET CheckOut = NOW() WHERE AttendanceID = ?")->execute([$open['AttendanceID']]);
            $result = ['type' => 'ok', 'title' => 'Checked out', 'text' => "See you next time, {$member['FirstName']}!"];
        } elseif (!$ms) {
            $result = ['type' => 'denied', 'title' => 'No membership', 'text' => "$name has no membership on record. Send them to the front desk."];
        } elseif ($ms['Status'] !== 'Active' || !$ms['EndDate'] || $ms['days_left'] < 0) {
            $end_date = $ms['EndDate'] ? date('M j, Y', strtotime($ms['EndDate'])) : 'an unknown date';
            $plan_name = $ms['PlanName'] ?: 'membership';
            $result = ['type' => 'denied', 'title' => 'Membership expired', 'text' => "$name's $plan_name plan ended on $end_date. Renew it to allow entry."];
        } else {
            $pdo->prepare("INSERT INTO attendance (MemberID, MembershipID, StaffID) VALUES (?, ?, ?)")
                ->execute([$member_id, $ms['MembershipID'], $staff_id]);
            $warn = $ms['days_left'] <= 7 ? " Membership ends in {$ms['days_left']} day(s) - offer a renewal." : '';
            $result = ['type' => $warn ? 'warn' : 'ok', 'title' => 'Welcome, ' . $member['FirstName'],
                       'text' => ($ms['PlanName'] ?: 'Membership') . " plan, {$ms['days_left']} days left.$warn"];
        }
    }
}

$checkins_today = (int) $pdo->query("SELECT COUNT(*) FROM attendance WHERE DATE(CheckIn) = CURDATE()")->fetchColumn();
$inside_now = (int) $pdo->query("SELECT COUNT(*) FROM attendance WHERE DATE(CheckIn) = CURDATE() AND CheckOut IS NULL")->fetchColumn();

$today_rows = $pdo->query("SELECT a.CheckIn, a.CheckOut, m.MemberID, m.FirstName, m.LastName
                           FROM attendance a JOIN member m ON m.MemberID = a.MemberID
                           WHERE DATE(a.CheckIn) = CURDATE() ORDER BY a.CheckIn DESC LIMIT 30")->fetchAll();

$colors = ['ok' => '#48bb78', 'warn' => '#f6ad55', 'denied' => '#fc8181'];
?>
<?php include 'includes/header.php'; ?>

<div class="page-header"><h1 class="page-title">Attendance</h1></div>

<div class="stats-grid">
  <div class="stat-card"><div class="stat-label">Check-ins Today</div><div class="stat-value"><?php echo $checkins_today; ?></div></div>
  <div class="stat-card"><div class="stat-label">Inside Now</div><div class="stat-value"><?php echo $inside_now; ?></div></div>
</div>

<div class="content-grid">
  <div class="dashboard-card">
    <div class="card-header-flex"><h2 class="card-title">Check-in</h2></div>

    <?php if ($result): ?>
      <div style="border-left:6px solid <?php echo $colors[$result['type']]; ?>;background:#f7fafc;padding:14px 16px;margin-bottom:16px;border-radius:6px;">
        <strong><?php echo htmlspecialchars($result['title']); ?></strong><br>
        <span style="color:#4a5568;"><?php echo htmlspecialchars($result['text']); ?></span>
      </div>
    <?php endif; ?>

    <form method="post" id="checkinForm" autocomplete="off" style="display:flex;gap:8px;">
      <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['attendance_csrf'], ENT_QUOTES, 'UTF-8'); ?>">
      <input type="text" name="member_id" id="member_id" placeholder="Scan QR or type Member ID" autofocus required
             style="flex:1;padding:10px 12px;border:1px solid #cbd5e0;border-radius:6px;">
      <button type="submit" style="padding:10px 18px;border:0;border-radius:6px;background:#4fd1c5;color:#fff;cursor:pointer;">Check in</button>
    </form>
    <p style="color:#718096;font-size:13px;margin:8px 0 12px;">A USB barcode/QR scanner types the code and presses Enter, so it works with this box. Scanning a member who is already inside checks them out.</p>

    <button type="button" id="camBtn" class="btn-secondary-modern" style="padding:9px 16px;border:1px solid #cbd5e0;background:#fff;border-radius:6px;cursor:pointer;">
      <i class="bi bi-camera-video"></i> Use camera
    </button>
  </div>

  <div class="dashboard-card" style="grid-column: span 2;">
    <div class="card-header-flex"><h2 class="card-title">Today's Visits</h2></div>
    <table class="dashboard-table">
      <thead><tr><th>Member</th><th>In</th><th>Out</th></tr></thead>
      <tbody>
      <?php if ($today_rows): foreach ($today_rows as $r): ?>
        <tr>
          <td><?php echo htmlspecialchars($r['FirstName'] . ' ' . $r['LastName']); ?></td>
          <td><?php echo date('g:i A', strtotime($r['CheckIn'])); ?></td>
          <td><?php echo $r['CheckOut'] ? date('g:i A', strtotime($r['CheckOut'])) : '<span class="plan-badge">Inside</span>'; ?></td>
        </tr>
      <?php endforeach; else: ?>
        <tr><td colspan="3" style="text-align:center;color:#718096;">No check-ins yet today</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<style>
/* Attendance QR scanner modal */
.att-scan-modal{position:fixed;inset:0;z-index:10000;display:flex;align-items:center;justify-content:center;padding:20px;background:rgba(5,12,20,.78);opacity:0;visibility:hidden;transition:opacity .2s ease,visibility .2s ease}
.att-scan-modal.is-open{opacity:1;visibility:visible}
.att-scan-dialog{position:relative;width:min(560px,100%);padding:24px;background:var(--card-bg,#fff);color:var(--text-color,#1f2937);border:1px solid var(--border-color,#e5e7eb);border-radius:16px;box-shadow:0 24px 80px rgba(0,0,0,.35);transform:translateY(12px) scale(.98);transition:transform .2s ease}
.att-scan-modal.is-open .att-scan-dialog{transform:translateY(0) scale(1)}
.att-scan-head{display:flex;align-items:center;justify-content:space-between;gap:16px;margin-bottom:18px}
.att-scan-head h2{font-size:20px;font-weight:700;margin:0}
.att-scan-close{width:38px;height:38px;border:0;border-radius:10px;background:var(--hover-bg,#f1f5f9);color:inherit;font-size:23px;line-height:1;cursor:pointer}
.att-scan-stage{position:relative;width:100%;aspect-ratio:4/3;overflow:hidden;border-radius:12px;background:#07111c}
#reader{position:absolute;inset:0;width:100%;height:100%;border:0!important}
#reader video{width:100%!important;height:100%!important;object-fit:cover!important}
#reader__scan_region{min-height:100%!important;background:transparent!important}
#reader__dashboard,#reader img{display:none!important}
.att-scan-guide{position:absolute;z-index:3;left:50%;top:50%;width:min(66%,260px);aspect-ratio:1;transform:translate(-50%,-50%);pointer-events:none}
.att-scan-guide:before,.att-scan-guide:after{content:"";position:absolute;inset:0;border-radius:0}
.att-scan-guide:before{background:linear-gradient(#fff,#fff) left top/34px 5px no-repeat,linear-gradient(#fff,#fff) left top/5px 34px no-repeat,linear-gradient(#fff,#fff) right top/34px 5px no-repeat,linear-gradient(#fff,#fff) right top/5px 34px no-repeat,linear-gradient(#fff,#fff) left bottom/34px 5px no-repeat,linear-gradient(#fff,#fff) left bottom/5px 34px no-repeat,linear-gradient(#fff,#fff) right bottom/34px 5px no-repeat,linear-gradient(#fff,#fff) right bottom/5px 34px no-repeat;filter:drop-shadow(0 1px 3px rgba(0,0,0,.65))}
.att-scan-shade{position:absolute;z-index:2;inset:0;pointer-events:none;background:radial-gradient(ellipse 35% 47% at center,transparent 98%,rgba(0,0,0,.27) 100%)}
.att-scan-hint{display:flex;justify-content:center;align-items:center;gap:8px;margin:16px 0 4px;font-size:14px;color:var(--muted-text,#64748b);text-align:center}
.att-scan-status{min-height:20px;margin:10px 0 0;text-align:center;font-size:13px;color:var(--muted-text,#64748b)}
.att-scan-status.error{color:#dc2626}
.att-scan-actions{display:flex;justify-content:center;margin-top:18px}
.att-scan-stop{border:0;border-radius:9px;padding:10px 20px;background:#dc4545;color:#fff;font-weight:600;cursor:pointer}
.att-scan-stop:disabled{opacity:.6;cursor:wait}
@media(max-width:520px){.att-scan-dialog{padding:16px}.att-scan-stage{aspect-ratio:3/4}.att-scan-guide{width:min(72%,260px)}}
@media(prefers-reduced-motion:reduce){.att-scan-modal,.att-scan-dialog{transition:none}}
</style>

<div class="att-scan-modal" id="attScanModal" aria-hidden="true">
  <section class="att-scan-dialog" role="dialog" aria-modal="true" aria-labelledby="attScanTitle">
    <div class="att-scan-head">
      <h2 id="attScanTitle">Scan Member QR</h2>
      <button type="button" class="att-scan-close" id="attScanClose" aria-label="Close scanner">&times;</button>
    </div>
    <div class="att-scan-stage">
      <div id="reader"></div>
      <div class="att-scan-shade"></div>
      <div class="att-scan-guide" aria-hidden="true"></div>
    </div>
    <p class="att-scan-hint"><i class="bi bi-qr-code-scan"></i> Align the QR code within the frame</p>
    <p class="att-scan-status" id="attScanStatus" role="status" aria-live="polite">Starting camera…</p>
    <div class="att-scan-actions">
      <button type="button" class="att-scan-stop" id="attScanStop">Stop camera</button>
    </div>
  </section>
</div>

<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
(() => {
    const camBtn = document.getElementById('camBtn');
    const modal = document.getElementById('attScanModal');
    const closeBtn = document.getElementById('attScanClose');
    const stopBtn = document.getElementById('attScanStop');
    const status = document.getElementById('attScanStatus');
    const memberInput = document.getElementById('member_id');
    const checkinForm = document.getElementById('checkinForm');
    let scanner = null;
    let starting = false;
    let closing = false;
    let decoded = false;

    function setStatus(message, isError = false) {
        status.textContent = message;
        status.classList.toggle('error', isError);
    }

    function showModal() {
        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
        closeBtn.focus();
    }

    function hideModal() {
        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
        camBtn.focus();
    }

    async function stopScanner() {
        if (!scanner) return;
        const activeScanner = scanner;
        scanner = null;
        try {
            if (activeScanner.isScanning) await activeScanner.stop();
        } catch (error) {
            console.warn('Unable to stop QR scanner cleanly:', error);
        }
        try { activeScanner.clear(); } catch (error) {}
    }

    async function closeScanner() {
        if (closing) return;
        closing = true;
        stopBtn.disabled = true;
        await stopScanner();
        hideModal();
        camBtn.disabled = false;
        camBtn.innerHTML = '<i class="bi bi-camera-video"></i> Use camera';
        stopBtn.disabled = false;
        closing = false;
    }

    async function startScanner() {
        if (starting || (scanner && scanner.isScanning)) return;
        starting = true;
        decoded = false;
        camBtn.disabled = true;
        camBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Starting camera…';
        setStatus('Requesting camera access…');
        showModal();

        if (!window.isSecureContext && location.hostname !== 'localhost') {
            setStatus('Camera access requires HTTPS or localhost.', true);
            starting = false;
            camBtn.disabled = false;
            camBtn.innerHTML = '<i class="bi bi-camera-video"></i> Use camera';
            return;
        }
        if (!window.Html5Qrcode) {
            setStatus('The QR scanner library could not be loaded. Refresh and try again.', true);
            starting = false;
            camBtn.disabled = false;
            return;
        }

        scanner = new Html5Qrcode('reader', { verbose: false });
        const config = {
            fps: 12,
            qrbox: (viewfinderWidth, viewfinderHeight) => {
                const edge = Math.floor(Math.min(viewfinderWidth, viewfinderHeight, 260) * 0.82);
                return { width: edge, height: edge };
            },
            aspectRatio: 1,
            experimentalFeatures: { useBarCodeDetectorIfSupported: true }
        };

        try {
            await scanner.start(
                { facingMode: 'environment' },
                config,
                async (decodedText) => {
                    if (decoded || closing) return;
                    decoded = true;
                    const value = decodedText.trim();
                    await closeScanner();
                    memberInput.value = value;
                    checkinForm.submit();
                },
                () => {}
            );
            setStatus('Camera active. Hold the QR code steady inside the corners.');
        } catch (error) {
            console.error('Camera error:', error);
            await stopScanner();
            const message = error && error.name === 'NotAllowedError'
                ? 'Camera permission was denied. Allow camera access in your browser settings.'
                : error && error.name === 'NotFoundError'
                    ? 'No camera was found on this device.'
                    : 'Unable to start the camera. Check browser permissions and try again.';
            setStatus(message, true);
            camBtn.disabled = false;
            camBtn.innerHTML = '<i class="bi bi-camera-video"></i> Use camera';
        } finally {
            starting = false;
        }
    }

    camBtn.addEventListener('click', startScanner);
    closeBtn.addEventListener('click', closeScanner);
    stopBtn.addEventListener('click', closeScanner);
    modal.addEventListener('click', (event) => {
        if (event.target === modal) closeScanner();
    });
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && modal.classList.contains('is-open')) closeScanner();
    });
    window.addEventListener('pagehide', () => {
        if (scanner && scanner.isScanning) scanner.stop().catch(() => {});
    });
})();
</script>

<?php include 'includes/footer.php'; ?>
