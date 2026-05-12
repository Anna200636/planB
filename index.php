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
