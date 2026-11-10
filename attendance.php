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

// Show the result of the previous scan once (post/redirect/get avoids duplicate check-ins on refresh).
if (!empty($_SESSION['attendance_flash'])) {
    $result = $_SESSION['attendance_flash'];
    unset($_SESSION['attendance_flash']);
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

    $_SESSION['attendance_flash'] = $result;
    header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?'));
    exit;
}

$checkins_today = (int) $pdo->query("SELECT COUNT(*) FROM attendance WHERE DATE(CheckIn) = CURDATE()")->fetchColumn();
$inside_now = (int) $pdo->query("SELECT COUNT(*) FROM attendance WHERE DATE(CheckIn) = CURDATE() AND CheckOut IS NULL")->fetchColumn();
$left_today = max(0, $checkins_today - $inside_now);

$today_rows = $pdo->query("SELECT a.CheckIn, a.CheckOut, m.MemberID, m.FirstName, m.LastName
                           FROM attendance a JOIN member m ON m.MemberID = a.MemberID
                           WHERE DATE(a.CheckIn) = CURDATE() ORDER BY a.CheckIn DESC LIMIT 30")->fetchAll();

$icons = ['ok' => 'bi-check-circle-fill', 'warn' => 'bi-exclamation-triangle-fill', 'denied' => 'bi-x-circle-fill'];

function att_duration($in, $out)
{
    $end = $out ? strtotime($out) : time();
    $mins = max(0, (int) floor(($end - strtotime($in)) / 60));
    if ($mins < 60) return $mins . 'm';
    return intdiv($mins, 60) . 'h ' . str_pad((string) ($mins % 60), 2, '0', STR_PAD_LEFT) . 'm';
}
?>
<?php include 'includes/header.php'; ?>

<div class="page-header att-head">
  <div>
    <h1 class="page-title">Attendance</h1>
    <p class="att-subtitle">Scan a member's QR code or enter their ID to check them in or out.</p>
  </div>
  <div class="att-clock">
    <i class="bi bi-clock"></i>
    <div><strong id="attClock">--:--</strong><span><?php echo date('D, M j, Y'); ?></span></div>
  </div>
</div>

<div class="stats-grid">
  <div class="stat-card att-stat">
    <div class="att-stat-icon"><i class="bi bi-box-arrow-in-right"></i></div>
    <div><div class="stat-label">Check-ins Today</div><div class="stat-value"><?php echo $checkins_today; ?></div></div>
  </div>
  <div class="stat-card att-stat">
    <div class="att-stat-icon inside"><i class="bi bi-people-fill"></i></div>
    <div><div class="stat-label">Inside Now</div><div class="stat-value"><?php echo $inside_now; ?></div></div>
  </div>
  <div class="stat-card att-stat">
    <div class="att-stat-icon out"><i class="bi bi-box-arrow-right"></i></div>
    <div><div class="stat-label">Checked Out</div><div class="stat-value"><?php echo $left_today; ?></div></div>
  </div>
</div>

<div class="att-layout">
  <!-- Check-in -->
  <div class="dashboard-card">
    <div class="card-header-flex"><h2 class="card-title">Check-in / Check-out</h2></div>

    <?php if ($result): ?>
      <div class="att-result <?php echo htmlspecialchars($result['type']); ?>" id="attResult" role="status">
        <i class="bi <?php echo $icons[$result['type']] ?? 'bi-info-circle-fill'; ?>"></i>
        <div>
          <strong><?php echo htmlspecialchars($result['title']); ?></strong>
          <span><?php echo htmlspecialchars($result['text']); ?></span>
        </div>
      </div>
    <?php endif; ?>

    <form method="post" id="checkinForm" autocomplete="off">
      <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['attendance_csrf'], ENT_QUOTES, 'UTF-8'); ?>">
      <label class="att-label" for="member_id">Member ID or QR code</label>
      <div class="att-scan-row">
        <div class="att-scan-input">
          <i class="bi bi-upc-scan"></i>
          <input type="text" class="form-control-modern" name="member_id" id="member_id" placeholder="Scan QR or type ID" autofocus required>
        </div>
        <button type="submit" class="btn-primary-modern"><i class="bi bi-check2-circle"></i> Check in</button>
      </div>
    </form>
    <p class="att-help">A USB barcode/QR scanner types the code and presses Enter, so it works with this box. Scanning a member who is already inside checks them out.</p>

    <div class="att-divider">or</div>

    <button type="button" id="camBtn" class="btn-secondary-modern att-cam-btn"><i class="bi bi-camera-video"></i> Use camera</button>
  </div>

  <!-- Today's visits -->
  <div class="dashboard-card">
    <div class="card-header-flex">
      <h2 class="card-title">Today's Visits <span class="att-count"><?php echo count($today_rows); ?></span></h2>
      <div class="att-card-tools">
        <div class="search-box att-search">
          <i class="bi bi-search"></i>
          <input type="text" id="visitSearch" placeholder="Search member..." aria-label="Search today's visits">
        </div>
      </div>
    </div>
    <div class="att-table-wrap">
      <table class="dashboard-table" id="visitTable">
        <thead><tr><th>Member</th><th>In</th><th>Out</th><th>Duration</th><th>Status</th></tr></thead>
        <tbody>
        <?php if ($today_rows): foreach ($today_rows as $r):
            $initials = strtoupper(mb_substr($r['FirstName'], 0, 1) . mb_substr($r['LastName'], 0, 1)); ?>
          <tr>
            <td>
              <div class="att-member">
                <div class="member-avatar member-avatar-initials"><?php echo htmlspecialchars($initials); ?></div>
                <div class="member-info">
                  <div class="member-name"><?php echo htmlspecialchars($r['FirstName'] . ' ' . $r['LastName']); ?></div>
                  <div class="member-plan">ID <?php echo (int) $r['MemberID']; ?></div>
                </div>
              </div>
            </td>
            <td><?php echo date('g:i A', strtotime($r['CheckIn'])); ?></td>
            <td><?php echo $r['CheckOut'] ? date('g:i A', strtotime($r['CheckOut'])) : '&mdash;'; ?></td>
            <td><?php echo att_duration($r['CheckIn'], $r['CheckOut']); ?></td>
            <td><?php echo $r['CheckOut'] ? '<span class="status-badge expired">Left</span>' : '<span class="status-badge active att-live">Inside</span>'; ?></td>
          </tr>
        <?php endforeach; else: ?>
          <tr><td colspan="5" class="att-empty"><i class="bi bi-calendar2-check"></i><br>No check-ins yet today</td></tr>
        <?php endif; ?>
        </tbody>
      </table>
      <div class="att-empty att-no-match" id="visitNoMatch"><i class="bi bi-search"></i><br>No visits match your search</div>
    </div>
  </div>
