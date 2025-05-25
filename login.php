<!-- login.php -->
<?php
session_start();
require 'config.php'; // Pour accéder à $db


$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['username'];
    $password = $_POST['password'];

    // Requête préparée pour éviter l'injection SQL
    $stmt = $db->prepare("SELECT * FROM utilisateurs WHERE email = :email");
    $stmt->bindParam(':email', $email);
    $stmt->execute();

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // Vérification du mot de passe
        if (password_verify($password, $user['mot_de_passe_hash'])) {
            // Connexion réussie
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['email'];
            $_SESSION['role'] = $user['role'];

            // Redirection selon le rôle
            switch ($user['role']) {
                case 'admin':
                    header("Location: index.php");
                    break;
                case 'fournisseur':
                    header("Location: fournisseur.php");
                    break;
                case 'transporteur':
                    header("Location: transporteur.php");
                    break;
                default:
                    $error = "Rôle inconnu.";
            }
            exit();
        } else {
            $error = "Mot de passe incorrect.";
        }
    } else {
        $error = "Email non trouvé.";
    }
}
?>



<!DOCTYPE html>
<html lang="fr">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Login - My Project</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- FontAwesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

    <style>
        body {
            background: linear-gradient(90deg,rgb(255, 255, 255) 0%,rgb(252, 252, 252) 100%);
            height: 100vh;
        }

        .card {
            border: 0;
            border-radius: 1rem;
            box-shadow: 0 0 25px rgba(0, 0, 0, 0.15);
        }

        .bg-login-image {
            background: url('https://wenhui.whb.cn/u/cms/www/202007/30014134cvyb.jpg') no-repeat center;
            background-size: cover;
            border-top-left-radius: 1rem;
            border-bottom-left-radius: 1rem;
        }

        .form-control-user {
            border-radius: 10rem;
            padding: 1.5rem 1rem;
            font-size: 1rem;
        }

        .btn-user {
            border-radius: 10rem;
            padding: 0.75rem 1rem;
            font-weight: 700;
            font-size: 1rem;
        }

        .text-gray-900 {
            color: #3a3b45 !important;
        }
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
                                    <!-- <h1 class="h4 text-gray-900">Welcome Back</h1> -->
                                    <h1 class="h4 text-gray-900">Système de Traçabilité des Produits Périssables</h1>
                                    <h1 class="h4 text-gray-900">By Master GL</h1>
                                </div>

                                <?php if ($error): ?>
                                    <div class="alert alert-danger" role="alert">
                                        <?= htmlspecialchars($error) ?>
                                    </div>
                                <?php endif; ?>

                                <form class="user" method="POST" action="">
                                    <div class="form-group">
                                        <input type="email" name="username" class="form-control form-control-user"
                                            aria-describedby="emailHelp" placeholder="Enter Email Address..." required>
                                    </div>
                                    <div class="form-group">
                                        <input type="password" name="password" class="form-control form-control-user"
                                            placeholder="Password" required>
                                    </div>
                                    <div class="form-group">
                                        <div class="custom-control custom-checkbox small">
                                            <input type="checkbox" class="custom-control-input" id="customCheck" name="remember">
                                            <label class="custom-control-label" for="customCheck">Remember Me</label>
                                        </div>
                                    </div>
                                    <button type="submit" class="btn btn-primary btn-user btn-block">
                                        Login
                                    </button>
                                </form>

                                <hr>
                                <!-- <a href="#" class="btn btn-google btn-user btn-block">
                                    <i class="fab fa-google fa-fw"></i> Login with Google
                                </a>
                                <a href="#" class="btn btn-facebook btn-user btn-block">
                                    <i class="fab fa-facebook-f fa-fw"></i> Login with Facebook
                                </a> -->

                                <hr>
                                <div class="text-center">
                                    <a class="small" href="#">Forgot Password?</a>
                                </div>
                                <!-- <div class="text-center">
                                    <a class="small" href="#">Create an Account!</a>
                                </div> -->

                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

    </div>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://code.jquery.com/jquery-3.6.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>
