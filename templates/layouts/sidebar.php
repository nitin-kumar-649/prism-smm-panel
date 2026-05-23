<?php
$role = current_user_role();
$currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

$userMenu = [
    ['url' => '/user', 'icon' => 'fas fa-chart-pie', 'label' => 'Dashboard'],
    ['url' => '/user/new-order', 'icon' => 'fas fa-cart-plus', 'label' => 'New Order'],
    ['url' => '/user/bulk-order', 'icon' => 'fas fa-layer-group', 'label' => 'Bulk Orders'],
    ['url' => '/user/orders', 'icon' => 'fas fa-list-ol', 'label' => 'My Orders'],
    ['url' => '/user/wallet', 'icon' => 'fas fa-wallet', 'label' => 'Wallet'],
    ['url' => '/user/tickets', 'icon' => 'fas fa-headset', 'label' => 'Support'],
    ['url' => '/user/api', 'icon' => 'fas fa-code', 'label' => 'API'],
    ['url' => '/user/profile', 'icon' => 'fas fa-user-cog', 'label' => 'Profile'],
];

$adminMenu = [
    ['url' => '/admin', 'icon' => 'fas fa-chart-line', 'label' => 'Dashboard'],
    ['url' => '/admin/orders', 'icon' => 'fas fa-shopping-bag', 'label' => 'Orders'],
    ['url' => '/admin/services', 'icon' => 'fas fa-cubes', 'label' => 'Services'],
    ['url' => '/admin/users', 'icon' => 'fas fa-users', 'label' => 'Users'],
    ['url' => '/admin/payments', 'icon' => 'fas fa-credit-card', 'label' => 'Payments'],
    ['url' => '/admin/providers', 'icon' => 'fas fa-server', 'label' => 'Providers'],
    ['url' => '/admin/tickets', 'icon' => 'fas fa-ticket-alt', 'label' => 'Tickets'],
    ['url' => '/admin/settings', 'icon' => 'fas fa-cog', 'label' => 'Settings'],
];

$menu = $role === 'admin' ? $adminMenu : $userMenu;
?>

<aside id="sidebar" class="fixed lg:static inset-y-0 left-0 z-50 w-64 glass-sidebar transform -translate-x-full lg:translate-x-0 transition-transform duration-300 ease-in-out flex flex-col">
    <!-- Logo -->
    <div class="flex items-center gap-3 px-6 py-5 border-b border-white/5">
        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center shadow-lg shadow-indigo-500/20">
            <i class="fas fa-prism text-white text-lg">P</i>
        </div>
        <div>
            <h1 class="text-lg font-bold bg-gradient-to-r from-indigo-400 to-purple-400 bg-clip-text text-transparent">Prism</h1>
            <p class="text-[10px] text-gray-500 uppercase tracking-wider">SMM Panel</p>
        </div>
        <button class="lg:hidden ml-auto text-gray-400 hover:text-white" onclick="toggleSidebar()">
            <i class="fas fa-times text-xl"></i>
        </button>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-1">
        <?php foreach ($menu as $item): ?>
            <?php $isActive = $currentPath === $item['url'] || ($item['url'] !== '/' && $item['url'] !== '/admin' && $item['url'] !== '/user' && str_starts_with($currentPath, $item['url'])); ?>
            <a href="<?= $item['url'] ?>"
               class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-all duration-200
                      <?= $isActive ? 'bg-indigo-600/20 text-indigo-400 shadow-lg shadow-indigo-500/5' : 'text-gray-400 hover:text-white hover:bg-white/5' ?>">
                <i class="<?= $item['icon'] ?> w-5 text-center <?= $isActive ? 'text-indigo-400' : '' ?>"></i>
                <span><?= $item['label'] ?></span>
                <?php if ($isActive): ?>
                    <div class="ml-auto w-1.5 h-1.5 rounded-full bg-indigo-400"></div>
                <?php endif; ?>
            </a>
        <?php endforeach; ?>
    </nav>

    <!-- User section -->
    <div class="border-t border-white/5 p-4">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-lg bg-gradient-to-br from-indigo-500/20 to-purple-500/20 flex items-center justify-center">
                <span class="text-sm font-bold text-indigo-400"><?= strtoupper(substr($currentUser['username'] ?? 'U', 0, 1)) ?></span>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-gray-300 truncate"><?= e($currentUser['username'] ?? 'User') ?></p>
                <p class="text-xs text-gray-500"><?= format_money((float)($currentUser['balance'] ?? 0)) ?></p>
            </div>
            <a href="/logout" class="text-gray-500 hover:text-red-400 transition-colors" title="Logout">
                <i class="fas fa-sign-out-alt"></i>
            </a>
        </div>
    </div>
</aside>
