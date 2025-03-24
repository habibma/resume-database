# Resume Database

This is a web-based resume database application built using PHP, MySQL, JavaScript, and jQuery. It allows users to create, update, and manage profiles, positions, and educational backgrounds.

## Features
- User authentication (Login & Logout)
- CRUD operations for profiles, positions, and education
- Many-to-one and many-to-many relationships (Profile ↔ Position, Profile ↔ Education ↔ Institution)
- Client-side validation using jQuery
- Server-side validation using PHP
- Flash messages for user feedback
- Secure database interactions with PDO
- Follows POST-Redirect-GET (PRG) pattern

## Technologies Used
- PHP
- MySQL
- JavaScript (jQuery)
- Tailwind CSS (for UI styling)

## Installation
1. **Clone the repository**:
   ```sh
   git clone https://github.com/YOUR_USERNAME/resume-database.git
   cd resume-database
   ```

2. **Set up the database**:
   - Import the `database.sql` file into your MySQL database.
   - Update the `pdo.php` file with your database credentials:
     ```php
     $pdo = new PDO('mysql:host=localhost;dbname=your_database', 'username', 'password');
     ```

3. **Start the server**:
   If using PHP's built-in server, run:
   ```sh
   php -S localhost:8000
   ```
   Then visit [http://localhost:8000](http://localhost:8000) in your browser.

## Usage
1. Register or log in to the system.
2. Add a new profile with positions and education details.
3. Edit or delete existing profiles.
4. Logout when done.

## File Structure
```
resume-database/
│── index.php           # Homepage with profile list
│── add.php             # Add new profile
│── edit.php            # Edit profile
│── delete.php          # Delete profile
│── view.php            # View profile details
│── pdo.php             # Database connection
│── login.php           # User authentication
│── logout.php          # Logout handler
│── styles.css          # CSS styles (if any)
│── README.md           # Documentation
```

## Contributing
Feel free to fork this repository and submit pull requests with improvements!

## License
This project is open-source under the [MIT License](LICENSE).
