<?php ob_start(); ?>

<div class="min-h-screen flex items-center justify-center px-4 py-12">
    <div class="w-full max-w-md">
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-gradient-to-br from-indigo-500 to-purple-600 shadow-xl shadow-indigo-500/20 mb-4">
                <span class="text-2xl font-black text-white">P</span>
            </div>
            <h1 class="text-2xl font-bold text-white">Create Account</h1>
            <p class="text-gray-500 mt-1">Start growing your social media today</p>
        </div>

        <div class="glass-card rounded-2xl border border-white/10 p-8">
            <form id="register-form" onsubmit="return handleRegister(event)">
                <?= csrf_field() ?>
                <div class="space-y-5">
                    <div>
                        <label class="block text-sm font-medium text-gray-400 mb-2">Username</label>
                        <div class="relative">
                            <i class="fas fa-user absolute left-4 top-1/2 -translate-y-1/2 text-gray-500"></i>
                            <input type="text" name="username" required minlength="3" maxlength="50" pattern="[a-zA-Z0-9_]+"
                                   class="w-full pl-11 pr-4 py-3 rounded-xl bg-white/5 border border-white/10 text-white placeholder-gray-500 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-all outline-none"
                                   placeholder="Choose a username">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-400 mb-2">Email Address</label>
                        <div class="relative">
                            <i class="fas fa-envelope absolute left-4 top-1/2 -translate-y-1/2 text-gray-500"></i>
                            <input type="email" name="email" required
                                   class="w-full pl-11 pr-4 py-3 rounded-xl bg-white/5 border border-white/10 text-white placeholder-gray-500 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-all outline-none"
                                   placeholder="you@example.com">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-400 mb-2">Password</label>
                        <div class="relative">
                            <i class="fas fa-lock absolute left-4 top-1/2 -translate-y-1/2 text-gray-500"></i>
                            <input type="password" name="password" required minlength="8"
                                   class="w-full pl-11 pr-12 py-3 rounded-xl bg-white/5 border border-white/10 text-white placeholder-gray-500 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-all outline-none"
                                   placeholder="Min. 8 characters">
                            <button type="button" onclick="togglePassword(this)" class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-300">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-400 mb-2">Confirm Password</label>
                        <div class="relative">
                            <i class="fas fa-lock absolute left-4 top-1/2 -translate-y-1/2 text-gray-500"></i>
                            <input type="password" name="confirm_password" required minlength="8"
                                   class="w-full pl-11 pr-4 py-3 rounded-xl bg-white/5 border border-white/10 text-white placeholder-gray-500 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-all outline-none"
                                   placeholder="Confirm your password">
                        </div>
                    </div>

                    <div id="register-error" class="hidden p-3 rounded-lg bg-red-500/10 border border-red-500/20 text-red-400 text-sm"></div>

                    <button type="submit" id="register-btn"
                            class="w-full py-3 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-semibold shadow-lg shadow-indigo-500/20 hover:shadow-indigo-500/40 transition-all duration-300 flex items-center justify-center gap-2">
                        <span>Create Account</span>
                        <i class="fas fa-arrow-right text-sm"></i>
                    </button>
                </div>
            </form>
        </div>

        <p class="text-center text-gray-500 mt-6 text-sm">
            Already have an account? <a href="/login" class="text-indigo-400 hover:text-indigo-300 font-medium">Sign in</a>
        </p>
    </div>
</div>

<script>
function handleRegister(e) {
    e.preventDefault();
    const form = document.getElementById('register-form');
    const btn = document.getElementById('register-btn');
    const errorDiv = document.getElementById('register-error');

    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating account...';
    errorDiv.classList.add('hidden');

    fetch('/register', {
        method: 'POST',
        body: new FormData(form),
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            window.location.href = data.redirect;
        } else {
            errorDiv.textContent = data.error;
            errorDiv.classList.remove('hidden');
            btn.disabled = false;
            btn.innerHTML = '<span>Create Account</span><i class="fas fa-arrow-right text-sm"></i>';
        }
    })
    .catch(() => {
        errorDiv.textContent = 'Network error. Please try again.';
        errorDiv.classList.remove('hidden');
        btn.disabled = false;
        btn.innerHTML = '<span>Create Account</span><i class="fas fa-arrow-right text-sm"></i>';
    });

    return false;
}
</script>

<?php $content = ob_get_clean(); include BASE_PATH . '/templates/layouts/auth.php'; ?>
