let session = window.isLoggedIn ? { id: window.userId, solde: window.userSolde, pseudo: window.userPseudo, email: window.userEmail, isLoggedIn: true } : null;
let solde = window.userSolde || 0;
let selections = [];
let tabActif = 'simple';
const HISTORIQUE_KEY = 'lakersNewz_paris';
const API_URL = 'http://127.0.0.1:8000';

async function envoyerParisAPI(paris) {
  // paris : array de { equipe, cote, mise }
  for (const pari of paris) {
    const estCombine = paris.length === 1 && pari.equipe.includes(' + ');

    const body = {
      equipe: pari.equipe,
      cote: pari.cote,
      mise: pari.mise,
    };

    if (estCombine) {
      body.selections = selections.map(sel => ({
        equipe: sel.teamName,
        cote: sel.cote,
      }));
    }

    const res = await fetch(`${API_URL}/api/pari`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify(body),
    });
    if (res.status === 400) {
      const err = new Error('solde_insuffisant');
      err.status = 400;
      throw err;
    }
    if (!res.ok) throw new Error('API error');
    const data = await res.json();
    if (data.solde !== undefined) {
      solde = data.solde;
      saveSolde();
      updateSoldeUI();
    }
  }
}

function getHistoriqueParis() {
  return JSON.parse(localStorage.getItem(HISTORIQUE_KEY)) || [];
}

function saveHistoriqueParis(list) {
  localStorage.setItem(HISTORIQUE_KEY, JSON.stringify(list));
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

function updateSoldeUI() {
  const s = solde.toFixed(2).replace('.', ',') + ' €';
  ['soldeDisplay', 'mesParisSolde'].forEach(id => {
    const el = document.getElementById(id);
    if (el) el.textContent = s;
  });
  const profilSoldeEl = document.getElementById('profilSolde');
  if (profilSoldeEl) profilSoldeEl.textContent = s;
}

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
  { id:7, date:'02:30, 05 février', team1:'Phoenix Suns', team2:'Memphis Grizzlies', cote1:1.65, cote2:2.30 },
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
    <div class="match-card__top">
      <span class="match-card__date">${match.date}</span>
      <span class="match-card__live">— — —</span>
    </div>
    <div class="match-card__teams">
      <div class="match-team">
        <div class="match-team__logo">
          <img src="${logo1}" alt="${match.team1}"
            onerror="this.src='https://via.placeholder.com/50x50/552583/FDB927?text=${match.team1.charAt(0)}'">
        </div>
        <span class="match-team__name">${match.team1}</span>
      </div>
      <span class="match-vs">VS</span>
      <div class="match-team">
        <div class="match-team__logo">
          <img src="${logo2}" alt="${match.team2}"
            onerror="this.src='https://via.placeholder.com/50x50/552583/FDB927?text=${match.team2.charAt(0)}'">
        </div>
        <span class="match-team__name">${match.team2}</span>
      </div>
    </div>
    <div class="match-card__cotes">
      <button class="cote-btn"
        data-match-id="${match.id}"
        data-team="${match.team1}"
        data-cote="${match.cote1}"
        data-label="${match.team1} - ${match.team2}">
        ${abrev1} (${match.cote1})
      </button>
      <button class="voir-options-btn" type="button">voir plus d'options</button>
      <button class="cote-btn"
        data-match-id="${match.id}"
        data-team="${match.team2}"
        data-cote="${match.cote2}"
        data-label="${match.team1} - ${match.team2}">
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
    const res = await fetch('https://site.api.espn.com/apis/site/v2/sports/basketball/nba/scoreboard');
    if (!res.ok) throw new Error('API ko');
    const data = await res.json();
    const events = (data.events || []).slice(0, 12);

    if (events.length === 0) throw new Error('Aucun match');

    const matchs = events.map((event, i) => {
      const comp = event.competitions[0];
      const t1 = comp.competitors[0];
      const t2 = comp.competitors[1];
      const dateObj = new Date(event.date);
      const dateStr = dateObj.toLocaleString('fr-FR', {
        hour: '2-digit', minute: '2-digit', day: 'numeric', month: 'long'
      });
      return {
        id: i + 1,
        date: dateStr,
        dateObj,
        team1: t1.team.displayName,
        team2: t2.team.displayName,
        cote1: genererCote(1.75),
        cote2: genererCote(1.90),
      };
    });

    const matchsFuturs = matchs.filter(m => m.dateObj > new Date());

    if (matchsFuturs.length === 0) {
      container.innerHTML = '<p class="no-matchs-msg">Aucun match disponible pour le moment, revenez plus tard !</p>';
      return;
    }

    afficherMatchs(matchsFuturs, container);
  } catch {
    afficherMatchs(MATCHS_DEMO, container);
  }
}

