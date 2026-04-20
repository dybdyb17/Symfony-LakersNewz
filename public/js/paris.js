const API_URL = 'http://127.0.0.1:8000';
let selections = [];
let tabActif = 'simple';

const NBA_TEAMS = {
  'hawks': 'atl', 'celtics': 'bos', 'nets': 'bkn', 'hornets': 'cha',
  'bulls': 'chi', 'cavaliers': 'cle', 'mavericks': 'dal', 'nuggets': 'den',
  'pistons': 'det', 'warriors': 'gs', 'rockets': 'hou', 'pacers': 'ind',
  'clippers': 'lac', 'lakers': 'lal', 'grizzlies': 'mem', 'heat': 'mia',
  'bucks': 'mil', 'timberwolves': 'min', 'pelicans': 'no', 'knicks': 'ny',
  'thunder': 'okc', 'magic': 'orl', '76ers': 'phi', 'suns': 'phx',
  'blazers': 'por', 'trail blazers': 'por', 'kings': 'sac', 'spurs': 'sa',
  'raptors': 'tor', 'jazz': 'utah', 'wizards': 'wsh', 'wolves': 'min',
};

function getLogoUrl(teamName) {
  const lower = teamName.toLowerCase();
  for (const [key, abbr] of Object.entries(NBA_TEAMS)) {
    if (lower.includes(key)) {
      return `https://a.espncdn.com/i/teamlogos/nba/500/${abbr}.png`;
    }
  }
  return `https://via.placeholder.com/55x55/552583/FDB927?text=${teamName.charAt(0)}`;
}

const MATCHS_DEMO = [
  { id:1, date:'19:00, 05 février', team1:'Cleveland Cavaliers', team2:'Brooklyn Nets', cote1:2.14, cote2:2.27 },
  { id:2, date:'19:00, 05 février', team1:'Philadelphia 76ers', team2:'Atlanta Hawks', cote1:1.96, cote2:2.22 },
  { id:3, date:'19:00, 05 février', team1:'Washington Wizards', team2:'Indiana Pacers', cote1:2.04, cote2:1.92 },
  { id:4, date:'01:30, 05 février', team1:'New York Knicks', team2:'Detroit Pistons', cote1:1.89, cote2:2.06 },
  { id:5, date:'02:00, 05 février', team1:'Chicago Bulls', team2:'Toronto Raptors', cote1:2.09, cote2:2.01 },
  { id:6, date:'02:00, 05 février', team1:'Los Angeles Lakers', team2:'Golden State Warriors', cote1:1.75, cote2:2.10 },
];

function genererCote(base) {
  return parseFloat((base + (Math.random() * 0.4 - 0.1)).toFixed(2));
}

function renderMatchCard(match) {
  const card = document.createElement('div');
  card.className = 'match-card';
  const logo1 = getLogoUrl(match.team1);
  const logo2 = getLogoUrl(match.team2);
  const abrev1 = match.team1.split(' ').pop().toUpperCase().slice(0, 7);
  const abrev2 = match.team2.split(' ').pop().toUpperCase().slice(0, 7);

  card.innerHTML = `
    <div class="match-card-haut">
      <span class="match-card-date">${match.date}</span>
      <span class="match-card-live">— — —</span>
    </div>
    <div class="match-card-equipes">
      <div class="match-equipe">
        <div class="match-equipe-logo">
          <img src="${logo1}" alt="${match.team1}" onerror="this.src='https://via.placeholder.com/50x50/552583/FDB927?text=${match.team1.charAt(0)}'">
        </div>
        <span class="match-equipe-nom">${match.team1}</span>
      </div>
      <span class="match-vs">VS</span>
      <div class="match-equipe">
        <div class="match-equipe-logo">
          <img src="${logo2}" alt="${match.team2}" onerror="this.src='https://via.placeholder.com/50x50/552583/FDB927?text=${match.team2.charAt(0)}'">
        </div>
        <span class="match-equipe-nom">${match.team2}</span>
      </div>
    </div>
    <div class="match-card-cotes">
      <button class="cote-btn" data-match-id="${match.id}" data-team="${match.team1}" data-cote="${match.cote1}" data-label="${match.team1} - ${match.team2}">
        ${abrev1} (${match.cote1})
      </button>
      <button class="cote-btn" data-match-id="${match.id}" data-team="${match.team2}" data-cote="${match.cote2}" data-label="${match.team1} - ${match.team2}">
        ${abrev2} (${match.cote2})
      </button>
    </div>
  `;
  card.querySelectorAll('.cote-btn').forEach(btn => {
    btn.addEventListener('click', () => toggleSelection(btn));
  });
  return card;
}

