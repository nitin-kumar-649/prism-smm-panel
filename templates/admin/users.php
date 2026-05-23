<?php ob_start(); ?>

<div class="flex items-center justify-between mb-6">
    <h3 class="text-lg font-semibold text-white">Users</h3>
    <form method="get" action="/admin/users">
        <input type="text" name="search" value="<?= e($search) ?>" placeholder="Search users..."
               class="px-4 py-2 rounded-xl bg-white/5 border border-white/10 text-white text-sm placeholder-gray-500 focus:border-indigo-500 outline-none w-48">
    </form>
</div>

<div class="glass-card rounded-xl border border-white/5 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-white/[0.03]">
                <tr>
                    <th class="px-4 py-3 text-left text-xs text-gray-500">ID</th>
                    <th class="px-4 py-3 text-left text-xs text-gray-500">Username</th>
                    <th class="px-4 py-3 text-left text-xs text-gray-500 hidden md:table-cell">Email</th>
                    <th class="px-4 py-3 text-center text-xs text-gray-500">Balance</th>
                    <th class="px-4 py-3 text-center text-xs text-gray-500">Spent</th>
                    <th class="px-4 py-3 text-center text-xs text-gray-500">Status</th>
                    <th class="px-4 py-3 text-center text-xs text-gray-500">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/5">
                <?php if (empty($users['data'])): ?>
                <tr><td colspan="7" class="px-4 py-12 text-center text-gray-500">No users found.</td></tr>
                <?php else: foreach ($users['data'] as $user): ?>
                <tr class="hover:bg-white/[0.02]">
                    <td class="px-4 py-3 text-xs text-gray-500"><?= $user['id'] ?></td>
                    <td class="px-4 py-3 text-xs text-gray-300 font-medium"><?= e($user['username']) ?></td>
                    <td class="px-4 py-3 text-xs text-gray-400 hidden md:table-cell"><?= e($user['email']) ?></td>
                    <td class="px-4 py-3 text-center text-xs font-medium text-indigo-400"><?= format_money((float)$user['balance']) ?></td>
                    <td class="px-4 py-3 text-center text-xs text-gray-400"><?= format_money((float)$user['spent']) ?></td>
                    <td class="px-4 py-3 text-center"><?= get_status_badge($user['status']) ?></td>
                    <td class="px-4 py-3 text-center">
                        <div class="flex items-center justify-center gap-2">
                            <select onchange="updateUserStatus(<?= $user['id'] ?>, this.value)" class="px-2 py-1 rounded-lg bg-white/5 border border-white/10 text-white text-xs outline-none">
                                <?php foreach (['active','inactive','banned'] as $s): ?>
                                <option value="<?= $s ?>" <?= $user['status'] === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <button onclick="showFundsModal(<?= $user['id'] ?>, '<?= e($user['username']) ?>')" class="px-2 py-1 rounded bg-green-500/10 text-green-400 text-xs hover:bg-green-500/20">
                                <i class="fas fa-dollar-sign"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php if (($users['total_pages'] ?? 0) > 1): ?>
<div class="flex justify-center mt-6"><?= get_pagination_html($users['current_page'], $users['total_pages'], '/admin/users') ?></div>
<?php endif; ?>

<!-- Add/Deduct Funds Modal -->
<div id="modal-funds" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm">
    <div class="glass-card rounded-2xl border border-white/10 p-6 w-full max-w-sm mx-4">
        <h3 class="text-lg font-semibold text-white mb-1">Manage Funds</h3>
        <p class="text-sm text-gray-500 mb-4" id="funds-username"></p>
        <form onsubmit="return handleFunds(event)">
            <?= csrf_field() ?>
            <input type="hidden" id="funds-user-id">
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
    });
}

function showFundsModal(id, username) {
    document.getElementById('funds-user-id').value = id;
    document.getElementById('funds-username').textContent = 'User: ' + username;
    document.getElementById('modal-funds').classList.remove('hidden');
}

function handleFunds(e) {
    e.preventDefault();
    const id = document.getElementById('funds-user-id').value;
    const fd = new FormData(e.target);
    fetch('/admin/users/' + id + '/funds', { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' } })
    .then(r => r.json())
    .then(data => {
        showToast(data.message || data.error, data.success ? 'success' : 'error');
        if (data.success) setTimeout(() => location.reload(), 800);
    });
    return false;
}
</script>

<?php $content = ob_get_clean(); include BASE_PATH . '/templates/layouts/app.php'; ?>
