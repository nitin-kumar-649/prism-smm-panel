<?php ob_start(); ?>

<div class="flex flex-wrap items-center gap-3 mb-6">
    <a href="/admin/orders" class="px-3 py-1.5 rounded-lg text-sm <?= empty($status_filter) ? 'bg-indigo-600 text-white' : 'bg-white/5 text-gray-400 hover:text-white' ?>">All</a>
    <?php foreach (['pending','processing','in_progress','completed','partial','cancelled','refunded'] as $s): ?>
    <a href="/admin/orders?status=<?= $s ?>" class="px-3 py-1.5 rounded-lg text-sm <?= $status_filter === $s ? 'bg-indigo-600 text-white' : 'bg-white/5 text-gray-400 hover:text-white' ?>"><?= ucfirst(str_replace('_',' ',$s)) ?></a>
    <?php endforeach; ?>
    <form class="ml-auto" method="get" action="/admin/orders">
        <input type="text" name="search" value="<?= e($search) ?>" placeholder="Search orders..."
               class="px-4 py-2 rounded-xl bg-white/5 border border-white/10 text-white text-sm placeholder-gray-500 focus:border-indigo-500 outline-none w-48">
    </form>
</div>

<div class="glass-card rounded-xl border border-white/5 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-white/[0.03]">
                <tr>
                    <th class="px-4 py-3 text-left text-xs text-gray-500 font-medium">ID</th>
                    <th class="px-4 py-3 text-left text-xs text-gray-500 font-medium">User</th>
                    <th class="px-4 py-3 text-left text-xs text-gray-500 font-medium">Service</th>
                    <th class="px-4 py-3 text-center text-xs text-gray-500 font-medium">Qty</th>
                    <th class="px-4 py-3 text-center text-xs text-gray-500 font-medium">Charge</th>
                    <th class="px-4 py-3 text-center text-xs text-gray-500 font-medium">Status</th>
                    <th class="px-4 py-3 text-center text-xs text-gray-500 font-medium">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/5">
                <?php if (empty($orders['data'])): ?>
                <tr><td colspan="7" class="px-4 py-12 text-center text-gray-500">No orders found.</td></tr>
                <?php else: foreach ($orders['data'] as $order): ?>
                <tr class="hover:bg-white/[0.02]">
                    <td class="px-4 py-3 text-xs font-mono text-gray-400"><?= e(substr($order['order_id'],0,15)) ?></td>
                    <td class="px-4 py-3 text-xs text-gray-300"><?= e($order['username']) ?></td>
                    <td class="px-4 py-3 text-xs text-gray-300 truncate max-w-[200px]"><?= e($order['service_name']) ?></td>
                    <td class="px-4 py-3 text-center text-xs"><?= number_format($order['quantity']) ?></td>
                    <td class="px-4 py-3 text-center text-xs font-medium text-white"><?= format_money((float)$order['charge']) ?></td>
                    <td class="px-4 py-3 text-center"><?= get_status_badge($order['status']) ?></td>
                    <td class="px-4 py-3 text-center">
                        <select onchange="updateOrderStatus(<?= $order['id'] ?>, this.value)" class="px-2 py-1 rounded-lg bg-white/5 border border-white/10 text-white text-xs outline-none">
                            <?php foreach (['pending','processing','in_progress','completed','partial','cancelled','refunded','failed'] as $s): ?>
                            <option value="<?= $s ?>" <?= $order['status'] === $s ? 'selected' : '' ?>><?= ucfirst(str_replace('_',' ',$s)) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php if (($orders['total_pages'] ?? 0) > 1): ?>
<div class="flex justify-center mt-6"><?= get_pagination_html($orders['current_page'], $orders['total_pages'], '/admin/orders') ?></div>
<?php endif; ?>

<script>
function updateOrderStatus(id, status) {
    ajaxPost('/admin/orders/' + id + '/status', { status: status }, (data) => {
        showToast(data.message || data.error, data.success ? 'success' : 'error');
    });
}
</script>

<?php $content = ob_get_clean(); include BASE_PATH . '/templates/layouts/app.php'; ?>