function afficherMatchs(matchs, container) {
  container.innerHTML = '';
  matchs.forEach(m => container.appendChild(renderMatchCard(m)));
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
      document.querySelectorAll(`.cote-btn[data-match-id="${matchId}"]`)
        .forEach(b => b.classList.remove('selected'));
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

  updateMobileBandeau();

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
      item.className = 'coupon-item';
      item.innerHTML = `
        <div class="coupon-item__header">
          <span class="coupon-item__match">${sel.matchLabel}</span>
          <span class="coupon-item__cote">${sel.cote}</span>
          <button class="coupon-item__remove" data-idx="${idx}">×</button>
        </div>
        <div class="coupon-item__team">${sel.teamName}</div>
        <div class="coupon-item__type">Vainqueur du match</div>
        <div class="coupon-item__mise-row">
          <input type="number" class="coupon-item__mise" data-idx="${idx}"
            placeholder="Mise" min="1" value="${sel.mise || ''}">
          <span class="coupon-item__mise-currency">€</span>
        </div>
      `;
      item.querySelector('.coupon-item__remove').addEventListener('click', () => removeSelection(idx));
      item.querySelector('.coupon-item__mise').addEventListener('input', e => {
        selections[idx].mise = parseFloat(e.target.value) || 0;
        calculerGains();
      });
      itemsEl.appendChild(item);
    });

  } else {
    if (combineMiseEl) combineMiseEl.style.display = 'flex';

    selections.forEach((sel, idx) => {
      const item = document.createElement('div');
      item.className = 'coupon-item';
      item.innerHTML = `
        <div class="coupon-item__header">
          <span class="coupon-item__match">${sel.matchLabel}</span>
          <span class="coupon-item__cote">${sel.cote}</span>
          <button class="coupon-item__remove" data-idx="${idx}">×</button>
        </div>
        <div class="coupon-item__team">${sel.teamName}</div>
        <div class="coupon-item__type">Vainqueur du match</div>
      `;
      item.querySelector('.coupon-item__remove').addEventListener('click', () => removeSelection(idx));
      itemsEl.appendChild(item);
    });

    const coteTotale = selections.reduce((acc, s) => acc * s.cote, 1);
    const combineCoteEl = document.getElementById('combineCote');
    if (combineCoteEl) combineCoteEl.textContent = coteTotale.toFixed(2);
  }
}

function removeSelection(idx) {
  const sel = selections[idx];
  document.querySelectorAll(`.cote-btn[data-match-id="${sel.matchId}"]`)
    .forEach(b => b.classList.remove('selected'));
  selections.splice(idx, 1);
  updateCoupon();
}

function calculerGains() {
  const gainsEl = document.getElementById('gainsAmount');
  if (!gainsEl) return;

  let gains = 0;
  if (tabActif === 'simple') {
    gains = selections.reduce((acc, s) => acc + s.mise * s.cote, 0);
  } else {
    const mise = parseFloat(document.getElementById('combineMiseInput')?.value) || 0;
    const cote = selections.reduce((acc, s) => acc * s.cote, 1);
    gains = mise * cote;
  }

  gainsEl.textContent = gains.toFixed(2).replace('.', ',') + ' €';
}

