# Laravel News CRUD - Projeto de Estudo

Este projeto é uma aplicação Laravel criada para estudo de arquitetura, organização de código e preparação para testes futuros.

O objetivo principal é implementar um CRUD de notícias/artigos usando o padrão **Action Pattern**, mantendo controllers finos, regras de negócio isoladas em Actions e uma estrutura fácil de testar com PHPUnit, Pest, Playwright e Selenium.

> Este projeto não tem objetivo de produção. Ele foi criado como material de aprendizado e prática.

## Tecnologias

- PHP 8.3+
- Laravel 13
- MySQL via Laravel Sail
- Blade
- Tailwind CSS via CDN
- Laravel Eloquent
- Laravel Form Requests
- Laravel Storage
- Laravel Factories e Seeders

## Funcionalidades

- Listar notícias
- Criar notícia
- Editar notícia
- Excluir notícia
- Buscar notícias por título
- Filtrar notícias por status
- Paginação
- Upload de imagem
- Preview da imagem no formulário
- Mensagens de sucesso e erro
- Confirmação antes de excluir
- Empty state quando não houver notícias

## Entidade News

A entidade principal do projeto é `News`.

Campos:

- `id`
- `title`
- `slug`
- `summary`
- `content`
- `category`
- `status`
- `image`
- `published_at`
- `created_at`
- `updated_at`

## Regras de Negócio

- `title` é obrigatório
- `summary` é obrigatório
- `content` é obrigatório
- `category` é obrigatório
- `status` pode ser `draft` ou `published`
- `status` padrão é `draft`
- `slug` é gerado automaticamente a partir do título
- `slug` deve ser único
- `image` é opcional
- `published_at` só é usado quando a notícia está publicada
- Se `status` for `published` e `published_at` vier vazio, o sistema preenche com `now()`
- Ao excluir uma notícia, a imagem vinculada é removida do storage
- Ao trocar uma imagem, a imagem antiga é removida do storage

## Arquitetura

O projeto usa **Action Pattern**.

A ideia é deixar o controller responsável apenas pelo fluxo HTTP:

1. Receber a request
2. Chamar a Action adequada
3. Retornar uma view ou redirect

As regras de negócio ficam dentro de classes de Action em:

```text
app/Actions/News
```

## Estrutura Principal

```text
app/
├── Actions/
│   └── News/
│       ├── CreateNewsAction.php
│       ├── DeleteNewsAction.php
│       ├── GenerateNewsSlugAction.php
│       ├── SearchNewsAction.php
│       ├── UpdateNewsAction.php
│       └── UploadNewsImageAction.php
├── Http/
│   ├── Controllers/
│   │   └── NewsController.php
│   └── Requests/
│       ├── StoreNewsRequest.php
│       └── UpdateNewsRequest.php
└── Models/
    └── News.php

database/
├── factories/
│   └── NewsFactory.php
├── migrations/
│   └── 2026_05_26_000000_create_news_table.php
└── seeders/
    └── NewsSeeder.php

resources/
└── views/
    ├── layouts/
    │   └── app.blade.php
    └── news/
        ├── _form.blade.php
        ├── create.blade.php
        ├── edit.blade.php
        └── index.blade.php
```

## Actions

### `CreateNewsAction`

Responsável por criar uma notícia.

Ela:

- Recebe os dados validados
- Gera o slug automaticamente
- Faz upload da imagem, se existir
- Define `published_at` quando necessário
- Cria o registro dentro de uma transaction

### `UpdateNewsAction`

Responsável por atualizar uma notícia.

Ela:

- Atualiza os dados principais
- Regenera o slug se o título mudar
- Troca a imagem se uma nova for enviada
- Remove a imagem antiga quando necessário
- Define `published_at` quando necessário
- Executa a atualização dentro de uma transaction

### `DeleteNewsAction`

Responsável por excluir uma notícia.

Ela:

