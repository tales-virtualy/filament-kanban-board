# Changelog

Este arquivo registra apenas mudancas do pacote que entram na release, sem incluir playground, containers ou arquivos locais de teste.

## 2026-05-18 (v1.0.9)

### Instalacao

- Migrations do Kanban passam a rodar automaticamente com `php artisan migrate` (`runsMigrations()` no service provider).
- README reorganizado em passos numerados (composer, plugin, migrate, config, storage, Tailwind).

## 2026-05-18 (v1.0.8)

### Compatibilidade

- Ampliado suporte do Composer para `filament/filament` **^5.0** e `livewire/livewire` **^4.0** (projetos como Filament v5 + Livewire v4).

## 2026-05-18

### Arquivamento de cartoes

- Listas ativas passaram a oferecer botao para mostrar ou ocultar cartoes arquivados.
- Cartoes arquivados aparecem com indicacao visual e podem ser desarquivados direto no board ou no modal do cartao.
- Corrigido `unarchiveAllLists()` para restaurar tambem os cartoes das listas (antes so limpava o arquivamento da lista).
- Corrigido refresh imediato do board apos "Arquivar todas as listas" via evento `refresh-board` no componente Livewire.

Arquivos:
- `src/Models/Board.php`
- `src/Livewire/Boards/BoardView.php`
- `src/Livewire/Boards/CardForm.php`
- `src/Filament/Pages/BoardViewPage.php`
- `resources/views/livewire/boards/view.blade.php`
- `resources/views/livewire/boards/card-form.blade.php`
- `resources/lang/en/kanban.php`
- `resources/lang/pt_BR/kanban.php`

## 2026-05-16

### Modal do cartao

- O formulario do cartao passou a usar layout em duas colunas com CSS embutido no pacote, evitando dependencia de classes Tailwind compiladas no app hospedeiro.
- A modal do cartao foi ampliada (`6xl`) com coluna lateral mais larga para comentarios e atividade.
- Comentarios, anexos, data, checklists (edicao e reordenacao) e feed de atividade foram integrados ao fluxo com refresh entre componentes Livewire.
- Comentarios ganharam edicao/exclusao, layout em balao e tipografia mais legivel na sidebar.
- Botoes de "Adicionar ao cartao" (membros, etiquetas, data, anexos) passaram a exibir rotulo, alinhados ao botao de checklist.
- Rodape Cancel/Save ficou fixo na base da modal durante o scroll do conteudo.

Arquivos:
- `resources/views/livewire/boards/view.blade.php`
- `resources/views/livewire/boards/card-form.blade.php`
- `resources/views/livewire/boards/card/card-comments.blade.php`
- `resources/views/livewire/boards/card/card-activity-feed.blade.php`
- `resources/views/livewire/boards/card/members-popover.blade.php`
- `resources/views/livewire/boards/card/tags-popover.blade.php`
- `resources/views/livewire/boards/card/date-popover.blade.php`
- `resources/views/livewire/boards/card/attachments-popover.blade.php`
- `src/Livewire/Boards/CardForm.php`
- `src/Livewire/Boards/CardChecklists.php`
- `src/Livewire/Boards/Card/CardComments.php`
- `resources/views/livewire/boards/card-checklists.blade.php`

### Quadros privados na listagem

- A listagem de boards passou a exibir todos os quadros ativos/arquivados, inclusive os privados sem acesso do usuario.
- Cards de board sem permissao exibem indicador "Sem permissao" e texto orientativo, sem revelar participantes.
- Ao tentar abrir um board privado sem ser membro, o usuario ve tela de acesso restrito em vez de erro 403, com opcao de voltar a lista.

Arquivos:
- `src/Filament/Pages/BoardListPage.php`
- `src/Filament/Pages/BoardViewPage.php`
- `src/Livewire/Boards/Components/BoardCard.php`
- `resources/views/livewire/boards/components/board-card.blade.php`
- `resources/views/filament/pages/boards/board-view-wrapper.blade.php`
- `resources/lang/en/kanban.php`
- `resources/lang/pt_BR/kanban.php`

## 2026-05-13

### Seguranca e autorizacao

- Politicas de `Board`, `BoardList` e `Card` foram registradas explicitamente no provider do pacote.
- Regras de acesso foram endurecidas para respeitar board publica/privada, membros, admin e owner.
- Exclusao ficou restrita ao owner; archive/unarchive e update seguem permissao administrativa.
- Paginas e componentes Livewire do fluxo de board/card passaram a validar autorizacao no servidor antes de executar acoes.

