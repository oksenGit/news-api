# News API Project

This project is a News API built with Laravel 11, PHP 8.4 and Docker. It includes a web server, application server, database, and a worker for background tasks.

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

## API Endpoints

- Get the news filtered
    `GET api/news?page=1&per_page=100&title=title&author=chris&source=newsapi&source_name=CNN&date_from=2025-02-12&date_to=2024-03-20&categories[]=6&categories[]=1`

- Get filters `GET /news/filters`

## Console Commands

To Fetch news from all news sources manually, --from is optional by default, the latest fetch time is cached and the news is fetched starting from the cached time
`php artisan news:fetch --from="2025-02-15"`

## System Architecture

### Key Components

1. **News Sources**
   - Located in `app/Services/NewsSources/`
   - Each source implements the `NewsSourceInterface`
   - Currently supports:
     - NewsAPI
     - The Guardian
     - New York Times

2. **Command Layer**
   - `FetchNewsCommand` orchestrates the news fetching process
   - Dispatches jobs for each configured news source
   - Manages fetch time tracking and cache invalidation

3. **Job System**
   - Uses Laravel's queue system
   - `FetchNewsFromSource` job handles individual source fetching
   - Jobs run asynchronously for better performance

4. **Repository Layer**
   - `EloquentNewsRepository` handles all database operations
   - Implements `NewsRepository` interface
   - Manages news storage and retrieval with category relationships

5. **Filtering System**
   - `NewsFiltersService` manages available filters
   - Supports filtering by title, author, source, date range, and categories
   - Caches filter options for better performance

### Adding a New News Source

To add a new news source:

1. Add the configs related to the new source in `config/services.php`
2. Create a new class in `app/Services/NewsSources/` that implements `NewsSourceInterface`:
3. Create a news source adapter in `app/Services/NewsSources/Adapters/` to map the response to the news model
4. Add the new source to the `FetchNewsCommand` to be dispatched
5. map the source to the adapter in the `NewsFetchService`
