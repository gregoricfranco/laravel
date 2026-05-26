# Laravel News CRUD

Projeto de estudo em Laravel 13 com foco em qualidade de código e boas práticas de desenvolvimento. Implementa um CRUD de notícias usando **Action Pattern** — controllers finos, regras de negócio isoladas em Actions e um pipeline de qualidade que bloqueia código problemático antes de chegar na `main`.

> A ideia não é só o CRUD funcionar. É demonstrar como PHPStan, Pint, pre-commit hooks e GitHub Actions trabalham juntos para manter o código saudável ao longo do tempo.

---

## Stack

- PHP 8.3+
- Laravel 13
- Laravel Sail (Docker)
- MySQL (local) / SQLite (CI)
- Blade + Tailwind CSS via CDN
- PHPStan + Larastan
- Laravel Pint
- PHPUnit

---

## Arquitetura

O projeto usa **Action Pattern**. Cada operação de negócio vive em sua própria classe dentro de `app/Actions/News/`, mantendo o controller responsável apenas por receber a request e devolver uma resposta.

```
app/
├── Actions/News/
│   ├── CreateNewsAction.php
│   ├── UpdateNewsAction.php
│   ├── DeleteNewsAction.php
│   ├── GenerateNewsSlugAction.php
│   ├── UploadNewsImageAction.php
│   └── SearchNewsAction.php
├── Http/
│   ├── Controllers/NewsController.php
│   └── Requests/
│       ├── StoreNewsRequest.php
│       └── UpdateNewsRequest.php
└── Models/News.php

database/
├── factories/NewsFactory.php
├── migrations/
└── seeders/NewsSeeder.php
```

**Por que Action Pattern?**
Evita controllers gordos e Service classes genéricas. Cada Action tem uma responsabilidade única, é fácil de localizar e fácil de testar isoladamente.

---

## Pipeline de Qualidade

Esse é o núcleo do projeto. Três ferramentas trabalhando em camadas:

### Laravel Pint
Garante que o estilo do código PHP segue o padrão do Laravel. Roda automaticamente no pre-commit e no CI — se o código não estiver formatado, o commit é bloqueado.

### PHPStan + Larastan
Análise estática que encontra erros de tipo, retornos incorretos e uso indevido de arrays **antes** da execução. O Larastan estende o PHPStan para entender recursos específicos do Laravel como Models, Collections e Builders.

### PHPUnit
Testes automatizados que validam o comportamento do sistema. A infraestrutura está pronta — factories, seeders e configuração de SQLite para o CI.

### Pre-commit Hook
Versionado em `.github/.githooks/pre-commit`. Bloqueia o commit localmente se qualquer check falhar. Para ativar após clonar:

```bash
git config core.hooksPath .github/.githooks
```

### Commit-msg Hook
Versionado em `.github/.githooks/commit-msg`. Valida a mensagem do commit antes de criá-lo usando **Commitlint**. Se a mensagem não seguir o padrão Conventional Commits, o commit é bloqueado com uma mensagem explicativa:

```
🔍 Validando mensagem de commit...

 Commit inválido.

 Use o padrão Conventional Commits:

  feat: adiciona autenticação JWT
  fix: corrige erro na criação de notícia
  refactor: melhora organização das actions
  chore: ajusta configuração do Docker
  ci: adiciona pipeline do GitHub Actions

  Tipos aceitos:
  feat, fix, docs, style, refactor, test, chore, ci
```

O hook roda automaticamente ao commitar — nenhum comando extra necessário após ativar os hooks.

### GitHub Actions
CI configurado para rodar em todo `push` e `pull_request` nas branches `main` e `dev`. Se o pipeline falhar, o merge é bloqueado.

---

## Fluxo de Trabalho

```
dev → PR → CI passa → revisão → merge na main
```

Antes de abrir qualquer PR, rode:

```bash
make check
```

Esse comando executa os três checks em sequência:

```bash
make lint      # verifica formatação com Pint
make analyse   # roda PHPStan
make test      # executa os testes
```

---

## Setup

**1. Suba o ambiente**
```bash
./vendor/bin/sail up -d
```

**2. Configure o `.env`**
```bash
cp .env.example .env
./vendor/bin/sail artisan key:generate
```

**3. Prepare o banco**
```bash
./vendor/bin/sail artisan migrate --seed
./vendor/bin/sail artisan storage:link
```

**4. Ative o pre-commit hook**
```bash
git config core.hooksPath .github/.githooks
```

**5. Acesse**
```
http://localhost/news
```

---

## Conventional Commits

O projeto adota a convenção de commits para manter o histórico legível:

```
feat: adiciona filtro por categoria na listagem
fix: corrige remoção de imagem ao deletar notícia
refactor: extrai lógica de slug para GenerateNewsSlugAction
test: adiciona testes de feature para criação de notícia
chore: atualiza dependências do composer
ci: adiciona threshold de coverage no GitHub Actions
docs: atualiza README com fluxo de trabalho
```

---

## Próximos Passos

A base de qualidade está pronta. O próximo ciclo é fechar a cobertura de testes:

- [ ] Testes unitários para cada Action
- [ ] Testes de feature para o CRUD completo
- [ ] Testar validações dos Form Requests
- [ ] Testar geração de slug único com casos de borda
- [ ] Testar upload e remoção de imagem com `Storage::fake()`
- [ ] Testar filtros de busca e paginação
- [ ] Migrar para Pest
- [ ] Testes end-to-end com Playwright

---

## Problemas Comuns

**Tabela `news` não existe**
```bash
./vendor/bin/sail artisan migrate
```

**Imagem não aparece após upload**
```bash
./vendor/bin/sail artisan storage:link
```

**Docker parado ao tentar commitar**
O pre-commit hook usa Sail, então o Docker precisa estar rodando antes de commitar. Suba com `./vendor/bin/sail up -d`.