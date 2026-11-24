<section class="module-page">
    <span class="eyebrow">THE LITERARY COLLECTION</span>
    <h1>Our Books</h1>
    <p>Discover, organize, and manage every treasured book.</p>

    <?php foreach (['book_error' => 'form-error', 'book_notice' => 'form-notice'] as $key => $class): ?>
        <?php if (isset($_SESSION[$key])): ?>
            <div class="<?= $class ?>"><?= htmlspecialchars($_SESSION[$key]) ?></div>
            <?php unset($_SESSION[$key]); ?>
        <?php endif; ?>
    <?php endforeach; ?>

    <?php include __DIR__ . '/books_add_form.php'; ?>
    <?php include __DIR__ . '/books_table.php'; ?>
</section>
