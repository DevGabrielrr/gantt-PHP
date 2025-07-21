<?php

require_once __DIR__ . '/../Conn/conn.php';

class HorarioDisponivel
{
    public static function verificarDisponibilidade($id_maquina, $inicio, $fim)
    {
        $pdo = conn::getConn();

        // Traduz dia da semana
        $diaSemanaIngles = strtolower(date('l', strtotime($inicio))); // ex: "monday"
        $mapa = [
            'sunday' => 'domingo',
            'monday' => 'segunda',
            'tuesday' => 'terca',
            'wednesday' => 'quarta',
            'thursday' => 'quinta',
            'friday' => 'sexta',
            'saturday' => 'sabado'
        ];

        $diaTraduzido = $mapa[$diaSemanaIngles];

        $horaInicio = date('H:i:s', strtotime($inicio));
        $horaFim = date('H:i:s', strtotime($fim));

        $stmt = $pdo->prepare("SELECT * FROM horarios_disponiveis WHERE id_maquina = ? AND dia_semana = ?");
        $stmt->execute([$id_maquina, $diaTraduzido]);
        $horarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($horarios as $horario) {
            if (
                $horaInicio >= $horario['hora_inicio'] &&
                $horaFim <= $horario['hora_fim']
            ) {
                return true;
            }
        }

        return false;
    }
}