</div>

<?php include 'includes/footer.php'; ?>

<!-- Camera scanner (placed after the footer like the other modals, so it isn't blurred by body.modal-open) -->
<div class="modal-overlay" id="attScanModal" aria-hidden="true">
  <div class="modal-dialog-modern att-scan-dialog" role="dialog" aria-modal="true" aria-labelledby="attScanTitle">
    <div class="modal-content-modern">
      <div class="modal-header-modern">
        <h5 class="modal-title-modern" id="attScanTitle"><i class="bi bi-qr-code-scan"></i> Scan Member QR</h5>
        <button type="button" class="modal-close-modern" id="attScanClose" aria-label="Close scanner"><i class="bi bi-x"></i></button>
      </div>

      <div class="modal-body-modern">
        <p class="att-scan-lead">Hold the member's QR code inside the frame</p>
        <div class="att-scan-stage" id="attStage">
          <div id="reader"></div>
          <div class="att-scan-shade"></div>
          <div class="att-scan-guide" aria-hidden="true">
            <i class="tl"></i><i class="tr"></i><i class="bl"></i><i class="br"></i>
            <span class="att-scan-line"></span>
          </div>
          <div class="att-scan-success" aria-hidden="true"><i class="bi bi-check-lg"></i></div>
          <div class="att-scan-loading" id="attLoading"><div class="spinner-modern"></div><span>Starting camera…</span></div>
        </div>
        <p class="att-scan-status" id="attScanStatus" role="status" aria-live="polite">Requesting camera access…</p>
      </div>

      <div class="modal-footer-modern">
        <button type="button" class="btn-secondary-modern" id="attScanSwitch" hidden><i class="bi bi-arrow-repeat"></i> Switch camera</button>
        <button type="button" class="att-btn-stop" id="attScanStop"><i class="bi bi-stop-circle"></i> Stop camera</button>
      </div>
    </div>
  </div>
</div>

<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
(() => {
    // ---- Live clock ----
    const clock = document.getElementById('attClock');
    const tick = () => { clock.textContent = new Date().toLocaleTimeString([], {hour: 'numeric', minute: '2-digit', second: '2-digit'}); };
    tick(); setInterval(tick, 1000);

    // ---- Result banner: dims after a few seconds; input stays ready for the next scan ----
    const memberInput = document.getElementById('member_id');
    const resultBox = document.getElementById('attResult');
    if (resultBox) setTimeout(() => resultBox.classList.add('fade'), 7000);
    memberInput.focus();

    // ---- Visit search ----
    const search = document.getElementById('visitSearch');
    const rows = document.querySelectorAll('#visitTable tbody tr:not(:has(.att-empty))');
    const noMatch = document.getElementById('visitNoMatch');
    search.addEventListener('input', () => {
        const q = search.value.trim().toLowerCase();
        let visible = 0;
        rows.forEach(row => {
            const show = !q || row.textContent.toLowerCase().includes(q);
            row.style.display = show ? '' : 'none';
            if (show) visible++;
        });
        noMatch.classList.toggle('show', rows.length > 0 && visible === 0);
    });

    // ---- Camera scanner ----
    const camBtn = document.getElementById('camBtn');
    const modal = document.getElementById('attScanModal');
    const stage = document.getElementById('attStage');
    const loading = document.getElementById('attLoading');
    const closeBtn = document.getElementById('attScanClose');
    const stopBtn = document.getElementById('attScanStop');
    const switchBtn = document.getElementById('attScanSwitch');
    const status = document.getElementById('attScanStatus');
    const checkinForm = document.getElementById('checkinForm');
    const camLabel = '<i class="bi bi-camera-video"></i> Use camera';
    let scanner = null;
    let cameras = [];
    let camIndex = -1;
    let starting = false;
    let closing = false;
    let decoded = false;

    function setStatus(message, kind = '') {
        status.textContent = message;
        status.className = 'att-scan-status' + (kind ? ' ' + kind : '');
    }

    function resetCamBtn() {
        camBtn.disabled = false;
        camBtn.innerHTML = camLabel;
    }

    function showModal() {
        modal.classList.add('show');
        modal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('modal-open');
        closeBtn.focus();
    }

    function hideModal() {
        modal.classList.remove('show');
        modal.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('modal-open');
        camBtn.focus();
    }

    async function stopScanner() {
        if (!scanner) return;
        const active = scanner;
        scanner = null;
        try { if (active.isScanning) await active.stop(); }
        catch (error) { console.warn('Unable to stop QR scanner cleanly:', error); }
        try { active.clear(); } catch (error) {}
    }

    async function closeScanner() {
        if (closing) return;
        closing = true;
        stopBtn.disabled = true;
        switchBtn.disabled = true;
        await stopScanner();
        hideModal();
        resetCamBtn();
        stage.classList.remove('is-success');
        stopBtn.disabled = false;
        switchBtn.disabled = false;
        closing = false;
    }

    function onDecoded(decodedText) {
        if (decoded || closing) return;
        decoded = true;
        if (navigator.vibrate) navigator.vibrate(80);
        stage.classList.add('is-success');
        setStatus('Code detected – checking member…', 'success');
        memberInput.value = decodedText.trim();
        // Brief confirmation flash, then release the camera and submit.
        setTimeout(async () => {
            await stopScanner();
            checkinForm.submit();
        }, 450);
    }

    async function launch(cameraConfig) {
        scanner = new Html5Qrcode('reader', { verbose: false });
        const config = {
            fps: 12,
            qrbox: (w, h) => {
                const edge = Math.floor(Math.min(w, h) * 0.66);
                return { width: edge, height: edge };
            },
            experimentalFeatures: { useBarCodeDetectorIfSupported: true }
        };
        await scanner.start(cameraConfig, config, onDecoded, () => {});
        loading.classList.add('hide');
        setStatus('Camera active. Hold the QR code steady inside the corners.');
    }

    async function startScanner(cameraConfig = { facingMode: 'environment' }) {
        if (starting) return;
        starting = true;
        decoded = false;
        stage.classList.remove('is-success');
        loading.classList.remove('hide');
        camBtn.disabled = true;
        camBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Starting camera…';
        setStatus('Requesting camera access…');
        showModal();

        if (!window.isSecureContext && location.hostname !== 'localhost') {
            loading.classList.add('hide');
            setStatus('Camera access requires HTTPS or localhost.', 'error');
            starting = false;
            resetCamBtn();
            return;
        }
        if (!window.Html5Qrcode) {
            loading.classList.add('hide');
            setStatus('The QR scanner library could not be loaded. Refresh and try again.', 'error');
            starting = false;
            resetCamBtn();
            return;
        }

        try {
            await launch(cameraConfig);
            // Offer "Switch camera" once permission is granted and labels are available.
            try {
                cameras = await Html5Qrcode.getCameras();
                switchBtn.hidden = cameras.length < 2;
            } catch (e) { switchBtn.hidden = true; }
        } catch (error) {
            console.error('Camera error:', error);
            await stopScanner();
            loading.classList.add('hide');
            const message = error && error.name === 'NotAllowedError'
                ? 'Camera permission was denied. Allow camera access in your browser settings.'
                : error && error.name === 'NotFoundError'
                    ? 'No camera was found on this device.'
                    : 'Unable to start the camera. Check browser permissions and try again.';
            setStatus(message, 'error');
            resetCamBtn();
        } finally {
            starting = false;
        }
    }

    async function switchCamera() {
        if (starting || closing || cameras.length < 2) return;
        camIndex = (camIndex + 1) % cameras.length;
        switchBtn.disabled = true;
        starting = true;
        await stopScanner();
        loading.classList.remove('hide');
        setStatus('Switching camera…');
        try {
            await launch({ deviceId: { exact: cameras[camIndex].id } });
        } catch (error) {
            console.error('Switch camera error:', error);
            await stopScanner();
            loading.classList.add('hide');
            setStatus('Unable to switch camera. Close and reopen the scanner.', 'error');
        } finally {
            starting = false;
            switchBtn.disabled = false;
        }
    }

    camBtn.addEventListener('click', () => startScanner());
    closeBtn.addEventListener('click', closeScanner);
    stopBtn.addEventListener('click', closeScanner);
    switchBtn.addEventListener('click', switchCamera);
    modal.addEventListener('click', (event) => { if (event.target === modal) closeScanner(); });
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && modal.classList.contains('show')) closeScanner();
    });
    window.addEventListener('pagehide', () => {
        if (scanner && scanner.isScanning) scanner.stop().catch(() => {});
    });
})();
</script>