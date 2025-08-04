<?php
// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Display any messages
if (!empty($message)) {
    $alert_class = $message_type === 'success' ? 'bg-green-100 border-green-400 text-green-700' : 'bg-red-100 border-red-400 text-red-700';
    echo '<div class="' . $alert_class . ' border px-4 py-3 rounded mb-4">' . esc_html($message) . '</div>';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Noor Homeopathy Prescription</title>
    <!-- Load Tailwind CSS from CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <!-- Include jsPDF and html2canvas for PDF generation -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script>
        // Ensure libraries are loaded
        window.addEventListener('load', function() {
            if (typeof html2canvas === 'undefined') {
                console.error('html2canvas not loaded');
            }
            if (typeof jspdf === 'undefined') {
                console.error('jsPDF not loaded');
            }
        });
    </script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        input, textarea {
            border-color: #d1d5db; /* Default gray border */
            transition: border-color 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
        }
        input:focus, textarea:focus {
            outline: none;
            border-color: #3b82f6; /* Blue border on focus */
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
        }
        input[type="date"]::-webkit-calendar-picker-indicator {
            cursor: pointer;
            filter: invert(0.5) sepia(1) saturate(5) hue-rotate(180deg) brightness(0.9);
        }
        
        /* Enhanced container styling */
        #main-container {
            background: linear-gradient(145deg, #ffffff 0%, #f8fafc 100%);
            border: 1px solid rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            box-shadow: 
                0 20px 40px rgba(0, 0, 0, 0.1),
                0 0 0 1px rgba(255, 255, 255, 0.2);
        }

        /* Beautiful header styling */
        .header-section {
            background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
            color: white;
            padding: 2rem;
            border-radius: 1rem;
            margin-bottom: 2rem;
            text-align: center;
            box-shadow: 0 10px 30px rgba(30, 64, 175, 0.3);
        }

        .header-section h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin: 0;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
        }

        .header-section p {
            margin: 0.5rem 0 0 0;
            opacity: 0.9;
            font-size: 1.1rem;
        }

        /* Enhanced form styling */
        .form-section {
            background: white;
            padding: 2rem;
            border-radius: 1rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
        }

        .form-section h2 {
            color: #1e40af;
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
            border-bottom: 2px solid #e5e7eb;
            padding-bottom: 0.5rem;
        }

        /* Beautiful input styling */
        .form-input {
            background: #f8fafc;
            border: 2px solid #e5e7eb;
            border-radius: 0.75rem;
            padding: 0.875rem 1rem;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-input:focus {
            background: white;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
            transform: translateY(-1px);
        }

        /* Enhanced button styling */
        .btn-primary {
            background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%);
            color: white;
            font-weight: 600;
            padding: 0.875rem 2rem;
            border-radius: 0.75rem;
            border: none;
            box-shadow: 0 4px 15px rgba(59, 130, 246, 0.3);
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(59, 130, 246, 0.4);
        }

        .btn-secondary {
            background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%);
            color: white;
            font-weight: 600;
            padding: 0.875rem 2rem;
            border-radius: 0.75rem;
            border: none;
            box-shadow: 0 4px 15px rgba(107, 114, 128, 0.3);
            transition: all 0.3s ease;
        }

        .btn-secondary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(107, 114, 128, 0.4);
        }

        .btn-success {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            font-weight: 600;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            border: none;
            box-shadow: 0 2px 10px rgba(16, 185, 129, 0.3);
            transition: all 0.3s ease;
        }

        .btn-success:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 15px rgba(16, 185, 129, 0.4);
        }
        
        /* Custom styles for the 3D-style Print button */
        .btn-print-shiny {
            background: linear-gradient(to bottom, #4fc0d7 0%, #1c90b0 100%);
            border: 2px solid #0f6c8d;
            border-radius: 9999px;
            color: white;
            font-weight: 700;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.3);
            box-shadow: 
                0 0 10px rgba(0, 0, 0, 0.2),
                inset 0 1px 0 rgba(255, 255, 255, 0.5),
                inset 0 -1px 0 rgba(0, 0, 0, 0.2);
            transition: all 0.2s ease-in-out;
            padding: 0.75rem 1.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }
        .btn-print-shiny:hover {
            transform: translateY(-1px);
            box-shadow: 
                0 4px 15px rgba(0, 0, 0, 0.3),
                inset 0 1px 0 rgba(255, 255, 255, 0.6),
                inset 0 -1px 0 rgba(0, 0, 0, 0.3);
        }

        /* Custom styles for the 3D Delete button */
        .btn-delete-3d {
            background: linear-gradient(to bottom, #ef4444 0%, #dc2626 100%);
            border: 2px solid #b91c1c;
            border-radius: 9999px;
            color: white;
            font-weight: 700;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.3);
            box-shadow: 
                0 0 10px rgba(239, 68, 68, 0.3),
                inset 0 1px 0 rgba(255, 255, 255, 0.5),
                inset 0 -1px 0 rgba(0, 0, 0, 0.2);
            transition: all 0.2s ease-in-out;
            padding: 0.5rem 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.25rem;
            font-size: 0.875rem;
        }
        .btn-delete-3d:hover {
            transform: translateY(-1px);
            box-shadow: 
                0 4px 15px rgba(239, 68, 68, 0.4),
                inset 0 1px 0 rgba(255, 255, 255, 0.6),
                inset 0 -1px 0 rgba(0, 0, 0, 0.3);
            background: linear-gradient(to bottom, #f87171 0%, #ef4444 100%);
        }

        /* Custom styles for the Add Medicine button */
        .btn-add-medicine {
            background-color: #10b981; /* Green color */
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            font-weight: 600;
            transition: background-color 0.2s, transform 0.2s;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        .btn-add-medicine:hover {
            background-color: #059669;
            transform: scale(1.05);
        }

        /* Responsive table-like grid */
        .medicine-row {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1rem;
            align-items: center;
        }
        @media (max-width: 640px) {
            .medicine-row {
                grid-template-columns: 1fr;
            }
        }
        
        /* Modal styling */
        .modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 1000;
        }
        .modal-content {
            background-color: white;
            padding: 2rem;
            border-radius: 1rem;
            max-width: 600px;
            width: 90%;
            max-height: 90%;
            overflow-y: auto;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        }

        /* Disease information styling */
        .disease-section {
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            border: 1px solid #f59e0b;
            border-radius: 0.75rem;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .disease-section h3 {
            color: #92400e;
            font-weight: 600;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body class="p-4 md:p-8 flex items-center justify-center min-h-screen">
    
    <!-- Main content container to toggle between form and table -->
    <div id="main-container" class="bg-white p-8 md:p-12 rounded-2xl shadow-2xl max-w-full lg:max-w-4xl">

        <!-- Form View Section -->
        <div id="form-view">
            <!-- Header Section -->
            <div class="header-section">
                <h1>Noor Homeopathy Prescription</h1>
                <p>Dr. Isahaque Obaidi - B.H.M.S (DU), D.M.U (Ultrasonography)</p>
                <p>Mirpur Tulaputti, Kushtia</p>
            </div>

            <!-- Form container -->
            <form id="prescription-form" class="space-y-6" method="post">
                <?php wp_nonce_field('noor_prescription_nonce', 'nonce'); ?>

                <!-- Patient Information Section -->
                <div class="form-section">
                    <h2>Patient Information</h2>
                    
                    <!-- Patient ID and Date -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label for="patient-id" class="block text-gray-700 text-sm font-medium mb-2">Patient ID</label>
                            <input type="text" id="patient-id" name="patient-id" placeholder="Enter Patient ID to auto-fill details" class="form-input w-full">
                            <button type="button" id="lookup-patient-btn" class="btn-success mt-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 inline mr-1" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                                </svg>
                                Lookup Patient
                            </button>
                        </div>
                        <div>
                            <label for="date" class="block text-gray-700 text-sm font-medium mb-2">Date</label>
                            <input type="date" id="date" name="date" class="form-input w-full">
                        </div>
                    </div>

                    <!-- Patient Name and Gender -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label for="patient-name" class="block text-gray-700 text-sm font-medium mb-2">Patient Name</label>
                            <input type="text" id="patient-name" name="patient-name" placeholder="Patient's full name" class="form-input w-full">
                        </div>
                        <div>
                            <label for="gender" class="block text-gray-700 text-sm font-medium mb-2">Gender</label>
                            <input type="text" id="gender" name="gender" placeholder="Male/Female/Other" class="form-input w-full">
                        </div>
                    </div>

                    <!-- Age and Phone -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label for="age" class="block text-gray-700 text-sm font-medium mb-2">Age</label>
                            <input type="number" id="age" name="age" placeholder="Age in years" class="form-input w-full">
                        </div>
                        <div>
                            <label for="phone" class="block text-gray-700 text-sm font-medium mb-2">Phone Number (11 digits)</label>
                            <input type="tel" id="phone" name="phone" placeholder="01XXXXXXXXX" pattern="[0-9]{11}" maxlength="11" class="form-input w-full">
                        </div>
                    </div>
                </div>

                <!-- Disease Information Section -->
                <div class="form-section">
                    <h2>Disease Information</h2>
                    <div class="disease-section">
                        <h3>Patient Symptoms & Diagnosis</h3>
                        <textarea id="disease-info" name="disease-info" rows="4" placeholder="Describe the patient's symptoms, diagnosis, and any relevant medical history..." class="form-input w-full resize-none"></textarea>
                    </div>
                </div>

                <!-- Medicine Table Section -->
                <div class="form-section">
                    <h2>Prescribed Medicines</h2>
                    
                    <!-- Table Header -->
                    <div class="hidden sm:grid medicine-row text-gray-600 font-semibold border-b border-gray-300 pb-2 mb-2">
                        <div>Medicine Name</div>
                        <div>Potency</div>
                        <div>Instructions</div>
                        <div>Actions</div>
                    </div>

                    <!-- Container for dynamic medicine rows -->
                    <div id="medicines-container" class="space-y-4">
                        <!-- Medicine rows will be added here by JavaScript -->
                    </div>

                    <div class="mt-4 text-center">
                        <button type="button" id="add-medicine-btn" class="btn-add-medicine inline-flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                              <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
                            </svg>
                            Add Medicine
                        </button>
                    </div>
                </div>
                
                <!-- Buttons container -->
                <div class="pt-4 flex flex-col sm:flex-row gap-4 justify-center">
                    <button type="button" id="save-prescription-btn" class="btn-primary">
                        Save Prescription
                    </button>
                    <button type="button" id="view-all-prescriptions-btn" class="btn-secondary">
                        View All Prescriptions
                    </button>
                </div>
            </form>
        </div>
        
        <!-- Table View Section (initially hidden) -->
        <div id="table-view" class="hidden">
            <header class="text-center mb-10">
                <h1 class="text-3xl font-bold text-blue-700 tracking-tight">All Prescriptions</h1>
            </header>
            
            <div id="prescriptions-table-container">
                <!-- Table will be rendered here by JavaScript -->
            </div>

            <div class="mt-8 text-center">
                <button type="button" id="back-to-form-btn" class="bg-blue-600 text-white font-semibold py-3 px-6 rounded-lg shadow-lg hover:bg-blue-700 transition duration-300 ease-in-out transform hover:scale-105">
                    Back to Form
                </button>
            </div>
        </div>

    </div>

    <!-- Modal for displaying prescription details -->
    <div id="details-modal" class="modal">
        <div class="modal-content">
            <div class="flex justify-between items-center border-b pb-2 mb-4">
                <h3 id="modal-title" class="text-xl font-bold text-gray-800">Prescription Details</h3>
                <button id="close-modal-btn" class="text-gray-500 hover:text-gray-700 text-3xl font-bold leading-none">
                    &times;
                </button>
            </div>
            <div id="modal-body" class="text-gray-700">
                <!-- Details will be rendered here -->
            </div>
        </div>
    </div>
    
    <!-- Hidden element for PDF generation -->
    <div id="pdf-content-to-print" class="hidden p-8 w-full"></div>

    <!-- JavaScript to handle dynamic rows and data persistence -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // DOM element references
            const container = document.getElementById('medicines-container');
            const addBtn = document.getElementById('add-medicine-btn');
            const form = document.getElementById('prescription-form');
            const saveBtn = document.getElementById('save-prescription-btn');
            const viewAllBtn = document.getElementById('view-all-prescriptions-btn');
            const backToFormBtn = document.getElementById('back-to-form-btn');
            const formView = document.getElementById('form-view');
            const tableView = document.getElementById('table-view');
            const prescriptionsTableContainer = document.getElementById('prescriptions-table-container');
            
            const detailsModal = document.getElementById('details-modal');
            const modalBody = document.getElementById('modal-body');
            const closeModalBtn = document.getElementById('close-modal-btn');
            const pdfContentToPrint = document.getElementById('pdf-content-to-print');

            // Get AJAX URL and nonce from WordPress
            const ajaxUrl = typeof noor_ajax !== 'undefined' ? noor_ajax.ajax_url : '<?php echo admin_url('admin-ajax.php'); ?>';
            const nonce = typeof noor_ajax !== 'undefined' ? noor_ajax.nonce : '<?php echo wp_create_nonce('noor_prescription_nonce'); ?>';

            // --- Modal Functions ---
            function openModal(content) {
                modalBody.innerHTML = content;
                detailsModal.style.display = 'flex';
            }

            function closeModal() {
                detailsModal.style.display = 'none';
                modalBody.innerHTML = '';
            }

            closeModalBtn.addEventListener('click', closeModal);
            detailsModal.addEventListener('click', (e) => {
                if (e.target === detailsModal) {
                    closeModal();
                }
            });

            // --- View Toggling ---
            function toggleView(viewId) {
                if (viewId === 'form') {
                    formView.classList.remove('hidden');
                    tableView.classList.add('hidden');
                } else {
                    formView.classList.add('hidden');
                    tableView.classList.remove('hidden');
                    renderPrescriptionsTable();
                }
            }

            // --- Dynamic Row Functions ---
            function addMedicineRow(medicine = {}) {
                const row = document.createElement('div');
                row.className = 'medicine-row items-center';
                row.innerHTML = `
                    <input type="text" name="medicine-name[]" placeholder="Medicine Name" value="${medicine.medicineName || ''}" class="w-full px-4 py-2 border rounded-lg text-gray-800 focus:ring-2 focus:ring-blue-500">
                    <input type="text" name="potency[]" placeholder="Potency" value="${medicine.potency || ''}" class="w-full px-4 py-2 border rounded-lg text-gray-800 focus:ring-2 focus:ring-blue-500">
                    <input type="text" name="instructions[]" placeholder="Instructions" value="${medicine.instructions || ''}" class="w-full px-4 py-2 border rounded-lg text-gray-800 focus:ring-2 focus:ring-blue-500">
                    <div class="flex items-center justify-center">
                        <button type="button" class="bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded-lg transition-colors remove-btn">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mx-auto" viewBox="0 0 20 20" fill="currentColor">
                              <path fill-rule="evenodd" d="M9 2a1 1 0 00-1 1v1H5a1 1 0 000 2h1v8a2 2 0 002 2h4a2 2 0 002-2V6h1a1 1 0 100-2h-3V3a1 1 0 00-1-1H9zm1 2H9v1h2V4zm0 2h-2v8h2V6z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </div>
                `;
                
                const removeButton = row.querySelector('.remove-btn');
                removeButton.addEventListener('click', () => {
                    row.remove();
                });

                container.appendChild(row);
            }

            // Add an initial row on page load
            addMedicineRow();

            // Event listener for the "Add Medicine" button
            addBtn.addEventListener('click', () => addMedicineRow());

            // --- Data Persistence and Rendering ---
            function getPrescriptions() {
                const prescriptions = localStorage.getItem('prescriptions');
                return prescriptions ? JSON.parse(prescriptions) : [];
            }

            function savePrescription(e) {
                e.preventDefault();

                const patientId = document.getElementById('patient-id').value.trim();
                const date = document.getElementById('date').value.trim();
                const patientName = document.getElementById('patient-name').value.trim();
                const gender = document.getElementById('gender').value.trim();
                const age = document.getElementById('age').value.trim();
                const phone = document.getElementById('phone').value.trim();
                const diseaseInfo = document.getElementById('disease-info').value.trim();
                
                const medicines = [];
                const rows = container.querySelectorAll('.medicine-row');
                rows.forEach(row => {
                    const medicineName = row.querySelector('input[name="medicine-name[]"]').value.trim();
                    const potency = row.querySelector('input[name="potency[]"]').value.trim();
                    const instructions = row.querySelector('input[name="instructions[]"]').value.trim();
                    if (medicineName) {
                        medicines.push({ medicineName, potency, instructions });
                    }
                });

                if (!patientName || medicines.length === 0) {
                    alert('Please enter a patient name and at least one medicine to save.');
                    return;
                }

                // Validate phone number (11 digits)
                if (phone && phone.length !== 11) {
                    alert('Phone number must be exactly 11 digits.');
                    return;
                }

                // Create form data for AJAX submission
                const formData = new FormData();
                formData.append('action', 'save_prescription_ajax');
                formData.append('nonce', nonce);
                formData.append('patient_id', patientId);
                formData.append('prescription_date', date);
                formData.append('patient_name', patientName);
                formData.append('gender', gender);
                formData.append('age', age);
                formData.append('phone', phone);
                formData.append('disease_info', diseaseInfo);
                formData.append('medicines', JSON.stringify(medicines));
                formData.append('instructions', '');

                // Submit to database via AJAX
                fetch(ajaxUrl, {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Prescription saved successfully to database!');
                        form.reset();
                        container.innerHTML = '';
                        addMedicineRow();
                        // Set current date
                        const today = new Date().toISOString().split('T')[0];
                        document.getElementById('date').value = today;
                    } else {
                        alert('Error saving prescription: ' + data.data);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error saving prescription. Please try again.');
                });

                // Also save to localStorage for immediate display
                const newPrescription = {
                    id: Date.now(),
                    patientId,
                    date,
                    patientName,
                    gender,
                    age,
                    phone,
                    diseaseInfo,
                    medicines
                };

                const allPrescriptions = getPrescriptions();
                allPrescriptions.push(newPrescription);
                localStorage.setItem('prescriptions', JSON.stringify(allPrescriptions));
            }

            function renderPrescriptionsTable() {
                // Fetch prescriptions from database
                fetch(ajaxUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'action=get_all_prescriptions&nonce=' + nonce
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const allPrescriptions = data.data;
                        let tableHtml = '';

                        if (allPrescriptions.length === 0) {
                            tableHtml = '<p class="text-center text-gray-500">No prescriptions found.</p>';
                        } else {
                            tableHtml = `
                                <div class="overflow-x-auto">
                                    <table class="min-w-full bg-white rounded-lg shadow-md">
                                        <thead class="bg-gray-200">
                                            <tr class="text-left text-gray-600 uppercase text-sm leading-normal">
                                                <th class="py-3 px-6 text-left">Patient ID</th>
                                                <th class="py-3 px-6 text-left">Patient Name</th>
                                                <th class="py-3 px-6 text-left">Gender</th>
                                                <th class="py-3 px-6 text-left">Age</th>
                                                <th class="py-3 px-6 text-left">Phone Number</th>
                                                <th class="py-3 px-6 text-center">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody class="text-gray-600 text-sm font-light">
                                            ${allPrescriptions.map(p => `
                                                <tr class="border-b border-gray-200 hover:bg-gray-100">
                                                    <td class="py-3 px-6 text-left whitespace-nowrap">${p.patient_id}</td>
                                                    <td class="py-3 px-6 text-left whitespace-nowrap">${p.patient_name}</td>
                                                    <td class="py-3 px-6 text-left whitespace-nowrap">${p.gender}</td>
                                                    <td class="py-3 px-6 text-left whitespace-nowrap">${p.age}</td>
                                                    <td class="py-3 px-6 text-left whitespace-nowrap">${p.phone}</td>
                                                    <td class="py-3 px-6 text-center space-x-2">
                                                        <button data-id="${p.id}" class="view-details-btn bg-blue-500 hover:bg-blue-600 text-white font-bold py-1 px-3 rounded-lg transition-colors">
                                                            View
                                                        </button>
                                                        <button data-id="${p.id}" class="generate-pdf-btn bg-red-500 hover:bg-red-600 text-white font-bold py-1 px-3 rounded-lg transition-colors">
                                                            PDF
                                                        </button>
                                                        <button data-id="${p.id}" class="btn-delete-3d delete-prescription-btn">
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                                                <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                                            </svg>
                                                            Delete
                                                        </button>
                                                    </td>
                                                </tr>
                                            `).join('')}
                                        </tbody>
                                    </table>
                                </div>
                            `;
                        }

                        prescriptionsTableContainer.innerHTML = tableHtml;
                        
                        // Add event listeners to the action buttons
                        document.querySelectorAll('.view-details-btn').forEach(button => {
                            button.addEventListener('click', (e) => {
                                const prescriptionId = parseInt(e.target.dataset.id);
                                const prescription = allPrescriptions.find(p => p.id === prescriptionId);
                                if (prescription) {
                                    showPrescriptionDetails(prescription);
                                }
                            });
                        });

                        document.querySelectorAll('.generate-pdf-btn').forEach(button => {
                            button.addEventListener('click', (e) => {
                                const prescriptionId = parseInt(e.target.dataset.id);
                                const prescription = allPrescriptions.find(p => p.id === prescriptionId);
                                if (prescription) {
                                    try {
                                        generatePrescriptionPdf(prescription);
                                    } catch (error) {
                                        console.error('Main PDF generation failed:', error);
                                        generateSimplePdf(prescription);
                                    }
                                }
                            });
                        });

                        document.querySelectorAll('.delete-prescription-btn').forEach(button => {
                            button.addEventListener('click', (e) => {
                                const prescriptionId = parseInt(e.target.dataset.id);
                                if (confirm('Are you sure you want to delete this prescription?')) {
                                    // Delete from database via AJAX
                                    fetch(ajaxUrl, {
                                        method: 'POST',
                                        headers: {
                                            'Content-Type': 'application/x-www-form-urlencoded',
                                        },
                                        body: 'action=delete_prescription_ajax&nonce=' + nonce + '&id=' + prescriptionId
                                    })
                                    .then(response => response.json())
                                    .then(data => {
                                        if (data.success) {
                                            alert('Prescription deleted successfully!');
                                            renderPrescriptionsTable(); // Re-render table to show updated list
                                        } else {
                                            alert('Error deleting prescription: ' + data.data);
                                        }
                                    })
                                    .catch(error => {
                                        console.error('Error deleting prescription:', error);
                                        alert('Error deleting prescription. Please try again.');
                                    });
                                }
                            });
                        });
                    } else {
                        prescriptionsTableContainer.innerHTML = '<p class="text-center text-gray-500">Error loading prescriptions.</p>';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    prescriptionsTableContainer.innerHTML = '<p class="text-center text-red-500">Error loading prescriptions.</p>';
                });
            }

            function showPrescriptionDetails(prescription) {
                let medicineListHtml = '';
                try {
                    const medicines = typeof prescription.medicines === 'string' ? 
                        JSON.parse(prescription.medicines) : prescription.medicines;
                    
                    if (Array.isArray(medicines)) {
                        medicineListHtml = medicines.map(m => `
                            <div class="mb-4 p-4 bg-gray-100 rounded-lg">
                                <h4 class="font-semibold text-lg text-gray-800">${m.medicineName} - ${m.potency}</h4>
                                <p class="text-sm">${m.instructions}</p>
                            </div>
                        `).join('');
                    } else {
                        medicineListHtml = '<p class="text-gray-500">No medicines listed</p>';
                    }
                } catch (error) {
                    medicineListHtml = '<p class="text-red-500">Error parsing medicines data</p>';
                }

                const detailsHtml = `
                    <h4 class="text-xl font-bold mb-4">${prescription.patient_name}</h4>
                    <p><strong>Patient ID:</strong> ${prescription.patient_id}</p>
                    <p><strong>Date:</strong> ${prescription.prescription_date}</p>
                    <p><strong>Gender:</strong> ${prescription.gender}</p>
                    <p><strong>Age:</strong> ${prescription.age}</p>
                    <p><strong>Phone:</strong> ${prescription.phone}</p>
                    ${prescription.disease_info ? `<p><strong>Disease Info:</strong> ${prescription.disease_info}</p>` : ''}
                    <h5 class="text-lg font-bold mt-6 mb-2">Medicines:</h5>
                    ${medicineListHtml}
                `;

                openModal(detailsHtml);
            }

            function generatePrescriptionPdf(prescription) {
                // Check if libraries are loaded
                if (typeof html2canvas === 'undefined') {
                    alert('PDF generation library not loaded. Please refresh the page and try again.');
                    return;
                }
                
                if (typeof jspdf === 'undefined') {
                    alert('PDF library not loaded. Please refresh the page and try again.');
                    return;
                }

                // Show loading message
                alert('Generating PDF... Please wait.');

                // Parse medicines data
                let medicineListHtml = '';
                try {
                    const medicines = typeof prescription.medicines === 'string' ? 
                        JSON.parse(prescription.medicines) : prescription.medicines;
                    
                    if (Array.isArray(medicines)) {
                        medicineListHtml = medicines.map(m => `
                            <div style="margin-bottom: 1rem; padding: 1rem; background-color: #f3f4f6; border-radius: 0.5rem;">
                                <h4 style="font-weight: 600; font-size: 1.125rem; color: #1f2937;">${m.medicineName} - ${m.potency}</h4>
                                <p style="font-size: 0.875rem; color: #4b5563;">${m.instructions}</p>
                            </div>
                        `).join('');
                    }
                } catch (error) {
                    medicineListHtml = '<p>Error parsing medicines data</p>';
                }

                // Prepare content for PDF
                pdfContentToPrint.innerHTML = `
                    <div style="font-family: 'Inter', sans-serif; padding: 2rem; background: white;">
                        <div style="text-align: center; margin-bottom: 2rem; border-bottom: 2px solid #1e40af; padding-bottom: 1rem;">
                            <h1 style="font-size: 1.875rem; font-weight: 700; color: #1e40af; margin: 0;">Noor Homeopathy Prescription</h1>
                            <p style="color: #6b7280; margin: 0.5rem 0 0 0;">Dr. Isahaque Obaidi - B.H.M.S (DU), D.M.U (Ultrasonography)</p>
                            <p style="color: #6b7280; margin: 0.25rem 0 0 0;">Mirpur Tulaputti, Kushtia</p>
                        </div>
                        <div style="border: 1px solid #d1d5db; border-radius: 0.5rem; padding: 1.5rem; margin-bottom: 2rem; background: #f9fafb;">
                            <h4 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 1rem; color: #1f2937;">Patient Information</h4>
                            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem;">
                                <p style="margin: 0.5rem 0;"><strong>Patient Name:</strong> ${prescription.patient_name}</p>
                                <p style="margin: 0.5rem 0;"><strong>Patient ID:</strong> ${prescription.patient_id}</p>
                                <p style="margin: 0.5rem 0;"><strong>Date:</strong> ${prescription.prescription_date}</p>
                                <p style="margin: 0.5rem 0;"><strong>Gender:</strong> ${prescription.gender}</p>
                                <p style="margin: 0.5rem 0;"><strong>Age:</strong> ${prescription.age}</p>
                                <p style="margin: 0.5rem 0;"><strong>Phone:</strong> ${prescription.phone}</p>
                            </div>
                        </div>
                        ${prescription.disease_info ? `
                            <div style="border: 1px solid #f59e0b; border-radius: 0.5rem; padding: 1.5rem; margin-bottom: 2rem; background: #fef3c7;">
                                <h4 style="font-size: 1.125rem; font-weight: 700; margin-bottom: 1rem; color: #92400e;">Disease Information</h4>
                                <p style="color: #92400e;">${prescription.disease_info}</p>
                            </div>
                        ` : ''}
                        <div style="margin-bottom: 2rem;">
                            <h5 style="font-size: 1.125rem; font-weight: 700; margin-bottom: 1rem; color: #1f2937;">Prescribed Medicines:</h5>
                            ${medicineListHtml}
                        </div>
                        <div style="margin-top: 3rem; text-align: center; border-top: 1px solid #d1d5db; padding-top: 2rem;">
                            <p style="margin: 0; color: #6b7280;">_________________________</p>
                            <p style="margin: 0.5rem 0 0 0; font-weight: 600; color: #1f2937;">Doctor's Signature</p>
                        </div>
                    </div>
                `;

                // Make the PDF content visible for rendering
                pdfContentToPrint.style.display = 'block';
                pdfContentToPrint.style.position = 'absolute';
                pdfContentToPrint.style.left = '-9999px';
                pdfContentToPrint.style.top = '0';

                // Generate and download PDF
                html2canvas(pdfContentToPrint, { 
                    scale: 2,
                    useCORS: true,
                    allowTaint: true,
                    backgroundColor: '#ffffff'
                }).then(canvas => {
                    try {
                        const imgData = canvas.toDataURL('image/png');
                        const pdf = new jspdf.jsPDF('p', 'mm', 'a4');
                        const imgProps = pdf.getImageProperties(imgData);
                        const pdfWidth = pdf.internal.pageSize.getWidth();
                        const pdfHeight = (imgProps.height * pdfWidth) / imgProps.width;
                        
                        // Check if content fits on one page
                        if (pdfHeight > pdf.internal.pageSize.getHeight()) {
                            // Split into multiple pages if needed
                            const pageHeight = pdf.internal.pageSize.getHeight();
                            const pages = Math.ceil(pdfHeight / pageHeight);
                            
                            for (let i = 0; i < pages; i++) {
                                if (i > 0) pdf.addPage();
                                const sourceY = i * pageHeight * imgProps.width / pdfWidth;
                                const sourceHeight = Math.min(pageHeight * imgProps.width / pdfWidth, imgProps.height - sourceY);
                                
                                pdf.addImage(imgData, 'PNG', 0, 0, pdfWidth, pdfHeight, '', 'FAST', 0, sourceY, imgProps.width, sourceHeight);
                            }
                        } else {
                            pdf.addImage(imgData, 'PNG', 0, 0, pdfWidth, pdfHeight);
                        }
                        
                        // Generate filename
                        const filename = `Prescription_${prescription.patient_name.replace(/[^a-zA-Z0-9]/g, '_')}_${prescription.prescription_date}.pdf`;
                        pdf.save(filename);
                        
                        // Hide the PDF content after generation
                        pdfContentToPrint.style.display = 'none';
                        
                        alert('PDF generated successfully!');
                    } catch (error) {
                        console.error('PDF generation error:', error);
                        alert('Error generating PDF. Please try again.');
                        pdfContentToPrint.style.display = 'none';
                    }
                }).catch(error => {
                    console.error('Canvas generation error:', error);
                    alert('Error generating PDF. Please try again.');
                    pdfContentToPrint.style.display = 'none';
                });
            }

            function generateSimplePdf(prescription) {
                // Parse medicines data
                let medicineListHtml = '';
                try {
                    const medicines = typeof prescription.medicines === 'string' ? 
                        JSON.parse(prescription.medicines) : prescription.medicines;
                    
                    if (Array.isArray(medicines)) {
                        medicineListHtml = medicines.map(m => `
                            <div class="medicine-item">
                                <h4>${m.medicineName} - ${m.potency}</h4>
                                <p><strong>Instructions:</strong> ${m.instructions}</p>
                            </div>
                        `).join('');
                    }
                } catch (error) {
                    medicineListHtml = '<p>Error parsing medicines data</p>';
                }

                // Fallback method - create a simple printable version
                const printWindow = window.open('', '_blank');
                const printContent = `
                    <!DOCTYPE html>
                    <html>
                    <head>
                        <title>Noor Homeopathy Prescription</title>
                        <style>
                            body { 
                                font-family: Arial, sans-serif; 
                                margin: 40px; 
                                line-height: 1.6;
                            }
                            .header { 
                                text-align: center; 
                                border-bottom: 2px solid #1e40af; 
                                padding-bottom: 20px; 
                                margin-bottom: 30px; 
                            }
                            .header h1 { 
                                color: #1e40af; 
                                margin: 0; 
                                font-size: 24px;
                            }
                            .patient-info { 
                                margin-bottom: 30px; 
                                border: 1px solid #d1d5db;
                                padding: 20px;
                                background: #f9fafb;
                            }
                            .patient-info table { 
                                width: 100%; 
                                border-collapse: collapse; 
                            }
                            .patient-info td { 
                                padding: 8px; 
                                border-bottom: 1px solid #eee; 
                            }
                            .medicines { 
                                margin-bottom: 30px; 
                            }
                            .medicines h3 { 
                                color: #1e40af; 
                                border-bottom: 1px solid #1e40af;
                                padding-bottom: 5px;
                            }
                            .medicine-item {
                                margin: 15px 0;
                                padding: 15px;
                                border: 1px solid #d1d5db;
                                border-radius: 5px;
                                background: #f3f4f6;
                            }
                            .signature {
                                margin-top: 50px;
                                text-align: center;
                            }
                            @media print { 
                                body { margin: 20px; } 
                            }
                        </style>
                    </head>
                    <body>
                        <div class="header">
                            <h1>Noor Homeopathy Prescription</h1>
                            <p>Dr. Isahaque Obaidi - B.H.M.S (DU), D.M.U (Ultrasonography)</p>
                            <p>Mirpur Tulaputti, Kushtia</p>
                        </div>
                        
                        <div class="patient-info">
                            <h3>Patient Information</h3>
                            <table>
                                <tr><td><strong>Patient ID:</strong></td><td>${prescription.patient_id}</td><td><strong>Date:</strong></td><td>${prescription.prescription_date}</td></tr>
                                <tr><td><strong>Name:</strong></td><td>${prescription.patient_name}</td><td><strong>Phone:</strong></td><td>${prescription.phone}</td></tr>
                                <tr><td><strong>Gender:</strong></td><td>${prescription.gender}</td><td><strong>Age:</strong></td><td>${prescription.age}</td></tr>
                            </table>
                        </div>
                        
                        ${prescription.disease_info ? `
                            <div class="patient-info" style="background: #fef3c7; border-color: #f59e0b;">
                                <h3 style="color: #92400e;">Disease Information</h3>
                                <p>${prescription.disease_info}</p>
                            </div>
                        ` : ''}
                        
                        <div class="medicines">
                            <h3>Prescribed Medicines</h3>
                            ${medicineListHtml}
                        </div>
                        
                        <div class="signature">
                            <p>_________________________</p>
                            <p><strong>Doctor's Signature</strong></p>
                        </div>
                    </body>
                    </html>
                `;
                
                printWindow.document.write(printContent);
                printWindow.document.close();
                printWindow.print();
            }

            // --- Event Listeners ---
            saveBtn.addEventListener('click', savePrescription);
            viewAllBtn.addEventListener('click', () => toggleView('table'));
            backToFormBtn.addEventListener('click', () => toggleView('form'));

            // Patient lookup functionality
            const lookupPatientBtn = document.getElementById('lookup-patient-btn');
            const patientIdInput = document.getElementById('patient-id');

            // Auto-fill on Enter key press
            patientIdInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    lookupPatient();
                }
            });

            // Lookup button click
            lookupPatientBtn.addEventListener('click', lookupPatient);

            function lookupPatient() {
                const patientId = patientIdInput.value.trim();
                
                if (!patientId) {
                    alert('Please enter a Patient ID to lookup.');
                    return;
                }

                // Show loading state
                lookupPatientBtn.innerHTML = `
                    <svg class="animate-spin h-4 w-4 inline mr-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Looking up...
                `;
                lookupPatientBtn.disabled = true;

                // Fetch patient data from database
                fetch(ajaxUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'action=get_patient&nonce=' + nonce + '&patient_id=' + encodeURIComponent(patientId)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Auto-fill all patient information
                        const patient = data.data;
                        document.getElementById('patient-name').value = patient.patient_name || '';
                        document.getElementById('gender').value = patient.gender || '';
                        document.getElementById('age').value = patient.age || '';
                        document.getElementById('phone').value = patient.phone || '';
                        
                        // Set current date
                        const today = new Date().toISOString().split('T')[0];
                        document.getElementById('date').value = today;
                        
                        alert('Patient found! All information has been auto-filled.');
                    } else {
                        alert('Patient not found. Please check the Patient ID and try again.');
                        // Clear fields
                        document.getElementById('patient-name').value = '';
                        document.getElementById('gender').value = '';
                        document.getElementById('age').value = '';
                        document.getElementById('phone').value = '';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error looking up patient. Please try again.');
                })
                .finally(() => {
                    // Reset button state
                    lookupPatientBtn.innerHTML = `
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 inline mr-1" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                        </svg>
                        Lookup Patient
                    `;
                    lookupPatientBtn.disabled = false;
                });
            }

            // Set current date on page load
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('date').value = today;
        });
    </script>
</body>
</html>