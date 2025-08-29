# 🚀 Laravel 12 Advanced Authentication & Role-Based System

A comprehensive, secure, and modern Laravel 12 application featuring advanced authentication, role-based access control, and a beautiful responsive UI with gradient animations.

## ✨ Features

### 🔐 **Advanced Authentication System**
- **Multi-Method Login**: Email/Password, Email OTP, Social Login (Google, Facebook, GitHub)
- **Role-Based Access Control**: Admin and Customer roles with granular permissions
- **API Token Authentication**: Laravel Sanctum for secure API access
- **Real-Time Password Breach Checking**: Live validation against HaveIBeenPwned database
- **Password Strength Validation**: Comprehensive strength scoring and recommendations
- **Account Security**: Account lockout, IP blacklisting, suspicious activity detection

### 🛡️ **Enhanced Security Features**
- **SQL Injection Protection**: Parameterized queries throughout
- **XSS Prevention**: Output escaping and CSP headers
- **CSRF Protection**: CSRF tokens on all forms
- **DDOS Mitigation**: Rate limiting and IP blacklisting
- **Real-Time Password Breach Detection**: Using HaveIBeenPwned API
- **Multi-Factor Authentication**: Foundation for 2FA implementation
- **Security Headers**: Comprehensive security headers middleware
- **Audit Logging**: Complete security event logging

### 🎨 **Modern UI/UX Design**
- **Responsive Design**: Mobile-first approach with tablet and desktop optimization
- **Gradient Theme**: Indigo, purple, and navy blue color palette
- **Glass-morphism**: Modern backdrop-blur effects
- **Animations**: Smooth transitions and motion effects
- **Dark Mode**: Automatic dark/light mode detection
- **Social Icons**: Real social media icons (no buttons)

### 📊 **Dashboard & Analytics**
- **Admin Dashboard**: User management, system analytics, audit logs
- **Customer Dashboard**: Profile management, activity logs, preferences
- **Real-Time Statistics**: Live user and system metrics
- **Security Monitoring**: Suspicious activity detection and logging

### 🔌 **Complete API System**
- **RESTful API**: Versioned API with `/api/v1` structure
- **Authentication Endpoints**: Login, register, OTP, social login
- **Admin Endpoints**: User management, analytics, logs
- **Customer Endpoints**: Profile, preferences, activity
- **Password Validation API**: Real-time password strength and breach checking
- **Rate Limiting**: Endpoint-specific rate limiting
- **Token Management**: Secure API token handling

## 🚀 **Real-Time Password Breach Checking**

### **Enhanced Security Features**
- **Live API Integration**: Real-time checking against HaveIBeenPwned database
- **Intelligent Caching**: 1-hour cache for performance optimization
- **Retry Mechanism**: 3 retry attempts with exponential backoff
- **Comprehensive Validation**: Password strength + breach status combined
- **Detailed Feedback**: Specific recommendations for password improvement
- **Severity Levels**: Safe, Low, Medium, High, Critical breach classifications
- **Common Pattern Detection**: Identifies weak patterns like "password123"
- **API Endpoints**: Public endpoints for real-time validation
- **Frontend Integration**: JavaScript validator with visual feedback
- **Performance Optimized**: Debounced validation to prevent API spam

### **API Endpoints**
```bash
# Real-time password validation
POST /api/v1/validate-password
{
    "password": "your_password"
}

# Detailed breach information
POST /api/v1/password-breach-details
{
    "password": "your_password"
}
```

### **Response Format**
```json
{
    "success": true,
    "data": {
        "password": "your_password",
        "is_safe": true,
        "strength": {
            "score": 4,
            "max_score": 5,
            "feedback": [],
            "strength": "Good"
        },
        "breach_status": {
            "compromised": false,
            "count": 0,
            "severity": "Safe",
            "recommendation": "This password appears to be safe."
        },
        "recommendations": ["Password meets security requirements."],
        "checked_at": "2025-08-29T18:50:00.000000Z"
    }
}
```

## 🛠️ Technology Stack

