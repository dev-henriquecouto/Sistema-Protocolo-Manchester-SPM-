<?php
// Variaveis de conexão  
const DB_HOST = '';
const DB_NAME = 'spm'; 
const DB_USER = ''; 
const DB_PASS = ''; 
const DB_CHARSET = 'utf8mb4';

const PACIENTE_PADRAO_ID = 2; // <- paciente demo 

// Credencial API - Google 
if (!defined('GOOGLE_CLIENT_ID')) {
    define('GOOGLE_CLIENT_ID', 'yourapikey.apps.googleusercontent.com');
}

// Credencial API - IA 
if (!defined('GEMINI_API_KEY')) {
    // pegue sua API key no Google AI Studio
    define('GEMINI_API_KEY', 'yourapikey'); // deixe vazio '' para usar o fallback local
}

date_default_timezone_set('America/Sao_Paulo');
?>  