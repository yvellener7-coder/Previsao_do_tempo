# Projeto Fullstack PHP + MySQL

Estrutura inicial para um projeto com:

- Backend: PHP
- Frontend: HTML, CSS e JavaScript
- Banco de dados: MySQL

## Estrutura

```text
.
|-- app/
|   |-- config/
|   |   `-- database.php
|   |-- controllers/
|   |   `-- HomeController.php
|   |-- core/
|   |   `-- Database.php
|   |-- models/
|   |   `-- User.php
|   |-- routes/
|   |   `-- web.php
|   `-- views/
|       `-- home.php
|-- database/
|   `-- schema.sql
|-- public/
|   |-- assets/
|   |   |-- css/
|   |   |   `-- style.css
|   |   `-- js/
|   |       `-- app.js
|   |-- .htaccess
|   `-- index.php
`-- .env.example
```

## Como usar

1. Copie `.env.example` para `.env` e ajuste as credenciais.
2. Crie o banco e execute `database/schema.sql`.
3. Configure seu servidor web para apontar para `public/`.
