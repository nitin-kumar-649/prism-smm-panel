/**
 * Prism SMM Panel - Main JavaScript
 * AJAX helpers, notifications, theme toggle, sidebar
 */

// CSRF Token
function getCsrfToken() {
    const meta = document.querySelector('input[name="csrf_token"]');
    return meta ? meta.value : '';
}

// AJAX POST Helper
function ajaxPost(url, data, callback) {
    const fd = new FormData();
    fd.append('csrf_token', getCsrfToken());
    for (const key in data) {
        fd.append(key, data[key]);
    }

    fetch(url, {
        method: 'POST',
        body: fd,
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(callback)
    .catch(err => {
        console.error('Request failed:', err);
        showToast('Network error. Please try again.', 'error');
    });
}

// Toast Notifications
function showToast(message, type = 'info') {
    const container = document.getElementById('toast-container');
    if (!container) return;

    const colors = {
        success: 'border-green-500/30 bg-green-500/10 text-green-400',
        error: 'border-red-500/30 bg-red-500/10 text-red-400',
        warning: 'border-yellow-500/30 bg-yellow-500/10 text-yellow-400',
        info: 'border-indigo-500/30 bg-indigo-500/10 text-indigo-400',
    };

    const icons = {
        success: 'fas fa-check-circle',
        error: 'fas fa-exclamation-circle',
        warning: 'fas fa-exclamation-triangle',
        info: 'fas fa-info-circle',
    };

    const toast = document.createElement('div');
    toast.className = `toast flex items-center gap-3 px-4 py-3 rounded-xl border backdrop-blur-lg shadow-2xl ${colors[type] || colors.info}`;
    toast.innerHTML = `
        <i class="${icons[type] || icons.info}"></i>
        <span class="text-sm flex-1">${escapeHtml(message)}</span>
        <button onclick="this.parentElement.remove()" class="opacity-60 hover:opacity-100 ml-2">&times;</button>
    `;

    container.appendChild(toast);

    setTimeout(() => {
        toast.style.transition = 'all 0.3s ease-out';
        toast.style.opacity = '0';
        toast.style.transform = 'translateX(100%)';
        setTimeout(() => toast.remove(), 300);
    }, 4000);
}

function escapeHtml(str) {
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
}

// Sidebar Toggle
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebar-overlay');
    if (!sidebar) return;

    sidebar.classList.toggle('-translate-x-full');
    if (overlay) overlay.classList.toggle('hidden');
}

// Theme Toggle
function toggleTheme() {
    const html = document.documentElement;
    const icon = document.getElementById('theme-icon');

    html.classList.toggle('dark');
    const isDark = html.classList.contains('dark');
    localStorage.setItem('theme', isDark ? 'dark' : 'light');

    if (icon) {
        icon.className = isDark ? 'fas fa-moon text-lg' : 'fas fa-sun text-lg';
    }
}

// Initialize theme
(function() {
    const saved = localStorage.getItem('theme');
    if (saved === 'light') {
        document.documentElement.classList.remove('dark');
        const icon = document.getElementById('theme-icon');
        if (icon) icon.className = 'fas fa-sun text-lg';
    }
})();

// Password Toggle
function togglePassword(btn) {
    const input = btn.parentElement.querySelector('input');
    const icon = btn.querySelector('i');
    if (input.type === 'password') {
        input.type = 'text';
        icon.className = 'fas fa-eye-slash';
    } else {
        input.type = 'password';
        icon.className = 'fas fa-eye';
    }
}

// Notifications
let notificationInterval = null;

function toggleNotifications() {
    const panel = document.getElementById('notification-panel');
    if (panel) {
        panel.classList.toggle('hidden');
        if (!panel.classList.contains('hidden')) {
            loadNotifications();
        }
    }
}

function loadNotifications() {
    fetch('/user/notifications', { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
    .then(r => r.json())
    .then(data => {
        const badge = document.getElementById('notification-badge');
        const list = document.getElementById('notification-list');

        if (badge) {
            if (data.count > 0) {
                badge.textContent = data.count > 9 ? '9+' : data.count;
                badge.classList.remove('hidden');
            } else {
                badge.classList.add('hidden');
            }
        }

        if (list && data.notifications) {
            if (data.notifications.length === 0) {
                list.innerHTML = '<p class="p-4 text-sm text-gray-500 text-center">No new notifications</p>';
            } else {
                list.innerHTML = data.notifications.map(n => `
                    <div class="px-4 py-3 hover:bg-white/5 cursor-pointer border-b border-white/5 last:border-0" onclick="markNotificationRead(${n.id})">
                        <div class="flex items-start gap-2">
                            <i class="fas fa-${n.type === 'success' ? 'check-circle text-green-400' : n.type === 'error' ? 'exclamation-circle text-red-400' : n.type === 'warning' ? 'exclamation-triangle text-yellow-400' : 'info-circle text-indigo-400'} mt-0.5"></i>
                            <div>
                                <p class="text-xs font-medium text-gray-300">${escapeHtml(n.title)}</p>
                                <p class="text-[11px] text-gray-500">${escapeHtml(n.message)}</p>
                            </div>
                        </div>
                    </div>
                `).join('');
            }
        }
    })
    .catch(() => {});
}

function markNotificationRead(id) {
    ajaxPost('/user/notifications/' + id + '/read', {}, () => loadNotifications());
}

function markAllRead() {
    ajaxPost('/user/notifications/read-all', {}, () => loadNotifications());
}

// Auto-load notifications every 30 seconds
if (document.getElementById('notification-badge')) {
    loadNotifications();
    notificationInterval = setInterval(loadNotifications, 30000);
}

// Close dropdowns on outside click
document.addEventListener('click', function(e) {
    // Close notification panel
    const notifDropdown = document.getElementById('notification-dropdown');
    const notifPanel = document.getElementById('notification-panel');
    if (notifDropdown && notifPanel && !notifDropdown.contains(e.target)) {
        notifPanel.classList.add('hidden');
    }

    // Close user dropdown
    const userMenu = document.getElementById('user-menu');
    const userDropdown = document.getElementById('user-dropdown');
    if (userMenu && userDropdown && !userMenu.contains(e.target)) {
        userDropdown.classList.add('hidden');
    }
});

// Auto-dismiss flash messages
document.addEventListener('DOMContentLoaded', function() {
    const flash = document.getElementById('flash-message');
    if (flash) {
        setTimeout(() => {
            flash.style.transition = 'all 0.5s ease-out';
            flash.style.opacity = '0';
            flash.style.transform = 'translateY(-10px)';
            setTimeout(() => flash.remove(), 500);
        }, 5000);
    }
});

// Smooth page transitions
document.addEventListener('DOMContentLoaded', function() {
    const main = document.querySelector('main');
    if (main) {
        main.style.animation = 'fadeIn 0.3s ease-out';
    }
});
