# Register Affiliate Email

A flexible WordPress plugin for managing email subscription forms with multiple service integrations.

## Features

- **OOP Architecture**: Clean, maintainable code with proper class autoloading
- **Multiple Service Support**: Integrate with AWeber, Customer.io, and any API-based email service
- **Custom Post Type**: Manage email services as WordPress custom posts
- **JSON Configuration**: Flexible service configuration using JSON format
- **Automatic Updates**: Built-in GitHub update checker
- **No jQuery**: Pure vanilla JavaScript for better performance
- **Shortcode Ready**: Simple `[register_affiliate_email]` shortcode
- **Customizable Form**: Configure placeholder text, button text, and background image

## Installation

1. Upload the plugin files to `/wp-content/plugins/register-affiliate-email/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Navigate to **Affiliate Email > Global Settings** to configure your form
4. Add email services via **Affiliate Email > Email Services**
5. Use the shortcode `[register_affiliate_email]` on any page or post

## Service Configuration

Create a new Email Service and add JSON configuration:

```json
{
  "service_type": "aweber",
  "api_key": "your-api-key-here",
  "list_id": "your-list-id",
  "endpoint": "https://api.aweber.com/1.0/accounts/YOUR_ACCOUNT/lists/YOUR_LIST/subscribers",
  "method": "POST",
  "headers": {
    "Authorization": "Bearer YOUR_TOKEN",
    "Content-Type": "application/json"
  },
  "body_template": {
    "email": "{{email}}",
    "list_id": "{{list_id}}"
  }
}
```

Available placeholders:
- `{{email}}` - User's email address
- `{{list_id}}` - List ID from config
- `{{api_key}}` - API key from config

## REST API Endpoints

### Subscribe to All Services
```
POST /wp-json/rae/v1/subscribe
```

**Request Body:**
```json
{
  "email": "user@example.com",
  "additional_data": {
    "name": "John Doe",
    "source": "homepage"
  }
}
```

**Response:**
```json
{
  "success": true,
  "message": "Subscribed to 2 of 2 services.",
  "results": {
    "success": {
      "aweber": true,
      "customerio": true
    },
    "failed": {},
    "total": 2
  }
}
```

### Subscribe to Specific Service
```
POST /wp-json/rae/v1/subscribe/{service_id}
```

**Parameters:**
- `service_id` - ID of the service post

**Request Body:**
```json
{
  "email": "user@example.com",
  "additional_data": {}
}
```

### Get Active Services
```
GET /wp-json/rae/v1/services
```

**Response:**
```json
{
  "total": 2,
  "services": [
    {
      "type": "aweber",
      "valid": true
    },
    {
      "type": "customerio",
      "valid": true
    }
  ]
}
```

## Architecture

### Service Classes

The plugin uses an abstract service pattern for email integrations:

**AbstractService** - Base class with common methods:
- `validate()` - Validate service configuration
- `authenticate()` - Authenticate with service API
- `subscribe($email, $data)` - Subscribe email to service

**Concrete Services:**
- `AWeberService` - AWeber integration
- `CustomerIOService` - Customer.io integration
- `MailchimpService` - Mailchimp integration

**ServiceFactory** - Creates service instances based on type
**ServiceRouter** - Routes subscription requests to active services

### Adding Custom Service

```php
use RegisterAffiliateEmail\Services\AbstractService;
use RegisterAffiliateEmail\Services\ServiceFactory;

class MyCustomService extends AbstractService {
    public function getType() {
        return 'my_custom_service';
    }
    
    public function validate() {
        // Validate configuration
        return true;
    }
    
    public function authenticate() {
        // Authenticate with API
        return true;
    }
    
    public function subscribe($email, $additional_data = []) {
        // Subscribe logic
        return true;
    }
}

// Register the service
ServiceFactory::registerService('my_custom_service', MyCustomService::class);
```

## Plugin Updates

To enable automatic updates from GitHub:

1. Edit `src/Updates/UpdateChecker.php`
2. Update `$github_user` and `$github_repo` with your repository details
3. Create releases on GitHub with version tags (e.g., `v1.0.0`)

## Requirements

- WordPress 5.8+
- PHP 7.4+

## Support

For issues and feature requests, please visit our GitHub repository.

## License

GPL v2 or later
