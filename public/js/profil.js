
let session = window.isLoggedIn ? { id: window.userId, solde: window.userSolde, pseudo: window.userPseudo, email: window.userEmail, firstname: window.userFirstname, lastname: window.userLastname, isLoggedIn: true } : null;

function afficherProfil(data) {
  const pseudo = data.pseudo || data.lastname || data.firstname || 'Utilisateur';
  const solde = (data.solde || 0).toFixed(2).replace('.', ',') + ' €';

  document.getElementById('profilPseudo').textContent = pseudo;
  document.getElementById('profilSolde').textContent = solde;

  const avatarDiv = document.querySelector('.profil-avatar');
  if (avatarDiv) {
    const prenom = data.firstname || data.prenom || '';
    const nom = data.lastname || data.nom || '';
    let initiales;
    if (prenom || nom) {
      initiales = ((prenom[0] || '') + (nom[0] || '')).toUpperCase();
    } else {
      initiales = pseudo.slice(0, 2).toUpperCase();
    }
    const colors = ['#552583', '#FDB927', '#e53935', '#1565C0', '#2E7D32', '#6A1B9A', '#EF6C00', '#00838F'];
    const couleur = colors[pseudo.charCodeAt(0) % colors.length];
    avatarDiv.innerHTML = initiales;
    avatarDiv.style.background = couleur;
  }

  document.getElementById('infoCivilite').textContent = data.civilite || 'Monsieur';
  document.getElementById('infoNom').textContent = data.lastname || data.nom || '—';
  document.getElementById('infoPrenom').textContent = data.firstname || data.prenom || '—';
  document.getElementById('infoDdn').textContent = data.dateNaissance || '—';
  document.getElementById('infoLieu').textContent = data.lieuNaissance || '—';
  document.getElementById('infoEmail').textContent = data.email || '—';
  document.getElementById('infoTel').textContent = data.telephone || '—';
}

document.addEventListener('DOMContentLoaded', async () => {
  if (!window.isLoggedIn) {
    window.location.href = '/connexion';
    return;
  }

  afficherProfil(session);

  try {
    const response = await fetch(`http://127.0.0.1:8000/api/profil/${session.id}`);

    if (!response.ok) return;

    const data = await response.json();

    Object.assign(session, data);
    afficherProfil(session);
  } catch (err) {
    // Affichage déjà fait avec les données window
  }
});

document.getElementById('deposerBtn')?.addEventListener('click', () => {
  window.location.href = '/deposer';
});

document.getElementById('retirerBtn')?.addEventListener('click', () => {
  window.location.href = '/retirer';
});

document.getElementById('infoPersoBtn')?.addEventListener('click', () => {
  document.getElementById('profilView').style.display = 'none';
  document.getElementById('infoPersoView').style.display = 'block';
});

document.getElementById('backBtn')?.addEventListener('click', () => {
  document.getElementById('infoPersoView').style.display = 'none';
  document.getElementById('profilView').style.display = 'block';
});

document.getElementById('deconnecterBtn')?.addEventListener('click', () => {
  window.location.href = '/deconnexion';
});

const editableFields = [
  { spanId: 'infoNom',      apiKey: 'lastname' },
  { spanId: 'infoPrenom',   apiKey: 'firstname' },
  { spanId: 'infoEmail',    apiKey: 'email' },
  { spanId: 'profilPseudo', apiKey: 'pseudo' },
  { spanId: 'infoDdn',      apiKey: 'dateNaissance' },
  { spanId: 'infoTel',      apiKey: 'telephone' },
  { spanId: 'infoLieu',     apiKey: 'lieuNaissance' },
];

let modeEdition = false;

document.getElementById('modifierBtn')?.addEventListener('click', async () => {
  const btn = document.getElementById('modifierBtn');

  if (!modeEdition) {
    // Passer en mode édition
    editableFields.forEach(({ spanId }) => {
      const span = document.getElementById(spanId);
      if (!span) return;
      const currentVal = span.textContent.trim();
      const input = document.createElement('input');
      input.type = 'text';
      input.value = currentVal === '—' ? '' : currentVal;
      input.className = 'info-edit-input';
      input.dataset.spanId = spanId;
      span.textContent = '';
      span.appendChild(input);
    });
    btn.textContent = 'Enregistrer';
    modeEdition = true;
  } else {
    // Enregistrer
    const body = {};
    editableFields.forEach(({ spanId, apiKey }) => {
      const span = document.getElementById(spanId);
      const input = span?.querySelector('input');
      if (input) body[apiKey] = input.value;
    });

    try {
      const response = await fetch(`http://127.0.0.1:8000/api/profil/${session.id}`, {
        method: 'PUT',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify(body),
      });

      if (!response.ok) throw new Error();

      alert('Profil mis à jour avec succès');
      window.location.reload();
    } catch {
      alert('Erreur lors de la mise à jour');
    }
  }
});

document.getElementById('clotureBtn')?.addEventListener('click', () => {
  document.getElementById('clotureOverlay').style.display = 'flex';
});

document.getElementById('clotureCancelBtn')?.addEventListener('click', () => {
  document.getElementById('clotureOverlay').style.display = 'none';
});

document.getElementById('clotureConfirmBtn')?.addEventListener('click', async () => {
  try {
    const response = await fetch(`http://127.0.0.1:8000/api/profil/${session.id}`, {
      method: 'DELETE',
    });
    if (!response.ok) throw new Error();
  } catch {
    // fallback
  }
  window.location.href = '/inscription';
});