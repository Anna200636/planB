<?php
$pageTitle = 'Новая заявка';
require_once __DIR__ . '/header.php';

if (!isLoggedIn()) {
    setFlash('warning', 'Сначала войдите в аккаунт.');
    redirect('login.php');
}

$courses = [
    'Основы алгоритмизации и программирования',
    'Основы веб-дизайна',
    'Основы проектирования баз данных'
];

$paymentStmt = $pdo->query('SELECT id, name FROM payment_methods ORDER BY id');
$paymentMethods = $paymentStmt->fetchAll();

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $courseName = trim($_POST['course_name'] ?? '');
    $startDate = trim($_POST['start_date'] ?? '');
    $paymentId = (int)($_POST['payment_id'] ?? 0);

    if (!in_array($courseName, $courses, true)) {
        $errors['course_name'] = 'Выберите курс из списка.';
    }
    if (!$startDate) {
        $errors['start_date'] = 'Выберите дату начала.';
    }
    if ($paymentId <= 0) {
        $errors['payment_id'] = 'Выберите способ оплаты.';
    }

    if (empty($errors)) {
        $insertStmt = $pdo->prepare('INSERT INTO applications (user_id, course_name, start_date, payment_id, status_id) VALUES (?, ?, ?, ?, 1)');
        $insertStmt->execute([$_SESSION['user']['id'], $courseName, $startDate, $paymentId]);
        setFlash('success', 'Заявка успешно создана со статусом "Новая".');
        redirect('applications.php');
    }
}
?>

<h1 class="mb-4">Создание заявки</h1>
<div class="row justify-content-center">
    <div class="col-lg-7">
        <form method="post" class="card card-body shadow-sm">
            <div class="mb-3">
                <label class="form-label" for="course_name">Курс</label>
                <select class="form-select <?= isset($errors['course_name']) ? 'is-invalid' : '' ?>" id="course_name" name="course_name" required>
                    <option value="">Выберите курс</option>
                    <?php foreach ($courses as $course): ?>
                        <option value="<?= escape($course) ?>" <?= (($_POST['course_name'] ?? '') === $course) ? 'selected' : '' ?>><?= escape($course) ?></option>
                    <?php endforeach; ?>
                </select>
                <div class="invalid-feedback"><?= $errors['course_name'] ?? 'Поле обязательно' ?></div>
            </div>
            <div class="mb-3">
                <label class="form-label" for="start_date">Дата начала</label>
                <input type="date" class="form-control <?= isset($errors['start_date']) ? 'is-invalid' : '' ?>" id="start_date" name="start_date" value="<?= escape($_POST['start_date'] ?? '') ?>" required>
                <div class="invalid-feedback"><?= $errors['start_date'] ?? 'Поле обязательно' ?></div>
            </div>
            <div class="mb-3">
                <label class="form-label" for="payment_id">Способ оплаты</label>
                <select class="form-select <?= isset($errors['payment_id']) ? 'is-invalid' : '' ?>" id="payment_id" name="payment_id" required>
                    <option value="">Выберите способ оплаты</option>
                    <?php foreach ($paymentMethods as $method): ?>
                        <option value="<?= (int)$method['id'] ?>" <?= ((int)($_POST['payment_id'] ?? 0) === (int)$method['id']) ? 'selected' : '' ?>><?= escape($method['name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <div class="invalid-feedback"><?= $errors['payment_id'] ?? 'Поле обязательно' ?></div>
            </div>
            <button type="submit" class="btn btn-primary">Создать заявку</button>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