- Remove a imagem do storage, se existir
- Exclui o registro do banco

### `GenerateNewsSlugAction`

Responsável por gerar um slug único a partir do título.

Exemplo:

```text
minha-noticia
minha-noticia-2
minha-noticia-3
```

### `UploadNewsImageAction`

Responsável pelo upload da imagem.

Ela usa o disk `public` do Laravel Storage.

### `SearchNewsAction`

Responsável pela listagem com filtros.

Ela:

- Busca por título
- Filtra por status
- Ordena pelos registros mais recentes
- Retorna resultado paginado

## Rotas

As rotas ficam em:

```text
routes/web.php
```

Rotas disponíveis:

```text
GET     /news
GET     /news/create
POST    /news
GET     /news/{news}/edit
PUT     /news/{news}
DELETE  /news/{news}
```

A rota `/` redireciona para `/news`.

## Pré-requisitos

Antes de iniciar, tenha instalado:

- Docker
- Docker Compose

O projeto usa Laravel Sail, então não é obrigatório ter PHP, Composer ou MySQL instalados diretamente na máquina.

## Passo a Passo para Rodar com Laravel Sail

### 1. Instalar dependências

Se ainda não tiver a pasta `vendor`, rode:

```bash
docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v "$(pwd):/var/www/html" \
    -w /var/www/html \
    laravelsail/php85-composer:latest \
    composer install --ignore-platform-reqs
```

Se o `vendor` já existir, pode pular esta etapa.

### 2. Criar o arquivo `.env`

```bash
cp .env.example .env
```

### 3. Subir os containers

```bash
./vendor/bin/sail up -d
```

### 4. Gerar a chave da aplicação

```bash
./vendor/bin/sail artisan key:generate
```

### 5. Rodar as migrations

```bash
./vendor/bin/sail artisan migrate
```

### 6. Rodar os seeders

```bash
./vendor/bin/sail artisan db:seed
```

Ou rode apenas o seeder de notícias:

```bash
./vendor/bin/sail artisan db:seed --class=NewsSeeder
```

### 7. Criar o link público do storage

```bash
./vendor/bin/sail artisan storage:link
```

Esse comando é necessário para exibir as imagens enviadas no formulário.

### 8. Acessar a aplicação

Abra no navegador:

```text
http://localhost/news
```

## Recriar o Banco do Zero

Se quiser apagar todas as tabelas e popular novamente:

```bash
./vendor/bin/sail artisan migrate:fresh --seed
```

Depois, garanta o link do storage:

```bash
./vendor/bin/sail artisan storage:link
```

## Comandos Artisan Usados como Base

Estes são os comandos que poderiam ser usados para gerar a base dos arquivos:

```bash
./vendor/bin/sail artisan make:model News -mfs
./vendor/bin/sail artisan make:controller NewsController
./vendor/bin/sail artisan make:request StoreNewsRequest
./vendor/bin/sail artisan make:request UpdateNewsRequest
```

As Actions foram criadas manualmente, pois o Laravel não possui um generator padrão para esse padrão arquitetural.

## Testes Futuros

Este projeto ainda não possui testes implementados.

Mesmo assim, a estrutura foi pensada para facilitar testes futuros:

- Actions pequenas e com responsabilidade única
- Controller fino
- Validação separada em Form Requests
- Factory para gerar dados de teste
- Seeder para popular dados iniciais
- Regras de negócio fora do Model
- Upload usando Storage, facilitando `Storage::fake()` em testes

Testes que podem ser criados futuramente:

- Testes unitários para Actions
- Testes de feature para rotas do CRUD
- Testes com Pest
- Testes com PHPUnit
- Testes end-to-end com Playwright
- Testes end-to-end com Selenium

## Analise Estatica com PHPStan

O projeto esta preparado para usar PHPStan como ferramenta de analise estatica.

