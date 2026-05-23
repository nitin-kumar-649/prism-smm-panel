<header class="sticky top-0 z-30 glass-navbar border-b border-white/5">
    <div class="flex items-center justify-between h-16 px-4 md:px-6">
        <!-- Left: Mobile menu + Page title -->
        <div class="flex items-center gap-3">
            <button class="lg:hidden p-2 rounded-lg text-gray-400 hover:text-white hover:bg-white/5" onclick="toggleSidebar()">
                <i class="fas fa-bars text-lg"></i>
            </button>
            <h2 class="text-lg font-semibold text-gray-200"><?= e($title ?? 'Dashboard') ?></h2>
        </div>

        <!-- Right: Actions -->
        <div class="flex items-center gap-3">
            <!-- Balance (user only) -->
            <?php if (current_user_role() !== 'admin'): ?>
            <a href="/user/wallet" class="hidden sm:flex items-center gap-2 px-3 py-1.5 rounded-lg bg-indigo-600/10 border border-indigo-500/20 text-indigo-400 text-sm font-medium hover:bg-indigo-600/20 transition-colors">
                <i class="fas fa-wallet text-xs"></i>
                <span><?= format_money((float)($currentUser['balance'] ?? 0)) ?></span>
            </a>
            <?php endif; ?>

            <!-- Theme Toggle -->
            <button onclick="toggleTheme()" class="p-2 rounded-lg text-gray-400 hover:text-white hover:bg-white/5 transition-colors" title="Toggle theme">
                <i class="fas fa-moon text-lg" id="theme-icon"></i>
            </button>

            <!-- Notifications -->
            <div class="relative" id="notification-dropdown">
                <button onclick="toggleNotifications()" class="relative p-2 rounded-lg text-gray-400 hover:text-white hover:bg-white/5 transition-colors">
                    <i class="fas fa-bell text-lg"></i>
                    <span id="notification-badge" class="absolute -top-0.5 -right-0.5 w-4 h-4 bg-red-500 rounded-full text-[10px] font-bold flex items-center justify-center text-white hidden">0</span>
                </button>

                <!-- Dropdown -->
                <div id="notification-panel" class="hidden absolute right-0 mt-2 w-80 glass-card rounded-xl border border-white/10 shadow-2xl overflow-hidden">
                    <div class="flex items-center justify-between px-4 py-3 border-b border-white/5">
                        <h3 class="text-sm font-semibold">Notifications</h3>
                        <button onclick="markAllRead()" class="text-xs text-indigo-400 hover:text-indigo-300">Mark all read</button>
                    </div>
                    <div id="notification-list" class="max-h-64 overflow-y-auto">
                        <p class="p-4 text-sm text-gray-500 text-center">No new notifications</p>
                    </div>
                </div>
            </div>

            <!-- User Menu -->
            <div class="relative" id="user-menu">
                <button onclick="document.getElementById('user-dropdown').classList.toggle('hidden')" class="flex items-center gap-2 p-1.5 rounded-lg hover:bg-white/5 transition-colors">
                    <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center">
                        <span class="text-xs font-bold text-white"><?= strtoupper(substr($currentUser['username'] ?? 'U', 0, 1)) ?></span>
                    </div>
                    <span class="hidden md:block text-sm text-gray-300"><?= e($currentUser['username'] ?? 'User') ?></span>
                    <i class="fas fa-chevron-down text-[10px] text-gray-500 hidden md:block"></i>
                </button>

                <div id="user-dropdown" class="hidden absolute right-0 mt-2 w-48 glass-card rounded-xl border border-white/10 shadow-2xl overflow-hidden">
                    <a href="/user/profile" class="flex items-center gap-2 px-4 py-2.5 text-sm text-gray-400 hover:text-white hover:bg-white/5">
                        <i class="fas fa-user w-4"></i> Profile
                    </a>
                    <a href="/user/wallet" class="flex items-center gap-2 px-4 py-2.5 text-sm text-gray-400 hover:text-white hover:bg-white/5">
                        <i class="fas fa-wallet w-4"></i> Wallet
                    </a>
                    <div class="border-t border-white/5"></div>
                    <a href="/logout" class="flex items-center gap-2 px-4 py-2.5 text-sm text-red-400 hover:text-red-300 hover:bg-white/5">
                        <i class="fas fa-sign-out-alt w-4"></i> Logout
                    </a>
                </div>
            </div>
        </div>
    </div>
</header>
