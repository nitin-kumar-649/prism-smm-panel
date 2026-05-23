<?php ob_start(); ?>

<div class="flex flex-wrap items-center gap-3 mb-6">
    <a href="/admin/tickets" class="px-3 py-1.5 rounded-lg text-sm <?= empty($status_filter) ? 'bg-indigo-600 text-white' : 'bg-white/5 text-gray-400' ?>">All</a>
    <?php foreach (['open','answered','closed'] as $s): ?>
    <a href="/admin/tickets?status=<?= $s ?>" class="px-3 py-1.5 rounded-lg text-sm <?= $status_filter === $s ? 'bg-indigo-600 text-white' : 'bg-white/5 text-gray-400' ?>"><?= ucfirst($s) ?></a>
    <?php endforeach; ?>
</div>

<div class="glass-card rounded-xl border border-white/5 overflow-hidden">
    <div class="divide-y divide-white/5">
        <?php if (empty($tickets['data'])): ?>
        <div class="p-12 text-center text-gray-500">No tickets.</div>
        <?php else: foreach ($tickets['data'] as $t): ?>
        <a href="/admin/tickets/<?= $t['id'] ?>" class="flex items-center justify-between px-5 py-4 hover:bg-white/[0.02] transition-colors block">
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 mb-1">
                    <span class="text-xs font-mono text-gray-500">#<?= e($t['ticket_id']) ?></span>
                    <?= get_status_badge($t['status']) ?>
                    <span class="text-xs text-gray-400">by <strong><?= e($t['username']) ?></strong></span>
                </div>
                <p class="text-sm text-gray-300 truncate"><?= e($t['subject']) ?></p>
            </div>
            <div class="text-xs text-gray-500 ml-4"><?= time_ago($t['updated_at']) ?></div>
        </a>
        <?php endforeach; endif; ?>
    </div>
</div>

<?php $content = ob_get_clean(); include BASE_PATH . '/templates/layouts/app.php'; ?>
