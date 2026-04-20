;(() => {
  const rosterGrid = document.getElementById("effectif-grille");
  const errorBox = document.getElementById("effectif-erreur");
  const positionBtns = document.querySelectorAll(".bouton-poste");

  if (!rosterGrid) return;

  let allPlayers = [];
  let currentFilter = "all";

  const showError = (msg) => {
    console.error("[Roster]", msg);
    if (errorBox) {
      errorBox.classList.add("show");
      errorBox.innerHTML = `<i class="fa-solid fa-triangle-exclamation"></i> ${msg}`;
    }
    rosterGrid.innerHTML = `<div class="erreur-effectif"><p> ${msg}</p></div>`;
  };

  const parsePlayers = (data) => {
    try {
      const athletes = data.team?.athletes || data.athletes || [];

      return athletes.map(athlete => {
        const a = athlete;
        const name = a.displayName || a.fullName || a.name || "Inconnu";
        const position = a.position?.abbreviation || a.position?.name || "n/a";
        const jersey = a.jersey || "00";
        const age = a.age || "N/A";

        const heightFeet = a.displayHeight || a.height || "n/a";
        const weight = a.displayWeight || a.weight || "n/a";

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
      <div class="joueur-carte" data-position="${player.position}">
        <div class="joueur-carte-entete">
          <span class="joueur-maillot">#${player.jersey}</span>
          <span class="joueur-poste">${player.position}</span>
        </div>

        <div class="joueur-carte-image">
          <img src="${player.headshot}" alt="${player.name}" loading="lazy">
        </div>

        <div class="joueur-carte-info">
          <h3 class="joueur-nom">${player.name}</h3>

          <div class="joueur-stats">
            <div class="stat-element">
              <i class="fa-solid fa-calendar"></i>
              <span>${player.age} ans</span>
            </div>
            <div class="stat-element">
              <i class="fa-solid fa-ruler-vertical"></i>
              <span>${player.height}</span>
            </div>
            <div class="stat-element">
              <i class="fa-solid fa-weight-scale"></i>
              <span>${player.weight}</span>
            </div>
          </div>

          <div class="joueur-details">
            <div class="detail-ligne">
              <span class="label">College:</span>
              <span class="value">${player.college}</span>
            </div>
            <div class="detail-ligne">
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
      rosterGrid.innerHTML = '<div class="aucun-joueur"><p>Aucun joueur trouvé</p></div>';
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

  positionBtns.forEach(bouton => {
    bouton.addEventListener("click", () => {
      positionBtns.forEach(b => b.classList.remove("active"));
      bouton.classList.add("active");
      currentFilter = bouton.dataset.position;
      filterPlayers(currentFilter);
    });
  });

  async function loadRoster() {
    const urls = [
      "http://127.0.0.1:8000/api/cache/roster"
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
