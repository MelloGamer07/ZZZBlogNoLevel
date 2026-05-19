document.addEventListener('DOMContentLoaded', () => {
    const backBtn = document.getElementById('backBtn');
    if (backBtn) {
        backBtn.addEventListener('click', handleBack);
    }

    const bar = document.querySelector('.profile-lvlBar-container');
    if (bar) {
        const fill    = bar.querySelector('.profile-lvlBar-completition');
        const percent = parseFloat(bar.dataset.percent) || 0;
        const current = bar.dataset.xpCurrent;
        const needed  = bar.dataset.xpNeeded;

        requestAnimationFrame(() => {
            fill.style.transition = 'width 1s cubic-bezier(0.4, 0, 0.2, 1)';
            fill.style.width = percent + '%';
        });

        bar.title = `${current} / ${needed} XP`;
    }

    const logoutOverlay = document.getElementById('logoutModalOverlay');
    if (logoutOverlay) {
        logoutOverlay.addEventListener('click', function(e) {
            if (e.target === this) closeLogoutModal();
        });
    }

    const editOverlay = document.getElementById('editModalOverlay');
    if (editOverlay) {
        editOverlay.addEventListener('click', function(e) {
            if (e.target === this) closeEditModal();
        });
    }

    const avatarOverlay = document.getElementById('avatarPickerOverlay');
    if (avatarOverlay) {
        avatarOverlay.addEventListener('click', function(e) {
            if (e.target === this) closeAvatarPicker();
        });
    }

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            closeLogoutModal();
            const searchOverlay = document.getElementById('searchModalOverlay');
            if (searchOverlay) searchOverlay.classList.remove('active');
            closeFriendListModal();
            closeAvatarPicker();
            closeEditModal();
        }
    });
});

function handleBack() {
    const { isOwnProfile, isGuest } = window.PAGE_DATA;
    if (!isOwnProfile && !isGuest) {
        window.location.href = 'ProfilePage.php';
    } else {
        window.location.href = 'home.php';
    }
}

function openLogoutModal() {
    document.getElementById('logoutModalOverlay').classList.add('active');
}

function closeLogoutModal() {
    document.getElementById('logoutModalOverlay').classList.remove('active');
}

function openSearchModal() {
    document.getElementById('searchModalOverlay').classList.add('active');
    setTimeout(() => document.getElementById('searchInput').focus(), 50);
}

function closeSearchModal(e) {
    if (e.target === document.getElementById('searchModalOverlay')) {
        document.getElementById('searchModalOverlay').classList.remove('active');
        document.getElementById('searchInput').value = '';
        document.getElementById('searchResults').innerHTML = '<p class="search-placeholder">Start typing to search...</p>';
    }
}

const FRIENDS_PER_PAGE = 15;
let friendCurrentPage  = window.PAGE_DATA?.friendsCurrentPage ?? 1;
let friendTotalPages   = window.PAGE_DATA?.friendsTotalPages   ?? 1;
let friendTotalCount   = window.PAGE_DATA?.friendsTotal        ?? 0;
let friendsCache       = {};

if (window.PAGE_DATA?.friends) {
    friendsCache[friendCurrentPage] = window.PAGE_DATA.friends;
}

function openFriendListModal() {
    document.getElementById('friendListModalOverlay').classList.add('active');
    renderFriendPage(friendCurrentPage);
}

function closeFriendListModal(e) {
    if (!e || e.target === document.getElementById('friendListModalOverlay')) {
        document.getElementById('friendListModalOverlay').classList.remove('active');
    }
}

function renderFriendPage(page) {
    friendCurrentPage = page;
    const resultsDiv   = document.getElementById('friendListResults');
    const paginationDiv = document.getElementById('friendListPagination');
    const countEl      = document.getElementById('friendListCount');

    countEl.textContent = friendTotalCount === 1
        ? '1 friend'
        : `${friendTotalCount} friends`;

    if (friendTotalCount === 0) {
        resultsDiv.innerHTML = '<p class="search-placeholder">No friends yet.</p>';
        paginationDiv.innerHTML = '';
        return;
    }

    if (friendsCache[page]) {
        displayFriendPage(friendsCache[page]);
        buildPagination();
        return;
    }

    resultsDiv.innerHTML = '<p class="search-placeholder">Loading...</p>';

    fetch(`PHP/get_friends.php?id=${window.PAGE_DATA.profileId}&page=${page}&per_page=${FRIENDS_PER_PAGE}`)
        .then(r => r.json())
        .then(data => {
            friendsCache[page] = data.friends ?? [];
            friendTotalCount   = data.total   ?? friendTotalCount;
            friendTotalPages   = data.pages   ?? friendTotalPages;
            displayFriendPage(friendsCache[page]);
            buildPagination();
        })
        .catch(() => {
            resultsDiv.innerHTML = '<p class="search-placeholder">Error loading friends.</p>';
        });
}