async function chargerMatchs() {
  const container = document.getElementById('matchs-container');
  if (!container) return;
  try {
    const res = await fetch('http://127.0.0.1:8000/api/cache/matchs');
    if (!res.ok) throw new Error('API ko');
    const data = await res.json();
    const events = (data.events || []).slice(0, 12);
    if (events.length === 0) throw new Error('Aucun match');
    const matchs = events.map((event, i) => {
      const comp = event.competitions[0];
      const t1 = comp.competitors[0];
      const t2 = comp.competitors[1];
      const dateObj = new Date(event.date);
      const dateStr = dateObj.toLocaleString('fr-FR', { hour: '2-digit', minute: '2-digit', day: 'numeric', month: 'long' });
      return { id: i + 1, date: dateStr, dateObj, team1: t1.team.displayName, team2: t2.team.displayName, cote1: genererCote(1.75), cote2: genererCote(1.90) };
    });
    const matchsFuturs = matchs.filter(m => m.dateObj > new Date());
    if (matchsFuturs.length === 0) {
      container.innerHTML = '<p class="no-matchs-msg">Aucun match disponible pour le moment, revenez plus tard !</p>';
      return;
    }
    container.innerHTML = '';
    matchsFuturs.forEach(m => container.appendChild(renderMatchCard(m)));
  } catch {
    container.innerHTML = '';
    MATCHS_DEMO.forEach(m => container.appendChild(renderMatchCard(m)));
  }
}

function toggleSelection(btn) {
  const matchId = parseInt(btn.dataset.matchId);
  const team = btn.dataset.team;
  const cote = parseFloat(btn.dataset.cote);
  const label = btn.dataset.label;
  const existingIdx = selections.findIndex(s => s.matchId === matchId);

  if (existingIdx !== -1 && selections[existingIdx].teamName === team) {
    selections.splice(existingIdx, 1);
    btn.classList.remove('selected');
  } else {
    if (existingIdx !== -1) {
      selections.splice(existingIdx, 1);
      document.querySelectorAll(`.cote-btn[data-match-id="${matchId}"]`).forEach(b => b.classList.remove('selected'));
    }
    selections.push({ matchId, teamName: team, cote, matchLabel: label, mise: 0 });
    btn.classList.add('selected');
  }
  updateCoupon();
}

function updateCoupon() {
  const count = selections.length;
  const couponCountEl = document.getElementById('couponCount');
  if (couponCountEl) couponCountEl.textContent = count + (count <= 1 ? ' sélection' : ' sélections');

  const panierCountEl = document.getElementById('panierCount');
  if (panierCountEl) {
    panierCountEl.textContent = count;
    panierCountEl.classList.toggle('show', count > 0);
  }

  const couponEmpty = document.getElementById('couponEmpty');
  const couponSelections = document.getElementById('couponSelections');

  if (count === 0) {
    if (couponEmpty) couponEmpty.style.display = 'flex';
    if (couponSelections) couponSelections.style.display = 'none';
  } else {
    if (couponEmpty) couponEmpty.style.display = 'none';
    if (couponSelections) couponSelections.style.display = 'flex';
    renderCouponItems();
  }
  calculerGains();
}

