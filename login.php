<?php
$pageTitle = 'Вход';
require_once __DIR__ . '/header.php';

if (isLoggedIn()) {
    redirect('index.php');
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = trim($_POST['login'] ?? '');
    $password = trim($_POST['password'] ?? '');

    $stmt = $pdo->prepare('SELECT * FROM users WHERE login = ? LIMIT 1');
    $stmt->execute([$login]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user'] = [
            'id' => (int)$user['id'],
            'login' => $user['login'],
            'fio' => $user['fio'],
            'role_id' => (int)$user['role_id']
        ];
        setFlash('success', 'Добро пожаловать, ' . $user['fio'] . '!');
        redirect('admin.php');
    } else {
        $error = 'Неверный логин или пароль.';
    }
}
?>

<h1 class="mb-4">Авторизация</h1>
<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <form method="post" class="card card-body shadow-sm">
            <div class="mb-3">
                <label for="login" class="form-label">Логин</label>
                <input type="text" id="login" name="login" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Пароль</label>
                <input type="password" id="password" name="password" class="form-control" required>
            </div>
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= escape($error) ?></div>
            <?php endif; ?>
            <button type="submit" class="btn btn-primary">Войти</button>
            <div class="mt-3 d-flex flex-column gap-1">
                <a href="register.php">Еще не зарегистрированы? Регистрация</a>
                <a href="login.php">Уже есть аккаунт? Войти</a>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
