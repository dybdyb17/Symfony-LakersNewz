let session = window.isLoggedIn ? { id: window.userId, solde: window.userSolde, isLoggedIn: true } : null;
let solde = window.userSolde || 0;
let montantSelectionne = 0;

function updateSoldeBadge() {
  const badge = document.getElementById('soldeBadge');
  if (badge) badge.textContent = solde.toFixed(2).replace('.', ',') + ' €';
}


document.querySelectorAll('.dep-preset').forEach(btn => {
  btn.addEventListener('click', () => {
    montantSelectionne = parseInt(btn.dataset.amount);
    document.getElementById('montantInput').value = montantSelectionne;
    document.querySelectorAll('.dep-preset').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
  });
});

document.getElementById('montantInput')?.addEventListener('input', e => {
  montantSelectionne = parseFloat(e.target.value) || 0;
  document.querySelectorAll('.dep-preset').forEach(b => b.classList.remove('active'));
  document.querySelectorAll('.dep-preset').forEach(b => {
    if (parseInt(b.dataset.amount) === montantSelectionne) b.classList.add('active');
  });
});

document.getElementById('retirerBtn')?.addEventListener('click', async () => {
  const montant = parseFloat(document.getElementById('montantInput')?.value) || 0;

  if (montant <= 0) {
    alert('Veuillez saisir un montant.');
    return;
  }

  if (montant < 10) {
    alert('Le montant minimum de retrait est de 10 €.');
    return;
  }

  if (!window.isLoggedIn || !window.userId) {
    alert('Session invalide. Veuillez vous reconnecter.');
    window.location.href = '/connexion';
    return;
  }

  try {
    const response = await fetch('http://127.0.0.1:8000/api/retirer', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({ montant })
    });

    const data = await response.json();

    if (!response.ok) {
      document.getElementById('soldeInsuffisantOverlay').style.display = 'flex';
      return;
    }

    solde = data.solde;
    updateSoldeBadge();

    const confirmMontant = document.getElementById('confirmMontant');
    if (confirmMontant) confirmMontant.textContent = montant.toFixed(2).replace('.', ',') + ' €';

    document.getElementById('retraitConfirmeOverlay').style.display = 'flex';
  } catch (err) {
    alert('Impossible de contacter le serveur. Vérifiez votre connexion.');
  }
});

document.getElementById('okInsuffisantBtn')?.addEventListener('click', () => {
  document.getElementById('soldeInsuffisantOverlay').style.display = 'none';
});

document.getElementById('confirmOkBtn')?.addEventListener('click', () => {
  window.location.href = '/paris';
});

document.getElementById('closeBtn')?.addEventListener('click', () => {
  window.location.href = '/paris';
});

document.addEventListener('DOMContentLoaded', () => {
  if (!window.isLoggedIn) {
    window.location.href = '/connexion';
    return;
  }
  updateSoldeBadge();
});