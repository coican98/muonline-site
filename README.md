# MU Online Web Platform

A full-stack web platform built with **Laravel 11, PHP 8.2, SQL Server and JavaScript** for managing a private MU Online server.

The project combines player-facing features with administrative tooling while integrating directly with an existing game database and legacy SQL Server schema.

## Highlights

- User registration, authentication, account settings, and account-related actions.
- Character and account information retrieved from the game database.
- Rankings and leaderboards.
- Dynamic server event list with real-time countdowns and multi-day event handling.
- News module with automatic unique slugs and friendly URLs.
- Administrative tools for server settings, downloads, news, players, and shop packages.
- Store and checkout flows with order tracking and Mercado Pago webhook integration.
- Role-based access restrictions for administrative features.
- Compatibility handling for legacy SQL Server tables and constraints.
- Responsive interface built with Blade, JavaScript, and custom CSS.

## Tech Stack

- **Backend:** PHP 8.2, Laravel 11
- **Database:** Microsoft SQL Server
- **Frontend:** Blade, JavaScript, HTML, CSS
- **Testing:** PHPUnit
- **Package management:** Composer, npm

## Main Modules

### Accounts

Registration, login, account settings, character information, and authenticated account actions.

### Rankings

Leaderboard views and filters backed by the existing MU Online database.

### Events

Dynamic event information, active/inactive status handling, and real-time countdowns.

### News

Public news pages with automatically generated unique slugs and administrative CRUD operations.

### Shop & Payments

Package management, checkout, order status tracking, cancellation flows, and payment webhook handling.

### Administration

Administrative controls for server configuration, downloads, news, players, registration bonuses, and shop packages.

## Running Locally

### Requirements

- PHP 8.2+
- Composer
- Microsoft ODBC Driver for SQL Server
- PHP `pdo_sqlsrv` extension
- Node.js / npm
- Access to a compatible MU Online SQL Server database

### Setup

```bash
git clone https://github.com/coican98/muonline-site.git
cd muonline-site

composer install
npm install

cp .env.example .env

php artisan key:generate
```

Configure the SQL Server connection in `.env`.

The project contains a site-specific migration for its own credentials table:

```bash
php artisan migrate --path=database/migrations/2024_09_05_130834_create_site_credentials_table.php
php artisan sync:site-credentials

npm run build
php artisan serve
```

> The MU Online game tables must already exist in the configured SQL Server database.

## Project Context

This project started as a website for a private MMORPG server and evolved into a broader management platform.

It is also used as a personal project to practice Laravel application design, legacy database integration, business rules, administration workflows, payment flows, and UI/UX improvements.

---

## Português

Plataforma web full stack desenvolvida com **Laravel 11, PHP 8.2, SQL Server, Blade e JavaScript** para gerenciamento de um servidor privado de MU Online.

O sistema integra funcionalidades para jogadores e ferramentas administrativas, incluindo autenticação, contas, rankings, eventos, notícias, loja, pagamentos e integração com o banco de dados legado do jogo.
