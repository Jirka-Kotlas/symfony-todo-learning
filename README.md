# Symfony Todo Learning

Jednoduchá Todo aplikace vytvořená v Symfony pro výukové účely.

## Požadavky

- Docker
- Docker Compose

## Instalace a spuštění

### 1. Klonování projektu

```bash
git clone <repository-url>
cd symfony-todo-learning
```

### 2. Spuštění Docker kontejnerů

```bash
docker-compose up -d
```

Tento příkaz spustí:
- **PostgreSQL databázi** na portu `5433`
- **Symfony aplikaci** na portu `8000`

### 3. Vytvoření databázové struktury

Po spuštění kontejnerů je nutné vytvořit databázové tabulky:

```bash
docker-compose exec app php bin/console doctrine:migrations:migrate
```

Potvrďte migraci zadáním `yes`.

### 4. Přístup k aplikaci

Aplikace bude dostupná na: **http://localhost:8000**

## Užitečné příkazy

### Zastavení aplikace

```bash
docker-compose down
```

### Restart aplikace

```bash
docker-compose restart
```

### Zobrazení logů

```bash
docker-compose logs -f app
```

### Přístup do kontejneru aplikace

```bash
docker-compose exec app bash
```

### Vytvoření nové migrace

```bash
docker-compose exec app php bin/console make:migration
```

### Spuštění migrace

```bash
docker-compose exec app php bin/console doctrine:migrations:migrate
```

### Cache clear

```bash
docker-compose exec app php bin/console cache:clear
```

## Databázové připojení

- **Host:** localhost
- **Port:** 5433
- **Databáze:** symfony_todo
- **Uživatel:** app
- **Heslo:** ChangeMe

## Struktura projektu

- `src/` - zdrojové kódy aplikace
  - `Controller/` - kontrolery
  - `Entity/` - entity (databázové modely)
  - `Form/` - formuláře
  - `Repository/` - repozitáře pro práci s databází
- `templates/` - Twig šablony
- `config/` - konfigurační soubory
- `migrations/` - databázové migrace
- `public/` - veřejně přístupné soubory
- `docker/` - Docker konfigurace

## Technologie

- PHP 8.2
- Symfony 7.3
- PostgreSQL 15
- Apache
- Docker & Docker Compose
