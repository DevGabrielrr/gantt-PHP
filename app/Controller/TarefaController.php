<?php
require_once __DIR__ . '../../models/Tarefa.php';

class TarefaController
{
    public function index()
    {
        require_once __DIR__ . '../../views/gantt/index.php';
    }

    public function listar()
    {
        header('Content-Type: application/json');
        echo json_encode(Tarefa::listarTodas());
    }

    public function atualizar()
    {
        $data = json_decode(file_get_contents('php://input'), true);
        

        if (!isset($data['id'], $data['id_maquina'], $data['inicio'], $data['fim'])) {
            echo json_encode([
                'sucesso' => false,
                'mensagem' => 'Campos obrigatórios ausentes.'
            ]);
            return;
        }

        $resultado = Tarefa::atualizar($data['id'], $data['id_maquina'], $data['inicio'], $data['fim']);

        if (!$resultado['sucesso']) {
            echo json_encode([
                'sucesso' => false,
                'mensagem' => $resultado['mensagem'] ?? 'Erro desconhecido.'
            ]);
            return;
        }

        echo json_encode(['sucesso' => true]);
    }

    public function criar()
    {
        $data = json_decode(file_get_contents('php://input'), true);

        if (!isset($data['titulo'], $data['id_maquina'], $data['inicio'], $data['fim'], $data['prioridade'])) {

            echo json_encode([
                'sucesso' => false,
                'mensagem' => 'Campos obrigatórios ausentes.'
            ]);
            return;
        }

        $descricao = $data['descricao'] ?? ''; // opcional

        $resultado = Tarefa::criar(
            $data['titulo'],
            $descricao,
            $data['id_maquina'],
            $data['inicio'],
            $data['fim'],
            $data['prioridade']
        );

        if (is_array($resultado) && isset($resultado['sucesso']) && !$resultado['sucesso']) {
            echo json_encode(['sucesso' => false, 'mensagem' => $resultado['mensagem']]);
            return;
        }

        echo json_encode(['sucesso' => true]);
    }

    public function deletar()
    {
        $data = json_decode(file_get_contents('php://input'), true);

        if (!isset($data['id'])) {
            echo json_encode([
                'sucesso' => false,
                'mensagem' => 'ID da tarefa é obrigatório.'
            ]);
            return;
        }

        $ok = Tarefa::deletar($data['id']);
        echo json_encode(['sucesso' => $ok]);
    }
}
