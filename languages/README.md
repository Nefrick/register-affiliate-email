# Translations

This directory contains translation files for the Register Affiliate Email plugin.

## File Format

Each language has its own PHP file that returns an array of translations.

**File naming:** `{locale}.php` (e.g., `de_DE.php`, `fr_FR.php`)

## Creating a New Translation

1. Create a new file named `{locale}.php` (e.g., `es_ES.php` for Spanish)
2. Copy the structure from `de_DE.php`
3. Translate all the values (keep the keys unchanged)
4. Save the file

Example structure:
```php
<?php
return [
    'form_heading' => 'Your translated heading',
    'button_text' => 'Your translated button',
    'submitting' => 'Submitting...',
    // etc.
];
```

## Available Translations

- German (`de_DE.php`)

## Translation Keys

### Form Fields (dynamic from admin settings)
- `form_heading` - Main form heading
- `form_subheading` - Form subheading text
- `input_placeholder` - Email input placeholder
- `button_text` - Submit button text
- `agreement_text` - Agreement checkbox text
- `success_message` - Success message (can contain HTML)

### Static Strings (used in code/templates)
- `submitting` - Loading message during form submission
- `please_enter_email` - Email required validation message
- `please_enter_valid_email` - Email format validation message
- `please_accept_agreement` - Agreement required validation message
- `error_occurred` - Generic error message