document.querySelectorAll('.coupon-tab').forEach(tab => {
  tab.addEventListener('click', () => {
    document.querySelectorAll('.coupon-tab').forEach(t => t.classList.remove('active'));
    tab.classList.add('active');
    tabActif = tab.dataset.tab;
    renderCouponItems();
    calculerGains();
  });
});

document.getElementById('combineMiseInput')?.addEventListener('input', calculerGains);

let mcvTabActif = 'combine';

function updateMobileBandeau() {
  const count = selections.length;
  const bar = document.getElementById('mobilePariBar');
  if (!bar) return;

  if (count > 0 && window.innerWidth <= 900) {
    bar.style.display = 'flex';
    const teams = selections.map(s => s.teamName).join(', ');
    const teamsEl = document.getElementById('mobilePariTeams');
    const detailEl = document.getElementById('mobilePariDetail');
    const coteEl = document.getElementById('mobilePariCote');
    if (teamsEl) teamsEl.textContent = teams;
    if (detailEl) detailEl.textContent = count > 1 ? `Combiné (${count})` : 'Simple (1)';
    if (coteEl) {
      const cote = selections.reduce((acc, s) => acc * s.cote, 1);
      coteEl.textContent = cote.toFixed(2);
    }
  } else {
    bar.style.display = 'none';
  }
}

function openMobileCoupon() {
  const view = document.getElementById('mobileCouponView');
  if (!view) return;
  view.style.display = 'flex';
  renderMcvList();
  updateMcvFooter();
  const soldeEl = document.getElementById('mcvSolde');
  if (soldeEl) soldeEl.textContent = '+ ' + solde.toFixed(2).replace('.', ',') + ' €';
}

function closeMobileCoupon() {
  const view = document.getElementById('mobileCouponView');
  if (view) view.style.display = 'none';
}

function renderMcvList() {
  const list = document.getElementById('mcvList');
  if (!list) return;
  list.innerHTML = '';

  if (mcvTabActif === 'combine') {
    const bloc = document.createElement('div');
    bloc.className = 'mcv-bloc-combine';
    selections.forEach((sel, idx) => {
      const item = document.createElement('div');
      item.className = 'mcv-item mcv-item--combine';
      item.innerHTML = `
        <div class="mcv-item__left">
          <span class="mcv-item__match"><i class="fa-solid fa-basketball"></i>${sel.matchLabel}</span>
          <span class="mcv-item__team">${sel.teamName}</span>
          <span class="mcv-item__type">Résultat du match (temps réglementaire)</span>
        </div>
        <div class="mcv-item__right">
          <button class="mcv-item__remove" data-idx="${idx}">×</button>
          <span class="mcv-item__cote">${sel.cote}</span>
        </div>
      `;
      item.querySelector('.mcv-item__remove').addEventListener('click', () => {
        removeSelection(idx);
        renderMcvList();
        updateMcvFooter();
        if (selections.length === 0) closeMobileCoupon();
      });
      bloc.appendChild(item);
    });
    list.appendChild(bloc);

  } else {
    selections.forEach((sel, idx) => {
      const item = document.createElement('div');
      item.className = 'mcv-item mcv-item--simple';
      item.innerHTML = `
        <div class="mcv-item__left">
          <span class="mcv-item__match"><i class="fa-solid fa-basketball"></i>${sel.matchLabel}</span>
          <span class="mcv-item__team">${sel.teamName}</span>
          <span class="mcv-item__type">Résultat du match (temps réglementaire)</span>
        </div>
        <div class="mcv-item__right">
          <button class="mcv-item__remove" data-idx="${idx}">×</button>
          <span class="mcv-item__cote">${sel.cote}</span>
        </div>
        <div class="mcv-item__mise-row">
          <input type="number" class="mcv-item__mise-input" data-idx="${idx}" 
            placeholder="0" min="0" value="${sel.mise || ''}">
          <span class="mcv-item__mise-euro">€</span>
        </div>
      `;
      item.querySelector('.mcv-item__remove').addEventListener('click', () => {
        removeSelection(idx);
        renderMcvList();
        updateMcvFooter();
        if (selections.length === 0) closeMobileCoupon();
      });
      item.querySelector('.mcv-item__mise-input').addEventListener('input', e => {
        selections[idx].mise = parseFloat(e.target.value) || 0;
        updateMcvFooter();
      });
      list.appendChild(item);
    });
  }
}

