<?php
$pageTitle = 'Регистрация';
require_once __DIR__ . '/header.php';

if (isLoggedIn()) {
    redirect('index.php');
}

$errors = [];
$old = [
    'login' => '',
    'fio' => '',
    'phone' => '',
    'email' => ''
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = trim($_POST['login'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $fio = trim($_POST['fio'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');

    $old = compact('login', 'fio', 'phone', 'email');

    if (!preg_match('/^[A-Za-z0-9]{6,}$/', $login)) {
        $errors['login'] = 'Логин: только латиница/цифры, минимум 6 символов.';
    }
    if (mb_strlen($password) < 8) {
        $errors['password'] = 'Пароль должен быть не менее 8 символов.';
    }
    if (!preg_match('/^[А-Яа-яЁё\s]+$/u', $fio)) {
        $errors['fio'] = 'ФИО: только кириллица и пробелы.';
    }
    if (!preg_match('/^8\(\d{3}\)\d{3}-\d{2}-\d{2}$/', $phone)) {
        $errors['phone'] = 'Телефон в формате 8(XXX)XXX-XX-XX.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Введите корректный email.';
    }

    if (empty($errors)) {
        $checkStmt = $pdo->prepare('SELECT id FROM users WHERE login = ? OR email = ?');
        $checkStmt->execute([$login, $email]);

        if ($checkStmt->fetch()) {
            $errors['login'] = 'Пользователь с таким логином или email уже существует.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $insertStmt = $pdo->prepare('INSERT INTO users (login, password, fio, phone, email, role) VALUES (?, ?, ?, ?, ?, ?)');
            $insertStmt->execute([$login, $hash, $fio, $phone, $email, 'user']);

            setFlash('success', 'Регистрация успешна. Теперь войдите в систему.');
            redirect('login.php');
        }
    }
}
?>

<h1 class="mb-4">Регистрация</h1>
<div class="row g-4 align-items-center">
    <div class="col-lg-6 order-2 order-lg-1">
        <form method="post" novalidate>
            <div class="mb-3">
                <label for="login" class="form-label">Логин</label>
                <input type="text" id="login" name="login" class="form-control <?= isset($errors['login']) ? 'is-invalid' : '' ?>" value="<?= escape($old['login']) ?>" required>
                <div class="invalid-feedback"><?= $errors['login'] ?? 'Поле обязательно' ?></div>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Пароль</label>
                <input type="password" id="password" name="password" class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>" required>
                <div class="invalid-feedback"><?= $errors['password'] ?? 'Поле обязательно' ?></div>
            </div>
            <div class="mb-3">
                <label for="fio" class="form-label">ФИО</label>
                <input type="text" id="fio" name="fio" class="form-control <?= isset($errors['fio']) ? 'is-invalid' : '' ?>" value="<?= escape($old['fio']) ?>" required>
                <div class="invalid-feedback"><?= $errors['fio'] ?? 'Поле обязательно' ?></div>
            </div>
            <div class="mb-3">
                <label for="phone" class="form-label">Телефон</label>
                <input type="text" id="phone" name="phone" class="form-control <?= isset($errors['phone']) ? 'is-invalid' : '' ?>" value="<?= escape($old['phone']) ?>" placeholder="8(900)123-45-67" required>
                <div class="invalid-feedback"><?= $errors['phone'] ?? 'Поле обязательно' ?></div>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" id="email" name="email" class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>" value="<?= escape($old['email']) ?>" required>
                <div class="invalid-feedback"><?= $errors['email'] ?? 'Поле обязательно' ?></div>
            </div>
            <button type="submit" class="btn btn-primary">Зарегистрироваться</button>
            <p class="mt-3 mb-0">Уже есть аккаунт? <a href="login.php">Войти</a></p>
        </form>
    </div>
    <div class="col-lg-6 order-1 order-lg-2">
        <img src="img/register.jpg" alt="Регистрация" class="img-fluid rounded shadow-sm register-img">
    </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
