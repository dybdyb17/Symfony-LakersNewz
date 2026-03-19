let session = JSON.parse(localStorage.getItem('lakersNewzSession')) ||
              JSON.parse(sessionStorage.getItem('lakersNewzSession')) || null;

let solde = session?.solde || 0;
let montantSelectionne = 0;

function updateSoldeBadge() {
  const badge = document.getElementById('soldeBadge');
  if (badge) badge.textContent = solde.toFixed(2).replace('.', ',') + ' €';
}

function saveSolde() {
  if (!session) return;
  session.solde = solde;
  if (localStorage.getItem('lakersNewzSession')) {
    localStorage.setItem('lakersNewzSession', JSON.stringify(session));
  } else {
    sessionStorage.setItem('lakersNewzSession', JSON.stringify(session));
  }
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

document.getElementById('carteOption')?.addEventListener('click', () => {
  const form = document.getElementById('carteForm');
  const option = document.getElementById('carteOption');
  if (form && option) {
    form.style.display = 'block';
    option.style.display = 'none';
  }
});

document.getElementById('fermerCarteBtn')?.addEventListener('click', () => {
  const form = document.getElementById('carteForm');
  const option = document.getElementById('carteOption');
  if (form && option) {
    form.style.display = 'none';
    option.style.display = 'block';
  }
});

document.getElementById('carteNumero')?.addEventListener('input', e => {
  let val = e.target.value.replace(/\D/g, '').substring(0, 16);
  e.target.value = val.replace(/(\d{4})(?=\d)/g, '$1 ');
});

document.getElementById('carteExpiry')?.addEventListener('input', e => {
  let val = e.target.value.replace(/\D/g, '');
  if (val.length >= 3) val = val.substring(0, 2) + '/' + val.substring(2, 4);
  e.target.value = val;
});

document.getElementById('continuerBtn')?.addEventListener('click', () => {
  const montant = parseFloat(document.getElementById('montantInput')?.value) || 0;

  if (montant <= 0) {
    alert('Veuillez saisir un montant valide.');
    return;
  }

  if (montant < 11) {
    alert('Le montant minimum est de 11 €.');
    return;
  }

  const carteForm = document.getElementById('carteForm');
  const carteOption = document.getElementById('carteOption');
  if (carteForm && carteForm.style.display === 'none') {
    carteForm.style.display = 'block';
    if (carteOption) carteOption.style.display = 'none';
    return;
  }

  validerDepot(montant);
});

document.getElementById('validerCarteBtn')?.addEventListener('click', () => {
  const montant = parseFloat(document.getElementById('montantInput')?.value) || 0;
  if (montant <= 0) { alert('Veuillez saisir un montant valide.'); return; }
  validerDepot(montant);
});

async function validerDepot(montant) {
  const sessionData = JSON.parse(localStorage.getItem('lakersNewzSession')) || null;
  if (!sessionData || !sessionData.id) {
    alert('Session invalide. Veuillez vous reconnecter.');
    window.location.href = '/connexion';
    return;
  }

  try {
    const response = await fetch('http://127.0.0.1:8000/api/deposer', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': 'Bearer ' + sessionData.token,
      },
      body: JSON.stringify({ montant })
    });

    const data = await response.json();

    if (!response.ok) {
      alert(data.message || 'Une erreur est survenue.');
      return;
    }

    solde = data.solde ?? (solde + montant);
    sessionData.solde = data.solde;
    sessionData.isLoggedIn = true;
    localStorage.setItem('lakersNewzSession', JSON.stringify(sessionData));
    updateSoldeBadge();

    const overlay = document.getElementById('confirmOverlay');
    const confirmMontant = document.getElementById('confirmMontant');
    if (overlay && confirmMontant) {
      confirmMontant.textContent = montant.toFixed(2).replace('.', ',') + ' €';
      overlay.style.display = 'flex';
    }
  } catch (err) {
    alert('Impossible de contacter le serveur. Vérifiez votre connexion.');
  }
}

document.getElementById('confirmOkBtn')?.addEventListener('click', () => {
  window.location.href = '/paris';
});

document.addEventListener('DOMContentLoaded', () => {
  if (!session || !session.isLoggedIn) {
    window.location.href = '/connexion';
    return;
  }
  updateSoldeBadge();
});