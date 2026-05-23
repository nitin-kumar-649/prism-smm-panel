<?php ob_start(); ?>

<!-- Stats Cards -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
    <?php
    $cards = [
        ['icon' => 'fas fa-dollar-sign', 'label' => 'Revenue Today', 'value' => format_money($stats['orders']['revenue_today']), 'sub' => 'Month: ' . format_money($stats['orders']['revenue_month']), 'gradient' => 'from-green-500 to-emerald-600'],
        ['icon' => 'fas fa-shopping-bag', 'label' => 'Total Orders', 'value' => number_format($stats['orders']['total']), 'sub' => 'Today: ' . $stats['orders']['today'], 'gradient' => 'from-indigo-500 to-purple-600'],
        ['icon' => 'fas fa-users', 'label' => 'Total Users', 'value' => number_format($stats['users']['total']), 'sub' => 'Today: ' . $stats['users']['today'], 'gradient' => 'from-blue-500 to-cyan-600'],
        ['icon' => 'fas fa-clock', 'label' => 'Pending', 'value' => number_format($stats['orders']['pending']), 'sub' => 'Processing: ' . $stats['orders']['processing'], 'gradient' => 'from-yellow-500 to-orange-500'],
    ];
    foreach ($cards as $card):
    ?>
    <div class="glass-card rounded-xl border border-white/5 p-5">
        <div class="flex items-center gap-3 mb-3">
            <div class="w-10 h-10 rounded-lg bg-gradient-to-br <?= $card['gradient'] ?> flex items-center justify-center opacity-80">
                <i class="<?= $card['icon'] ?> text-white text-sm"></i>
            </div>
        </div>
        <p class="text-2xl font-bold text-white"><?= $card['value'] ?></p>
        <p class="text-xs text-gray-500 mt-0.5"><?= $card['label'] ?></p>
        <p class="text-[10px] text-gray-600 mt-1"><?= $card['sub'] ?></p>
    </div>
    <?php endforeach; ?>
</div>

<div class="grid lg:grid-cols-3 gap-6 mb-6">
    <!-- Revenue Chart -->
    <div class="lg:col-span-2 glass-card rounded-xl border border-white/5 p-5">
        <h3 class="text-sm font-semibold text-white mb-4">Revenue & Orders (30 Days)</h3>
        <canvas id="revenue-chart" height="250"></canvas>
    </div>

    <!-- Top Users -->
    <div class="glass-card rounded-xl border border-white/5 p-5">
        <h3 class="text-sm font-semibold text-white mb-4">Top Users</h3>
        <div class="space-y-3">
            <?php foreach ($topUsers as $i => $user): ?>
            <div class="flex items-center gap-3">
                <span class="w-6 h-6 rounded-full bg-indigo-500/20 flex items-center justify-center text-xs text-indigo-400 font-bold"><?= $i + 1 ?></span>
                <div class="flex-1 min-w-0">
                    <p class="text-sm text-gray-300 truncate"><?= e($user['username']) ?></p>
                    <p class="text-xs text-gray-500"><?= e($user['email']) ?></p>
                </div>
                <span class="text-xs font-medium text-green-400"><?= format_money((float)$user['spent']) ?></span>
            </div>
            <?php endforeach; ?>
            <?php if (empty($topUsers)): ?>
            <p class="text-sm text-gray-500 text-center">No users yet.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Recent Orders -->
<div class="glass-card rounded-xl border border-white/5 overflow-hidden">
    <div class="flex items-center justify-between px-5 py-4 border-b border-white/5">
        <h3 class="text-sm font-semibold text-white">Recent Orders</h3>
        <a href="/admin/orders" class="text-xs text-indigo-400 hover:text-indigo-300">View All →</a>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-white/[0.02]">
                <tr>
                    <th class="px-4 py-2.5 text-left text-xs text-gray-500 font-medium">Order ID</th>
                    <th class="px-4 py-2.5 text-left text-xs text-gray-500 font-medium">User</th>
                    <th class="px-4 py-2.5 text-left text-xs text-gray-500 font-medium">Service</th>
                    <th class="px-4 py-2.5 text-center text-xs text-gray-500 font-medium">Charge</th>
                    <th class="px-4 py-2.5 text-center text-xs text-gray-500 font-medium">Status</th>
                    <th class="px-4 py-2.5 text-right text-xs text-gray-500 font-medium">Date</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/5">
                <?php foreach ($recentOrders as $order): ?>
                <tr class="hover:bg-white/[0.02]">
                    <td class="px-4 py-2.5 text-xs font-mono text-gray-400"><?= e(substr($order['order_id'], 0, 15)) ?></td>
                    <td class="px-4 py-2.5 text-xs text-gray-300"><?= e($order['username']) ?></td>
                    <td class="px-4 py-2.5 text-xs text-gray-300 truncate max-w-[200px]"><?= e($order['service_name']) ?></td>
                    <td class="px-4 py-2.5 text-center text-xs font-medium text-white"><?= format_money((float)$order['charge']) ?></td>
                    <td class="px-4 py-2.5 text-center"><?= get_status_badge($order['status']) ?></td>
                    <td class="px-4 py-2.5 text-right text-xs text-gray-500"><?= time_ago($order['created_at']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const chartData = <?= json_encode($chartData) ?>;
    if (chartData.length && typeof Chart !== 'undefined') {
        const ctx = document.getElementById('revenue-chart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: chartData.map(d => d.date),
                datasets: [
                    {
                        label: 'Revenue ($)',
                        data: chartData.map(d => parseFloat(d.revenue || 0)),
                        borderColor: '#6366f1',
                        backgroundColor: 'rgba(99,102,241,0.1)',
                        fill: true,
                        tension: 0.4,
                        borderWidth: 2,
                        pointRadius: 0,
                    },
                    {
                        label: 'Orders',
                        data: chartData.map(d => parseInt(d.orders || 0)),
                        borderColor: '#22c55e',
                        backgroundColor: 'rgba(34,197,94,0.1)',
                        fill: true,
                        tension: 0.4,
                        borderWidth: 2,
                        pointRadius: 0,
                        yAxisID: 'y1',
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { labels: { color: '#94a3b8', font: { size: 11 } } } },
                scales: {
                    x: { ticks: { color: '#475569', font: { size: 10 } }, grid: { color: 'rgba(255,255,255,0.03)' } },
                    y: { ticks: { color: '#475569', font: { size: 10 } }, grid: { color: 'rgba(255,255,255,0.03)' } },
                    y1: { position: 'right', ticks: { color: '#475569', font: { size: 10 } }, grid: { display: false } }
                }
            }
        });
    }
});
</script>

<?php $content = ob_get_clean(); include BASE_PATH . '/templates/layouts/app.php'; ?>
