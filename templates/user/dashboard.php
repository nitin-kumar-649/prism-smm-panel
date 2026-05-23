<?php ob_start(); ?>

<!-- Stats Cards -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
    <?php
    $cards = [
        ['icon' => 'fas fa-shopping-bag', 'label' => 'Total Orders', 'value' => number_format($stats['total_orders']), 'color' => 'indigo', 'gradient' => 'from-indigo-500 to-indigo-600'],
        ['icon' => 'fas fa-clock', 'label' => 'Pending', 'value' => number_format($stats['pending_orders']), 'color' => 'yellow', 'gradient' => 'from-yellow-500 to-orange-500'],
        ['icon' => 'fas fa-check-circle', 'label' => 'Completed', 'value' => number_format($stats['completed_orders']), 'color' => 'green', 'gradient' => 'from-green-500 to-emerald-600'],
        ['icon' => 'fas fa-dollar-sign', 'label' => 'Total Spent', 'value' => format_money($stats['total_spent']), 'color' => 'purple', 'gradient' => 'from-purple-500 to-pink-500'],
    ];
    foreach ($cards as $card):
    ?>
    <div class="glass-card rounded-xl border border-white/5 p-5 hover:border-<?= $card['color'] ?>-500/20 transition-all duration-300 group">
        <div class="flex items-center justify-between mb-3">
            <div class="w-10 h-10 rounded-lg bg-gradient-to-br <?= $card['gradient'] ?> bg-opacity-20 flex items-center justify-center opacity-80 group-hover:opacity-100 transition-opacity">
                <i class="<?= $card['icon'] ?> text-white text-sm"></i>
            </div>
        </div>
        <p class="text-2xl font-bold text-white"><?= $card['value'] ?></p>
        <p class="text-xs text-gray-500 mt-1"><?= $card['label'] ?></p>
    </div>
    <?php endforeach; ?>
</div>

<div class="grid lg:grid-cols-2 gap-6">
    <!-- Recent Orders -->
    <div class="glass-card rounded-xl border border-white/5 overflow-hidden">
        <div class="flex items-center justify-between px-5 py-4 border-b border-white/5">
            <h3 class="text-sm font-semibold text-white">Recent Orders</h3>
            <a href="/user/orders" class="text-xs text-indigo-400 hover:text-indigo-300">View All →</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-white/[0.02]">
                    <tr>
                        <th class="px-4 py-2.5 text-left text-xs text-gray-500 font-medium">Order ID</th>
                        <th class="px-4 py-2.5 text-left text-xs text-gray-500 font-medium">Service</th>
                        <th class="px-4 py-2.5 text-center text-xs text-gray-500 font-medium">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    <?php if (empty($recentOrders)): ?>
                    <tr><td colspan="3" class="px-4 py-8 text-center text-gray-500">No orders yet. <a href="/user/new-order" class="text-indigo-400">Place your first order!</a></td></tr>
                    <?php else: foreach ($recentOrders as $order): ?>
                    <tr class="hover:bg-white/[0.02]">
                        <td class="px-4 py-2.5 text-xs text-gray-400 font-mono"><?= e(substr($order['order_id'], 0, 15)) ?></td>
                        <td class="px-4 py-2.5 text-xs text-gray-300 truncate max-w-[150px]"><?= e($order['service_name']) ?></td>
                        <td class="px-4 py-2.5 text-center"><?= get_status_badge($order['status']) ?></td>
                    </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Recent Transactions -->
    <div class="glass-card rounded-xl border border-white/5 overflow-hidden">
        <div class="flex items-center justify-between px-5 py-4 border-b border-white/5">
            <h3 class="text-sm font-semibold text-white">Recent Transactions</h3>
            <a href="/user/wallet" class="text-xs text-indigo-400 hover:text-indigo-300">View All →</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-white/[0.02]">
                    <tr>
                        <th class="px-4 py-2.5 text-left text-xs text-gray-500 font-medium">Type</th>
                        <th class="px-4 py-2.5 text-right text-xs text-gray-500 font-medium">Amount</th>
                        <th class="px-4 py-2.5 text-right text-xs text-gray-500 font-medium">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    <?php if (empty($recentTransactions)): ?>
                    <tr><td colspan="3" class="px-4 py-8 text-center text-gray-500">No transactions yet.</td></tr>
                    <?php else: foreach ($recentTransactions as $txn): ?>
                    <tr class="hover:bg-white/[0.02]">
                        <td class="px-4 py-2.5">
                            <span class="text-xs <?= $txn['amount'] > 0 ? 'text-green-400' : 'text-red-400' ?>"><?= ucfirst(str_replace('_', ' ', $txn['type'])) ?></span>
                        </td>
                        <td class="px-4 py-2.5 text-right text-xs font-medium <?= $txn['amount'] > 0 ? 'text-green-400' : 'text-red-400' ?>">
                            <?= $txn['amount'] > 0 ? '+' : '' ?><?= format_money((float) $txn['amount']) ?>
                        </td>
                        <td class="px-4 py-2.5 text-right text-xs text-gray-500"><?= time_ago($txn['created_at']) ?></td>
                    </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="mt-6 grid grid-cols-2 md:grid-cols-4 gap-4">
    <a href="/user/new-order" class="glass-card rounded-xl border border-white/5 p-4 text-center hover:border-indigo-500/30 transition-all group">
        <i class="fas fa-cart-plus text-2xl text-indigo-400 group-hover:scale-110 transition-transform mb-2"></i>
        <p class="text-sm font-medium text-gray-300">New Order</p>
    </a>
    <a href="/user/wallet" class="glass-card rounded-xl border border-white/5 p-4 text-center hover:border-green-500/30 transition-all group">
        <i class="fas fa-plus-circle text-2xl text-green-400 group-hover:scale-110 transition-transform mb-2"></i>
        <p class="text-sm font-medium text-gray-300">Add Funds</p>
    </a>
    <a href="/user/tickets" class="glass-card rounded-xl border border-white/5 p-4 text-center hover:border-purple-500/30 transition-all group">
        <i class="fas fa-headset text-2xl text-purple-400 group-hover:scale-110 transition-transform mb-2"></i>
        <p class="text-sm font-medium text-gray-300">Support</p>
    </a>
    <a href="/user/api" class="glass-card rounded-xl border border-white/5 p-4 text-center hover:border-yellow-500/30 transition-all group">
        <i class="fas fa-code text-2xl text-yellow-400 group-hover:scale-110 transition-transform mb-2"></i>
        <p class="text-sm font-medium text-gray-300">API Docs</p>
    </a>
</div>

<?php $content = ob_get_clean(); include BASE_PATH . '/templates/layouts/app.php'; ?>
