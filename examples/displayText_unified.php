#!/usr/bin/env php
<?php

/**
 * Simple Text Display Example - LEDController SDK
 * 
 * This example demonstrates:
 * - Basic text display functionality
 * - Universal text processing with different modes
 * - Color support (RGB, hex, named colors)
 * - Font and alignment options
 * - Error handling and fallback mechanisms
 * 
 * For clock displays with GB2312 font compatibility issues, 
 * see example 04_clock_and_temperature.php for proper handling.
 */

require_once __DIR__ . '/../vendor/autoload.php';

use LEDController\LEDController;
use LEDController\Enum\FontSize;
use LEDController\Enum\Color;
use LEDController\Enum\Effect;
use LEDController\Enum\Alignment;

echo "=== displayText() - Single Source of Truth ===\n\n";

try {
    // Initialize controller
    $controller = new LEDController([
        'ip' => '192.168.1.222',
        'port' => 5200,
        'cardId' => 1
    ]);
    
    echo "1. Connecting to LED controller...\n";
    $controller->connect();
    echo "   ✓ Connected successfully\n\n";
    
    // Clear display
    $controller->resetDisplay();
    
    $controller->clearDisplay();
    echo "2. Cleared display\n\n";
    
    // Single method demonstration
    echo "3. Single displayText() method demonstration:\n";
    
    // Single window usage (default windowId = 0)
    echo "   - Single window: \$controller->displayText() with default window\n";
    $controller->displayText('SINGLE WINDOW', [
        'font' => FontSize::FONT_24,
        'color' => Color::RED,
        'align' => Alignment::CENTER,
        'effect' => Effect::OPEN_CENTER_H
    ]);
    
    sleep(3);
    
    // Set up split screen
    echo "   - Setting up split screen...\n";
    $controller->external()->splitScreen([
        ['x' => 0, 'y' => 0, 'width' => 128, 'height' => 32],
        ['x' => 0, 'y' => 32, 'width' => 128, 'height' => 32]
    ]);
    
    // Multi-window usage with window in options
    echo "   - Window 0: \$controller->displayText() with window in options\n";
    $controller->displayText('WINDOW 0', [
        'window' => 0,
        'font' => FontSize::FONT_16,
        'color' => Color::BLUE,
        'align' => Alignment::CENTER,
        'effect' => Effect::OPEN_CENTER_H
    ]);
    
    sleep(3);
    
    // Multiple windows using the same method
    echo "\n4. Multi-window display with single method:\n";
    
    echo "   - Window 1: \$controller->displayText() with window=1 in options\n";
    $controller->displayText('WINDOW 1', [
        'window' => 1,
        'font' => FontSize::FONT_16,
        'color' => Color::GREEN,
        'align' => Alignment::CENTER,
        'effect' => Effect::MOVE_RIGHT
    ]);
    
    sleep(3);
    
    // Color demonstration with multiple windows
    echo "\n5. Color support demonstration:\n";
    
    // Window 0 with hex color
    echo "   - Window 0 with hex color...\n";
    $controller->displayText('COLORFUL', [
        'window' => 0,
        'color' => '#FF6600', // Hex color
        'font' => FontSize::FONT_24,
        'align' => Alignment::CENTER
    ]);
    
    sleep(2);
    
    // Window 1 with enum color
    echo "   - Window 1 with enum color...\n";
    $controller->displayText('COLORFUL', [
        'window' => 1,
        'color' => Color::MAGENTA,
        'font' => FontSize::FONT_16,
        'align' => Alignment::CENTER,
        'effect' => Effect::FLICKER
    ]);
    
    sleep(2);
    
    // Clear and show single window
    echo "   - Single window with cyan color...\n";
    $controller->clearDisplay();
    $controller->displayText('CYAN TEXT', [
        'color' => Color::CYAN,
        'font' => FontSize::FONT_24,
        'align' => Alignment::CENTER
    ]); // Default window 0
    
    sleep(3);
    
    // Final demonstration - single method everywhere
    echo "\n6. Final demonstration - single method for everything:\n";
    
    // Single window usage (omit window option - defaults to 0)
    echo "   - Single window: omit window option (defaults to 0)\n";
    $controller->clearDisplay();
    $controller->displayText('ONE METHOD', [
        'font' => FontSize::FONT_24,
        'color' => Color::YELLOW,
        'align' => Alignment::CENTER
    ]);
    
    sleep(2);
    
    // Split screen with window options
    echo "   - Split screen: use window option\n";
    $controller->external()->splitScreen([
        ['x' => 0, 'y' => 0, 'width' => 128, 'height' => 32],
        ['x' => 0, 'y' => 32, 'width' => 128, 'height' => 32]
    ]);
    
    $controller->displayText('UNIFIED API', [
        'window' => 0,
        'font' => FontSize::FONT_16,
        'color' => Color::RED,
        'align' => Alignment::CENTER
    ]);
    
    $controller->displayText('SINGLE SOURCE', [
        'window' => 1,
        'font' => FontSize::FONT_16,
        'color' => Color::BLUE,
        'align' => Alignment::CENTER
    ]);
    
    sleep(3);
    
    echo "\n=== Single Source of Truth Achieved! ===\n";
    echo "\nUnified displayText() Method:\n";
    echo "• \$controller->displayText(\$text, \$options)\n";
    echo "• Single method handles all text display scenarios\n";
    echo "• window option defaults to 0 for backward compatibility\n";
    echo "• Full feature support: colors, fonts, effects, text processing\n";
    echo "• Uses Pure Text packet (0x7B/0x12) consistently\n\n";
    
    echo "Usage Examples:\n";
    echo "• Single window: \$controller->displayText('Hello');\n";
    echo "• Specific window: \$controller->displayText('Hello', ['window' => 1]);\n";
    echo "• With options: \$controller->displayText('Hello', ['color' => Color::RED]);\n";
    echo "• All options: \$controller->displayText('Hello', ['window' => 1, 'color' => Color::RED]);\n";
    echo "• External calls now delegate to this single method!\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
} finally {
    // Clean up
    if (isset($controller)) {
        try {
            $controller->disconnect();
            echo "\n✓ Disconnected from LED controller\n";
        } catch (Exception $e) {
            echo "Warning: Error during disconnect: " . $e->getMessage() . "\n";
        }
    }
} 