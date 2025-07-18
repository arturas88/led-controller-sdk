<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use LEDController\Enum\Alignment;
use LEDController\Enum\Color;
use LEDController\Enum\Effect;
use LEDController\Enum\FontSize;
use LEDController\Enum\TextProcessorMode;
use LEDController\LEDController;

echo "=== LEDController LED Controller: Basic Usage (Modernized) ===\n\n";

try {
    // Create LED controller connection
    $controller = new LEDController([
        'ip' => '192.168.1.222',
        'port' => 5200,
    ]);

    echo "1. Connecting to LED controller...\n";
    $controller->connect();
    echo "   ✓ Connected to LED controller successfully\n\n";

    // Clear the display first
    echo "2. Clearing display...\n";
    $controller->clearDisplay();
    echo "   ✓ Display cleared\n\n";

    // Set up split screen using external calls manager
    echo "3. Creating split screen layout...\n";
    $controller->external()->splitScreen([
        ['x' => 0, 'y' => 0, 'width' => 128, 'height' => 32],
        ['x' => 0, 'y' => 32, 'width' => 128, 'height' => 32],
    ]);
    echo "   ✓ Split screen windows created\n\n";

    // Display text using modern enums
    echo "4. Displaying text with modern enums...\n";

    // Main text with large font and red color
    $controller->external()->displayText(0, 'MODERN LED', [
        'font' => FontSize::FONT_24,
        'color' => Color::RED,
        'align' => Alignment::CENTER,
        'effect' => Effect::OPEN_CENTER_H,
    ]);

    // Status text with smaller font and green color
    $controller->external()->displayText(1, 'SDK v2.0', [
        'font' => FontSize::FONT_16,
        'color' => Color::GREEN,
        'align' => Alignment::CENTER,
        'effect' => Effect::MOVE_RIGHT,
    ]);

    echo "   ✓ Text displayed with modern enums\n\n";

    // Demonstrate different colors using enum
    echo "5. Color demonstration using enums...\n";
    $colors = [
        Color::RED,
        Color::GREEN,
        Color::BLUE,
        Color::YELLOW,
        Color::MAGENTA,
        Color::CYAN,
    ];

    foreach ($colors as $color) {
        echo "   - Displaying in {$color->name} color\n";
        $controller->external()->displayText(0, $color->name, [
            'font' => FontSize::FONT_24,
            'color' => $color,
            'align' => Alignment::CENTER,
            'effect' => Effect::DRAW,
        ]);
        sleep(1);
    }
    echo "   ✓ Color demonstration complete\n\n";

    // Demonstrate effects using enum
    echo "6. Effect demonstration using enums...\n";
    $effects = [
        Effect::OPEN_LEFT,
        Effect::OPEN_RIGHT,
        Effect::OPEN_CENTER_H,
        Effect::MOVE_LEFT,
        Effect::MOVE_RIGHT,
        Effect::SCROLL_LEFT,
        Effect::FLICKER,
    ];

    foreach ($effects as $effect) {
        echo "   - Effect: {$effect->getName()}\n";
        $controller->external()->displayText(0, 'EFFECT DEMO', [
            'font' => FontSize::FONT_16,
            'color' => Color::WHITE,
            'align' => Alignment::CENTER,
            'effect' => $effect,
        ]);
        sleep(2);
    }
    echo "   ✓ Effect demonstration complete\n\n";

    // Demonstrate text processing modes
    echo "7. Text processing modes demonstration...\n";

    // Text mode (default)
    $controller->displayText('TEXT MODE', [
        'font' => FontSize::FONT_16,
        'color' => Color::YELLOW,
        'align' => Alignment::CENTER,
        'mode' => TextProcessorMode::TEXT,
    ]);
    echo "   - Text mode: Direct text display\n";
    sleep(2);

    // Transliterate mode
    $controller->displayText('TRANSLITERATE', [
        'font' => FontSize::FONT_16,
        'color' => Color::CYAN,
        'align' => Alignment::CENTER,
        'mode' => TextProcessorMode::TRANSLITERATE,
    ]);
    echo "   - Transliterate mode: ASCII conversion\n";
    sleep(2);

    echo "   ✓ Text processing modes demonstrated\n\n";

    // Brightness control
    echo "8. Brightness control demonstration...\n";
    $brightnessLevels = [31, 25, 15, 5, 31]; // Scale 0-31, end at max brightness

    foreach ($brightnessLevels as $brightness) {
        echo "   - Setting brightness to {$brightness}\n";
        $controller->setBrightness($brightness);
        sleep(1);
    }
    echo "   ✓ Brightness control complete\n\n";

    // Modern color support demonstration
    echo "9. Modern color support demonstration...\n";

    // Hex color support
    $controller->displayText('HEX COLOR', [
        'font' => FontSize::FONT_16,
        'color' => '#FF6600', // Orange
        'align' => Alignment::CENTER,
    ]);
    echo "   - Hex color: #FF6600 (Orange)\n";
    sleep(2);

    // RGB array support
    $controller->displayText('RGB ARRAY', [
        'font' => FontSize::FONT_16,
        'color' => ['r' => 255, 'g' => 0, 'b' => 128], // Pink
        'align' => Alignment::CENTER,
    ]);
    echo "   - RGB array: Pink (255,0,128)\n";
    sleep(2);

    // Color constant support (legacy)
    $controller->displayText('LEGACY COLOR', [
        'font' => FontSize::FONT_16,
        'color' => 0x05, // Magenta constant
        'align' => Alignment::CENTER,
    ]);
    echo "   - Legacy color constant: Magenta\n";
    sleep(2);

    echo "   ✓ Modern color support demonstrated\n\n";

    // Final display
    echo "10. Final display with modern features...\n";
    $controller->external()->displayText(0, 'MODERN SDK!', [
        'font' => FontSize::FONT_24,
        'color' => Color::GREEN,
        'align' => Alignment::CENTER,
        'effect' => Effect::OPEN_CENTER_H,
    ]);

    $controller->external()->displayText(1, 'PHP 8.1+ Ready', [
        'font' => FontSize::FONT_12,
        'color' => Color::YELLOW,
        'align' => Alignment::CENTER,
        'effect' => Effect::MOVE_UP,
    ]);

    echo "   ✓ Modern SDK demonstration complete!\n\n";

    // Unified displayText() method demonstration
    echo "11. Unified displayText() method demonstration...\n";
    echo "   - Single source of truth for all text display\n\n";

    // Clear display for unified demonstration
    $controller->clearDisplay();

    // Single window usage (default windowId = 0)
    echo "   - Single window: \$controller->displayText() with default window\n";
    $controller->displayText('UNIFIED API', [
        'font' => FontSize::FONT_24,
        'color' => Color::RED,
        'align' => Alignment::CENTER,
        'effect' => Effect::OPEN_CENTER_H,
    ]);
    sleep(2);

    // Set up split screen for multi-window demonstration
    echo "   - Setting up split screen for multi-window demo...\n";
    $controller->external()->splitScreen([
        ['x' => 0, 'y' => 0, 'width' => 128, 'height' => 32],
        ['x' => 0, 'y' => 32, 'width' => 128, 'height' => 32],
    ]);

    // Multi-window usage with window in options
    echo "   - Window 0: \$controller->displayText() with window in options\n";
    $controller->displayText('WINDOW 0', [
        'window' => 0,
        'font' => FontSize::FONT_16,
        'color' => Color::BLUE,
        'align' => Alignment::CENTER,
        'effect' => Effect::OPEN_CENTER_H,
    ]);
    sleep(1);

    echo "   - Window 1: \$controller->displayText() with window=1 in options\n";
    $controller->displayText('WINDOW 1', [
        'window' => 1,
        'font' => FontSize::FONT_16,
        'color' => Color::GREEN,
        'align' => Alignment::CENTER,
        'effect' => Effect::MOVE_RIGHT,
    ]);
    sleep(2);

    // Color format demonstration
    echo "   - Color format support: hex, RGB, enum\n";
    $controller->displayText('HEX COLOR', [
        'window' => 0,
        'color' => '#FF6600', // Hex color
        'font' => FontSize::FONT_16,
        'align' => Alignment::CENTER,
    ]);
    sleep(1);

    $controller->displayText('RGB COLOR', [
        'window' => 1,
        'color' => ['r' => 255, 'g' => 0, 'b' => 128], // RGB array
        'font' => FontSize::FONT_16,
        'align' => Alignment::CENTER,
    ]);
    sleep(2);

    echo "   ✓ Unified displayText() method demonstrated\n\n";

    echo "Unified displayText() Method Benefits:\n";
    echo "• \$controller->displayText(\$text, \$options) - Single method for all text display\n";
    echo "• window option defaults to 0 for backward compatibility\n";
    echo "• Full feature support: colors, fonts, effects, text processing\n";
    echo "• Works for both single window and multi-window scenarios\n";
    echo "• External calls manager delegates to this unified method\n\n";

    echo "=== Example completed successfully! ===\n";
    echo "Features demonstrated:\n";
    echo "• Modern PHP 8.1+ enum usage\n";
    echo "• Union type support for colors\n";
    echo "• Match expression improvements\n";
    echo "• Backward compatibility maintained\n";
    echo "• Enhanced type safety\n";
    echo "• Multiple color format support\n";
    echo "• Text processing modes\n";
    echo "• Effect demonstrations\n\n";
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";

    echo "\n=== Troubleshooting Tips ===\n";
    echo "1. Check network connection to LED controller\n";
    echo "2. Verify IP address (192.168.1.222) is correct\n";
    echo "3. Ensure LED controller is powered on\n";
    echo "4. Check firewall settings on port 5200\n";
    echo "5. Try pinging the controller: ping 192.168.1.222\n";
    echo "6. Verify PHP version is 8.0+ for enum support\n";

    exit(1);
} finally {
    // Clean up connection
    if (isset($controller)) {
        try {
            $controller->disconnect();
            echo "✓ Disconnected from LED controller\n";
        } catch (Exception $e) {
            echo 'Warning: Error during disconnect: ' . $e->getMessage() . "\n";
        }
    }
}
