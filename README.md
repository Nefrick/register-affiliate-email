# Register Affiliate Email

**The easiest way to connect email subscription forms with multiple marketing services.**

Grow your email list faster with flexible forms that work with AWeber, Customer.io, Mailchimp, and more. No coding required.

## Description

Register Affiliate Email is a powerful yet simple WordPress plugin that helps you capture email subscribers and automatically sync them with your favorite email marketing services.

**Perfect for:**
* Affiliate marketers managing multiple campaigns
* Bloggers growing their email lists
* Businesses using multiple email platforms
* Anyone who wants flexible, beautiful subscription forms

**What makes it special:**
* **One Form, Multiple Services** - Subscribers automatically added to all your connected services
* **Simple Setup** - Just add a shortcode and you're done
* **Beautiful Forms** - Customizable design that matches your site
* **REST API** - Integrate with any application or landing page builder
* **Auto Updates** - Get new features automatically from GitHub

## Supported Services

**Currently Integrated:**
* ✓ AWeber - Popular email marketing platform
* ✓ Customer.io - Customer engagement automation (with segment support)
* ✓ Mailchimp - Email marketing and automation

**Easily Extensible:**
Add any API-based email service with simple JSON configuration. No PHP coding required!

## Installation

**Method 1: WordPress Admin**
1. Download the plugin ZIP file
2. Go to Plugins → Add New → Upload Plugin
3. Choose the ZIP file and click Install Now
4. Activate the plugin

**Method 2: Manual**
1. Upload the plugin files to `/wp-content/plugins/register-affiliate-email/`
2. Go to Plugins page in WordPress admin
3. Find "Register Affiliate Email" and click Activate

**Quick Start:**
1. Go to **Affiliate Email → Global Settings**
2. Customize your form (button text, placeholder, background)
3. Go to **Affiliate Email → Email Services** → Add New
4. Configure your email service (AWeber, Customer.io, or Mailchimp)
5. Enable the service in Global Settings
6. Add shortcode `[register_affiliate_email]` to any page

## Features

**For Users:**
* Simple one-field email form
* Beautiful, customizable design
* Works anywhere with shortcode `[register_affiliate_email]`
* Mobile-responsive
* Fast and lightweight (no jQuery)

**For Developers:**
* Clean OOP architecture
* REST API endpoints
* Custom service integration
* WordPress coding standards
* Automatic GitHub updates
* Extensible with hooks and filters

**For Marketers:**
* Connect unlimited services
* Segment-based targeting (Customer.io)
* Track subscription sources
* Multiple forms on one site
* Global settings for easy management

## Frequently Asked Questions

**How do I add a subscription form?**

Just add the shortcode `[register_affiliate_email]` to any page, post, or widget area.

**Can I use multiple email services?**

Yes! Enable as many services as you want in Global Settings. Subscribers will be added to all enabled services automatically.

**How do I customize the form design?**

Go to Affiliate Email → Global Settings. You can change button text, placeholder text, and upload a custom background image.

**Does it work with landing page builders?**

Yes! Use the shortcode in any page builder, or integrate via REST API at `/wp-json/rae/v1/subscribe`

**How do I add a new service?**

1. Go to Affiliate Email → Email Services → Add New
2. Enter service name
3. Add JSON configuration with your API credentials
4. Enable it in Global Settings

**Is it GDPR compliant?**

The plugin only collects email addresses. You're responsible for adding proper consent mechanisms and privacy policy links to your forms.

## Changelog

### 0.0.8
* Added beautiful plugin icons and banners
* Improved update information display
* Added WordPress 6.9 compatibility
* Simplified README for better display

### 0.0.7
* Fixed update mechanism for GitHub releases
* Improved release detection

### 0.0.6
* Initial public release
* AWeber integration
* Customer.io integration with segment support
* Mailchimp integration
* REST API implementation
* Shortcode support
* Global settings page

## Privacy Policy

This plugin does not collect any user data. Email addresses submitted through forms are sent directly to your configured email marketing services.

## Credits

Developed by Michael Chizhevskiy

## Support

Need help? Have a feature request?

* GitHub: https://github.com/Nefrick/register-affiliate-email
* Issues: https://github.com/Nefrick/register-affiliate-email/issues

## License

GPL v2 or later
