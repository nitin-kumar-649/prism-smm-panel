<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= e($title ?? 'Prism SMM Panel') ?>">
    <meta name="robots" content="index, follow">
    <title><?= e($title ?? 'Dashboard') ?> - <?= e($appName) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        primary: { 50:'#eef2ff',100:'#e0e7ff',200:'#c7d2fe',300:'#a5b4fc',400:'#818cf8',500:'#6366f1',600:'#4f46e5',700:'#4338ca',800:'#3730a3',900:'#312e81' },
                        dark: { 50:'#f8fafc',100:'#f1f5f9',200:'#e2e8f0',300:'#cbd5e1',400:'#94a3b8',500:'#64748b',600:'#475569',700:'#334155',800:'#1e293b',900:'#0f172a',950:'#020617' }
                    }
                }
            }
        }
    </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="<?= asset('css/app.css') ?>">
</head>
<body class="bg-dark-950 text-gray-200 min-h-screen antialiased">
    <div class="flex h-screen overflow-hidden" id="app">
        <!-- Sidebar -->
        <?php include BASE_PATH . '/templates/layouts/sidebar.php'; ?>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Top Navbar -->
            <?php include BASE_PATH . '/templates/layouts/navbar.php'; ?>

            <!-- Page Content -->
            <main class="flex-1 overflow-y-auto p-4 md:p-6 lg:p-8">
                <!-- Flash Messages -->
                <?php $flash = flash(); if ($flash): ?>
                <div class="mb-4 p-4 rounded-xl glass-card border <?= $flash['type'] === 'error' ? 'border-red-500/30 text-red-400' : 'border-green-500/30 text-green-400' ?>" id="flash-message">
                    <div class="flex items-center justify-between">
                        <span><?= e($flash['message']) ?></span>
                        <button onclick="this.parentElement.parentElement.remove()" class="ml-4 opacity-60 hover:opacity-100">&times;</button>
                    </div>
                </div>
                <?php endif; ?>

                <?= $content ?? '' ?>
            </main>
        </div>
    </div>

    <!-- Mobile Sidebar Overlay -->
    <div id="sidebar-overlay" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-40 hidden lg:hidden" onclick="toggleSidebar()"></div>

    <!-- Notification Toast Container -->
    <div id="toast-container" class="fixed top-4 right-4 z-50 space-y-2"></div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script src="<?= asset('js/app.js') ?>"></script>
</body>
</html>
