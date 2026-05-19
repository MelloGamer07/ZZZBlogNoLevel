/* ================= Element References ================= */

const loadingScreen = document.getElementById('LoadingScreen');
const changeFooterBtn = document.getElementById('change-footer-btn');

const footerContainer = document.getElementById('footer-container');
const mainFooterHTML = footerContainer?.innerHTML || '';

const video = document.getElementById('DynamicWallpaper');
const source = video?.querySelector('source');
const title = document.getElementById('title');

const interKnot = document.getElementById('interKnot');
const mailPage = document.getElementById('mail');
const posts = document.getElementById('posts');

const profilePage = document.getElementById('profile-btn');


let isDynamicFooter = false;

/* ================= Modal (Dynamic HTML) ================= */

window.addEventListener('load', () => {
    const hash = window.location.hash;

    if (hash.startsWith('#InterKnot/idArticle=')) {
        const articleId = hash.split('idArticle=')[1];
        posts.style.display = "grid";
        openModalById(articleId);
    } 
    else if (hash.startsWith('#InterKnot/')) {
        posts.style.display = "grid";
    }
    
});

function openModalById(articleId) {
    window.location.hash = '#InterKnot/idArticle=' + articleId;
    editModal(articleId, "");
    document.body.style.overflow = "hidden";
}

window.openModal = function (element) {
    const parent = element.closest('.post-container');
    if (!parent) return;

    window.location.hash = `#InterKnot/idArticle=${parent.id}`;

    editModal(parent.id , "clicked");
    document.body.style.overflow = "hidden";
};

window.closeModal = function () {
    const modal = document.getElementById('modal-post');
    if (!modal) return;

    modal.classList.add('close-modal');
    setTimeout(() => {
        modal.style.display = 'none';
        modal.remove();
        document.body.style.overflow = "auto";
    }, 1000);
    
    /*history.pushState(
        "InterKnot/",
        document.title,
        window.location.pathname + window.location.search
    );*/

    window.location.hash = 'InterKnot/';
};

window.editModal = function (id, state) {
    fetch('PHP/addPost.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `postId=${id}`
    })
    .then(res => res.text())
    .then(html => {
        const oldModal = document.getElementById('modal-post');
        if (oldModal) oldModal.remove();

        document.body.insertAdjacentHTML('afterbegin', html);

        const modal = document.getElementById('modal-post');

        const postImage = document.getElementById('post-image');
        const IMGModalContainerBackground = document.getElementById('IMG-Modal-Container-Background');
        const IMGModalContainer = document.getElementById('IMG-Modal-Container');
        const IMGModal = document.getElementById('IMG-Modal');

        if (postImage && IMGModal && IMGModalContainer && IMGModalContainerBackground) {
            postImage.addEventListener('click', () => {
                IMGModal.src = postImage.src;
                IMGModalContainerBackground.style.display = "flex";
                IMGModalContainer.style.display = "flex";
                setTimeout(() => {
                    IMGModalContainerBackground.style.opacity = 0.9;
                    IMGModalContainer.style.opacity = 1;
                }, 200);
        });
        }

        function closeIMGModal(){
            IMGModalContainerBackground.style.opacity = 0;
            IMGModalContainer.style.opacity = 0;
            setTimeout(() => {
                IMGModalContainerBackground.style.display = "none";
                IMGModalContainer.style.display = "none";
            }, 200);
        }

        IMGModalContainerBackground.addEventListener('click', () => {
            closeIMGModal();
        });

        IMGModalContainer.addEventListener('click', () => {
            closeIMGModal()
        });

        modal.style.display = 'flex';
        if(state == "clicked"){
            modal.classList.add('open-modal');
        }
        modal.addEventListener('click', e => {
            if (e.target === modal) closeModal();
        });
        
        const formComment = document.getElementById("comment-form");

        if(formComment){
            formComment.addEventListener("submit", (e) => {
                e.preventDefault();
                const comment = document.getElementById("add-post-comment");
                if(!comment.value.trim()) return;
                e.target.submit();
            });
        }

        let CheckModal = null; 
        const Background = document.querySelector('.Background');
        let ragione = null;

        let selectedForm = null;

        document.querySelectorAll('.post-segnala, .post-elimina').forEach(btn => {
            btn.addEventListener('click', (e) => {
                openCheckModal(e);
            });
        });
        function openCheckModal(e) {
            e.preventDefault();
            selectedForm = e.currentTarget.closest('form');

            if (e.currentTarget.classList.contains('post-elimina')) {
                CheckModal = document.getElementById('ModalCheckCommentoDestroy');
            } 
            else if (e.currentTarget.classList.contains('post-segnala')) {
                CheckModal = document.getElementById('ModalCheckCommentoReport');
                
                const reportTextarea = document.getElementById('reportReason');
                const reportCounter  = document.getElementById('reportCharCount');
                const max = 160;

                reportTextarea.addEventListener('input', () => {
                    const len = reportTextarea.value.length;

                    reportCounter.textContent = `${len}/${max}`;

                    reportCounter.className = 'edit-char-count' +
                        (len >= max ? ' over' : len >= max * 0.85 ? ' warn' : '');
                });
                
                ragione = CheckModal.querySelector('.input');
            }

            CheckModal.style.display = "flex";
            Background.style.display = "flex";
        }


        document.querySelectorAll('.confirm').forEach(btn => {
            btn.addEventListener('click', () => {
                if (selectedForm) {

                    if (CheckModal.id === 'ModalCheckCommentoReport') {
                        const hiddenInput = selectedForm.querySelector('input[name="ragione"]');
                        if (hiddenInput && ragione.value.trim()) {
                            hiddenInput.value = ragione.value.trim();
                        }
                        else{
                            alert("B");
                            return;
                        }
                    }

                    selectedForm.submit();
                }
            });
        });
        
        document.querySelectorAll('.Background, .XModalCheck').forEach(btn => {
            btn.addEventListener('click', closeCheckModal);
        });
        function closeCheckModal() {
            CheckModal.style.display = "none";
            Background.style.display = "none";
            if (ragione) ragione.value = '';
        }

    });
};

