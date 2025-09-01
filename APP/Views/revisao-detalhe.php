<?php
$pageTitle = 'SPM - Revisão da Sessão';
$showHomeLink = true;
require __DIR__ . '/includes/header.php';

/** @var array $sessao  (TriagemRepository::detalharSessao)
 *  Campos usados: sessao_id, status, codigo_chamada, queixa_principal, sintomas_texto,
 *  antecedentes_texto, alergias_texto, medicamentos_texto, paciente_nome, paciente_email,
 *  ia_confianca, laudo_ia, prioridade_prevista_id, prioridade_prevista,
 *  prioridade_final_id, prioridade_final
 *
 *  @var array $opts  Lista de prioridades: [ ['id'=>..,'codigo'=>..], ... ]
 */
function prio_badge_class(?string $p): string {
  $p = strtoupper((string)$p);
  return match ($p) {
    'VERMELHO' => 'text-bg-danger',
    'LARANJA'  => 'badge-orange',
    'AMARELO'  => 'text-bg-warning',
    'VERDE'    => 'text-bg-success',
    'AZUL'     => 'text-bg-primary',
    default    => 'text-bg-secondary',
  };
}
$codigo = htmlspecialchars((string)($sessao['codigo_chamada'] ?? ''), ENT_QUOTES, 'UTF-8');
$status = htmlspecialchars((string)($sessao['status'] ?? ''), ENT_QUOTES, 'UTF-8');
$pacNm  = htmlspecialchars((string)($sessao['paciente_nome'] ?? ''), ENT_QUOTES, 'UTF-8');
$pacEm  = htmlspecialchars((string)($sessao['paciente_email'] ?? ''), ENT_QUOTES, 'UTF-8');
$prev   = (string)($sessao['prioridade_prevista'] ?? '');
$final  = (string)($sessao['prioridade_final'] ?? '');
$prevBadge  = prio_badge_class($prev);
$finalBadge = prio_badge_class($final);
?>
<style>.badge-orange{color:#fff;background-color:#fd7e14}</style>

<main class="flex-grow-1">
  <div class="container px-3 py-4 py-md-5">

    <?php if (!empty($_SESSION['flash'])): ?>
      <div class="alert alert-warning rounded-xl mb-3" role="alert">
        <?php echo htmlspecialchars($_SESSION['flash'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['flash']); ?>
      </div>
    <?php endif; ?>

    <div class="d-flex align-items-center justify-content-between mb-3">
      <h2 class="h4 fw-semibold mb-0">Sessão <?php echo $codigo; ?></h2>
      <div class="d-flex gap-2">
        <a class="btn btn-light btn-sm rounded-xl border-secondary-soft" href="?r=admin/fila">
          <i class="fa-solid fa-chevron-left me-1"></i> Voltar
        </a>
        <a class="btn btn-outline-secondary btn-sm rounded-xl border-secondary-soft" href="?r=auth/sair">
          <i class="fa-solid fa-right-from-bracket me-1"></i> Sair
        </a>
      </div>
    </div>

    <div class="row g-3">
      <!-- Coluna esquerda: dados -->
      <div class="col-12 col-lg-7">
        <div class="bg-white border border-secondary-soft rounded-xl shadow-soft p-3 p-md-4 mb-3">
          <div class="d-flex justify-content-between align-items-center mb-2">
            <div class="d-flex align-items-center gap-2">
              <span class="badge <?php echo $final ? $finalBadge : $prevBadge; ?>">
                <?php echo htmlspecialchars($final ?: $prev ?: '—', ENT_QUOTES, 'UTF-8'); ?>
              </span>
              <?php if ($status): ?>
                <span class="badge text-bg-secondary"><?php echo $status; ?></span>
              <?php endif; ?>
            </div>
            <div class="text-end small text-secondary-soft">
              <i class="fa-solid fa-user me-1"></i><?php echo $pacNm; ?> · <i class="fa-solid fa-envelope ms-2 me-1"></i><?php echo $pacEm; ?>
            </div>
          </div>

          <hr class="my-3">

          <div class="mb-3">
            <h5 class="h6 fw-semibold mb-1">Queixa principal</h5>
            <div class="text-body" style="white-space:pre-line">
              <?php echo htmlspecialchars((string)($sessao['queixa_principal'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>
            </div>
          </div>

          <div class="mb-3">
            <h5 class="h6 fw-semibold mb-1">Sintomas</h5>
            <div class="text-body" style="white-space:pre-line">
              <?php echo htmlspecialchars((string)($sessao['sintomas_texto'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>
            </div>
          </div>

          <div class="row g-3">
            <div class="col-md-6">
              <h5 class="h6 fw-semibold mb-1">Antecedentes</h5>
              <div class="text-body" style="white-space:pre-line">
                <?php echo htmlspecialchars((string)($sessao['antecedentes_texto'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>
              </div>
            </div>
            <div class="col-md-6">
              <h5 class="h6 fw-semibold mb-1">Alergias</h5>
              <div class="text-body" style="white-space:pre-line">
                <?php echo htmlspecialchars((string)($sessao['alergias_texto'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>
              </div>
            </div>
          </div>

          <div class="mt-3">
            <h5 class="h6 fw-semibold mb-1">Medicamentos em uso</h5>
            <div class="text-body" style="white-space:pre-line">
              <?php echo htmlspecialchars((string)($sessao['medicamentos_texto'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>
            </div>
          </div>
        </div>
      </div>

      <!-- Coluna direita: IA + decisão -->
      <div class="col-12 col-lg-5">
        <!-- Avaliação IA -->
        <div class="bg-white border border-secondary-soft rounded-xl shadow-soft p-3 p-md-4 mb-3">
          <div class="d-flex justify-content-between align-items-center mb-2">
            <h3 class="h6 fw-semibold mb-0">Avaliação Automática (IA)</h3>
            <?php if (!empty($sessao['ia_confianca'])): ?>
              <span class="badge text-bg-info">Confiança: <?php echo number_format((float)$sessao['ia_confianca'] * 100, 0); ?>%</span>
            <?php endif; ?>
          </div>
          <div class="mb-2">
            <span class="badge <?php echo $prevBadge; ?>"><?php echo htmlspecialchars($prev ?: '—', ENT_QUOTES, 'UTF-8'); ?></span>
          </div>
          <div class="text-secondary-soft small" style="white-space:pre-line">
            <?php echo htmlspecialchars((string)($sessao['laudo_ia'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>
          </div>
        </div>

        <!-- Form de revisão -->
        <div class="bg-white border border-secondary-soft rounded-xl shadow-soft p-3 p-md-4 mb-3">
          <form method="post" action="?r=admin/sessao/confirmar" class="d-grid gap-2">
            <input type="hidden" name="sid" value="<?php echo (int)$sessao['sessao_id']; ?>">
            <div>
              <label class="form-label">Prioridade final</label>
              <select name="prioridade_final" class="form-select rounded-xl border-secondary-soft" required>
                <option value="">Selecione…</option>
                <?php foreach ($opts as $o): 
                  $id  = (int)$o['id']; 
                  $cod = htmlspecialchars((string)$o['codigo'], ENT_QUOTES, 'UTF-8');
                  $sel = ((int)($sessao['prioridade_final_id'] ?? 0) === $id) ? 'selected' : '';
                ?>
                  <option value="<?php echo $id; ?>" <?php echo $sel; ?>><?php echo $cod; ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div>
              <label class="form-label">Observações</label>
              <textarea name="observacoes" class="form-control rounded-xl" rows="3" maxlength="2000" placeholder="Anote achados, justificativa, etc."></textarea>
            </div>
            <button type="submit" class="btn btn-spm btn-compact rounded-xl">
              <i class="fa-solid fa-floppy-disk me-1"></i> Confirmar prioridade
            </button>
          </form>
        </div>

        <!-- Chamar paciente -->
        <div class="bg-white border border-secondary-soft rounded-xl shadow-soft p-3 p-md-4">
          <form method="post" action="?r=admin/sessao/chamar" onsubmit="return confirm('Confirmar chamada deste paciente?');">
            <input type="hidden" name="sid" value="<?php echo (int)$sessao['sessao_id']; ?>">
            <button type="submit" class="btn btn-outline-secondary w-100 rounded-xl border-secondary-soft">
              <i class="fa-solid fa-bell me-1"></i> Chamar paciente
            </button>
          </form>
        </div>
      </div>
    </div>

  </div>
</main>

</body>
</html>
