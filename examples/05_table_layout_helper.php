<?php

declare(strict_types=1);
/**
 * Table Layout Helper Example - LEDController LED Controller SDK.
 *
 * This example demonstrates:
 * - Using the new createTableLayout() helper method
 * - Different table configurations (2x2, 3x2, 4x2, etc.)
 * - Automatic window sizing and positioning
 * - Displaying content in table cells
 * - Error handling for invalid configurations
 */

require_once __DIR__ . '/../vendor/autoload.php';

use LEDController\Enum\Alignment;
use LEDController\Enum\Color;
use LEDController\Enum\Effect;
use LEDController\Enum\FontSize;
use LEDController\Enum\VerticalAlignment;
use LEDController\LEDController;

echo "=== LEDController LED Controller - Table Layout Helper Example ===\n\n";

// Configuration - Update these values for your setup
$config = [
    'ip' => '192.168.1.222',
    'port' => 5200,
    'cardId' => 1,
    'timeout' => 5000,
    'retries' => 3,
    'networkIdCode' => 0xFFFFFFFF,
    'displayWidth' => 128,
    'displayHeight' => 64,
];

try {
    echo "1. Connecting to LED controller...\n";

    $controller = new LEDController([
        'ip' => $config['ip'],
        'port' => $config['port'],
        'cardId' => $config['cardId'],
        'timeout' => $config['timeout'],
        'retries' => $config['retries'],
        'networkIdCode' => $config['networkIdCode'],
        'displayWidth' => $config['displayWidth'],
        'displayHeight' => $config['displayHeight'],
    ]);

    $controller->connect();
    $controller->clearDisplay();

    echo "   ✓ Connected to {$config['ip']}:{$config['port']}\n";
    echo "   ✓ Display reset completed\n\n";

    // Show display dimensions detection
    echo "2. Detecting display dimensions...\n";
    $displayDimensions = $controller->external()->getDisplayDimensions();
    echo "   ✓ Detected display size: {$displayDimensions['width']}x{$displayDimensions['height']} pixels\n\n";

    // Example 1: 2x2 Table Layout (4 windows) - using automatic dimensions
    echo "3. Creating 2x2 table layout (automatic dimensions)...\n";

    $windows = $controller->external()->createTableLayout(2, 2);

    echo '   ✓ Created 2x2 table layout with ' . count($windows) . " windows\n";
    foreach ($windows as $window) {
        echo "   • Window {$window['id']}: ({$window['x']},{$window['y']}) - {$window['width']}x{$window['height']}\n";
    }

    // Apply the layout
    $controller->external()->splitScreen($windows);

    // Display content in each window
    $content = ['A1', 'A2', 'B1', 'B2'];
    foreach ($windows as $index => $window) {
        $controller->external()->displayText($window['id'], $content[$index], [
            'font' => FontSize::FONT_12,
            'color' => Color::GREEN,
            'effect' => Effect::DRAW,
            'align' => Alignment::CENTER,
            'valign' => VerticalAlignment::CENTER,
        ]);
        usleep((int)(0.2 * 1000000));
    }

    echo "   ✓ Content displayed in 2x2 table\n";
    sleep(3);

    // Example 2: 4x2 Table Layout (8 windows) - like the original example
    echo "\n4. Creating 4x2 table layout (like original example)...\n";

    $controller->clearDisplay();

    $windows = $controller->external()->createTableLayout(4, 2);

    echo '   ✓ Created 4x2 table layout with ' . count($windows) . " windows\n";
    foreach ($windows as $window) {
        echo "   • Window {$window['id']}: ({$window['x']},{$window['y']}) - {$window['width']}x{$window['height']}\n";
    }

    // Apply the layout
    $controller->external()->splitScreen($windows);

    // Display content in each window
    $content = [
        'QUEUE', 'RESERVED', 'MEB459', 'LFT746',
        'HM350', 'LPT926', 'HU167', 'HTL999',
    ];

    foreach ($windows as $index => $window) {
        $controller->external()->displayText($window['id'], $content[$index], [
            'font' => FontSize::FONT_8,
            'color' => $index < 2 ? Color::YELLOW : ($index < 4 ? Color::GREEN : Color::RED),
            'effect' => $index < 4 ? Effect::FLICKER : Effect::DRAW,
            'align' => Alignment::CENTER,
            'valign' => VerticalAlignment::CENTER,
        ]);
        usleep((int)(0.2 * 1000000));
    }

    echo "   ✓ Content displayed in 4x2 table\n";
    sleep(3);

    // Example 3: Custom display dimensions (for larger displays)
    echo "\n5. Creating 3x2 table layout with custom dimensions...\n";

    $controller->clearDisplay();

    // For a 128x64 display
    $customDimensions = ['width' => 128, 'height' => 64];
    $windows = $controller->external()->createTableLayout(3, 2, $customDimensions);

    echo '   ✓ Created 3x2 table layout with ' . count($windows) . " windows\n";
    echo "   ✓ Using custom dimensions: {$customDimensions['width']}x{$customDimensions['height']}\n";
    foreach ($windows as $window) {
        echo "   • Window {$window['id']}: ({$window['x']},{$window['y']}) - {$window['width']}x{$window['height']}\n";
    }

    // Apply the layout
    $controller->external()->splitScreen($windows);

    // Display content in each window
    $content = [
        'TOP-L', 'TOP-C', 'TOP-R',
        'BOT-L', 'BOT-C', 'BOT-R',
    ];

    foreach ($windows as $index => $window) {
        $controller->external()->displayText($window['id'], $content[$index], [
            'font' => FontSize::FONT_12,
            'color' => Color::BLUE,
            'effect' => Effect::DRAW,
            'align' => Alignment::CENTER,
            'valign' => VerticalAlignment::CENTER,
        ]);
        usleep((int)(0.2 * 1000000));
    }

    echo "   ✓ Content displayed in 3x2 table\n";
    sleep(3);

    // Example 4: Using the convenience method applyTableLayout()
    echo "\n6. Using applyTableLayout() convenience method...\n";

    $controller->clearDisplay();

    // This method creates and applies the layout in one step
    $windows = $controller->external()->applyTableLayout(2, 3); // 2 columns, 3 rows

    echo '   ✓ Applied 2x3 table layout with ' . count($windows) . " windows\n";

    // Display content
    $content = ['A1', 'A2', 'B1', 'B2', 'C1', 'C2'];
    foreach ($windows as $index => $window) {
        $controller->external()->displayText($window['id'], $content[$index], [
            'font' => FontSize::FONT_16,
            'color' => Color::WHITE,
            'effect' => Effect::OPEN_CENTER_H,
            'align' => Alignment::CENTER,
            'valign' => VerticalAlignment::CENTER,
        ]);
        usleep((int)(0.3 * 1000000));
    }

    echo "   ✓ Content displayed using applyTableLayout()\n";
    sleep(3);

    // Cleanup
    echo "\n7. Cleaning up...\n";
    $controller->clearDisplay();
    $controller->disconnect();

    echo "   ✓ Display cleared and disconnected\n\n";

    echo "=== Table Layout Helper Summary ===\n\n";
    echo "Available Methods:\n";
    echo "• getDisplayDimensions() - Gets display size from controller config\n";
    echo "• createTableLayout(columns, rows, [dimensions]) - Creates window coordinates\n";
    echo "• applyTableLayout(columns, rows, [dimensions]) - Creates and applies layout\n\n";

    echo "Parameters:\n";
    echo "• columns: Number of columns (1-8)\n";
    echo "• rows: Number of rows (1-8)\n";
    echo "• dimensions: Optional ['width' => int, 'height' => int] (auto-detected if not provided)\n\n";

    echo "Features:\n";
    echo "• Automatic display dimension detection from controller configuration\n";
    echo "• Automatic window sizing and positioning\n";
    echo "• Handles edge cases (last column/row uses remaining space)\n";
    echo "• Input validation (max 8 windows, minimum 8x8 pixels)\n";
    echo "• Returns window array with 'id', 'x', 'y', 'width', 'height'\n\n";

    echo "Usage Examples:\n";
    echo "• Auto-detected: \$windows = \$controller->external()->createTableLayout(2, 2);\n";
    echo "• Custom size: \$windows = \$controller->external()->createTableLayout(3, 2, ['width' => 128, 'height' => 64]);\n";
    echo "• One-step: \$windows = \$controller->external()->applyTableLayout(2, 3);\n";
    echo "• Get dimensions: \$dimensions = \$controller->external()->getDisplayDimensions();\n";
} catch (Exception $e) {
    echo "\n❌ Error: " . $e->getMessage() . "\n";
    echo 'File: ' . $e->getFile() . "\n";
    echo 'Line: ' . $e->getLine() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n\n";

    echo "Troubleshooting tips:\n";
    echo "• Ensure your controller supports multi-window/split screen functionality\n";
    echo "• Check that external calls protocol is enabled on your controller\n";
    echo "• Verify the display dimensions fit your actual display size\n";
    echo "• Make sure font sizes are smaller than window heights\n";
    echo "• Total windows (columns × rows) cannot exceed 8\n";
    echo "• Minimum window size is 8x8 pixels\n\n";

    // Try to cleanup
    if (isset($controller)) {
        try {
            $controller->external()->exitSplitScreen();
            $controller->clearDisplay();
            $controller->disconnect();
        } catch (Exception $cleanupError) {
            echo 'Note: Cleanup error (can be ignored): ' . $cleanupError->getMessage() . "\n";
        }
    }

    exit(1);
}