Arquivos:
- `src/Providers/KanbanServiceProvider.php`
- `src/Policies/BoardPolicy.php`
- `src/Policies/BoardListPolicy.php`
- `src/Policies/CardPolicy.php`
- `src/Models/Board.php`
- `src/Filament/Pages/BoardListPage.php`
- `src/Filament/Pages/BoardCreatePage.php`
- `src/Livewire/Boards/BoardView.php`
- `src/Livewire/Boards/CardForm.php`
- `src/Livewire/Boards/Card/AttachmentsPopover.php`
- `src/Livewire/Boards/Card/CardActivityFeed.php`
- `src/Livewire/Boards/Card/CardAttachments.php`
- `src/Livewire/Boards/Card/CardComments.php`
- `src/Livewire/Boards/Card/DatePopover.php`
- `src/Livewire/Boards/Card/MembersPopover.php`
- `src/Livewire/Boards/Card/TagsPopover.php`
- `src/Livewire/Boards/CardChecklists.php`

### Configuracao e desacoplamento

- Uploads e delecao de anexos deixaram de depender de disk hardcoded.
- O pacote passou a expor configuracao propria para `storage_disk` e `storage_directory`.
- Views de anexos foram ajustadas para consumir a URL calculada pelo model do pacote.

Arquivos:
- `config/kanban.php`
- `src/Models/CardAttachment.php`
- `src/Livewire/Boards/Card/AttachmentsPopover.php`
- `resources/views/livewire/boards/card/card-attachments.blade.php`

### Navegacao e consistencia de estado

- Criacao de board passou a normalizar `is_private` como boolean e respeitar acesso da pagina.
- A rota da visualizacao da board foi corrigida para route model binding.
- O card da board passou a abrir a pagina correta do board.
- O estado do board no Livewire foi estabilizado usando `boardId` e recarga do model por request, evitando falhas de autorizacao e inconsistencias de rehidratacao.

Arquivos:
- `src/Filament/Pages/BoardCreatePage.php`
- `src/Filament/Pages/BoardViewPage.php`
- `src/Livewire/Boards/Components/BoardCard.php`
- `src/Livewire/Boards/BoardView.php`
- `src/Models/Board.php`

### Board, listas e cartoes

- O board view foi refatorado para usar modal nativa do Filament no fluxo de criar/editar cartao.
- Drag-and-drop de listas e cartoes foi refeito com HTML5 drag-and-drop + Alpine + Livewire.
- O drop passou a respeitar a posicao de destino, incluindo insercao antes/depois do item alvo.
- O fluxo de listas arquivadas foi corrigido: mostrar/ocultar arquivadas, desarquivar sem refresh manual e bloqueio de interacoes inconsistentes em listas arquivadas.
- O formulario de criacao de listas passou a limpar estado corretamente entre criacoes.

Arquivos:
- `src/Livewire/Boards/BoardView.php`
- `src/Filament/Pages/BoardViewPage.php`
- `resources/views/filament/pages/boards/board-view-wrapper.blade.php`
- `resources/views/livewire/boards/view.blade.php`

### Membros — feedback visual

- Componente reutilizavel de avatares para exibir participantes do board (owner + membros).
- Avatares e contagem no topo da visualizacao do board, clicaveis para abrir o modal de membros.
- Avatares resumidos nos cards da listagem de boards quando ha mais de um participante.
- Botao de membros no header passa a exibir contagem quando ha mais de um participante.

Arquivos:
- `src/Models/Board.php`
- `src/Filament/Pages/BoardViewPage.php`
- `src/Filament/Pages/BoardListPage.php`
- `resources/views/components/user-avatars.blade.php`
- `resources/views/filament/pages/boards/board-view-wrapper.blade.php`
- `resources/views/livewire/boards/components/board-card.blade.php`
- `resources/lang/en/kanban.php`
- `resources/lang/pt_BR/kanban.php`

### Traducoes e textos do pacote

- Foram adicionadas chaves ausentes usadas pelo fluxo de board/list/card.
- Foram adicionadas mensagens de archive/unarchive de listas em ingles e portugues.

Arquivos:
- `resources/lang/en/kanban.php`
- `resources/lang/pt_BR/kanban.php`
