#!/usr/bin/env php
<?php

/**
 * Example 4: Clock and Temperature Display with GB2312 Compatibility
 * 
 * This example demonstrates how to display clock and temperature information
 * with proper GB2312 font compatibility to avoid character display issues.
 */

require __DIR__ . '/../vendor/autoload.php';

use LEDController\LEDController;
use LEDController\Manager\ClockManager;
use LEDController\Manager\TemperatureManager;
use LEDController\Enum\FontSize;
use LEDController\Enum\Color;
use LEDController\Enum\Effect;
use LEDController\Enum\Alignment;

echo "=== Clock and Temperature Display Example (GB2312 Compatible) ===\n\n";

try {
    // Initialize LED controller
    $controller = new LEDController([
        'ip' => '192.168.1.222',     // Adjust for your network
        'port' => 5200,
        'cardId' => 1,
        'timeout' => 5000
    ]);
    
    // Connect to the controller
    $controller->connect();
    echo "✓ Connected to LED controller\n";
    
    // Clear screen first
    $controller->clearDisplay();
    echo "✓ Screen cleared\n";
    
    // Create split screen: left side for clock, right side for temperature
    $controller->external()->splitScreen([
        ['x' => 0, 'y' => 0, 'width' => 64, 'height' => 32],    // Left window for clock
        ['x' => 64, 'y' => 0, 'width' => 64, 'height' => 32]   // Right window for temperature
    ]);
    echo "✓ Split screen created\n";
    
    // Get clock manager
    $clockManager = $controller->clock();
    
    // Demonstrate GB2312 compatibility features
    echo "\n=== GB2312 Compatibility Testing ===\n";
    
    // Test different clock formats for GB2312 compatibility
    $testFormats = [
        'Standard 24-hour' => [
            'format' => ClockManager::FORMAT_24_HOUR,
            'content' => ClockManager::SHOW_HOUR | ClockManager::SHOW_MINUTE
        ],
        '12-hour with AM/PM' => [
            'format' => ClockManager::FORMAT_12_HOUR,
            'content' => ClockManager::SHOW_HOUR | ClockManager::SHOW_MINUTE
        ],
        'Full datetime' => [
            'format' => ClockManager::FORMAT_24_HOUR,
            'content' => ClockManager::SHOW_YEAR | ClockManager::SHOW_MONTH | 
                        ClockManager::SHOW_DAY | ClockManager::SHOW_HOUR | ClockManager::SHOW_MINUTE,
            'rowFormat' => ClockManager::MULTI_ROW
        ],
        'With weekday' => [
            'format' => ClockManager::FORMAT_24_HOUR,
            'content' => ClockManager::SHOW_HOUR | ClockManager::SHOW_MINUTE | ClockManager::SHOW_WEEKDAY
        ]
    ];
    
    foreach ($testFormats as $name => $options) {
        echo "\nTesting: $name\n";
        
        // Get compatibility report
        $report = $clockManager->getGB2312CompatibilityReport($options);
        
        if ($report['compatible']) {
            echo "  ✓ Compatible with GB2312\n";
        } else {
            echo "  ⚠ GB2312 issues found:\n";
            foreach ($report['issues'] as $issue) {
                echo "    - $issue\n";
            }
            echo "  Recommendations:\n";
            foreach ($report['recommendations'] as $rec) {
                echo "    - $rec\n";
            }
        }
        
        // Test what mode will be used
        $testResult = $clockManager->testClockDisplay(0, $options);
        echo "  Mode: {$testResult['mode']}\n";
        echo "  Message: {$testResult['message']}\n";
        
        if (isset($testResult['preview_text'])) {
            echo "  Preview text: '{$testResult['preview_text']}'\n";
            echo "  Text GB2312 compatible: " . ($testResult['text_compatible'] ? 'Yes' : 'No') . "\n";
        }
    }
    
    echo "\n=== Displaying Clock with GB2312 Safe Settings ===\n";
    
    // Use GB2312 safe preset
    $clockManager->displayClockPreset(0, 'time_gb2312_safe', [
        'font' => FontSize::FONT_16,
        'color' => Color::RED,
        'stay' => 0  // Persistent display
    ]);
    echo "✓ Clock displayed using GB2312 safe preset\n";
    
    // Display temperature in the second window
    echo "\n=== Temperature Display ===\n";
    $temperatureManager = $controller->temperature();
    
    // Show temperature with GB2312 compatible format
    $temperatureManager->displayTemperature(1, [
        'sensor' => 'internal',
        'unit' => 'celsius',
        'showUnit' => true,
        'font' => FontSize::FONT_16,
        'color' => Color::BLUE,
        'stay' => 0
    ]);
    echo "✓ Temperature displayed in window 1\n";
    
    // Demonstrate fallback mode
    echo "\n=== Fallback Mode Demonstration ===\n";
    
    // This will automatically use fallback mode due to 12-hour format
    $clockManager->displayClockPreset(0, 'time_12h', [
        'font' => FontSize::FONT_12,
        'color' => Color::GREEN
    ]);
    echo "✓ Clock displayed using fallback text mode (12-hour format)\n";
    
    sleep(3);
    
    // Show how to manually enable fallback mode
    $clockManager->displayClock(0, [
        'format' => ClockManager::FORMAT_24_HOUR,
        'content' => ClockManager::SHOW_HOUR | ClockManager::SHOW_MINUTE | ClockManager::SHOW_SECOND,
        'fallbackToTextMode' => true,  // Force fallback mode
        'font' => FontSize::FONT_12,
        'color' => Color::YELLOW
    ]);
    echo "✓ Clock displayed using forced fallback mode\n";
    
    echo "\n=== GB2312 Validation Settings ===\n";
    
    // Show how to disable GB2312 validation if needed
    $clockManager->setGB2312Validation(false);
    echo "✓ GB2312 validation disabled\n";
    
    // Display clock without validation (may have issues)
    $clockManager->displayClock(0, [
        'format' => ClockManager::FORMAT_12_HOUR,
        'content' => ClockManager::SHOW_HOUR | ClockManager::SHOW_MINUTE | ClockManager::SHOW_WEEKDAY,
        'calendar' => ClockManager::CALENDAR_LUNAR,
        'font' => FontSize::FONT_16,
        'color' => Color::MAGENTA
    ]);
    echo "⚠ Clock displayed without GB2312 validation (may have font issues)\n";
    
    sleep(3);
    
    // Re-enable validation and use safe settings
    $clockManager->setGB2312Validation(true);
    $clockManager->displayClockPreset(0, 'datetime_gb2312_safe');
    echo "✓ GB2312 validation re-enabled and safe preset applied\n";
    
    echo "\n=== Summary ===\n";
    echo "Available GB2312 safe presets:\n";
    $presets = $clockManager->getAvailablePresets();
    foreach ($presets as $key => $description) {
        if (strpos($key, 'gb2312') !== false) {
            echo "  - $key: $description\n";
        }
    }
    
    echo "\nKey points for GB2312 compatibility:\n";
    echo "  - Use 24-hour format instead of 12-hour\n";
    echo "  - Avoid lunar calendar modes\n";
    echo "  - Disable time scale display\n";
    echo "  - Avoid weekday display\n";
    echo "  - Use fallback text mode when in doubt\n";
    echo "  - Test with testClockDisplay() before deployment\n";
    
    // Keep display running
    echo "\nClock and temperature display running...\n";
    echo "Press Ctrl+C to stop\n";
    
    // Keep running to show the display
    while (true) {
        sleep(1);
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
} 