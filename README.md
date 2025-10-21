# String Analysis API (Laravel)

## 🧩 Overview
The **String Analysis API** is a Laravel-based RESTful service that analyzes strings and provides computed properties such as:
- Length  
- Palindrome check  
- Unique character count  
- Word count  
- Character frequency map  
- SHA-256 hash  

It also supports:
- Filtering by properties  
- Natural-language queries  
- Case-insensitive lookups  
- JSON file-based persistence (no database needed)

---

## ⚙️ Setup Instructions

### 🖥️ 1. Clone the Repository
```bash
git clone https://github.com/yourusername/string-analysis-api.git
cd string-analysis-api
```

### 📦 2. Install Dependencies
Make sure you have **PHP ≥ 8.1**, **Composer**, and **Laravel 10+** installed.

```bash
composer install
```

### 🧱 3. Environment Configuration
Copy the example environment file and generate an app key:

```bash
cp .env.example .env
php artisan key:generate
```

If you are using file storage (default setup), no extra configuration is needed.  
The app stores analyzed strings in a local JSON file at:

```
storage/app/strings.json
```

### ⚡ 4. Run the Application
Start the local development server:
```bash
php artisan serve
```

Your API will be available at:
```
http://127.0.0.1:8000/api
```

---

## 🧪 Running Locally with Thunder Client or cURL

### ➕ Create a String
**POST** `/api/strings`
```json
{
  "value": "racecar"
}
```
**Response (201 Created):**
```json
{
  "id": "e00f9e...",
  "value": "racecar",
  "properties": {
    "length": 7,
    "is_palindrome": true,
    "unique_characters": 4,
    "word_count": 1,
    "sha256_hash": "e00f9e...",
    "character_frequency_map": {"r":2,"a":2,"c":2,"e":1}
  },
  "created_at": "2025-10-21T12:00:00Z"
}
```

### 🔍 Get All Strings (with filters)
**GET** `/api/strings?is_palindrome=true&min_length=3&contains_character=a`

### 🔎 Get a Specific String
**GET** `/api/strings/{string_value}`

### ❌ Delete a String
**DELETE** `/api/strings/{string_value}`

### 🧠 Natural Language Filtering
**GET** `/api/strings/filter-by-natural-language?query=all single word palindromic strings`

---

## 🧰 Dependencies

| Package | Description |
|----------|-------------|
| **laravel/framework** | Core Laravel framework |
| **php** | ≥ 8.1 |
| **composer** | Dependency manager |
| **ext-json** | PHP JSON extension |
| **ext-mbstring** | For multibyte string analysis |

Install all via:
```bash
composer install
```

---

## ⚙️ Environment Variables

| Variable | Default | Description |
|-----------|----------|-------------|
| `APP_NAME` | `Laravel` | App name |
| `APP_ENV` | `local` | Environment type |
| `APP_DEBUG` | `true` | Enable error details |
| `APP_URL` | `http://127.0.0.1:8000` | Base URL |
| `LOG_CHANNEL` | `stack` | Logging channel |

For file-based persistence, no DB configuration is needed.

If you want to use a database later, you can set:
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=string_api
DB_USERNAME=root
DB_PASSWORD=
```

---


## 🗂️ Project Structure
```
app/
 └── Http/
      └── Controllers/
           └── StringController.php
routes/
 └── api.php
storage/
 └── app/
      └── strings.json
```

---

## 🧾 Notes
- Case-insensitive string handling is supported.
- Data is persisted in `storage/app/strings.json` (no DB required).
- Supports query parameter validation with detailed errors.
- Designed to be easily extendable to database storage in the future.

## Author
- Name: Eda Cynthia Itsekirimi
- Track: Backend
- Email: edacynthia3@gmail.com
