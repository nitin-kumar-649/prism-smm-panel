<?php ob_start(); ?>

<div class="grid lg:grid-cols-3 gap-6">
    <!-- Create Ticket -->
    <div class="lg:col-span-1">
        <div class="glass-card rounded-xl border border-white/5 p-6">
            <h3 class="text-lg font-semibold text-white mb-4">New Ticket</h3>
            <form id="ticket-form" onsubmit="return handleCreateTicket(event)">
                <?= csrf_field() ?>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-400 mb-2">Subject</label>
                        <input type="text" name="subject" required maxlength="255"
                               class="w-full px-4 py-3 rounded-xl bg-white/5 border border-white/10 text-white placeholder-gray-500 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-all outline-none"
                               placeholder="Brief description">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-400 mb-2">Priority</label>
                        <select name="priority" class="w-full px-4 py-3 rounded-xl bg-white/5 border border-white/10 text-white focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-all outline-none">
                            <option value="low">Low</option>
                            <option value="medium" selected>Medium</option>
                            <option value="high">High</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-400 mb-2">Message</label>
                        <textarea name="message" required rows="4"
                                  class="w-full px-4 py-3 rounded-xl bg-white/5 border border-white/10 text-white placeholder-gray-500 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-all outline-none resize-none"
                                  placeholder="Describe your issue in detail..."></textarea>
                    </div>
                    <div id="ticket-error" class="hidden p-3 rounded-lg bg-red-500/10 border border-red-500/20 text-red-400 text-sm"></div>
                    <button type="submit" class="w-full py-3 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-semibold shadow-lg shadow-indigo-500/20 transition-all duration-300">
                        <i class="fas fa-paper-plane mr-2"></i> Submit Ticket
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Tickets List -->
    <div class="lg:col-span-2">
        <div class="glass-card rounded-xl border border-white/5 overflow-hidden">
            <div class="px-5 py-4 border-b border-white/5">
                <h3 class="text-sm font-semibold text-white">My Tickets</h3>
            </div>
            <div class="divide-y divide-white/5">
                <?php if (empty($tickets['data'])): ?>
                <div class="p-8 text-center text-gray-500">No tickets yet.</div>
                <?php else: foreach ($tickets['data'] as $ticket): ?>
                <a href="/user/tickets/<?= $ticket['id'] ?>" class="flex items-center justify-between px-5 py-4 hover:bg-white/[0.02] transition-colors">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="text-xs font-mono text-gray-500">#<?= e($ticket['ticket_id']) ?></span>
                            <?= get_status_badge($ticket['status']) ?>
                            <span class="text-[10px] px-1.5 py-0.5 rounded bg-<?= $ticket['priority'] === 'high' ? 'red' : ($ticket['priority'] === 'medium' ? 'yellow' : 'gray') ?>-500/10 text-<?= $ticket['priority'] === 'high' ? 'red' : ($ticket['priority'] === 'medium' ? 'yellow' : 'gray') ?>-400">
                                <?= ucfirst($ticket['priority']) ?>
                            </span>
                        </div>
                        <p class="text-sm text-gray-300 truncate"><?= e($ticket['subject']) ?></p>
                    </div>
                    <div class="text-xs text-gray-500 ml-4"><?= time_ago($ticket['updated_at']) ?></div>
                </a>
                <?php endforeach; endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
function handleCreateTicket(e) {
    e.preventDefault();
    const form = document.getElementById('ticket-form');
    const errorDiv = document.getElementById('ticket-error');
    errorDiv.classList.add('hidden');

    fetch('/user/tickets', {
        method: 'POST',
        body: new FormData(form),
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            errorDiv.textContent = data.error;
            errorDiv.classList.remove('hidden');
        }
    });
    return false;
}
</script>

<?php $content = ob_get_clean(); include BASE_PATH . '/templates/layouts/app.php'; ?>
