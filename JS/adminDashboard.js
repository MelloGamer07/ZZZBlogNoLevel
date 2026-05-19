const ACTIONS = {
    approve_post:           { title:'✔ Approve Post',       body:l=>`Publish <b style="color:#a2ff00">"${l}"</b>? It will become publicly visible.`,              btnClass:'approve', btnLabel:'✔ Approve',        toast:'Post approved!',                toastType:'success' },
    reject_post:            { title:'✕ Reject Post',        body:l=>`Reject <b style="color:#ff4444">"${l}"</b>? The submission will be permanently deleted.`,     btnClass:'reject',  btnLabel:'✕ Reject',         toast:'Post rejected.',                toastType:'error'   },
    dismiss_post_report:    { title:'✕ Dismiss Reports',    body:l=>`Dismiss all reports for <b style="color:#2aa8ff">"${l}"</b>? The post will remain published.`, btnClass:'dismiss', btnLabel:'✕ Dismiss',        toast:'Reports dismissed.',            toastType:'info'    },
    delete_reported_post:   { title:'🗑 Delete Post',       body:l=>`Delete <b style="color:#ff4444">"${l}"</b> permanently? Author will be notified.`,            btnClass:'delete',  btnLabel:'🗑 Delete Post',   toast:'Post deleted. Author notified.', toastType:'error'   },
    dismiss_comment_report: { title:'✕ Dismiss Reports',    body:l=>`Dismiss all reports on <b style="color:#2aa8ff">${l}</b>'s comment?`,                         btnClass:'dismiss', btnLabel:'✕ Dismiss',        toast:'Comment reports dismissed.',    toastType:'info'    },
    delete_comment:         { title:'🗑 Delete Comment',    body:l=>`Delete this comment by <b style="color:#ff4444">${l}</b> permanently? Author will be notified.`,btnClass:'delete', btnLabel:'🗑 Delete',        toast:'Comment deleted. Author notified.', toastType:'error' },
    ban_user:               { title:'✕ Ban User',           body:l=>`Ban <b style="color:#ff4444">${l}</b>? They won't be able to log in or interact.`,               btnClass:'ban',    btnLabel:'✕ Ban',           toast:'User banned.',                  toastType:'error',   userAction:true },
    suspend_user:           { title:'⚠ Suspend User',       body:l=>`Suspend <b style="color:#ffcc00">${l}</b>? Their account will be restricted.<br><br>
    <div style="display:flex;flex-direction:column;gap:10px;margin-top:4px">
    <div style="display:flex;gap:8px;align-items:center">
        <label style="font-size:.78rem;color:#888;width:90px;flex-shrink:0">End date</label>
        <input type="datetime-local" id="suspend_date" style="flex:1;background:#111;border:2px solid #333;border-radius:8px;color:#fff;font-family:inherit;font-style:italic;padding:4px 10px;font-size:.82rem" />
    </div>
    <div style="display:flex;gap:8px;align-items:center">
        <label style="font-size:.78rem;color:#888;width:90px;flex-shrink:0">— or duration</label>
        <input type="number" id="suspend_amount" min="1" placeholder="e.g. 3" style="width:70px;background:#111;border:2px solid #333;border-radius:8px;color:#fff;font-family:inherit;font-style:italic;padding:4px 10px;font-size:.82rem" />
        <select id="suspend_unit" style="flex:1;background:#111;border:2px solid #333;border-radius:8px;color:#fff;font-family:inherit;font-style:italic;padding:4px 10px;font-size:.82rem">
        <option value="hours">Hours</option>
        <option value="days" selected>Days</option>
        <option value="months">Months</option>
        </select>
    </div>
    <div style="font-size:.7rem;color:#555;font-style:normal">Leave both empty for an indefinite suspension.</div>
    </div>`,                   btnClass:'suspend',btnLabel:'⚠ Suspend',       toast:'User suspended.',               toastType:'info',    userAction:true, isSuspend:true },
    unban_user:             { title:'✔ Unban User',         body:l=>`Unban <b style="color:#a2ff00">${l}</b>? They'll regain full access.`,                           btnClass:'restore',btnLabel:'✔ Unban',         toast:'User unbanned.',                toastType:'success', userAction:true },
    unsuspend_user:         { title:'✔ Unsuspend User',     body:l=>`Unsuspend <b style="color:#a2ff00">${l}</b>? Their account will be restored to normal.`,         btnClass:'restore',btnLabel:'✔ Unsuspend',     toast:'User unsuspended.',             toastType:'success', userAction:true }
};

let _pending = null;

