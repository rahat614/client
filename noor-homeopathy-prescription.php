<?php
/*
Plugin Name: Noor Homeopathy Prescription System
Description: A comprehensive prescription management system for homeopathy clinics with patient registration, prescription creation, and PDF generation capabilities.
Version: 1.0.0
Author: Noor Homeopathy
Text Domain: noor-prescription
Domain Path: /languages
License: GPL v2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('NOOR_PRESCRIPTION_VERSION', '1.0.0');
define('NOOR_PRESCRIPTION_PLUGIN_URL', plugin_dir_url(__FILE__));
define('NOOR_PRESCRIPTION_PLUGIN_PATH', plugin_dir_path(__FILE__));

// Plugin activation hook
register_activation_hook(__FILE__, 'noor_prescription_activate');

function noor_prescription_activate() {
    global $wpdb;
    
    $charset_collate = $wpdb->get_charset_collate();
    
    // Create patients table
    $table_patients = $wpdb->prefix . 'patients';
    $sql_patients = "CREATE TABLE $table_patients (
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
    ) $charset_collate;";
    
    // Create prescriptions table
    $table_prescriptions = $wpdb->prefix . 'prescriptions';
    $sql_prescriptions = "CREATE TABLE $table_prescriptions (
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
    ) $charset_collate;";
    
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql_patients);
    dbDelta($sql_prescriptions);
    
    // Create upload directory for PDF storage
    $upload_dir = wp_upload_dir();
    $noor_dir = $upload_dir['basedir'] . '/noor-prescriptions';
    if (!file_exists($noor_dir)) {
        wp_mkdir_p($noor_dir);
        // Add .htaccess for security
        file_put_contents($noor_dir . '/.htaccess', 'Options -Indexes');
    }
}

// Plugin deactivation hook
register_deactivation_hook(__FILE__, 'noor_prescription_deactivate');

function noor_prescription_deactivate() {
    // Clean up any scheduled events if needed
    wp_clear_scheduled_hook('noor_prescription_cleanup');
}

// Enqueue scripts and styles
add_action('wp_enqueue_scripts', 'noor_prescription_enqueue_scripts');

function noor_prescription_enqueue_scripts() {
    wp_enqueue_script('jspdf', 'https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js', array(), '2.5.1', true);
    wp_enqueue_script('html2canvas', 'https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js', array(), '1.4.1', true);
    wp_enqueue_style('google-fonts', 'https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap');
    
    // Add AJAX URL for frontend scripts
    wp_localize_script('jspdf', 'noor_ajax', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('noor_prescription_nonce')
    ));
}

// Add admin menu
add_action('admin_menu', 'noor_prescription_admin_menu');

function noor_prescription_admin_menu() {
    add_menu_page(
        'Noor Prescription System',
        'Prescriptions',
        'manage_options',
        'noor-prescriptions',
        'noor_prescription_admin_page',
        'dashicons-clipboard',
        30
    );
    
    add_submenu_page(
        'noor-prescriptions',
        'All Prescriptions',
        'All Prescriptions',
        'manage_options',
        'noor-prescriptions',
        'noor_prescription_admin_page'
    );
    
    add_submenu_page(
        'noor-prescriptions',
        'Patients',
        'Patients',
        'manage_options',
        'noor-patients',
        'noor_patients_admin_page'
    );
}

// Admin page for prescriptions
function noor_prescription_admin_page() {
    global $wpdb;
    
    $table_prescriptions = $wpdb->prefix . 'prescriptions';
    $prescriptions = $wpdb->get_results("SELECT * FROM $table_prescriptions ORDER BY created_at DESC", ARRAY_A);
    
    echo '<div class="wrap">';
    echo '<h1>Prescription Management</h1>';
    echo '<table class="wp-list-table widefat fixed striped">';
    echo '<thead><tr><th>ID</th><th>Patient Name</th><th>Patient ID</th><th>Date</th><th>Actions</th></tr></thead>';
    echo '<tbody>';
    
    foreach ($prescriptions as $prescription) {
        echo '<tr>';
        echo '<td>' . esc_html($prescription['id']) . '</td>';
        echo '<td>' . esc_html($prescription['patient_name']) . '</td>';
        echo '<td>' . esc_html($prescription['patient_id']) . '</td>';
        echo '<td>' . esc_html($prescription['prescription_date']) . '</td>';
        echo '<td><a href="#" class="button">View</a> <a href="#" class="button button-primary">Edit</a></td>';
        echo '</tr>';
    }
    
    echo '</tbody></table>';
    echo '</div>';
}

// Admin page for patients
function noor_patients_admin_page() {
    global $wpdb;
    
    $table_patients = $wpdb->prefix . 'patients';
    $patients = $wpdb->get_results("SELECT * FROM $table_patients ORDER BY created_at DESC", ARRAY_A);
    
    echo '<div class="wrap">';
    echo '<h1>Patient Management</h1>';
    echo '<table class="wp-list-table widefat fixed striped">';
    echo '<thead><tr><th>ID</th><th>Patient Name</th><th>Patient ID</th><th>Phone</th><th>Age</th><th>Actions</th></tr></thead>';
    echo '<tbody>';
    
    foreach ($patients as $patient) {
        echo '<tr>';
        echo '<td>' . esc_html($patient['id']) . '</td>';
        echo '<td>' . esc_html($patient['patient_name']) . '</td>';
        echo '<td>' . esc_html($patient['patient_id']) . '</td>';
        echo '<td>' . esc_html($patient['phone']) . '</td>';
        echo '<td>' . esc_html($patient['age']) . '</td>';
        echo '<td><a href="#" class="button">View</a> <a href="#" class="button button-primary">Edit</a></td>';
        echo '</tr>';
    }
    
    echo '</tbody></table>';
    echo '</div>';
}

// Add shortcode
add_shortcode('noor_prescription', 'noor_prescription_shortcode');

function noor_prescription_shortcode($atts) {
    // Start output buffering
    ob_start();
    
    // Handle form submissions
    $message = "";
    $message_type = "";
    
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && wp_verify_nonce($_POST['nonce'], 'noor_prescription_nonce')) {
        global $wpdb;
        
        if ($_POST['action'] == 'register_patient') {
            $patient_id = sanitize_text_field($_POST['patient_id']);
            $date = sanitize_text_field($_POST['date']);
            $patient_name = sanitize_text_field($_POST['patient_name']);
            $gender = sanitize_text_field($_POST['gender']);
            $age = intval($_POST['age']);
            $blood_group = sanitize_text_field($_POST['blood_group']);
            $address = sanitize_textarea_field($_POST['address']);
            $phone = sanitize_text_field($_POST['phone']);
            
            $table_patients = $wpdb->prefix . 'patients';
            
            // Check for duplicate patient ID
            $existing = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM $table_patients WHERE patient_id = %s",
                $patient_id
            ));
            
            if ($existing > 0) {
                $message = "Error: Patient ID already exists!";
                $message_type = "error";
            } else {
                $result = $wpdb->insert(
                    $table_patients,
                    array(
                        'patient_id' => $patient_id,
                        'date' => $date,
                        'patient_name' => $patient_name,
                        'gender' => $gender,
                        'age' => $age,
                        'blood_group' => $blood_group,
                        'address' => $address,
                        'phone' => $phone
                    ),
                    array('%s', '%s', '%s', '%s', '%d', '%s', '%s', '%s')
                );
                
                if ($result !== false) {
                    $message = "Patient registered successfully!";
                    $message_type = "success";
                } else {
                    $message = "Error: " . $wpdb->last_error;
                    $message_type = "error";
                }
            }
        }
    }
    
    // Include the frontend HTML and JavaScript
    include_once NOOR_PRESCRIPTION_PLUGIN_PATH . 'includes/frontend-form.php';
    
    // Return the buffered content
    return ob_get_clean();
}

// AJAX handlers for patient lookup
add_action('wp_ajax_get_patient', 'noor_get_patient_ajax');
add_action('wp_ajax_nopriv_get_patient', 'noor_get_patient_ajax');

function noor_get_patient_ajax() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], 'noor_prescription_nonce')) {
        wp_send_json_error('Security check failed');
        return;
    }
    
    global $wpdb;
    
    $patient_id = sanitize_text_field($_POST['patient_id']);
    $table_patients = $wpdb->prefix . 'patients';
    
    $patient = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $table_patients WHERE patient_id = %s",
        $patient_id
    ), ARRAY_A);
    
    if ($patient) {
        wp_send_json_success($patient);
    } else {
        wp_send_json_error('Patient not found');
    }
}

// AJAX handler for saving prescriptions
add_action('wp_ajax_save_prescription_ajax', 'noor_save_prescription_ajax');
add_action('wp_ajax_nopriv_save_prescription_ajax', 'noor_save_prescription_ajax');

function noor_save_prescription_ajax() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], 'noor_prescription_nonce')) {
        wp_send_json_error('Security check failed');
        return;
    }
    
    global $wpdb;
    
    $patient_id = sanitize_text_field($_POST['patient_id']);
    $prescription_date = sanitize_text_field($_POST['prescription_date']);
    $patient_name = sanitize_text_field($_POST['patient_name']);
    $gender = sanitize_text_field($_POST['gender']);
    $age = intval($_POST['age']);
    $phone = sanitize_text_field($_POST['phone']);
    $disease_info = sanitize_textarea_field($_POST['disease_info']);
    $medicines = sanitize_textarea_field($_POST['medicines']);
    $instructions = sanitize_textarea_field($_POST['instructions']);
    
    $table_prescriptions = $wpdb->prefix . 'prescriptions';
    
    $result = $wpdb->insert(
        $table_prescriptions,
        array(
            'patient_id' => $patient_id,
            'prescription_date' => $prescription_date,
            'patient_name' => $patient_name,
            'gender' => $gender,
            'age' => $age,
            'phone' => $phone,
            'disease_info' => $disease_info,
            'medicines' => $medicines,
            'instructions' => $instructions
        ),
        array('%s', '%s', '%s', '%s', '%d', '%s', '%s', '%s', '%s')
    );
    
    if ($result !== false) {
        wp_send_json_success('Prescription saved successfully!');
    } else {
        wp_send_json_error('Error: ' . $wpdb->last_error);
    }
}

// AJAX handler for deleting prescriptions
add_action('wp_ajax_delete_prescription_ajax', 'noor_delete_prescription_ajax');
add_action('wp_ajax_nopriv_delete_prescription_ajax', 'noor_delete_prescription_ajax');

function noor_delete_prescription_ajax() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], 'noor_prescription_nonce')) {
        wp_send_json_error('Security check failed');
        return;
    }
    
    global $wpdb;
    
    $prescription_id = intval($_POST['id']);
    
    $table_prescriptions = $wpdb->prefix . 'prescriptions';
    
    $result = $wpdb->delete(
        $table_prescriptions,
        array('id' => $prescription_id),
        array('%d')
    );
    
    if ($result !== false) {
        wp_send_json_success('Prescription deleted successfully!');
    } else {
        wp_send_json_error('Error deleting prescription: ' . $wpdb->last_error);
    }
}

// AJAX handler for getting all prescriptions
add_action('wp_ajax_get_all_prescriptions', 'noor_get_all_prescriptions_ajax');
add_action('wp_ajax_nopriv_get_all_prescriptions', 'noor_get_all_prescriptions_ajax');

function noor_get_all_prescriptions_ajax() {
    global $wpdb;
    
    $table_prescriptions = $wpdb->prefix . 'prescriptions';
    $prescriptions = $wpdb->get_results(
        "SELECT * FROM $table_prescriptions ORDER BY created_at DESC LIMIT 50",
        ARRAY_A
    );
    
    if ($prescriptions) {
        wp_send_json_success($prescriptions);
    } else {
        wp_send_json_success(array()); // Return empty array if no prescriptions
    }
}

// Add security headers
add_action('send_headers', 'noor_prescription_security_headers');

function noor_prescription_security_headers() {
    if (is_admin() || strpos($_SERVER['REQUEST_URI'], 'noor-prescription') !== false) {
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: SAMEORIGIN');
        header('X-XSS-Protection: 1; mode=block');
    }
}

// Add custom CSS for admin pages
add_action('admin_head', 'noor_prescription_admin_styles');

function noor_prescription_admin_styles() {
    $current_screen = get_current_screen();
    if (strpos($current_screen->id, 'noor-') !== false) {
        ?>
        <style>
            .noor-admin-header {
                background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
                color: white;
                padding: 20px;
                border-radius: 8px;
                margin-bottom: 20px;
            }
            .noor-admin-header h1 {
                color: white;
                margin: 0;
            }
            .noor-stats {
                display: flex;
                gap: 20px;
                margin-bottom: 20px;
            }
            .noor-stat-card {
                background: white;
                padding: 20px;
                border-radius: 8px;
                box-shadow: 0 2px 4px rgba(0,0,0,0.1);
                flex: 1;
                text-align: center;
            }
            .noor-stat-number {
                font-size: 2em;
                font-weight: bold;
                color: #1e40af;
            }
        </style>
        <?php
    }
}

// Create includes directory structure on activation
register_activation_hook(__FILE__, 'noor_create_includes_directory');

function noor_create_includes_directory() {
    $includes_dir = NOOR_PRESCRIPTION_PLUGIN_PATH . 'includes';
    if (!file_exists($includes_dir)) {
        wp_mkdir_p($includes_dir);
    }
}
?>