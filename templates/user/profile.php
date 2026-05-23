<?php ob_start(); ?>

<div class="max-w-2xl mx-auto space-y-6">
    <!-- Profile Info -->
    <div class="glass-card rounded-xl border border-white/5 p-6">
        <h3 class="text-lg font-semibold text-white mb-6">Profile Information</h3>
        <form id="profile-form" onsubmit="return handleProfile(event)">
            <?= csrf_field() ?>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-400 mb-2">Username</label>
                    <input type="text" name="username" value="<?= e($currentUser['username'] ?? '') ?>"
                           class="w-full px-4 py-3 rounded-xl bg-white/5 border border-white/10 text-white focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-all outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-400 mb-2">Email</label>
                    <input type="email" value="<?= e($currentUser['email'] ?? '') ?>" disabled
                           class="w-full px-4 py-3 rounded-xl bg-white/5 border border-white/10 text-gray-500 cursor-not-allowed outline-none">
                    <p class="text-xs text-gray-500 mt-1">Email cannot be changed.</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-400 mb-2">Phone</label>
                    <input type="tel" name="phone" value="<?= e($currentUser['phone'] ?? '') ?>"
                           class="w-full px-4 py-3 rounded-xl bg-white/5 border border-white/10 text-white placeholder-gray-500 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-all outline-none"
                           placeholder="+1234567890">
                </div>
                <button type="submit" class="px-6 py-3 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-semibold shadow-lg shadow-indigo-500/20 transition-all">
                    Save Changes
                </button>
            </div>
        </form>
    </div>

    <!-- Change Password -->
    <div class="glass-card rounded-xl border border-white/5 p-6">
        <h3 class="text-lg font-semibold text-white mb-6">Change Password</h3>
        <form id="password-form" onsubmit="return handlePassword(event)">
            <?= csrf_field() ?>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-400 mb-2">Current Password</label>
                    <input type="password" name="current_password" required
                           class="w-full px-4 py-3 rounded-xl bg-white/5 border border-white/10 text-white placeholder-gray-500 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-all outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-400 mb-2">New Password</label>
                    <input type="password" name="new_password" required minlength="8"
                           class="w-full px-4 py-3 rounded-xl bg-white/5 border border-white/10 text-white placeholder-gray-500 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-all outline-none"
                           placeholder="Min. 8 characters">
                </div>
                <div id="password-error" class="hidden p-3 rounded-lg bg-red-500/10 border border-red-500/20 text-red-400 text-sm"></div>
                <button type="submit" class="px-6 py-3 rounded-xl bg-gradient-to-r from-red-600 to-orange-600 text-white font-semibold shadow-lg shadow-red-500/20 transition-all">
                    Update Password
                </button>
            </div>
        </form>
    </div>

    <!-- Account Info -->
    <div class="glass-card rounded-xl border border-white/5 p-6">
        <h3 class="text-lg font-semibold text-white mb-4">Account Details</h3>
        <div class="grid grid-cols-2 gap-4 text-sm">
            <div><span class="text-gray-500">Member Since:</span><br><span class="text-gray-300"><?= date('M j, Y', strtotime($currentUser['created_at'] ?? 'now')) ?></span></div>
            <div><span class="text-gray-500">Total Spent:</span><br><span class="text-gray-300"><?= format_money((float)($currentUser['spent'] ?? 0)) ?></span></div>
            <div><span class="text-gray-500">Balance:</span><br><span class="text-indigo-400 font-semibold"><?= format_money((float)($currentUser['balance'] ?? 0)) ?></span></div>
            <div><span class="text-gray-500">Status:</span><br><?= get_status_badge($currentUser['status'] ?? 'active') ?></div>
        </div>
    </div>
</div>

<script>
function handleProfile(e) {
    e.preventDefault();
    fetch('/user/profile', {
        method: 'POST',
        body: new FormData(document.getElementById('profile-form')),
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(data => showToast(data.message || data.error, data.success ? 'success' : 'error'));
    return false;
}

function handlePassword(e) {
    e.preventDefault();
    const errorDiv = document.getElementById('password-error');
    errorDiv.classList.add('hidden');

    fetch('/user/profile/password', {
        method: 'POST',
        body: new FormData(document.getElementById('password-form')),
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, 'success');
            document.getElementById('password-form').reset();
        } else {
            errorDiv.textContent = data.error;
            errorDiv.classList.remove('hidden');
        }
    });
    return false;
}
</script>

<?php $content = ob_get_clean(); include BASE_PATH . '/templates/layouts/app.php'; ?>
