<?php
/**
 * Working Multi-Window Display Example - LEDController LED Controller SDK
 * 
 * This example demonstrates:
 * - Functional split screen with proper window sizing
 * - Multiple windows with readable text
 * - Real-time data updates
 * - Simple and effective layout
 */

require_once __DIR__ . '/../vendor/autoload.php';

use LEDController\LEDController;
use LEDController\Enum\FontSize;
use LEDController\Enum\Effect;
use LEDController\Enum\Alignment;
use LEDController\Enum\Color;
use LEDController\PacketBuilder;
use LEDController\Enum\Protocol;
use LEDController\Enum\VerticalAlignment;

echo "=== LEDController LED Controller - Working Multi-Window Display Example ===\n\n";

// Configuration - Update these values for your setup
$config = [
    'ip' => '192.168.1.222',
    'port' => 5200,
    'cardId' => 1,
    'timeout' => 5000,
    'retries' => 3,
    'networkIdCode' => 0xFFFFFFFF,
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
    ]);
    
    $controller->connect();
    // sleep(1);

    $controller->clearDisplay();
    // sleep(1);
    
    echo "   ✓ Connected to {$config['ip']}:{$config['port']}\n";
    echo "   ✓ Display reset completed\n";
    
    echo "2. Setting up full-height multi-window layout...\n";
    
    // Full-height layout for 128x64 display (uses entire display)
    $windows = [
        // ROW 1
        [
            'id' => 0,
            'name' => 'QUEUE',
            'font' => FontSize::FONT_8,
            'color' => Color::YELLOW,
            'effect' => Effect::DRAW,
            'align' => Alignment::CENTER,
            'x' => 0,
            'y' => 0,
            'width' => 32,
            'height' => 16,
        ],
        [
            'id' => 1,
            'name' => 'RESERVED',
            'font' => FontSize::FONT_8,
            'color' => Color::YELLOW,
            'effect' => Effect::DRAW,
            'align' => Alignment::CENTER,
            'x' => 33,
            'y' => 0,
            'width' => 32,
            'height' => 16,
        ],

        // ROW 2
        [
            'id' => 2,
            'name' => 'MEB459->A',
            'font' => FontSize::FONT_8,
            'color' => Color::GREEN,
            'effect' => Effect::FLICKER,
            'align' => Alignment::CENTER,
            'x' => 0,
            'y' => 16,
            'width' => 32,
            'height' => 16,
        ],
        [
            'id' => 3,
            'name' => 'LFT746->B',
            'font' => FontSize::FONT_8,
            'color' => Color::GREEN,
            'effect' => Effect::FLICKER,
            'align' => Alignment::CENTER,
            'x' => 33,
            'y' => 16,
            'width' => 32,
            'height' => 16,
        ],

        // ROW 3
        [
            'id' => 4,
            'name' => 'HM350',
            'font' => FontSize::FONT_8,
            'color' => Color::RED,
            'effect' => Effect::DRAW,
            'align' => Alignment::CENTER,
            'x' => 0,
            'y' => 32,
            'width' => 32,
            'height' => 16,
        ],
        [
            'id' => 5,
            'name' => 'LPT926/GS899',
            'font' => FontSize::FONT_8,
            'color' => Color::RED,
            'effect' => Effect::DRAW,
            'align' => Alignment::CENTER,
            'x' => 33,
            'y' => 32,
            'width' => 32,
            'height' => 16,
        ],

        // ROW 4
        [
            'id' => 6,
            'name' => 'HU167',
            'font' => FontSize::FONT_8,
            'color' => Color::RED,
            'effect' => Effect::DRAW,
            'align' => Alignment::CENTER,
            'x' => 0,
            'y' => 48,
            'width' => 32,
            'height' => 16,
        ],
        [
            'id' => 7,
            'name' => 'HTL999',
            'font' => FontSize::FONT_8,
            'color' => Color::RED,
            'effect' => Effect::DRAW,
            'align' => Alignment::CENTER,
            'x' => 33,
            'y' => 48,
            'width' => 32,
            'height' => 16,
        ],
    ];
    
    // Create split screen using external calls manager
    $windowCoords = array_map(function($window) {
        return [
            'x' => $window['x'] * 2,
            'y' => $window['y'],
            'width' => $window['width'] * 2,
            'height' => $window['height'],
        ];
    }, $windows);

    $controller->external()->splitScreen($windowCoords);
    
    echo "3. Displaying initial content in each window...\n";

    // Display content in all windows
    foreach ($windows as $window) {        
        $controller->external()->displayText($window['id'], $window['name'], [
            'font' => FontSize::FONT_8,
            'color' => $window['color'],
            'effect' => $window['effect'],
            'align' => $window['align'],
            'valign' => VerticalAlignment::CENTER,
        ]);
        usleep(0.2 * 1000000);
    }

    echo "   ✓ Content displayed in windows\n";

    usleep(0.5 * 1000000);
    $controller->clearDisplay();
    $controller->disconnect();

} catch (Exception $e) {
    echo "\n❌ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
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
            echo "Note: Cleanup error (can be ignored): " . $cleanupError->getMessage() . "\n";
        }
    }
    
    exit(1);
}