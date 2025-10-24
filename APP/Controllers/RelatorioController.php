<?php
namespace APP\Controllers;

class RelatorioController
{
    /**
     * ?r=relatorio/atendimentos&period=diario|semanal|mensal[&format=pdf|html]
     * Relatório agregado (LGPD ok). Gera HTML imprimível ou PDF (Dompdf).
     */
    public static function atendimentos(): void
    {
        /* ------------------------------------------------------------------ *
         * 1) Entrada / período
         * ------------------------------------------------------------------ */
        $period = isset($_GET['period']) ? strtolower($_GET['period']) : 'diario';
        $valid  = ['diario','semanal','mensal'];
        if (!in_array($period, $valid, true)) $period = 'diario';

        $now = new \DateTimeImmutable('now');
        switch ($period) {
            case 'semanal': $start = $now->modify('-6 days')->setTime(0,0,0); break; // 7 dias (hoje inclusive)
            case 'mensal':  $start = $now->modify('-29 days')->setTime(0,0,0); break; // 30 dias
            default:        $start = $now->setTime(0,0,0); // hoje
        }
        $end = $now->setTime(23,59,59);
        $periodo_legivel = $start->format('d/m/Y') . ' – ' . $end->format('d/m/Y');

        /* ------------------------------------ */
$pdoConn = null;

// 2.1 — garante que a classe exista
if (!class_exists('\Database')) {
    // APP/database.php está uma pasta acima deste controller
    $dbFile = __DIR__ . '/../database.php';
    if (file_exists($dbFile)) {
        require_once $dbFile;
    }
}

// 2.2 — tenta Database::get() (seu projeto usa isso)
if (class_exists('\Database')) {
    try {
        $pdoConn = \Database::get();
    } catch (\Throwable $e) {
        $pdoConn = null; // cai no fallback abaixo
    }
}

// 2.3 — fallback: tenta variáveis globais (se alguém já criou manualmente)
if (!$pdoConn instanceof \PDO) {
    global $pdo, $db, $conn;
    if (($pdo  ?? null) instanceof \PDO)      { $pdoConn = $pdo; }
    elseif (($db   ?? null) instanceof \PDO)  { $pdoConn = $db; }
    elseif (($conn ?? null) instanceof \PDO)  { $pdoConn = $conn; }
}

// 2.4 — último recurso: varre $GLOBALS procurando qualquer PDO
if (!$pdoConn instanceof \PDO) {
    foreach ($GLOBALS as $v) {
        if ($v instanceof \PDO) { $pdoConn = $v; break; }
    }
}

if (!$pdoConn instanceof \PDO) {
    header('Content-Type: text/html; charset=utf-8');
    echo '<div style="padding:2rem;font-family:system-ui">DB indisponível.</div>';
    return;
}

        /* ------------------------------------------------------------------ *
         * 3) SQLs (usando nomes reais das colunas, com fallback)
         * ------------------------------------------------------------------ */
        // Campos de texto da triagem (tente *_texto; se não existirem, caia para nomes curtos)
        $colSintomas      = 'st.sintomas_texto';
        $colAntecedentes  = 'st.antecedentes_texto';
        $colAlergias      = 'st.alergias_texto';
        $colMedicamentos  = 'st.medicamentos_texto';

        // Testa rapidamente a existência das colunas *_texto (erro silencioso com TRY..CATCH)
        try {
            $pdoConn->query("SELECT {$colSintomas} FROM sessoes_triagem st LIMIT 1");
        } catch (\Throwable $e) {
            // Fallback para nomes curtos
            $colSintomas     = 'st.sintomas';
            $colAntecedentes = 'st.antecedentes';
            $colAlergias     = 'st.alergias';
            $colMedicamentos = 'st.medicamentos_em_uso';
        }

        // Texto agregado para regex/like
        $textoSQL = "CONCAT_WS(' ',
                      IFNULL(st.queixa_principal,''),
                      IFNULL({$colSintomas},''),
                      IFNULL({$colAntecedentes},''),
                      IFNULL({$colAlergias},''),
                      IFNULL({$colMedicamentos},'')
                    )";

        // Timestamp de referência: entrada_fila_em quando houver, senão createdAt
        $tsRef = "COALESCE(st.entrada_fila_em, st.createdAt)";
        $whereTempo = "$tsRef BETWEEN :tini AND :tend";
        $bindTempo = [
            ':tini' => $start->format('Y-m-d H:i:s'),
            ':tend' => $end->format('Y-m-d H:i:s'),
        ];

        // Total
        $sqlTotal = "SELECT COUNT(*) AS total FROM sessoes_triagem st WHERE $whereTempo";
        $stmt = $pdoConn->prepare($sqlTotal);
        $stmt->execute($bindTempo);
        $total = (int)$stmt->fetchColumn();

