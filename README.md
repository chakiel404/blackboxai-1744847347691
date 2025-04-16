
Built by https://www.blackbox.ai

---

```markdown
# CORS and Authentication API

## Project Overview
This project is a PHP-based API designed to handle various educational operations, particularly focused on managing users (students and teachers), schedules, subjects, and grades. The API supports CORS (Cross-Origin Resource Sharing) and includes JWT (JSON Web Tokens) for authentication. It has routes to handle CRUD (Create, Read, Update, Delete) operations for teachers, classes, subjects, assignments, grades, and materials.

## Installation
To set up the project locally, ensure you have a web server such as Apache or Nginx configured with PHP and a MySQL database. Follow these steps:

1. **Clone the repository**:
   ```bash
   git clone <repository-url>
   cd cors-auth-api
   ```

2. **Setup configuration**:
   - Update `config/database.php` with your database credentials.

3. **Create database tables**:
   Execute the necessary SQL statements to create required tables in the database based on your schema.

4. **Start your server**:
   Use the built-in PHP server or deploy it to your local web server.

5. **Access your API**:
   Open your browser or an API client (like Postman) and navigate to `http://localhost/cors-auth-api/` to start accessing the endpoints.

## Usage
The API supports the following HTTP methods for different resources:

- **GET**: Retrieve resources (e.g., fetch teachers, classes).
- **POST**: Create new resources (e.g., add a teacher, add a class).
- **PUT**: Update existing resources (e.g., update a teacher's details).
- **DELETE**: Delete resources (e.g., remove a class).

**Example Usage**: 
To log in:
- POST to `/auth.php` with JSON body containing email and password.

To fetch a list of teachers:
- GET request to `/guru.php`.

## Features
- Authentication using JWT for secure access.
- CORS configuration for cross-domain requests.
- CRUD operations for users, classes, subjects, and grades.
- Detailed error handling for better debugging.
- Supports multiple user roles (admin, teacher, student) with permission checks.

## Dependencies
The project does not use external libraries and relies mainly on PHP's built-in functions. However, ensure that the following PHP extensions are enabled:
- `mysqli` for database operations.
- `json` for handling JSON data.
- `curl` for server requests (if external API integration is necessary).

## Project Structure
Here's a summary of key files and their purposes:

```
/config
    - cors.php         # Handles CORS headers and responses.
    - database.php     # Database connection settings and initialization.
    - jwt.php          # JWT token generation and verification.
    
/auth.php             # Handles user authentication and login.
(cors_test.php)       # A simple script to test CORS behavior.
    
/guru.php             # Endpoint to manage teachers (CRUD).
/kelas.php            # Endpoint to manage classes (CRUD).
/mapel.php            # Endpoint to manage subjects (CRUD).
/nilai.php            # Endpoint to manage grades (CRUD).
/pengumpulan.php       # Endpoint to manage submissions for tasks (CRUD).
/materi.php           # Endpoint to manage teaching materials.
```

## Contributing
To contribute to this project:
1. Fork the repository.
2. Create a feature branch.
3. Commit your changes.
4. Push to the branch.
5. Submit a pull request.

Please make sure to update tests as appropriate.

## License
This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.
```