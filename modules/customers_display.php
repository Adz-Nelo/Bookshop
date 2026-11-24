<?php
$clientAccounts = $customerRepo->getClientAccounts();
$clientOrders = $customerRepo->getClientOrders();
$ordersByUser = [];

foreach ($clientOrders as $order) {
    $ordersByUser[(int)$order['user_id']][] = $order;
}
?>

<section class="module-page">
<div class="admin-page-heading">
<div>
<span class="eyebrow">CUSTOMER ACCOUNTS</span>
<h1>User management</h1>
<p>Reader accounts, purchase totals, and order history.</p>
</div>
<a class="export-button" href="index.php?export=users">Export users to Excel</a>
</div>

<div class="stats-grid user-stats">
<div class="stat-card"><span>♙</span><h3>Reader accounts</h3><strong><?= number_format(count($clientAccounts)) ?></strong></div>
<div class="stat-card"><span>▤</span><h3>Books purchased</h3><strong><?= number_format(array_sum(array_column($clientAccounts,'books_purchased'))) ?></strong></div>
<div class="stat-card"><span>↗</span><h3>Orders placed</h3><strong><?= number_format(array_sum(array_column($clientAccounts,'order_count'))) ?></strong></div></div>
<div class="content-panel"><div class="panel-heading"><div><h2>Reader accounts</h2><p>Purchase totals include approved orders only.</p></div><a class="text-link" href="index.php?export=orders">Export order history <span aria-hidden="true">↓</span></a></div>
<?php if(empty($clientAccounts)): ?><p>No reader accounts have registered yet.</p>
<?php else: ?><div class="table-scroll"><table class="user-table"><thead><tr><th>Reader</th><th>Orders</th><th>Books purchased</th><th>Total spent</th><th>Order history</th></tr></thead><tbody>
<?php foreach($clientAccounts as $client): $accountOrders=$ordersByUser[(int)$client['id']]??[]; ?>
<tr><td><strong><?= htmlspecialchars($client['name']) ?></strong><small class="table-subtext"><?= htmlspecialchars($client['email']) ?> · @<?= htmlspecialchars($client['username']) ?></small></td><td><?= number_format((int)$client['order_count']) ?></td><td><?= number_format((int)$client['books_purchased']) ?></td><td>₱<?= number_format((float)$client['total_spent'],2) ?></td>
<td><?php if(empty($accountOrders)): ?><span class="muted-text">No orders</span><?php else: ?><details class="order-details"><summary>View <?= count($accountOrders) ?> order<?= count($accountOrders)===1?'':'s' ?></summary><div class="table-scroll"><table><thead><tr><th>Book</th><th>Qty</th><th>Total</th><th>Status</th><th>Date</th></tr></thead><tbody>
<?php foreach($accountOrders as $order): ?><tr><td><?= htmlspecialchars($order['book']) ?></td><td><?= (int)$order['quantity'] ?></td><td>₱<?= number_format((float)$order['total'],2) ?></td><td><?= htmlspecialchars(ucfirst($order['status'])) ?></td><td><?= htmlspecialchars($order['created_at']) ?></td></tr><?php endforeach; ?>
</tbody></table></div></details><?php endif; ?></td></tr><?php endforeach; ?>
</tbody></table></div><?php endif; ?></div>
<div class="content-panel walk-in-panel"><div class="panel-heading"><div><h2>Walk-in customer records</h2><p>Optional contact records for in-store visitors.</p></div></div>
<form method="POST" class="vintage-form walk-in-form"><input type="text" name="name" placeholder="Customer name" required maxlength="255"><input type="email" name="email" placeholder="Email address" required maxlength="255"><button type="submit" name="add_customer">Add walk-in customer</button></form>
<?php if(!empty($_SESSION['customers'])): ?><div class="table-scroll"><table><thead><tr><th>Customer name</th><th>Email address</th></tr></thead><tbody>
<?php foreach($_SESSION['customers'] as $customer): ?><tr><td><?= htmlspecialchars($customer['name']) ?></td><td><?= htmlspecialchars($customer['email']) ?></td></tr><?php endforeach; ?>
</tbody></table></div><?php endif; ?></div></section>
