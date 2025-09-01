<?php
// Variaveis de conexão  
const DB_HOST = '127.0.0.1';
const DB_NAME = 'spm'; 
const DB_USER = 'root'; 
const DB_PASS = 'Aluno@2007'; 
const DB_CHARSET = 'utf8mb4';

const PACIENTE_PADRAO_ID = 2; // <- paciente demo 

// Credencial API - Google 
if (!defined('GOOGLE_CLIENT_ID')) {
    define('GOOGLE_CLIENT_ID', '259247049315-5h1vu8erf6640nkl2jojtpec3nf1l00b.apps.googleusercontent.com');
}

// Credencial API - IA 
if (!defined('GEMINI_API_KEY')) {
    // pegue sua API key no Google AI Studio
    define('GEMINI_API_KEY', 'AIzaSyCT6aIwnJKX7b4_GDIF1Ewv0CyTDsguIWc'); // deixe vazio '' para usar o fallback local
}

date_default_timezone_set('America/Sao_Paulo');
?>  