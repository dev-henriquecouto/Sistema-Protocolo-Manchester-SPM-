<?php
namespace APP\Observers;

use APP\Repositories\NotificacaoRepository;

class NotificarPacienteChamadoObserver
{
    private NotificacaoRepository $notifs;

    public function __construct(NotificacaoRepository $notifs)
    {
        $this->notifs = $notifs;
    }

    /**
     * @param array<string,mixed> $event
     */
    public function handle(array $event): void
    {
        // Validação defensiva do payload
        $sessaoId  = isset($event['sessao_id']) ? (int)$event['sessao_id'] : 0;
        $usuarioId = isset($event['paciente_usuario_id']) ? (int)$event['paciente_usuario_id'] : 0;
        $codigo    = isset($event['codigo_chamada']) ? (string)$event['codigo_chamada'] : '';
        $prio      = isset($event['prioridade']) ? (string)$event['prioridade'] : null;

        if ($sessaoId <= 0 || $usuarioId <= 0 || $codigo === '') {
            return; // payload inválido -> ignora silenciosamente
        }

        $mensagem = 'Chegou a sua vez na fila, por favor se dirija ao consultório.';
        $metadata = [
            'sessao_id'       => $sessaoId,
            'codigo_chamada'  => $codigo,
            'prioridade'      => $prio,
            'motivo'          => 'paciente_chamado'
        ];

        // Persistência segura; exceções internas são capturadas no EventBus publish()
        $this->notifs->criar($usuarioId, 'paciente_chamado', $mensagem, $metadata);
    }
}
