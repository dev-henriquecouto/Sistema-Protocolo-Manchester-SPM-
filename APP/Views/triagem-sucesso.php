<?php
$pageTitle = 'Triagem SPM - Sucesso';
$showHomeLink = true;
require __DIR__ . '/includes/header.php';
?>

<main class="flex-grow-1">
  <div class="container container-narrow px-3 py-4 py-md-5">

    <!-- Sucesso -->
    <section class="fade-in mb-4 text-center">
      <div class="icon-circle mb-3">
        <i class="fa-solid fa-circle-check fa-lg"></i>
      </div>
      <h2 class="h4 fw-bold mb-2 text-dark">Triagem enviada com sucesso</h2>
      <p class="mb-0 text-secondary-soft">Aguarde ser chamado. A equipe está analisando suas informações.</p>
    </section>

    <!-- Código / Status -->
    <section class="bg-white border border-secondary-soft rounded-xl p-4 shadow-soft text-center mb-4">
      <h3 class="h6 text-success fw-semibold mb-2">Seu código de atendimento</h3>
      <p class="display-6 fw-bold mb-0" style="letter-spacing:.04em;">
        <?php echo htmlspecialchars($codigo ?? '', ENT_QUOTES, 'UTF-8'); ?>
      </p>
      <?php if (!empty($status)): ?>
        <div class="mt-2">
          <span class="badge text-bg-secondary"><?php echo htmlspecialchars($status, ENT_QUOTES, 'UTF-8'); ?></span>
        </div>
      <?php endif; ?>
    </section>

    <!-- Ações -->
    <div class="d-grid gap-2">
      <a href="./" class="btn btn-spm btn-compact rounded-xl">
        <i class="fa-solid fa-house me-1"></i> Voltar ao início
      </a>
      <a href="?r=triagem/identificar" class="btn btn-outline-secondary rounded-xl border-secondary-soft">
        Nova triagem
      </a>
    </div>

    <div class="text-center mt-5">
      <small class="text-muted">MVP • LGPD: coletamos apenas o necessário para atendimento.</small>
    </div>

  </div>
</main>

</body>
</html>
