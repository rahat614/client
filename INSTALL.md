# Quick Installation Guide

## Step 1: Upload Plugin
1. Upload the `noor-homeopathy-prescription` folder to `/wp-content/plugins/`
2. Or zip the folder and upload via WordPress Admin > Plugins > Add New > Upload Plugin

## Step 2: Activate Plugin
1. Go to WordPress Admin > Plugins
2. Find "Noor Homeopathy Prescription System"
3. Click "Activate"

## Step 3: Create Frontend Page
1. Go to WordPress Admin > Pages > Add New
2. Title: "Prescription System"
3. Content: `[noor_prescription]`
4. Publish the page

## Step 4: Start Using
- Visit your new page to access the prescription system
- Access admin panel via WordPress Admin > Prescriptions

## Database Tables Created
- `wp_patients` - Patient information
- `wp_prescriptions` - Prescription records

## Shortcode
Use `[noor_prescription]` to display the prescription form on any page or post.

## Requirements
- WordPress 5.0+
- PHP 7.4+
- MySQL 5.6+