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
