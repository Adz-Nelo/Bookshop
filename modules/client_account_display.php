<section class="module-page client-account">
<span class="eyebrow">YOUR READER ACCOUNT</span>
<h1>My Account</h1>
<p>Your details at Whispering Scriptorium.</p>

<?php if (isset($_SESSION['purchase_notice'])): ?>
<div class="form-notice"><?= htmlspecialchars($_SESSION['purchase_notice']) ?></div>
<?php unset($_SESSION['purchase_notice']); endif; ?>
<?php if (isset($_SESSION['purchase_error'])): ?>
<div class="form-error"><?= htmlspecialchars($_SESSION['purchase_error']) ?></div>
<?php unset($_SESSION['purchase_error']); endif; ?>

<div class="content-panel account-card">
<span class="account-avatar" aria-hidden="true">✧</span>
<h2><?= htmlspecialchars($_SESSION['user_name']) ?></h2>
<dl>
<div><dt>Username</dt><dd><?= htmlspecialchars($_SESSION['username']) ?></dd></div>
<div><dt>Email</dt><dd><?= htmlspecialchars($_SESSION['user_email']) ?></dd></div>
<div><dt>Account type</dt><dd>Reader</dd></div>
</dl>
<a class="vintage-button" href="index.php?page=catalog">Return to the collection</a>
</div>

<?php $myOrders=$saleRepo->getRequestsForUser((int)$_SESSION['user_id']); ?>
<div class="content-panel order-history">
<h2>My Orders</h2>
<?php if (empty($myOrders)): ?>
<p>You have not placed any orders yet.</p>
<?php else: ?>
<div class="table-scroll">
<table>
<tr><th>Book</th><th>Qty</th><th>Total</th><th>Order status</th><th>Payment</th><th>Payment status</th><th>Delivery details</th><th>Placed</th></tr>
<?php foreach ($myOrders as $order): ?>
<tr>
<td><?= htmlspecialchars($order['book']) ?></td>
<td><?= (int)$order['quantity'] ?></td>
<td>₱<?= number_format($order['total'],2) ?></td>
<td><?= htmlspecialchars(ucfirst($order['status'])) ?></td>
<td><?= $order['payment_method']==='manual_transfer'?'Manual transfer (legacy order)':'Cash on delivery' ?></td>
<td>
<?php if ($order['payment_status']==='paid'): ?>Paid
<?php elseif ($order['payment_status']==='verification_pending'): ?>Reference submitted — awaiting verification
<?php else: ?>Due on delivery<?php endif; ?>
<?php if (!empty($order['payment_reference'])): ?><br>Ref: <?= htmlspecialchars($order['payment_reference']) ?><?php endif; ?>
</td>
<td><?= htmlspecialchars($order['shipping_name']) ?><br><?= htmlspecialchars($order['phone']) ?><br><?= nl2br(htmlspecialchars($order['shipping_address']??'')) ?></td>
<td><?= htmlspecialchars($order['created_at']) ?></td>
</tr>
<?php endforeach; ?>
</table>
</div>
<?php endif; ?>
</div></section>