function updateMcvFooter() {
  const cote = selections.reduce((acc, s) => acc * s.cote, 1);
  const coteEl = document.getElementById('mcvCoteBadge');
  if (coteEl) coteEl.textContent = cote.toFixed(2);

  let mise = 0;
  let gains = 0;

  if (mcvTabActif === 'combine') {
    mise = parseFloat(document.getElementById('mcvMise')?.value) || 0;
    gains = mise * cote;
  } else {
    mise = selections.reduce((acc, s) => acc + (s.mise || 0), 0);
    gains = selections.reduce((acc, s) => acc + (s.mise || 0) * s.cote, 0);
  }

  const miseGlobal = document.querySelector('.mcv-mise-global');
  const miseSimple = document.querySelector('.mcv-parier-simple-row');
  if (miseGlobal) miseGlobal.style.display = mcvTabActif === 'combine' ? 'flex' : 'none';
  if (miseSimple) miseSimple.style.display = mcvTabActif === 'simple' ? 'flex' : 'none';

  const pariMontantSimple = document.getElementById('mcvPariMontantSimple');
  if (pariMontantSimple) pariMontantSimple.textContent = mise > 0 ? `Parier ${mise} €` : 'Parier';

  const gainsEl = document.getElementById('mcvGains');
  if (gainsEl) gainsEl.textContent = gains.toFixed(2).replace('.', ',') + ' €';

  const pariMontant = document.getElementById('mcvPariMontant');
  if (pariMontant) pariMontant.textContent = mise > 0 ? `Parier ${mise} €` : 'Parier';
}

document.getElementById('mobilePariBar')?.addEventListener('click', openMobileCoupon);

document.getElementById('mcvClose')?.addEventListener('click', closeMobileCoupon);

document.getElementById('mcvTabSimple')?.addEventListener('click', () => {
  mcvTabActif = 'simple';
  document.getElementById('mcvTabSimple')?.classList.add('mcv-tab--active');
  document.getElementById('mcvTabCombine')?.classList.remove('mcv-tab--active');
  renderMcvList();
  updateMcvFooter();
});

document.getElementById('mcvTabCombine')?.addEventListener('click', () => {
  mcvTabActif = 'combine';
  document.getElementById('mcvTabCombine')?.classList.add('mcv-tab--active');
  document.getElementById('mcvTabSimple')?.classList.remove('mcv-tab--active');
  renderMcvList();
  updateMcvFooter();
});

document.getElementById('mcvMise')?.addEventListener('input', updateMcvFooter);

document.getElementById('mcvPariSimpleBtn')?.addEventListener('click', async () => {
  const mise = selections.reduce((acc, s) => acc + (s.mise || 0), 0);
  if (mise <= 0) { alert('Veuillez saisir une mise pour au moins un match.'); return; }
  if (mise > solde) {
    showModal('depotNecessaireModal');
    return;
  }

  const paris = selections.map(s => ({ equipe: s.teamName, cote: s.cote, mise: s.mise || 0 }));
  const selectionsSnapshot = [...selections];

  try {
    await envoyerParisAPI(paris);
  } catch (err) {
    if (err.status === 400) { showModal('depotNecessaireModal'); return; }
    // Fallback localStorage
    solde -= mise;
    saveSolde();
    updateSoldeUI();
    const historique = getHistoriqueParis();
    historique.push({
      id: Date.now(),
      date: new Date().toLocaleDateString('fr-FR'),
      selections: selectionsSnapshot,
      mise,
      gains: selectionsSnapshot.reduce((acc, s) => acc + (s.mise || 0) * s.cote, 0),
      statut: 'encours',
      type: 'simple',
    });
    saveHistoriqueParis(historique);
  }

  selections.forEach(sel => {
    document.querySelectorAll(`.cote-btn[data-match-id="${sel.matchId}"]`)
      .forEach(b => b.classList.remove('selected'));
  });
  selections.length = 0;
  closeMobileCoupon();
  updateCoupon();
  updateMobileBandeau();
});