function renderCouponItems() {
  const itemsEl = document.getElementById('couponItems');
  const combineMiseEl = document.getElementById('combineMise');
  if (!itemsEl) return;
  itemsEl.innerHTML = '';

  if (tabActif === 'simple') {
    if (combineMiseEl) combineMiseEl.style.display = 'none';
    selections.forEach((sel, idx) => {
      const item = document.createElement('div');
      item.className = 'coupon-element';
      item.innerHTML = `
        <div class="coupon-element-header">
          <span class="coupon-element-match">${sel.matchLabel}</span>
          <span class="coupon-element-cote">${sel.cote}</span>
          <button class="coupon-element-supprimer" data-idx="${idx}">×</button>
        </div>
        <div class="coupon-element-equipe">${sel.teamName}</div>
        <div class="coupon-element-type">Vainqueur du match</div>
        <div class="coupon-element-mise-ligne">
          <input type="number" class="coupon-element-mise" data-idx="${idx}" placeholder="Mise" min="1" value="${sel.mise || ''}">
          <span class="coupon-element-devise">€</span>
        </div>
      `;
      item.querySelector('.coupon-element-supprimer').addEventListener('click', () => removeSelection(idx));
      item.querySelector('.coupon-element-mise').addEventListener('input', e => {
        selections[idx].mise = parseFloat(e.target.value) || 0;
        calculerGains();
      });
      itemsEl.appendChild(item);
    });
  } else {
    if (combineMiseEl) combineMiseEl.style.display = 'flex';
    selections.forEach((sel, idx) => {
      const item = document.createElement('div');
      item.className = 'coupon-element';
      item.innerHTML = `
        <div class="coupon-element-header">
          <span class="coupon-element-match">${sel.matchLabel}</span>
          <span class="coupon-element-cote">${sel.cote}</span>
          <button class="coupon-element-supprimer" data-idx="${idx}">×</button>
        </div>
        <div class="coupon-element-equipe">${sel.teamName}</div>
        <div class="coupon-element-type">Vainqueur du match</div>
      `;
      item.querySelector('.coupon-element-supprimer').addEventListener('click', () => removeSelection(idx));
      itemsEl.appendChild(item);
    });
    let coteTotale = 1;
    for (let i = 0; i < selections.length; i++) {
      coteTotale = coteTotale * selections[i].cote;
    }
    const combineCoteEl = document.getElementById('combineCote');
    if (combineCoteEl) combineCoteEl.textContent = coteTotale.toFixed(2);
  }
}

function removeSelection(idx) {
  const sel = selections[idx];
  document.querySelectorAll(`.cote-btn[data-match-id="${sel.matchId}"]`).forEach(b => b.classList.remove('selected'));
  selections.splice(idx, 1);
  updateCoupon();
}

function calculerGains() {
  const gainsEl = document.getElementById('gainsAmount');
  if (!gainsEl) return;
  let gains = 0;
  if (tabActif === 'simple') {
    for (let i = 0; i < selections.length; i++) {
      gains = gains + selections[i].mise * selections[i].cote;
    }
  } else {
    const mise = parseFloat(document.getElementById('combineMiseInput')?.value) || 0;
    let cote = 1;
    for (let i = 0; i < selections.length; i++) {
      cote = cote * selections[i].cote;
    }
    gains = mise * cote;
  }
  gainsEl.textContent = gains.toFixed(2).replace('.', ',') + ' €';
}

document.querySelectorAll('.coupon-onglet').forEach(tab => {
  tab.addEventListener('click', () => {
    document.querySelectorAll('.coupon-onglet').forEach(t => t.classList.remove('active'));
    tab.classList.add('active');
    tabActif = tab.dataset.tab;
    renderCouponItems();
    calculerGains();
  });
});

document.getElementById('combineMiseInput')?.addEventListener('input', calculerGains);

