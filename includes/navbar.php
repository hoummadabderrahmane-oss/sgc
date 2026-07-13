<nav class="navbar navbar-expand-lg sticky-top">
    <div class="container-fluid">
        <button class="navbar-toggler d-md-none" type="button" id="sidebarToggle">
            <i class="bi bi-list text-white"></i>
        </button>
        <a class="navbar-brand" href="/dashboard/">
            <i class="bi bi-building"></i> CMS Baladiya
        </a>
        <div class="d-flex align-items-center gap-3">
            <span class="text-muted d-none d-md-block" id="liveClock"><?= date('H:i:s') ?></span>
            <div class="dropdown">
                <a class="nav-link dropdown-toggle d-flex align-items-center gap-2" href="#" data-bs-toggle="dropdown">
                    <div class="avatar" style="width:32px;height:32px;font-size:0.875rem;">
                        <?= strtoupper(substr($_SESSION['full_name'] ?? 'U', 0, 1)) ?>
                    </div>
                    <span class="d-none d-md-inline"><?= htmlspecialchars($_SESSION['full_name'] ?? 'User') ?></span>
                </a>
                <ul class="dropdown-menu dropdown-menu-end" style="background:rgba(15,15,26,0.95);border:1px solid var(--card-border);border-radius:16px;">
                    <li><a class="dropdown-item" href="/users/" style="color:var(--text);"><i class="bi bi-gear"></i> Settings</a></li>
                    <li><hr class="dropdown-divider" style="border-color:var(--card-border);"></li>
                    <li><a class="dropdown-item text-danger" href="/auth/logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
                </ul>
            </div>
        </div>
    </div>
</nav>