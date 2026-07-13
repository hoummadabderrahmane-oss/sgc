<nav class="col-md-3 col-lg-2 d-md-block sidebar collapse">
    <div class="position-sticky pt-3">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'index.php' && strpos($_SERVER['PHP_SELF'], 'dashboard') !== false ? 'active' : '' ?>" href="/dashboard/">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= strpos($_SERVER['PHP_SELF'], '/citizens/') !== false ? 'active' : '' ?>" href="/citizens/">
                    <i class="bi bi-people"></i> Citizens
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= strpos($_SERVER['PHP_SELF'], '/documents/') !== false ? 'active' : '' ?>" href="/documents/">
                    <i class="bi bi-file-earmark-text"></i> Documents
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= strpos($_SERVER['PHP_SELF'], '/requests/') !== false ? 'active' : '' ?>" href="/requests/">
                    <i class="bi bi-inbox"></i> Requests
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= strpos($_SERVER['PHP_SELF'], '/reports/') !== false ? 'active' : '' ?>" href="/reports/">
                    <i class="bi bi-graph-up"></i> Reports
                </a>
            </li>
            <?php if (hasRole('admin')): ?>
            <li class="nav-item mt-3">
                <small class="text-muted px-3 text-uppercase" style="font-size:0.7rem;letter-spacing:0.1em;">Admin</small>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= strpos($_SERVER['PHP_SELF'], '/users/') !== false ? 'active' : '' ?>" href="/users/">
                    <i class="bi bi-shield-lock"></i> Users
                </a>
            </li>
            <?php endif; ?>
        </ul>
    </div>
</nav>