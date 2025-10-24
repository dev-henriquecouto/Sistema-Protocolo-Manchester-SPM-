<?php
namespace APP\Events;

final class PacienteChamadoEvent
{
    public const NAME = 'paciente.chamado';
    /**
     * Payload esperado:
     * - sessao_id (int)
     * - paciente_usuario_id (int)
     * - codigo_chamada (string)
     * - prioridade (string|null)  // final se existir, senão prevista
     */
}
