<?php ob_start(); ?>

<div class="min-h-screen flex items-center justify-center px-4 py-12">
    <div class="w-full max-w-md">
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-gradient-to-br from-indigo-500 to-purple-600 shadow-xl shadow-indigo-500/20 mb-4">
                <span class="text-2xl font-black text-white">P</span>
            </div>
            <h1 class="text-2xl font-bold text-white">Reset Password</h1>
            <p class="text-gray-500 mt-1">Enter your email to receive an OTP</p>
        </div>

        <div class="glass-card rounded-2xl border border-white/10 p-8">
            <!-- Step 1: Enter Email -->
            <div id="step-email">
                <form onsubmit="return sendOtp(event)">
                    <?= csrf_field() ?>
                    <div class="space-y-5">
                        <div>
                            <label class="block text-sm font-medium text-gray-400 mb-2">Email Address</label>
                            <div class="relative">
                                <i class="fas fa-envelope absolute left-4 top-1/2 -translate-y-1/2 text-gray-500"></i>
                                <input type="email" name="email" id="reset-email" required
                                       class="w-full pl-11 pr-4 py-3 rounded-xl bg-white/5 border border-white/10 text-white placeholder-gray-500 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-all outline-none"
                                       placeholder="you@example.com">
                            </div>
                        </div>
                        <div id="otp-error" class="hidden p-3 rounded-lg bg-red-500/10 border border-red-500/20 text-red-400 text-sm"></div>
                        <button type="submit" id="send-otp-btn"
                                class="w-full py-3 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-semibold shadow-lg shadow-indigo-500/20 hover:shadow-indigo-500/40 transition-all duration-300">
                            Send OTP
                        </button>
                    </div>
                </form>
            </div>

            <!-- Step 2: Enter OTP -->
            <div id="step-otp" class="hidden">
                <form onsubmit="return verifyOtp(event)">
                    <?= csrf_field() ?>
                    <input type="hidden" name="user_id" id="otp-user-id">
                    <div class="space-y-5">
                        <div>
                            <label class="block text-sm font-medium text-gray-400 mb-2">Enter OTP</label>
                            <input type="text" name="otp" id="otp-input" required maxlength="6"
                                   class="w-full px-4 py-3 rounded-xl bg-white/5 border border-white/10 text-white text-center text-2xl tracking-[0.5em] font-mono focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-all outline-none"
                                   placeholder="------">
                        </div>
                        <div id="otp-verify-error" class="hidden p-3 rounded-lg bg-red-500/10 border border-red-500/20 text-red-400 text-sm"></div>
                        <button type="submit" class="w-full py-3 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-semibold shadow-lg shadow-indigo-500/20 transition-all duration-300">
                            Verify OTP
                        </button>
                    </div>
                </form>
            </div>

            <!-- Step 3: New Password -->
            <div id="step-password" class="hidden">
                <form onsubmit="return resetPassword(event)">
                    <?= csrf_field() ?>
                    <div class="space-y-5">
                        <div>
                            <label class="block text-sm font-medium text-gray-400 mb-2">New Password</label>
                            <input type="password" name="password" required minlength="8"
                                   class="w-full px-4 py-3 rounded-xl bg-white/5 border border-white/10 text-white placeholder-gray-500 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-all outline-none"
                                   placeholder="Min. 8 characters">
                        </div>
                        <div id="reset-error" class="hidden p-3 rounded-lg bg-red-500/10 border border-red-500/20 text-red-400 text-sm"></div>
                        <button type="submit" class="w-full py-3 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-semibold shadow-lg shadow-indigo-500/20 transition-all duration-300">
                            Reset Password
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <p class="text-center text-gray-500 mt-6 text-sm">
            <a href="/login" class="text-indigo-400 hover:text-indigo-300 font-medium"><i class="fas fa-arrow-left mr-1"></i> Back to login</a>
        </p>
    </div>
</div>

<script>
function sendOtp(e) {
    e.preventDefault();
    const errorDiv = document.getElementById('otp-error');
    errorDiv.classList.add('hidden');

    const fd = new FormData();
    fd.append('email', document.getElementById('reset-email').value);
    fd.append('csrf_token', '<?= csrf_token() ?>');

    fetch('/forgot-password/send-otp', { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' } })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            document.getElementById('otp-user-id').value = data.user_id;
            document.getElementById('step-email').classList.add('hidden');
            document.getElementById('step-otp').classList.remove('hidden');
            if (data.debug_otp) showToast('Debug OTP: ' + data.debug_otp, 'info');
        } else {
            errorDiv.textContent = data.error;
            errorDiv.classList.remove('hidden');
        }
    });
    return false;
}

function verifyOtp(e) {
    e.preventDefault();
    const errorDiv = document.getElementById('otp-verify-error');
    errorDiv.classList.add('hidden');

    const fd = new FormData();
    fd.append('user_id', document.getElementById('otp-user-id').value);
    fd.append('otp', document.getElementById('otp-input').value);
    fd.append('csrf_token', '<?= csrf_token() ?>');

    fetch('/forgot-password/verify-otp', { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' } })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            document.getElementById('step-otp').classList.add('hidden');
            document.getElementById('step-password').classList.remove('hidden');
        } else {
            errorDiv.textContent = data.error;
            errorDiv.classList.remove('hidden');
        }
    });
    return false;
}

function resetPassword(e) {
    e.preventDefault();
    const errorDiv = document.getElementById('reset-error');
    errorDiv.classList.add('hidden');

    const fd = new FormData(e.target);

    fetch('/forgot-password/reset', { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' } })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, 'success');
            setTimeout(() => window.location.href = data.redirect, 1500);
        } else {
            errorDiv.textContent = data.error;
            errorDiv.classList.remove('hidden');
        }
    });
    return false;
}
</script>

<?php $content = ob_get_clean(); include BASE_PATH . '/templates/layouts/auth.php'; ?>
