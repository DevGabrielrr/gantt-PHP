<?php
require_once '../app/Controller/TarefaController.php';

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

$controller = new TarefaController();

switch ($uri) {
    case '/':
        if ($method === 'GET') {
            $controller->index(); // Carrega a view do Gantt
        } else {
            http_response_code(405); // Método não permitido
            echo 'Método não permitido.';
        }
        break;

    case '/tarefas':
        if ($method === 'GET') {
            $controller->listar(); // Retorna JSON das tarefas
        } elseif ($method === 'POST') {
            $controller->atualizar(); // Atualiza tarefa via fetch
        } else {
            http_response_code(405);
            echo 'Método não permitido.';
        }
        break;
    case '/tarefas/criar':
        if ($method === 'POST') {
            $controller->criar();
        } else {
            http_response_code(405);
            echo 'Método não permitido.';
        }
        break;

    case '/tarefas/deletar':
        if ($method === 'POST') {
            $controller->deletar();
        } else {
            http_response_code(405);
            echo 'Método não permitido.';
        }
        break;

    default:
        http_response_code(404);
        echo 'Página não encontrada.';
        break;
}
