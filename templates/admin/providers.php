<?php ob_start(); ?>

<div class="flex items-center justify-between mb-6">
    <h3 class="text-lg font-semibold text-white">API Providers</h3>
    <button onclick="document.getElementById('modal-provider').classList.remove('hidden')" class="px-4 py-2 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 text-white text-sm">
        <i class="fas fa-plus mr-1"></i> Add Provider
    </button>
</div>

<div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4">
    <?php foreach ($providers as $p): ?>
    <div class="glass-card rounded-xl border border-white/5 p-5">
        <div class="flex items-center justify-between mb-3">
            <h4 class="text-sm font-semibold text-white"><?= e($p['name']) ?></h4>
            <?= get_status_badge($p['status']) ?>
        </div>
        <p class="text-xs text-gray-500 truncate mb-2"><?= e($p['api_url']) ?></p>
        <p class="text-sm text-gray-400 mb-3">Balance: <span class="text-indigo-400 font-semibold"><?= format_money((float)$p['balance']) ?></span></p>
        <div class="flex gap-2">
            <button onclick="checkProviderBalance(<?= $p['id'] ?>)" class="flex-1 py-2 rounded-lg bg-indigo-500/10 text-indigo-400 text-xs hover:bg-indigo-500/20">
                <i class="fas fa-sync-alt mr-1"></i> Check Balance
            </button>
            <button onclick="if(confirm('Delete?')) ajaxPost('/admin/providers/<?= $p['id'] ?>/delete',{},(d)=>{if(d.success)location.reload()})"
                    class="py-2 px-3 rounded-lg bg-red-500/10 text-red-400 text-xs hover:bg-red-500/20">
                <i class="fas fa-trash"></i>
            </button>
        </div>
    </div>
    <?php endforeach; ?>
    <?php if (empty($providers)): ?>
    <div class="col-span-full p-12 text-center text-gray-500">No providers configured. Add one to start processing orders automatically.</div>
    <?php endif; ?>
</div>

<!-- Add Provider Modal -->
<div id="modal-provider" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm">
    <div class="glass-card rounded-2xl border border-white/10 p-6 w-full max-w-md mx-4">
        <h3 class="text-lg font-semibold text-white mb-4">Add Provider</h3>
        <form onsubmit="return submitProviderForm(event)">
            <?= csrf_field() ?>
            <div class="space-y-4">
                <input type="text" name="name" required placeholder="Provider name" class="w-full px-4 py-3 rounded-xl bg-white/5 border border-white/10 text-white placeholder-gray-500 focus:border-indigo-500 outline-none">
                <input type="url" name="api_url" required placeholder="API URL (https://...)" class="w-full px-4 py-3 rounded-xl bg-white/5 border border-white/10 text-white placeholder-gray-500 focus:border-indigo-500 outline-none">
                <input type="text" name="api_key" required placeholder="API Key" class="w-full px-4 py-3 rounded-xl bg-white/5 border border-white/10 text-white placeholder-gray-500 focus:border-indigo-500 outline-none">
                <textarea name="description" rows="2" placeholder="Description (optional)" class="w-full px-4 py-3 rounded-xl bg-white/5 border border-white/10 text-white placeholder-gray-500 focus:border-indigo-500 outline-none resize-none"></textarea>
                <div class="flex gap-3">
                    <button type="button" onclick="this.closest('[id^=modal]').classList.add('hidden')" class="flex-1 py-3 rounded-xl bg-white/5 text-gray-400">Cancel</button>
                    <button type="submit" class="flex-1 py-3 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-semibold">Add</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
function submitProviderForm(e) {
    e.preventDefault();
    fetch('/admin/providers', { method: 'POST', body: new FormData(e.target), headers: { 'X-Requested-With': 'XMLHttpRequest' } })
    .then(r => r.json())
    .then(data => {
        showToast(data.message || data.error, data.success ? 'success' : 'error');
        if (data.success) setTimeout(() => location.reload(), 800);
    });
    return false;
}

function checkProviderBalance(id) {
    fetch('/admin/providers/' + id + '/balance', { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
    .then(r => r.json())
    .then(data => {
        if (data.success) showToast('Balance: $' + parseFloat(data.balance).toFixed(2), 'success');
        else showToast(data.error, 'error');
    });
}
</script>

<?php $content = ob_get_clean(); include BASE_PATH . '/templates/layouts/app.php'; ?>
