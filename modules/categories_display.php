<section class="module-page">

    <span class="eyebrow">ORGANIZE YOUR STORIES</span>
    <h1>Book Categories</h1>
    <p>Arrange your books according to their literary genres.</p>

    <div class="content-panel">
        <h2>Add New Category</h2>

        <form method="POST" class="vintage-form">
            <input type="text" name="category"
                   placeholder="Enter category name" required>
            <button type="submit" name="add_category">
                Add Category
            </button>
        </form>
    </div>

    <div class="content-panel">
        <h2>Our Literary Shelves</h2>

        <div class="category-grid">
            <?php foreach ($categoryRepo->getAll() as $category): ?>
                <div class="category-card">
                    <span>❧</span>
                    <h3><?= htmlspecialchars($category) ?></h3>
                    <small>Literary Collection</small>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

</section>