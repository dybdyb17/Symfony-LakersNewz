;(() => {
  const tbody = document.getElementById("nba-standings-body");
  const errBox = document.getElementById("standings-error");
  const confButtons = document.querySelectorAll(".conf-btn");
  
  if (!tbody) return;

  let allTeams = [];
  let currentFilter = "all";

  const conferences = {
    west: [
      "Lakers", "Clippers", "Warriors", "Kings", "Suns", "Mavericks", 
      "Rockets", "Grizzlies", "Pelicans", "Spurs", "Thunder", "Timberwolves",
      "Trail Blazers", "Nuggets", "Jazz"
    ],
    east: [
      "Celtics", "Nets", "Knicks", "76ers", "Raptors", "Bulls", "Cavaliers",
      "Pistons", "Pacers", "Bucks", "Hawks", "Hornets", "Heat", "Magic", "Wizards"
    ]
  };

  const showError = (msg, ...e) => {
    console.error("[Classement]", msg, ...e);
    if (errBox) {
      errBox.classList.add("show");
      errBox.innerHTML = `<i class="fa-solid fa-triangle-exclamation"></i> ${msg}`;
    }
    tbody.innerHTML = `<tr><td colspan="13" class="error-cell"> ${msg}</td></tr>`;
  };

  const getConference = (teamName) => {
    const name = teamName.toLowerCase();
    if (conferences.west.some(t => name.includes(t.toLowerCase()))) return "west";
    if (conferences.east.some(t => name.includes(t.toLowerCase()))) return "east";
    return "unknown";
  };

  const parseStandings = (data) => {
    try {
      let entries = [];
      
      if (data.children) {
        entries = data.children.flatMap(c => c?.standings?.entries || []);
      } else if (data.standings?.entries) {
        entries = data.standings.entries;
      } else if (Array.isArray(data)) {
        entries = data;
      }

      return entries.map((e, index) => {
        const t = e.team || {};
        const name = t.displayName || t.shortDisplayName || t.name || "—";
        const logo = t.logos?.[0]?.href || t.logo || "";

        const statsArr = e.stats || [];
        const s = statsArr.reduce((acc, st) => {
          acc[st.name] = st.displayValue ?? st.value ?? null;
          acc[st.name + "_raw"] = st.value;
          return acc;
        }, {});

        const wins = Number(s.wins ?? s.gamesWon ?? 0);
        const losses = Number(s.losses ?? s.gamesLost ?? 0);

        let rawPct = s.winPercent ?? s.winPct ?? s.playoffSeed ?? 0;
        if (typeof rawPct === "string") {
          rawPct = parseFloat(rawPct.replace(",", ".").replace("%", ""));
          if (rawPct > 1.5) rawPct = rawPct / 100;
        }
        if (!isFinite(rawPct) || rawPct === 0) {
          rawPct = wins + losses > 0 ? wins / (wins + losses) : 0;
        }

        const gb = s.gamesBehind ?? s.gamesBack ?? "-";
        const home = s.home ?? s.homeRecord ?? "-";
        const away = s.road ?? s.away ?? s.roadRecord ?? "-";
        
        const pf = parseFloat(s.avgPointsFor ?? s.pointsFor ?? 0);
        const pa = parseFloat(s.avgPointsAgainst ?? s.pointsAgainst ?? 0);
        const diff = isFinite(pf) && isFinite(pa) ? pf - pa : parseFloat(s.differential ?? 0);

        const streak = s.streak ?? "-";
        const l10 = s.lastTenGames ?? s.vsConf ?? "-";

        const conference = getConference(name);

        return {
          rank: index + 1,
          name,
          logo,
          wins,
          losses,
          pct: rawPct,
          gb,
          home,
          away,
          pf,
          pa,
          diff,
          streak,
          l10,
          conference
        };
      });
    } catch (error) {
      console.error("Erreur lors du parsing:", error);
      return [];
    }
  };

  const render = (teams) => {
    if (!teams || teams.length === 0) {
      tbody.innerHTML = `<tr><td colspan="13" style="padding: 2rem; text-align: center;">Aucune donnée disponible</td></tr>`;
      return;
    }

    teams.sort((a, b) => b.pct - a.pct || b.wins - a.wins);

    tbody.innerHTML = "";
    teams.forEach((team, i) => {
      const diffStr = isFinite(team.diff) ? 
        (team.diff > 0 ? "+" : "") + team.diff.toFixed(1) : "-";
      
      const pctStr = (team.pct * 100 / 100).toFixed(3).slice(1);
      
      const isLakers = team.name.toLowerCase().includes("lakers");
      const rowClass = isLakers ? 'lakers-row' : '';

      const tr = document.createElement("tr");
      tr.className = rowClass;
      tr.innerHTML = `
        <td style="font-weight: bold; color: #fcd34d;">${i + 1}</td>
        <td style="text-align: left;">
          <div class="team-cell">
            ${team.logo ? `<img src="${team.logo}" alt="${team.name}" class="team-logo">` : ''}
            <span style="${isLakers ? 'font-weight: bold; color: #fcd34d;' : ''}">${team.name}</span>
          </div>
        </td>
        <td>${team.wins}</td>
        <td>${team.losses}</td>
        <td>${pctStr}</td>
        <td>${team.gb}</td>
        <td>${team.home}</td>
        <td>${team.away}</td>
        <td>${isFinite(team.pf) ? team.pf.toFixed(1) : "-"}</td>
        <td>${isFinite(team.pa) ? team.pa.toFixed(1) : "-"}</td>
        <td>${diffStr}</td>
        <td>${team.streak}</td>
        <td>${team.l10}</td>
      `;
      tbody.appendChild(tr);
    });
  };

  const filterTeams = (conference) => {
    if (conference === "all") {
      render(allTeams);
    } else {
      const filtered = allTeams.filter(t => t.conference === conference);
      render(filtered);
    }
  };

  confButtons.forEach(btn => {
    btn.addEventListener("click", () => {
      confButtons.forEach(b => b.classList.remove("active"));
      btn.classList.add("active");
      currentFilter = btn.dataset.conf;
      filterTeams(currentFilter);
    });
  });

  async function loadStandings() {
    const urls = [
      "https://site.api.espn.com/apis/site/v2/sports/basketball/nba/standings?season=2026",
      "https://site.web.api.espn.com/apis/v2/sports/basketball/nba/standings?season=2026",
      "https://cdn.espn.com/core/nba/standings?xhr=1&season=2026"
    ];

    for (let url of urls) {
      try {
        const response = await fetch(url);
        if (!response.ok) continue;
        
        const data = await response.json();
        const teams = parseStandings(data);
        
        if (teams.length > 0) {
          allTeams = teams;
          render(allTeams);
          if (errBox) errBox.style.display = "none";
          return;
        }
      } catch (error) {
        console.error(`Erreur avec ${url}:`, error);
        continue;
      }
    }

    showError("Impossible de charger le classement nba, veuillez réessayer plus tard.");
  }

  loadStandings();
})();