# Server Maintenance Log CMS

A professional, secure, and responsive Content Management System for tracking server maintenance activities. Built with modern web technologies, this system allows IT staff and administrators to efficiently record, manage, and search maintenance logs with enterprise-grade features.

## 🚀 Project Overview

The Server Maintenance Log CMS is designed to streamline IT operations by providing a centralized platform for tracking, monitoring, and reporting on server maintenance activities. It features role-based access control, advanced search capabilities, and a modern responsive interface that works seamlessly across all devices.

This system is ideal for:
- IT departments and managed service providers
- Data centers and hosting companies
- System administrators and DevOps teams
- Organizations requiring audit trails for compliance

## 🛠️ Technologies Used

### Backend
- **PHP 7.4+** - Server-side scripting language
- **MySQL 5.7+** - Relational database management system
- **PDO** - Database abstraction layer for secure connections

### Frontend
- **HTML5** - Semantic markup structure
- **CSS3** - Modern styling with Flexbox/Grid layouts
- **JavaScript (ES6+)** - Interactive functionality and form handling
- **Responsive Design** - Mobile-first approach with CSS media queries

### Security & Performance
- **Password Hashing** - Bcrypt encryption for user credentials
- **CSRF Protection** - Cross-site request forgery prevention
- **Input Validation** - Comprehensive sanitization and validation
- **Session Management** - Secure user authentication system

## ✨ Key Features

### 🔐 Authentication & Security
- Secure user registration and login system
- Password hashing with bcrypt algorithm
- CSRF token protection on all forms
- Session timeout and security management
- Role-based access control (Admin/Staff)

### 📊 Maintenance Log Management
- Complete CRUD operations for maintenance records
- Rich text descriptions with formatting support
- File attachment capabilities
- Maintenance type categorization
- Status tracking and workflow management

### 🔍 Advanced Search & Filtering
- Full-text search across all log fields
- Advanced filtering by date, status, type, and server
- Real-time search with highlighting
- Export functionality for reports
- Pagination for large datasets

### 📱 User Experience
- Fully responsive design for all devices
- Modern, intuitive interface
- Auto-save draft functionality
- Keyboard shortcuts and accessibility features
- Dark/light theme support

### 📈 Analytics & Reporting
- Dashboard with key performance indicators
- Maintenance statistics and trends
- Custom report generation
- Data visualization charts
- Export to multiple formats

## 👥 User Roles

### 🔑 Administrator
- **Full System Access**: Complete control over all features
- **User Management**: Create, edit, and manage user accounts
- **System Configuration**: Modify application settings and preferences
- **Data Management**: Access to all maintenance logs and system data
- **Audit Logs**: View system activity and user actions
- **Backup & Restore**: Database backup and restoration capabilities

### 👷 Staff Member
- **Log Management**: Create and manage own maintenance logs
- **View Access**: Access to all maintenance logs for reference
- **Search & Filter**: Use advanced search and filtering capabilities
- **Limited Editing**: Edit only logs created by themselves
- **Reporting**: Generate reports for assigned tasks
- **Notifications**: Receive updates on maintenance schedules

## 🏗️ Project Structure

```
server-maintenance-cms/
├── 📁 assets/                    # Static assets
│   ├── 📁 css/                   # Stylesheets
│   │   └── style.css            # Main CSS file
│   ├── 📁 js/                    # JavaScript files
│   │   └── app.js               # Main JS functionality
│   └── 📁 images/                # Image assets
├── 📁 config/                     # Configuration files
│   ├── config.php                # Application settings
│   └── database.php              # Database connection
├── 📁 includes/                   # PHP classes and components
│   ├── User.php                  # User management class
│   ├── MaintenanceLog.php        # Maintenance log operations
│   ├── header.php                # Common header template
│   └── footer.php                # Common footer template
├── 📁 sql/                        # Database files
│   └── database_setup.sql        # Database schema and initial data
├── 📁 docs/                       # Documentation
│   └── CONFIGURATION.md          # Configuration guide
├── 📄 index.php                   # Entry point and routing
├── 📄 login.php                   # User authentication
├── 📄 register.php                # User registration
├── 📄 dashboard.php               # Main dashboard
├── 📄 logs.php                    # Maintenance logs listing
├── 📄 add_log.php                 # Create new log
├── 📄 edit_log.php                # Edit existing log
├── 📄 view_log.php                # View log details
├── 📄 delete_log.php              # Delete log
├── 📄 search.php                  # Search functionality
├── 📄 users.php                   # User management (admin)
├── 📄 logout.php                  # Logout functionality
├── 📄 install.php                 # Installation wizard
└── 📄 README.md                   # This documentation
```

