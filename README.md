# DesignHub - Freelance Design Services Platform

A multi-vendor platform connecting clients with freelance designers, built with PHP and MySQL.

## Features

- **User Roles**: Admin, Vendor (Designer), and Client
- **Service Management**: Vendors can create and manage their design services
- **Booking System**: Clients can browse and book design services
- **Order Tracking**: Real-time order status updates and history
- **Profile Management**: Customizable profiles for vendors and clients
- **Admin Dashboard**: Complete platform management and oversight

## Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache/Nginx web server
- mod_rewrite enabled (for clean URLs)

## Installation

1. Clone the repository:
```bash
git clone https://github.com/yourusername/designhub.git
cd designhub
```

2. Create a MySQL database:
```sql
CREATE DATABASE fbp CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

3. Import the database schema:
```bash
mysql -u your_username -p fbp < sample_schema.sql
```

4. (Optional) Import sample data:
```bash
mysql -u your_username -p fbp < sample_data.sql
```

5. Configure database connection:
   - Open `includes/db.php`
   - Update the database credentials:
     ```php
     $host = 'localhost';
     $db   = 'fbp';
     $user = 'your_username';
     $pass = 'your_password';
     ```

6. Set up your web server:
   - Point your web root to the project directory
   - Ensure the `uploads` directory is writable:
     ```bash
     chmod 755 uploads
     ```

## Sample Users

After importing sample data, you can log in with these accounts:

- **Admin**:
  - Email: admin@designhub.com
  - Password: password

- **Vendor**:
  - Email: sarah@designhub.com
  - Password: password

- **Client**:
  - Email: client1@example.com
  - Password: password

## Directory Structure

```
FBP/
├── admin/          # Admin dashboard and management
├── api/           # API endpoints (future use)
├── assets/        # Static assets (CSS, JS, images)
├── client/        # Client area
├── includes/      # Core PHP includes
├── uploads/       # User uploads
└── vendor/        # Vendor/designer area
```

## Usage

### For Clients

1. Register a new account as a client
2. Browse available design services
3. Book services and provide requirements
4. Track order progress
5. Manage your profile and bookings

### For Vendors

1. Register as a vendor
2. Set up your profile and portfolio
3. Add your design services
4. Manage orders and update their status
5. Communicate with clients

### For Admins

1. Log in to the admin dashboard
2. Manage users, services, and orders
3. Monitor platform activity
4. Configure site settings

## Security

- All passwords are hashed using bcrypt
- Input validation and sanitization
- Session-based authentication
- Role-based access control
- SQL injection prevention
- XSS protection

## Contributing

1. Fork the repository
2. Create your feature branch
3. Commit your changes
4. Push to the branch
5. Create a new Pull Request

## License

This project is licensed under the MIT License - see the LICENSE file for details.