document.getElementById('mcvPariBtn')?.addEventListener('click', async () => {
  const mise = parseFloat(document.getElementById('mcvMise')?.value) || 0;
  if (mise <= 0) { alert('Veuillez saisir une mise.'); return; }
  if (mise > solde) {
    showModal('depotNecessaireModal');
    return;
  }

  const cote = selections.reduce((acc, s) => acc * s.cote, 1);
  const equipe = selections.map(s => s.teamName).join(' + ');
  const selectionsSnapshot = [...selections];

  try {
    await envoyerParisAPI([{ equipe, cote, mise }]);
  } catch (err) {
    if (err.status === 400) { showModal('depotNecessaireModal'); return; }
    // Fallback localStorage
    solde -= mise;
    saveSolde();
    updateSoldeUI();
    const historique = getHistoriqueParis();
    historique.push({
      id: Date.now(),
      date: new Date().toLocaleDateString('fr-FR'),
      selections: selectionsSnapshot,
      mise,
      gains: parseFloat((mise * cote).toFixed(2)),
      statut: 'encours',
      type: mcvTabActif,
    });
    saveHistoriqueParis(historique);
  }

  selections.forEach(sel => {
    document.querySelectorAll(`.cote-btn[data-match-id="${sel.matchId}"]`)
      .forEach(b => b.classList.remove('selected'));
  });
  selections.length = 0;
  closeMobileCoupon();
  updateCoupon();
  updateMobileBandeau();
});

document.getElementById('parierBtn')?.addEventListener('click', async () => {
  if (selections.length === 0) { alert('Veuillez sélectionner au moins un match.'); return; }

  let mise = 0;
  if (tabActif === 'simple') {
    mise = selections.reduce((acc, s) => acc + s.mise, 0);
  } else {
    mise = parseFloat(document.getElementById('combineMiseInput')?.value) || 0;
  }

  if (mise <= 0) { alert('Veuillez saisir une mise.'); return; }
  if (mise > solde) { showModal('depotNecessaireModal'); return; }

  const selectionsSnapshot = [...selections];
  const gainsVal = parseFloat(document.getElementById('gainsAmount').textContent) || 0;

  let paris;
  if (tabActif === 'simple') {
    paris = selectionsSnapshot.map(s => ({ equipe: s.teamName, cote: s.cote, mise: s.mise }));
  } else {
    const cote = selectionsSnapshot.reduce((acc, s) => acc * s.cote, 1);
    const equipe = selectionsSnapshot.map(s => s.teamName).join(' + ');
    paris = [{ equipe, cote, mise }];
  }

  try {
    await envoyerParisAPI(paris);
  } catch (err) {
    if (err.status === 400) { showModal('depotNecessaireModal'); return; }
    // Fallback localStorage
    solde -= mise;
    saveSolde();
    updateSoldeUI();
    const historique = getHistoriqueParis();
    historique.push({
      id: Date.now(),
      date: new Date().toLocaleDateString('fr-FR'),
      selections: selectionsSnapshot,
      mise,
      gains: gainsVal,
      statut: 'encours',
      type: tabActif,
    });
    saveHistoriqueParis(historique);
  }

  selections.forEach(sel => {
    document.querySelectorAll(`.cote-btn[data-match-id="${sel.matchId}"]`)
      .forEach(b => b.classList.remove('selected'));
  });
  selections = [];
  updateCoupon();

  const pariBetText = document.getElementById('pariBetText');
  if (pariBetText) pariBetText.textContent = `Pari de ${mise.toFixed(2)}€ enregistré avec succès.`;
  showModal('pariBetModal');
});

