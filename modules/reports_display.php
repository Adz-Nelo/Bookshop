<?php
$books = $_SESSION['books'];
$sales = $_SESSION['sales'];
$totalStock = array_sum(array_column($books, 'stock'));
$totalRevenue = array_sum(array_column($sales, 'total'));
$clientAccounts = $customerRepo->getClientAccounts();
?>

<section class="module-page">
    <div class="admin-page-heading">
        <div>
            <span class="eyebrow">BOOKSHOP ANALYTICS</span>
            <h1>Reports & exports</h1>
            <p>Review inventory and download reports for Microsoft Excel.</p>
        </div>
    </div>

    <div class="stats-grid">
        <div class="stat-card"><span>▤</span><h3>Books in catalog</h3><strong><?= number_format(count($books)) ?></strong></div>
        <div class="stat-card"><span>▥</span><h3>Total stock</h3><strong><?= number_format($totalStock) ?></strong></div>
        <div class="stat-card"><span>♙</span><h3>Reader accounts</h3><strong><?= number_format(count($clientAccounts)) ?></strong></div>
        <div class="stat-card"><span>₱</span><h3>Recorded sales</h3><strong>₱<?= number_format($totalRevenue, 2) ?></strong></div>
    </div>

    <div class="content-panel">
        <div class="panel-heading">
            <div><h2>Excel exports</h2><p>Download UTF-8 CSV files that open directly in Excel.</p></div>
        </div>
        <div class="export-grid">
            <a class="export-card" href="index.php?export=sales"><span aria-hidden="true">↗</span><strong>Sales report</strong><small>Recorded sales and income</small><b>Download CSV →</b></a>
            <a class="export-card" href="index.php?export=orders"><span aria-hidden="true">▤</span><strong>Order history</strong><small>Customer and order details</small><b>Download CSV →</b></a>
            <a class="export-card" href="index.php?export=users"><span aria-hidden="true">♙</span><strong>User report</strong><small>Accounts and purchase totals</small><b>Download CSV →</b></a>
            <a class="export-card" href="index.php?export=inventory"><span aria-hidden="true">▥</span><strong>Inventory report</strong><small>Books, prices, and stock</small><b>Download CSV →</b></a>
        </div>
    </div>

    <div class="content-panel">
        <div class="panel-heading">
            <div><h2>Inventory report</h2><p>Current availability across the book catalog.</p></div>
            <a class="text-link" href="index.php?export=inventory">Export inventory <span aria-hidden="true">↓</span></a>
        </div>
        <div class="table-scroll">
            <table>
                <thead><tr><th>Book</th><th>Author</th><th>Price</th><th>Available stock</th><th>Status</th></tr></thead>
                <tbody>
                    <?php foreach ($books as $book): ?>
                        <tr>
                            <td><?= htmlspecialchars($book['title']) ?></td>
                            <td><?= htmlspecialchars($book['author']) ?></td>
                            <td>₱<?= number_format((float) $book['price'], 2) ?></td>
                            <td><?= number_format((int) $book['stock']) ?></td>
                            <td><span class="stock-status <?= (int) $book['stock'] <= 5 ? 'low-stock' : '' ?>"><?= (int) $book['stock'] <= 5 ? 'Low stock' : 'Available' ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>
