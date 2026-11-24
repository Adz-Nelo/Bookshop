<?php $formId = 'edit-book-' . (int) $book['id']; ?>
<tr>
    <td>
        <form id="<?= $formId ?>" method="POST"></form>
        <img class="book-cover-thumb" src="<?= htmlspecialchars($book['cover_image']) ?>"
             alt="<?= htmlspecialchars($book['title']) ?> cover image">
        <select form="<?= $formId ?>" name="cover_image" aria-label="Cover for <?= htmlspecialchars($book['title']) ?>">
            <?php foreach (array_unique(array_merge([$book['cover_image']], $coverOptions)) as $coverPath): ?>
                <?php $coverValue = str_replace(__DIR__ . '/../', '', $coverPath); ?>
                <option value="<?= htmlspecialchars($coverValue) ?>" <?= $coverPath === $book['cover_image'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars(basename($coverPath)) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </td>
    <td><input form="<?= $formId ?>" name="title" aria-label="Title for <?= htmlspecialchars($book['title']) ?>" value="<?= htmlspecialchars($book['title']) ?>" maxlength="255" required></td>
    <td><input form="<?= $formId ?>" name="author" aria-label="Author for <?= htmlspecialchars($book['title']) ?>" value="<?= htmlspecialchars($book['author']) ?>" maxlength="255" required></td>
    <td>
        <select form="<?= $formId ?>" name="category" aria-label="Category for <?= htmlspecialchars($book['title']) ?>" required>
            <?php foreach ($bookCategories as $category): ?>
                <option value="<?= htmlspecialchars($category) ?>" <?= $category === $book['category'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($category) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </td>
    <td>
        <select form="<?= $formId ?>" name="supplier_id" aria-label="Supplier for <?= htmlspecialchars($book['title']) ?>">
            <option value="0">Unassigned</option>
            <?php foreach ($supplierRepo->getAll() as $supplier): ?>
                <option value="<?= (int) $supplier['id'] ?>" <?= (int) $supplier['id'] === (int) ($book['supplier_id'] ?? 0) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($supplier['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </td>
    <td><input form="<?= $formId ?>" type="number" name="price" aria-label="Price for <?= htmlspecialchars($book['title']) ?>" value="<?= htmlspecialchars($book['price']) ?>" min="0" step="0.01" required></td>
    <td><input form="<?= $formId ?>" type="number" name="stock" aria-label="Stock for <?= htmlspecialchars($book['title']) ?>" value="<?= (int) $book['stock'] ?>" min="0" required></td>
    <td><textarea form="<?= $formId ?>" name="description" aria-label="Description for <?= htmlspecialchars($book['title']) ?>" maxlength="3000"><?= htmlspecialchars($book['description'] ?? '') ?></textarea></td>
    <td>
        <input form="<?= $formId ?>" type="hidden" name="book_id" value="<?= (int) $book['id'] ?>">
        <button class="book-save-button" form="<?= $formId ?>" type="submit" name="update_book" value="1">Save changes</button>
        <form class="book-delete-form" method="POST" onsubmit="return confirm('Delete this book?');">
            <input type="hidden" name="book_id" value="<?= (int) $book['id'] ?>">
            <button class="book-delete-button" type="submit" name="delete_book" value="1">Delete</button>
        </form>
    </td>
</tr>
