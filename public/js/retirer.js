document.querySelectorAll('.dep-valeur').forEach(btn => {
    btn.addEventListener('click', () => {
        document.getElementById('montantInput').value = btn.dataset.amount;
    });
});
