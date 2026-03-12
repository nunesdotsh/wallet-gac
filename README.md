# 💰 Wallet GAC — Carteira Financeira Digital

[![PHP 8.4](https://img.shields.io/badge/PHP-8.4-777BB4?logo=php&logoColor=white)](https://www.php.net/)
[![Laravel 12](https://img.shields.io/badge/Laravel-12-FF2D20?logo=laravel&logoColor=white)](https://laravel.com/)
[![Pest PHP](https://img.shields.io/badge/Tests-Pest_PHP-F28D1A?logo=pestphp&logoColor=white)](https://pestphp.com/)
[![PostgreSQL](https://img.shields.io/badge/PostgreSQL-17-4169E1?logo=postgresql&logoColor=white)](https://www.postgresql.org/)
[![Docker](https://img.shields.io/badge/Docker-Sail-2496ED?logo=docker&logoColor=white)](https://laravel.com/docs/sail)

> Aplicação de carteira financeira digital desenvolvida como teste técnico para o setor fintech. Permite cadastro de usuários, depósitos, transferências entre usuários, consulta de saldo/extrato e reversão de operações.

**Projeto construído com:** Clean Architecture • DDD • TDD • SOLID

---

## 📋 Índice

- [Visão Geral](#-visão-geral)
- [Arquitetura](#-arquitetura)
- [Stack Tecnológica](#-stack-tecnológica)
- [Pré-requisitos](#-pré-requisitos)
- [Instalação e Configuração](#-instalação-e-configuração)
- [Comandos CLI (Uso)](#-comandos-cli-uso)
- [Interface Web](#-interface-web)
- [Testes](#-testes)
- [Estrutura do Projeto](#-estrutura-do-projeto)
- [Decisões de Design](#-decisões-de-design)
- [Padrões Utilizados](#-padrões-utilizados)
- [Observabilidade](#-observabilidade)
- [O que foi Avaliado](#-o-que-foi-avaliado)
- [Melhorias Futuras](#-melhorias-futuras)

---

## 🎯 Visão Geral

Sistema de carteira financeira digital onde usuários podem:

- **Cadastrar-se** e autenticar-se no sistema
- **Depositar** dinheiro na carteira
- **Transferir** saldo entre usuários (com validação de saldo)
- **Consultar** saldo e extrato de transações
- **Reverter** qualquer operação (depósito ou transferência)
- Saldo negativo: depósitos **acrescentam ao valor**, quitando débitos automaticamente
- **Interface web** completa com Vue 3 + Inertia.js (dashboard, formulários, listagem)

### Requisitos do Desafio

| Requisito | Status |
|-----------|--------|
| Cadastro de usuários | ✅ |
| Autenticação | ✅ |
| Depósito de dinheiro | ✅ |
| Transferência entre usuários | ✅ |
| Validação de saldo antes da transferência | ✅ |
| Saldo negativo: depósito quita débito | ✅ |
| Reversão de transferência/depósito | ✅ |
| **Diferencial:** Docker | ✅ |
| **Diferencial:** Testes unitários | ✅ |
| **Diferencial:** Testes de integração | ✅ |
| **Diferencial:** Documentação | ✅ |
| **Diferencial:** Observabilidade | ✅ |
| **Diferencial:** Interface web | ✅ |

---

## 🏗 Arquitetura

O projeto segue **Clean Architecture** combinada com **Domain-Driven Design (DDD)**, garantindo separação clara de responsabilidades e independência do framework na camada de domínio.

```
┌─────────────────────────────────────────────────────────┐
│                  PRESENTATION LAYER                     │
│     CLI (Artisan Commands) • HTTP (Controllers + Vue)   │
├─────────────────────────────────────────────────────────┤
│                  APPLICATION LAYER                      │
│           Use Cases • DTOs • UnitOfWork                 │
├─────────────────────────────────────────────────────────┤
│                    DOMAIN LAYER                         │
│     Entities • Value Objects • Enums • Exceptions       │
│            Repository Interfaces (contratos)            │
├─────────────────────────────────────────────────────────┤
│                INFRASTRUCTURE LAYER                     │
│    Eloquent Repositories • Models • Migrations • DI     │
└─────────────────────────────────────────────────────────┘
```

### Fluxo de Dependências

```
Presentation ──→ Application ──→ Domain ←── Infrastructure
                                   ▲              │
                                   └──────────────┘
                              (implementa interfaces)
```

> **Regra de Dependência:** As camadas internas nunca dependem das externas. A camada de Domínio é PHP puro — zero dependências do Laravel. A Infrastructure implementa as interfaces definidas pelo Domain (Inversão de Dependência).

### Fluxo de uma Transferência

```
TransferCommand (CLI)  ──┐
                         ├──→ TransferUseCase (Application)
TransferController (HTTP)──┘       │
    │                              ├── UnitOfWork.execute() ──→ DB::transaction
    │                              │       │
    │                              │       ├── WalletRepository.findByUserIdForUpdate()
    │                              │       ├── Wallet.debit(amount)
    │                              │       ├── Wallet.credit(amount)
    │                              │       ├── Transaction.createTransferOut()
    │                              │       ├── Transaction.createTransferIn()
    │                              │       ├── WalletRepository.save() × 2
    │                              │       └── TransactionRepository.save() × 2
    │                              ▼
    │                        TransferOutputDTO
    ▼
CLI: tabela no terminal / HTTP: redirect com flash message
```

---

## 🛠 Stack Tecnológica

| Tecnologia | Versão | Propósito |
|------------|--------|-----------|
| **PHP** | 8.4 | Runtime — typed properties, enums, readonly, fibers |
| **Laravel** | 12.x | Framework backend, Sail, Artisan CLI |
| **PostgreSQL** | 17+ | Banco de dados principal — ACID, `DECIMAL(15,2)` |
| **SQLite** | - | Banco alternativo — demonstra independência de DB |
| **Pest PHP** | 4.x | Framework de testes — expressivo, integração Laravel |
| **Laravel Sail** | 1.x | Docker simplificado para desenvolvimento |
| **Laravel Telescope** | 5.x | Observabilidade em desenvolvimento |
| **Laravel Fortify** | 1.x | Autenticação (login, registro, 2FA) |
| **Laravel Pint** | 1.x | Code style — PSR-12 |
| **Inertia.js** | 2.x | Bridge Laravel ↔ Vue.js (SPA server-driven) |
| **Vue.js** | 3.5 | Framework frontend (Composition API + TypeScript) |
| **Tailwind CSS** | 4.x | Estilização utilitária |
| **vue-currency-input** | - | Input monetário com máscara BRL |

---

## 📦 Pré-requisitos

### Com Docker (recomendado)

- [Docker Desktop](https://www.docker.com/products/docker-desktop/) instalado e rodando
- [Composer](https://getcomposer.org/) (para instalar dependências iniciais)

### Sem Docker (alternativa com SQLite)

- PHP 8.4+ com extensões: `pdo_sqlite`, `mbstring`, `openssl`, `tokenizer`, `xml`
- [Composer](https://getcomposer.org/)
- SQLite 3

---

## 🚀 Instalação e Configuração

### Opção 1: Docker + PostgreSQL (recomendado)

```bash
# 1. Clonar o repositório
git clone https://github.com/nunesdotsh/wallet-gac.git
cd wallet-gac

# 2. Instalar dependências PHP (necessário antes de subir o Sail)
composer install

# 3. Configurar ambiente
cp .env.example .env
php artisan key:generate

# 4. Subir os containers (PHP + PostgreSQL)
./vendor/bin/sail up -d

# 5. Executar migrations
./vendor/bin/sail artisan migrate
```

> **Aplicação disponível via CLI.** Os comandos Artisan já funcionam a partir daqui — veja a seção [Comandos CLI](#-comandos-cli-uso).

> **💡 Alias útil:** Adicione `alias sail='./vendor/bin/sail'` ao seu `.bashrc`/`.zshrc` para digitar apenas `sail` ao invés de `./vendor/bin/sail`.

#### Opcional: Interface Web

```bash
# 6. Instalar dependências JS
./vendor/bin/sail npm install

# 7. Compilar os assets do frontend
./vendor/bin/sail npm run build
```

> **Acesse a aplicação em:** [http://localhost](http://localhost)
>
> Para desenvolvimento do frontend com hot-reload, use `./vendor/bin/sail npm run dev` em um terminal separado.

### Opção 2: SQLite (sem Docker)

```bash
# 1. Clonar e instalar
git clone https://github.com/nunesdotsh/wallet-gac.git
cd wallet-gac
composer install

# 2. Configurar ambiente
cp .env.sqlite.example .env
php artisan key:generate

# 3. Criar o arquivo do banco
touch database/database.sqlite

# 4. Executar migrations e iniciar o servidor
php artisan migrate
php artisan serve
```

> **Aplicação disponível via CLI.** Com o servidor rodando, a interface web estará em [http://localhost:8000](http://localhost:8000).

#### Opcional: Interface Web

```bash
# Em outro terminal, compilar os assets do frontend
npm install
npm run build
```

> **💡 Por que suportar SQLite?** Demonstra a independência de banco de dados da Clean Architecture. Trocar de PostgreSQL para SQLite requer apenas alterar variáveis de ambiente — zero mudanças no código.

### Verificar instalação

```bash
# Com Sail
./vendor/bin/sail artisan about

# Sem Sail
php artisan about
```

---

## 💻 Comandos CLI (Uso)

A interação principal com o sistema é via **Artisan CLI**. Todos os comandos aceitam opções interativas ou flags diretas.

> **Usando com Docker/Sail:** substitua `php artisan` por `./vendor/bin/sail artisan` em todos os comandos abaixo.
>
> ```bash
> # Sem Sail (SQLite local)
> php artisan wallet:deposit --email="joao@email.com" --amount="500.00"
>
> # Com Sail (Docker + PostgreSQL)
> ./vendor/bin/sail artisan wallet:deposit --email="joao@email.com" --amount="500.00"
> ```

### Criar Usuário

```bash
php artisan wallet:create-user
# Modo interativo: solicita nome, e-mail e senha

# Exemplo com opções (se disponíveis)
php artisan wallet:create-user
# → Nome: João Silva
# → Email: joao@email.com
# → Senha: ********
# ✅ Usuário criado com sucesso!
```

### Depositar Dinheiro

```bash
php artisan wallet:deposit --email="joao@email.com" --amount="500.00"

# Saída:
# ✅ Deposit completed successfully!
# +----------------+--------------------------------------+
# | Field          | Value                                |
# +----------------+--------------------------------------+
# | Transaction ID | 550e8400-e29b-41d4-a716-446655440000 |
# | Amount         | R$ 500.00                            |
# | Balance Before | R$ 0.00                              |
# | Balance After  | R$ 500.00                            |
# | Status         | COMPLETED                            |
# +----------------+--------------------------------------+
```

### Transferir entre Usuários

```bash
php artisan wallet:transfer \
  --from="joao@email.com" \
  --to="maria@email.com" \
  --amount="150.00"

# Saída:
# ✅ Transfer completed successfully!
# (exibe detalhes da transação com saldos antes/depois)
```

### Consultar Saldo

```bash
php artisan wallet:balance --email="joao@email.com"

# Saída:
# Saldo atual: R$ 350.00
```

### Reverter Operação

```bash
php artisan wallet:reverse --transaction="550e8400-e29b-41d4-a716-446655440000"

# ✅ Transação revertida com sucesso!
# (crédito/débito compensatório é criado automaticamente)
```

### Extrato de Transações

```bash
php artisan wallet:history --email="joao@email.com"

# Exibe tabela com histórico completo:
# ID | Tipo | Valor | Saldo Antes | Saldo Depois | Status | Data
```

---

## 🌐 Interface Web

A aplicação possui uma **interface web completa** construída com Vue 3 + Inertia.js, que reutiliza os mesmos Use Cases da CLI.

### Rotas Web

| Método | URI | Descrição |
|--------|-----|-----------|
| `GET` | `/dashboard` | Dashboard com saldo e últimas transações |
| `GET` | `/deposit` | Formulário de depósito |
| `POST` | `/deposit` | Processar depósito |
| `GET` | `/transfer` | Formulário de transferência |
| `POST` | `/transfer` | Processar transferência |
| `GET` | `/transactions` | Listagem paginada de transações |
| `GET` | `/transactions/{id}` | Detalhes de uma transação |
| `POST` | `/transactions/{id}/reverse` | Reverter uma transação |

Todas as rotas requerem autenticação (`auth` + `verified`).

### Iniciar o frontend

```bash
# Com Sail
./vendor/bin/sail npm run dev

# Sem Sail
npm run dev
```

---

## 🧪 Testes

O projeto utiliza **Pest PHP** com **TDD** e possui **184 testes** com **567 assertions**.

### Executar todos os testes

```bash
# Com Sail
./vendor/bin/sail test

# Sem Sail
php artisan test
```

### Executar por suíte

```bash
# Testes unitários (domínio + use cases)
php artisan test --testsuite=Unit

# Testes de integração (CLI + banco SQLite em memória)
php artisan test --testsuite=Integration

# Testes de feature (auth, perfil)
php artisan test --testsuite=Feature
```

### Executar testes específicos

```bash
# Testes de um arquivo
php artisan test tests/Unit/Domain/Wallet/MoneyValueObjectTest.php

# Filtrar por nome
php artisan test --filter="deposit"
```

### Cobertura dos testes

| Suíte | Escopo | Quantidade |
|-------|--------|------------|
| **Unit** | Entidades, Value Objects, Enums, Use Cases (com mocks) | 16 arquivos |
| **Integration** | Comandos CLI com SQLite in-memory | 1 arquivo |
| **Feature** | Auth, perfil, dashboard, wallet (depósito, transferência, reversão, transações) | 18 arquivos |

### Estratégia de testes

- **Unitários:** Testam regras de negócio isoladamente. Use Cases mockam repositórios e UnitOfWork.
- **Integração:** Testam fluxo completo via CLI com banco SQLite em memória — validam que todas as camadas funcionam juntas.
- **Feature:** Testes do starter kit Laravel (autenticação, perfil) + testes de feature da wallet (dashboard, depósito, transferência, reversão, listagem de transações via HTTP/Inertia).

> Os testes usam **SQLite in-memory** (`DB_CONNECTION=sqlite`, `DB_DATABASE=:memory:`) configurado no `phpunit.xml`, garantindo execução rápida e isolada.

---

## 📁 Estrutura do Projeto

```
app/
├── Domain/                              # 🟢 DOMÍNIO — PHP puro, zero dependências Laravel
│   ├── User/
│   │   ├── Entities/User.php            # Entidade de usuário
│   │   ├── ValueObjects/
│   │   │   ├── Email.php                # VO com validação de formato
│   │   │   └── UserId.php               # UUID tipado
│   │   ├── Repositories/
│   │   │   └── UserRepositoryInterface.php
│   │   └── Exceptions/
│   │       ├── DuplicateEmailException.php
│   │       ├── InvalidEmailException.php
│   │       └── UserNotFoundException.php
│   │
│   ├── Wallet/
│   │   ├── Entities/Wallet.php          # Entidade com credit/debit
│   │   ├── ValueObjects/
│   │   │   ├── Money.php                # VO monetário (centavos internamente)
│   │   │   └── WalletId.php             # UUID tipado
│   │   ├── Repositories/
│   │   │   └── WalletRepositoryInterface.php
│   │   └── Exceptions/
│   │       ├── InsufficientBalanceException.php
│   │       ├── NegativeAmountException.php
│   │       └── WalletNotFoundException.php
│   │
│   └── Transaction/
│       ├── Entities/Transaction.php     # Entidade com factory methods
│       ├── ValueObjects/
│       │   ├── TransactionId.php        # UUID tipado
│       │   ├── TransactionType.php      # Enum: DEPOSIT, TRANSFER_IN, TRANSFER_OUT, etc.
│       │   └── TransactionStatus.php    # Enum: COMPLETED, REVERSED
│       ├── Repositories/
│       │   └── TransactionRepositoryInterface.php
│       └── Exceptions/
│           ├── InvalidTransactionException.php
│           ├── TransactionAlreadyReversedException.php
│           └── TransactionNotFoundException.php
│
├── Application/                         # 🔵 APLICAÇÃO — Orquestração de use cases
│   ├── Contracts/
│   │   └── UnitOfWorkInterface.php      # Contrato para transações atômicas
│   └── UseCases/
│       ├── CreateUser/
│       │   ├── CreateUserUseCase.php
│       │   ├── CreateUserInputDTO.php
│       │   └── CreateUserOutputDTO.php
│       ├── Deposit/
│       │   ├── DepositUseCase.php
│       │   ├── DepositInputDTO.php
│       │   └── DepositOutputDTO.php
│       ├── Transfer/
│       │   ├── TransferUseCase.php
│       │   ├── TransferInputDTO.php
│       │   └── TransferOutputDTO.php
│       ├── ReverseTransaction/
│       │   ├── ReverseTransactionUseCase.php
│       │   ├── ReverseTransactionInputDTO.php
│       │   └── ReverseTransactionOutputDTO.php
│       ├── GetBalance/
│       │   ├── GetBalanceUseCase.php
│       │   └── GetBalanceOutputDTO.php
│       └── GetTransactionHistory/
│           ├── GetTransactionHistoryUseCase.php
│           └── TransactionHistoryItemDTO.php
│
├── Infrastructure/                      # 🟠 INFRAESTRUTURA — Implementações concretas
│   ├── Persistence/
│   │   ├── Eloquent/
│   │   │   ├── Models/                  # Eloquent Models (TransactionModel, WalletModel)
│   │   │   └── Repositories/           # Implementações dos contratos do domínio
│   │   │       ├── EloquentUserRepository.php
│   │   │       ├── EloquentWalletRepository.php
│   │   │       └── EloquentTransactionRepository.php
│   │   └── UnitOfWork.php              # DB::transaction wrapper
│   └── Providers/
│       └── DomainServiceProvider.php    # Bindings de DI (interface → implementação)
│
├── Presentation/                        # 🟣 APRESENTAÇÃO — Interface com o usuário
│   ├── Console/Commands/               # Comandos Artisan (CLI)
│   │   ├── CreateUserCommand.php        # wallet:create-user
│   │   ├── DepositCommand.php           # wallet:deposit
│   │   ├── TransferCommand.php          # wallet:transfer
│   │   ├── BalanceCommand.php           # wallet:balance
│   │   ├── ReverseTransactionCommand.php # wallet:reverse
│   │   └── TransactionHistoryCommand.php # wallet:history
│   └── Http/                            # Controllers HTTP + Vue
│       ├── Controllers/Wallet/
│       │   ├── DashboardController.php   # Dashboard com saldo e transações
│       │   ├── DepositController.php     # Formulário e processamento de depósito
│       │   ├── TransferController.php    # Formulário e processamento de transferência
│       │   ├── TransactionController.php # Listagem e detalhes de transações
│       │   └── ReversalController.php    # Reversão de transações
│       ├── Requests/
│       │   ├── DepositRequest.php        # Validação de depósito
│       │   └── TransferRequest.php       # Validação de transferência
│       └── Traits/
│           └── FormatsMoney.php          # Formatação monetária (R$ X.XXX,XX)
│
├── Shared/                              # 🔶 COMPARTILHADO — Base classes
│   ├── Exceptions/
│   │   ├── DomainException.php          # Base para exceções de domínio
│   │   └── ApplicationException.php     # Base para exceções de aplicação
│   └── ValueObjects/
│       └── Uuid.php                     # Base class para UUIDs tipados
│
tests/
├── Unit/
│   ├── Domain/
│   │   ├── User/                        # EmailValueObjectTest, UserEntityTest
│   │   ├── Wallet/                      # MoneyValueObjectTest, WalletEntityTest
│   │   └── Transaction/                 # TransactionEntityTest, TransactionTypeTest
│   └── Application/                     # Testes dos Use Cases (com mocks)
├── Integration/
│   └── Console/
│       └── WalletCommandsTest.php       # Teste end-to-end via CLI
└── Feature/
    └── Wallet/                          # Testes HTTP/Inertia
        ├── DashboardTest.php            # Dashboard, saldo, transações recentes
        ├── DepositTest.php              # Formulário e validações de depósito
        ├── TransferTest.php             # Formulário e validações de transferência
        ├── ReversalTest.php             # Reversão de transações
        └── TransactionListTest.php      # Listagem, detalhes e paginação

resources/js/
├── components/                          # Componentes Vue reutilizáveis
│   ├── WalletBalance.vue                # Exibição de saldo
│   ├── TransactionList.vue              # Tabela de transações
│   ├── TransactionBadge.vue             # Badge de tipo/status
│   └── MoneyInput.vue                   # Input monetário (BRL)
├── pages/
│   ├── Dashboard.vue                    # Dashboard principal
│   └── wallet/
│       ├── Deposit.vue                  # Formulário de depósito
│       ├── Transfer.vue                 # Formulário de transferência
│       ├── Transactions.vue             # Listagem paginada
│       └── TransactionDetail.vue        # Detalhes + reversão
└── types/
    └── wallet.ts                        # Tipos TypeScript (Wallet, Transaction)
```

---

## 🎨 Decisões de Design

### Dinheiro como inteiro (centavos)

```php
// ❌ Float — problemas de precisão
0.1 + 0.2 = 0.30000000000000004

// ✅ Integer (centavos) — precisão garantida
10 + 20 = 30  // R$ 0.30
```

O Value Object `Money` armazena valores em **centavos** (`int`) internamente e converte para `DECIMAL(15,2)` na persistência. Isso elimina erros de arredondamento em operações financeiras.

### UUID como chave primária

Todos os IDs são **UUIDs v4** gerados pela aplicação (não pelo banco). Benefícios:
- IDs gerados antes da persistência (domínio controla)
- Sem colisão em ambientes distribuídos
- Não expõe volume de dados (vs. auto-increment)

### Pessimistic Locking (SELECT FOR UPDATE)

```php
// EloquentWalletRepository
public function findByUserIdForUpdate(UserId $userId): ?Wallet
{
    $model = WalletModel::where('user_id', $userId->value())
        ->lockForUpdate()  // SELECT FOR UPDATE
        ->first();
    // ...
}
```

Operações financeiras usam **lock pessimista** para evitar race conditions. Quando uma transferência é processada, a wallet é travada no banco até o commit da transação.

### Operações atômicas via UnitOfWork

```php
// DepositUseCase
return $this->unitOfWork->execute(function () use ($userId, $amount) {
    $wallet = $this->walletRepository->findByUserIdForUpdate($userId);
    $wallet->credit($amount);
    $this->walletRepository->save($wallet);
    $this->transactionRepository->save($transaction);
    return new DepositOutputDTO(/* ... */);
});
```

O `UnitOfWork` encapsula `DB::transaction()`, garantindo que todas as operações de uma ação (atualizar saldo + criar transação) são **atômicas** — ou todas acontecem, ou nenhuma.

### Transferência = 2 transações vinculadas

Uma transferência cria **duas transações** no banco:
- `TRANSFER_OUT` na wallet de origem (débito)
- `TRANSFER_IN` na wallet de destino (crédito)

Ambas compartilham uma referência (`related_transaction_id`), facilitando rastreabilidade e reversão.

### Reversão de operações

A reversão **não deleta** a transação original. Em vez disso:
1. Marca a transação original como `REVERSED`
2. Cria uma nova transação compensatória (crédito onde houve débito e vice-versa)
3. Para transferências, reverte ambos os lados automaticamente

Isso mantém **auditabilidade total** — o histórico nunca é alterado.

### Domínio independente do framework

A camada de Domínio é **PHP puro** — sem `use Illuminate\...` em nenhum arquivo. Isso significa:
- Testável sem framework (testes unitários puros)
- Portável para qualquer framework PHP
- Foco exclusivo em regras de negócio

---

## 🧩 Padrões Utilizados

| Padrão | Onde | Propósito |
|--------|------|-----------|
| **Clean Architecture** | Estrutura geral | Separação em 4 camadas com regra de dependência |
| **DDD (Domain-Driven Design)** | Camada Domain | Modelagem baseada no domínio de negócio |
| **Repository** | Domain (interface) + Infrastructure (impl.) | Abstração de acesso a dados |
| **Value Object** | `Money`, `Email`, `UserId`, `WalletId`, `TransactionId` | Objetos imutáveis que representam valores do domínio |
| **DTO** | Application (Input/Output DTOs) | Transferência de dados entre camadas |
| **Use Case** | Application | Um caso de uso = uma ação do sistema |
| **Unit of Work** | Application (interface) + Infrastructure (impl.) | Transações atômicas de banco de dados |
| **Dependency Injection** | `DomainServiceProvider` | Inversão de controle — interfaces → implementações |
| **Factory Method** | Entidades (`Transaction::createDeposit()`, `Wallet::create()`) | Criação expressiva de objetos de domínio |
| **Enum** | `TransactionType`, `TransactionStatus` | Valores tipados e finitos |

### Inversão de Dependência na prática

```php
// DomainServiceProvider.php — único ponto de binding
$this->app->bind(UserRepositoryInterface::class, EloquentUserRepository::class);
$this->app->bind(WalletRepositoryInterface::class, EloquentWalletRepository::class);
$this->app->bind(TransactionRepositoryInterface::class, EloquentTransactionRepository::class);
$this->app->bind(UnitOfWorkInterface::class, UnitOfWork::class);
```

> Para trocar de Eloquent/PostgreSQL para Doctrine/MySQL, basta alterar os bindings neste arquivo. Nenhuma outra camada precisa mudar.

---

## 🔭 Observabilidade

### Laravel Telescope (Desenvolvimento)

O **Telescope** está integrado como ferramenta de debug e observabilidade em ambiente de desenvolvimento.

```bash
# Acessar o dashboard
http://localhost/telescope
```

**Watchers ativos:**
- 🔍 **Queries** — todas as SQL queries executadas
- 📨 **Requests** — requisições HTTP com payload e resposta
- ⚡ **Commands** — execuções de comandos Artisan
- 🚨 **Exceptions** — exceções com stack trace completo
- 📋 **Logs** — entradas de log estruturadas
- 🔄 **Models** — criações, atualizações e exclusões de modelos
- 📬 **Events** — eventos disparados pela aplicação

> O Telescope é **desabilitado automaticamente** nos testes (`TELESCOPE_ENABLED=false` no `phpunit.xml`).

### Laravel Pulse (Produção — documentado)

Para ambientes de produção, o **Laravel Pulse** é a ferramenta recomendada para monitoramento contínuo:
- Métricas de performance em tempo real
- Monitoramento de queries lentas
- Uso de filas e jobs
- Dashboard de saúde da aplicação

---

## ✅ O que foi Avaliado

Mapeamento dos critérios de avaliação do desafio e como foram atendidos:

### Requisitos Obrigatórios

| Critério | Implementação |
|----------|---------------|
| **Segurança** | Laravel Fortify (auth), validação de inputs em Value Objects, exceções tipadas, CSRF |
| **Código limpo** | PSR-12 (Pint), SOLID, nomes expressivos, sem comentários desnecessários |
| **Arquitetura** | Clean Architecture + DDD com 4 camadas separadas e regra de dependência |
| **Tratamento de erros** | Hierarquia de exceções de domínio tipadas (`DomainException`, `ApplicationException`, 8+ específicas) |
| **Argumentação** | Cada decisão está documentada neste README |
| **Design patterns** | Repository, Value Object, DTO, Use Case, Unit of Work, Factory Method, DI |
| **Modelagem de dados** | UUIDs, `DECIMAL(15,2)`, normalização, integridade referencial, locking pessimista |

### Diferenciais

| Diferencial | Implementação |
|-------------|---------------|
| **Docker** | ✅ Laravel Sail com PHP + PostgreSQL |
| **Testes unitários** | ✅ Domínio + Use Cases (Pest PHP com mocks) |
| **Testes de integração** | ✅ CLI commands com SQLite in-memory |
| **Documentação** | ✅ README completo |
| **Observabilidade** | ✅ Telescope (dev) + Pulse (prod, documentado) |
| **Interface web** | ✅ Vue 3 + Inertia.js (SPA server-driven) |

---

## 🔮 Melhorias Futuras

- [x] **Interface Web** — Vue.js 3 + Inertia.js (dashboard, depósito, transferência, transações, reversão)
- [ ] **API REST** — Controllers HTTP com recursos JSON
- [ ] **Domain Events** — Publicar eventos de domínio para notificações e auditoria
- [ ] **Notificações** — E-mail/push para transferências recebidas e reversões
- [ ] **Limites e regras** — Limite diário de transferência, valores máximos/mínimos
- [ ] **Relatórios** — Exportação de extratos em PDF/CSV
- [ ] **Cache** — Redis para consultas frequentes de saldo
- [ ] **CI/CD** — GitHub Actions com pipeline de testes automatizados
- [ ] **Laravel Pulse** — Configuração completa para monitoramento em produção
- [ ] **Autenticação 2FA** — Fortify já suporta, falta integrar no fluxo CLI/web

---

## 📄 Licença

Projeto desenvolvido como teste técnico — uso educacional e demonstrativo.

---

<p align="center">
  Desenvolvido com ☕ e Clean Architecture
</p>
