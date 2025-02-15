# News API Project

This project is a News API built with Laravel 11, PHP 8.4 and Docker. It includes a web server, application server, database, and a worker for background tasks.

## Prerequisites

- Docker
- Docker Compose

## Getting Started

Follow these steps to set up and run the project:

### 1. Clone the Repository

```sh
git clone https://github.com/oksenGit/news-api
cd news-api
```

### 2. Set Up Environment Variables

Copy a `.env.example` file into .env in the root directory and add the Keys for news services:

```env
NEWS_API_KEY=
GUARDIAN_API_KEY=
NYT_API_KEY=
```

### 3. Build and Start the Containers

Use Docker Compose to build and start the containers:

```sh
docker-compose up -d
```

### 4. Run Database Migrations and Seeders

After the containers are up and running, run the following command to migrate and seed the database:

```sh
php artisan migrate:fresh --seed
```

## Accessing the Application

- The API endpoints will be available at `http://localhost:8082/api`.

## API Endpoints

- Get the news filtered
    `GET api/news?page=1&per_page=100&title=Trump&author=Chr&source=newsapi&source_name=CNN&date_from=2025-02-12&date_to=2024-03-20&categories[]=6&categories[]=1`

- Get filters `GET /news/filters`

## Console Commands

`php artisan news:fetch --from="2025-02-15"`