<?php ob_start(); ?>

<div class="max-w-2xl mx-auto">
    <a href="/user/orders" class="inline-flex items-center gap-2 text-indigo-400 hover:text-indigo-300 text-sm mb-4">
        <i class="fas fa-arrow-left"></i> Back to Orders
    </a>

    <div class="glass-card rounded-xl border border-white/5 p-6">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-semibold text-white">Order Details</h3>
            <?= get_status_badge($order['status']) ?>
        </div>

        <div class="grid grid-cols-2 gap-6 text-sm">
            <div><span class="text-gray-500 block mb-1">Order ID</span><span class="text-gray-300 font-mono"><?= e($order['order_id']) ?></span></div>
            <div><span class="text-gray-500 block mb-1">Service</span><span class="text-gray-300"><?= e($service['name'] ?? 'N/A') ?></span></div>
            <div><span class="text-gray-500 block mb-1">Link</span><a href="<?= e($order['link']) ?>" target="_blank" class="text-indigo-400 hover:text-indigo-300 break-all"><?= e(substr($order['link'], 0, 50)) ?></a></div>
            <div><span class="text-gray-500 block mb-1">Quantity</span><span class="text-gray-300"><?= number_format($order['quantity']) ?></span></div>
            <div><span class="text-gray-500 block mb-1">Charge</span><span class="text-indigo-400 font-semibold"><?= format_money((float)$order['charge']) ?></span></div>
            <div><span class="text-gray-500 block mb-1">Start Count</span><span class="text-gray-300"><?= number_format($order['start_count']) ?></span></div>
            <div><span class="text-gray-500 block mb-1">Remains</span><span class="text-gray-300"><?= number_format($order['remains']) ?></span></div>
            <div><span class="text-gray-500 block mb-1">Created</span><span class="text-gray-300"><?= date('M j, Y g:i A', strtotime($order['created_at'])) ?></span></div>
            <?php if ($order['drip_feed']): ?>
            <div><span class="text-gray-500 block mb-1">Drip Feed</span><span class="text-blue-400">Every <?= $order['drip_feed_interval'] ?>min, <?= $order['runs'] ?> runs</span></div>
            <?php endif; ?>
        </div>

        <?php if ($order['status'] === 'completed' && ($service['refill'] ?? false)): ?>
        <div class="mt-6 pt-6 border-t border-white/5">
            <button onclick="refillOrder(<?= $order['id'] ?>)" class="px-5 py-2.5 rounded-xl bg-green-500/10 text-green-400 hover:bg-green-500/20 text-sm font-medium transition-colors">
                <i class="fas fa-sync mr-2"></i> Request Refill
            </button>
        </div>
        <?php endif; ?>

        <?php if (in_array($order['status'], ['pending', 'processing']) && ($service['cancel'] ?? false)): ?>
        <div class="mt-6 pt-6 border-t border-white/5">
            <button onclick="cancelOrder(<?= $order['id'] ?>)" class="px-5 py-2.5 rounded-xl bg-red-500/10 text-red-400 hover:bg-red-500/20 text-sm font-medium transition-colors">
                <i class="fas fa-times mr-2"></i> Request Cancel
            </button>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
function refillOrder(id) {
    if (!confirm('Request refill?')) return;
    ajaxPost('/user/orders/' + id + '/refill', {}, (data) => {
        showToast(data.message || data.error, data.success ? 'success' : 'error');
    });
}

function cancelOrder(id) {
    if (!confirm('Request cancellation?')) return;
    ajaxPost('/user/orders/' + id + '/cancel', {}, (data) => {
        showToast(data.message || data.error, data.success ? 'success' : 'error');
    });
}
</script>

<?php $content = ob_get_clean(); include BASE_PATH . '/templates/layouts/app.php'; ?>