O PHPStan ajuda a encontrar problemas no codigo antes da execucao, como tipos incorretos, chamadas invalidas e possiveis erros em classes, metodos e retornos.

Arquivo de configuracao:

```text
phpstan.neon
```

O arquivo esta configurado inicialmente para analisar:

```text
app
database
routes
```

Tambem foi adicionada a configuracao abaixo no `phpstan.neon`:

```yaml
universalObjectCratesClasses:
    - Illuminate\Database\Eloquent\Model
```

Ela foi usada porque os Models do Laravel Eloquent trabalham com atributos dinamicos, como `title`, `slug`, `status`, `published_at` e outros campos que existem no banco, mas nao aparecem declarados diretamente como propriedades PHP na classe.

Sem essa configuracao, o PHPStan pode apontar falsos positivos ao acessar propriedades dinamicas de Models, mesmo quando elas fazem parte da tabela e estao corretas para o Laravel.

Neste projeto de estudo, essa configuracao deixa a analise estatica mais amigavel para o padrao do Eloquent. Em projetos maiores, uma alternativa mais rigorosa seria documentar propriedades com PHPDoc, usar Larastan de forma mais completa ou criar tipos especificos para os dados das Actions.

Se o PHPStan ainda nao estiver instalado, finalize a instalacao pelo Laravel Sail:

```bash
./vendor/bin/sail composer update phpstan/phpstan --with-dependencies
```

Depois, execute a analise com:

```bash
./vendor/bin/sail php ./vendor/bin/phpstan analyse
```

Observacao: se aparecer a mensagem `Docker or Podman is not running.`, abra o Docker Desktop ou inicie o servico Docker antes de rodar os comandos.

## Antes de Commitar

Antes de criar um commit, rode os comandos abaixo para avaliar erros e formatar o codigo.

Analise estatica com PHPStan:

```bash
./vendor/bin/sail php ./vendor/bin/phpstan analyse
```

Formatacao com Laravel Pint:

```bash
./vendor/bin/sail php ./vendor/bin/pint
```

Depois confira os arquivos alterados:

```bash
git status
```

Esse passo ajuda a manter o projeto consistente, evita subir codigo com problemas simples de tipo ou estilo e deixa o historico do Git mais organizado.

## Comandos Úteis

Listar rotas de notícias:

```bash
./vendor/bin/sail artisan route:list --path=news
```

Limpar cache:

```bash
./vendor/bin/sail artisan optimize:clear
```

Abrir shell dentro do container:

```bash
./vendor/bin/sail shell
```

Rodar o servidor Sail:

```bash
./vendor/bin/sail up -d
```

Parar os containers:

```bash
./vendor/bin/sail down
```

## Problemas Comuns

### Erro: tabela `news` não existe

Mensagem comum:

```text
SQLSTATE[42S02]: Base table or view not found: 1146 Table 'laravel.news' doesn't exist
```

Solução:

```bash
./vendor/bin/sail artisan migrate
```

Se quiser recriar tudo:

```bash
./vendor/bin/sail artisan migrate:fresh --seed
```

### Imagem não aparece após upload

Provavelmente falta criar o link simbólico do storage.

Rode:

```bash
./vendor/bin/sail artisan storage:link
```

### Docker não está rodando

Se aparecer:

```text
Docker or Podman is not running.
```

Abra o Docker Desktop ou inicie o serviço Docker e rode novamente:

```bash
./vendor/bin/sail up -d
```

## Observação sobre o Estudo

Este projeto foi criado para praticar organização de código em Laravel.

O foco não é criar a menor quantidade possível de arquivos, mas sim separar responsabilidades de forma clara:

- Requests validam
- Controllers coordenam o fluxo HTTP
- Actions executam regras de negócio
- Models representam os dados
- Migrations definem a estrutura do banco
- Factories e Seeders ajudam na preparação para testes e dados de exemplo

Essa separação deixa o projeto mais fácil de entender, manter e testar.
