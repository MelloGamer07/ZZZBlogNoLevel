function openModalMail(e, id){

    fetch('PHP/addModalMail.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `notificaId=${id}`
    })
    .then(res => res.text())
    .then(html => {

        const container = e.currentTarget;
        const nonLettaNotifica = container.querySelector('.nonLettaNotifica');
        if (nonLettaNotifica) nonLettaNotifica.remove();

        const oldModal = document.querySelector('.containerNotificona');
        if (oldModal) oldModal.remove();

        document.body.insertAdjacentHTML('afterbegin', html);

    })
    .catch(err => console.error(err));
}


document.addEventListener('DOMContentLoaded', () => {
    const firstContainer = document.querySelector('.containerNotifica');
    if (firstContainer) {
        const notificaId = parseInt(firstContainer.dataset.id, 10);
        openModalMail({ currentTarget: firstContainer }, notificaId);
    }
});

document.querySelectorAll('.containerNotifica').forEach(container => {
    container.addEventListener('click', e => {
        const notificaId = parseInt(container.dataset.id, 10);
        openModalMail({ currentTarget: container }, notificaId);
    });
});

window.goToProfile = function(userId) {
    window.open(
        'ProfilePage.php?id=' + userId + '#user/' + userId,
        '_blank',
        'noopener,noreferrer'
    );
}
