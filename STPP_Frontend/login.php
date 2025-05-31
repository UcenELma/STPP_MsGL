<!-- STPP_Frontend/login.php -->
<?php session_start(); ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Login - My Project</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(90deg, #fff, #fcfcfc);
            height: 100vh;
        }
        .card { border: 0; border-radius: 1rem; box-shadow: 0 0 25px rgba(0,0,0,0.15); }
        .bg-login-image {
            background: url('https://wenhui.whb.cn/u/cms/www/202007/30014134cvyb.jpg') no-repeat center;
            background-size: cover;
            border-top-left-radius: 1rem;
            border-bottom-left-radius: 1rem;
        }
        .form-control-user { border-radius: 10rem; padding: 1.5rem 1rem; font-size: 1rem; }
        .btn-user { border-radius: 10rem; padding: 0.75rem 1rem; font-weight: 700; font-size: 1rem; }
        .text-gray-900 { color: #3a3b45 !important; }
    </style>
</head>
<body>
    <div class="container h-100 d-flex justify-content-center align-items-center">
        <div class="col-xl-10 col-lg-12 col-md-9">
            <div class="card o-hidden shadow-lg my-5">
                <div class="card-body p-0">
                    <div class="row no-gutters">
                        <div class="col-lg-6 d-none d-lg-block bg-login-image"></div>
                        <div class="col-lg-6">
                            <div class="p-5">
                                <div class="text-center mb-4">
                                    <h1 class="h4 text-gray-900">Système de Traçabilité des Produits Périssables</h1>
                                    <h1 class="h4 text-gray-900">By Master GL</h1>
                                </div>

                                <?php if (isset($_SESSION['error'])): ?>
                                    <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['error']) ?></div>
                                    <?php unset($_SESSION['error']); ?>
                                <?php endif; ?>

                                <form class="user" method="POST" action="../STPP_Backend/login_process.php">
                                    <div class="form-group">
                                        <input type="email" name="username" class="form-control form-control-user" placeholder="Enter Email Address..." required>
                                    </div>
                                    <div class="form-group">
                                        <input type="password" name="password" class="form-control form-control-user" placeholder="Password" required>
                                    </div>
                                    <div class="form-group">
                                        <div class="custom-control custom-checkbox small">
                                            <input type="checkbox" class="custom-control-input" id="customCheck" name="remember">
                                            <label class="custom-control-label" for="customCheck">Remember Me</label>
                                        </div>
                                    </div>
                                    <button type="submit" class="btn btn-primary btn-user btn-block">Login</button>
                                </form>

                                <hr>
                                <div class="text-center">
                                    <a class="small" href="#">Mot de passe oublié ?</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.6.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
