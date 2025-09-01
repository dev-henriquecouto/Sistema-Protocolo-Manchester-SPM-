<?php
require_once __DIR__ . '/../config.php';
$pageTitle = 'Triagem SPM - Identificação do Paciente';
$showHomeLink = true;
require __DIR__ . '/includes/header.php';
?>

<main class="flex-grow-1">
  <div class="container container-narrow px-3 py-4 py-md-5">

    <?php if (!empty($_SESSION['flash'])): ?>
      <div class="alert alert-warning rounded-xl mb-4" role="alert">
        <?php echo htmlspecialchars($_SESSION['flash'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['flash']); ?>
      </div>
    <?php endif; ?>

    <!-- Título / Passo -->
    <section class="fade-in mb-4 text-center">
      <div class="icon-circle mb-3">
        <i class="fa-solid fa-user fa-lg"></i>
      </div>
      <h2 class="h4 fw-bold mb-1 text-dark">Identificação do Paciente</h2>
      <p class="mb-0 text-secondary-soft small">Passo 1 de 2</p>
    </section>

    <!-- Botão Google -->
    <section class="mb-4 text-center">
      <div id="gbtn" class="d-inline-block"></div>
      <div class="text-secondary-soft small mt-2">ou preencha manualmente abaixo</div>
    </section>

    <!-- Form Manual -->
    <section class="mb-4">
      <form method="post" action="?r=triagem/identificar-post" class="card border border-secondary-soft rounded-xl shadow-soft p-3 p-md-4">
        <div class="mb-3">
          <label class="form-label">Nome completo *</label>
          <input type="text" name="nome" class="form-control rounded-xl" required maxlength="120" autocomplete="name">
        </div>
        <div class="mb-3">
          <label class="form-label">E-mail *</label>
          <input type="email" name="email" class="form-control rounded-xl" required maxlength="180" autocomplete="email">
          <div class="form-text">Usaremos para localizar seu cadastro. Se não existir, criaremos automaticamente.</div>
        </div>
        <div class="d-grid gap-2">
          <button class="btn btn-spm btn-compact rounded-xl" type="submit">
            <i class="fa-solid fa-arrow-right-long me-1"></i> Continuar
          </button>
          <a class="btn btn-outline-secondary rounded-xl border-secondary-soft" href="./">← Voltar</a>
        </div>
      </form>
    </section>

    <!-- Como funciona -->
    <section class="bg-white border border-secondary-soft rounded-xl p-4 shadow-soft">
      <div class="d-flex align-items-start gap-3">
        <div class="text-success mt-1">
          <i class="fa-solid fa-circle-info"></i>
        </div>
        <div>
          <h3 class="h6 fw-semibold text-success mb-1">Como funciona?</h3>
          <p class="mb-0 text-secondary-soft small">
            Você responderá algumas perguntas sobre seus sintomas e nossa IA gerará um relatório médico inicial.
          </p>
        </div>
      </div>
    </section>

    <div class="text-center mt-5">
      <small class="text-muted">MVP • LGPD: coletamos apenas o necessário para atendimento.</small>
    </div>

  </div>
</main>

<!-- Google Identity -->
<script src="https://accounts.google.com/gsi/client" async defer></script>
<script>
  function handleCredentialResponse(response) {
    const form = new FormData();
    form.append('credential', response.credential);
    fetch('?r=auth/google-paciente', { method: 'POST', body: form })
      .then(r => r.redirected ? window.location = r.url : window.location = '?r=triagem/nova')
      .catch(() => alert('Falha no login com Google. Tente novamente.'));
  }

  window.onload = function() {
    google.accounts.id.initialize({
      client_id: "<?= htmlspecialchars(GOOGLE_CLIENT_ID, ENT_QUOTES, 'UTF-8') ?>",
      callback: handleCredentialResponse
    });
    const btn = document.getElementById('gbtn');
    if (btn) {
      google.accounts.id.renderButton(btn, { theme: 'outline', size: 'large', width: 320 });
    }
  };
</script>

</body>
</html>
