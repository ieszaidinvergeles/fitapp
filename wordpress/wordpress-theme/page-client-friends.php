<?php
require_once 'functions.php';
require_login();

// Handle AJAX Search
if (isset($_GET['ajax_search'])) {
    while (ob_get_level()) ob_end_clean();
    $q = $_GET['q'] ?? '';
    $resp = api_get('/friends/search?q=' . urlencode($q), auth: true);
    header('Content-Type: application/json');
    echo json_encode($resp);
    exit;
}

// Handle AJAX Toggle
if (isset($_POST['ajax_toggle'])) {
    while (ob_get_level()) ob_end_clean();
    $id = (int)($_POST['id'] ?? 0);
    $resp = api_post("/friends/{$id}/toggle", [], auth: true);
    header('Content-Type: application/json');
    echo json_encode($resp);
    exit;
}

$response = api_get('/friends', auth: true);
$friends = $response['result'] ?? [];

wp_app_page_start('My Network');
?>
    <div class="mb-10 flex flex-col md:flex-row md:items-end justify-between gap-6">
        <div>
            <p class="text-zinc-500 text-sm font-medium mt-1">Connect with other members and track your collective progress.</p>
        </div>
        <button id="open-search-modal" class="px-8 py-4 kinetic-gradient text-on-primary-container rounded-2xl text-[10px] font-black uppercase tracking-widest shadow-xl shadow-primary-container/20 hover:scale-[1.02] active:scale-95 transition-all flex items-center gap-2">
            <span class="material-symbols-outlined text-sm">person_add</span>
            Find Friends
        </button>
    </div>

    <?php if (empty($friends)): ?>
        <div class="flex flex-col items-center justify-center py-24 bg-surface-container/30 rounded-[3rem] border border-dashed border-outline-variant/20">
            <div class="w-20 h-20 rounded-full bg-zinc-800 flex items-center justify-center mb-6">
                <span class="material-symbols-outlined text-4xl text-zinc-600">group_off</span>
            </div>
            <h3 class="text-xl font-black uppercase tracking-tight text-white mb-2">Socially Distant</h3>
            <p class="text-zinc-500 text-sm max-w-xs text-center leading-relaxed">Your network is currently empty. Start adding friends to share stats and stay motivated.</p>
            <button onclick="document.getElementById('open-search-modal').click()" class="mt-8 px-10 py-4 bg-white/5 hover:bg-white/10 text-white rounded-full text-[10px] font-black uppercase tracking-widest border border-white/10 transition-all">
                Search Members
            </button>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            <?php foreach ($friends as $f): ?>
                <div class="bg-surface-container rounded-3xl border border-outline-variant/10 p-6 flex flex-col items-center text-center group hover:border-primary-container/30 transition-all shadow-xl shadow-black/20">
                    <div class="relative mb-4">
                        <div class="w-24 h-24 rounded-full overflow-hidden border-2 border-outline-variant/20 group-hover:border-primary-container/50 transition-colors shadow-lg">
                            <?php if (!empty($f['profile_photo_url'])): ?>
                                <img src="<?= esc_url($f['profile_photo_url']) ?>" class="w-full h-full object-cover">
                            <?php else: ?>
                                <div class="w-full h-full bg-zinc-800 flex items-center justify-center">
                                    <span class="material-symbols-outlined text-4xl text-zinc-600">person</span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <h4 class="font-headline text-xl font-black uppercase italic tracking-tight text-white mb-1"><?= h($f['full_name'] ?: $f['username']) ?></h4>
                    <p class="text-[10px] font-black uppercase tracking-widest text-primary-container mb-6">@<?= h($f['username']) ?></p>
                    
                    <div class="w-full grid grid-cols-2 gap-3">
                        <a href="?pagename=client-user-profile&id=<?= (int)$f['id'] ?>" class="px-4 py-3 bg-zinc-800 hover:bg-zinc-700 text-white rounded-xl text-[10px] font-black uppercase tracking-widest border border-white/5 transition-all">
                            Profile
                        </a>
                        <button onclick="toggleFriend(<?= (int)$f['id'] ?>, this)" class="px-4 py-3 bg-error/10 hover:bg-error/20 text-error rounded-xl text-[10px] font-black uppercase tracking-widest border border-error/20 transition-all">
                            Remove
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Search Modal -->
    <div id="search-modal" class="fixed inset-0 z-[100] hidden">
        <div class="absolute inset-0 bg-black/80 backdrop-blur-md"></div>
        <div class="absolute inset-0 flex items-center justify-center p-4">
            <div class="bg-surface-container border border-outline-variant/20 rounded-[2.5rem] w-full max-w-2xl max-h-[85vh] flex flex-col overflow-hidden shadow-2xl">
                <!-- Modal Header -->
                <div class="p-8 border-b border-outline-variant/10 flex items-center justify-between bg-surface-container-high/50">
                    <div>
                        <h3 class="font-headline text-3xl font-black uppercase italic tracking-tight text-white leading-none">Find Friends</h3>
                        <p class="text-[10px] font-black uppercase tracking-widest text-primary-container mt-2">Expand your network</p>
                    </div>
                    <button id="close-search-modal" class="w-12 h-12 rounded-2xl bg-zinc-800 text-zinc-500 hover:text-white flex items-center justify-center transition-all border border-white/5">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>

                <!-- Modal Search Bar -->
                <div class="p-8 pb-4">
                    <div class="relative">
                        <input type="text" id="modal-search-input" placeholder="Search by name or @username..." 
                               class="w-full bg-zinc-900 border border-outline-variant/20 rounded-2xl py-4 pl-12 pr-6 text-white placeholder-zinc-500 focus:border-primary-container/50 focus:ring-0 transition-all outline-none">
                        <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-zinc-500">search</span>
                    </div>
                </div>

                <!-- Modal Content -->
                <div id="modal-results" class="flex-1 overflow-y-auto p-8 pt-0 space-y-4">
                    <div class="flex justify-center py-10"><div class="w-8 h-8 border-4 border-primary-container border-t-transparent rounded-full animate-spin"></div></div>
                </div>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const modal = document.getElementById('search-modal');
        const openBtn = document.getElementById('open-search-modal');
        const closeBtn = document.getElementById('close-search-modal');
        const searchInput = document.getElementById('modal-search-input');
        const resultsContainer = document.getElementById('modal-results');

        const performSearch = async (q = '') => {
            resultsContainer.innerHTML = '<div class="flex justify-center py-10"><div class="w-8 h-8 border-4 border-primary-container border-t-transparent rounded-full animate-spin"></div></div>';
            
            try {
                const searchUrl = window.location.origin + window.location.pathname.replace(/\/$/, '') + '/?pagename=client-friends&ajax_search=1&q=' + encodeURIComponent(q);
                const resp = await fetch(searchUrl);
                const data = await resp.json();
                const users = data.result || [];

                if (users.length === 0) {
                    resultsContainer.innerHTML = `
                        <div class="text-center py-10">
                            <p class="text-zinc-500 text-sm mb-2">No members found matching "${q}"</p>
                            <p class="text-[10px] text-zinc-600 uppercase font-black tracking-widest">Try a different search term</p>
                        </div>`;
                    return;
                }

                resultsContainer.innerHTML = users.map(u => `
                    <div class="bg-zinc-900/50 rounded-2xl border border-outline-variant/10 p-4 flex items-center justify-between group hover:border-primary-container/20 transition-all">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-full overflow-hidden border border-outline-variant/20">
                                ${u.profile_photo_url 
                                    ? `<img src="${u.profile_photo_url}" class="w-full h-full object-cover">`
                                    : `<div class="w-full h-full bg-zinc-800 flex items-center justify-center"><span class="material-symbols-outlined text-xl text-zinc-600">person</span></div>`
                                }
                            </div>
                            <div>
                                <h4 class="font-headline font-black uppercase italic text-white leading-none mb-1 text-sm">${u.full_name || u.username}</h4>
                                <p class="text-[9px] font-bold text-primary-container uppercase tracking-widest">@${u.username}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <a href="?pagename=client-user-profile&id=${u.id}" class="p-2.5 rounded-lg bg-zinc-800 text-zinc-500 hover:text-white transition-all">
                                <span class="material-symbols-outlined text-lg">visibility</span>
                            </a>
                            <button onclick="toggleFriend(${u.id}, this)" class="px-5 py-2.5 rounded-lg text-[9px] font-black uppercase tracking-widest transition-all ${u.is_friend ? 'bg-error/10 text-error border border-error/20 hover:bg-error/20' : 'bg-primary-container text-black hover:scale-105'}">
                                ${u.is_friend ? 'Remove' : 'Add Friend'}
                            </button>
                        </div>
                    </div>
                `).join('');
            } catch (err) {
                resultsContainer.innerHTML = `<div class="text-center py-10"><p class="text-error text-xs uppercase font-black">Search Error: ${err.message}</p></div>`;
            }
        };

        const openModal = () => {
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
            performSearch();
            setTimeout(() => searchInput.focus(), 100);
        };

        const closeModal = () => {
            modal.classList.add('hidden');
            document.body.style.overflow = '';
            if (window.needsRefresh) window.location.reload();
        };

        openBtn.addEventListener('click', openModal);
        closeBtn.addEventListener('click', closeModal);
        modal.addEventListener('click', (e) => {
            if (e.target.closest('.bg-surface-container')) return;
            closeModal();
        });

        let searchTimeout;
        searchInput.addEventListener('input', (e) => {
            clearTimeout(searchTimeout);
            const q = e.target.value.trim();
            searchTimeout = setTimeout(() => performSearch(q), 300);
        });
    });

    async function toggleFriend(id, btn) {
        const isRemoving = btn.innerText.includes('Remove');
        if (isRemoving && !confirm('Are you sure you want to remove this friend?')) return;

        btn.disabled = true;
        btn.style.opacity = '0.5';

        try {
            const formData = new FormData();
            formData.append('ajax_toggle', '1');
            formData.append('id', id);

            const toggleUrl = window.location.origin + window.location.pathname.replace(/\/$/, '') + '/?pagename=client-friends';
            const resp = await fetch(toggleUrl, { method: 'POST', body: formData });
            const data = await resp.json();

            if (data.result) {
                window.needsRefresh = true;
                if (btn.closest('#modal-results')) {
                    const searchInput = document.getElementById('modal-search-input');
                    const event = new Event('input');
                    searchInput.dispatchEvent(event);
                } else {
                    window.location.reload();
                }
            }
        } catch (err) {
            alert('Error updating friendship: ' + err.message);
        } finally {
            btn.disabled = false;
            btn.style.opacity = '1';
        }
    }
    </script>

<?php
$GLOBALS['active'] = 'friends';
wp_app_page_end(false);
?>
