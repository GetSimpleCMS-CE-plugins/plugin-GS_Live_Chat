<?php
/*
Plugin Name: GS Public Live Chat
Description: Real-time public community chat plugin for GetSimple CMS CE.
Version: 1.0
Author: Zeth
*/

$thisfile = basename(__FILE__, ".php");

register_plugin(
    $thisfile,
    'GS Live Chat',
    '1.0',
    'Zeth',
    'http://getsimple-ce.ovh',
    'Live Chat Widget for GetSimple CE',
    'plugins',
    'gs_chat_admin'
);

add_action('plugins-sidebar', 'createSideMenu', array($thisfile, 'GS Live Chat'));

// Register template function tag
function get_gs_chat_widget() {
    $frontend_file = __DIR__ . '/gs_chat/chat_frontend.php';
    if (file_exists($frontend_file)) {
        include($frontend_file);
    } else {
        echo '<!-- GetSimple Chat Error: chat_frontend.php not found inside plugins/gs_chat/ -->';
    }
}

// Intercept API calls before GetSimple templates load
add_action('index-pretemplate', 'gs_chat_api_router');

function gs_chat_api_router() {
    if (isset($_GET['gs_chat_api'])) {
        $handler_file = __DIR__ . '/gs_chat/chat_handler.php';
        if (file_exists($handler_file)) {
            include($handler_file);
        }
        exit;
    }
}

