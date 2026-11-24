<?php
$checkoutBook = null;
foreach ($_SESSION['books'] as $book) {
    if ((int)$book['id'] === (int)($_GET['book_id'] ?? 0)) {
        $checkoutBook = $book;
        break;
    }
}
?>
<section class="module-page checkout-page">
<span class="eyebrow">SECURE CHECKOUT</span><h1>Complete Your Order</h1>
<?php if (isset($_SESSION['purchase_error'])): ?>
<div class="form-error"><?= htmlspecialchars($_SESSION['purchase_error']) ?></div>
<?php unset($_SESSION['purchase_error']); endif; ?>
<?php if (!$checkoutBook): ?>
<div class="content-panel"><p>That book is not available for checkout.</p><a class="vintage-button" href="index.php?page=catalog">Return to the collection</a></div>
<?php else: ?>
<div class="content-panel checkout-summary">
<img src="<?= htmlspecialchars($checkoutBook['cover_image'] ?: 'assets/books/default-book.svg') ?>" alt="<?= htmlspecialchars($checkoutBook['title']) ?> cover">
<div><h2><?= htmlspecialchars($checkoutBook['title']) ?></h2><p>by <?= htmlspecialchars($checkoutBook['author']) ?></p><p>₱<?= number_format($checkoutBook['price'], 2) ?> each · <?= (int)$checkoutBook['stock'] ?> in stock</p></div>
</div>
<div class="content-panel">
<h2>Delivery and Payment</h2>
<form method="POST" class="vintage-form checkout-form">
<input type="hidden" name="book_id" value="<?= (int)$checkoutBook['id'] ?>">
<label for="shipping_name">Recipient name</label><input id="shipping_name" type="text" name="shipping_name" value="<?= htmlspecialchars($_SESSION['user_name']) ?>" maxlength="255" autocomplete="name" required>
<label for="phone">Phone number</label><input id="phone" type="tel" name="phone" maxlength="40" autocomplete="tel" required>
<label for="shipping_address">Delivery address</label><textarea id="shipping_address" name="shipping_address" rows="3" maxlength="1500" autocomplete="street-address" required></textarea>
<label for="quantity">Quantity</label><input id="quantity" type="number" name="quantity" min="1" max="<?= (int)$checkoutBook['stock'] ?>" value="1" required>
<span class="checkout-total">Order total: <strong id="checkout-total" data-unit-price="<?= htmlspecialchars((string)$checkoutBook['price']) ?>" aria-live="polite">₱<?= number_format($checkoutBook['price'], 2) ?></strong></span>
<input type="hidden" name="payment_method" value="cash_on_delivery"><p class="checkout-help">Payment is due in cash when your order is delivered.</p>
<button type="submit" name="request_purchase">Place Order Request</button>
</form></div>
<script>
const quantityInput=document.getElementById('quantity'),orderTotal=document.getElementById('checkout-total'),unitPrice=Number(orderTotal.dataset.unitPrice);
const updateOrderTotal=()=>{const quantity=Math.max(1,Number(quantityInput.value)||1);orderTotal.textContent='₱'+(unitPrice*quantity).toLocaleString('en-PH',{minimumFractionDigits:2,maximumFractionDigits:2});};
quantityInput.addEventListener('input',updateOrderTotal);
</script>
<?php endif; ?>
</section>
