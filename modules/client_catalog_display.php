<section class="module-page client-store">
    <span class="eyebrow">A SANCTUARY FOR EVERY STORY</span>
    <h1>Browse the Collection</h1>
    <p>Find your next favorite among the books on our shelves.</p>

    <?php if (isset($_SESSION['purchase_notice'])): ?>
        <div class="form-notice"><?= htmlspecialchars($_SESSION['purchase_notice']) ?></div>
        <?php unset($_SESSION['purchase_notice']); ?>
    <?php endif; ?>
    <?php if (isset($_SESSION['purchase_error'])): ?>
        <div class="form-error"><?= htmlspecialchars($_SESSION['purchase_error']) ?></div>
        <?php unset($_SESSION['purchase_error']); ?>
    <?php endif; ?>

    <?php if (empty($_SESSION['books'])): ?>
        <div class="content-panel">
            <p>Our shelves are being arranged. Please check back soon.</p>
        </div>
    <?php else: ?>
        <div class="client-book-grid">
            <?php foreach ($_SESSION['books'] as $book): ?>
                <article class="client-book-card">
                    <a class="book-cover-link"
                       href="index.php?page=book&amp;book_id=<?= (int) $book['id'] ?>"
                       aria-label="View <?= htmlspecialchars($book['title']) ?> details">
                        <img class="client-book-cover"
                             src="<?= htmlspecialchars($book['cover_image'] ?: 'assets/books/default-book.svg') ?>"
                             alt="<?= htmlspecialchars($book['title']) ?> cover image">
                    </a>
                    <span class="book-mark" aria-hidden="true">❧</span>
                    <small><?= htmlspecialchars($book['category']) ?></small>
                    <h2><?= htmlspecialchars($book['title']) ?></h2>
                    <p>by <?= htmlspecialchars($book['author']) ?></p>
                    <a class="book-details-link"
                       href="index.php?page=book&amp;book_id=<?= (int) $book['id'] ?>">
                        View book and description <span aria-hidden="true">→</span>
                    </a>
                    <div class="book-card-footer">
                        <strong>₱<?= number_format($book['price'], 2) ?></strong>
                        <span><?= (int) $book['stock'] > 0 ? 'In stock' : 'Sold out' ?></span>
                    </div>
                    <?php if ((int) $book['stock'] > 0): ?>
                        <a class="vintage-button checkout-link"
                           href="index.php?page=checkout&amp;book_id=<?= (int) $book['id'] ?>">
                            Buy this book
                        </a>
                    <?php endif; ?>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
