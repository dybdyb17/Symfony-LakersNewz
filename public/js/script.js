const burgerBtn = document.querySelector('.btn-menu');
const sidebar = document.querySelector('.sidebar');
const closeBtn = document.querySelector('.btn-fermer');

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


const settingsDropdown = document.querySelector('.settings-menu');
const settingsBtn = document.querySelector('.btn-settings');
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

const openSearchBtn = document.getElementById('openSearchModal');
const searchModal = document.getElementById('searchModal');
const closeSearchBg = document.getElementById('closeSearchModal');

if (openSearchBtn && searchModal) {
  openSearchBtn.addEventListener('click', () => {
    searchModal.style.display = 'flex';
    searchModal.querySelector('input').focus();
  });
}

if (closeSearchBg) {
  closeSearchBg.addEventListener('click', () => {
    searchModal.style.display = 'none';
  });
}

document.addEventListener('keydown', (e) => {
  if (e.key === 'Escape' && searchModal) {
    searchModal.style.display = 'none';
  }
});