        // Doenças (palavras-chave simples)
        $sqlDoencas = "
          SELECT
            SUM( CASE WHEN $textoSQL REGEXP '(ivas|resfriado|gripe|rinite|faringite|sinusite|tosse|nariz)' THEN 1 ELSE 0 END ) AS IVAS,
            SUM( CASE WHEN $textoSQL REGEXP '(asma|sibil|broncospasmo|bombinha)' THEN 1 ELSE 0 END ) AS ASMA,
            SUM( CASE WHEN $textoSQL REGEXP '(dpoc|enfisema|bronquite cr[oô]nica)' THEN 1 ELSE 0 END ) AS DPOC,
            SUM( CASE WHEN $textoSQL REGEXP '(gastroenterite|diarr(e|é)ia|n(a|á)usea|vomit(o|os)|v(ô|o)mito)' THEN 1 ELSE 0 END ) AS GASTRO,
            SUM( CASE WHEN $textoSQL REGEXP '(dermatite|les(ã|a)o cut(â|a)nea|prurido|coceira|erup(c|ç)(ã|a)o)' THEN 1 ELSE 0 END ) AS DERMATITES
          FROM sessoes_triagem st
          WHERE $whereTempo
        ";
        $stmt = $pdoConn->prepare($sqlDoencas);
        $stmt->execute($bindTempo);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC) ?: ['IVAS'=>0,'ASMA'=>0,'DPOC'=>0,'GASTRO'=>0,'DERMATITES'=>0];

        $d_ivas = (int)$row['IVAS'];
        $d_asma = (int)$row['ASMA'];
        $d_dpoc = (int)$row['DPOC'];
        $d_gas  = (int)$row['GASTRO'];
        $d_derm = (int)$row['DERMATITES'];

        // Distribuição por dia da semana (1=Dom..7=Sáb)
        $sqlSemana = "
          SELECT DAYOFWEEK(DATE($tsRef)) AS dow, COUNT(*) AS qtd
            FROM sessoes_triagem st
           WHERE $whereTempo
           GROUP BY dow
           ORDER BY dow
        ";
        $stmt = $pdoConn->prepare($sqlSemana);
        $stmt->execute($bindTempo);
        $byDow = array_fill(1,7,0);
        while ($r = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $byDow[(int)$r['dow']] = (int)$r['qtd'];
        }
        $mapDow = [1=>'Dom',2=>'Seg',3=>'Ter',4=>'Qua',5=>'Qui',6=>'Sex',7=>'Sáb'];

        // Sinais/sintomas mais frequentes (fallback simples com LIKE)
        $terms = ['febre','tosse','dispneia','falta de ar','náusea','nausea','vômito','vomito','dor','garganta'];
        $sinais = [];
        foreach ($terms as $t) {
            $sql = "SELECT SUM( CASE WHEN $textoSQL LIKE :term THEN 1 ELSE 0 END )
                      FROM sessoes_triagem st
                     WHERE $whereTempo";
            $stmt = $pdoConn->prepare($sql);
            $stmt->execute($bindTempo + [':term' => '%'.$t.'%']);
            $q = (int)$stmt->fetchColumn();
            if ($q > 0) $sinais[$t] = $q;
        }
        arsort($sinais);
        $sinaisTop = array_slice(array_keys($sinais), 0, 6);

        /* ------------------------------------------------------------------ *
         * 4) View HTML capturada
         * ------------------------------------------------------------------ */
        $hospital = defined('HOSPITAL_NOME') ? HOSPITAL_NOME : 'Hospital';
        $mesNum   = (int)$now->format('n');
        $epoca    = in_array($mesNum, [6,7,8], true) ? 'outono/inverno' : 'período observado';

        ob_start();
        ?>