document.getElementById('panierBtn')?.addEventListener('click', () => {
  openMesParis('encours');
});

async function openMesParis(filtre = 'encours') {
  const overlay = document.getElementById('mesParis');
  if (!overlay) return;
  overlay.style.display = 'block';

  document.querySelectorAll('.mp-tab').forEach(tab => {
    tab.classList.toggle('active', tab.dataset.filter === filtre);
  });

  await renderMesParis(filtre);
  updateSoldeUI();
}

document.getElementById('closeMesParis')?.addEventListener('click', () => {
  const overlay = document.getElementById('mesParis');
  if (overlay) overlay.style.display = 'none';
});

document.getElementById('voirTousParis')?.addEventListener('click', () => {
  const overlay = document.getElementById('mesParis');
  if (overlay) overlay.style.display = 'none';
});

document.querySelectorAll('.mp-tab').forEach(tab => {
  tab.addEventListener('click', () => {
    document.querySelectorAll('.mp-tab').forEach(t => t.classList.remove('active'));
    tab.classList.add('active');
    renderMesParis(tab.dataset.filter);
  });
});

async function renderMesParis(filtre) {
  const list = document.getElementById('mesParisList');
  const emptyEl = document.getElementById('mesPariEmpty');
  if (!list) return;

  let historique = [];
  if (session && session.id) {
    try {
      const res = await fetch(`${API_URL}/api/mes-paris`);
      if (!res.ok) throw new Error('API error');
      historique = await res.json();
    } catch {
      historique = getHistoriqueParis();
    }
  } else {
    historique = getHistoriqueParis();
  }

  const filtered = historique.filter(p => {
    if (filtre === 'encours') return p.statut === 'encours' || !p.statut;
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
      item.className = 'mes-paris-item';
      item.innerHTML = `
        <div class="mes-paris-item__left">
          <span class="mes-paris-item__match">${sel.matchLabel}</span>
          <span class="mes-paris-item__team${pari.statut === 'gagne' ? ' gagne' : pari.statut === 'perdu' ? ' perdu' : ''}">${sel.teamName.toUpperCase()}</span>
          <span class="mes-paris-item__type">Résultat du match (temps réglementaire)</span>
        </div>
        <span class="mes-paris-item__cote">${sel.cote}</span>
      `;
      card.appendChild(item);
    });

    list.appendChild(card);
  });
}

function showModal(id) {
  const el = document.getElementById(id);
  if (el) el.classList.add('show');
}

function hideModal(id) {
  const el = document.getElementById(id);
  if (el) el.classList.remove('show');
}

document.getElementById('closeProfilModal')?.addEventListener('click', () => hideModal('profilModal'));
document.getElementById('closeDeposerModal')?.addEventListener('click', () => hideModal('deposerModal'));
document.getElementById('closeRetirerModal')?.addEventListener('click', () => hideModal('retirerModal'));
document.getElementById('okDeposerBtn')?.addEventListener('click', () => hideModal('deposerConfirmModal'));
document.getElementById('okSoldeBtn')?.addEventListener('click', () => hideModal('soldeInsuffisantModal'));
document.getElementById('okPariBetBtn')?.addEventListener('click', () => hideModal('pariBetModal'));

document.getElementById('depotFaireBtn')?.addEventListener('click', () => {
  window.location.href = '/deposer';
});
document.getElementById('depotPlusTardBtn')?.addEventListener('click', () => {
  hideModal('depotNecessaireModal');
});
document.getElementById('okRetirerBtn')?.addEventListener('click', () => {
  hideModal('retirerConfirmModal');
  showModal('profilModal');
});