function displayFriendPage(friends) {
    const resultsDiv = document.getElementById('friendListResults');

    if (!friends || friends.length === 0) {
        resultsDiv.innerHTML = '<p class="search-placeholder">No friends on this page.</p>';
        return;
    }

    resultsDiv.innerHTML = '';
    friends.forEach(user => {
        const avatarSrc = 'ASSETS/IMG/Avatars/Avatar' + user.Avatar + '.png';
        const div = document.createElement('div');
        div.className = 'search-result-item';
        div.innerHTML = `
            <img src="${avatarSrc}" onerror="this.src='ASSETS/IMG/Avatars/Avatar0.png'" alt="">
            <span>${user.Username}</span>
        `;
        div.onclick = () => goToProfile(user.Id);
        resultsDiv.appendChild(div);
    });
}

function buildPagination() {
    const paginationDiv = document.getElementById('friendListPagination');
    paginationDiv.innerHTML = '';

    if (friendTotalPages <= 1) return;

    const WINDOW = 2; 
    const createBtn = (label, page, disabled, active) => {
        const btn = document.createElement('button');
        btn.className = 'fl-page-btn' + (active ? ' active' : '') + (disabled ? ' disabled' : '');
        btn.textContent = label;
        btn.disabled = disabled;
        if (!disabled) btn.onclick = () => renderFriendPage(page);
        return btn;
    };

    paginationDiv.appendChild(createBtn('←', friendCurrentPage - 1, friendCurrentPage === 1, false));

    let pages = new Set([1, friendTotalPages]);
    for (let p = Math.max(1, friendCurrentPage - WINDOW); p <= Math.min(friendTotalPages, friendCurrentPage + WINDOW); p++) {
        pages.add(p);
    }
    pages = Array.from(pages).sort((a, b) => a - b);

    let prev = null;
    pages.forEach(p => {
        if (prev !== null && p - prev > 1) {
            const dots = document.createElement('span');
            dots.className = 'fl-ellipsis';
            dots.textContent = '…';
            paginationDiv.appendChild(dots);
        }
        paginationDiv.appendChild(createBtn(p, p, false, p === friendCurrentPage));
        prev = p;
    });

    paginationDiv.appendChild(createBtn('→', friendCurrentPage + 1, friendCurrentPage === friendTotalPages, false));
}

let searchTimeout;
function searchUsers(query) {
    clearTimeout(searchTimeout);
    const resultsDiv = document.getElementById('searchResults');

    if (query.trim().length === 0) {
        resultsDiv.innerHTML = '<p class="search-placeholder">Start typing to search...</p>';
        return;
    }

    resultsDiv.innerHTML = '<p class="search-placeholder">Searching...</p>';

    searchTimeout = setTimeout(() => {
        fetch('PHP/search_users.php?q=' + encodeURIComponent(query))
            .then(res => res.json())
            .then(users => {
                if (users.length === 0) {
                    resultsDiv.innerHTML = '<p class="search-placeholder">No users found.</p>';
                    return;
                }

                resultsDiv.innerHTML = '';
                users.forEach(user => {
                    const avatarSrc = 'ASSETS/IMG/Avatars/Avatar' + user.Avatar + '.png';
                    const div = document.createElement('div');
                    div.className = 'search-result-item';
                    div.innerHTML = `
                        <img src="${avatarSrc}" onerror="this.src='ASSETS/IMG/Avatars/Avatar0.png'" alt="">
                        <span>${user.Username}</span>
                    `;
                    div.onclick = () => goToProfile(user.Id);
                    resultsDiv.appendChild(div);
                });
            })
            .catch(() => {
                resultsDiv.innerHTML = '<p class="search-placeholder">Error searching.</p>';
            });
    }, 250);
}

function goToProfile(userId) {
    window.location.href = 'ProfilePage.php?id=' + userId;
}

function goToPost(postId) {
    window.open(
        'home.php#InterKnot/idArticle=' + postId,
        '_blank',
        'noopener,noreferrer'
    );
}

let isFollowing = window.PAGE_DATA?.isFollowing ?? false;

