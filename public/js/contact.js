const contactForm = document.getElementById('contactForm');
const successMessage = document.getElementById('successMessage');

function showSuccess() {
  contactForm.style.display = 'none';
  successMessage.classList.add('show');

  setTimeout(() => {
    contactForm.reset();
    contactForm.style.display = 'flex';
    successMessage.classList.remove('show');
  }, 3000);
}

if (contactForm) {
  contactForm.addEventListener('submit', async (e) => {
    e.preventDefault();

    const nom = document.getElementById('name').value;
    const email = document.getElementById('email').value;
    const sujet = document.getElementById('subject').value;
    const message = document.getElementById('message').value;

    try {
      const response = await fetch('http://127.0.0.1:8000/api/contact', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ nom, email, sujet, message }),
      });

      if (!response.ok) {
        throw new Error('Erreur serveur');
      }
    } catch (err) {
      console.warn('Envoi API échoué (fallback) :', err);
    }

    showSuccess();
  });
}
