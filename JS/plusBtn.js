const plusBtn = document.getElementById('plus-btn');

plusBtn.addEventListener('click', () => {
    fetch('addPostModal.php?t=' + Date.now())
    .then(res => res.text())
    .then(html => {
        const oldModal = document.getElementById('modal-post');
        if (oldModal) oldModal.remove();
        
        document.body.style.overflow = "hidden";

        document.body.insertAdjacentHTML('afterbegin', html);
        const modal = document.getElementById('modal-post');
        modal.style.display = 'flex';

        modal.classList.add('open-modal');

        setTimeout(() => {
            loadingScreen.classList.remove('open-modal');
        }, 1150);

        bindAddPostTags();

        modal.addEventListener('click', e => {
            if (e.target === modal) closeModal();
        });

        const formPost = document.getElementById("send-post-form");

        formPost.addEventListener("submit", (e) => {
            e.preventDefault();

            const title = document.getElementById("add-post-title");
            const text = document.getElementById("add-post-text");
            const fileInput = document.getElementById("add-post-img");

            if (fileInput.files.length === 0) {
                console.log("No file selected");
                return;
            }

            if(title.value == "" || !title.value.trim()){
                console.log("Empty title");
                return;
            }
            
            if(text.value == "" || !text.value.trim()){
                console.log("Empty text");
                return;
            }

            e.target.submit();
        });
    });
});