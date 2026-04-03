const burgerBtn = document.querySelector('.bouton-menu');
const menuLateral = document.querySelector('.menu-lateral');
const closeBtn = document.querySelector('.bouton-fermer');

if (burgerBtn && menuLateral) {
  burgerBtn.addEventListener('click', () => {
    menuLateral.classList.add('open');
    burgerBtn.classList.add('active');
  });
}

if (closeBtn && menuLateral) {
  closeBtn.addEventListener('click', () => {
    menuLateral.classList.remove('open');
    burgerBtn.classList.remove('active');
  });
}


const settingsDropdown = document.querySelector('.reglages-menu');
const settingsBtn = document.querySelector('.bouton-reglages');
const dropdownMenu = document.querySelector('.sous-menu');

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


function logout() {
  window.location.href = '/logout';
}


function normalize(text) {
  return text.normalize("NFD").replace(/[\u0300-\u036f]/g, "").toLowerCase();
}

document.addEventListener('DOMContentLoaded', () => {
  const tbody = document.querySelector('.calendrier-tableau tbody');
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
            <span class="resultat ${m.resultLetter === 'W' ? 'victoire' : m.resultLetter === 'L' ? 'defaite' : ''}">
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
