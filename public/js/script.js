const burgerBtn = document.querySelector('.burger-btn');
const sidebar = document.querySelector('.sidebar');
const closeBtn = document.querySelector('.close-btn');

if (burgerBtn && sidebar) {
  burgerBtn.addEventListener('click', () => {
    sidebar.classList.add('open');
    burgerBtn.classList.add('active');
  });
}

if (closeBtn && sidebar) {
  closeBtn.addEventListener('click', () => {
    sidebar.classList.remove('open');
    burgerBtn.classList.remove('active');
  });
}


const settingsDropdown = document.querySelector('.settings-dropdown');
const settingsBtn = document.querySelector('.settings-btn');
const dropdownMenu = document.querySelector('.dropdown-menu');

if (settingsBtn && settingsDropdown) {
  settingsBtn.addEventListener('click', (e) => {
    e.stopPropagation();
    settingsDropdown.classList.toggle('active');
  });

  document.addEventListener('click', (e) => {
    if (!settingsDropdown.contains(e.target)) {
      settingsDropdown.classList.remove('active');
    }
  });

  if (dropdownMenu) {
    dropdownMenu.addEventListener('click', (e) => {
      e.stopPropagation();
    });
  }
}


function updateDropdownMenu() {
  const dropdownMenu = document.querySelector('.dropdown-menu');
  if (!dropdownMenu) return;

  const isLoggedIn = window.isLoggedIn || false;

  if (isLoggedIn) {
    dropdownMenu.innerHTML = `
      <div class="dropdown-item user-status">
        <i class="fa-solid fa-circle-check"></i>
        <span>Vous êtes connecté</span>
      </div>
      <button class="dropdown-item" onclick="logout()">
        <i class="fa-solid fa-right-from-bracket"></i>
        <span>Se déconnecter</span>
      </button>
      <div class="dropdown-divider"></div>
      <a href="/contact" class="dropdown-item">
        <i class="fa-solid fa-envelope"></i>
        <span>Contact</span>
      </a>
    `;
  } else {
    dropdownMenu.innerHTML = `
      <a href="/inscription" class="dropdown-item">
        <i class="fa-solid fa-user-plus"></i>
        <span>S'inscrire</span>
      </a>
      <a href="/connexion" class="dropdown-item">
        <i class="fa-solid fa-right-to-bracket"></i>
        <span>Se connecter</span>
      </a>
      <div class="dropdown-divider"></div>
      <a href="/contact" class="dropdown-item">
        <i class="fa-solid fa-envelope"></i>
        <span>Contact</span>
      </a>
    `;
  }
}

function logout() {
  window.location.href = '/deconnexion';
}

function updateSidebar() {
  const navList = document.querySelector('.sidebar .nav ul');
  if (!navList) return;

  const isLoggedIn = window.isLoggedIn || false;

  const currentPath = window.location.pathname;

  if (isLoggedIn) {
    navList.innerHTML = `
      <li><a href="/" class="${currentPath === '/' ? 'active' : ''}"><i class="fa-solid fa-basketball"></i>News</a></li>
      <li><a href="/calendrier" class="${currentPath === '/calendrier' ? 'active' : ''}"><i class="fa-solid fa-calendar"></i>Calendrier</a></li>
      <li><a href="/classement" class="${currentPath === '/classement' ? 'active' : ''}"><i class="fa-solid fa-ranking-star"></i>Classement</a></li>
      <li><a href="/roster" class="${currentPath === '/roster' ? 'active' : ''}"><i class="fa-solid fa-star"></i>Roster</a></li>
      <li><a href="/paris" class="${currentPath === '/paris' ? 'active' : ''}"><i class="fa-solid fa-dice"></i>Paris</a></li>
    `;
  } else {
    navList.innerHTML = `
      <li><a href="/" class="${currentPath === '/' ? 'active' : ''}"><i class="fa-solid fa-basketball"></i>News</a></li>
      <li><a href="/calendrier" class="${currentPath === '/calendrier' ? 'active' : ''}"><i class="fa-solid fa-calendar"></i>Calendrier</a></li>
      <li><a href="/classement" class="${currentPath === '/classement' ? 'active' : ''}"><i class="fa-solid fa-ranking-star"></i>Classement</a></li>
      <li><a href="/roster" class="${currentPath === '/roster' ? 'active' : ''}"><i class="fa-solid fa-star"></i>Roster</a></li>
    `;
  }
}

document.addEventListener('DOMContentLoaded', () => {
  updateDropdownMenu();
  updateSidebar();
});


function normalize(text) {
  return text.normalize("NFD").replace(/[\u0300-\u036f]/g, "").toLowerCase();
}

document.addEventListener('DOMContentLoaded', () => {
  const tbody = document.querySelector('.calendar-table tbody');
  const monthSelect = document.querySelector('select:nth-of-type(1)');
  let allMatches = [];

  if (tbody) {
    fetch('https://site.api.espn.com/apis/site/v2/sports/basketball/nba/teams/13/schedule')
      .then(response => response.json())
      .then(data => {
        const events = data.events || [];

        allMatches = events.map(event => {
          const comp = event.competitions[0];
          const competitors = comp.competitors;
          const lakers = competitors.find(c => c.team.id === '13');
          const opponent = competitors.find(c => c.team.id !== '13');

          const homeAway = lakers.homeAway === 'home' ? 'vs' : '@';
          const matchDate = new Date(event.date);

          const dateFormatted = matchDate.toLocaleDateString('fr-FR', {
            weekday: 'short', day: 'numeric', month: 'short'
          });

          const monthLong = normalize(matchDate.toLocaleString('fr-FR', { month: 'long' }));

          const timeFormatted = matchDate.toLocaleTimeString('fr-FR', {
            hour: '2-digit',
            minute: '2-digit',
            timeZone: 'America/Los_Angeles'
          });

          const venue = comp.venue
            ? comp.venue.fullName + (comp.venue.address?.city ? ", " + comp.venue.address.city : "")
            : "N/A";

          let scoreDisplay = '-';
          let resultLetter = '';
          if (lakers.score && opponent.score) {
            scoreDisplay = `${lakers.score.displayValue} — ${opponent.score.displayValue}`;
            resultLetter = lakers.winner ? 'W' : 'L';
          }

          return {
            dateObj: matchDate,
            date: dateFormatted,
            month: monthLong,
            time: timeFormatted,
            opponent: opponent.team.displayName,
            logo: opponent.team.logos[0].href,
            homeAway,
            venue,
            score: scoreDisplay,
            resultLetter
          };
        });

        displayMatches(allMatches);
      })
      .catch(error => console.error('Erreur lors du chargement du calendrier :', error));

    function displayMatches(matches) {
      tbody.innerHTML = "";
      matches.forEach(m => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
          <td>${m.date}</td>
          <td>${m.time}</td>
          <td>
            <span class="location">${m.homeAway}</span>
            <img src="${m.logo}" alt="${m.opponent}" class="logo">
            ${m.opponent}
          </td>
          <td>${m.venue}</td>
          <td>
            <span class="result ${m.resultLetter === 'W' ? 'win' : m.resultLetter === 'L' ? 'loss' : ''}">
              ${m.score} ${m.resultLetter}
            </span>
          </td>
        `;
        tbody.appendChild(tr);
      });
    }

    if (monthSelect) {
      monthSelect.addEventListener('change', () => {
        const value = normalize(monthSelect.value);

        if (value === "all") {
          displayMatches(allMatches);
          return;
        }

        const filtered = allMatches.filter(m => m.month.includes(value));
        displayMatches(filtered);
      });
    }
  }
});
