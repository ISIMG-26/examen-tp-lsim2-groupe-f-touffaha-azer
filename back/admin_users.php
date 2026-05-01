<?php
require_once 'db.php';
require_once 'helpers.php';
include 'header.php';

// Protection admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['username']) || strtolower($_SESSION['role']) !== 'admin') {
    echo "<section><p style='text-align: center; margin-top: 2rem; color: #ef4444;'>Accès refusé. Cette page est réservée aux administrateurs.</p></section>";
    include 'footer.php';
    exit;
}

// Mise à jour du rôle
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id'], $_POST['role'])) {
    $u_id = (int)$_POST['user_id'];
    $new_role = trim($_POST['role']);
    
    // Empêcher de changer son propre rôle si on est le seul admin
    if ($u_id !== (int)$_SESSION['user_id']) {
        db_exec($conn, "UPDATE users SET role = ? WHERE id = ?", 'si', [$new_role, $u_id]);
        $success = "Rôle mis à jour avec succès.";
    } else {
        $error = "Vous ne pouvez pas changer votre propre rôle.";
    }
}

// Suppression d'un utilisateur
if (isset($_GET['delete_id'])) {
    $d_id = (int)$_GET['delete_id'];
    if ($d_id !== (int)$_SESSION['user_id']) {
        db_exec($conn, "DELETE FROM users WHERE id = ?", 'i', [$d_id]);
        header("Location: admin_users.php?success=Utilisateur+supprimé");
        exit;
    } else {
        $error = "Vous ne pouvez pas vous supprimer vous-même.";
    }
}

// Récupérer tous les utilisateurs
$all_users = db_fetch_all($conn, "SELECT id, username, email, role, created_at FROM users ORDER BY created_at DESC");

if (isset($_GET['success'])) $success = $_GET['success'];
?>

<section>
    <h1 style="text-align: center; margin-bottom: 2rem;">Gestion des Utilisateurs</h1>

    <?php if(isset($success)): ?>
        <div style="background: #dcfce7; color: #166534; padding: 1rem; border-radius: 0.5rem; margin-bottom: 2rem; max-width: 1000px; margin-inline: auto;">
            <?= htmlspecialchars($success) ?>
        </div>
    <?php endif; ?>

    <?php if(isset($error)): ?>
        <div style="background: #fee2e2; color: #ef4444; padding: 1rem; border-radius: 0.5rem; margin-bottom: 2rem; max-width: 1000px; margin-inline: auto;">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <div style="max-width: 1000px; margin: 0 auto; background: var(--card-bg); padding: 2rem; border-radius: 1rem; overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="border-bottom: 2px solid var(--border-color); text-align: left;">
                    <th style="padding: 1rem;">ID</th>
                    <th style="padding: 1rem;">Username</th>
                    <th style="padding: 1rem;">Email</th>
                    <th style="padding: 1rem;">Rôle</th>
                    <th style="padding: 1rem;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($all_users as $u): ?>
                <tr style="border-bottom: 1px solid var(--border-color);">
                    <td style="padding: 1rem;">#<?= $u['id'] ?></td>
                    <td style="padding: 1rem; font-weight: bold;"><?= htmlspecialchars($u['username']) ?></td>
                    <td style="padding: 1rem;"><?= htmlspecialchars($u['email']) ?></td>
                    <td style="padding: 1rem;">
                        <span style="padding: 0.25rem 0.75rem; border-radius: 1rem; font-size: 0.875rem; font-weight: 500; 
                            background: <?= ($u['role'] === 'admin') ? '#fef3c7' : '#f1f5f9' ?>; 
                            color: <?= ($u['role'] === 'admin') ? '#d97706' : '#64748b' ?>;">
                            <?= htmlspecialchars($u['role']) ?>
                        </span>
                    </td>
                    <td style="padding: 1rem;">
                        <form method="POST" style="display: flex; gap: 0.5rem; align-items: center;">
                            <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                            <select name="role" style="padding: 0.5rem; border-radius: 0.5rem; border: 1px solid var(--border-color); background: var(--bg-color); color: var(--text-color); font-size: 0.875rem;">
                                <option value="user" <?= $u['role'] === 'user' ? 'selected' : '' ?>>User</option>
                                <option value="admin" <?= $u['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                            </select>
                            <button type="submit" class="btn" style="padding: 0.5rem 1rem; font-size: 0.875rem;">OK</button>
                            <?php if($u['id'] !== (int)$_SESSION['user_id']): ?>
                                <a href="admin_users.php?delete_id=<?= $u['id'] ?>" class="btn" 
                                   style="padding: 0.5rem 1rem; font-size: 0.875rem; background: #ef4444;"
                                   onclick="return confirm('Supprimer cet utilisateur ?');">🗑️</a>
                            <?php endif; ?>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<?php include 'footer.php'; ?>
