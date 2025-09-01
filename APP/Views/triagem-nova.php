<?php
$pageTitle = 'Triagem SPM - Nova Triagem';
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
        <i class="fa-solid fa-stethoscope fa-lg"></i>
      </div>
      <h2 class="h4 fw-bold mb-1 text-dark">Informações dos Sintomas</h2>
      <p class="mb-0 text-secondary-soft small">Passo 2 de 2</p>
    </section>

    <!-- Form -->
    <form method="post" action="?r=triagem/criar" class="card border border-secondary-soft rounded-xl shadow-soft p-3 p-md-4">
      <div class="mb-3">
        <label class="form-label">Queixa principal *</label>
        <input type="text" name="queixa_principal" class="form-control rounded-xl" required maxlength="255" autocomplete="off">
      </div>

      <div class="mb-3">
        <label class="form-label">Sintomas (texto livre)</label>
        <textarea name="sintomas_texto" class="form-control rounded-xl" rows="4" maxlength="8000" placeholder="Descreva seus sintomas da forma mais clara possível."></textarea>
      </div>

      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label">Antecedentes</label>
          <textarea name="antecedentes_texto" class="form-control rounded-xl" rows="3" maxlength="8000" placeholder="Doenças prévias, cirurgias, etc."></textarea>
        </div>
        <div class="col-md-6">
          <label class="form-label">Alergias</label>
          <textarea name="alergias_texto" class="form-control rounded-xl" rows="3" maxlength="4000" placeholder="Informe alergias conhecidas."></textarea>
        </div>
      </div>

      <div class="mt-3 mb-3">
        <label class="form-label">Medicamentos em uso</label>
        <textarea name="medicamentos_texto" class="form-control rounded-xl" rows="3" maxlength="4000" placeholder="Nome do medicamento e dosagem, se souber."></textarea>
      </div>

      <div class="form-check mb-3">
        <input class="form-check-input" type="checkbox" id="consentimento" name="consentimento" value="1">
        <label class="form-check-label" for="consentimento">
          Estou ciente do uso de meus dados para fins de atendimento e triagem.
        </label>
      </div>

      <div class="d-grid gap-2">
        <button class="btn btn-spm btn-compact rounded-xl" type="submit">
          <i class="fa-solid fa-paper-plane me-1"></i> Enviar triagem
        </button>
        <a class="btn btn-outline-secondary rounded-xl border-secondary-soft" href="./">Cancelar</a>
      </div>
    </form>

    <section class="bg-white border border-secondary-soft rounded-xl p-4 shadow-soft mt-4">
      <div class="d-flex align-items-start gap-3">
        <div class="text-success mt-1">
          <i class="fa-solid fa-circle-info"></i>
        </div>
        <div>
          <h3 class="h6 fw-semibold text-success mb-1">Dica</h3>
          <p class="mb-0 text-secondary-soft small">
            Quanto mais detalhes você fornecer, melhor será a avaliação inicial da sua prioridade.
          </p>
        </div>
      </div>
    </section>

    <div class="text-center mt-5">
      <small class="text-muted">MVP • LGPD: coletamos apenas o necessário para atendimento.</small>
    </div>

  </div>
</main>

</body>
</html>
