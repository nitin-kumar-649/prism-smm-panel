<?php ob_start(); ?>

<div class="flex flex-wrap items-center gap-3 mb-6">
    <a href="/admin/payments" class="px-3 py-1.5 rounded-lg text-sm <?= empty($status_filter) ? 'bg-indigo-600 text-white' : 'bg-white/5 text-gray-400' ?>">All</a>
    <?php foreach (['pending','completed','failed','cancelled'] as $s): ?>
    <a href="/admin/payments?status=<?= $s ?>" class="px-3 py-1.5 rounded-lg text-sm <?= $status_filter === $s ? 'bg-indigo-600 text-white' : 'bg-white/5 text-gray-400' ?>"><?= ucfirst($s) ?></a>
    <?php endforeach; ?>
</div>

<div class="glass-card rounded-xl border border-white/5 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-white/[0.03]">
                <tr>
                    <th class="px-4 py-3 text-left text-xs text-gray-500">ID</th>
                    <th class="px-4 py-3 text-left text-xs text-gray-500">User</th>
                    <th class="px-4 py-3 text-center text-xs text-gray-500">Amount</th>
                    <th class="px-4 py-3 text-center text-xs text-gray-500">Gateway</th>
                    <th class="px-4 py-3 text-center text-xs text-gray-500">Status</th>
                    <th class="px-4 py-3 text-right text-xs text-gray-500">Date</th>
                    <th class="px-4 py-3 text-center text-xs text-gray-500">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/5">
                <?php if (empty($payments['data'])): ?>
                <tr><td colspan="7" class="px-4 py-12 text-center text-gray-500">No payment requests.</td></tr>
                <?php else: foreach ($payments['data'] as $p): ?>
                <tr class="hover:bg-white/[0.02]">
                    <td class="px-4 py-3 text-xs text-gray-500"><?= $p['id'] ?></td>
                    <td class="px-4 py-3 text-xs text-gray-300"><?= e($p['username']) ?></td>
                    <td class="px-4 py-3 text-center text-xs font-medium text-green-400"><?= format_money((float)$p['amount']) ?></td>
                    <td class="px-4 py-3 text-center text-xs text-gray-400"><?= ucfirst($p['gateway']) ?></td>
                    <td class="px-4 py-3 text-center"><?= get_status_badge($p['status']) ?></td>
                    <td class="px-4 py-3 text-right text-xs text-gray-500"><?= time_ago($p['created_at']) ?></td>
                    <td class="px-4 py-3 text-center">
                        <?php if ($p['status'] === 'pending'): ?>
                        <div class="flex items-center justify-center gap-1">
                            <button onclick="ajaxPost('/admin/payments/<?= $p['id'] ?>/approve',{},(d)=>{showToast(d.message||d.error,d.success?'success':'error');if(d.success)setTimeout(()=>location.reload(),800)})"
                                    class="px-2 py-1 rounded bg-green-500/10 text-green-400 text-xs hover:bg-green-500/20"><i class="fas fa-check"></i></button>
                            <button onclick="ajaxPost('/admin/payments/<?= $p['id'] ?>/reject',{},(d)=>{showToast(d.message||d.error,d.success?'success':'error');if(d.success)setTimeout(()=>location.reload(),800)})"
                                    class="px-2 py-1 rounded bg-red-500/10 text-red-400 text-xs hover:bg-red-500/20"><i class="fas fa-times"></i></button>
                        </div>
                        <?php else: ?>
                        <span class="text-xs text-gray-600">-</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php $content = ob_get_clean(); include BASE_PATH . '/templates/layouts/app.php'; ?>
