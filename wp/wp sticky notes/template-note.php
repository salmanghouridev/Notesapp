<?php
global $wpdb;
$note_id = isset($_GET['note_id']) ? intval($_GET['note_id']) : 0;
$note = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}sticky_notes WHERE id = %d", $note_id));

if ($note) {
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title><?php echo esc_html($note->heading); ?></title>
        <style>
            body {
                background-color: <?php echo esc_attr($note->color); ?>;
                color: #fff;
                font-family: Arial, sans-serif;
                padding: 20px;
                margin: 0;
            }
            .note {
                background-color: rgba(0, 0, 0, 0.7);
                padding: 20px;
                border-radius: 5px;
                position: relative;
                max-width: 600px;
                margin: auto;
                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            }
            .note-heading {
                font-size: 2em;
                margin-bottom: 20px;
            }
            .note-description {
                font-size: 1.2em;
                white-space: pre-wrap;
            }
            .copy-button {
                display: inline-block;
                padding: 10px 20px;
                background-color: #b21d1d;
                color: white;
                border: none;
                cursor: pointer;
                position: absolute;
                bottom: 20px;
                right: 20px;
                border-radius: 5px;
                transition: background-color 0.3s ease;
            }
            .copy-button:hover {
                background-color: #b21d1da1;
            }
            .note-content {
                position: relative;
            }
            .code-pad {
                overflow: auto;
                max-height: 600px; /* Adjust as needed */
                margin-bottom: 20px;
                border-radius: 5px;
                background-color: #2b2b2b;
            }
            .code-pad pre {
                white-space: pre-wrap;
                margin: 0;
            }
            .code-pad code {
                display: block;
                padding: 10px;
                color: #f8f8f2;
                font-family: monospace;
            }
        </style>
    </head>
    <body>
        <div class="note">
            <div class="note-heading"><?php echo esc_html($note->heading); ?></div>
            <div class="note-content">
                <?php if (strlen($note->description) > 100): ?>
                    <div class="code-pad">
                        <pre><code><?php echo htmlentities($note->description); ?></code></pre>
                    </div>
                <?php else: ?>
                    <div class="note-description"><?php echo esc_html($note->description); ?></div>
                <?php endif; ?>
                <button class="copy-button" onclick="copyToClipboard()">Copy</button>
            </div>
        </div>
        <script>
            function copyToClipboard() {
                var text = document.querySelector('.note-content code').innerText;
                navigator.clipboard.writeText(text).then(function() {
                    alert('Code copied to clipboard!');
                }).catch(function() {
                    alert('Failed to copy code to clipboard.');
                });
            }
        </script>
    </body>
    </html>
    <?php
} else {
    echo 'Note not found.';
}
?>
