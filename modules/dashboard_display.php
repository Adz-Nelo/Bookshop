<?php
$books = $_SESSION['books'];
$sales = $_SESSION['sales'];
$totalStock = array_sum(array_column($books, 'stock'));
$now = new DateTimeImmutable('today');
$weekly = [];
$monthly = [];
$yearly = [];
$weekStart = $now->modify('monday this week');
for ($day = 0; $day < 7; $day++) {
    $date = $weekStart->modify('+' . $day . ' days');
    $weekly[$date->format('Y-m-d')] = ['label' => $date->format('D'), 'value' => 0.0];
}
$monthStart = $now->modify('first day of this month')->modify('-11 months');
for ($month = 0; $month < 12; $month++) {
    $date = $monthStart->modify('+' . $month . ' months');
    $monthly[$date->format('Y-m')] = ['label' => $date->format('M'), 'value' => 0.0];
}
$yearStart = $now->modify('first day of January')->modify('-4 years');
for ($year = 0; $year < 5; $year++) {
    $date = $yearStart->modify('+' . $year . ' years');
    $yearly[$date->format('Y')] = ['label' => $date->format('Y'), 'value' => 0.0];
}
foreach ($sales as $sale) {
    $timestamp = strtotime($sale['created_at'] ?? '');
    if ($timestamp === false) continue;
    $date = (new DateTimeImmutable('@' . $timestamp))->setTimezone(new DateTimeZone(date_default_timezone_get()));
    $amount = (float)$sale['total'];
    $day = $date->format('Y-m-d');
    $month = $date->format('Y-m');
    $year = $date->format('Y');
    if (isset($weekly[$day])) $weekly[$day]['value'] += $amount;
    if (isset($monthly[$month])) $monthly[$month]['value'] += $amount;
    if (isset($yearly[$year])) $yearly[$year]['value'] += $amount;
}
$groups = [
    ['title' => 'This week', 'caption' => 'Daily income · Monday to Sunday', 'data' => array_values($weekly)],
    ['title' => 'Monthly income', 'caption' => 'Last 12 months', 'data' => array_values($monthly)],
    ['title' => 'Yearly income', 'caption' => 'Last 5 years', 'data' => array_values($yearly)],
];
$periods = [
    'week' => array_sum(array_column($weekly, 'value')),
    'month' => $monthly[$now->format('Y-m')]['value'],
    'year' => $yearly[$now->format('Y')]['value'],
];
?>
<section class="dashboard-page"><div class="admin-page-heading"><div><span class="eyebrow">OVERVIEW</span><h1>Dashboard</h1><p>Here’s what’s happening at your bookshop.</p></div><a class="export-button" href="index.php?export=sales">Export sales to Excel</a></div>
<div class="stats-grid dashboard-stats">
<div class="stat-card"><span>▤</span><h3>Books in catalog</h3><strong><?= count($books) ?></strong></div><div class="stat-card"><span>▥</span><h3>Available stock</h3><strong><?= number_format($totalStock) ?></strong></div><div class="stat-card"><span>♙</span><h3>Registered readers</h3><strong><?= number_format(count($customerRepo->getClientAccounts())) ?></strong></div><div class="stat-card"><span>↗</span><h3>Recorded sales</h3><strong><?= number_format(count($sales)) ?></strong></div><div class="stat-card"><span>₱</span><h3>This week</h3><strong>₱<?= number_format($periods['week'],2) ?></strong></div><div class="stat-card"><span>₱</span><h3>This month</h3><strong>₱<?= number_format($periods['month'],2) ?></strong></div><div class="stat-card"><span>₱</span><h3>This year</h3><strong>₱<?= number_format($periods['year'],2) ?></strong></div></div>
<div class="income-charts"><?php foreach($groups as $group): $maximum=max(array_column($group['data'],'value')); ?>
<section class="content-panel chart-card" aria-label="<?= htmlspecialchars($group['title']) ?> graph"><div class="panel-heading"><div><h2><?= htmlspecialchars($group['title']) ?></h2><p><?= htmlspecialchars($group['caption']) ?></p></div></div><div class="chart-bars">
<?php foreach($group['data'] as $point): $height=$maximum>0?max(3,($point['value']/$maximum)*100):0; ?>
<div class="chart-column" title="<?= htmlspecialchars($point['label']) ?>: ₱<?= number_format($point['value'],2) ?>"><span class="chart-value"><?= $point['value']>0?number_format($point['value'],0):'–' ?></span><div class="chart-track"><span class="chart-bar" style="height: <?= $height ?>%"></span></div><span class="chart-label"><?= htmlspecialchars($point['label']) ?></span></div>
<?php endforeach; ?></div></section><?php endforeach; ?></div>
<div class="dashboard-lower"><section class="content-panel"><div class="panel-heading"><div><h2>Recently added books</h2><p>Keep an eye on your collection.</p></div><a class="text-link" href="index.php?page=books">Manage books <span aria-hidden="true">→</span></a></div>
<?php if(empty($books)): ?><p>No books have been added yet.</p><?php else: ?><div class="featured-list"><?php foreach(array_slice($books,0,4) as $book): ?><div class="featured-book"><span aria-hidden="true">▤</span><div><strong><?= htmlspecialchars($book['title']) ?></strong><small><?= htmlspecialchars($book['author']) ?></small></div><b>₱<?= number_format((float)$book['price'],2) ?></b></div><?php endforeach; ?></div><?php endif; ?></section>
<section class="content-panel dashboard-shortcuts"><h2>Quick actions</h2><a href="index.php?page=sales">Review customer orders <span aria-hidden="true">→</span></a><a href="index.php?page=customers">Manage reader accounts <span aria-hidden="true">→</span></a><a href="index.php?page=reports">View reports & exports <span aria-hidden="true">→</span></a></section></div></section>
