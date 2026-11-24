<?php $page = $page ?? 'welcome'; ?>
<?php include 'components/head.php'; ?>
<div class="original-login-screen">
    <div class="login-card">
        <?php if ($page === 'register'): ?>
            <h1>Join Our Readers</h1>
            <p>Create an account to explore our collection.</p>
        <?php else: ?>
            <h1>Log In</h1>
            <p>Welcome to Whispering Scriptorium</p>
        <?php endif; ?>
        <?php if (isset($_SESSION[$page === 'register' ? 'register_error' : 'login_error'])): ?>
            <div class="login-error">
                <?= htmlspecialchars($_SESSION[$page === 'register' ? 'register_error' : 'login_error']) ?>
            </div>
            <?php unset($_SESSION[$page === 'register' ? 'register_error' : 'login_error']); ?>
        <?php endif; ?>
        <form method="POST" action="index.php?page=<?= htmlspecialchars($page) ?>">
            <?php if ($page === 'register'): ?>
                <input type="text" name="name" placeholder="Full name" required autocomplete="name" maxlength="255">
                <input type="text" name="username" placeholder="Username" required autocomplete="username" maxlength="80">
                <input type="email" name="email" placeholder="Email address" required autocomplete="email" maxlength="255">
                <input type="password" name="password" placeholder="Password (8+ characters)" required autocomplete="new-password" minlength="8">
                <input type="password" name="password_confirmation" placeholder="Confirm password" required autocomplete="new-password" minlength="8">
                <button type="submit" name="register">Create Client Account</button>
            <?php else: ?>
                <input type="text" name="username" placeholder="Username or email" required autocomplete="username">
                <input type="password" name="password" placeholder="Password" required autocomplete="current-password">
                <button type="submit" name="login">Log In</button>
            <?php endif; ?>
        </form>
        <a class="back-to-welcome" href="index.php?page=<?= $page === 'register' ? 'login' : 'register' ?>">
            <?= $page === 'register' ? 'Already have an account? Log in' : 'New reader? Create an account' ?>
        </a>
    </div>
</div>
</body>

</html>
