<?php
namespace APP\Controllers;

require_once __DIR__ . '/../Repositories/TriagemRepository.php';
require_once __DIR__ . '/../Repositories/NotificacaoRepository.php';

use APP\Repositories\NotificacaoRepository;
use PDO;

class NotificacaoController
{
    /**
     * Resolve o usuario_id do paciente de forma segura:
     * 1) Usa sessão existente (se disponível)
     * 2) Em último caso, aceita ?codigo=ABC-123 e resolve para usuario_id correspondente
     *    (e persiste em sessão para as próximas chamadas)
     */
   private static function resolverPacienteUsuarioId(PDO $db): ?int
{
    if (isset($_SESSION['paciente_usuario_id']) && (int)$_SESSION['paciente_usuario_id'] > 0) {
        return (int)$_SESSION['paciente_usuario_id'];
    }

    $codigo = isset($_GET['codigo']) ? trim((string)$_GET['codigo']) : '';
    if ($codigo !== '') {
        // Resolução direta via PDO, evitando dependência de repositório aqui
        $sql = "SELECT paciente_usuario_id
                  FROM sessoes_triagem
                 WHERE codigo_chamada = :codigo
                 LIMIT 1";
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':codigo', $codigo, \PDO::PARAM_STR);
        $stmt->execute();
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($row && isset($row['paciente_usuario_id'])) {
            $usuarioId = (int)$row['paciente_usuario_id'];
            if ($usuarioId > 0) {
                $_SESSION['paciente_usuario_id'] = $usuarioId;
                return $usuarioId;
            }
        }
    }

    return null;
}
    /**
     * GET /?r=api/notificacoes
     * Lista notificações NÃO LIDAS do paciente logado (ou identificado pelo código).
     * Retorna JSON: [{id,tipo,mensagem,metadata,created_at}]
     */
    public static function listar(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        global $db; /** @var \PDO $db */
        if (!$db instanceof \PDO) {
            http_response_code(500);
            echo json_encode(['error' => 'DB indisponível']); return;
        }

        $usuarioId = self::resolverPacienteUsuarioId($db);
        if (!$usuarioId) {
            http_response_code(401);
            echo json_encode(['error' => 'Paciente não identificado']); return;
        }

        $repo = new NotificacaoRepository($db);
        $rows = $repo->listarNaoLidasPorUsuario($usuarioId, 10);

        echo json_encode($rows, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * POST /?r=api/notificacoes/marcar
     * Body JSON: { "ids":[1,2,3] }
     * Marca como lidas as notificações pertencentes ao próprio paciente.
     */
    public static function marcar(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        global $db; /** @var \PDO $db */
        if (!$db instanceof \PDO) {
            http_response_code(500);
            echo json_encode(['error' => 'DB indisponível']); return;
        }

        $usuarioId = self::resolverPacienteUsuarioId($db);
        if (!$usuarioId) {
            http_response_code(401);
            echo json_encode(['error' => 'Paciente não identificado']); return;
        }

        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true);
        $ids  = is_array($data['ids'] ?? null) ? array_map('intval', $data['ids']) : [];

        if (empty($ids)) {
            http_response_code(400);
            echo json_encode(['error' => 'Lista de IDs vazia']); return;
        }

        $repo = new NotificacaoRepository($db);
        $qtd  = $repo->marcarVariasComoLidas($ids, $usuarioId);

        echo json_encode(['updated' => (int)$qtd]);
    }
}
