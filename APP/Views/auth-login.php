<!doctype html>
<html lang="pt-br">
<head>
  <meta charset="utf-8">
  <title>Login - SPM</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- Bootstrap CDN -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5" style="max-width: 480px;">
  <h1 class="mb-4">Acesso do Profissional</h1>

  <?php if (!empty($_SESSION['flash'])): ?>
    <div class="alert alert-warning"><?php echo htmlspecialchars($_SESSION['flash'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['flash']); ?></div>
  <?php endif; ?>

  <form method="post" action="?r=auth/entrar" class="card p-4 shadow-sm bg-white">
    <div class="mb-3">
      <label class="form-label">E-mail</label>
      <input type="email" name="email" class="form-control" required autofocus>
    </div>
    <div class="mb-3">
      <label class="form-label">Senha</label>
      <input type="password" name="senha" class="form-control" required>
    </div>
    <button class="btn btn-primary w-100" type="submit">Entrar</button>
    <a class="btn btn-link mt-2" href="./">← Voltar</a>
  </form>
</div>
</body>
</html>
