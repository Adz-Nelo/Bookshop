<?php if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']): ?>

<header class="welcome-header">
    <div class="brand">
        Whispering Scriptorium
        <span>A Sanctuary for Every Story</span>
    </div>
</header>

<?php elseif (($_SESSION['user_role'] ?? '') === 'client'): ?>
<header class="main-header client-header">
    <a class="brand" href="index.php?page=catalog">
        Whispering Scriptorium
        <span>Books • Stories • Discoveries</span>
    </a>
    <nav aria-label="Client navigation">
        <a href="index.php?page=catalog">Browse Books</a>
        <a href="index.php?page=account">My Account</a>
        <a href="index.php?logout=1">Log Out</a>
    </nav>
</header>

<?php else: ?>

<header class="main-header admin-header">
    <div class="admin-heading">
        <span>ADMIN WORKSPACE</span>
        <strong>Whispering Scriptorium</strong>
    </div>
    <div class="admin-profile">
        <span class="admin-avatar" aria-hidden="true"><?= htmlspecialchars(strtoupper(substr($_SESSION['user_name'] ?? 'A', 0, 1))) ?></span>
        <span>
            <small>Signed in as</small>
            <strong><?= htmlspecialchars($_SESSION['user_name'] ?? 'Administrator') ?></strong>
        </span>
    </div>
</header>

<?php endif; ?>