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

    <button type="button" id="camBtn" style="padding:8px 14px;border:1px solid #cbd5e0;background:#fff;border-radius:6px;cursor:pointer;">Use camera</button>
    <div id="reader" style="width:100%;max-width:320px;margin-top:12px;"></div>
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

<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
let html5QrCode = null;

document.getElementById('camBtn').addEventListener('click', async function () {
    const button = this;
    
    if (html5QrCode && html5QrCode.isScanning) {
        await html5QrCode.stop();
        button.textContent = 'Use camera';
        return;
    }

    button.disabled = true;
    button.textContent = 'Starting camera...';

    html5QrCode = new Html5Qrcode("reader");

    const config = {
        fps: 15,
        // Using experimental features allows reading inverted QR codes & improves focus
        experimentalFeatures: {
            useBarCodeDetectorIfSupported: true
        },
        // Increase resolution request to help webcams read smaller phone screens
        videoConstraints: {
            width: { min: 640, ideal: 1280 },
            height: { min: 480, ideal: 720 },
            facingMode: "user"
        }
    };

    try {
        await html5QrCode.start(
            { facingMode: "user" },
            config,
            async (decodedText) => {
                await html5QrCode.stop();
                document.getElementById('member_id').value = decodedText.trim();
                document.getElementById('checkinForm').submit();
            },
            () => {}
        );
        button.disabled = false;
        button.textContent = 'Stop camera';
    } catch (err) {
        console.error('Camera error:', err);
        alert('Unable to start camera: ' + err);
        button.disabled = false;
        button.textContent = 'Use camera';
    }
});
</script>

<?php include 'includes/footer.php'; ?>
