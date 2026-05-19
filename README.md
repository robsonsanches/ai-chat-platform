# AI Chat Platform

Modern AI chat platform built with **Laravel 13**, designed for creating conversational AI applications with support for local and cloud LLMs.

---

## 📂 Project Structure

```bash
ai-chat-platform/
├── api/                          # Backend - Laravel API
│   ├── app/
│   ├── bootstrap/
│   ├── config/
│   ├── database/
│   ├── public/
│   ├── resources/
│   ├── routes/
│   ├── storage/
│   ├── tests/
│   ├── .env
│   ├── composer.json
│   └── ...
├── docker/
│   ├── api/
│   │   ├── Dockerfile           # PHP-FPM for Laravel
│   │   ├── docker-entrypoint.sh
│   │   └── default.conf         # Nginx configuration
│   └── mysql/
├── data/
│   └── mysql/                   # Persistent MySQL data
├── logs/
│   └── api/
├── .env                         # Root environment variables
├── env.example
├── docker-compose.yml
└── README.md
```

---

## 🛠️ Technologies Used

### Backend
- **Laravel 13** - PHP framework for API development
- **Laravel Sanctum** - Authentication for SPA and API
- **LLPhant** - AI orchestration and LLM integration
- **MySQL 8.0** - Relational database

### Infrastructure
- **Docker & Docker Compose** - Containerization and orchestration
- **Nginx** - Web server and reverse proxy

---

## ✨ Features

- AI chat conversations
- Conversation history management
- Support for local LLMs with Ollama
- Integration-ready for OpenAI and compatible APIs
- RESTful API architecture
- Token-based authentication
- Dockerized development environment
- Scalable backend structure

---

## 📋 Prerequisites

- [Docker](https://docs.docker.com/get-docker/)
- [Docker Compose](https://docs.docker.com/compose/)

---

## 🚀 How to Run

### 1. Configure environment variables

Create a `.env` file in the project root based on `env.example`:

```bash
cp env.example .env
```

Edit the `.env` file with your environment settings.

---

### 2. Start the containers

```bash
docker compose up -d
```

---

### 3. Configure the Laravel API

```bash
# Install backend dependencies
docker compose exec api composer install

# Generate application key
docker compose exec api php artisan key:generate

# Run database migrations
docker compose exec api php artisan migrate

# (Optional) Run database seeders
docker compose exec api php artisan db:seed
```

---

## 🔌 AI Providers

The platform is designed to support multiple AI providers, including:

- Ollama (local LLMs)
- OpenAI API
- Compatible OpenAI-like APIs
- Future support for additional providers

Example supported local models:

- Qwen
- Gemma
- Phi
- TinyLlama

---

## 📚 API Documentation

API documentation will be available soon.

---

## 🧪 Testing

Run backend tests with:

```bash
docker compose exec api php artisan test
```

---

## 🤝 Contributing

1. Fork the project
2. Create a feature branch:

```bash
git checkout -b feature/amazing-feature
```

3. Commit your changes:

```bash
git commit -m 'feat(scope): add some amazing feature'
```

4. Push to your branch:

```bash
git push origin feature/amazing-feature
```

5. Open a pull request

---

## 📝 License

This project is licensed under the MIT License. See the `LICENSE` file for details.