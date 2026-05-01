document.addEventListener('DOMContentLoaded', () => {
    const loginForm = document.getElementById('login-form');
    const registerForm = document.getElementById('register-form');
    const showRegisterBtn = document.getElementById('show-register');
    const showLoginBtn = document.getElementById('show-login');

    if (loginForm && registerForm && showRegisterBtn && showLoginBtn) {
        showRegisterBtn.addEventListener('click', (e) => {
            e.preventDefault();
            loginForm.classList.add('hidden');
            registerForm.classList.remove('hidden');
        });

        showLoginBtn.addEventListener('click', (e) => {
            e.preventDefault();
            registerForm.classList.add('hidden');
            loginForm.classList.remove('hidden');
        });

        // Form Validation (Partie 4)
        const loginFormElement = loginForm.querySelector('form');
        loginFormElement.addEventListener('submit', (e) => {
            const email = document.getElementById('login-email').value;
            const password = document.getElementById('login-password').value;

            if (!email || !password) {
                e.preventDefault();
                alert("Erreur: Tous les champs de connexion sont obligatoires.");
            }
        });

        const registerFormElement = registerForm.querySelector('form');
        registerFormElement.addEventListener('submit', (e) => {
            const username = document.getElementById('reg-username').value;
            const email = document.getElementById('reg-email').value;
            const password = document.getElementById('reg-password').value;

            if (!username || !email || !password) {
                e.preventDefault();
                alert("Erreur: Tous les champs d'inscription sont obligatoires.");
                return;
            }
            if (password.length < 6) {
                e.preventDefault();
                alert("Erreur: Le mot de passe doit faire au moins 6 caractères.");
                return;
            }
        });
    }

    // AJAX - Ajout au panier (Partie 5)
    const addToCartBtns = document.querySelectorAll('.add-to-cart-btn');
    addToCartBtns.forEach(btn => {
        btn.addEventListener('click', (e) => {
            const productId = e.target.dataset.id;
            const originalText = e.target.textContent;

            e.target.textContent = 'Ajout en cours...';
            e.target.disabled = true;

            fetch('add_to_cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `product_id=${productId}`
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        e.target.textContent = 'Ajouté !';
                        e.target.style.backgroundColor = '#22c55e'; // Vert success
                        setTimeout(() => {
                            e.target.textContent = originalText;
                            e.target.style.backgroundColor = '';
                            e.target.disabled = false;
                        }, 2000);
                    } else {
                        alert('Erreur : ' + data.message);
                        e.target.textContent = originalText;
                        e.target.disabled = false;
                    }
                })
                .catch(error => {
                    console.error('Erreur AJAX:', error);
                    alert('Une erreur est survenue.');
                    e.target.textContent = originalText;
                    e.target.disabled = false;
                });
        });
    });

});
