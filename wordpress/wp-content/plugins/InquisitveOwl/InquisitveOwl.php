<?php
/**
 * Plugin Name: Inquisitive Owl Plugin
 * Description: A plugin to manage invoices and generate PDFs.
 * Version: 1.0
 * Author: Your Name
 */

// Include necessary files
require_once plugin_dir_path(__FILE__) . 'admin/admin-page.php';
require_once plugin_dir_path(__FILE__) . 'includes/db.php';

// Enqueue Bootstrap CSS
function enqueue_bootstrap_css() {
    wp_enqueue_style('bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css');
}
add_action('wp_enqueue_scripts', 'enqueue_bootstrap_css');

// Add PDF generation functionality (example)
function generate_invoice_pdf($invoice_id) {
    // Implement PDF generation logic here
    // You can use libraries like TCPDF or mPDF
    
    // Example using TCPDF:
    require_once plugin_dir_path(__FILE__) . 'libs/tcpdf/tcpdf.php';
    
    $pdf = new TCPDF();
    // Set PDF content and layout
    
    // Output PDF
    $pdf->Output('invoice_' . $invoice_id . '.pdf', 'D'); // 'D' for download, other options available
}

// Add a link to generate PDF on the invoice list page
function add_generate_pdf_link($actions, $invoice) {
    $actions['generate_pdf'] = '<a href="' . admin_url('admin.php') . '?page=your-plugin-settings&generate_pdf=' . $invoice->id . '">Generate PDF</a>';
    return $actions;
}
add_filter('your_plugin_invoice_actions', 'add_generate_pdf_link', 10, 2);

// Handle PDF generation request
function handle_generate_pdf_request() {
    if (isset($_GET['generate_pdf']) && is_numeric($_GET['generate_pdf'])) {
        $invoice_id = intval($_GET['generate_pdf']);
        generate_invoice_pdf($invoice_id);
    }
}
add_action('admin_init', 'handle_generate_pdf_request');