function toggleFollow(userId) {
    const btn  = document.getElementById('followBtn');
    const text = document.getElementById('follow-btn-text');

    isFollowing = !isFollowing;
    text.textContent = isFollowing ? 'Unfollow' : 'Follow';
    btn.classList.toggle('following', isFollowing);

    fetch('PHP/isFollow.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'user_id=' + userId
    })
    .then(res => res.json())
    .then(data => {
        if (data.error) {
            isFollowing = !isFollowing;
            text.textContent = isFollowing ? 'Unfollow' : 'Follow';
            btn.classList.toggle('following', isFollowing);
            alert(data.error);
            return;
        }
        isFollowing = data.following;
        text.textContent = isFollowing ? 'Unfollow' : 'Follow';
        btn.classList.toggle('following', isFollowing);
    })
    .catch(() => {
        isFollowing = !isFollowing;
        text.textContent = isFollowing ? 'Unfollow' : 'Follow';
        btn.classList.toggle('following', isFollowing);
    });
}

let pendingAvatarIndex = window.PAGE_DATA?.avatarIndex ?? 0;

function openEditModal() {
    document.getElementById('editFeedback').textContent = '';
    document.getElementById('editFeedback').className = 'edit-feedback';

    pendingAvatarIndex = window.PAGE_DATA.avatarIndex;
    document.getElementById('editAvatarPreview').src =
        'ASSETS/IMG/Avatars/Avatar' + pendingAvatarIndex + '.png';

    document.getElementById('editModalOverlay').classList.add('active');
}

function closeEditModal() {
    const el = document.getElementById('editModalOverlay');
    if (el) el.classList.remove('active');
}

function updateCharCount(inputId, counterId, max) {
    const len = document.getElementById(inputId).value.length;
    const el  = document.getElementById(counterId);
    el.textContent = len + '/' + max;
    el.className = 'edit-char-count' +
        (len >= max ? ' over' : len >= max * 0.85 ? ' warn' : '');
}

function saveProfile() {
    const username = document.getElementById('editUsername').value.trim();
    const desc     = document.getElementById('editDesc').value.trim();
    const feedback = document.getElementById('editFeedback');
    const saveBtn  = document.getElementById('editSaveBtn');

    if (username.length === 0) {
        feedback.textContent = 'Username cannot be empty.';
        feedback.className = 'edit-feedback error';
        return;
    }

    saveBtn.disabled = true;
    feedback.textContent = 'Saving…';
    feedback.className = 'edit-feedback';

    const body = new URLSearchParams({
        username:  username,
        desc:      desc,
        avatar_id: pendingAvatarIndex
    });

    fetch('PHP/updateProfile.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: body.toString()
    })
    .then(res => res.json())
    .then(data => {
        saveBtn.disabled = false;

        if (data.error) {
            feedback.textContent = data.error;
            feedback.className = 'edit-feedback error';
            return;
        }

        const newAvatarSrc = 'ASSETS/IMG/Avatars/Avatar' + pendingAvatarIndex + '.png';
        document.getElementById('profilePfp').src             = newAvatarSrc;
        document.getElementById('profileUsername').textContent = username;
        document.getElementById('profileDesc').textContent     = desc || username;

        feedback.textContent = '✓ Profile updated!';
        feedback.className = 'edit-feedback';

        setTimeout(closeEditModal, 900);
    })
    .catch(() => {
        saveBtn.disabled = false;
        feedback.textContent = 'Network error. Please try again.';
        feedback.className = 'edit-feedback error';
    });
}

const AVATAR_COUNT = 58;

function buildAvatarGrid() {
    const grid = document.getElementById('avatarGrid');
    if (!grid || grid.childElementCount > 0) return;

    for (let i = 0; i < AVATAR_COUNT; i++) {
        const item = document.createElement('div');
        item.className = 'avatar-grid-item' + (i === pendingAvatarIndex ? ' selected' : '');
        item.dataset.index = i;
        item.innerHTML = `
            <div class="avatar-circle">
                <img src="ASSETS/IMG/Avatars/Avatar${i}.png"
                     onerror="this.src='ASSETS/IMG/Avatars/Avatar0.png'"
                     alt="Avatar ${i}">
            </div>`;
        item.addEventListener('click', () => selectAvatar(i));
        grid.appendChild(item);
    }
}

function openAvatarPicker() {
    buildAvatarGrid();
    document.querySelectorAll('.avatar-grid-item').forEach(el => {
        el.classList.toggle('selected', parseInt(el.dataset.index) === pendingAvatarIndex);
    });
    document.getElementById('avatarPickerOverlay').classList.add('active');
}

function closeAvatarPicker() {
    const el = document.getElementById('avatarPickerOverlay');
    if (el) el.classList.remove('active');
}

function selectAvatar(index) {
    pendingAvatarIndex = index;
    document.querySelectorAll('.avatar-grid-item').forEach(el => {
        el.classList.toggle('selected', parseInt(el.dataset.index) === index);
    });
    document.getElementById('editAvatarPreview').src = 'ASSETS/IMG/Avatars/Avatar' + index + '.png';
    setTimeout(closeAvatarPicker, 180);
}