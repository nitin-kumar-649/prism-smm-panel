<?php ob_start(); ?>

<div class="flex items-center justify-between mb-6">
    <h3 class="text-lg font-semibold text-white">Services & Categories</h3>
    <div class="flex gap-2">
        <button onclick="document.getElementById('modal-category').classList.remove('hidden')" class="px-4 py-2 rounded-xl bg-white/5 text-gray-300 text-sm hover:bg-white/10">
            <i class="fas fa-folder-plus mr-1"></i> Add Category
        </button>
        <button onclick="document.getElementById('modal-service').classList.remove('hidden')" class="px-4 py-2 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 text-white text-sm">
            <i class="fas fa-plus mr-1"></i> Add Service
        </button>
    </div>
</div>

<!-- Categories -->
<div class="flex flex-wrap gap-2 mb-6">
    <?php foreach ($categories as $cat): ?>
    <div class="flex items-center gap-2 px-3 py-1.5 rounded-lg bg-white/5 border border-white/10">
        <span class="text-sm text-gray-300"><?= e($cat['name']) ?></span>
        <span class="text-[10px] text-gray-500">(<?= $cat['service_count'] ?>)</span>
        <button onclick="if(confirm('Delete category?')) ajaxPost('/admin/categories/<?= $cat['id'] ?>/delete',{},(d)=>{if(d.success)location.reload()})"
                class="text-gray-600 hover:text-red-400 text-xs"><i class="fas fa-times"></i></button>
    </div>
    <?php endforeach; ?>
</div>

<!-- Services Table -->
<div class="glass-card rounded-xl border border-white/5 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-white/[0.03]">
                <tr>
                    <th class="px-4 py-3 text-left text-xs text-gray-500">ID</th>
                    <th class="px-4 py-3 text-left text-xs text-gray-500">Name</th>
                    <th class="px-4 py-3 text-left text-xs text-gray-500">Category</th>
                    <th class="px-4 py-3 text-center text-xs text-gray-500">Rate/1K</th>
                    <th class="px-4 py-3 text-center text-xs text-gray-500">Min-Max</th>
                    <th class="px-4 py-3 text-center text-xs text-gray-500">Provider</th>
                    <th class="px-4 py-3 text-center text-xs text-gray-500">Status</th>
                    <th class="px-4 py-3 text-center text-xs text-gray-500">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/5">
                <?php foreach ($services as $s): ?>
                <tr class="hover:bg-white/[0.02]">
                    <td class="px-4 py-3 text-xs text-gray-500"><?= $s['id'] ?></td>
                    <td class="px-4 py-3 text-xs text-gray-300 max-w-[200px] truncate"><?= e($s['name']) ?></td>
                    <td class="px-4 py-3 text-xs text-gray-400"><?= e($s['category_name']) ?></td>
                    <td class="px-4 py-3 text-center text-xs font-medium text-indigo-400"><?= format_money((float)$s['price_per_1000']) ?></td>
                    <td class="px-4 py-3 text-center text-xs text-gray-400"><?= number_format($s['min_quantity']) ?>-<?= number_format($s['max_quantity']) ?></td>
                    <td class="px-4 py-3 text-center text-xs text-gray-500"><?= e($s['provider_name'] ?? '-') ?></td>
                    <td class="px-4 py-3 text-center"><?= get_status_badge($s['status']) ?></td>
                    <td class="px-4 py-3 text-center">
                        <button onclick="if(confirm('Delete service?')) ajaxPost('/admin/services/<?= $s['id'] ?>/delete',{},(d)=>{if(d.success)location.reload()})"
                                class="text-red-400/60 hover:text-red-400 text-xs"><i class="fas fa-trash"></i></button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add Category Modal -->
<div id="modal-category" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm">
    <div class="glass-card rounded-2xl border border-white/10 p-6 w-full max-w-md mx-4">
        <h3 class="text-lg font-semibold text-white mb-4">Add Category</h3>
        <form onsubmit="return submitForm(event, '/admin/categories')">
            <?= csrf_field() ?>
            <div class="space-y-4">
                <input type="text" name="name" required placeholder="Category name" class="w-full px-4 py-3 rounded-xl bg-white/5 border border-white/10 text-white placeholder-gray-500 focus:border-indigo-500 outline-none">
                <input type="number" name="sort_order" value="0" placeholder="Sort order" class="w-full px-4 py-3 rounded-xl bg-white/5 border border-white/10 text-white placeholder-gray-500 focus:border-indigo-500 outline-none">
                <div class="flex gap-3">
                    <button type="button" onclick="this.closest('[id^=modal]').classList.add('hidden')" class="flex-1 py-3 rounded-xl bg-white/5 text-gray-400 hover:bg-white/10">Cancel</button>
                    <button type="submit" class="flex-1 py-3 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-semibold">Create</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Add Service Modal -->
