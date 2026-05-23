<?php ob_start(); ?>

<div class="max-w-3xl mx-auto">
    <div class="glass-card rounded-xl border border-white/5 p-6">
        <h3 class="text-lg font-semibold text-white mb-6">General Settings</h3>
        <form id="settings-form" onsubmit="return handleSettings(event)">
            <?= csrf_field() ?>
            <div class="space-y-5">
                <div>
                    <label class="block text-sm font-medium text-gray-400 mb-2">Site Name</label>
                    <input type="text" name="site_name" value="<?= e($settings['site_name'] ?? '') ?>"
                           class="w-full px-4 py-3 rounded-xl bg-white/5 border border-white/10 text-white focus:border-indigo-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-400 mb-2">Site Description</label>
                    <textarea name="site_description" rows="2"
                              class="w-full px-4 py-3 rounded-xl bg-white/5 border border-white/10 text-white focus:border-indigo-500 outline-none resize-none"><?= e($settings['site_description'] ?? '') ?></textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-400 mb-2">SEO Keywords</label>
                    <input type="text" name="site_keywords" value="<?= e($settings['site_keywords'] ?? '') ?>"
                           class="w-full px-4 py-3 rounded-xl bg-white/5 border border-white/10 text-white focus:border-indigo-500 outline-none"
                           placeholder="keyword1, keyword2, keyword3">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-400 mb-2">Support Email</label>
                    <input type="email" name="support_email" value="<?= e($settings['support_email'] ?? '') ?>"
                           class="w-full px-4 py-3 rounded-xl bg-white/5 border border-white/10 text-white focus:border-indigo-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-400 mb-2">Announcement (shown on dashboard)</label>
                    <textarea name="announcement" rows="2"
                              class="w-full px-4 py-3 rounded-xl bg-white/5 border border-white/10 text-white focus:border-indigo-500 outline-none resize-none"><?= e($settings['announcement'] ?? '') ?></textarea>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-400 mb-2">Min Deposit ($)</label>
                        <input type="number" name="min_deposit" value="<?= e($settings['min_deposit'] ?? '5') ?>"
                               class="w-full px-4 py-3 rounded-xl bg-white/5 border border-white/10 text-white focus:border-indigo-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-400 mb-2">Max Deposit ($)</label>
                        <input type="number" name="max_deposit" value="<?= e($settings['max_deposit'] ?? '10000') ?>"
                               class="w-full px-4 py-3 rounded-xl bg-white/5 border border-white/10 text-white focus:border-indigo-500 outline-none">
                    </div>
                </div>

                <div class="flex flex-wrap gap-6">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="hidden" name="registration_enabled" value="0">
                        <input type="checkbox" name="registration_enabled" value="1" <?= ($settings['registration_enabled'] ?? '1') === '1' ? 'checked' : '' ?>
                               class="w-4 h-4 rounded bg-white/5 border-white/20 text-indigo-600 focus:ring-indigo-500">
                        <span class="text-sm text-gray-400">Allow Registration</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="hidden" name="maintenance_mode" value="0">
                        <input type="checkbox" name="maintenance_mode" value="1" <?= ($settings['maintenance_mode'] ?? '0') === '1' ? 'checked' : '' ?>
                               class="w-4 h-4 rounded bg-white/5 border-white/20 text-red-600 focus:ring-red-500">
                        <span class="text-sm text-gray-400">Maintenance Mode</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="hidden" name="api_enabled" value="0">
                        <input type="checkbox" name="api_enabled" value="1" <?= ($settings['api_enabled'] ?? '1') === '1' ? 'checked' : '' ?>
                               class="w-4 h-4 rounded bg-white/5 border-white/20 text-indigo-600 focus:ring-indigo-500">
                        <span class="text-sm text-gray-400">Enable API</span>
                    </label>
                </div>

                <button type="submit" class="w-full py-3 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-semibold shadow-lg shadow-indigo-500/20 transition-all">
                    <i class="fas fa-save mr-2"></i> Save Settings
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function handleSettings(e) {
    e.preventDefault();
    fetch('/admin/settings', {
        method: 'POST',
        body: new FormData(document.getElementById('settings-form')),
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(data => showToast(data.message || data.error, data.success ? 'success' : 'error'));
    return false;
}
</script>

<?php $content = ob_get_clean(); include BASE_PATH . '/templates/layouts/app.php'; ?>