function confirmAction(action, id, label) {
    const cfg = ACTIONS[action]; if (!cfg) return;
    _pending = { action, id, cfg };
    document.getElementById('confirmTitle').textContent = cfg.title;
    document.getElementById('confirmBody').innerHTML    = cfg.body(label);
    const btn = document.getElementById('confirmBtn');
    btn.className = `action-btn ${cfg.btnClass}`;
    btn.textContent = cfg.btnLabel;
    document.getElementById('confirmOverlay').classList.add('active');
}

function closeConfirm() {
    document.getElementById('confirmOverlay').classList.remove('active');
    _pending = null;
}

function executeAction() {
    if (!_pending) return;
    const { action, id, cfg } = _pending;
    closeConfirm();

    const fd = new FormData();
    fd.append('action', action);
    fd.append('id', id);

    if (cfg.isSuspend) {
        const dateVal   = document.getElementById('suspend_date')?.value;
        const amount    = parseInt(document.getElementById('suspend_amount')?.value);
        const unit      = document.getElementById('suspend_unit')?.value;

        let until = null;
        if (dateVal) {
            until = dateVal.replace('T', ' ') + ':00';
        } else if (amount > 0) {
            const now = new Date();
            if (unit === 'hours')  now.setHours(now.getHours() + amount);
            if (unit === 'days')   now.setDate(now.getDate() + amount);
            if (unit === 'months') now.setMonth(now.getMonth() + amount);
            until = now.toISOString().slice(0,19).replace('T',' ');
        }
        if (until) fd.append('suspend_until', until);
    }

    fetch(window.location.pathname, { method:'POST', body:fd })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                showToast(cfg.toast, cfg.toastType);
                if (cfg.userAction) {
                    setTimeout(() => location.reload(), 800);
                } else {
                    const card = document.querySelector(`.list-card[data-id="${id}"]`);
                    if (card) {
                        card.style.transition = 'opacity .3s,transform .3s';
                        card.style.opacity    = '0';
                        card.style.transform  = 'translateX(20px)';
                        setTimeout(() => card.remove(), 320);
                    }
                    const badge = document.querySelector('.nav-tab.active .tab-badge');
                    if (badge) {
                        const n = Math.max(0, parseInt(badge.textContent) - 1);
                        badge.textContent = n;
                        if (n === 0) badge.classList.add('zero');
                    }
                }
            } else {
                showToast('Error: ' + (data.error ?? 'Unknown'), 'error');
            }
        })
        .catch(() => showToast('Network error.', 'error'));
}

function showToast(msg, type='success') {
    const tc  = document.getElementById('toastContainer');
    const div = document.createElement('div');
    div.className   = `toast ${type}`;
    div.textContent = msg;
    tc.appendChild(div);
    setTimeout(() => div.remove(), 3350);
}

function filterCards() {
    const q = (document.getElementById('searchInput')?.value ?? '').toLowerCase().trim();
    document.querySelectorAll('#cardList .list-card').forEach(c => {
        c.style.display = (c.dataset.search ?? '').includes(q) ? '' : 'none';
    });
}

function previewPost(postId) {
    const wrapper = document.getElementById('modal-wrapper');
    const loading = document.getElementById('previewLoading');

    wrapper.innerHTML = '';
    loading.classList.add('active');

    const fd = new FormData();
    fd.append('postId', postId);

    fetch('PHP/addPost.php', { method: 'POST', body: fd })
        .then(r => {
            if (!r.ok) throw new Error('Server error ' + r.status);
            return r.text();
        })
        .then(html => {
            loading.classList.remove('active');
            wrapper.innerHTML = html;

            const modal = wrapper.querySelector('.post-modal-container');
            if (modal) {
                modal.style.display = 'flex';
                modal.classList.add('open-modal');
                document.body.style.overflow = 'hidden';
                
                modal.addEventListener('click', function(e) {
                    if (e.target === modal) closeModal();
                });
            }
        })
        .catch(err => {
            loading.classList.remove('active');
            showToast('Failed to load preview.', 'error');
            console.error(err);
        });
}

function closeModal() {
    const wrapper = document.getElementById('modal-wrapper');
    const modal   = wrapper.querySelector('.post-modal-container');
    if (modal) {
        modal.classList.add('close-modal');
        setTimeout(() => {
            modal.style.opacity   = '0';
            modal.style.transform = 'scale(.95)';
            modal.style.transition = 'opacity .25s, transform .25s';
            setTimeout(() => { wrapper.innerHTML = ''; }, 260);
            modal.remove();
            document.body.style.overflow = 'auto';
        },1000);
    }
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        if (document.getElementById('modal-wrapper').innerHTML.trim()) closeModal();
        else closeConfirm();
    }
});

document.getElementById('confirmOverlay').addEventListener('click', function(e) {
    if (e.target === this) closeConfirm();
});