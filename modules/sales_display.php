<section class="module-page">
<span class="eyebrow">THE ART OF EXCHANGE</span><h1>Book Sales</h1>
<p>Record purchases and monitor your bookshop transactions.</p>
<?php if (isset($_SESSION['purchase_notice'])): ?>
<div class="form-notice"><?= htmlspecialchars($_SESSION['purchase_notice']) ?></div>
<?php unset($_SESSION['purchase_notice']); endif; ?>
<?php if (isset($_SESSION['purchase_error'])): ?>
<div class="form-error"><?= htmlspecialchars($_SESSION['purchase_error']) ?></div>
<?php unset($_SESSION['purchase_error']); endif; ?>
<div class="content-panel">
<h2>Customer Orders</h2>
<?php $customerOrders = $saleRepo->getManageableRequests(); ?>
<?php if (empty($customerOrders)): ?>
<p>There are no pending or approved customer orders.</p>
<?php else: ?>
<div class="table-scroll">
<table>
<tr><th>Customer</th><th>Book</th><th>Quantity</th><th>Total</th><th>Order status</th><th>Delivery</th><th>Payment</th><th>Payment status</th><th>Requested</th><th>Action</th></tr>
<?php foreach ($customerOrders as $request): ?>
<tr>
<td><?= htmlspecialchars($request['customer']) ?><br><?= htmlspecialchars($request['email']) ?><br><?= htmlspecialchars($request['phone']) ?></td>
<td><?= htmlspecialchars($request['book']) ?></td>
<td><?= (int)$request['quantity'] ?></td>
<td>₱<?= number_format($request['total'], 2) ?></td>
<td><?= htmlspecialchars(ucfirst($request['status'])) ?></td>
<td><?= htmlspecialchars($request['shipping_name']) ?><br><?= nl2br(htmlspecialchars($request['shipping_address'] ?? '')) ?></td>
<td><?= $request['payment_method']==='manual_transfer'?'Manual transfer (legacy order)':'Cash on delivery' ?>
<?php if (!empty($request['payment_reference'])): ?><br>Ref: <?= htmlspecialchars($request['payment_reference']) ?><?php endif; ?></td>
<td><?= htmlspecialchars(str_replace('_', ' ', ucfirst($request['payment_status']))) ?></td>
<td><?= htmlspecialchars($request['created_at']) ?></td>
<td>
<div class="order-actions">
<?php if ($request['status'] === 'pending'): ?>
<form method="POST" class="request-review-form">
<input type="hidden" name="request_id" value="<?= (int)$request['id'] ?>">
<input type="hidden" name="review_purchase" value="1">
<button type="submit" name="decision" value="approve" class="request-approve">Approve</button>
<button type="submit" name="decision" value="reject" class="request-reject">Reject</button>
</form>
<?php endif; ?>
<?php if ($request['payment_status'] !== 'paid'): ?>
<form method="POST" class="request-review-form">
<input type="hidden" name="request_id" value="<?= (int)$request['id'] ?>">
<button type="submit" name="mark_payment_received" value="1">Mark payment received</button>
</form>
<?php endif; ?>
</div>
</td>
</tr>
<?php endforeach; ?>
</table>
</div>
<?php endif; ?>
</div>
<div class="content-panel"><h2>Record a New Sale</h2><?php if(isset($_SESSION['sale_error'])):?><p class="form-error"><?= htmlspecialchars($_SESSION['sale_error']) ?></p><?php unset($_SESSION['sale_error']); endif; ?>
<form method="POST" class="vintage-form"><label>Select Book</label><select name="book_id" required><?php foreach($bookRepo->getAll() as $book): ?><option value="<?= (int)$book['id'] ?>" <?= $book['stock']<=0?'disabled':'' ?>><?= htmlspecialchars($book['title']) ?> (Stock: <?= $book['stock'] ?>)</option><?php endforeach; ?></select>
<select name="customer_id"><option value="0">Walk-in / no customer record</option><?php foreach($customerRepo->getAll() as $customer): ?><option value="<?= (int)$customer['id'] ?>"><?= htmlspecialchars($customer['name']) ?></option><?php endforeach; ?></select><input type="number" name="quantity" placeholder="Quantity" min="1" required><button type="submit" name="add_sale">Record Sale</button></form></div>
<div class="content-panel"><h2>Sales Transactions</h2><div class="table-scroll"><table><tr><th>No.</th><th>Book</th><th>Customer</th><th>Quantity</th><th>Total Amount</th></tr>
<?php foreach($_SESSION['sales'] as $i=>$sale): ?><tr><td><?= $i+1 ?></td><td><?= htmlspecialchars($sale['book']) ?></td><td><?= htmlspecialchars($sale['customer']??'Walk-in') ?></td><td><?= $sale['quantity'] ?></td><td>₱<?= number_format($sale['total'],2) ?></td></tr><?php endforeach; ?>
</table></div></div></section>
