<?php

require_once __DIR__ . '../../Conn/conn.php';
require_once __DIR__ . '../../models/HorarioDisponivel.php';

class Tarefa
{
    public static function listarTodas()
    {
        $pdo = conn::getConn();
        $stmt = $pdo->query("SELECT id, id_maquina AS resourceId, inicio AS start, fim AS end, descricao AS title, cor FROM tarefas");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    public static function atualizar($id, $id_maquina, $inicio, $fim)
    {
        $conexao = conn::getConn();

        // Tratamento do formato vindo do FullCalendar
        $inicio = preg_replace('/\.\d+Z?$/', '', $inicio);
        $inicio = str_replace('T', ' ', $inicio);

        $fim = preg_replace('/\.\d+Z?$/', '', $fim);
        $fim = str_replace('T', ' ', $fim);

        // Converte para datetime no formato padrão
        $dtInicio = new DateTime($inicio);
        $dtFim = new DateTime($fim);

        $inicio = $dtInicio->format('Y-m-d H:i:s');
        $fim = $dtFim->format('Y-m-d H:i:s');

        // Tradução do dia da semana para o formato usado no banco
        $diasMap = [
            'Sunday' => 'domingo',
            'Monday' => 'segunda',
            'Tuesday' => 'terca',
            'Wednesday' => 'quarta',
            'Thursday' => 'quinta',
            'Friday' => 'sexta',
            'Saturday' => 'sabado',
        ];

        $diaSemanaIngles = $dtInicio->format('l'); // Ex: 'Monday'
        $diaSemana = $diasMap[$diaSemanaIngles];  // Ex: 'segunda'

        // Consulta se o horário está dentro do intervalo permitido
        $stmt = $conexao->prepare("
        SELECT * FROM horarios_disponiveis 
        WHERE id_maquina = :id_maquina 
          AND dia_semana = :dia_semana 
          AND :hora_inicio >= hora_inicio 
          AND :hora_fim <= hora_fim
    ");
        $stmt->execute([
            ':id_maquina' => $id_maquina,
            ':dia_semana' => $diaSemana,
            ':hora_inicio' => $dtInicio->format('H:i:s'),
            ':hora_fim' => $dtFim->format('H:i:s'),
        ]);
        $disponivel = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$disponivel) {
            return [
                'sucesso' => false,
                'mensagem' => 'Horário fora do período disponível.'
            ];
        }

        // Verifica conflitos com outras tarefas
        if (self::existeConflito($id_maquina, $inicio, $fim, $id)) {
            return [
                'sucesso' => false,
                'mensagem' => 'Horário em conflito com outra tarefa.'
            ];
        }

        // Atualiza tarefa
        $stmt = $conexao->prepare("UPDATE tarefas SET id_maquina = ?, inicio = ?, fim = ? WHERE id = ?");
        $ok = $stmt->execute([$id_maquina, $inicio, $fim, $id]);

        if (!$ok) {
            return [
                'sucesso' => false,
                'mensagem' => 'Erro ao atualizar no banco.'
            ];
        }

        return ['sucesso' => true];
    }





    public static function criar($titulo, $descricao, $id_maquina, $inicio, $fim, $prioridade)
    {
        $pdo = conn::getConn();

        $inicio = preg_replace('/\.\d+Z?$/', '', $inicio);
        $inicio = str_replace('T', ' ', $inicio);

        $fim = preg_replace('/\.\d+Z?$/', '', $fim);
        $fim = str_replace('T', ' ', $fim);

        $inicio_dt = new DateTime($inicio);
        $fim_dt = new DateTime($fim);

        $dias_semana = ['domingo', 'segunda', 'terca', 'quarta', 'quinta', 'sexta', 'sabado'];
        $dia_semana = $dias_semana[$inicio_dt->format('w')];

        $hora_inicio = $inicio_dt->format('H:i:s');
        $hora_fim = $fim_dt->format('H:i:s');

        // Verifica se há disponibilidade
        $stmt = $pdo->prepare("SELECT * FROM horarios_disponiveis WHERE id_maquina = ? AND dia_semana = ? AND hora_inicio <= ? AND hora_fim >= ?");
        $stmt->execute([$id_maquina, $dia_semana, $hora_inicio, $hora_fim]);

        $disponivel = $stmt->fetch();
        if (!$disponivel) {
            return [
                'sucesso' => false,
                'mensagem' => 'Horário indisponível para esta máquina.'
            ];
        }

        // Verifica conflitos
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM tarefas WHERE id_maquina = ? AND inicio < ? AND fim > ?");
        $stmt->execute([$id_maquina, $fim, $inicio]);

        if ($stmt->fetchColumn() > 0) {
            return [
                'sucesso' => false,
                'mensagem' => 'Conflito com outra tarefa já agendada.'
            ];
        }

        $prioridade = strtolower(trim($prioridade)); // normaliza

        $cores = [
            'baixa' => '#1976d2',   // azul
            'media' => '#f9a825',   // amarelo
            'alta' => '#ef5350',    // vermelho
            'critica' => '#7e57c2'  // roxo
        ];


        $cor = isset($cores[$prioridade]) ? $cores[$prioridade] : '#3498db';
        $stmt = $pdo->prepare("INSERT INTO tarefas (titulo, descricao, id_maquina, inicio, fim, prioridade, cor) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $ok = $stmt->execute([$titulo, $descricao, $id_maquina, $inicio, $fim, $prioridade, $cor]);

        if (!$ok) {
            return [
                'sucesso' => false,
                'mensagem' => 'Erro ao salvar tarefa no banco.'
            ];
        }

        return ['sucesso' => true];
    }




    public static function deletar($id)
    {
        $pdo = conn::getConn();
        $stmt = $pdo->prepare("DELETE FROM tarefas WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public static function existeConflito($id_maquina, $inicio, $fim, $id_tarefa_excluir = null)
    {
        $pdo = conn::getConn();

        // Verifica conflito com outras tarefas da mesma máquina
        $sql = "SELECT COUNT(*) FROM tarefas WHERE id_maquina = ? AND inicio < ? AND fim > ?";
        $params = [$id_maquina, $fim, $inicio];

        if ($id_tarefa_excluir) {
            $sql .= " AND id != ?";
            $params[] = $id_tarefa_excluir;
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        $count = $stmt->fetchColumn();
        if ($count > 0) {
            return true;
        }

        // Verifica se o horário está dentro do horário disponível da máquina
        $diaSemana = strtolower(strftime('%A', strtotime($inicio))); // ex: 'monday' → 'segunda'

        // Ajustar nomes do dia para o formato do banco
        $dias_traduzidos = [
            'sunday' => 'domingo',
            'monday' => 'segunda',
            'tuesday' => 'terca',
            'wednesday' => 'quarta',
            'thursday' => 'quinta',
            'friday' => 'sexta',
            'saturday' => 'sabado',
        ];

        $dia_semana = $dias_traduzidos[$diaSemana] ?? null;

        if (!$dia_semana) {
            return true; // Dia inválido, considera como conflito
        }

        $stmt = $pdo->prepare("SELECT hora_inicio, hora_fim FROM horarios_disponiveis WHERE id_maquina = ? AND dia_semana = ?");
        $stmt->execute([$id_maquina, $dia_semana]);
        $horario = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$horario) {
            return true; // Não há disponibilidade definida
        }

        $horaInicio = date('H:i:s', strtotime($inicio));
        $horaFim = date('H:i:s', strtotime($fim));

        if ($horaInicio < $horario['hora_inicio'] || $horaFim > $horario['hora_fim']) {
            return true; // Fora do horário disponível
        }

        return false; // Tudo certo
    }
}
