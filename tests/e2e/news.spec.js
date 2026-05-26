// @ts-check
import { expect, test } from '@playwright/test';

test('lista de notícias mostra ação de criar e estado vazio ou tabela', async ({ page }) => {
  // Acessa a página de listagem de notícias
  await page.goto('/news');

  // Verifica se o título da página está visível
  await expect(page.getByRole('heading', { name: 'Noticias' })).toBeVisible();

  // Verifica se o link para criar uma nova notícia está visível
  await expect(page.getByRole('link', { name: 'Nova noticia' })).toBeVisible();

  // Verifica se o campo de busca por título está visível
  await expect(page.getByPlaceholder('Buscar por titulo')).toBeVisible();

  // Localiza a mensagem de estado vazio
  const estadoVazio = page.getByText('Nenhuma noticia encontrada');

  // Localiza a tabela de notícias
  const tabela = page.locator('table');

  // A página deve mostrar ou a mensagem de vazio ou a tabela
  await expect(estadoVazio.or(tabela)).toBeVisible();
});

test('usuário consegue abrir o formulário de criação de notícia', async ({ page }) => {
  // Acessa a listagem de notícias
  await page.goto('/news');

  // Clica no link para criar uma nova notícia
  await page.getByRole('link', { name: 'Nova noticia' }).click();

  // Verifica se a URL mudou para a tela de criação
  await expect(page).toHaveURL(/\/news\/create$/);

  // Verifica se o título da tela de criação está visível
  await expect(page.getByRole('heading', { name: 'Criar noticia' })).toBeVisible();

  // Verifica se os campos principais do formulário estão visíveis
  await expect(page.getByLabel('Titulo')).toBeVisible();
  await expect(page.getByLabel('Resumo')).toBeVisible();
  await expect(page.getByLabel('Conteudo')).toBeVisible();
  await expect(page.getByLabel('Categoria')).toBeVisible();
});

test('usuário consegue criar, editar, buscar e excluir uma notícia', async ({ page }) => {
  // Gera um sufixo único para evitar conflito com notícias já existentes
  const sufixo = Date.now();

  // Título original da notícia criada pelo teste
  const titulo = `Noticia E2E ${sufixo}`;

  // Título que será usado depois da edição
  const tituloEditado = `${titulo} editada`;

  // Acessa a listagem de notícias
  await page.goto('/news');

  // Abre o formulário de criação
  await page.getByRole('link', { name: 'Nova noticia' }).click();

  // Preenche os campos do formulário
  await page.getByLabel('Titulo').fill(titulo);
  await page.getByLabel('Resumo').fill('Resumo criado pelo teste end-to-end.');
  await page.getByLabel('Conteudo').fill('Conteudo criado pelo Playwright para validar o CRUD.');
  await page.getByLabel('Categoria').fill('Testes');

  // Seleciona o status como rascunho
  await page.getByLabel('Status').selectOption('draft');

  // Envia o formulário
  await page.getByRole('button', { name: 'Criar noticia' }).click();

  // Verifica se voltou para a listagem
  await expect(page).toHaveURL(/\/news$/);

  // Verifica a mensagem de sucesso
  await expect(page.getByText('Noticia criada com sucesso.')).toBeVisible();

  // Verifica se a notícia criada aparece na tela
  await expect(page.getByText(titulo)).toBeVisible();

  // Busca a notícia pelo título criado
  await page.getByPlaceholder('Buscar por titulo').fill(titulo);
  await page.getByRole('button', { name: 'Filtrar' }).click();

  // Confirma que a notícia buscada aparece
  await expect(page.getByText(titulo)).toBeVisible();

  // Localiza a linha da tabela que contém a notícia criada
  // Isso evita usar seletores frágeis como nth-child
  const linhaCriada = page.getByRole('row').filter({ hasText: titulo });

  // Clica no botão/link de editar dentro da linha correta
  await linhaCriada.getByRole('link', { name: 'Editar' }).click();

  // Edita o título e o resumo
  await page.getByLabel('Titulo').fill(tituloEditado);
  await page.getByLabel('Resumo').fill('Resumo editado pelo teste end-to-end.');

  // Salva as alterações
  await page.getByRole('button', { name: 'Salvar alteracoes' }).click();

  // Verifica se voltou para a listagem
  await expect(page).toHaveURL(/\/news$/);

  // Verifica a mensagem de atualização
  await expect(page.getByText('Noticia atualizada com sucesso.')).toBeVisible();

  // Busca pelo novo título editado
  await page.getByPlaceholder('Buscar por titulo').fill(tituloEditado);
  await page.getByRole('button', { name: 'Filtrar' }).click();

  // Confirma que a notícia editada aparece
  await expect(page.getByText(tituloEditado)).toBeVisible();

  // Prepara o Playwright para aceitar o alerta de confirmação do navegador
  page.once('dialog', async (dialog) => {
    // Confirma se a mensagem do alerta é a esperada
    expect(dialog.message()).toContain('Deseja excluir esta noticia?');

    // Aceita o alerta, confirmando a exclusão
    await dialog.accept();
  });

  // Localiza a linha com a notícia editada
  const linhaEditada = page.getByRole('row').filter({ hasText: tituloEditado });

  // Clica no botão de excluir dentro da linha correta
  await linhaEditada.getByRole('button', { name: 'Excluir' }).click();

  // Verifica a mensagem de exclusão
  await expect(page.getByText('Noticia excluida com sucesso.')).toBeVisible();

  // Confirma que a notícia excluída não aparece mais na tela
  await expect(page.getByText(tituloEditado)).not.toBeVisible();
});