document.getElementById('parierBtn')?.addEventListener('click', async () => {
  if (selections.length === 0) { alert('Veuillez sélectionner au moins un match.'); return; }

  let mise = 0;
  if (tabActif === 'simple') {
    for (let i = 0; i < selections.length; i++) {
      mise = mise + selections[i].mise;
    }
  } else {
    mise = parseFloat(document.getElementById('combineMiseInput')?.value) || 0;
  }
  if (mise <= 0) { alert('Veuillez saisir une mise.'); return; }

  if (tabActif === 'simple') {
    for (const sel of selections) {
      if (!sel.mise || sel.mise <= 0) continue;
      const parts = sel.matchLabel.split(' - ');
      const body = {
        equipe: sel.teamName,
        cote: sel.cote,
        mise: sel.mise,
        match: { team1: parts[0]?.trim(), team2: parts[1]?.trim(), cote1: sel.cote, cote2: sel.cote },
      };
      try {
        const res = await fetch(`${API_URL}/api/pari`, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(body),
        });
        if (res.status === 400) { alert('Solde insuffisant. Veuillez faire un dépôt.'); return; }
        if (!res.ok) { alert('Erreur lors du pari.'); return; }
      } catch {
        alert('Erreur réseau.');
        return;
      }
    }
  } else {
    let cote = 1;
    for (let i = 0; i < selections.length; i++) {
      cote = cote * selections[i].cote;
    }
    const equipe = selections.map(s => s.teamName).join(' + ');
    const body = {
      equipe,
      cote,
      mise,
      selections: selections.map(s => ({ equipe: s.teamName, cote: s.cote })),
      matchs: selections.map(s => {
        const parts = s.matchLabel.split(' - ');
        return { team1: parts[0]?.trim(), team2: parts[1]?.trim(), cote1: s.cote, cote2: s.cote };
      }),
    };
    try {
      const res = await fetch(`${API_URL}/api/pari`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(body),
      });
      if (res.status === 400) { alert('Solde insuffisant. Veuillez faire un dépôt.'); return; }
      if (!res.ok) { alert('Erreur lors du pari.'); return; }
    } catch {
      alert('Erreur réseau.');
      return;
    }
  }

  window.location.reload();
});

document.getElementById('panierBtn')?.addEventListener('click', () => openMesParis('en_cours'));

async function openMesParis(filtre = 'en_cours') {
  const overlay = document.getElementById('mesParis');
  if (!overlay) return;
  overlay.style.display = 'block';
  document.querySelectorAll('.mp-onglet').forEach(tab => {
    tab.classList.toggle('active', tab.dataset.filter === filtre);
  });
  await renderMesParis(filtre);
}

document.getElementById('closeMesParis')?.addEventListener('click', () => {
  const overlay = document.getElementById('mesParis');
  if (overlay) overlay.style.display = 'none';
});

document.querySelectorAll('.mp-onglet').forEach(tab => {
  tab.addEventListener('click', () => {
    document.querySelectorAll('.mp-onglet').forEach(t => t.classList.remove('active'));
    tab.classList.add('active');
    renderMesParis(tab.dataset.filter);
  });
});

async function renderMesParis(filtre) {
  const list = document.getElementById('mesParisList');
  const emptyEl = document.getElementById('mesPariEmpty');
  if (!list) return;

  let historique = [];
  try {
    const res = await fetch(`${API_URL}/api/mes-paris`);
    if (!res.ok) throw new Error('API error');
    historique = await res.json();
  } catch {
    historique = [];
  }

  const filtered = historique.filter(p => {
    if (filtre === 'en_cours') return p.statut === 'en_cours' || !p.statut;
    return p.statut === filtre;
  });

  if (filtered.length === 0) {
    list.innerHTML = '';
    list.style.display = 'none';
    if (emptyEl) emptyEl.style.display = 'flex';
    return;
  }

  list.style.display = 'flex';
  if (emptyEl) emptyEl.style.display = 'none';
  list.innerHTML = '';

  filtered.forEach(pari => {
    const card = document.createElement('div');
    card.className = 'mes-paris-card';
    pari.selections.forEach(sel => {
      const item = document.createElement('div');
      item.className = 'mes-paris-element';
      item.innerHTML = `
        <div class="mes-paris-element-gauche">
          <span class="mes-paris-element-match">${sel.typePari}</span>
          <span class="mes-paris-element-equipe${pari.statut === 'gagne' ? ' gagne' : pari.statut === 'perdu' ? ' perdu' : ''}">${sel.equipeChoisie.toUpperCase()}</span>
          <span class="mes-paris-element-type">Résultat du match (temps réglementaire)</span>
        </div>
        <span class="mes-paris-element-cote">${sel.cote}</span>
      `;
      card.appendChild(item);
    });
    list.appendChild(card);
  });
}

document.getElementById('profilBtn')?.addEventListener('click', () => { window.location.href = '/profil'; });
document.getElementById('soldeBtn')?.addEventListener('click', () => { window.location.href = '/deposer'; });
document.getElementById('deconnecterBtn')?.addEventListener('click', () => { window.location.href = '/logout'; });

document.addEventListener('DOMContentLoaded', () => {
  chargerMatchs();
});
