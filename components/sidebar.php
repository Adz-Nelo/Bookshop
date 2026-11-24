<aside class="sidebar" aria-label="Bookshop sections">
    <a class="sidebar-title" href="index.php?page=dashboard">Whispering<br>Scriptorium</a>
    <span class="sidebar-label">WORKSPACE</span>
    <a class="sidebar-link <?= $page === 'dashboard' ? 'active' : '' ?>" href="index.php?page=dashboard"><span aria-hidden="true">⌂</span> Dashboard</a>
    <a class="sidebar-link <?= $page === 'books' ? 'active' : '' ?>" href="index.php?page=books"><span aria-hidden="true">▤</span> Books</a>
    <a class="sidebar-link <?= $page === 'categories' ? 'active' : '' ?>" href="index.php?page=categories"><span aria-hidden="true">▦</span> Categories</a>
    <a class="sidebar-link <?= $page === 'customers' ? 'active' : '' ?>" href="index.php?page=customers"><span aria-hidden="true">♙</span> Users</a>
    <a class="sidebar-link <?= $page === 'sales' ? 'active' : '' ?>" href="index.php?page=sales"><span aria-hidden="true">↗</span> Sales & orders</a>
    <a class="sidebar-link <?= $page === 'suppliers' ? 'active' : '' ?>" href="index.php?page=suppliers"><span aria-hidden="true">♧</span> Suppliers</a>
    <a class="sidebar-link <?= $page === 'reports' ? 'active' : '' ?>" href="index.php?page=reports"><span aria-hidden="true">▥</span> Reports</a>

    <div class="sidebar-bottom">
        <a class="sidebar-link" href="index.php?logout=1"><span aria-hidden="true">↪</span> Sign out</a>
    </div>
</aside>