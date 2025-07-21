<!DOCTYPE html>
<html>

<head>
  <meta charset="UTF-8">
  <title>Gantt - Agendamento de Tarefas</title>

  <!-- Estilos do FullCalendar -->
  <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.7/main.min.css" rel="stylesheet">

  <!-- Scripts FullCalendar + Scheduler + Localização -->
  <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.7/main.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/fullcalendar-scheduler@6.1.7/index.global.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.7/locales/pt-br.global.min.js"></script>

  <!-- Estilo adicional -->
  <style>
    body {
      margin: 20px;
      font-family: Arial, sans-serif;
      background-color: #f9f9f9;
    }

    #calendar-container {
      overflow-x: auto;
      max-width: 100%;
      border: 1px solid #ddd;
      border-radius: 8px;
      background: #fff;
      padding: 15px;
      box-shadow: 0 0 10px rgba(0, 0, 0, 0.05);
    }

    #calendar {
      max-width: 100%;
      height: auto;
    }

    #modalCriar {
      position: fixed;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -20%);
      background: #fff;
      padding: 20px;
      border-radius: 8px;
      border: 1px solid #ccc;
      box-shadow: 0px 0px 20px rgba(0, 0, 0, 0.1);
      z-index: 9999;
    }

    input,
    select {
      margin: 6px 0;
      padding: 6px;
      width: 100%;
    }

    button {
      margin-top: 10px;
      margin-right: 5px;
    }

    #mensagem {
      margin-top: 10px;
      font-weight: bold;
    }
  </style>
</head>

<body>
  <h2>📅 Gantt - Agendamento de Tarefas por Máquina</h2>

  <div id="calendar-container">
    <div id="calendar"></div>
  </div>

  <div id="mensagem"></div>

  <button style="margin-top:20px;" onclick="abrirModalCriar()">➕ Agendar Tarefa</button>

  <!-- Modal de criação -->
  <div id="modalCriar" style="display:none;">
    <h3>Criar Tarefa</h3>
    <input id="titulo" placeholder="Título" />
    <input id="desc" placeholder="Descrição" />
    <select id="maquina"></select>
    <input type="datetime-local" id="inicio" />
    <input type="datetime-local" id="fim" />
    <select id="prioridade">
      <option value="baixa">🔵 Baixa Prioridade (Azul)</option>
      <option value="media">🟡 Média Prioridade (Amarelo)</option>
      <option value="alta">🔴 Alta Prioridade (Vermelho)</option>
      <option value="critica">🟣 Crítica (Roxo)</option>
    </select>


    <button onclick="salvarTarefa()">Salvar</button>
    <button onclick="fecharModal()">Cancelar</button>
  </div>

  <script src="/assets/js/calendario.js"></script>

</body>

</html>