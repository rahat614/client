# Noor Homeopathy Prescription System

A comprehensive WordPress plugin for homeopathy clinics to manage patient registration, prescription creation, and PDF generation with a beautiful, modern interface.

## Features

### 🏥 Patient Management
- **Patient Registration**: Register new patients with complete information
- **Patient Lookup**: Quickly find and auto-fill patient details using Patient ID
- **Patient Database**: Secure storage of patient information with proper indexing

### 📋 Prescription Management
- **Dynamic Medicine Forms**: Add multiple medicines with potency and instructions
- **Disease Information**: Record patient symptoms and diagnosis
- **Prescription History**: View all prescriptions with search and filter options
- **Auto-fill Functionality**: Lookup patients by ID to auto-populate forms

### 📄 PDF Generation
- **Professional PDFs**: Generate beautifully formatted prescription PDFs
- **Multiple Generation Methods**: Advanced HTML2Canvas + jsPDF or simple print fallback
- **Custom Branding**: Includes doctor information and clinic branding
- **Automatic Naming**: PDFs are named with patient name and date

### 🎨 Modern Interface
- **Beautiful UI**: Modern, responsive design with Tailwind CSS
- **Mobile Friendly**: Fully responsive design works on all devices
- **Smooth Animations**: Enhanced user experience with smooth transitions
- **Modal Dialogs**: Clean modal interfaces for viewing prescription details

### 🔒 Security Features
- **Nonce Protection**: WordPress nonce verification for all AJAX requests
- **Data Sanitization**: All inputs are properly sanitized and validated
- **SQL Injection Protection**: Uses WordPress prepared statements
- **Access Control**: Proper capability checks for admin functions

## Installation

### Prerequisites
- WordPress 5.0 or higher
- PHP 7.4 or higher
- MySQL 5.6 or higher

### Installation Steps

1. **Download the Plugin**
   ```bash
   # Clone or download the plugin files
   git clone [repository-url] noor-homeopathy-prescription
   ```

2. **Upload to WordPress**
   - Upload the plugin folder to `/wp-content/plugins/`
   - Or compress the folder and upload via WordPress admin

3. **Activate the Plugin**
   - Go to WordPress Admin > Plugins
   - Find "Noor Homeopathy Prescription System"
   - Click "Activate"

4. **Database Setup**
   - The plugin automatically creates necessary database tables on activation:
     - `wp_patients` - Patient information
     - `wp_prescriptions` - Prescription records

## Usage

### Setting Up the Frontend Form

1. **Create a Page**
   - Go to WordPress Admin > Pages > Add New
   - Add a title like "Prescription System"
   - Add the shortcode: `[noor_prescription]`
   - Publish the page

2. **Access the System**
   - Visit the page you created
   - You'll see the prescription form interface

### Using the Prescription System

#### Patient Registration
1. **New Patient**: Enter patient details in the form
2. **Existing Patient**: Use the "Lookup Patient" feature
   - Enter Patient ID
   - Click "Lookup Patient" or press Enter
   - Patient information will auto-fill

#### Creating Prescriptions
1. **Patient Information**: Fill in or lookup patient details
2. **Disease Information**: Describe symptoms and diagnosis
3. **Add Medicines**: 
   - Click "Add Medicine" to add rows
   - Enter medicine name, potency, and instructions
   - Remove medicines with the delete button
4. **Save**: Click "Save Prescription" to store in database

#### Managing Prescriptions
1. **View All**: Click "View All Prescriptions" to see the table
2. **View Details**: Click "View" to see prescription details in a modal
3. **Generate PDF**: Click "PDF" to download prescription as PDF
4. **Delete**: Click "Delete" to remove a prescription (with confirmation)

### Admin Panel

Access the admin panel via WordPress Admin > Prescriptions:

- **All Prescriptions**: View and manage all prescriptions
- **Patients**: View and manage patient records
- Statistics and management tools

## Database Schema

