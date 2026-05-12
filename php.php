config

<?php
session_start();

$host = 'localhost';
$dbname = 'shnel';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die('Ошибка подключения к БД: ' . $e->getMessage());
}

function isLoggedIn(): bool
{
    return isset($_SESSION['user']);
}

function isAdmin(): bool
{
    return isset($_SESSION['user']) && $_SESSION['user']['role_id' ?? 0] === 1;
}

function redirect(string $url): void
{
    header("Location: $url");
    exit;
}

function setFlash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash(): ?array
{
    if (!isset($_SESSION['flash'])) {
        return null;
    }

    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return $flash;
}

function escape(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}
?>




register

<?php
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
        $errors['phone'] = 'Телефон в формате  $errors['password'] = 'Пароль: не мнее 8 символов'.';
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



login 

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


zayavka

<?php
$pageTitle = 'Мои заявки';
require_once __DIR__ . '/header.php';

if (!isLoggedIn()) {
    setFlash('warning', 'Сначала войдите в аккаунт.');
    redirect('login.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['review_submit'])) {
    $applicationId = (int)($_POST['application_id'] ?? 0);
    $reviewText = trim($_POST['review_text'] ?? '');

    if ($applicationId > 0 && $reviewText !== '') {
        $checkStmt = $pdo->prepare('SELECT id FROM applications WHERE id = ? AND user_id = ? AND status_id = 3');
        $checkStmt->execute([$applicationId, $_SESSION['user']['id']]);

        if ($checkStmt->fetch()) {
            $reviewInsert = $pdo->prepare('INSERT INTO reviews (user_id, application_id, review_text) VALUES (?, ?, ?)');
            $reviewInsert->execute([$_SESSION['user']['id'], $applicationId, $reviewText]);
            setFlash('success', 'Отзыв успешно добавлен.');
        } else {
            setFlash('danger', 'Оставлять отзыв можно только по завершенному обучению.');
        }
    } else {
        setFlash('danger', 'Заполните текст отзыва.');
    }
    redirect('applications.php');
}

$stmt = $pdo->prepare(
    'SELECT a.id, a.course_name, a.start_date, pm.name AS payment_name, st.name AS status_name, st.id AS status_id
     FROM applications a
     JOIN payment_methods pm ON pm.id = a.payment_id
     JOIN application_statuses st ON st.id = a.status_id
     WHERE a.user_id = ?
     ORDER BY a.created_at DESC'
);
$stmt->execute([$_SESSION['user']['id']]);
$applications = $stmt->fetchAll();

$reviewStmt = $pdo->prepare('SELECT application_id FROM reviews WHERE user_id = ?');
$reviewStmt->execute([$_SESSION['user']['id']]);
$reviewedIds = array_map('intval', array_column($reviewStmt->fetchAll(), 'application_id'));
?>

<h1 class="mb-4">Мои заявки</h1>

<?php if (empty($applications)): ?>
    <div class="alert alert-info">У вас пока нет заявок. <a href="create_application.php">Создать первую</a>.</div>
<?php else: ?>
    <div class="table-responsive">
        <table class="table table-striped table-bordered align-middle">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Курс</th>
                    <th>Дата начала</th>
                    <th>Оплата</th>
                    <th>Статус</th>
                    <th>Отзыв</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($applications as $app): ?>
                    <tr>
                        <td><?= (int)$app['id'] ?></td>
                        <td><?= escape($app['course_name']) ?></td>
                        <td><?= escape($app['start_date']) ?></td>
                        <td><?= escape($app['payment_name']) ?></td>
                        <td><?= escape($app['status_name']) ?></td>
                        <td>
                            <?php if ((int)$app['status_id'] === 3 && !in_array((int)$app['id'], $reviewedIds, true)): ?>
                                <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#reviewModal<?= (int)$app['id'] ?>">Оставить отзыв</button>
                            <?php elseif (in_array((int)$app['id'], $reviewedIds, true)): ?>
                                <span class="badge text-bg-success">Отзыв оставлен</span>
                            <?php else: ?>
                                <span class="text-muted">Недоступно</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php foreach ($applications as $app): ?>
        <?php if ((int)$app['status_id'] === 3 && !in_array((int)$app['id'], $reviewedIds, true)): ?>
            <div class="modal fade" id="reviewModal<?= (int)$app['id'] ?>" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Отзыв по заявке #<?= (int)$app['id'] ?></h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <form method="post">
                            <div class="modal-body">
                                <input type="hidden" name="application_id" value="<?= (int)$app['id'] ?>">
                                <textarea name="review_text" class="form-control" rows="4" required></textarea>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                                <button type="submit" name="review_submit" class="btn btn-primary">Сохранить отзыв</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    <?php endforeach; ?>
<?php endif; ?>

<?php require_once __DIR__ . '/footer.php'; ?>


new_zayavka


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


index


<?php require_once 'header.php'; ?>

<section class="mb-5">
    <div class="row align-items-center g-4">
        <div class="col-lg-7">
            <h1 class="fw-bold mb-3">Онлайн-платформа «Корочки.есть»</h1>
            <p class="lead">Записывайтесь на курсы дополнительного профессионального образования и получайте новые компетенции в удобном формате.</p>
            <?php if (!isLoggedIn()): ?>
                <a href="register.php" class="btn btn-primary btn-lg me-2">Регистрация</a>
                <a href="login.php" class="btn btn-outline-primary btn-lg">Войти</a>
            <?php else: ?>
                <a href="create_application.php" class="btn btn-primary btn-lg">Создать заявку</a>
            <?php endif; ?>
        </div>
        <div class="col-lg-5 text-center">
            <img src="img/logo.png" alt="Логотип Корочки.есть" class="main-logo img-fluid">
        </div>
    </div>
</section>

<section class="mb-5">
    <h2 class="mb-3">Популярные курсы</h2>
    <div class="row g-4">
        <div class="col-md-4">
            <div class="card h-100">
                <img src="img/card1.jpg" class="card-img-top course-card-img" alt="Курс по алгоритмам">
                <div class="card-body">
                    <h5 class="card-title">Основы алгоритмизации и программирования</h5>
                    <p class="card-text">Базовые конструкции, алгоритмы и практические задачи.</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100">
                <img src="img/card2.jpg" class="card-img-top course-card-img" alt="Курс по веб-дизайну">
                <div class="card-body">
                    <h5 class="card-title">Основы веб-дизайна</h5>
                    <p class="card-text">Композиция, UI-элементы и адаптивная верстка.</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100">
                <img src="img/card3.jpg" class="card-img-top course-card-img" alt="Курс по БД">
                <div class="card-body">
                    <h5 class="card-title">Основы проектирования баз данных</h5>
                    <p class="card-text">Нормализация, связи и проектирование структуры БД.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<section>
    <h2 class="mb-3">Галерея обучения</h2>
    <div id="coursesCarousel" class="carousel slide" data-bs-ride="carousel">
        <div class="carousel-inner rounded shadow-sm">
            <div class="carousel-item active">
                <img src="img/slide1.jpg" class="d-block w-100 slider-img" alt="Слайд 1">
            </div>
            <div class="carousel-item">
                <img src="img/slide2.jpg" class="d-block w-100 slider-img" alt="Слайд 2">
            </div>
            <div class="carousel-item">
                <img src="img/slide3.jpg" class="d-block w-100 slider-img" alt="Слайд 3">
            </div>
            <div class="carousel-item">
                <img src="img/slide4.jpg" class="d-block w-100 slider-img" alt="Слайд 4">
            </div>
        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#coursesCarousel" data-bs-slide="prev">
            <span class="carousel-control-prev-icon"></span>
            <span class="visually-hidden">Предыдущий</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#coursesCarousel" data-bs-slide="next">
            <span class="carousel-control-next-icon"></span>
            <span class="visually-hidden">Следующий</span>
        </button>
    </div>
</section>

<?php require_once __DIR__ . '/footer.php'; ?>



admin 

<?php
$pageTitle = 'Админ-панель';
require_once __DIR__ . '/header.php';

if (!isLoggedIn() || !isAdmin()) {
    setFlash('danger', 'Доступ только для администратора.');
    redirect('login.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $applicationId = (int)($_POST['application_id'] ?? 0);
    $statusId = (int)($_POST['status_id'] ?? 0);

    if ($applicationId > 0 && in_array($statusId, [1, 2, 3], true)) {
        $updateStmt = $pdo->prepare('UPDATE applications SET status_id = ? WHERE id = ?');
        $updateStmt->execute([$statusId, $applicationId]);
        setFlash('success', 'Статус заявки обновлен.');
    } else {
        setFlash('danger', 'Некорректные данные для обновления.');
    }
    redirect('admin.php');
}

$statusFilter = (int)($_GET['status_id'] ?? 0);
$courseFilter = trim($_GET['course'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 5;
$offset = ($page - 1) * $limit;

$whereParts = [];
$params = [];

if (in_array($statusFilter, [1, 2, 3], true)) {
    $whereParts[] = 'a.status_id = ?';
    $params[] = $statusFilter;
}
if ($courseFilter !== '') {
    $whereParts[] = 'a.course_name = ?';
    $params[] = $courseFilter;
}

$whereSql = $whereParts ? ('WHERE ' . implode(' AND ', $whereParts)) : '';

$countSql = "SELECT COUNT(*) FROM applications a $whereSql";
$countStmt = $pdo->prepare($countSql);
$countStmt->execute($params);
$totalRows = (int)$countStmt->fetchColumn();
$totalPages = max(1, (int)ceil($totalRows / $limit));

$listSql =
    "SELECT a.id, u.fio, u.login, a.course_name, a.start_date, pm.name AS payment_name, st.name AS status_name, st.id AS status_id
     FROM applications a
     JOIN users u ON u.id = a.user_id
     JOIN payment_methods pm ON pm.id = a.payment_id
     JOIN application_statuses st ON st.id = a.status_id
     $whereSql
     ORDER BY a.created_at DESC
     LIMIT $limit OFFSET $offset";
$listStmt = $pdo->prepare($listSql);
$listStmt->execute($params);
$applications = $listStmt->fetchAll();

$statuses = $pdo->query('SELECT id, name FROM application_statuses ORDER BY id')->fetchAll();
$courses = [
    'Основы алгоритмизации и программирования',
    'Основы веб-дизайна',
    'Основы проектирования баз данных'
];
?>

<h1 class="mb-4">Панель администратора</h1>

<form method="get" class="row g-3 mb-4">
    <div class="col-md-4">
        <label class="form-label">Фильтр по статусу</label>
        <select name="status_id" class="form-select">
            <option value="0">Все статусы</option>
            <?php foreach ($statuses as $status): ?>
                <option value="<?= (int)$status['id'] ?>" <?= $statusFilter === (int)$status['id'] ? 'selected' : '' ?>><?= escape($status['name']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-4">
        <label class="form-label">Фильтр по курсу</label>
        <select name="course" class="form-select">
            <option value="">Все курсы</option>
            <?php foreach ($courses as $course): ?>
                <option value="<?= escape($course) ?>" <?= $courseFilter === $course ? 'selected' : '' ?>><?= escape($course) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-4 d-flex align-items-end gap-2">
        <button class="btn btn-primary" type="submit">Применить</button>
        <a class="btn btn-outline-secondary" href="admin.php">Сбросить</a>
    </div>
</form>

<div class="table-responsive">
    <table class="table table-bordered table-hover align-middle">
        <thead class="table-light">
            <tr>
                <th>#</th>
                <th>Пользователь</th>
                <th>Курс</th>
                <th>Дата начала</th>
                <th>Оплата</th>
                <th>Статус</th>
                <th>Изменить</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($applications)): ?>
                <tr><td colspan="7" class="text-center">Заявки не найдены.</td></tr>
            <?php endif; ?>
            <?php foreach ($applications as $app): ?>
                <tr>
                    <td><?= (int)$app['id'] ?></td>
                    <td><?= escape($app['fio']) ?> (<?= escape($app['login']) ?>)</td>
                    <td><?= escape($app['course_name']) ?></td>
                    <td><?= escape($app['start_date']) ?></td>
                    <td><?= escape($app['payment_name']) ?></td>
                    <td><span class="badge text-bg-info"><?= escape($app['status_name']) ?></span></td>
                    <td>
                        <form method="post" class="d-flex gap-2">
                            <input type="hidden" name="application_id" value="<?= (int)$app['id'] ?>">
                            <select name="status_id" class="form-select form-select-sm">
                                <?php foreach ($statuses as $status): ?>
                                    <option value="<?= (int)$status['id'] ?>" <?= (int)$app['status_id'] === (int)$status['id'] ? 'selected' : '' ?>><?= escape($status['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit" name="update_status" class="btn btn-sm btn-success confirm-action">Сохранить</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php if ($totalPages > 1): ?>
<nav>
    <ul class="pagination">
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                <a class="page-link" href="?page=<?= $i ?>&status_id=<?= $statusFilter ?>&course=<?= urlencode($courseFilter) ?>"><?= $i ?></a>
            </li>
        <?php endfor; ?>
    </ul>
</nav>
<?php endif; ?>

<?php require_once __DIR__ . '/footer.php'; ?>


logout

<?php
require_once __DIR__ . '/db.php';
session_unset();
session_destroy();
session_start();
setFlash('info', 'Вы вышли из аккаунта.');
redirect('index.php');
?>



header
<?php require_once 'db.php'; ?>
<!doctype html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Корочки.есть</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center gap-2" href="index.php">
            <img src="img/logo.png" alt="Логотип" width="36" height="36" class="rounded-circle">
            <span>Корочки.есть</span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="index.php">Главная</a></li>
                <?php if (isLoggedIn()): ?>
                    <li class="nav-item"><a class="nav-link" href="create_application.php">Новая заявка</a></li>
                    <li class="nav-item"><a class="nav-link" href="applications.php">Мои заявки</a></li>
                    <?php if (isAdmin()): ?>
                        <li class="nav-item"><a class="nav-link" href="admin.php">Админ-панель</a></li>
                    <?php endif; ?>
                    <li class="nav-item"><a class="nav-link" href="logout.php">Выход</a></li>
                <?php else: ?>
                    <li class="nav-item"><a class="nav-link" href="login.php">Вход</a></li>
                    <li class="nav-item"><a class="nav-link" href="register.php">Регистрация</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<main class="container py-4">
    <?php if (!empty($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_SESSION['success']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (!empty($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_SESSION['error']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>



footer 

</div>

<footer class="bg-light border-top mt-4">
    <div class="container py-3 text-center text-muted">
        <small>&copy; <?= date('Y') ?> Корочки.есть - онлайн-курсы ДПО</small>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/main.js"></script>
</body>
</html>


main.js 

document.addEventListener('DOMContentLoaded', function () {
    const carouselElement = document.querySelector('#coursesCarousel');
    if (carouselElement) {
        new bootstrap.Carousel(carouselElement, {
            interval: 3000,
            ride: 'carousel',
            pause: 'hover'
        });
    }

    document.querySelectorAll('.confirm-action').forEach(function (button) {
        button.addEventListener('click', function (event) {
            const isConfirmed = confirm('Подтвердить действие?');
            if (!isConfirmed) {
                event.preventDefault();
            }
        });
    });

    const registerForm = document.querySelector('form[novalidate]');
    if (registerForm) {
        registerForm.addEventListener('submit', function (event) {
            const login = document.getElementById('login');
            const password = document.getElementById('password');
            const fio = document.getElementById('fio');
            const phone = document.getElementById('phone');
            const email = document.getElementById('email');

            const loginRegex = /^[A-Za-z0-9]{6,}$/;
            const fioRegex = /^[А-Яа-яЁё\s]+$/;
            const phoneRegex = /^8\(\d{3}\)\d{3}-\d{2}-\d{2}$/;
            let valid = true;

            if (!loginRegex.test(login.value.trim())) {
                login.classList.add('is-invalid');
                valid = false;
            }
            if (password.value.trim().length < 8) {
                password.classList.add('is-invalid');
                valid = false;
            }
            if (!fioRegex.test(fio.value.trim())) {
                fio.classList.add('is-invalid');
                valid = false;
            }
            if (!phoneRegex.test(phone.value.trim())) {
                phone.classList.add('is-invalid');
                valid = false;
            }
            if (!email.value.trim()) {
                email.classList.add('is-invalid');
                valid = false;
            }

            if (!valid) {
                event.preventDefault();
            }
        });
    }
});



style.css 


body {
    background-color: #f8f9fa;
}

.site-logo {
    width: 42px;
    height: 42px;
    object-fit: cover;
    border-radius: 50%;
}

.main-logo {
    max-height: 220px;
    object-fit: contain;
}

.slider-img {
    height: 380px;
    object-fit: cover;
}

.course-card-img {
    height: 220px;
    object-fit: cover;
}

.register-img {
    max-height: 560px;
    width: 100%;
    object-fit: cover;
}

@media (max-width: 576px) {
    .slider-img {
        height: 220px;
    }

    .course-card-img {
        height: 180px;
    }
}
