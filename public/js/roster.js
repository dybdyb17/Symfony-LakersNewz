;(() => {
  const rosterGrid = document.getElementById("roster-grid");
  const errorBox = document.getElementById("roster-error");
  const positionBtns = document.querySelectorAll(".position-btn");

  if (!rosterGrid) return;

  let allPlayers = [];
  let currentFilter = "all";

  const showError = (msg) => {
    console.error("[Roster]", msg);
    if (errorBox) {
      errorBox.classList.add("show");
      errorBox.innerHTML = `<i class="fa-solid fa-triangle-exclamation"></i> ${msg}`;
    }
    rosterGrid.innerHTML = `<div class="error-roster"><p>❌ ${msg}</p></div>`;
  };

  const parsePlayers = (data) => {
    try {
      const athletes = data.team?.athletes || data.athletes || [];
      
      return athletes.map(athlete => {
        const a = athlete;
        const name = a.displayName || a.fullName || a.name || "Inconnu";
        const position = a.position?.abbreviation || a.position?.name || "N/A";
        const jersey = a.jersey || "00";
        const age = a.age || "N/A";
        
        const heightFeet = a.displayHeight || a.height || "N/A";
        const weight = a.displayWeight || a.weight || "N/A";
        
        const college = a.college?.name || a.college || "--";
        
        const headshot = a.headshot?.href || 
                        a.headshot?.url || 
                        a.athlete?.headshot?.href ||
                        "https://via.placeholder.com/300x400/552583/FDB927?text=No+Photo";
        
        const salary = a.salary ? `$${(a.salary / 1000000).toFixed(1)}M` : "N/A";

        const experience = a.experience?.years || a.experience || 0;
        const expText = experience === 0 ? "Rookie" : `${experience} an${experience > 1 ? 's' : ''}`;

        return {
          name,
          position,
          jersey,
          age,
          height: heightFeet,
          weight,
          college,
          headshot,
          salary,
          experience: expText
        };
      });
    } catch (error) {
      console.error("Erreur lors du parsing:", error);
      return [];
    }
  };

  const createPlayerCard = (player) => {
    return `
      <div class="player-card" data-position="${player.position}">
        <div class="player-card__header">
          <span class="player-jersey">#${player.jersey}</span>
          <span class="player-position">${player.position}</span>
        </div>
        
        <div class="player-card__image">
          <img src="${player.headshot}" alt="${player.name}" loading="lazy">
        </div>
        
        <div class="player-card__info">
          <h3 class="player-name">${player.name}</h3>
          
          <div class="player-stats">
            <div class="stat-item">
              <i class="fa-solid fa-calendar"></i>
              <span>${player.age} ans</span>
            </div>
            <div class="stat-item">
              <i class="fa-solid fa-ruler-vertical"></i>
              <span>${player.height}</span>
            </div>
            <div class="stat-item">
              <i class="fa-solid fa-weight-scale"></i>
              <span>${player.weight}</span>
            </div>
          </div>
          
          <div class="player-details">
            <div class="detail-row">
              <span class="label">College:</span>
              <span class="value">${player.college}</span>
            </div>
            <div class="detail-row">
              <span class="label">Expérience:</span>
              <span class="value">${player.experience}</span>
            </div>
          </div>
        </div>
      </div>
    `;
  };

  const renderPlayers = (players) => {
    if (!players || players.length === 0) {
      rosterGrid.innerHTML = '<div class="no-players"><p>Aucun joueur trouvé</p></div>';
      return;
    }

    rosterGrid.innerHTML = players.map(createPlayerCard).join("");
  };

  const filterPlayers = (position) => {
    if (position === "all") {
      renderPlayers(allPlayers);
    } else {
      const filtered = allPlayers.filter(p => p.position.includes(position));
      renderPlayers(filtered);
    }
  };

  positionBtns.forEach(btn => {
    btn.addEventListener("click", () => {
      positionBtns.forEach(b => b.classList.remove("active"));
      btn.classList.add("active");
      currentFilter = btn.dataset.position;
      filterPlayers(currentFilter);
    });
  });

  async function loadRoster() {
    const urls = [
      "https://site.api.espn.com/apis/site/v2/sports/basketball/nba/teams/13/roster",
      "https://sports.core.api.espn.com/v2/sports/basketball/leagues/nba/teams/13/athletes?limit=50"
    ];

    for (let url of urls) {
      try {
        const response = await fetch(url);
        if (!response.ok) continue;

        const data = await response.json();
        const players = parsePlayers(data);

        if (players.length > 0) {
          allPlayers = players;
          renderPlayers(allPlayers);
          if (errorBox) errorBox.classList.remove("show");
          return;
        }
      } catch (error) {
        console.error(`Erreur avec ${url}:`, error);
        continue;
      }
    }

    showError("Impossible de charger l'effectif des Lakers. Veuillez réessayer plus tard.");
  }

  loadRoster();
})();