<?php
$books = $bookRepo->getAll();
$bookCategories = $categoryRepo->getAll();
$coverOptions = glob(__DIR__ . '/../assets/books/*.svg');
?>
<div class="content-panel">
    <div class="book-collection-heading">
        <div>
            <h2>Book Collection</h2>
            <p>Edit book details in the table, or remove books that have no order history.</p>
        </div>
        <span class="book-count"><?= count($books) ?> <?= count($books) === 1 ? 'book' : 'books' ?></span>
    </div>
    <div class="table-scroll">
        <table class="book-collection-table">
            <thead><tr><th>Cover</th><th>Title</th><th>Author</th><th>Category</th><th>Supplier</th><th>Price</th><th>Stock</th><th>Description</th><th>Actions</th></tr></thead>
            <tbody>
                <?php if ($books): ?>
                    <?php foreach ($books as $book): ?>
                        <?php include __DIR__ . '/books_table_row.php'; ?>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td class="book-empty-state" colspan="9">No books have been added yet. Use the form above to add the first book.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