/* ================= Random Loading Screen Animation ================= */

function playLoadingAnimation() {
    loadingScreen.classList.remove('loading-in', 'loading-out');
    loadingScreen.style.display = 'block';

    void loadingScreen.offsetWidth;

    let id = Math.floor(Math.random() * 128);

    if ([36, 54, 95, 124].includes(id)) {
        if (Math.random() * 100 < 99) {
            id += Math.random() > 0.5 ? 1 : -1;
        }
    }

    loadingScreen.style.backgroundImage =
        `url("ASSETS/IMG/LoadingScreens/${id}.jpg")`;
    loadingScreen.style.backgroundSize = "100% auto";
    loadingScreen.style.backgroundPosition = "center";

    loadingScreen.classList.add('loading-in');

    setTimeout(() => {
        loadingScreen.classList.replace('loading-in', 'loading-out');
    }, 1150);

    setTimeout(() => {
        loadingScreen.style.display = 'none';
        loadingScreen.classList.remove('loading-out');
    }, 1250);
}

/* ================= Footer Scroll Fix ================= */

function enableHorizontalScroll() {
    const footerScroll = document.querySelector('.footer-scroll');
    if (!footerScroll || footerScroll.dataset.scrollEnabled) return;

    footerScroll.addEventListener('wheel', e => {
        e.preventDefault();
        footerScroll.scrollLeft += e.deltaY;
    });

    footerScroll.dataset.scrollEnabled = 'true';
}


/* ================= Footer Swap ================= */

changeFooterBtn?.addEventListener('click', () => {
    playLoadingAnimation();

    setTimeout(() => {
        if (!isDynamicFooter) {
            title.innerHTML = "Dynamic Wallpapers";
            title.dataset.text = "Dynamic Wallpapers";

            fetch('CDWfooter.html?t=' + Date.now())
                .then(res => res.text())
                .then(html => {
                    footerContainer.innerHTML = html;
                    isDynamicFooter = true;

                    enableHorizontalScroll();
                    bindInterKnot();

                    footerContainer
                        .querySelectorAll('.footer-scroll .btn')
                        .forEach(btn => {
                            btn.addEventListener('click', () => {
                                fetch('PHP/backgroundPreference.php?t=' + Date.now(), {
                                    method: 'POST',
                                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                                    body: `backgroundPreference=${btn.id}`
                                });
                                playLoadingAnimation();
                                setTimeout(() => {
                                    source.src =
                                        `ASSETS/DynamicWallpapers/DynamicWallpaper${btn.id}.mp4`;
                                    video.load();
                                    video.play();
                                }, 500);
                            });
                        });
                });
        } else {
            footerContainer.innerHTML = mainFooterHTML;
            title.innerHTML = "Home page";
            title.dataset.text = "Home page";
            isDynamicFooter = false;
            bindInterKnot();
        }
    }, 500);
});


/* ================= InterKnot Toggle ================= */

function bindInterKnot() {
    const knot = document.getElementById('interKnot');
    if (!knot) return;

    knot.onclick = () => {

        const hash = window.location.hash;
        const isCurrentlyInterKnot = hash.startsWith('#InterKnot/');

        window.location.hash = isCurrentlyInterKnot ? '' : '#InterKnot/';

        setTimeout(() => {
            posts.style.display = isCurrentlyInterKnot ? "none" : "grid";
        }, 500);

        playLoadingAnimation();

    };
}

bindInterKnot();


/* ================= Add-Post Modal Support ================= */



function bindAddPostTags() {
    const fileDiv = document.getElementById('fileDiv');
    const addPostTitle = document.getElementById('add-post-title');
    const addPostText = document.getElementById('add-post-text');
    const fileInput = document.getElementById('add-post-img');

    if (!fileDiv || !addPostTitle || !addPostText || !fileInput) return;

    fileDiv.addEventListener('click', function () {
        fileInput.click();
    });

    fileInput.addEventListener('change', (e) => {
        if (e.target === fileInput) {
            if (fileInput.files.length > 0) {
                const file = fileInput.files[0];
                const imageUrl = URL.createObjectURL(file);
                fileDiv.style.backgroundImage = `url(${imageUrl})`;
                fileDiv.style.backgroundSize = 'cover';      
                fileDiv.style.backgroundPosition = 'center'; 
                fileInput.style.opacity = 0;
            }
        }
    });
}

function openAuthModal() {
    const overlay = document.getElementById('authModalOverlay');
    if (!overlay) return;
    overlay.classList.add('auth-modal-open');
}

function closeAuthModal() {
    const overlay = document.getElementById('authModalOverlay');
    if (!overlay) return;
    overlay.classList.remove('auth-modal-open');
    overlay.classList.add('auth-modal-closing');
    setTimeout(() => overlay.classList.remove('auth-modal-closing'), 350);
}

document.getElementById('authModalOverlay')?.addEventListener('click', function(e) {
    if (e.target === this) closeAuthModal();
});

profilePage.addEventListener('click', () => {
    if (!isLoggedIn) {
        openAuthModal();
        return;
    }
    window.location.href = "ProfilePage.php";
});

mailPage.addEventListener('click', () => {
    if (!isLoggedIn) {
        openAuthModal();
        return;
    }
    window.location.href = "mailPage.php";
});

function goToProfile(userId) {
    window.open(
        'ProfilePage.php?id=' + userId + '#user/' + userId,
        '_blank',
        'noopener,noreferrer'
    );
}