## 🚀 Setup Instructions

### Prerequisites
- **Web Server**: Apache 2.4+ or Nginx 1.18+
- **PHP**: Version 7.4 or higher
- **Database**: MySQL 5.7+ or MariaDB 10.2+
- **Extensions**: PDO, PDO_MySQL, OpenSSL, JSON

### Quick Installation

1. **Download & Extract**
   ```bash
   # Clone the repository
   git clone https://github.com/soikot-shahriaar/server-maintenance-cms
   cd server-maintenance-cms
   
   # Or download and extract ZIP file
   # Extract to your web server directory
   ```

2. **Database Setup**
   ```bash
   # Create MySQL database
   mysql -u root -p
   CREATE DATABASE server_maintenance_cms;
   USE server_maintenance_cms;
   
   # Import schema
   mysql -u root -p server_maintenance_cms < sql/database_setup.sql
   ```

3. **Configuration**
   ```php
   # Edit config/database.php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'server_maintenance_cms');
   define('DB_USER', 'your_username');
   define('DB_PASS', 'your_password');
   ```
   
   **Note:** The application automatically detects the base URL (including project folder name) and removes any port numbers (like :8080). No manual URL configuration is needed.

4. **Web Server Configuration**
   - Point document root to project directory or place project in a subdirectory
   - Ensure proper file permissions (755 for dirs, 644 for files)
   - Enable URL rewriting if needed
   - The application works with or without a subdirectory (project folder)

5. **Access Application**
   - Navigate to `http://localhost/server-maintenance-cms` (or your project folder name)
   - The application will automatically detect the correct base URL without port numbers
   - If you see redirects to `http://localhost:8080/project_name`, the application will automatically fix this to `http://localhost/project_name`
   - Login with admin credentials
   - Complete initial setup wizard

### Manual Installation Steps

1. **File Permissions**
   ```bash
   # Set proper permissions
   chmod 755 -R directories/
   chmod 644 -R files/
   chmod 755 config/
   ```

2. **Database Configuration**
   - Update database connection settings
   - Verify database user permissions
   - Test connection

3. **Security Setup**
   - Change default admin password
   - Configure session timeout
   - Set up SSL certificate (recommended)

## 📖 Usage

### Getting Started

1. **First Login**
   - Use default admin credentials
   - Change password immediately
   - Configure system settings

2. **User Management**
   - Create staff accounts
   - Assign appropriate roles
   - Set up access permissions

3. **Maintenance Logs**
   - Add new maintenance records
   - Categorize by type and priority
   - Track completion status

### Daily Operations

1. **Creating Logs**
   - Fill in server information
   - Describe maintenance activities
   - Set appropriate status and type
   - Add relevant attachments

2. **Monitoring Progress**
   - Check dashboard for overview
   - Review pending maintenance
   - Track completion rates

3. **Reporting**
   - Generate monthly reports
   - Export data for analysis
   - Review performance metrics

### Best Practices

- **Regular Updates**: Keep system and dependencies updated
- **Backup Strategy**: Implement regular database backups
- **User Training**: Provide training for new staff members
- **Documentation**: Maintain up-to-date procedures
- **Security Review**: Regular security audits and updates

## 🎯 Intended Use

### Primary Applications
- **IT Infrastructure Management**: Track server maintenance schedules
- **Compliance & Auditing**: Maintain detailed records for regulatory requirements
- **Team Collaboration**: Coordinate maintenance activities across teams
- **Performance Monitoring**: Analyze maintenance patterns and optimize schedules
- **Incident Response**: Document and track emergency maintenance activities

### Industry Use Cases
- **Data Centers**: Manage large-scale infrastructure maintenance
- **Managed Services**: Track client server maintenance activities
- **Enterprise IT**: Coordinate maintenance across multiple locations
- **Cloud Services**: Monitor and maintain cloud infrastructure
- **Healthcare IT**: Ensure compliance with healthcare regulations

### Limitations
- **Free Version**: Basic functionality for demo and personal use
- **Pro Features**: Advanced features require Pro license
- **Customization**: Limited customization in free version
- **Support**: Basic support for free version users

## 📄 License

**License for RiverTheme**

RiverTheme makes this project available for demo, instructional, and personal use. You can ask for or buy a license from [RiverTheme.com](https://RiverTheme.com) if you want a pro website, sophisticated features, or expert setup and assistance. A Pro license is needed for production deployments, customizations, and commercial use.

**Disclaimer**

The free version is offered "as is" with no warranty and might not function on all devices or browsers. It might also have some coding or security flaws. For additional information or to get a Pro license, please get in touch with [RiverTheme.com](https://RiverTheme.com).

---