<?php ob_start(); ?>

<div class="max-w-2xl mx-auto">
    <a href="/admin/users" class="inline-flex items-center gap-2 text-indigo-400 hover:text-indigo-300 text-sm mb-4">
        <i class="fas fa-arrow-left"></i> Back to Users
    </a>

    <div class="glass-card rounded-xl border border-white/5 p-6">
        <div class="flex items-center gap-4 mb-6">
            <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center">
                <span class="text-xl font-bold text-white"><?= strtoupper(substr($user['username'], 0, 1)) ?></span>
            </div>
            <div>
                <h3 class="text-lg font-semibold text-white"><?= e($user['username']) ?></h3>
                <p class="text-sm text-gray-500"><?= e($user['email']) ?></p>
            </div>
            <?= get_status_badge($user['status']) ?>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-3 gap-4 text-sm">
            <div class="p-3 rounded-lg bg-white/5"><span class="text-gray-500 block text-xs">Balance</span><span class="text-indigo-400 font-semibold"><?= format_money((float)$user['balance']) ?></span></div>
            <div class="p-3 rounded-lg bg-white/5"><span class="text-gray-500 block text-xs">Total Spent</span><span class="text-green-400 font-semibold"><?= format_money((float)$user['spent']) ?></span></div>
            <div class="p-3 rounded-lg bg-white/5"><span class="text-gray-500 block text-xs">Member Since</span><span class="text-gray-300"><?= date('M j, Y', strtotime($user['created_at'])) ?></span></div>
            <div class="p-3 rounded-lg bg-white/5"><span class="text-gray-500 block text-xs">Last Login</span><span class="text-gray-300"><?= $user['last_login'] ? time_ago($user['last_login']) : 'Never' ?></span></div>
            <div class="p-3 rounded-lg bg-white/5"><span class="text-gray-500 block text-xs">Last IP</span><span class="text-gray-300 font-mono text-xs"><?= e($user['last_ip'] ?? 'N/A') ?></span></div>
            <div class="p-3 rounded-lg bg-white/5"><span class="text-gray-500 block text-xs">Phone</span><span class="text-gray-300"><?= e($user['phone'] ?? 'N/A') ?></span></div>
        </div>

        <div class="mt-6 pt-6 border-t border-white/5 flex flex-wrap gap-3">
            <select onchange="updateUserStatus(<?= $user['id'] ?>, this.value)" class="px-4 py-2 rounded-xl bg-white/5 border border-white/10 text-white text-sm outline-none">
                <?php foreach (['active','inactive','banned'] as $s): ?>
                <option value="<?= $s ?>" <?= $user['status'] === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                <?php endforeach; ?>
            </select>
            <button onclick="showFundsModal(<?= $user['id'] ?>, '<?= e($user['username']) ?>')" class="px-4 py-2 rounded-xl bg-green-500/10 text-green-400 text-sm hover:bg-green-500/20">
                <i class="fas fa-dollar-sign mr-1"></i> Add/Deduct Funds
            </button>
        </div>
    </div>
</div>

<!-- Funds Modal -->
<div id="modal-funds" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm">
    <div class="glass-card rounded-2xl border border-white/10 p-6 w-full max-w-sm mx-4">
        <h3 class="text-lg font-semibold text-white mb-4">Manage Funds</h3>
        <form onsubmit="return handleFunds(event)">
            <?= csrf_field() ?>
            <div class="space-y-4">
                <input type="number" name="amount" step="0.01" required placeholder="Amount (negative to deduct)"
                       class="w-full px-4 py-3 rounded-xl bg-white/5 border border-white/10 text-white placeholder-gray-500 focus:border-indigo-500 outline-none">
                <div class="flex gap-3">
                    <button type="button" onclick="this.closest('[id^=modal]').classList.add('hidden')" class="flex-1 py-3 rounded-xl bg-white/5 text-gray-400">Cancel</button>
                    <button type="submit" class="flex-1 py-3 rounded-xl bg-gradient-to-r from-green-600 to-emerald-600 text-white font-semibold">Submit</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
function updateUserStatus(id, status) {
    ajaxPost('/admin/users/' + id + '/status', { status: status }, (data) => {
        showToast(data.message || data.error, data.success ? 'success' : 'error');
        if (data.success) setTimeout(() => location.reload(), 800);
    });
}

function showFundsModal() {
    document.getElementById('modal-funds').classList.remove('hidden');
}

function handleFunds(e) {
    e.preventDefault();
    const fd = new FormData(e.target);
    fetch('/admin/users/<?= $user['id'] ?>/funds', { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' } })
    .then(r => r.json())
    .then(data => {
        showToast(data.message || data.error, data.success ? 'success' : 'error');
        if (data.success) setTimeout(() => location.reload(), 800);
    });
    return false;
}
</script>

<?php $content = ob_get_clean(); include BASE_PATH . '/templates/layouts/app.php'; ?>
