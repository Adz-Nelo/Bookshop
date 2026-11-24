<div class="content-panel">
    <h2>Add a New Book</h2>
    <form method="POST" class="vintage-form" enctype="multipart/form-data">
        <input type="text" name="title" placeholder="Book Title" maxlength="255" required>
        <input type="text" name="author" placeholder="Author" maxlength="255" required>
        <select name="category" required>
            <?php foreach ($categoryRepo->getAll() as $category): ?>
                <option value="<?= htmlspecialchars($category) ?>"><?= htmlspecialchars($category) ?></option>
            <?php endforeach; ?>
        </select>
        <select name="supplier_id">
            <option value="0">No supplier assigned</option>
            <?php foreach ($supplierRepo->getAll() as $supplier): ?>
                <option value="<?= (int) $supplier['id'] ?>"><?= htmlspecialchars($supplier['name']) ?></option>
            <?php endforeach; ?>
        </select>
        <input type="number" name="price" placeholder="Price" min="0" step="0.01" required>
        <input type="number" name="stock" placeholder="Stock" min="0" required>
        <label for="book_description">Book description</label>
        <textarea id="book_description" name="description" rows="4" maxlength="3000"
                  placeholder="Add a description readers can view." class="book-description-input"></textarea>
        <?php $coverOptions = glob(__DIR__ . '/../assets/books/*.svg'); sort($coverOptions); ?>
        <label for="cover_image">Select a book cover</label>
        <select id="cover_image" name="cover_image">
            <?php foreach ($coverOptions as $coverPath): ?>
                <?php $coverValue = str_replace(__DIR__ . '/../', '', $coverPath); ?>
                <option value="<?= htmlspecialchars($coverValue) ?>"><?= htmlspecialchars(basename($coverPath)) ?></option>
            <?php endforeach; ?>
        </select>
        <label for="cover_file">Import a custom image</label>
        <input id="cover_file" type="file" name="cover_file" accept="image/*">
        <button type="submit" name="add_book">Add Book</button>
    </form>
</div>
