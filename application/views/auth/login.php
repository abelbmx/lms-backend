<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - LMS System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
        }

        .login-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(51, 51, 51, 0.1);
            padding: 3rem;
            max-width: 400px;
            width: 100%;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 25px;
            padding: 12px 30px;
        }

        .form-control {
            border-radius: 25px;
            padding: 12px 20px;
            border: 2px solid #f1f1f1;
        }

        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="login-card">
                    <div class="text-center mb-4">
                        <h2 class="fw-bold">Iniciar Sesión</h2>
                        <p class="text-muted">Accede a tu cuenta del LMS</p>
                    </div>

                    <form id="loginForm">
                        <div class="mb-3">
                            <input type="email" class="form-control" name="email" placeholder="Email" required>
                        </div>
                        <div class="mb-3">
                            <input type="password" class="form-control" name="password" placeholder="Contraseña" required>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">Iniciar Sesión</button>
                        </div>
                    </form>

                    <div class="text-center mt-3">
                        <p>¿No tienes cuenta? <a href="<?= base_url('auth/register') ?>">Regístrate aquí</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);

            fetch('<?= base_url('auth/login') ?>', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        alert('Login exitoso');
                        window.location.href = '<?= base_url('dashboard') ?>';
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error de conexión');
                });
        });
    </script>
</body>

</html>
