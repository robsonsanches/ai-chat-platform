# AI Chat Platform

AI chat platform with a Laravel API and a Vue frontend. The backend uses the
[Laravel AI SDK](https://laravel.com/docs/ai) for conversational agents,
conversation persistence, and integrations with supported AI providers.

## Stack

- **Laravel 13** and **PHP 8.3+** - API backend
- **Laravel AI SDK** (`laravel/ai`) - AI agents and conversations
- **Laravel Sanctum** - token-based authentication
- **Vue 3** and **Vite** - frontend
- **MySQL 8** - application database
- **Docker Compose** - local development environment
- **Nginx** - API web server

## Features

- User registration, login, profile management, and token revocation
- Authenticated AI conversations
- Conversation history and message pagination
- Conversation ownership: users only receive their own conversations
- Support for Laravel AI SDK providers configured in the application

## Project structure

```text
ai-chat-platform/
├── api/                  # Laravel API
├── web/                  # Vue/Vite frontend
├── docker/               # API and MySQL Docker configuration
├── data/mysql/           # Persistent MySQL data
├── env.example           # Docker Compose environment template
└── docker-compose.yml
```

## Requirements

- [Docker](https://docs.docker.com/get-docker/)
- [Docker Compose](https://docs.docker.com/compose/)

The Docker setup provides PHP, Node.js, MySQL, Nginx, and phpMyAdmin. No local
PHP, Composer, or Node.js installation is required to run the stack.

## Configuration and startup

1. Create the root environment file:

   ```bash
   cp env.example .env
   ```

2. Set the database credentials and at least one AI provider key in `.env`.
   The available provider variables include `GEMINI_API_KEY`,
   `OPENAI_API_KEY`, `ANTHROPIC_API_KEY`, `OLLAMA_API_KEY`, and the other
   providers supported by the Laravel AI SDK configuration.

3. Start the containers:

   ```bash
   docker compose up -d --build
   ```

   Before starting the frontend, create its environment file:

   ```bash
   cp web/.env.example web/.env
   ```

   Adjust `VITE_API_URL` in `web/.env` when the API is not available at
   `http://localhost:8000/api/v1`. Do not put provider API keys in this file.

4. Install backend dependencies, generate the application key, and run the
   migrations:

   ```bash
   docker compose exec api composer install
   docker compose exec api php artisan key:generate
   docker compose exec api php artisan migrate
   ```

The API is available at `http://localhost:8000` and the Vite frontend at
`http://localhost:5173`. phpMyAdmin is available at
`http://localhost:8081`.

## Laravel AI SDK

AI behavior is implemented with Laravel AI SDK contracts and concerns. The
application's chat agent is located at
[`api/app/Ai/Agents/ChatAgent.php`](./api/app/Ai/Agents/ChatAgent.php).
Provider credentials and the default provider are configured in
[`api/config/ai.php`](./api/config/ai.php).

Cloud providers require their corresponding
API key in the root `.env` file.

## API

All API routes are prefixed with `/api/v1`. Registration and login are public;
conversation and profile routes require a Sanctum bearer token.

### Authentication

| Method | Endpoint | Authentication |
| --- | --- | --- |
| `POST` | `/auth/register` | Public |
| `POST` | `/auth/login` | Public |
| `GET` | `/auth/me` | Required |
| `GET` | `/auth/refresh` | Required |
| `POST` | `/auth/logout` | Required |
| `POST` | `/auth/logout-all` | Required |
| `PATCH` | `/auth/profile` | Required |

### Conversations

| Method | Endpoint | Authentication |
| --- | --- | --- |
| `GET` | `/conversations` | Required |
| `POST` | `/conversations` | Required |
| `GET` | `/conversations/{id}` | Required |
| `PATCH` | `/conversations/{id}` | Required |
| `DELETE` | `/conversations/{id}` | Required |
| `GET` | `/conversations/{conversationId}/messages` | Required |

Conversation listing supports the `per_page`, `order_by`, and `order` query
parameters. Results are restricted to conversations owned by the authenticated
user.

## Testing

Run the backend test suite inside the API container:

```bash
docker compose exec api php artisan test
```

Run frontend linting and build commands inside the frontend container:

```bash
docker compose exec web npm run lint
docker compose exec web npm run build
```

For production, `npm run build` generates `web/dist/`, which should be served
by a web server or CDN. The generated directory is intentionally ignored by
Git and should not be committed.

## License

This project is licensed under the MIT License. See the `LICENSE` file for
details.
