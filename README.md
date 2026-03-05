# GFrame - Secure Google Forms Proxy

GFrame is a lightweight PHP-based portal designed to securely embed and manage Google Forms. It provides a clean dashboard for administrators and a custom viewer for users, featuring theme persistence and authentication.

## 🚀 Features

- **Secure Proxy**: Hide direct Google Form URLs and serve them through a custom proxy.
- **Theme Persistence**: Light and dark mode support that persists across sessions and devices.
- **Admin Dashboard**: Easily manage form configurations and settings.
- **Authentication**: Secure login system for administrators.
- **SQLite Database**: Self-contained, zero-configuration database.
- **Responsive Design**: Modern UI that looks great on all devices.

## 🛠️ Tech Stack

- **PHP 8.2+**
- **SQLite 3**
- **Vanilla CSS** (with custom design system)
- **Vanilla JavaScript**

## 📂 Project Structure

```text
.
├── config/           # Application configuration
├── database/         # SQLite database file
├── public/           # Document root (images, CSS, entry points)
├── src/              # Core PHP logic and classes
├── router.php        # Development server router
└── README.md         # Project documentation
```

## ⚙️ Installation & Setup

1. **Clone the repository**:
   ```bash
   git clone <repository-url>
   cd gframe
   ```

2. **Configure the application**:
   - Copy `config/config.php` if it doesn't exist (see `config.example.php` if provided).
   - Ensure the `database/` directory is writable by the web server.

3. **Start the Development Server**:
   ```bash
   php -S localhost:8000 router.php
   ```

4. **Access the application**:
   - Open `http://localhost:8000` in your browser.
   - Login with the default credentials (found in `config/config.php`).

## 🔒 Security

- Sensitive directories (`/src`, `/config`, `/database`) are blocked by the router.
- Password hashing is handled via PHP's `password_hash()`.
- Data is sanitized before database insertion.

## 📄 License

This project is licensed under the MIT License.