<div id="modal-service" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm overflow-y-auto">
    <div class="glass-card rounded-2xl border border-white/10 p-6 w-full max-w-lg mx-4 my-8">
        <h3 class="text-lg font-semibold text-white mb-4">Add Service</h3>
        <form onsubmit="return submitForm(event, '/admin/services')">
            <?= csrf_field() ?>
            <div class="space-y-3">
                <select name="category_id" required class="w-full px-4 py-3 rounded-xl bg-white/5 border border-white/10 text-white focus:border-indigo-500 outline-none">
                    <option value="">Select Category</option>
                    <?php foreach ($categories as $cat): ?><option value="<?= $cat['id'] ?>"><?= e($cat['name']) ?></option><?php endforeach; ?>
                </select>
                <input type="text" name="name" required placeholder="Service name" class="w-full px-4 py-3 rounded-xl bg-white/5 border border-white/10 text-white placeholder-gray-500 focus:border-indigo-500 outline-none">
                <textarea name="description" rows="2" placeholder="Description" class="w-full px-4 py-3 rounded-xl bg-white/5 border border-white/10 text-white placeholder-gray-500 focus:border-indigo-500 outline-none resize-none"></textarea>
                <div class="grid grid-cols-3 gap-3">
                    <input type="number" name="price_per_1000" step="0.0001" required placeholder="Rate/1K" class="px-3 py-3 rounded-xl bg-white/5 border border-white/10 text-white placeholder-gray-500 focus:border-indigo-500 outline-none">
                    <input type="number" name="min_quantity" value="100" placeholder="Min" class="px-3 py-3 rounded-xl bg-white/5 border border-white/10 text-white placeholder-gray-500 focus:border-indigo-500 outline-none">
                    <input type="number" name="max_quantity" value="10000" placeholder="Max" class="px-3 py-3 rounded-xl bg-white/5 border border-white/10 text-white placeholder-gray-500 focus:border-indigo-500 outline-none">
                </div>
                <select name="provider_id" class="w-full px-4 py-3 rounded-xl bg-white/5 border border-white/10 text-white focus:border-indigo-500 outline-none">
                    <option value="">No Provider (Manual)</option>
                    <?php foreach ($providers as $p): ?><option value="<?= $p['id'] ?>"><?= e($p['name']) ?></option><?php endforeach; ?>
                </select>
                <input type="text" name="provider_service_id" placeholder="Provider Service ID" class="w-full px-4 py-3 rounded-xl bg-white/5 border border-white/10 text-white placeholder-gray-500 focus:border-indigo-500 outline-none">
                <div class="flex flex-wrap gap-4">
                    <label class="flex items-center gap-2 text-sm text-gray-400"><input type="checkbox" name="drip_feed" value="1" class="rounded bg-white/5 border-white/20 text-indigo-600"> Drip-Feed</label>
                    <label class="flex items-center gap-2 text-sm text-gray-400"><input type="checkbox" name="refill" value="1" class="rounded bg-white/5 border-white/20 text-indigo-600"> Refill</label>
                    <label class="flex items-center gap-2 text-sm text-gray-400"><input type="checkbox" name="cancel" value="1" class="rounded bg-white/5 border-white/20 text-indigo-600"> Cancel</label>
                </div>
                <div class="flex gap-3">
                    <button type="button" onclick="this.closest('[id^=modal]').classList.add('hidden')" class="flex-1 py-3 rounded-xl bg-white/5 text-gray-400 hover:bg-white/10">Cancel</button>
                    <button type="submit" class="flex-1 py-3 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-semibold">Create</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
function submitForm(e, url) {
    e.preventDefault();
    const form = e.target;
    fetch(url, { method: 'POST', body: new FormData(form), headers: { 'X-Requested-With': 'XMLHttpRequest' } })
    .then(r => r.json())
    .then(data => {
        showToast(data.message || data.error, data.success ? 'success' : 'error');
        if (data.success) setTimeout(() => location.reload(), 800);
    });
    return false;
}
</script>

<?php $content = ob_get_clean(); include BASE_PATH . '/templates/layouts/app.php'; ?>
