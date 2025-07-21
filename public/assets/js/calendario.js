let calendar;

document.addEventListener('DOMContentLoaded', function () {
  const calendarEl = document.getElementById('calendar');

  calendar = new FullCalendar.Calendar(calendarEl, {
    timeZone: 'America/Sao_Paulo',
    locale: 'pt-br',
    schedulerLicenseKey: 'CC-Attribution-NonCommercial-NoDerivatives',
    initialView: 'resourceTimelineWeek',
    initialDate: new Date(),
    slotMinWidth: 60,
    slotDuration: '00:30:00',
    slotLabelFormat: {
      hour: '2-digit',
      minute: '2-digit',
      hour12: false
    },
    height: 'auto',
    headerToolbar: {
      left: 'prev,next today',
      center: 'title',
      right: 'resourceTimelineDay,resourceTimelineWeek,resourceTimelineMonth'
    },
    resources: function (fetchInfo, successCallback, failureCallback) {
      fetch('/buscarMaquinas.php')
        .then(response => response.json())
        .then(data => successCallback(data))
        .catch(error => {
          console.error('Erro ao carregar máquinas:', error);
          failureCallback(error);
        });
    },
    events: '/tarefas',
    editable: true,
    selectable: true,
    nowIndicator: true,
    eventDrop: function (info) {
      fetch('/tarefas', {
        method: 'POST',
        body: JSON.stringify({
          id: info.event.id,
          id_maquina: info.event.getResources()[0].id,
          inicio: info.event.start.toISOString(),
          fim: info.event.end.toISOString()
        }),
        headers: {
          'Content-Type': 'application/json'
        }
      })
        .then(res => res.json())
        .then(data => {
          if (!data.sucesso) {
            info.revert();
            alert(data.mensagem || 'Horário indisponível ou conflito com outra tarefa.');
          } else {
            mostrarMensagem('Tarefa atualizada com sucesso!', 'green');   
          }
        })
        .catch(() => {
          info.revert();
          alert('Erro ao atualizar tarefa!');
        });
    },
    eventClick: function (info) {
      if (confirm('Deseja deletar esta tarefa?')) {
        fetch('/tarefas/deletar', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({ id: info.event.id })
        })
          .then(res => res.json())
          .then(json => {
            if (json.sucesso) {
              info.event.remove();
              mostrarMensagem('Tarefa excluída!', 'green');
            } else {
              alert('Erro ao excluir.');
            }
          });
      }
    },
    loading: function (isLoading) {
      if (!isLoading) {
        console.log('Eventos carregados:', calendar.getEvents());
      }
    },
    eventDidMount: function (info) {
      const cor = info.event.extendedProps.cor || '#1976d2'; // Cor padrão azul
      info.el.style.backgroundColor = cor;
      info.el.style.borderRadius = '6px';
      info.el.style.color = '#fff';
      info.el.style.border = 'none';
      info.el.style.padding = '2px 6px';
    }
  });

  calendar.render();
});

function abrirModalCriar() {
  document.getElementById('modalCriar').style.display = 'block';
  document.getElementById('mensagem').innerText = '';

  fetch('/buscarMaquinas.php')
    .then(res => res.json())
    .then(data => {
      const sel = document.getElementById('maquina');
      sel.innerHTML = '';
      data.forEach(m => {
        const opt = document.createElement('option');
        opt.value = m.id;
        opt.text = m.title;
        sel.add(opt);
      });
    });
}

function fecharModal() {
  document.getElementById('modalCriar').style.display = 'none';
}

function mostrarMensagem(msg, cor = 'black') {
  const el = document.getElementById('mensagem');
  el.innerText = msg;
  el.style.color = cor;
}

function limparFormulario() {
  document.getElementById('titulo').value = '';
  document.getElementById('desc').value = '';
  document.getElementById('maquina').selectedIndex = 0;
  document.getElementById('inicio').value = '';
  document.getElementById('fim').value = '';
}

function salvarTarefa() {
  const titulo = document.getElementById('titulo').value.trim();
  const descricao = document.getElementById('desc').value.trim();
  const id_maquina = document.getElementById('maquina').value;
  const inicio = document.getElementById('inicio').value;
  const fim = document.getElementById('fim').value;
  const prioridade  = document.getElementById('prioridade').value;


  if (!titulo || !id_maquina || !inicio || !fim || !prioridade) {
    mostrarMensagem('Preencha todos os campos obrigatórios.', 'red');
    return;
  }

  const data = {
    titulo,
    descricao,
    id_maquina,
    inicio,
    fim,
    prioridade
  };

  fetch('/tarefas/criar', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify(data)
  })
    .then(res => res.json())
    .then(json => {
      if (json.sucesso) {
        mostrarMensagem('Tarefa criada com sucesso!', 'green');
        fecharModal();
        limparFormulario();
        calendar.refetchEvents();
      } else {
        mostrarMensagem(json.mensagem || 'Erro ao criar tarefa', 'red');
      }
    });
}
