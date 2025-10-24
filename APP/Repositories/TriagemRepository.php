<?php
require_once __DIR__ . '/../database.php';

final class TriagemRepository
{
    public static function criarSessao(
        int $pacienteId,
        string $queixa,
        string $sintomas,
        string $antecedentes,
        string $alergias,
        string $medicamentos,
        string $codigoChamada
    ): int {
        $pdo = Database::get();
        $st = $pdo->prepare("
            INSERT INTO sessoes_triagem
              (paciente_usuario_id, status, queixa_principal, sintomas_texto, antecedentes_texto, alergias_texto, medicamentos_texto, codigo_chamada)
            VALUES (?, 'pendente_ia', ?, ?, ?, ?, ?, ?)
        ");
        $st->execute([$pacienteId, $queixa, $sintomas, $antecedentes, $alergias, $medicamentos, $codigoChamada]);
        return (int)$pdo->lastInsertId();
    }

    public static function inserirConsentimento(int $pacienteId, int $sessaoId, int $concedido, ?string $ip, ?string $ua): void
    {
        $pdo = Database::get();
        $st = $pdo->prepare("
            INSERT INTO consentimentos
              (paciente_usuario_id, sessao_triagem_id, tipo_consentimento, concedido, ip, user_agent)
            VALUES (?, ?, 'triagem_mvp', ?, ?, ?)
        ");
        $st->execute([$pacienteId, $sessaoId, $concedido, $ip, $ua]);
    }

    public static function obterNivelIdPorCodigo(string $codigo): ?int
    {
        $pdo = Database::get();
        $st = $pdo->prepare("SELECT id FROM niveis_prioridade WHERE codigo=? LIMIT 1");
        $st->execute([$codigo]);
        $r = $st->fetch();
        return $r ? (int)$r['id'] : null;
    }

    public static function obterNivelMaisAltoPeso(): ?int
    {
        $pdo = Database::get();
        $r = $pdo->query("SELECT id FROM niveis_prioridade ORDER BY peso_ordenacao DESC LIMIT 1")->fetch();
        return $r ? (int)$r['id'] : null;
    }

    public static function salvarAvaliacaoIA(
        int $sessaoId, int $nivelId, float $confianca, string $laudo, string $modelo, string $versao
    ): void {
        $pdo = Database::get();
        $st = $pdo->prepare("
            INSERT INTO avaliacoes_ia
              (sessao_triagem_id, prioridade_prevista, confianca, laudo_ia, modelo_nome, modelo_versao, payload_bruto)
            VALUES (?, ?, ?, ?, ?, ?, NULL)
        ");
        $st->execute([$sessaoId, $nivelId, $confianca, $laudo, $modelo, $versao]);
    }

    public static function colocarEmFila(int $sessaoId): void
    {
        $pdo = Database::get();
        $st  = $pdo->prepare("UPDATE sessoes_triagem SET status='em_fila', entrada_fila_em=NOW() WHERE id=?");
        $st->execute([$sessaoId]);
    }

    public static function detalharSessao(int $sessaoId): ?array
    {
        $pdo = Database::get();
        $sql = "
        SELECT
          st.id AS sessao_id, st.status, st.codigo_chamada,
          st.queixa_principal, st.sintomas_texto, st.antecedentes_texto, st.alergias_texto, st.medicamentos_texto,
          u.nome AS paciente_nome, u.email AS paciente_email,
          ai.confianca AS ia_confianca, ai.laudo_ia,
          npp.id AS prioridade_prevista_id, npp.codigo AS prioridade_prevista,
          npf.id AS prioridade_final_id,   npf.codigo AS prioridade_final
        FROM sessoes_triagem st
        JOIN usuarios u ON u.id = st.paciente_usuario_id
        LEFT JOIN avaliacoes_ia ai ON ai.sessao_triagem_id = st.id
        LEFT JOIN revisoes_profissionais rp ON rp.sessao_triagem_id = st.id
        LEFT JOIN niveis_prioridade npp ON npp.id = ai.prioridade_prevista
        LEFT JOIN niveis_prioridade npf ON npf.id = rp.prioridade_final
        WHERE st.id = ? LIMIT 1";
        $st = $pdo->prepare($sql);
        $st->execute([$sessaoId]);
        $r = $st->fetch();
        return $r ?: null;
    }

    public static function listarPrioridades(): array
    {
        $pdo = Database::get();
        return $pdo->query("SELECT id, codigo FROM niveis_prioridade ORDER BY peso_ordenacao DESC")->fetchAll();
    }

    public static function upsertRevisao(int $sessaoId, int $revisorId, int $prioridadeId, string $obs): void
    {
        $pdo = Database::get();
        $sql = "
        INSERT INTO revisoes_profissionais (sessao_triagem_id, revisor_usuario_id, prioridade_final, observacoes, revisado_em)
        VALUES (?, ?, ?, ?, NOW())
        ON DUPLICATE KEY UPDATE
          revisor_usuario_id = VALUES(revisor_usuario_id),
          prioridade_final   = VALUES(prioridade_final),
          observacoes        = VALUES(observacoes),
          revisado_em        = VALUES(revisado_em)";
        $st = $pdo->prepare($sql);
        $st->execute([$sessaoId, $revisorId, $prioridadeId, $obs]);
    }

    public static function chamarSessao(int $sessaoId): void
    {
        $pdo = Database::get();
        $st  = $pdo->prepare("UPDATE sessoes_triagem SET status='chamado', saida_fila_em=NOW() WHERE id=?");
        $st->execute([$sessaoId]);
    }

    // leo 
    public function obterStatusSessao(int $sessaoId): ?string {
    $pdo = Database::get();
    $sql = "SELECT status FROM sessoes_triagem WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$sessaoId]);
    $row = $stmt->fetch(\PDO::FETCH_ASSOC);
    return $row['status'] ?? null;
    }

    public function obterResumoSessao(int $sessaoId): ?array {
        $pdo = Database::get();
        $sql = "SELECT s.id, s.status, s.codigo_chamada, s.paciente_id
                FROM sessoes_triagem s
                WHERE s.id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$sessaoId]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function obterUsuarioIdPorCodigoChamada(string $codigo): ?int
{
    $pdo = Database::get();
    $sql = " SELECT
            st.id AS sessao_id,
            st.paciente_usuario_id,
            st.codigo_chamada,
            COALESCE(npf.codigo, npp.codigo) AS prioridade
        FROM sessoes_triagem st
        LEFT JOIN revisoes_profissionais rp ON rp.sessao_id = st.id
        LEFT JOIN niveis_prioridade npf ON npf.codigo = rp.prioridade_final
        LEFT JOIN avaliacoes_ia ai ON ai.sessao_id = st.id
        LEFT JOIN niveis_prioridade npp ON npp.codigo = ai.prioridade_prevista
        WHERE st.codigo_chamada = :codigo
        LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':codigo', $codigo, \PDO::PARAM_STR);
    $stmt->execute();
    $row = $stmt->fetch(\PDO::FETCH_ASSOC);
    return $row ? (int)$row['paciente_usuario_id'] : null;
}


}
