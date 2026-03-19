document.querySelectorAll('.toggle-password').forEach(btn => {
  btn.addEventListener('click', function() {
    const input = this.parentElement.querySelector('input');
    const icon = this.querySelector('i');

    if (input.type === 'password') {
      input.type = 'text';
      icon.classList.remove('fa-eye', 'fa-eye-slash');
      icon.classList.add('fa-eye-slash');
    } else {
      input.type = 'password';
      icon.classList.remove('fa-eye-slash');
      icon.classList.add('fa-eye');
    }
  });
});

const API_URL = 'http://127.0.0.1:8000';

const inscriptionForm = document.getElementById('inscriptionForm');
const inscriptionCard = document.getElementById('inscriptionCard');
const successCard = document.getElementById('successCard');

if (inscriptionForm) {
  inscriptionForm.addEventListener('submit', async function(e) {
    e.preventDefault();

    const prenom = document.getElementById('prenom').value.trim();
    const nom = document.getElementById('nom').value.trim();
    const pseudo = document.getElementById('pseudo').value.trim();
    const email = document.getElementById('email').value.trim();
    const dateNaissance = document.getElementById('dateNaissance').value.trim();
    const telephone = document.getElementById('telephone').value.trim();
    const lieuNaissance = document.getElementById('lieuNaissance').value.trim();
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirmPassword').value;
    const age18 = document.getElementById('age18').checked;
    const conditions = document.getElementById('conditions').checked;

    if (!prenom || !nom || !pseudo || !email || !dateNaissance || !password) {
      alert('Veuillez remplir tous les champs');
      return;
    }

    if (password !== confirmPassword) {
      alert('Les mots de passe ne correspondent pas');
      return;
    }

    if (password.length < 6) {
      alert('Le mot de passe doit contenir au moins 6 caractères');
      return;
    }

    if (!age18) {
      alert('Vous devez certifier avoir 18 ans ou plus');
      return;
    }

    if (!conditions) {
      alert('Vous devez accepter les conditions d\'utilisation');
      return;
    }

    try {
      const response = await fetch(`${API_URL}/api/register`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          email,
          password,
          pseudo,
          firstname: prenom,
          lastname: nom,
          dateNaissance: dateNaissance,
          telephone: telephone,
          lieuNaissance: lieuNaissance
        })
      });

      const data = await response.json();

      if (response.ok) {
        localStorage.setItem('lakersNewzSession', JSON.stringify(data));
        inscriptionCard.style.display = 'none';
        successCard.classList.add('show');
        document.querySelector('#successCard p').textContent = 'Bienvenue ' + prenom;
      } else {
        alert(data.message || 'Erreur lors de l\'inscription');
      }
    } catch (err) {
      alert('Impossible de contacter le serveur');
    }
  });
}

const connexionForm = document.getElementById('connexionForm');

if (connexionForm) {
  connexionForm.addEventListener('submit', async function(e) {
    e.preventDefault();

    const email = document.getElementById('email').value.trim();
    const password = document.getElementById('password').value;

    if (!email || !password) {
      alert('Veuillez remplir tous les champs');
      return;
    }

    try {
      const response = await fetch(`${API_URL}/api/login`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ email, password })
      });

      if (response.status === 401) {
        alert('Email ou mot de passe incorrect');
        return;
      }

      const data = await response.json();

      if (response.ok) {
        // data contient le token JWT + les infos utilisateur
        localStorage.setItem('lakersNewzSession', JSON.stringify({ ...data, isLoggedIn: true }));
        window.location.href = '/paris';
      } else {
        alert(data.message || 'Erreur lors de la connexion');
      }
    } catch (err) {
      alert('Impossible de contacter le serveur');
    }
  });
}
