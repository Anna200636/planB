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