### Patients Table (`wp_patients`)
```sql
CREATE TABLE wp_patients (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    patient_id VARCHAR(50) NOT NULL UNIQUE,
    date DATE NOT NULL,
    patient_name VARCHAR(200) NOT NULL,
    gender VARCHAR(10) NOT NULL,
    age INT(3) NOT NULL,
    blood_group VARCHAR(10) NOT NULL,
    address TEXT NOT NULL,
    phone VARCHAR(20) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_patient_id (patient_id),
    INDEX idx_patient_name (patient_name),
    INDEX idx_phone (phone)
);
```

### Prescriptions Table (`wp_prescriptions`)
```sql
CREATE TABLE wp_prescriptions (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    patient_id VARCHAR(50) NOT NULL,
    prescription_date DATE NOT NULL,
    patient_name VARCHAR(200) NOT NULL,
    gender VARCHAR(10) NOT NULL,
    age INT(3) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    disease_info TEXT,
    medicines TEXT NOT NULL,
    instructions TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_patient_id (patient_id),
    INDEX idx_prescription_date (prescription_date),
    INDEX idx_patient_name (patient_name)
);
```

## Customization

### Doctor Information
Edit the header information in `includes/frontend-form.php`:
```html
<h1>Noor Homeopathy Prescription</h1>
<p>Dr. Isahaque Obaidi - B.H.M.S (DU), D.M.U (Ultrasonography)</p>
<p>Mirpur Tulaputti, Kushtia</p>
```

### Styling
The plugin uses Tailwind CSS with custom styles. You can:
- Modify the CSS in `includes/frontend-form.php`
- Override styles in your theme's CSS
- Customize colors and branding

### Validation Rules
Phone number validation (11 digits) can be modified in the JavaScript section.

## AJAX Endpoints

The plugin provides several AJAX endpoints:

- `get_patient` - Lookup patient by ID
- `save_prescription_ajax` - Save new prescription
- `delete_prescription_ajax` - Delete prescription
- `get_all_prescriptions` - Fetch all prescriptions

## Security Considerations

1. **Nonce Verification**: All AJAX requests include nonce verification
2. **Data Sanitization**: All inputs are sanitized using WordPress functions
3. **Capability Checks**: Admin functions require proper permissions
4. **SQL Injection Protection**: Uses WordPress prepared statements
5. **File Upload Security**: PDF directory has index protection

## Troubleshooting

### Common Issues

1. **PDF Generation Not Working**
   - Check if jsPDF and html2canvas libraries are loading
   - Ensure pop-up blockers are disabled
   - Try the fallback print method

2. **Patient Lookup Not Working**
   - Verify AJAX URL is correct
   - Check browser console for JavaScript errors
   - Ensure patient exists in database

3. **Form Not Saving**
   - Check database permissions
   - Verify nonce is being passed correctly
   - Look for PHP errors in WordPress debug log

### Debug Mode
Enable WordPress debug mode in `wp-config.php`:
```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
```

## File Structure

```
noor-homeopathy-prescription/
├── noor-homeopathy-prescription.php    # Main plugin file
├── includes/
│   └── frontend-form.php               # Frontend form HTML/CSS/JS
├── README.md                           # This file
└── assets/                             # Future: Separate CSS/JS files
```

## Requirements

### Server Requirements
- PHP 7.4+
- MySQL 5.6+
- WordPress 5.0+

### Browser Requirements
- Modern browsers with JavaScript enabled
- HTML5 Canvas support for PDF generation
- LocalStorage support

## License

This plugin is licensed under the GPL v2 or later.

## Support

For support and questions:
1. Check the troubleshooting section
2. Review WordPress debug logs
3. Ensure all requirements are met

## Changelog

### Version 1.0.0
- Initial release
- Patient registration and lookup
- Prescription creation and management
- PDF generation with multiple methods
- Responsive design with modern UI
- Admin panel integration
- Security features and validation

## Future Enhancements

Planned features for future versions:
- Medicine template system
- Prescription templates
- Patient photo upload
- Advanced reporting
- Email prescription delivery
- Multi-doctor support
- Appointment scheduling integration

## Contributing

To contribute to this plugin:
1. Follow WordPress coding standards
2. Test thoroughly before submitting
3. Document any new features
4. Ensure security best practices

---

*This plugin was developed specifically for Noor Homeopathy Clinic by Dr. Isahaque Obaidi.*
