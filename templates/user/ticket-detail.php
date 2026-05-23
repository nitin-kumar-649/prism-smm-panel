<?php ob_start(); ?>

<div class="max-w-3xl mx-auto">
    <!-- Ticket Header -->
    <div class="glass-card rounded-xl border border-white/5 p-5 mb-6">
        <div class="flex items-center justify-between flex-wrap gap-3">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="text-xs font-mono text-gray-500">#<?= e($ticket['ticket_id']) ?></span>
                    <?= get_status_badge($ticket['status']) ?>
                </div>
                <h3 class="text-lg font-semibold text-white"><?= e($ticket['subject']) ?></h3>
            </div>
            <?php if ($ticket['status'] !== 'closed'): ?>
            <button onclick="closeTicket(<?= $ticket['id'] ?>)" class="px-4 py-2 rounded-lg bg-red-500/10 text-red-400 text-sm hover:bg-red-500/20 transition-colors">
                Close Ticket
            </button>
            <?php endif; ?>
        </div>
    </div>

    <!-- Messages -->
    <div class="space-y-4 mb-6">
        <?php foreach ($messages as $msg): ?>
        <div class="glass-card rounded-xl border border-white/5 p-5 <?= $msg['is_admin'] ? 'border-l-2 border-l-indigo-500' : '' ?>">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-8 h-8 rounded-lg <?= $msg['is_admin'] ? 'bg-indigo-500/20' : 'bg-white/10' ?> flex items-center justify-center">
                    <span class="text-xs font-bold <?= $msg['is_admin'] ? 'text-indigo-400' : 'text-gray-400' ?>"><?= strtoupper(substr($msg['username'], 0, 1)) ?></span>
                </div>
                <div>
                    <span class="text-sm font-medium <?= $msg['is_admin'] ? 'text-indigo-400' : 'text-gray-300' ?>"><?= e($msg['username']) ?></span>
                    <?php if ($msg['is_admin']): ?><span class="text-[10px] px-1.5 py-0.5 rounded bg-indigo-500/10 text-indigo-400 ml-1">Staff</span><?php endif; ?>
                    <span class="text-xs text-gray-500 ml-2"><?= time_ago($msg['created_at']) ?></span>
                </div>
            </div>
            <div class="text-sm text-gray-300 whitespace-pre-wrap"><?= e($msg['message']) ?></div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Reply Form -->
    <?php if ($ticket['status'] !== 'closed'): ?>
    <div class="glass-card rounded-xl border border-white/5 p-5">
        <form id="reply-form" onsubmit="return handleReply(event)">
            <?= csrf_field() ?>
            <textarea name="message" required rows="3"
                      class="w-full px-4 py-3 rounded-xl bg-white/5 border border-white/10 text-white placeholder-gray-500 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-all outline-none resize-none mb-3"
                      placeholder="Type your reply..."></textarea>
            <button type="submit" class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 text-white text-sm font-semibold shadow-lg shadow-indigo-500/20 transition-all">
                <i class="fas fa-reply mr-2"></i> Send Reply
            </button>
        </form>
    </div>
    <?php endif; ?>
</div>

<script>
function handleReply(e) {
    e.preventDefault();
    const form = document.getElementById('reply-form');
    fetch('/user/tickets/<?= $ticket['id'] ?>/reply', {
        method: 'POST',
        body: new FormData(form),
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) location.reload();
        else showToast(data.error, 'error');
    });
    return false;
}

function closeTicket(id) {
    if (!confirm('Close this ticket?')) return;
    ajaxPost('/user/tickets/' + id + '/close', {}, (data) => {
        if (data.success) location.reload();
    });
}
</script>

<?php $content = ob_get_clean(); include BASE_PATH . '/templates/layouts/app.php'; ?>
