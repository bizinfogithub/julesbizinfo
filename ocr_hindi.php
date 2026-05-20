<?php
/**
 * Standalone Hindi OCR Script
 * Extracts text from uploaded PDF files using Tesseract OCR.
 */

// Error reporting for development
ini_set('display_errors', 1);
error_reporting(E_ALL);

$output_text = "";
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['pdf_file'])) {
    $file = $_FILES['pdf_file'];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        $error = "Upload failed with error code " . $file['error'];
    } elseif (pathinfo($file['name'], PATHINFO_EXTENSION) !== 'pdf') {
        $error = "Only PDF files are allowed.";
    } else {
        $upload_dir = sys_get_temp_dir() . '/ocr_process_' . uniqid();
        if (!mkdir($upload_dir, 0777, true)) {
            $error = "Failed to create temporary directory.";
        } else {
            $pdf_path = $upload_dir . '/input.pdf';
            if (move_uploaded_file($file['tmp_name'], $pdf_path)) {

                // 1. Convert PDF to images
                // Using pdftoppm for better reliability
                $image_prefix = $upload_dir . '/page';
                $convert_cmd = "pdftoppm -jpeg -r 300 " . escapeshellarg($pdf_path) . " " . escapeshellarg($image_prefix);
                exec($convert_cmd, $convert_output, $convert_result);

                if ($convert_result !== 0) {
                    $error = "Failed to convert PDF to images. Make sure poppler-utils is installed.";
                } else {
                    // 2. Run Tesseract on each generated image
                    $images = glob($upload_dir . '/page-*.jpg');
                    // Use SORT_NATURAL to handle cases like page-1.jpg, page-10.jpg correctly
                    sort($images, SORT_NATURAL);

                    if (empty($images)) {
                        $error = "No images were generated from the PDF.";
                    } else {
                        foreach ($images as $image) {
                            $tess_cmd = "tesseract " . escapeshellarg($image) . " stdout -l hin+eng";
                            $text = shell_exec($tess_cmd);
                            $output_text .= "--- Page " . basename($image) . " ---\n";
                            $output_text .= $text . "\n\n";
                        }
                    }
                }

                // Cleanup
                // In a production environment, you might want to delay cleanup
                // or use a more robust method, but for this script, we'll do it now.
                array_map('unlink', glob("$upload_dir/*"));
                rmdir($upload_dir);

            } else {
                $error = "Failed to move uploaded file.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hindi OCR Tool</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .container { max-width: 800px; margin-top: 50px; }
        .result-area { background: #fff; border: 1px solid #ddd; padding: 20px; border-radius: 5px; white-space: pre-wrap; font-family: 'Courier New', Courier, monospace; }
    </style>
</head>
<body>
    <div class="container">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">Hindi PDF Text Extractor</h4>
            </div>
            <div class="card-body">
                <p class="text-muted">Upload a handwritten or printed Hindi PDF to extract text using Tesseract OCR.</p>

                <?php if ($error): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <form action="" method="POST" enctype="multipart/form-data" class="mb-4">
                    <div class="mb-3">
                        <label for="pdf_file" class="form-label">Select PDF File</label>
                        <input class="form-control" type="file" id="pdf_file" name="pdf_file" accept=".pdf" required>
                    </div>
                    <button type="submit" class="btn btn-success">Upload and Process</button>
                </form>

                <?php if ($output_text): ?>
                    <hr>
                    <h5>Extracted Text:</h5>
                    <div class="result-area"><?= htmlspecialchars($output_text) ?></div>
                <?php endif; ?>
            </div>
        </div>
        <div class="text-center mt-3 text-muted">
            <small>Requires tesseract-ocr, tesseract-ocr-hin, and poppler-utils.</small>
        </div>
    </div>
</body>
</html>
