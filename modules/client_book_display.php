<?php
$requestedBookId = filter_var(
    $_GET['book_id'] ?? '',
    FILTER_VALIDATE_INT,
    ['options' => ['min_range' => 1]]
);
$selectedBook = null;
if ($requestedBookId !== false) {
    foreach ($_SESSION['books'] as $book) {
        if ((int) $book['id'] === (int) $requestedBookId) {
            $selectedBook = $book;
            break;
        }
    }
}
?>

<section class="module-page book-detail-page">
    <a class="back-to-catalog" href="index.php?page=catalog">← Back to the collection</a>

    <?php if (!$selectedBook): ?>
        <div class="content-panel">
            <h1>Book not found</h1>
            <p>This book is no longer available in the collection.</p>
        </div>
    <?php else: ?>
        <div class="book-detail-layout">
            <div class="book-detail-art">
                <img src="<?= htmlspecialchars($selectedBook['cover_image'] ?: 'assets/books/default-book.svg') ?>"
                     alt="<?= htmlspecialchars($selectedBook['title']) ?> cover image">
            </div>
            <article class="content-panel book-detail-content">
                <span class="eyebrow"><?= htmlspecialchars($selectedBook['category']) ?></span>
                <h1><?= htmlspecialchars($selectedBook['title']) ?></h1>
                <p class="book-detail-author">by <?= htmlspecialchars($selectedBook['author']) ?></p>
                <div class="book-detail-meta">
                    <strong>₱<?= number_format((float) $selectedBook['price'], 2) ?></strong>
                    <span><?= (int) $selectedBook['stock'] > 0 ? 'In stock' : 'Currently sold out' ?></span>
                </div>

                <div class="book-description">
                    <h2>About this book</h2>
                    <?php if (trim((string) ($selectedBook['description'] ?? '')) !== ''): ?>
                        <p><?= nl2br(htmlspecialchars($selectedBook['description'])) ?></p>
                    <?php else: ?>
                        <p>The bookshop has not added a description for this title yet.</p>
                    <?php endif; ?>
                </div>

                <?php if ((int) $selectedBook['stock'] > 0): ?>
                    <a class="vintage-button book-detail-buy"
                       href="index.php?page=checkout&amp;book_id=<?= (int) $selectedBook['id'] ?>">
                        Buy this book
                    </a>
                <?php endif; ?>
            </article>
        </div>
    <?php endif; ?>
</section>
