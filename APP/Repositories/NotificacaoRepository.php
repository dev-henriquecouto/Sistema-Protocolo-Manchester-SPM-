<?php
namespace APP\Repositories;

use PDO;
use PDOException;

class NotificacaoRepository
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Cria uma notificação de forma segura (prepared statements).
     *
     * @param int         $usuarioId  ID do usuário destinatário (paciente)
     * @param string      $tipo       Curto, ex.: 'paciente_chamado'
     * @param string      $mensagem   Texto curto a exibir para o usuário
     * @param array|null  $metadata   Dados extras (sessao_id, codigo_chamada, prioridade, etc.)
     * @return int                    ID da notificação criada
     */
    public function criar(int $usuarioId, string $tipo, string $mensagem, ?array $metadata = null): int
    {
        $sql = "INSERT INTO notificacoes (usuario_id, tipo, mensagem, metadata)
                VALUES (:usuario_id, :tipo, :mensagem, :metadata)";
        $stmt = $this->db->prepare($sql);

        $metaJson = $metadata ? json_encode($metadata, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : null;

        $stmt->bindValue(':usuario_id', $usuarioId, PDO::PARAM_INT);
        $stmt->bindValue(':tipo', $tipo, PDO::PARAM_STR);
        $stmt->bindValue(':mensagem', mb_substr($mensagem, 0, 255), PDO::PARAM_STR);
        $stmt->bindValue(':metadata', $metaJson, $metaJson === null ? PDO::PARAM_NULL : PDO::PARAM_STR);

        if (!$stmt->execute()) {
            throw new PDOException('Falha ao inserir notificação.');
        }

        return (int)$this->db->lastInsertId();
    }

    /**
     * Busca notificações não lidas do usuário (padrão: mais novas primeiro).
     * Use para polling/sse no front do paciente.
     */
    public function listarNaoLidasPorUsuario(int $usuarioId, int $limit = 20): array
    {
        $sql = "SELECT id, tipo, mensagem, metadata, lida, created_at
                  FROM notificacoes
                 WHERE usuario_id = :usuario_id AND lida = 0
              ORDER BY id DESC
                 LIMIT :limit";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':usuario_id', $usuarioId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as &$r) {
            if (isset($r['metadata']) && $r['metadata'] !== null) {
                $decoded = json_decode($r['metadata'], true);
                $r['metadata'] = is_array($decoded) ? $decoded : null;
            }
        }
        return $rows;
    }

    /**
     * Marca uma notificação como lida.
     */
    public function marcarComoLida(int $id, int $usuarioId): bool
    {
        $sql = "UPDATE notificacoes SET lida = 1 WHERE id = :id AND usuario_id = :usuario_id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->bindValue(':usuario_id', $usuarioId, PDO::PARAM_INT);
        return $stmt->execute();
    }

    /**
     * Marca várias notificações como lidas (seguro: só do próprio usuário).
     */
    public function marcarVariasComoLidas(array $ids, int $usuarioId): int
    {
        if (empty($ids)) return 0;

        // Gera placeholders seguros
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $sql = "UPDATE notificacoes SET lida = 1
                  WHERE usuario_id = ? AND id IN ($placeholders)";
        $stmt = $this->db->prepare($sql);

        // Primeiro parâmetro é o usuarioId, depois a lista de ids
        $params = array_merge([$usuarioId], array_map('intval', $ids));

        $stmt->execute($params);
        return $stmt->rowCount(); // quantidade atualizada
    }
}
