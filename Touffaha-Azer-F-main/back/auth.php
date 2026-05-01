
<?php include 'header.php'; ?>

<?php
// Redirection si déjà connecté
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
?>

<div class="auth-container">
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-error"><?= htmlspecialchars($_SESSION['error']) ?></div>
        <?php unset($_SESSION['error']); endif; ?>
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success']) ?></div>
        <?php unset($_SESSION['success']); endif; ?>

    <!-- Formulaire de Connexion -->
    <div id="login-form">
        <h2>Connexion</h2>
        <form action="auth_action.php" method="POST" class="auth-form">
            <input type="hidden" name="action" value="login">
            <div class="form-group">
                <label for="login-email">Adresse Email</label>
                <input type="email" id="login-email" name="email" required>
            </div>
            <div class="form-group">
                <label for="login-password">Mot de passe</label>
                <input type="password" id="login-password" name="password" required>
            </div>
            <button type="submit" class="btn">Se connecter</button>
        </form>
        <div class="form-toggle">
            <p>Pas encore de compte ? <a href="#" id="show-register">S'inscrire</a></p>
        </div>
    </div>

    <!-- Formulaire d'Inscription -->
    <div id="register-form" class="hidden">
        <h2>Inscription</h2>
        <form action="auth_action.php" method="POST" class="auth-form">
            <input type="hidden" name="action" value="register">
            <div class="form-group">
                <label for="reg-username">Nom d'utilisateur</label>
                <input type="text" id="reg-username" name="username" required>
            </div>
            <div class="form-group">
                <label for="reg-email">Adresse Email</label>
                <input type="email" id="reg-email" name="email" required>
            </div>
            <div class="form-group">
                <label for="reg-password">Mot de passe</label>
                <input type="password" id="reg-password" name="password" required>
            </div>
            <button type="submit" class="btn">S'inscrire</button>
        </form>
        <div class="form-toggle">
            <p>Déjà un compte ? <a href="#" id="show-login">Se connecter</a></p>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
