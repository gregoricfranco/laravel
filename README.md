# Laravel News CRUD - Estudo de Qualidade e Testes

Este projeto é um estudo em Laravel para praticar arquitetura, organização de código e fluxo de qualidade antes de evoluir para uma suíte completa de testes.

O sistema implementa um CRUD de notícias/artigos usando **Action Pattern**. A ideia é manter controllers finos, regras de negócio em Actions e uma base preparada para PHPUnit, Pest, Playwright e Selenium.

> Este projeto é didático. As ferramentas de qualidade existem para demonstrar como PHPStan, Pint, hooks de Git e GitHub Actions ajudam a impedir que código problemático avance sem visibilidade.

## Objetivo

O objetivo não é apenas criar o CRUD, mas estudar um fluxo de desenvolvimento com qualidade:

- Separar responsabilidades com Action Pattern
- Validar entrada com Form Requests
- Manter regras de negócio fora do controller
- Usar análise estática com PHPStan/Larastan
- Usar formatação com Laravel Pint
- Bloquear commits quando checks falharem
- Rodar validações no GitHub Actions
- Preparar o projeto para testes automatizados futuros

## Stack

- PHP 8.3+
- Laravel 13
- Laravel Sail
- MySQL no ambiente local com Sail
- SQLite no GitHub Actions
- Blade
- Tailwind CSS via CDN
- PHPStan/Larastan
- Laravel Pint
- PHPUnit

## Funcionalidades do CRUD

- Listagem de notícias
- Criação de notícia
- Edição de notícia
- Exclusão de notícia
- Busca por título
- Filtro por status
- Paginação
- Upload de imagem
- Preview de imagem no formulário
- Mensagens de sucesso e erro
- Confirmação antes de excluir
- Empty state quando não houver notícias

## Entidade News

Campos principais:

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

Regras principais:

- `title`, `summary`, `content` e `category` são obrigatórios
- `status` pode ser `draft` ou `published`
- O status padrão é `draft`
- O slug é gerado automaticamente a partir do título
- O slug deve ser único
- A imagem é opcional
- Se `status` for `published` e `published_at` vier vazio, o sistema preenche com `now()`
- Ao excluir uma notícia, a imagem associada deve ser removida do storage

## Arquitetura

O projeto usa **Action Pattern**.

O controller deve apenas receber a request, chamar a Action correta e retornar uma view ou redirect.

As regras de negócio ficam em:

```text
app/Actions/News
```

Actions criadas:

- `CreateNewsAction`
- `UpdateNewsAction`
- `DeleteNewsAction`
- `GenerateNewsSlugAction`
- `UploadNewsImageAction`
- `SearchNewsAction`

Estrutura principal:

```text
app/
├── Actions/News/
├── Http/Controllers/NewsController.php
├── Http/Requests/StoreNewsRequest.php
├── Http/Requests/UpdateNewsRequest.php
└── Models/News.php

database/
├── factories/NewsFactory.php
├── migrations/
└── seeders/NewsSeeder.php

resources/views/
├── layouts/app.blade.php
└── news/
```

## Rodando o Projeto

Suba os containers:

```bash
./vendor/bin/sail up -d
```

Crie o `.env`, caso ainda não exista:

```bash
cp .env.example .env
```

Gere a chave da aplicação:

```bash
./vendor/bin/sail artisan key:generate
```

Rode as migrations e seeders:

```bash
./vendor/bin/sail artisan migrate --seed
```

Crie o link do storage:

```bash
./vendor/bin/sail artisan storage:link
```

Acesse:

```text
http://localhost/news
```

## Qualidade de Código

Este projeto foi configurado para usar ferramentas que ajudam a manter o código consistente e a evitar que problemas simples avancem.

### Laravel Pint

O Pint verifica e formata o estilo do código PHP.

Rodar em modo verificação:

```bash
./vendor/bin/sail php ./vendor/bin/pint --test
```

Formatar o código:

```bash
./vendor/bin/sail php ./vendor/bin/pint
```

Configuração:

```text
pint.json
```

O projeto usa o preset `laravel` e não força `declare(strict_types=1);` em todos os arquivos, para manter o padrão mais próximo do Laravel.

### PHPStan e Larastan

O PHPStan faz análise estática e encontra problemas antes da execução do código.

Rodar:

```bash
./vendor/bin/sail php ./vendor/bin/phpstan analyse
```

Configuração:

```text
phpstan.neon
```

O Larastan foi adicionado para melhorar a leitura do código Laravel pelo PHPStan, especialmente em Models, Builders, Collections e recursos do framework.

## Antes de Commitar

Antes de criar um commit, o código deve passar pelos checks de qualidade:

```bash
./vendor/bin/sail php ./vendor/bin/pint --test
./vendor/bin/sail php ./vendor/bin/phpstan analyse
./vendor/bin/sail artisan test
```

