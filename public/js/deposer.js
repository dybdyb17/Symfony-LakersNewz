document.querySelectorAll('.dep-valeur').forEach(bouton => {
    bouton.addEventListener('click', () => {
        document.getElementById('montantInput').value = bouton.dataset.amount;
    });
});
