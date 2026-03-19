
let session = JSON.parse(localStorage.getItem('lakersNewzSession')) ||
              JSON.parse(sessionStorage.getItem('lakersNewzSession')) || null;

function afficherProfil(data) {
  const pseudo = data.pseudo || data.nom || data.prenom || 'Utilisateur';
  const solde = (data.solde || 0).toFixed(2).replace('.', ',') + ' €';

  document.getElementById('profilPseudo').textContent = pseudo;
  document.getElementById('profilSolde').textContent = solde;

  const avatarDiv = document.querySelector('.profil-avatar');
  if (avatarDiv) {
    const prenom = data.prenom || data.firstname || '';
    const nom = data.nom || data.lastname || '';
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
  document.getElementById('infoNom').textContent = data.nom || '—';
  document.getElementById('infoPrenom').textContent = data.prenom || '—';
  document.getElementById('infoDdn').textContent = data.dateNaissance || '—';
  document.getElementById('infoLieu').textContent = data.lieuNaissance || '—';
  document.getElementById('infoEmail').textContent = data.email || '—';
  document.getElementById('infoTel').textContent = data.telephone || '—';
}

document.addEventListener('DOMContentLoaded', async () => {
  if (!session || !session.isLoggedIn) {
    window.location.href = '/connexion';
    return;
  }

  afficherProfil(session);

  try {
    const response = await fetch(`http://127.0.0.1:8000/api/profil/${session.id}`, {
      headers: { 'Authorization': 'Bearer ' + session.token },
    });

    if (!response.ok) return;

    const data = await response.json();

    Object.assign(session, data);
    localStorage.setItem('lakersNewzSession', JSON.stringify(session));

    afficherProfil(session);
  } catch (err) {
    // Affichage déjà fait avec les données localStorage
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
  localStorage.removeItem('lakersNewzSession');
  sessionStorage.removeItem('lakersNewzSession');
  window.location.href = '/';
});

const editableFields = [
  { spanId: 'infoNom',     apiKey: 'lastname' },
  { spanId: 'infoPrenom',  apiKey: 'firstname' },
  { spanId: 'infoEmail',   apiKey: 'email' },
  { spanId: 'profilPseudo', apiKey: 'pseudo' },
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
          'Authorization': 'Bearer ' + session.token,
        },
        body: JSON.stringify(body),
      });

      if (!response.ok) throw new Error();

      // Mettre à jour la session
      if (body.lastname)  session.nom    = body.lastname;
      if (body.firstname) session.prenom = body.firstname;
      if (body.email)     session.email  = body.email;
      if (body.pseudo)    session.pseudo = body.pseudo;
      localStorage.setItem('lakersNewzSession', JSON.stringify(session));

      // Repasser en lecture seule
      editableFields.forEach(({ spanId }) => {
        const span = document.getElementById(spanId);
        const input = span?.querySelector('input');
        if (input) span.textContent = input.value || '—';
      });

      // Mettre à jour le pseudo affiché dans le hero
      document.getElementById('profilPseudo').textContent = session.pseudo || session.nom || 'Utilisateur';

      btn.textContent = 'Modifier mes informations';
      modeEdition = false;
      alert('Profil mis à jour avec succès');
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
      headers: { 'Authorization': 'Bearer ' + session.token },
    });
    if (!response.ok) throw new Error();
  } catch {
    // fallback : on supprime quand même en local
  }
  localStorage.removeItem('lakersNewzSession');
  sessionStorage.removeItem('lakersNewzSession');
  window.location.href = '/inscription';
});