Esses comandos ajudam a evitar:

- Código fora do padrão de formatação
- Erros de tipo
- Uso incorreto de arrays e retornos
- Regressões em funcionalidades existentes
- Commits que deixam o projeto quebrado sem perceber

## Pre-commit Hook

O projeto possui um hook versionado em:

```text
.github/.githooks/pre-commit
```

Ele roda automaticamente antes do commit:

```bash
./vendor/bin/sail php ./vendor/bin/pint --test
./vendor/bin/sail php ./vendor/bin/phpstan analyse
./vendor/bin/sail artisan test
```

Se qualquer comando falhar, o commit é bloqueado.

Para ativar o hook no clone local:

```bash
git config core.hooksPath .github/.githooks
```

Como os comandos usam Laravel Sail, o Docker precisa estar rodando antes de commitar.

## GitHub Actions

O projeto possui CI em:

```text
.github/workflows/ci.yml
```

Ele roda em `push` e `pull_request` para:

- `main`
- `dev`

Checks executados no CI:

```bash
vendor/bin/pint --test
vendor/bin/phpstan analyse
php artisan test
```

No GitHub Actions, os testes usam SQLite. O workflow cria `database/database.sqlite`, roda as migrations e executa os testes.

## Como Testar o Bloqueio de Qualidade

O projeto deve permanecer sem erros conhecidos para permitir commits normais.

Para testar se o PHPStan, o pre-commit e o GitHub Actions estão funcionando, crie uma falha temporária em uma branch de estudo e desfaça antes do commit final.

Exemplo simples em uma Action:

```php
// Exemplo temporario apenas para testar o PHPStan.
$title = $data['title'];
$this->generateNewsSlugAction->execute($title);
```

Se o PHPStan não conseguir garantir que `$title` é uma string, ele pode apontar erro de tipo.

Outro exemplo temporário:

```php
// Exemplo temporario apenas para testar o PHPStan.
$data['image'] = $this->uploadNewsImageAction->execute($data['image'], null);
```

Se `image` não existir no array ou estiver tipado como `mixed`, o PHPStan deve apontar erro.

Depois de criar a falha, rode:

```bash
./vendor/bin/sail php ./vendor/bin/phpstan analyse
```

Ou tente commitar normalmente:

```bash
git commit -m "Teste de bloqueio"
```

O commit deve ser bloqueado se algum check falhar.

Depois do teste, desfaça a alteração temporária e rode novamente:

```bash
./vendor/bin/sail php ./vendor/bin/pint --test
./vendor/bin/sail php ./vendor/bin/phpstan analyse
./vendor/bin/sail artisan test
```

Esse exercício demonstra que:

- O PHPStan consegue encontrar problemas antes da execução
- O pre-commit bloqueia commits quando algo falha
- O GitHub Actions deixa problemas visíveis na branch ou no pull request
- O código precisa voltar a um estado saudável antes de ser commitado

O fluxo recomendado é não manter erros propositais no histórico principal. Use falhas temporárias apenas para estudo ou validação das ferramentas.

## Branches

Fluxo usado no estudo:

- `main`: branch principal
- `dev`: branch de desenvolvimento

O fluxo esperado é trabalhar na `dev`, abrir pull request para `main` e usar os checks do GitHub Actions como proteção.

## Próximos Passos

Os próximos passos do projeto são focados em testes.

Sugestões:

- Criar testes unitários para as Actions
- Criar testes de feature para o CRUD de notícias
- Testar validações dos Form Requests
- Testar geração de slug único
- Testar upload e remoção de imagem com `Storage::fake()`
- Testar filtros de busca e status
- Testar paginação
- Adicionar Pest como alternativa ao PHPUnit
- Criar testes end-to-end com Playwright
- Criar testes end-to-end com Selenium

## Comandos Úteis

Ver rotas:

```bash
./vendor/bin/sail artisan route:list --path=news
```

Recriar banco com seed:

```bash
./vendor/bin/sail artisan migrate:fresh --seed
```

Limpar caches:

```bash
./vendor/bin/sail artisan optimize:clear
```

Subir Sail:

```bash
./vendor/bin/sail up -d
```

Parar Sail:

```bash
./vendor/bin/sail down
```

## Problemas Comuns

Erro de tabela `news` inexistente:

```text
SQLSTATE[42S02]: Base table or view not found: 1146 Table 'laravel.news' doesn't exist
```

Solução:

```bash
./vendor/bin/sail artisan migrate
```

Imagem não aparece após upload:

```bash
./vendor/bin/sail artisan storage:link
```

Docker parado:

```text
Docker or Podman is not running.
```

Solução: abra o Docker Desktop ou inicie o serviço Docker antes de rodar comandos com Sail.
