<?php
$ip = $_SERVER['REMOTE_ADDR'];
$host = gethostbyaddr($ip);

if (php_sapi_name() == "cli" || (isset($_SERVER['HTTP_USER_AGENT']) && strpos($_SERVER['HTTP_USER_AGENT'], 'curl') !== false)) {
    header('Content-Type: text/plain');
    echo $ip . "\n";
    exit;
}

$protocol = "IP";

if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {    
    $protocol = "IPv4";
} elseif (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
    $protocol = "IPv6";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>🌐</text></svg>">
    <title>IP info</title>
    <style>
        * {
            -webkit-text-size-adjust: 100%;
        }
        body {
            margin: 0;
            padding: 40px 20px 20px;
            color: #000;
            background-color: #eee;
            font-family: Arial, sans-serif;
            font-size: 16px;
            line-height: 1.5;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            border-radius: 10px;
            background-color: #fff;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .wrapper {
            max-width: 480px;
            margin: 0 auto;
        }
        h1 {
            margin: 0 0 40px;
            text-align: center;
        }
        .data {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        .label {
            margin-right: 10px;
        }
        .addr {
            width: 100%;
        }
        samp {
            border-bottom: 1px dashed #ccc;
            cursor: pointer;
            word-break: break-all;
            font-family: monospace;
        }
        samp:hover {
            border-color: #aaa;
        }
        .icon {
            margin-left: 10px;
        }
        .btn-copy {
            display: inline-block;
            margin-left: 10px;
            padding: 4px;
            border: none;
            border-radius: 4px;
            background-color: #eee;
            cursor: pointer;
            text-align: center;
            font-size: 16px;
            line-height: 1;
        }
        .btn-copy[disabled] {
            opacity: 0.6;
            cursor: not-allowed;
        }
        .message-wrapper {
            display: flex;
            justify-content: center;
            margin-top: 20px;
        }
        .message-text {
            margin: 0 auto;
            padding: 4px 8px;
            border-radius: 4px;
            color: #444;
            text-align: center;
            font-size: 14px;
        }
        .warning {
            background-color: #fec;
        }
        .loading {
            opacity: 0.6;
        }
        button.loading {
            cursor: wait !important;
        }
        samp.loading {
            cursor: progress;
        }
        samp.loading, samp.error, samp.nojs {
            border-bottom: 0;
        }
        samp.error, samp.nojs {
            cursor: auto;
        }
        .tooltip {
            position: relative;
        }
        .tooltip::before {
            display: none;
            position: absolute;
            left: 50%;
            bottom: 120%;
            transform: translateX(-50%);
            z-index: 1;
            padding: 2px 4px;
            border-radius: 4px;
            opacity: 1 !important;
            color: #fff;
            background-color: #666;
            white-space: nowrap;
            font-size: 14px;
            line-height: 1.5;
            content: attr(data-tooltip);
        }
        .tooltip:hover::before {
            display: inline-block;
        }
        footer {
            margin-top: 40px;
            color: #666;
            text-align: center;
            font-size: 14px;
        }
        h3 {
            margin: 0 0 10px;
            color: #222;
            font-size: 15px;
            font-weight: bold;
        }
        p {
            margin: 0 0 10px;
            line-height: 1.8;
        }
        code {
            margin: 0 2px;
            padding: 2px;
            border-radius: 4px;
            color: #444;
            background-color: #eee;
            cursor: copy;
            white-space: nowrap;
        }
        details {
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        summary {
            margin: 0 auto;
            padding: 4px 8px;
            border-radius: 5px;
            background-color: #eee;
            cursor: pointer;
            user-select: none;
            -webkit-user-select: none;
        }
        details[open] summary {
            border-radius: 5px 5px 0 0;
        }
        .collapsed {
            padding: 8px;
        }
        ul {
            margin: 0;
            padding: 0;
            list-style-type: none;
        }
        li {
            line-height: 1.8;
        }
        @media screen and (max-width: 640px) {
            body {
                padding: 20px 10px;
            }
        }
        @media screen and (max-height: 400px) {
            body {
                padding-top: 20px;
            }
        }
    </style>
    <script>
        async function fetchData() {
            const dataConfigs = [
                { id: 'ipv4', url: '//ip4.igro.me/' },
                { id: 'ipv6', url: '//ip6.igro.me/' },
            ];

            dataConfigs.forEach(config => fetchAndDisplayData(config.id, config.url));
        }

        async function fetchAndDisplayData(id, url) {
            const textField = document.getElementById(id);
            const button = document.getElementById(id + 'btn');

            try {
                const ipResponse = await fetch(url + 'ip');
                const ipAddress = (await ipResponse.text()).trim();

                const hostResponse = await fetch(url + 'host');
                const hostAddress = (await hostResponse.text()).trim();

                textField.textContent = ipAddress;
                textField.classList.remove('loading');
                textField.setAttribute('data-alt', hostAddress);
                textField.setAttribute('onclick', 'swapData(this)');
                
                button.disabled = false;
                button.classList.remove('loading');
                button.textContent = "📋";
            } catch (error) {
                textField.textContent = "Failed to fetch";
                textField.classList.remove('loading');
                textField.classList.add('error');
                textField.onclick = null;

                button.disabled = true;
                button.classList.remove('loading');
                button.textContent = "🚫";
                button.setAttribute('data-tooltip', 'Nothing to copy');
                console.error(`Failed to fetch ${id} address:`, error);
            }
        }

        function swapData(element) {
            const temp = element.getAttribute('data-alt');
            const button = document.getElementById(element.id + 'btn');

            element.setAttribute('data-alt', element.textContent);
            element.textContent = temp;

            button.disabled = false;
            button.setAttribute('data-tooltip', 'Copy to clipboard');
            button.textContent = "📋";
        }

        function copyToClipboard(elementId, button) {
            const textToCopy = document.getElementById(elementId).textContent;

            navigator.clipboard.writeText(textToCopy).then(() => {
                button.disabled = true;
                button.setAttribute('data-tooltip', 'Copied');
                button.textContent = '✔️';
            }).catch(error => {
                button.disabled = true;
                button.setAttribute('data-tooltip', 'Failed to copy');
                button.textContent = '⚠️';
                console.error('Failed to copy:', error);
            });
        }

        function addListeners() {
            const codeElements = document.querySelectorAll('code');
            
            codeElements.forEach(function(codeElement) {
                codeElement.addEventListener('click', function() {
                    navigator.clipboard.writeText(this.textContent);
                });
            });         
        }

        window.onload = function() {
            fetchData();

            addListeners();
        };
    </script>
</head>
<body>
    <div class="container">
        <div class="wrapper">
            <h1>🌐 IP info</h1>

            <noscript>
                <div class="data">
                    <div class="label"><strong><?php echo $protocol; ?>:</strong></div>
                    <div class="addr"><samp class="nojs"><?php echo $ip; ?></samp></div>
                </div>

                <div class="data">
                    <div class="label"><strong>Host:</strong></div>
                    <div class="addr"><samp class="nojs"><?php echo $host; ?></samp></div>
                </div>

                <div class="message-wrapper">
                    <span class="message-text warning">⚠️ Enable JavaScript to use interactive features and automatically fetch both IPv4 and IPv6 addresses</span>
                </div>

                <style type="text/css">
                    #js-only {
                        display: none;
                    }
                    code {
                        cursor: text;
                    }
                </style>
            </noscript>

            <section id="js-only">
                <div class="data">
                    <div class="label"><strong>IPv4:</strong></div>
                    <div class="addr"><samp id="ipv4" class="loading" onclick="">Fetching...</samp></div>
                    <div class="icon"><button id="ipv4btn" class="btn-copy tooltip loading" data-tooltip="Copy to clipboard" onclick="copyToClipboard('ipv4', this)" disabled>⏳</button></div>
                </div>

                <div class="data">
                    <div class="label"><strong>IPv6:</strong></div>
                    <div class="addr"><samp id="ipv6" class="loading" onclick="">Fetching...</samp></div>
                    <div class="icon"><button id="ipv6btn" class="btn-copy tooltip loading" data-tooltip="Copy to clipboard" onclick="copyToClipboard('ipv6', this)" disabled>⏳</button></div>
                </div>
            </section>

            <footer>
                <h3>ℹ️ Command Line Interface:</h3>

                <p><strong>IP:</strong> <code>curl ip.igro.me</code> │ <strong>Hostname:</strong> <code>curl ip.igro.me/host</code></p>

                <details>
                    <summary><em>Advanced usage</em></summary>
                    <div class="collapsed">
                        <ul>
                            <li><strong>IPv4:</strong> <code>curl -4 ip.igro.me</code> or <code>curl ip4.igro.me</code></li>
                            <li><strong>IPv6:</strong> <code>curl -6 ip.igro.me</code> or <code>curl ip6.igro.me</code></li>
                        </ul>
                    </div>
                </details>
            </footer>
        </div>
    </div>
</body>
</html>
