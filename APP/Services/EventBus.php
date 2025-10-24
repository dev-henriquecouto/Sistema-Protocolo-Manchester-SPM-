<?php
namespace APP\Services;

/**
 * EventBus mínimo: subscribe por nome de evento e publish com payload imutável.
 * Não depende de framework e mantém baixo acoplamento.
 */
class EventBus
{
    /** @var array<string, array<int, callable>> */
    private array $listeners = [];

    public function subscribe(string $eventName, callable $listener): void
    {
        if (!isset($this->listeners[$eventName])) {
            $this->listeners[$eventName] = [];
        }
        $this->listeners[$eventName][] = $listener;
    }

    /**
     * @param string $eventName
     * @param array<string,mixed> $payload  Dados do evento (somente leitura pelo contrato)
     */
    public function publish(string $eventName, array $payload = []): void
    {
        if (empty($this->listeners[$eventName])) {
            return;
        }

        // Copia para evitar mutação acidental por listeners
        $data = $payload;

        foreach ($this->listeners[$eventName] as $listener) {
            try {
                $listener($data);
            } catch (\Throwable $e) {
                // Logue se tiver logger; aqui silenciamos para não quebrar o fluxo principal
                // error_log("[EventBus] Listener error on {$eventName}: " . $e->getMessage());
            }
        }
    }
}
