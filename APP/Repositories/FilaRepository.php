<?php
require_once __DIR__ . '/../database.php';

final class FilaRepository
{
    public static function listarEmFila(): array
    {
        $pdo = Database::get();
        $sql = "
        SELECT
          st.id AS sessao_id,
          st.codigo_chamada,
          u.nome AS paciente_nome,
          st.entrada_fila_em,
          COALESCE(npf.codigo, npp.codigo) AS prioridade,
          COALESCE(npf.peso_ordenacao, npp.peso_ordenacao) AS peso
        FROM sessoes_triagem st
        JOIN usuarios u ON u.id = st.paciente_usuario_id
        LEFT JOIN avaliacoes_ia ai ON ai.sessao_triagem_id = st.id
        LEFT JOIN revisoes_profissionais rp ON rp.sessao_triagem_id = st.id
        LEFT JOIN niveis_prioridade npp ON npp.id = ai.prioridade_prevista
        LEFT JOIN niveis_prioridade npf ON npf.id = rp.prioridade_final
        WHERE st.status = 'em_fila'
        ORDER BY COALESCE(npf.peso_ordenacao, npp.peso_ordenacao) DESC,
                 st.entrada_fila_em ASC";
        return $pdo->query($sql)->fetchAll();
    }
}
