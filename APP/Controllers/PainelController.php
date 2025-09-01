<?php
require_once __DIR__ . '/../database.php';

final class PainelController
{
    // Tela pública: últimos chamados (ordem do mais recente)
    public static function chamadas(): void
    {
        $pdo = Database::get();
        $sql = "
          SELECT st.codigo_chamada, COALESCE(npf.codigo, npp.codigo) AS prioridade, st.saida_fila_em
          FROM sessoes_triagem st
          LEFT JOIN avaliacoes_ia ai  ON ai.sessao_triagem_id  = st.id
          LEFT JOIN revisoes_profissionais rp ON rp.sessao_triagem_id = st.id
          LEFT JOIN niveis_prioridade npp ON npp.id = ai.prioridade_prevista
          LEFT JOIN niveis_prioridade npf ON npf.id = rp.prioridade_final
          WHERE st.status = 'chamado'
          ORDER BY st.saida_fila_em DESC
          LIMIT 10";
        $rows = $pdo->query($sql)->fetchAll();

        require __DIR__ . '/../Views/painel-chamadas.php';
    }
}
?> 