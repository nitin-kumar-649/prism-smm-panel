<?php ob_start(); ?>

<!-- Filters -->
<div class="flex flex-wrap items-center gap-3 mb-6">
    <a href="/user/orders" class="px-3 py-1.5 rounded-lg text-sm <?= empty($status_filter) ? 'bg-indigo-600 text-white' : 'bg-white/5 text-gray-400 hover:text-white' ?> transition-colors">All</a>
    <?php foreach (['pending', 'processing', 'in_progress', 'completed', 'partial', 'cancelled'] as $s): ?>
    <a href="/user/orders?status=<?= $s ?>" class="px-3 py-1.5 rounded-lg text-sm <?= $status_filter === $s ? 'bg-indigo-600 text-white' : 'bg-white/5 text-gray-400 hover:text-white' ?> transition-colors">
        <?= ucfirst(str_replace('_', ' ', $s)) ?>
    </a>
    <?php endforeach; ?>
</div>

<!-- Orders Table -->
<div class="glass-card rounded-xl border border-white/5 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-white/[0.03]">
                <tr>
                    <th class="px-4 py-3 text-left text-xs text-gray-500 font-medium">Order ID</th>
                    <th class="px-4 py-3 text-left text-xs text-gray-500 font-medium">Service</th>
                    <th class="px-4 py-3 text-left text-xs text-gray-500 font-medium hidden md:table-cell">Link</th>
                    <th class="px-4 py-3 text-center text-xs text-gray-500 font-medium">Qty</th>
                    <th class="px-4 py-3 text-center text-xs text-gray-500 font-medium">Charge</th>
                    <th class="px-4 py-3 text-center text-xs text-gray-500 font-medium">Status</th>
                    <th class="px-4 py-3 text-center text-xs text-gray-500 font-medium">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/5">
                <?php if (empty($orders['data'])): ?>
                <tr><td colspan="7" class="px-4 py-12 text-center text-gray-500">No orders found.</td></tr>
                <?php else: foreach ($orders['data'] as $order): ?>
                <tr class="hover:bg-white/[0.02] transition-colors">
                    <td class="px-4 py-3 text-xs font-mono text-gray-400"><?= e(substr($order['order_id'], 0, 15)) ?></td>
                    <td class="px-4 py-3 text-xs text-gray-300 max-w-[200px] truncate"><?= e($order['service_name']) ?></td>
                    <td class="px-4 py-3 text-xs text-gray-500 max-w-[150px] truncate hidden md:table-cell">
                        <a href="<?= e($order['link']) ?>" target="_blank" class="hover:text-indigo-400"><?= e(substr($order['link'], 0, 40)) ?></a>
                    </td>
                    <td class="px-4 py-3 text-center text-xs text-gray-400"><?= number_format($order['quantity']) ?></td>
                    <td class="px-4 py-3 text-center text-xs font-medium text-white"><?= format_money((float) $order['charge']) ?></td>
                    <td class="px-4 py-3 text-center"><?= get_status_badge($order['status']) ?></td>
                    <td class="px-4 py-3 text-center">
                        <div class="flex items-center justify-center gap-1">
                            <?php if ($order['status'] === 'completed'): ?>
                                <button onclick="refillOrder(<?= $order['id'] ?>)" class="px-2 py-1 rounded text-[10px] bg-green-500/10 text-green-400 hover:bg-green-500/20" title="Refill">
                                    <i class="fas fa-sync"></i>
                                </button>
                            <?php endif; ?>
                            <?php if (in_array($order['status'], ['pending', 'processing'])): ?>
                                <button onclick="cancelOrder(<?= $order['id'] ?>)" class="px-2 py-1 rounded text-[10px] bg-red-500/10 text-red-400 hover:bg-red-500/20" title="Cancel">
                                    <i class="fas fa-times"></i>
                                </button>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Pagination -->
<?php if (($orders['total_pages'] ?? 0) > 1): ?>
<div class="flex justify-center mt-6">
    <?= get_pagination_html($orders['current_page'], $orders['total_pages'], '/user/orders' . ($status_filter ? "?status={$status_filter}&" : '')) ?>
</div>
<?php endif; ?>

<script>
function refillOrder(id) {
    if (!confirm('Request refill for this order?')) return;
    ajaxPost('/user/orders/' + id + '/refill', {}, (data) => {
        showToast(data.message || data.error, data.success ? 'success' : 'error');
        if (data.success) setTimeout(() => location.reload(), 1000);
    });
}

function cancelOrder(id) {
    if (!confirm('Request cancellation for this order?')) return;
    ajaxPost('/user/orders/' + id + '/cancel', {}, (data) => {
        showToast(data.message || data.error, data.success ? 'success' : 'error');
        if (data.success) setTimeout(() => location.reload(), 1000);
    });
}
</script>

<?php $content = ob_get_clean(); include BASE_PATH . '/templates/layouts/app.php'; ?>
