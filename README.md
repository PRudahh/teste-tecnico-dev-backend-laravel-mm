# Agency System — Backend Laravel

Sistema de gestão de contratos recorrentes, ordens de serviço e cobranças mensais para agências digitais.

## O que você vai precisar

- **PHP 8.2 ou superior**
- **Composer** (gerenciador de pacotes do PHP)
- **MySQL 8.0 ou superior**
- **Git**

## Instalando o projeto

Com PHP, Composer e MySQL prontos, agora é só seguir os passos abaixo.

### Passo 1 — Clone o repositório

```bash
git clone https://github.com/PRudahh/teste-tecnico-dev-backend-laravel-mm
cd agency-system-tecnical-test
```

### Passo 2 — Instalar as dependências

### Passo 3 — Crie e configure o arquivo de ambiente

### Passo 4 — Crie o banco de dados

### Passo 5 — Execute as migrations e popule o banco

### Passo 6 — Inicie o worker de filas

O sistema usa filas para processar jobs em segundo plano (como aplicação automática de crédito em cobranças). Abra um **segundo terminal**, entre na pasta do projeto e rode:

```bash
cd agency-system-tecnical-test
php artisan queue:work --tries=4
```

### Passo 7 — Inicie o servidor

## Testando a API

### Opção 1 — Postman (recomendado)

O projeto inclui uma Postman Collection completa com todos os endpoints já configurados.

1. Baixe e instale o [Postman](https://www.postman.com/downloads/) se ainda não tiver
2. Abra o Postman e clique em **Import**
3. Selecione o arquivo `agency-system-tecnical-test.postman_collection.json` na raiz do projeto
4. A collection vai aparecer com todos os endpoints organizados por categoria (Auth, Dashboard, Cobranças, Clientes, etc.)

**Para começar:** rode primeiro a requisição **Auth > Login**. O Postman já está configurado para capturar o token retornado e salvá-lo automaticamente na variável `{{token}}` — todas as outras requisições já usam esse token, então você não precisa copiar nada manualmente.

Use qualquer um destes usuários criados pelo seeder:

| E-mail | Senha | Permissões |
|---|---|---|
| admin@agency.com | password | Acesso total |
| financeiro@agency.com | password | Acesso total + aplicar crédito |
| operacional@agency.com | password | Acesso geral, sem aplicar crédito |

## Rodando os testes automatizados

```bash
php artisan test
```

Resultado esperado:

```
PASS  Tests\Unit\Domain\StatusCobrancaTest
✓ transicao valida nao lanca excecao
✓ transicao invalida lanca domain exception
✓ pago e status terminal sem transicoes
✓ cancelado e status terminal
...

PASS  Tests\Feature\CobrancaTest
✓ listar cobrancas requer autenticacao
✓ usuario autenticado pode listar cobrancas
✓ pode filtrar cobrancas por status
...

PASS  Tests\Feature\CreditoTest
✓ usuario sem role financeiro nao pode aplicar credito
✓ valor negativo retorna 422
...

Tests:  20 passed
```

---

## Endpoints disponíveis

| Método | Rota | Descrição | Restrição |
|---|---|---|---|
| GET | /api/v1/dashboard | Dashboard financeiro consolidado (cache 5min) (*REQUISITO) | — |
| GET | /api/v1/cobrancas | Listar cobranças com filtros e paginação (*REQUISITO) | Rate limit: 20/min |
| POST | /api/v1/clientes/{id}/aplicar-credito | Aplicar crédito manual (*REQUISITO) | Financeiro / Admin |
| POST | /api/v1/auth/login | Login | Pública |
| POST | /api/v1/auth/logout | Logout | — |
| GET | /api/v1/auth/me | Dados do usuário logado  | — |
| PATCH | /api/v1/cobrancas/{id}/status | Mudar status de uma cobrança | — |
| GET | /api/v1/clientes | Listar clientes | — |
| POST | /api/v1/clientes | Criar cliente | — |
| GET | /api/v1/clientes/{id} | Ver cliente com contratos | — |
| PUT | /api/v1/clientes/{id} | Atualizar cliente | — |
| DELETE | /api/v1/clientes/{id} | Remover cliente | — |
| GET | /api/v1/contratos | Listar contratos | — |
| POST | /api/v1/contratos | Criar contrato com itens | — |
| GET | /api/v1/contratos/{id} | Ver contrato completo | — |
| PUT | /api/v1/contratos/{id} | Atualizar contrato e itens | — |
| DELETE | /api/v1/contratos/{id} | Remover contrato | — |
| GET | /api/v1/ordens-servico | Listar ordens de serviço | — |
| POST | /api/v1/ordens-servico | Criar OS | — |
| GET | /api/v1/ordens-servico/{id} | Ver OS com histórico de status | — |
| PATCH | /api/v1/ordens-servico/{id}/status | Mudar status da OS (registra auditoria) | — |

> Todas as rotas acima (exceto login) exigem o header `Authorization: Bearer {token}`.
