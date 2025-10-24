<?php
// Variaveis de conexão  
const DB_HOST = '127.0.0.';
const DB_NAME = 'Your_database'; 
const DB_USER = 'your_user'; 
const DB_PASS = 'your_pass'; 
const DB_CHARSET = 'utf8mb4';

const PACIENTE_PADRAO_ID = 2; // <- paciente demo 

// Credencial API - Google 
if (!defined('GOOGLE_CLIENT_ID')) {
    define('GOOGLE_CLIENT_ID', 'YourClientID.apps.googleusercontent.com');
}

// Credencial API - IA 
if (!defined('GEMINI_API_KEY')) {
    // pegue sua API key no Google AI Studio
    define('GEMINI_API_KEY', 'Your Key'); // deixe vazio '' para usar o fallback local
}

date_default_timezone_set('America/Sao_Paulo');

// Nome padrão do hospital (pode trocar por algo vindo do .env)
if (!defined('HOSPITAL_NOME')) {
    define('HOSPITAL_NOME', 'Hospital SPM');
}

?>  
