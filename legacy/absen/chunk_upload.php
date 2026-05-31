<?php
/**
 * chunk_upload.php
 * Standalone chunked upload handler (bypasses framework routing)
 */

// Prevent any output
ob_start();

// Disable error display
error_reporting(0);
ini_set('display_errors', 0);

// Increase limits
@ini_set('memory_limit', '256M');
@ini_set('max_execution_time', '300');

try {
    // Verify this is a POST request
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }
    
    // Verify chunk parameter exists
    if (!isset($_POST['chunkIndex'])) {
        throw new Exception('Missing chunk parameters');
    }
    
    $chunkIndex = intval($_POST['chunkIndex']);
    $totalChunks = intval($_POST['totalChunks']);
    $fileName = $_POST['fileName'];
    $uploadType = $_POST['upload_type'];
    $uploadTime = $_POST['upload_time'] ?? '';
    
    // Determine target directory
    // This file is now in: /public_html/absen/chunk_upload.php
    // Target: /public_html/bahan/
    $baseDir = __DIR__ . '/../bahan/';
    
    // Normalize path
    $baseDir = realpath($baseDir);
    if ($baseDir === false) {
        // Try to create if doesn't exist
        $baseDir = __DIR__ . '/../bahan/';
        @mkdir($baseDir, 0755, true);
        $baseDir = realpath($baseDir);
        if ($baseDir === false) {
            throw new Exception('Cannot access base directory');
        }
    }
    
    if (substr($baseDir, -1) !== '/') {
        $baseDir .= '/';
    }
    
    // Determine subfolder
    $targetDir = '';
    switch ($uploadType) {
        case 'slide_ibadah':
            if ($uploadTime === 'pagi') {
                $targetDir = $baseDir . 'pagi/';
            } elseif ($uploadTime === 'siang') {
                $targetDir = $baseDir . 'siang/';
            } elseif ($uploadTime === 'sore') {
                $targetDir = $baseDir . 'sore/';
            }
            break;
        case 'slide_overlay':
            $targetDir = $baseDir . 'overlay/';
            break;
        case 'streaming_elements':
            $targetDir = $baseDir . 'elements/';
            break;
    }
    
    if (empty($targetDir)) {
        throw new Exception('Invalid upload type');
    }
    
    // Create target directory if needed
    if (!is_dir($targetDir)) {
        if (!@mkdir($targetDir, 0755, true)) {
            throw new Exception('Cannot create target directory');
        }
    }
    
    // Check if writable
    if (!is_writable($targetDir)) {
        throw new Exception('Target directory not writable');
    }
    
    // Temporary directory for chunks
    $tempDir = sys_get_temp_dir() . '/chunk_uploads/';
    if (!is_dir($tempDir)) {
        @mkdir($tempDir, 0755, true);
    }
    
    // Unique temp filename based on original filename and upload params
    $tempFileName = $tempDir . md5($fileName . $uploadType . $uploadTime . session_id());
    
    // Verify chunk file was uploaded
    if (!isset($_FILES['chunk']) || $_FILES['chunk']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('Chunk file upload error: ' . ($_FILES['chunk']['error'] ?? 'no file'));
    }
    
    // Read chunk data
    $chunkData = file_get_contents($_FILES['chunk']['tmp_name']);
    if ($chunkData === false) {
        throw new Exception('Cannot read chunk data');
    }
    
    // Write chunk to temp file
    $mode = ($chunkIndex === 0) ? 'wb' : 'ab';
    $handle = fopen($tempFileName, $mode);
    if (!$handle) {
        throw new Exception('Cannot open temp file for writing');
    }
    
    $written = fwrite($handle, $chunkData);
    fclose($handle);
    
    if ($written === false) {
        throw new Exception('Cannot write chunk data');
    }
    
    // If this is the last chunk, move to final destination
    if ($chunkIndex === $totalChunks - 1) {
        $finalPath = $targetDir . basename($fileName);
        
        // Check if final file already exists
        if (file_exists($finalPath)) {
            // Add timestamp to make unique
            $pathInfo = pathinfo($finalPath);
            $finalPath = $pathInfo['dirname'] . '/' . 
                        $pathInfo['filename'] . '_' . time() . '.' . 
                        $pathInfo['extension'];
        }
        
        if (!rename($tempFileName, $finalPath)) {
            throw new Exception('Cannot move file to final destination');
        }
        
        // Success - all chunks uploaded
        ob_end_clean();
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => true,
            'complete' => true,
            'fileName' => basename($finalPath),
            'path' => $finalPath
        ]);
        exit;
    } else {
        // Chunk uploaded successfully, but not complete yet
        ob_end_clean();
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => true,
            'complete' => false,
            'chunkIndex' => $chunkIndex,
            'totalChunks' => $totalChunks
        ]);
        exit;
    }
    
} catch (Exception $e) {
    ob_end_clean();
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
    exit;
}