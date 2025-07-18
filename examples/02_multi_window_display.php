<?php

declare(strict_types=1);
/**
 * Working Multi-Window Display Example - LEDController LED Controller SDK.
 *
 * This example demonstrates:
 * - Functional split screen with proper window sizing
 * - Multiple windows with readable text
 * - Real-time data updates
 * - Simple and effective layout
 */

require_once __DIR__ . '/../vendor/autoload.php';

use LEDController\Enum\Alignment;
use LEDController\Enum\Color;
use LEDController\Enum\Effect;
use LEDController\Enum\FontSize;
use LEDController\LEDController;

echo "=== LEDController LED Controller - Working Multi-Window Display Example ===\n\n";

// Configuration - Update these values for your setup
$config = [
    'ip' => '192.168.1.222',
    'port' => 5200,
    'cardId' => 1,
    'timeout' => 5,
];

try {
    echo "1. Connecting to LED controller...\n";

    $controller = new LEDController([
        'ip' => $config['ip'],
        'port' => $config['port'],
        'cardId' => $config['cardId'],
        'timeout' => $config['timeout'] * 1000,
        'retries' => 3,
        'networkIdCode' => 0xFFFFFFFF,
    ]);

    $controller->connect();
    $controller->resetDisplay();

    echo "   ✓ Connected to {$config['ip']}:{$config['port']}\n";
    echo "   ✓ Display reset completed\n\n";

    echo "2. Setting up full-height multi-window layout...\n";

    // Full-height layout for 128x64 display (uses entire display)
    $windows = [
        // Top-left: Clock (larger window)
        [
            'id' => 0,
            'name' => 'Clock',
            'x' => 0,
            'y' => 0,
            'width' => 64,
            'height' => 32,  // Using full half-height
        ],
        // Top-right: Status (larger window)
        [
            'id' => 1,
            'name' => 'Status',
            'x' => 64,
            'y' => 0,
            'width' => 64,
            'height' => 32,  // Using full half-height
        ],
        // Bottom-left: Counter (larger window)
        [
            'id' => 2,
            'name' => 'Counter',
            'x' => 0,
            'y' => 32,
            'width' => 64,
            'height' => 32,  // Using full half-height
        ],
        // Bottom-right: Data (larger window)
        [
            'id' => 3,
            'name' => 'Data',
            'x' => 64,
            'y' => 32,
            'width' => 64,
            'height' => 32,  // Using full half-height
        ],
    ];

    // Create split screen using external calls manager
    $windowCoords = array_map(static function ($window) {
        return [
            'x' => $window['x'],
            'y' => $window['y'],
            'width' => $window['width'],
            'height' => $window['height'],
        ];
    }, $windows);

    $controller->external()->splitScreen($windowCoords);

    echo "   ✓ Split screen created with 4 windows\n";
    foreach ($windows as $window) {
        echo "   • Window {$window['id']}: {$window['name']} ({$window['x']},{$window['y']} - {$window['width']}x{$window['height']})\n";
    }
    echo "\n";

    echo "3. Displaying initial content in each window...\n";

    // Window 0: Clock display with larger font
    $controller->external()->displayText(0, date('H:i'), [
        'font' => FontSize::FONT_16,  // Using larger font for bigger window
        'color' => Color::RGB_GREEN,
        'effect' => Effect::DRAW,
        'align' => Alignment::CENTER,
    ]);
    echo "   ✓ Clock displayed in window 0\n";

    // Window 1: System status
    $controller->external()->displayText(1, 'ONLINE', [
        'font' => FontSize::FONT_12,  // Using larger font for bigger window
        'color' => Color::RGB_GREEN,
        'effect' => Effect::DRAW,
        'align' => Alignment::CENTER,
    ]);
    echo "   ✓ Status displayed in window 1\n";

    // Window 2: Counter
    $controller->external()->displayText(2, 'CNT:0', [
        'font' => FontSize::FONT_12,  // Using larger font for bigger window
        'color' => Color::RGB_BLUE,
        'effect' => Effect::DRAW,
        'align' => Alignment::CENTER,
    ]);
    echo "   ✓ Counter displayed in window 2\n";

    // Window 3: Temperature data
    $controller->external()->displayText(3, '22C', [
        'font' => FontSize::FONT_16,  // Using larger font for bigger window
        'color' => Color::RGB_YELLOW,
        'effect' => Effect::DRAW,
        'align' => Alignment::CENTER,
    ]);
    echo "   ✓ Temperature displayed in window 3\n\n";

    echo "4. Running dynamic updates for 30 seconds...\n";

    // Simulation data
    $statusMessages = [
        ['text' => 'ONLINE', 'color' => Color::RGB_GREEN],
        ['text' => 'BUSY', 'color' => Color::RGB_YELLOW],
        ['text' => 'READY', 'color' => Color::RGB_BLUE],
        ['text' => 'WAIT', 'color' => Color::RGB_CYAN],
    ];

    $temperatures = [20, 21, 22, 23, 24, 25, 24, 23, 22, 21];

    // Run for 30 seconds with updates
    $startTime = time();
    $counter = 0;
    $statusIndex = 0;
    $tempIndex = 0;

    while ((time() - $startTime) < 30) {
        // Update clock every 5 seconds
        if ((time() - $startTime) % 5 === 0) {
            $timeStr = date('H:i');
            $controller->external()->displayText(0, $timeStr, [
                'font' => FontSize::FONT_16,  // Using larger font for bigger window
                'color' => Color::RGB_GREEN,
                'effect' => Effect::DRAW,
                'align' => Alignment::CENTER,
            ]);
        }

        // Update counter every 3 seconds
        if ((time() - $startTime) % 3 === 0) {
            $counter++;
            $controller->external()->displayText(2, "CNT:{$counter}", [
                'font' => FontSize::FONT_12,  // Using larger font for bigger window
                'color' => Color::RGB_BLUE,
                'effect' => Effect::DRAW,
                'align' => Alignment::CENTER,
            ]);
            echo "   • Counter: {$counter}\n";
        }

        // Update status every 7 seconds
        if ((time() - $startTime) % 7 === 0) {
            $status = $statusMessages[$statusIndex % count($statusMessages)];
            $controller->external()->displayText(1, $status['text'], [
                'font' => FontSize::FONT_12,  // Using larger font for bigger window
                'color' => $status['color'],
                'effect' => Effect::DRAW,
                'align' => Alignment::CENTER,
            ]);
            echo "   • Status: {$status['text']}\n";
            $statusIndex++;
        }

        // Update temperature every 4 seconds
        if ((time() - $startTime) % 4 === 0) {
            $temp = $temperatures[$tempIndex % count($temperatures)];
            $controller->external()->displayText(3, "{$temp}C", [
                'font' => FontSize::FONT_16,  // Using larger font for bigger window
                'color' => Color::RGB_YELLOW,
                'effect' => Effect::DRAW,
                'align' => Alignment::CENTER,
            ]);
            echo "   • Temperature: {$temp}°C\n";
            $tempIndex++;
        }

        sleep(1);
    }

    echo "\n5. Demonstrating window effects...\n";

    // Show different effects in each window
    $effects = [
        ['name' => 'Scroll Left', 'effect' => Effect::SCROLL_LEFT],
        ['name' => 'Scroll Right', 'effect' => Effect::SCROLL_RIGHT],
        ['name' => 'Open Center', 'effect' => Effect::OPEN_CENTER_H],
        ['name' => 'Flicker', 'effect' => Effect::FLICKER],
    ];

    foreach ($effects as $windowIndex => $effectInfo) {
        $controller->external()->displayText($windowIndex, $effectInfo['name'], [
            'font' => FontSize::FONT_12,  // Using larger font for bigger window
            'color' => Color::RGB_RED,
            'effect' => $effectInfo['effect'],
            'align' => Alignment::CENTER,
        ]);
        echo "   ✓ Window {$windowIndex}: {$effectInfo['name']}\n";
        sleep(2);
    }

    echo "\n6. Testing simple animations...\n";

    // Simple animations in all windows
    $animationTexts = ['WIN1', 'WIN2', 'WIN3', 'WIN4'];
    $animationColors = [
        Color::RGB_RED,
        Color::RGB_GREEN,
        Color::RGB_BLUE,
        Color::RGB_YELLOW,
    ];

    for ($i = 0; $i < 4; $i++) {
        $controller->external()->displayText($i, $animationTexts[$i], [
            'font' => FontSize::FONT_8,
            'color' => $animationColors[$i],
            'effect' => Effect::WINDMILL,
            'align' => Alignment::CENTER,
        ]);
    }

    echo "   ✓ All windows animating simultaneously\n";
    echo "   • Waiting 5 seconds for animations...\n";
    sleep(5);

    echo "\n7. Final demonstration - simple text display...\n";

    // Display simple, readable text
    $finalTexts = ['TIME', 'STAT', 'NUM', 'TEMP'];
    $finalValues = [date('H:i'), 'OK', '999', '25C'];

    for ($i = 0; $i < 4; $i++) {
        $controller->external()->displayText($i, $finalTexts[$i], [
            'font' => FontSize::FONT_8,
            'color' => Color::RGB_WHITE,
            'effect' => Effect::DRAW,
            'align' => Alignment::CENTER,
        ]);
        sleep(1);

        $controller->external()->displayText($i, $finalValues[$i], [
            'font' => FontSize::FONT_8,
            'color' => Color::RGB_CYAN,
            'effect' => Effect::DRAW,
            'align' => Alignment::CENTER,
        ]);
        echo "   ✓ Window {$i}: {$finalTexts[$i]} -> {$finalValues[$i]}\n";
        sleep(1);
    }

    echo "\n8. Cleaning up...\n";

    // Wait a moment then exit split screen
    sleep(3);

    // Exit split screen mode
    $controller->external()->exitSplitScreen();
    echo "   ✓ Split screen mode exited\n";

    // Show final message
    $controller->displayText('Multi-Window Demo Complete!', [
        'font' => FontSize::FONT_16,
        'color' => Color::RGB_GREEN,
        'effect' => Effect::SCROLL_LEFT,
        'align' => Alignment::CENTER,
    ]);

    sleep(3);
    $controller->clearDisplay();
    $controller->disconnect();

    echo "   ✓ Display cleared and disconnected\n\n";

    echo "=== Full-Height Multi-Window Display Example Completed Successfully! ===\n";
    echo "\nKey improvements made:\n";
    echo "• Full-height window sizing (32 pixels high for larger fonts)\n";
    echo "• Larger font usage (FONT_12, FONT_16) for better readability\n";
    echo "• Utilizes entire display height (64 pixels total)\n";
    echo "• Proper 4-window layout with generous spacing\n";
    echo "• Clear visual separation between windows\n\n";

    echo "Full-height window layout used:\n";
    echo "┌─────────┬─────────┐\n";
    echo "│         │         │\n";
    echo "│ CLOCK   │ STATUS  │ (0,0 - 64x32 each)\n";
    echo "│         │         │\n";
    echo "├─────────┼─────────┤\n";
    echo "│         │         │\n";
    echo "│ COUNTER │  DATA   │ (0,32 - 64x32 each)\n";
    echo "│         │         │\n";
    echo "└─────────┴─────────┘\n";
    echo "Total display size: 128x64 pixels\n\n";
} catch (Exception $e) {
    echo "\n❌ Error: " . $e->getMessage() . "\n";
    echo 'File: ' . $e->getFile() . "\n";
    echo 'Line: ' . $e->getLine() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n\n";

    echo "Troubleshooting tips:\n";
    echo "• Ensure your controller supports multi-window/split screen functionality\n";
    echo "• Check that external calls protocol is enabled on your controller\n";
    echo "• Verify the window dimensions fit your display size\n";
    echo "• Make sure font sizes are smaller than window heights\n";
    echo "• Try using only FONT_8 for small windows\n";
    echo "• Check network connectivity and controller response times\n\n";

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

echo "Next steps:\n";
echo "• The windows now use full display height for better text visibility\n";
echo "• Experiment with different font sizes (FONT_8, FONT_12, FONT_16)\n";
echo "• Try adjusting window dimensions for displays larger than 128x64\n";
echo "• Rule of thumb: Window height should be at least font size + 8 pixels\n";
echo "• Use appropriate text lengths for your window sizes\n";
echo "• Test with single window first, then add more windows\n";
echo "• For 128x32 displays, halve the window heights to 16 pixels each\n\n";