- **Framework**: Laravel 12 (PHP 8.4)
- **Authentication**: Laravel Breeze, Laravel Sanctum
- **Role Management**: Spatie Laravel Permission
- **Social Login**: Laravel Socialite
- **Frontend**: Tailwind CSS 4, Alpine.js
- **Testing**: Pest 4
- **Debugging**: Laravel Telescope
- **Password Security**: HaveIBeenPwned API integration

## 📦 Installation

### Prerequisites
- PHP 8.4+
- Composer
- Node.js & NPM
- SQLite/MySQL/PostgreSQL

### Setup Instructions

1. **Clone the repository**
```bash
git clone <repository-url>
cd laravel-blade
```

2. **Install PHP dependencies**
```bash
composer install
```

3. **Install Node.js dependencies**
```bash
npm install
```

4. **Environment setup**
```bash
cp .env.example .env
php artisan key:generate
```

5. **Database setup**
```bash
php artisan migrate
php artisan db:seed --class=RolePermissionSeeder
```

6. **Build frontend assets**
```bash
npm run build
```

7. **Start the development server**
```bash
php artisan serve
```

## 🔧 Configuration

### Environment Variables
```env
# Database
DB_CONNECTION=sqlite
DB_DATABASE=database/database.sqlite

# Mail (for OTP)
MAIL_MAILER=log
MAIL_FROM_ADDRESS=noreply@example.com
MAIL_FROM_NAME="${APP_NAME}"

# Social Login (optional)
GOOGLE_CLIENT_ID=your_google_client_id
GOOGLE_CLIENT_SECRET=your_google_client_secret
GOOGLE_REDIRECT_URI=http://localhost:8000/auth/google/callback

FACEBOOK_CLIENT_ID=your_facebook_client_id
FACEBOOK_CLIENT_SECRET=your_facebook_client_secret
FACEBOOK_REDIRECT_URI=http://localhost:8000/auth/facebook/callback

GITHUB_CLIENT_ID=your_github_client_id
GITHUB_CLIENT_SECRET=your_github_client_secret
GITHUB_REDIRECT_URI=http://localhost:8000/auth/github/callback

# HaveIBeenPwned API Configuration (for password breach checking)
HAVEIBEENPWNED_API_URL=https://api.pwnedpasswords.com/range/
HAVEIBEENPWNED_CACHE_TTL=3600
HAVEIBEENPWNED_TIMEOUT=10
HAVEIBEENPWNED_MAX_RETRIES=3
HAVEIBEENPWNED_ENABLED=true
```

### Password Breach Service Configuration
The password breach checking service is automatically configured with:
- **Cache TTL**: 1 hour (3600 seconds)
- **Timeout**: 10 seconds
- **Max Retries**: 3 attempts
- **API URL**: https://api.pwnedpasswords.com/range/

## 🧪 Testing

### Run all tests
```bash
php artisan test
```

### Run specific test suites
```bash
# Password breach tests
php artisan test --filter=PasswordBreachTest

# Authentication tests
php artisan test --filter=Auth

# API tests
php artisan test --filter=Api
```

### Test Coverage
The application includes comprehensive tests for:
- ✅ Password breach service functionality
- ✅ API endpoints and validation
- ✅ Authentication and authorization
- ✅ Role-based access control
- ✅ Frontend components
- ✅ Error handling and edge cases

## 📚 API Documentation

### Authentication Endpoints

#### Register User
```http
POST /api/v1/register
Content-Type: application/json

{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "SecurePassword123!",
    "password_confirmation": "SecurePassword123!",
    "phone": "+1234567890"
}
```

#### Login
```http
POST /api/v1/login
Content-Type: application/json

{
    "email": "john@example.com",
    "password": "SecurePassword123!"
}
```

#### Send OTP
```http
POST /api/v1/send-otp
Content-Type: application/json

{
    "email": "john@example.com"
}
```

#### Verify OTP
```http
POST /api/v1/verify-otp
Content-Type: application/json

{
    "email": "john@example.com",
    "otp": "123456"
}
```

### Password Validation Endpoints