// Backend Administration UI Panel
function gs_chat_admin() {
    $logs_dir = __DIR__ . '/gs_chat/data';
    $data_file = $logs_dir . '/chat_messages.json';

    // Handle form submissions from admin
    if (isset($_POST['gs_chat_save_announcement'])) {
        $announcement = strip_tags(trim($_POST['gs_chat_announcement'] ?? ''));

        if (file_exists($data_file)) {
            $raw = file_get_contents($data_file);
            $data = json_decode($raw, true);
            if (is_array($data)) {
                $data['announcement'] = $announcement;
                file_put_contents($data_file, json_encode($data, JSON_PRETTY_PRINT));
                echo '<p class="updated" style="background:#ffffe0; border:1px solid #e6db55; padding:8px; margin-bottom:15px;">Announcement updated successfully!</p>';
            }
        }
    }

    if (isset($_POST['gs_chat_save_color'])) {
        $theme_color = trim($_POST['gs_chat_theme_color'] ?? '');

        // Fallback to default #2563EB if input is empty or invalid
        if (empty($theme_color) || !preg_match('/^#[a-fA-F0-9]{6}$/', $theme_color)) {
            $theme_color = '#2563EB';
        }

        if (file_exists($data_file)) {
            $raw = file_get_contents($data_file);
            $data = json_decode($raw, true);
            if (is_array($data)) {
                $data['theme_color'] = $theme_color;
                file_put_contents($data_file, json_encode($data, JSON_PRETTY_PRINT));
                echo '<p class="updated" style="background:#ffffe0; border:1px solid #e6db55; padding:8px; margin-bottom:15px;">Theme color updated successfully!</p>';
            }
        }
    }

    if (isset($_POST['gs_chat_save_position'])) {
        $position = ($_POST['gs_chat_position'] === 'left') ? 'left' : 'right';

        if (file_exists($data_file)) {
            $raw = file_get_contents($data_file);
            $data = json_decode($raw, true);
            if (is_array($data)) {
                $data['chat_position'] = $position;
                file_put_contents($data_file, json_encode($data, JSON_PRETTY_PRINT));
                echo '<p class="updated" style="background:#ffffe0; border:1px solid #e6db55; padding:8px; margin-bottom:15px;">Chat position updated successfully!</p>';
            }
        }
    }

    if (isset($_POST['gs_chat_clear_history'])) {
        if (file_exists($data_file)) {
            $raw = file_get_contents($data_file);
            $data = json_decode($raw, true);
            if (is_array($data)) {
                $data['messages'] = array();
                file_put_contents($data_file, json_encode($data, JSON_PRETTY_PRINT));
                echo '<p class="updated" style="background:#ffffe0; border:1px solid #e6db55; padding:8px; margin-bottom:15px;">Chat message history cleared successfully!</p>';
            }
        }
    }

    // Load current storage stats and settings
    $message_count = 0;
    $current_announcement = '';
    $current_color = '#2563EB';
    $current_position = 'right';

    if (file_exists($data_file)) {
        $data = json_decode(file_get_contents($data_file), true);
        if (is_array($data)) {
            $message_count = count($data['messages'] ?? []);
            $current_announcement = $data['announcement'] ?? '';
            $current_color = !empty($data['theme_color']) ? $data['theme_color'] : '#2563EB';
            $current_position = !empty($data['chat_position']) ? $data['chat_position'] : 'right';
        }
    }
    ?>
    <style>
        /* Mobile Scaling & Layout Styles */
        .gs-chat-admin-wrap {
            font-size: 14px;
            max-width: 100%;
            box-sizing: border-box;
            overflow-x: hidden;
        }
        .gs-chat-admin-card {
            background: #fff;
            border: 1px solid #ccd0d4;
            padding: 20px;
            border-radius: 5px;
            margin-top: 15px;
            box-sizing: border-box;
        }
        .gs-chat-action-row {
            margin-top: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }
        .gs-chat-admin-wrap input[type="text"],
        .gs-chat-admin-wrap select {
            font-size: 14px !important;
            padding: 8px !important;
            width: 100% !important;
            box-sizing: border-box;
            max-width: 100%;
        }
        .gs-chat-admin-wrap code {
            word-break: break-all;
        }

        @media screen and (max-width: 768px) {
            .gs-chat-admin-card {
                padding: 12px;
            }
            .gs-chat-action-row {
                flex-direction: column;
                align-items: stretch;
            }
            .gs-chat-action-row input[type="submit"],
            .gs-chat-action-row input[type="button"],
            .gs-chat-action-row button.submit,
            .gs-chat-admin-card form input[type="submit"] {
                width: 100% !important;
                box-sizing: border-box;
                margin-top: 5px;
            }
        }
    </style>

    <div class="gs-chat-admin-wrap">
        <h3>Live Chat Moderation</h3>
        <p>Manage community live chat messages, pinned announcements, and storage settings from your GetSimple dashboard.</p>
        <p style="background: #eef; padding: 10px; border-left: 4px solid #33f; word-break: break-all; font-size: 13px;">
            To display the widget, add the following PHP tag to your theme template (e.g., <code>footer.inc.php</code>), just before the closing <code>&lt;/body&gt;</code> tag:<br>
            <strong><code>&lt;?php if (function_exists('get_gs_chat_widget')) { get_gs_chat_widget(); } ?&gt;</code></strong>
        </p>
        
        <div class="gs-chat-admin-card">
            <!-- Combined Announcement and Theme Color Form -->
            <form id="gs_chat_settings_form" action="" method="post">
                <p>
                    <label style="display:block; font-weight:bold; margin-bottom:5px;">Pinned Announcement Banner:</label>
                    <input type="text" name="gs_chat_announcement" value="<?php echo htmlspecialchars($current_announcement); ?>" class="text" />
                    <small style="color: #666; display: block; margin-top: 4px;">This message is displayed at the top of the frontend chat widget for all visitors.</small>
                </p>

                <!-- Hidden inputs for color change submission -->
                <input type="hidden" id="theme_color_input" name="gs_chat_theme_color" value="<?php echo htmlspecialchars($current_color); ?>" />
                <input type="hidden" name="gs_chat_save_color" value="1" />
                
                <!-- Hidden native color picker element -->
                <input type="color" id="hidden_color_picker" value="<?php echo htmlspecialchars($current_color); ?>" style="display:none;" onchange="document.getElementById('theme_color_input').value = this.value; document.getElementById('gs_chat_settings_form').submit();" />

                <div class="gs-chat-action-row">
                    <input type="submit" name="gs_chat_save_announcement" class="submit" value="Save Announcement" style="cursor:pointer;" />
                    <input type="button" class="submit" onclick="document.getElementById('hidden_color_picker').click();" style="cursor:pointer;" value="Change Theme Color" />
                </div>
            </form>

            <hr style="border:0; border-top:1px solid #eee; margin: 20px 0;" />

            <!-- Chat Window Position Form -->
            <form action="" method="post">
                <p>
                    <label style="display:block; font-weight:bold; margin-bottom:5px;">Chat Window Position:</label>
                    <select name="gs_chat_position" style="max-width: 250px;">
                        <option value="right" <?php if($current_position === 'right') echo 'selected'; ?>>Right</option>
                        <option value="left" <?php if($current_position === 'left') echo 'selected'; ?>>Left</option>
                    </select>
                    <small style="color: #666; display: block; margin-top: 4px;">Choose whether the chat widget floats on the bottom-right or bottom-left of your website.</small>
                </p>
                <div class="gs-chat-action-row">
                    <input type="submit" name="gs_chat_save_position" class="submit" value="Save Position" style="cursor:pointer;" />
                </div>
            </form>

            <hr style="border:0; border-top:1px solid #eee; margin: 20px 0;" />

            <h4>Chat Statistics & Storage</h4>
            <p>Total Stored Messages: <strong><?php echo $message_count; ?></strong> / 200 max cached</p>
            <p>Data file location: <code>/plugins/gs_chat/data/chat_messages.json</code></p>
            <form action="" method="post" onsubmit="return confirm('Are you sure you want to wipe all chat history?');" style="margin-top: 15px;">
                <input type="submit" name="gs_chat_clear_history" value="Clear All Chat History" style="background: #dc2626; color: #fff; border: none; padding: 8px 14px; border-radius: 4px; cursor: pointer;" />
            </form>
        </div>
    </div>
    <?php
}

// Automatically enqueue FontAwesome stylesheet in header
add_action('theme-header', 'gs_chat_head_assets');
function gs_chat_head_assets() {
    echo '<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">';
}