# Módulo de Agendamento de Tarefas - Gantt de Produção com Restrições de Horário

Este projeto é uma solução para o desafio técnico de criar um módulo de agendamento de tarefas em máquinas, respeitando restrições de horário específicas para cada máquina. As tarefas são exibidas em um gráfico de Gantt interativo, onde o usuário pode:

- Visualizar as tarefas agendadas para cada máquina;
- Arrastar tarefas para alterar horário ou máquina;
- Garantir que os horários respeitem os períodos disponíveis e não haja conflito entre tarefas.

<img width="1892" height="483" alt="gantt" src="https://github.com/user-attachments/assets/1a81442a-2f7d-4d22-b1a9-3414d40a1c0f" />


## Funcionalidades

- Visualização das tarefas em uma timeline semanal (FullCalendar com Scheduler);
- Suporte para múltiplos recursos (máquinas) com seus horários disponíveis definidos;
- Validação de conflito e indisponibilidade ao criar ou atualizar tarefas;
- Modal para criação de novas tarefas com definição de prioridade (que altera a cor da tarefa);
- Exclusão de tarefas ao clicar sobre elas;
- Persistência dos dados no banco MySQL;
- Comunicação via API REST simples em PHP para listar, criar, atualizar e deletar tarefas.

## Tecnologias Utilizadas

- Frontend: FullCalendar Scheduler (JS), HTML, CSS puro;
- Backend: PHP (sem framework), PDO para conexão com MySQL;
- Banco de Dados: MySQL, com tabelas para tarefas, máquinas e horários disponíveis.

## Estrutura do Banco de Dados

- **tarefas:** tabela que armazena as tarefas agendadas, com `id_maquina`, intervalo de tempo (`inicio` e `fim`), título, descrição, prioridade e cor;
- **horarios_disponiveis:** horários disponíveis de cada máquina, definidos por dia da semana e intervalo de horas;
- **maquinas:** cadastro das máquinas (não detalhado nos códigos enviados, mas presumido);

```bash
Endpoints da API
GET /tarefas — Lista todas as tarefas no formato JSON para o FullCalendar;

POST /tarefas/criar — Cria nova tarefa, dados no corpo JSON;

POST /tarefas — Atualiza tarefa existente (usado no drag & drop);

POST /tarefas/deletar — Remove tarefa, passando id no JSON;

onsiderações Técnicas
O backend valida conflito de horários e verifica se a tarefa está dentro do intervalo disponível da máquina;

Os horários são tratados no formato Y-m-d H:i:s para compatibilidade com MySQL DATETIME;

As cores das tarefas são definidas pela prioridade escolhida;

O frontend utiliza FullCalendar Scheduler para oferecer uma interface rica e responsiva;

As tarefas podem ser arrastadas para outra máquina e horário, mas a atualização só é aceita se não houver conflito ou indisponibilidade.
```

## Como Rodar o Projeto

1. **Configurar o banco de dados:**

   - Crie as tabelas `tarefas`, `horarios_disponiveis` e `maquinas` conforme os scripts SQL.
   - Preencha os horários disponíveis para cada máquina.
   
2. **Configurar a conexão no arquivo `app/config.php`:**

   ```php
   <?php
   return [
       'db_host' => 'localhost',
       'db_user' => 'seu_usuario',
       'db_pass' => 'sua_senha',
       'db_name' => 'nome_do_banco'
   ];

3. Rodar em dispositivos específicos

   ```bash
    php -S localhost:8000 -t public
   ```