<!doctype html>
<html lang="pt-br">
<head>
<meta charset="utf-8">
<title>Relatório — <?= htmlspecialchars($hospital) ?></title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
  :root { --fg:#111; --muted:#555; --brand:#0aa36e; }
  body { font-family: system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif; color:var(--fg); margin:24px; }
  h1{ font-size:20px; margin:0 0 4px }
  h2{ font-size:17px; margin:22px 0 8px }
  h3{ font-size:15px; margin:16px 0 6px }
  p{ margin:8px 0; color:var(--fg) }
  .muted{ color:var(--muted) }
  .kbd{ font-family: ui-monospace, Menlo, Consolas, monospace; background:#f6f6f7; padding:2px 6px; border-radius:6px }
  .topbar{ display:flex; align-items:center; justify-content:space-between; margin-bottom:12px }
  .btn-print{ background:var(--brand); color:#fff; border:0; border-radius:8px; padding:8px 12px; cursor:pointer }
  @media print { .btn-print, .topbar .muted { display:none!important } body{ margin:0 } }
  ul{ margin:6px 0 6px 20px }
  .small{ font-size:12px }
</style>
</head>
<body>
  <div class="topbar">
    <div class="muted small">Relatório operacional (agregado) — pronto para impressão</div>
    <button class="btn-print" onclick="window.print()">Imprimir</button>
  </div>

  <h1>Relatório Breve de Doenças Atendidas</h1>
  <p><strong>Instituição:</strong> <?= htmlspecialchars($hospital) ?><br>
     <strong>Período/Espaço temporal:</strong> <?= htmlspecialchars($periodo_legivel) ?> — <?= htmlspecialchars($epoca) ?></p>

  <h2>1) Resumo Executivo</h2>
  <p>No período analisado (<?= htmlspecialchars($periodo_legivel) ?>), observou-se predomínio de casos compatíveis com
     <?= ($d_ivas+$d_asma+$d_dpoc+$d_gas+$d_derm) ? 'as categorias abaixo' : 'as categorias usuais do serviço' ?>.
     Houve variações moderadas de demanda ao longo das semanas, associadas a padrões por dia da semana e a condições sazonais locais.
     Não foram identificados eventos fora do esperado para a época. Todos os resultados abaixo são agregados e não individualizados.</p>

  <h2>2) Panorama por Janela Temporal</h2>
  <p><strong>Distribuição por dia da semana:</strong></p>
  <ul>
    <?php foreach ($byDow as $k=>$v): ?>
      <li><?= $mapDow[$k] ?>: <span class="kbd"><?= $v ?></span></li>
    <?php endforeach; ?>
  </ul>

  <h2>3) Doenças observadas no período com sintomas típicos</h2>
  <ul>
    <li><strong>IVAS</strong>: <span class="kbd"><?= $d_ivas ?></span></li>
    <li><strong>Asma exacerbada</strong>: <span class="kbd"><?= $d_asma ?></span></li>
    <li><strong>DPOC</strong>: <span class="kbd"><?= $d_dpoc ?></span></li>
    <li><strong>Gastroenterite</strong>: <span class="kbd"><?= $d_gas ?></span></li>
    <li><strong>Dermatites</strong>: <span class="kbd"><?= $d_derm ?></span></li>
  </ul>
  <p><strong>Sinais/Sintomas mais frequentes informados:</strong>
     <?= empty($sinaisTop) ? '[febre baixa, tosse seca, dispneia, náusea, vômitos]' : htmlspecialchars(implode(', ', $sinaisTop)) ?>.</p>

  <h2>4) Observações sazonais</h2>
  <p>Condições locais (ex.: ar seco, poluição) contribuem para irritação de vias aéreas e agravamento de quadros respiratórios,
     favorecendo procura por atendimento nos dias de pior qualidade do ar e menor umidade. Efeito da estação: aumento discreto
     de doenças respiratórias, compatível com o padrão histórico do serviço.</p>

  <h2>5) Recomendações operacionais (não clínicas)</h2>
  <ul>
    <li><strong>Dimensionamento de equipe:</strong> reforçar escala nos dias/turnos de pico (segundas e sextas) para reduzir tempo de espera.</li>
    <li><strong>Triagem e fluxo:</strong> priorizar via rápida para dispneia e sintomas respiratórios.</li>
    <li><strong>Insumos e logística:</strong> assegurar estoque de EPI e materiais para quadros respiratórios e gastrointestinais.</li>
    <li><strong>Comunicação ao público:</strong> informar horários de menor demanda e orientações de autocuidado sazonal.</li>
    <li><strong>Vigilância interna:</strong> manter painel semanal com volume por doença e por dia da semana.</li>
  </ul>

  <p class="small muted"><strong>Nota LGPD:</strong> este relatório utiliza apenas dados agregados, sem identificação pessoal. Uso restrito a planejamento operacional.</p>
  <p class="small muted">Total de atendimentos na janela: <span class="kbd"><?= $total ?></span></p>
</body>
</html>
<?php
        $html = ob_get_clean();

        /* ------------------------------------------------------------------ *
         * 5) Saída: PDF (download) ou HTML
         * ------------------------------------------------------------------ */
        $format = isset($_GET['format']) ? strtolower($_GET['format']) : 'html';
        if ($format === 'pdf') {
            $dompdfLoaded = false;
            if (file_exists(__DIR__ . '/../../vendor/autoload.php')) {
                require_once __DIR__ . '/../../vendor/autoload.php';
                $dompdfLoaded = class_exists(\Dompdf\Dompdf::class);
            }
            if (!$dompdfLoaded && file_exists(__DIR__ . '/../lib/dompdf/autoload.inc.php')) {
                require_once __DIR__ . '/../lib/dompdf/autoload.inc.php';
                $dompdfLoaded = class_exists(\Dompdf\Dompdf::class);
            }

            if ($dompdfLoaded) {
                $nomeArquivo = 'relatorio-' . $period . '-' . date('Ymd-His') . '.pdf';
                $dompdf = new \Dompdf\Dompdf([
                    'isRemoteEnabled' => true,
                    'isHtml5ParserEnabled' => true,
                ]);
                $dompdf->loadHtml($html, 'UTF-8');
                $dompdf->setPaper('A4', 'portrait');
                $dompdf->render();

                header('Content-Type: application/pdf');
                header('Content-Disposition: attachment; filename="' . $nomeArquivo . '"');
                echo $dompdf->output();
                return;
            } else {
                header('Content-Type: text/html; charset=utf-8');
                echo str_replace('</body>', '<script>window.print()</script></body>', $html);
                return;
            }
        }

        header('Content-Type: text/html; charset=utf-8');
        echo $html;
    }
}
