<section class="module-page">

    <span class="eyebrow">OUR TRUSTED PARTNERS</span>
    <h1>Book Suppliers</h1>
    <p>Manage the publishing houses and suppliers of our collection.</p>

    <div class="content-panel">
        <h2>Add New Supplier</h2>

        <form method="POST" class="vintage-form">
            <input type="text" name="name"
                   placeholder="Supplier Name" required>

            <input type="text" name="contact"
                   placeholder="Contact Number" required>

            <button type="submit" name="add_supplier">
                Add Supplier
            </button>
        </form>
    </div>

    <div class="content-panel">
        <h2>Supplier Directory</h2>

        <div class="table-scroll">
        <table>
            <tr>
                <th>No.</th>
                <th>Supplier Name</th>
                <th>Contact Number</th>
            </tr>

            <?php foreach ($_SESSION['suppliers'] as $i => $supplier): ?>
            <tr>
                <td><?= $i + 1 ?></td>
                <td><?= htmlspecialchars($supplier['name']) ?></td>
                <td><?= htmlspecialchars($supplier['contact']) ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
        </div>
    </div>

</section>