#### Validate Password (Real-time)
```http
POST /api/v1/validate-password
Content-Type: application/json

{
    "password": "your_password"
}
```

#### Get Breach Details
```http
POST /api/v1/password-breach-details
Content-Type: application/json

{
    "password": "your_password"
}
```

### Admin Endpoints (Requires Admin Role)

#### Dashboard
```http
GET /api/v1/admin/dashboard
Authorization: Bearer {token}
```

#### Users List
```http
GET /api/v1/admin/users
Authorization: Bearer {token}
```

#### System Analytics
```http
GET /api/v1/admin/analytics
Authorization: Bearer {token}
```

### Customer Endpoints (Requires Customer Role)

#### Profile
```http
GET /api/v1/customer/profile
Authorization: Bearer {token}
```

#### Update Profile
```http
PUT /api/v1/customer/profile
Authorization: Bearer {token}
Content-Type: application/json

{
    "name": "Updated Name",
    "phone": "+1234567890"
}
```

## 🔒 Security Features

### Password Security
- **Real-time breach checking** against HaveIBeenPwned database
- **Strength validation** with detailed feedback
- **Common pattern detection** (password123, qwerty, etc.)
- **Minimum requirements**: 8+ characters, uppercase, lowercase, numbers, special characters
- **Caching** for performance optimization

### Account Protection
- **Account lockout** after failed attempts
- **IP blacklisting** for suspicious activity
- **Rate limiting** on authentication endpoints
- **Audit logging** for security events
- **Multi-factor authentication** foundation

### API Security
- **Token-based authentication** with Laravel Sanctum
- **Rate limiting** on all endpoints
- **CORS protection** with proper headers
- **Input validation** and sanitization
- **Error handling** without information leakage

## 🎨 Frontend Features

### Password Validator Component
```javascript
// Initialize password validator
const validator = new PasswordValidator({
    apiUrl: '/api/v1',
    debounceDelay: 500,
    minLength: 8
});

// Initialize on form
validator.init('#registration-form', '#password-field', '#password-feedback');
```

### Features
- **Real-time validation** with debouncing
- **Visual strength meter** with color coding
- **Breach status indicators** with severity levels
- **Detailed recommendations** for improvement
- **Loading and error states**
- **Form integration** for registration and password change

## 📊 Monitoring & Logging

### Laravel Telescope
Access the debugging dashboard at `/telescope` to monitor:
- HTTP requests and responses
- Database queries
- Cache operations
- Mail sending
- Queue jobs

### Security Logs
- **Application logs**: `storage/logs/laravel.log`
- **Security logs**: `storage/logs/security.log`
- **Audit logs**: Database-stored audit trail
- **Login logs**: Failed attempt tracking

## 🚀 Deployment

### Production Checklist
- [ ] Set `APP_ENV=production`
- [ ] Configure production database
- [ ] Set up email service (SMTP)
- [ ] Configure social login providers
- [ ] Set up SSL certificate
- [ ] Configure caching (Redis recommended)
- [ ] Set up monitoring and logging
- [ ] Run `php artisan config:cache`
- [ ] Run `php artisan route:cache`
- [ ] Run `npm run build`

### Environment Variables for Production
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

DB_CONNECTION=mysql
DB_HOST=your-db-host
DB_PORT=3306
DB_DATABASE=your-database
DB_USERNAME=your-username
DB_PASSWORD=your-password

CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis

MAIL_MAILER=smtp
MAIL_HOST=your-smtp-host
MAIL_PORT=587
MAIL_USERNAME=your-email
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls
```

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🆘 Support

For support and questions:
- Create an issue in the repository
- Check the documentation
- Review the test cases for usage examples

## 🎯 Roadmap

### Planned Features
- [ ] Email templates for OTP
- [ ] Geolocation-based security
- [ ] Advanced analytics dashboard
- [ ] Mobile app API endpoints
- [ ] WebSocket real-time notifications
- [ ] File upload functionality
- [ ] Advanced caching strategies
- [ ] Load balancing support

---

**Built with ❤️ using Laravel 12 and modern web technologies**
