<?php
// insights.php - attendance analytics + at-risk members
require_once 'config/db.php';
require_once 'includes/functions.php';
check_login();

// ---- At-risk members: active membership + warning signs from attendance ----
$rows = $pdo->query("
  SELECT m.MemberID, m.FirstName, m.LastName, m.PhoneNo, p.PlanName, ms.EndDate,
         DATEDIFF(ms.EndDate, CURDATE()) AS days_left,
         (SELECT MAX(a.CheckIn) FROM attendance a WHERE a.MemberID = m.MemberID) AS last_visit,
         (SELECT COUNT(*) FROM attendance a WHERE a.MemberID = m.MemberID
            AND a.CheckIn >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)) AS v30,
         (SELECT COUNT(*) FROM attendance a WHERE a.MemberID = m.MemberID
            AND a.CheckIn >= DATE_SUB(CURDATE(), INTERVAL 60 DAY)
            AND a.CheckIn <  DATE_SUB(CURDATE(), INTERVAL 30 DAY)) AS vprev
  FROM member m
  JOIN membership ms ON ms.MemberID = m.MemberID
  LEFT JOIN plan p ON p.PlanID = ms.PlanID
  WHERE ms.MembershipID = (SELECT MembershipID FROM membership WHERE MemberID = m.MemberID ORDER BY EndDate DESC LIMIT 1)
    AND ms.Status = 'Active' AND ms.EndDate >= CURDATE()
")->fetchAll();

$at_risk = [];
foreach ($rows as $r) {
    $score = 0; $reasons = [];
    $idle = $r['last_visit'] ? (int) floor((time() - strtotime($r['last_visit'])) / 86400) : null;

    if ($idle === null)      { $score += 2; $reasons[] = 'Never checked in'; }
    elseif ($idle >= 14)     { $score += 2; $reasons[] = "No visit in $idle days"; }
    if ($r['v30'] < 4)       { $score += 1; $reasons[] = "Only {$r['v30']} visit(s) in 30 days"; }
    if ($r['vprev'] >= 4 && $r['v30'] <= $r['vprev'] / 2) {
        $score += 1; $reasons[] = "Visits dropped from {$r['vprev']} to {$r['v30']}";
    }
    if ($r['days_left'] <= 14) { $score += 1; $reasons[] = "Ends in {$r['days_left']} day(s)"; }

    if ($score >= 3) {
        $r['score'] = $score; $r['level'] = $score >= 4 ? 'High' : 'Medium';
        $r['reasons'] = implode('; ', $reasons);
        $at_risk[] = $r;
    }
}
usort($at_risk, fn($a, $b) => $b['score'] <=> $a['score'] ?: $a['days_left'] <=> $b['days_left']);

// ---- Peak hours (last 30 days) ----
$hours = array_fill(5, 18, 0); // 5 AM .. 10 PM
foreach ($pdo->query("SELECT HOUR(CheckIn) h, COUNT(*) c FROM attendance
                      WHERE CheckIn >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) GROUP BY h") as $r) {
    if (isset($hours[(int) $r['h']])) $hours[(int) $r['h']] = (int) $r['c'];
}
$hour_labels = array_map(fn($h) => date('ga', strtotime("$h:00")), array_keys($hours));

// ---- Visits per day (last 14 days) ----
$days = [];
for ($i = 13; $i >= 0; $i--) $days[date('Y-m-d', strtotime("-$i day"))] = 0;
foreach ($pdo->query("SELECT DATE(CheckIn) d, COUNT(*) c FROM attendance
                      WHERE CheckIn >= DATE_SUB(CURDATE(), INTERVAL 13 DAY) GROUP BY d") as $r) {
    $days[$r['d']] = (int) $r['c'];
}
$day_labels = array_map(fn($d) => date('M j', strtotime($d)), array_keys($days));

$busiest = array_search(max($hours), $hours);
$avg_day = round(array_sum($days) / 14, 1);
?>
<?php include 'includes/header.php'; ?>

<div class="page-header"><h1 class="page-title">Member Insights</h1></div>

<div class="stats-grid">
  <div class="stat-card"><div class="stat-label">At-Risk Members</div><div class="stat-value"><?php echo count($at_risk); ?></div></div>
  <div class="stat-card"><div class="stat-label">Avg. Visits / Day (14d)</div><div class="stat-value"><?php echo $avg_day; ?></div></div>
  <div class="stat-card"><div class="stat-label">Busiest Hour (30d)</div>
    <div class="stat-value"><?php echo max($hours) > 0 ? date('g A', strtotime("$busiest:00")) : '-'; ?></div></div>
</div>

<div class="content-grid">
  <div class="dashboard-card">
    <div class="card-header-flex"><h2 class="card-title">Peak Hours (last 30 days)</h2></div>
    <canvas id="hoursChart"></canvas>
  </div>
  <div class="dashboard-card">
    <div class="card-header-flex"><h2 class="card-title">Daily Visits (last 14 days)</h2></div>
    <canvas id="daysChart"></canvas>
  </div>
</div>

<div class="dashboard-card" style="margin-top:20px;">
  <div class="card-header-flex"><h2 class="card-title">Members to Follow Up</h2></div>
  <p style="color:#718096;font-size:13px;margin:0 0 10px;">Active members with several warning signs: long gaps between visits, low or falling attendance, or a plan ending soon.</p>
  <table class="dashboard-table">
    <thead><tr><th>Member</th><th>Plan</th><th>Risk</th><th>Why</th><th>Last visit</th><th>Contact</th></tr></thead>
    <tbody>
    <?php if ($at_risk): foreach ($at_risk as $r): ?>
      <tr>
        <td><?php echo htmlspecialchars($r['FirstName'] . ' ' . $r['LastName']); ?></td>
        <td><span class="plan-badge"><?php echo htmlspecialchars($r['PlanName'] ?? '-'); ?></span></td>
        <td><span class="days-badge" style="background:<?php echo $r['level'] === 'High' ? '#fc8181' : '#f6ad55'; ?>;color:#fff;"><?php echo $r['level']; ?></span></td>
        <td><?php echo htmlspecialchars($r['reasons']); ?></td>
        <td><?php echo $r['last_visit'] ? date('M j', strtotime($r['last_visit'])) : 'Never'; ?></td>
        <td><?php echo htmlspecialchars($r['PhoneNo'] ?? '-'); ?></td>
      </tr>
    <?php endforeach; else: ?>
      <tr><td colspan="6" style="text-align:center;color:#718096;">No at-risk members right now. Check-ins need to be recorded first.</td></tr>
    <?php endif; ?>
    </tbody>
  </table>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
new Chart(document.getElementById('hoursChart'), {
  type: 'bar',
  data: { labels: <?php echo json_encode($hour_labels); ?>,
          datasets: [{ label: 'Check-ins', data: <?php echo json_encode(array_values($hours)); ?>, backgroundColor: '#4fd1c5' }] },
  options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
});
new Chart(document.getElementById('daysChart'), {
  type: 'line',
  data: { labels: <?php echo json_encode($day_labels); ?>,
          datasets: [{ label: 'Visits', data: <?php echo json_encode(array_values($days)); ?>, borderColor: '#63b3ed', backgroundColor: 'rgba(99,179,237,.15)', fill: true, tension: .3 }] },
  options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
});
</script>

<?php include 'includes/footer.php'; ?>
