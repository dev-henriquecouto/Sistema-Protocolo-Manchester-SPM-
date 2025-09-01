<?php

// Tudo certinho :)) brigado 
final class GeminiIAService
{
    // Retorna ['codigo' => 'LARANJA', 'justificativa' => '...', 'modelo' => 'gemini-2.0-flash', 'versao' => 'latest']
    // ou null se falhar / chave ausente
    public static function classificar(string $texto): ?array
    {
        $apiKey = defined('GEMINI_API_KEY') ? GEMINI_API_KEY : '';
        if (!$apiKey) return null;

        $endpoint = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=' . urlencode($apiKey);

        // Prompt curto e objetivo, pedindo JSON canônico
        $prompt = "Você é um classificador de risco em pronto-atendimento segundo o Protocolo de Manchester.
Classifique o caso em UMA das cores: VERMELHO, LARANJA, AMARELO, VERDE ou AZUL.
Responda APENAS em JSON com os campos:
- codigo: uma das cinco cores acima
- justificativa: texto curto de 1-2 frases  

Texto clínico:
" . $texto;

        // Para forçar JSON estruturado
        $payload = [
            "contents" => [[ "parts" => [[ "text" => $prompt ]]]],
            "generationConfig" => [
                "responseMimeType" => "application/json",
                "responseSchema" => [
                    "type" => "object",
                    "properties" => [
                        "codigo" => ["type" => "string", "enum" => ["VERMELHO","LARANJA","AMARELO","VERDE","AZUL"]],
                        "justificativa" => ["type" => "string"]
                    ],
                    "required" => ["codigo","justificativa"]
                ],
            ],
        ];

        $ch = curl_init($endpoint);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
            CURLOPT_POSTFIELDS     => json_encode($payload, JSON_UNESCAPED_UNICODE),
            CURLOPT_TIMEOUT        => 12,
        ]);
        $resp = curl_exec($ch);
        $http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err  = curl_error($ch);
        curl_close($ch);

        if ($resp === false || $http >= 400) {
            // log opcional em dev: error_log("Gemini falhou: HTTP $http - $err - $resp");
            return null;
        }

        $data = json_decode($resp, true);

        // A API retorna o JSON estruturado como texto dentro de candidates[0].content.parts[0].text
        $jsonText = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;
        if (!$jsonText) return null;

        $parsed = json_decode($jsonText, true);
        if (!is_array($parsed)) return null;

        $codigo = strtoupper(trim($parsed['codigo'] ?? ''));
        $ok = in_array($codigo, ['VERMELHO','LARANJA','AMARELO','VERDE','AZUL'], true);
        if (!$ok) return null;

        return [
            'codigo'        => $codigo,
            'justificativa' => (string)($parsed['justificativa'] ?? ''),
            'modelo'        => 'gemini-2.0-flash',
            'versao'        => 'latest',
        ];
    }
}