document.querySelectorAll('.modal-overlay').forEach(overlay => {
  overlay.addEventListener('click', e => {
    if (e.target === overlay) overlay.classList.remove('show');
  });
});

document.getElementById('profilBtn')?.addEventListener('click', () => {
  window.location.href = '/profil';
});

document.getElementById('openDeposerBtn')?.addEventListener('click', () => {
  window.location.href = '/deposer';
});

document.getElementById('openRetirerBtn')?.addEventListener('click', () => {
  window.location.href = '/retirer';
});

document.getElementById('mesParisProfil')?.addEventListener('click', () => {
  hideModal('profilModal');
  openMesParis('encours');
});

document.getElementById('deconnecterBtn')?.addEventListener('click', () => {
  window.location.href = '/deconnexion';
});

document.getElementById('soldeBtn')?.addEventListener('click', () => {
  window.location.href = '/deposer';
});

document.querySelectorAll('#deposerModal .preset-btn').forEach(btn => {
  btn.addEventListener('click', () => {
    const input = document.getElementById('deposerMontant');
    if (input) input.value = btn.dataset.amount;
    document.querySelectorAll('#deposerModal .preset-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
  });
});

document.getElementById('continuerDeposerBtn')?.addEventListener('click', () => {
  const carteForm = document.getElementById('carteForm');
  const continuerBtn = document.getElementById('continuerDeposerBtn');
  if (carteForm && continuerBtn) {
    carteForm.style.display = 'block';
    continuerBtn.style.display = 'none';
  }
});

document.getElementById('validerCarteBtn')?.addEventListener('click', () => {
  const montant = parseFloat(document.getElementById('deposerMontant')?.value) || 0;
  if (montant <= 0) { alert('Montant invalide.'); return; }

  solde += montant;
  saveSolde();
  updateSoldeUI();

  const carteForm = document.getElementById('carteForm');
  const continuerBtn = document.getElementById('continuerDeposerBtn');
  if (carteForm) carteForm.style.display = 'none';
  if (continuerBtn) continuerBtn.style.display = 'block';
  const montantEl = document.getElementById('deposerMontant');
  if (montantEl) montantEl.value = 0;
  document.querySelectorAll('#deposerModal .preset-btn').forEach(b => b.classList.remove('active'));

  hideModal('deposerModal');
  const confirmEl = document.getElementById('deposerConfirmAmount');
  if (confirmEl) confirmEl.textContent = montant.toFixed(2).replace('.', ',') + ' €';
  showModal('deposerConfirmModal');
});

document.getElementById('annulerCarteBtn')?.addEventListener('click', () => {
  const carteForm = document.getElementById('carteForm');
  const continuerBtn = document.getElementById('continuerDeposerBtn');
  if (carteForm) carteForm.style.display = 'none';
  if (continuerBtn) continuerBtn.style.display = 'block';
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

document.querySelectorAll('.retirer-preset').forEach(btn => {
  btn.addEventListener('click', () => {
    const input = document.getElementById('retirerMontant');
    if (input) input.value = btn.dataset.amount;
    document.querySelectorAll('.retirer-preset').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
  });
});

document.getElementById('continuerRetirerBtn')?.addEventListener('click', () => {
  const montant = parseFloat(document.getElementById('retirerMontant')?.value) || 0;
  if (montant <= 0) { alert('Montant invalide.'); return; }

  if (montant > solde) {
    hideModal('retirerModal');
    showModal('soldeInsuffisantModal');
    return;
  }

  solde -= montant;
  saveSolde();
  updateSoldeUI();

  hideModal('retirerModal');
  const confirmEl = document.getElementById('retirerConfirmAmount');
  if (confirmEl) confirmEl.textContent = montant.toFixed(2).replace('.', ',') + ' €';
  showModal('retirerConfirmModal');
});

document.addEventListener('DOMContentLoaded', () => {
  updateSoldeUI();
  chargerMatchs();
});