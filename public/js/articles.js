const ARTICLES_FALLBACK = [
  {
    image: 'https://images.unsplash.com/photo-1546519638-68e109498ffc?w=800&h=600&fit=crop',
    categorie: 'News',
    titre: 'LeBron James franchit la barre des 40 000 points en carrière',
    contenu: 'Le King continue d\'écrire l\'histoire de la NBA avec ce nouveau record historique qui le place encore plus haut dans les annales du basket...',
    auteur: 'Lakers Newz',
    createdAt: '2025-01-05T00:00:00',
  },
  {
    image: 'https://images.unsplash.com/photo-1608245449230-4ac19066d2d0?w=800&h=600&fit=crop',
    categorie: 'News',
    titre: 'Anthony Davis élu joueur défensif du mois de décembre',
    contenu: 'L\'intérieur des Lakers confirme son statut de meilleur défenseur de la ligue avec cette distinction amplement méritée...',
    auteur: 'Lakers Newz',
    createdAt: '2025-01-04T00:00:00',
  },
  {
    image: 'https://images.unsplash.com/photo-1519861531473-9200262188bf?w=800&h=600&fit=crop',
    categorie: 'Rumeurs',
    titre: 'Rumeur : Les Lakers sur la piste d\'un meneur All-Star',
    contenu: 'Selon plusieurs sources, le front office californien serait en discussions avancées pour renforcer le poste de meneur avant la trade deadline...',
    auteur: 'Lakers Newz',
    createdAt: '2025-01-03T00:00:00',
  },
  {
    image: 'https://images.unsplash.com/photo-1504450758481-7338eba7524a?w=800&h=600&fit=crop',
    categorie: 'Interviews',
    titre: 'Interview exclusive : JJ Redick dévoile sa stratégie',
    contenu: 'Le coach des Lakers nous a accordé un entretien exclusif dans lequel il détaille sa philosophie de jeu et ses ambitions pour la saison...',
    auteur: 'Lakers Newz',
    createdAt: '2025-01-02T00:00:00',
  },
  {
    image: 'https://images.unsplash.com/photo-1517649763962-0c623066013b?w=800&h=600&fit=crop',
    categorie: 'Analyses',
    titre: 'Analyse : Comment les Lakers dominent en défense',
    contenu: 'Plongée tactique dans le système défensif mis en place par le staff technique qui fait des Lakers l\'une des meilleures équipes défensives...',
    auteur: 'Lakers Newz',
    createdAt: '2025-01-01T00:00:00',
  },
  {
    image: 'https://images.unsplash.com/photo-1574623452334-1e0ac2b3ccb4?w=800&h=600&fit=crop',
    categorie: 'News',
    titre: 'Austin Reaves en pleine explosion offensive',
    contenu: 'Le jeune arrière enchaîne les performances XXL et s\'impose comme un élément clé de la rotation des Lakers cette saison...',
    auteur: 'Lakers Newz',
    createdAt: '2024-12-31T00:00:00',
  },
];

const PLACEHOLDER_IMAGE = 'https://images.unsplash.com/photo-1546519638-68e109498ffc?w=800&h=600&fit=crop';

const CATEGORIE_CLASSES = {
  rumeurs:    'category-rumeurs',
  interviews: 'category-interviews',
  analyses:   'category-analyses',
};

function formatDate(dateStr) {
  if (!dateStr) return '';
  const date = new Date(dateStr);
  return date.toLocaleDateString('fr-FR', { day: 'numeric', month: 'long', year: 'numeric' });
}

function buildCard(article) {
  const categorieLower = (article.categorie || '').toLowerCase();
  const categorieClass = CATEGORIE_CLASSES[categorieLower] || '';
  const imgSrc = article.image || PLACEHOLDER_IMAGE;
  const excerpt = (article.contenu || '').length > 150
    ? article.contenu.slice(0, 150) + '...'
    : (article.contenu || '');

  const card = document.createElement('article');
  card.className = 'news-card';
  card.innerHTML = `
    <div class="news-card__image">
      <img src="${imgSrc}" alt="${article.titre || ''}" loading="lazy">
      <span class="news-category ${categorieClass}">${article.categorie || 'News'}</span>
    </div>
    <div class="news-card__content">
      <div class="news-card__date">
        <i class="fa-solid fa-calendar"></i>
        ${formatDate(article.createdAt)}
      </div>
      <h3 class="news-card__title">${article.titre || ''}</h3>
      <p class="news-card__excerpt">${excerpt}</p>
      <div class="news-card__footer">
        <span class="news-card__author">
          <i class="fa-solid fa-user"></i>
          ${article.auteur || 'Lakers Newz'}
        </span>
        <a href="#" class="news-card__read-more">
          Lire la suite
          <i class="fa-solid fa-arrow-right"></i>
        </a>
      </div>
    </div>
  `;
  return card;
}

function renderArticles(articles) {
  const grid = document.querySelector('.news-grid');
  if (!grid) return;
  grid.innerHTML = '';
  articles.forEach(article => grid.appendChild(buildCard(article)));
}

document.addEventListener('DOMContentLoaded', async () => {
  try {
    const response = await fetch('http://127.0.0.1:8000/api/articles');
    if (!response.ok) throw new Error('Réponse non OK');
    const data = await response.json();
    if (!Array.isArray(data) || data.length === 0) throw new Error('Tableau vide');
    renderArticles(data);
  } catch {
    renderArticles(ARTICLES_FALLBACK);
  }
});
