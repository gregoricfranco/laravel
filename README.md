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
- Playwright

---

## Comandos Principais

```bash
./vendor/bin/sail up -d          # sobe o ambiente
./vendor/bin/sail artisan migrate --seed
./vendor/bin/sail artisan storage:link
git config core.hooksPath .github/.githooks
```

```bash
make lint       # verifica formatação com Pint
make analyse    # roda PHPStan
make test       # executa PHPUnit
make e2e        # executa Playwright
make e2e-report # abre o relatório HTML do Playwright
make check      # roda lint, analyse e test
```

```bash
npm run e2e         # Playwright headless
npm run e2e:headed  # Playwright com navegador visível
npm run e2e:report  # abre o relatório HTML
npm run e2e:ui      # Playwright UI
npx playwright test --ui
```

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

### Playwright
Testes end-to-end que validam o fluxo no navegador real. Os cenários ficam em `tests/e2e` e usam `playwright.config.js`.

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

Para validar o fluxo no navegador:

```bash
make e2e
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

## Playwright

Os testes E2E ficam em:

```text
tests/e2e
```

Por padrão, eles usam:

```text
http://localhost
```

Para usar outra URL:

```bash
PLAYWRIGHT_BASE_URL=http://localhost:8080 npm run e2e
```

Antes de rodar:

```bash
./vendor/bin/sail up -d
./vendor/bin/sail artisan migrate --seed
```

Depois:

```bash
npm run e2e
```

Para abrir o modo interativo do Playwright:

```bash
npx playwright test --ui
```

### Como escrever testes E2E neste projeto

Os testes devem evitar depender dos dados gerados pelo seeder. Prefira criar os próprios dados durante o fluxo do teste, usando valores únicos:

```js
const suffix = Date.now();
const title = `Noticia E2E ${suffix}`;
```

Também prefira seletores acessíveis e estáveis:

```js
page.getByRole('link', { name: 'Nova noticia' });
page.getByLabel('Titulo');
page.getByPlaceholder('Buscar por titulo');
```

Evite seletores frágeis como:

```js
page.locator('tr:nth-child(8)');
```

Quando precisar interagir com uma linha específica da tabela, filtre pelo texto criado no próprio teste:

```js
const row = page.getByRole('row').filter({ hasText: title });
await row.getByRole('link', { name: 'Editar' }).click();
```

Para ações com `confirm()`, registre o listener do diálogo antes do clique:

```js
page.once('dialog', async (dialog) => {
    await dialog.accept();
});
```

Essa abordagem deixa os testes mais fáceis de entender, menos dependentes da ordem da tabela e mais úteis para quem for evoluir o projeto.

Para abrir o relatório do último teste:

```bash
npm run e2e:report
```

---

## Release

As notas da primeira release ficam em:

```text
docs/releases/v0.1.0.md
```

Para criar a release no GitHub e anexar o vídeo de demonstração:

```bash
gh release create v0.1.0 \
  --title "v0.1.0 - Laravel News CRUD com Action Pattern" \
  --notes-file docs/releases/v0.1.0.md \
  "/Users/gregorifranco/Desktop/Gravação de Tela 2026-05-26 às 14.21.43.mov#demo-news-crud-playwright.mov"
```

Se preferir pela interface do GitHub, crie uma release com a tag `v0.1.0`, cole o conteúdo de `docs/releases/v0.1.0.md` e anexe o vídeo como asset.

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
