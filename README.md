# Agency System — Backend Laravel

Sistema de gestão de contratos recorrentes, ordens de serviço e cobranças mensais para agências digitais.

> **Para quem está avaliando este projeto:** se você já tem PHP 8.2+, Composer e MySQL instalados, pode pular direto para a seção [4. Instalando o projeto](#4-instalando-o-projeto). Se preferir não instalar nada na sua máquina, a seção [Rodando com Docker](#opção-b-rodando-com-docker-recomendado-se-você-não-quiser-instalar-nada) resolve tudo com dois comandos.

---

## O que você vai precisar

- **PHP 8.2 ou superior**
- **Composer** (gerenciador de pacotes do PHP)
- **MySQL 8.0 ou superior**
- **Git**

Nas seções abaixo tem o passo a passo de instalação para cada sistema operacional. Se já tiver tudo instalado, pode pular para a etapa [4. Instalando o projeto](#4-instalando-o-projeto).

---

## 1. Instalando o PHP

### Linux (Ubuntu / Debian)

Abra o terminal e rode:

```bash
sudo apt update
sudo apt install -y software-properties-common
sudo add-apt-repository ppa:ondrej/php
sudo apt update
sudo apt install -y php8.2 php8.2-cli php8.2-mbstring php8.2-xml php8.2-curl \
     php8.2-mysql php8.2-zip php8.2-bcmath php8.2-tokenizer
```

Confirme a instalação:

```bash
php -v
# Deve mostrar algo como: PHP 8.2.x
```

### macOS

A forma mais simples é via [Homebrew](https://brew.sh). Se não tiver o Homebrew instalado ainda, rode primeiro:

```bash
/bin/bash -c "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh)"
```

Depois instale o PHP:

```bash
brew install php@8.2
echo 'export PATH="/opt/homebrew/opt/php@8.2/bin:$PATH"' >> ~/.zshrc
source ~/.zshrc
```

Confirme:

```bash
php -v
```

### Windows

1. Acesse [https://windows.php.net/download](https://windows.php.net/download)
2. Baixe a versão **PHP 8.2 — VS16 x64 Non Thread Safe** (arquivo `.zip`)
3. Extraia o conteúdo para `C:\php`
4. Adicione `C:\php` ao PATH do sistema:
   - Pesquise **"Variáveis de ambiente"** no menu Iniciar
   - Clique em **"Editar as variáveis de ambiente do sistema"**
   - Clique em **"Variáveis de Ambiente..."**
   - Em **"Variáveis do sistema"**, selecione `Path` e clique em **Editar**
   - Clique em **Novo** e adicione `C:\php`
   - Clique em OK em todas as janelas
5. Dentro de `C:\php`, copie o arquivo `php.ini-development` e renomeie a cópia para `php.ini`
6. Abra o `php.ini` num editor de texto (Notepad, VS Code, etc.) e remova o `;` do início das seguintes linhas para habilitar as extensões necessárias:

```ini
extension=mbstring
extension=openssl
extension=pdo_mysql
extension=zip
extension=curl
extension=fileinfo
extension=bcmath
```

7. Feche e abra um novo terminal (PowerShell ou CMD) e confirme:

```powershell
php -v
```

---

## 2. Instalando o Composer

O Composer é o gerenciador de dependências do PHP — pense nele como o npm do JavaScript ou o pip do Python.

### Linux e macOS

```bash
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```

Confirme:

```bash
composer -V
```

### Windows

1. Acesse [https://getcomposer.org/Composer-Setup.exe](https://getcomposer.org/Composer-Setup.exe)
2. Baixe e execute o instalador — ele detecta o PHP automaticamente
3. Siga o assistente até o fim
4. Feche e abra um novo terminal e confirme:

```powershell
composer -V
```

---

## 3. Instalando o MySQL

### Linux (Ubuntu / Debian)

```bash
sudo apt install -y mysql-server
sudo systemctl start mysql
sudo systemctl enable mysql

# Rode o assistente de segurança — ele vai pedir para você definir uma senha para o root
sudo mysql_secure_installation
```

Depois de definir a senha, confirme que funciona:

```bash
mysql -u root -p
# Digite a senha e você verá o prompt "mysql>"
exit
```

### macOS

```bash
brew install mysql
brew services start mysql

# Define a senha do root
mysql_secure_installation
```

### Windows

1. Acesse [https://dev.mysql.com/downloads/installer/](https://dev.mysql.com/downloads/installer/)
2. Baixe o **MySQL Installer Community** (gratuito — clique em "No thanks, just start my download")
3. Execute o instalador e escolha a opção **"Developer Default"**
4. Siga o assistente. Quando aparecer a tela de configuração de senha, defina uma senha para o usuário `root` — **anote essa senha**, você vai precisar dela logo
5. Conclua a instalação

Para confirmar, abra o **MySQL Command Line Client** no menu Iniciar, digite a senha do root e você verá o prompt `mysql>`.

> O instalador também instala o **MySQL Workbench**, que é uma interface gráfica opcional para gerenciar o banco. Útil se você não se sentir confortável com a linha de comando.

---

## 4. Instalando o projeto

Com PHP, Composer e MySQL prontos, agora é só seguir os passos abaixo.

### Passo 1 — Clone o repositório

```bash
git clone https://github.com/seu-usuario/agency-system.git
cd agency-system
```

### Passo 2 — Instale as dependências

```bash
composer install
```

Isso baixa todos os pacotes listados no `composer.json`. Pode demorar alguns minutos na primeira vez.

### Passo 3 — Crie e configure o arquivo de ambiente

```bash
# Linux / macOS
cp .env.example .env

# Windows (PowerShell)
Copy-Item .env.example .env
```

Gere a chave de criptografia da aplicação:

```bash
php artisan key:generate
```

Agora abra o arquivo `.env` em qualquer editor de texto e ajuste o bloco do banco de dados com os dados do seu MySQL:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=agency_system
DB_USERNAME=root
DB_PASSWORD=sua_senha_aqui
```

> Se você não definiu senha para o root durante a instalação do MySQL, deixe `DB_PASSWORD=` em branco.

### Passo 4 — Crie o banco de dados

Acesse o MySQL pelo terminal e crie o banco:

```bash
# Linux / macOS
mysql -u root -p -e "CREATE DATABASE agency_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Windows (PowerShell)
& "C:\Program Files\MySQL\MySQL Server 8.0\bin\mysql.exe" -u root -p -e "CREATE DATABASE agency_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

Vai pedir sua senha do MySQL. Depois disso o banco estará criado.

> **Prefere usar interface gráfica?** Se você tem MySQL Workbench, TablePlus, DBeaver ou HeidiSQL, pode criar o schema por lá também: crie um banco chamado `agency_system` com charset `utf8mb4` e collation `utf8mb4_unicode_ci`.

### Passo 5 — Execute as migrations e popule o banco

```bash
php artisan migrate --seed
```

Esse comando cria todas as tabelas e já insere dados de exemplo. Ao final você deve ver algo assim:

```
INFO  Running migrations.
  2024_01_01_000001_create_users_table .............. 10ms DONE
  2024_01_01_000002_create_clientes_table ........... 8ms DONE
  ...

INFO  Seeding database.
Usuários criados: admin@agency.com, financeiro@agency.com, operacional@agency.com (senha: password)
5 clientes criados com contratos, cobranças e ordens de serviço.
```

### Passo 6 — Inicie o worker de filas

O sistema usa filas para processar jobs em segundo plano (como aplicação automática de crédito em cobranças). Abra um **segundo terminal**, entre na pasta do projeto e rode:

```bash
cd agency-system
php artisan queue:work --tries=4
```

Deixe esse terminal aberto enquanto testa. Quando um job for processado, ele vai aparecer aqui.

### Passo 7 — Inicie o servidor

De volta ao terminal original:

```bash
php artisan serve
```

A aplicação estará disponível em **http://localhost:8000**. Pronto!

---

## Opção B — Rodando com Docker (recomendado se você não quiser instalar nada)

Se você tem o [Docker Desktop](https://www.docker.com/products/docker-desktop/) instalado, pode subir toda a aplicação — PHP, MySQL, Redis e o worker de filas — com poucos comandos, sem instalar mais nada na sua máquina.

### Passo 1 — Configure o ambiente

```bash
# Linux / macOS
cp .env.example .env

# Windows (PowerShell)
Copy-Item .env.example .env
```

Abra o `.env` e ajuste estas linhas para apontar para os containers:

```env
DB_HOST=db
DB_USERNAME=agency
DB_PASSWORD=secret
DB_DATABASE=agency_system
CACHE_DRIVER=redis
QUEUE_CONNECTION=database
REDIS_HOST=redis
```

### Passo 2 — Suba os containers

```bash
docker-compose up -d
```

Na primeira vez vai baixar as imagens necessárias — pode demorar alguns minutos dependendo da sua internet.

### Passo 3 — Instale as dependências e prepare o banco

```bash
docker-compose exec app composer install
docker-compose exec app php artisan key:generate
docker-compose exec app php artisan migrate --seed
```

Pronto. A aplicação estará em **http://localhost:8000** e o worker de filas sobe automaticamente.

Para parar tudo quando terminar:

```bash
docker-compose down
```

---

## Testando a API

### Opção 1 — Postman (recomendado)

O projeto inclui uma Postman Collection completa com todos os endpoints já configurados.

1. Baixe e instale o [Postman](https://www.postman.com/downloads/) se ainda não tiver
2. Abra o Postman e clique em **Import**
3. Selecione o arquivo `agency-system.postman_collection.json` na raiz do projeto
4. A collection vai aparecer com todos os endpoints organizados por categoria (Auth, Dashboard, Cobranças, Clientes, etc.)

**Para começar:** rode primeiro a requisição **Auth > Login**. O Postman já está configurado para capturar o token retornado e salvá-lo automaticamente na variável `{{token}}` — todas as outras requisições já usam esse token, então você não precisa copiar nada manualmente.

Use qualquer um destes usuários criados pelo seeder:

| E-mail | Senha | Permissões |
|---|---|---|
| admin@agency.com | password | Acesso total |
| financeiro@agency.com | password | Acesso total + aplicar crédito |
| operacional@agency.com | password | Acesso geral, sem aplicar crédito |

### Opção 2 — curl no terminal

Se preferir testar sem instalar nada adicional:

```bash
# 1. Faça login e copie o token retornado
curl -s -X POST http://localhost:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@agency.com","password":"password"}'

# 2. Cole o token aqui
TOKEN="seu_token_aqui"

# 3. Dashboard financeiro
curl -s http://localhost:8000/api/v1/dashboard \
  -H "Authorization: Bearer $TOKEN"

# 4. Listar cobranças com filtro de status
curl -s "http://localhost:8000/api/v1/cobrancas?status[]=pendente&status[]=aguardando_pagamento" \
  -H "Authorization: Bearer $TOKEN"

# 5. Aplicar crédito a um cliente (só funciona com o usuário financeiro ou admin)
curl -s -X POST http://localhost:8000/api/v1/clientes/1/aplicar-credito \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"valor": 500}'
```

---

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
| POST | /api/v1/auth/login | Login | Pública |
| POST | /api/v1/auth/logout | Logout | — |
| GET | /api/v1/auth/me | Dados do usuário logado | — |
| GET | /api/v1/dashboard | Dashboard financeiro consolidado (cache 5min) | — |
| GET | /api/v1/cobrancas | Listar cobranças com filtros e paginação | Rate limit: 20/min |
| PATCH | /api/v1/cobrancas/{id}/status | Mudar status de uma cobrança | — |
| GET | /api/v1/clientes | Listar clientes | — |
| POST | /api/v1/clientes | Criar cliente | — |
| GET | /api/v1/clientes/{id} | Ver cliente com contratos | — |
| PUT | /api/v1/clientes/{id} | Atualizar cliente | — |
| DELETE | /api/v1/clientes/{id} | Remover cliente | — |
| POST | /api/v1/clientes/{id}/aplicar-credito | Aplicar crédito manual | Financeiro / Admin |
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

---

## Stack utilizada

- PHP 8.2+
- Laravel 12
- MySQL 8.0+
- Laravel Sanctum (autenticação via token)
- Laravel Queues com driver database (jobs assíncronos)
- PHPUnit (testes)

---

## Problemas comuns

**`composer install` reclama que falta uma extensão do PHP**

Alguma extensão não está habilitada. Instale pelo terminal:
```bash
# Linux
sudo apt install php8.2-<nome>    # ex: php8.2-zip, php8.2-bcmath

# macOS — reinstalar com brew geralmente resolve
brew reinstall php@8.2
```
No Windows, edite o `C:\php\php.ini` e remova o `;` da linha da extensão em questão, depois reinicie o terminal.

**`SQLSTATE[HY000] [2002] Connection refused`**

O MySQL não está rodando. Inicie o serviço:
```bash
sudo systemctl start mysql    # Linux
brew services start mysql     # macOS
# Windows: abra "Serviços" (Win+R → services.msc) e inicie o serviço MySQL80
```

**`php artisan serve` diz que a porta 8000 está em uso**

Use outra porta:
```bash
php artisan serve --port=8001
```

**Jobs não aparecem sendo processados**

Confirme que o worker está rodando em um segundo terminal:
```bash
php artisan queue:work --tries=4
```

**Erro de permissão em `storage/` ou `bootstrap/cache/` no Linux/macOS**

```bash
chmod -R 775 storage bootstrap/cache
```