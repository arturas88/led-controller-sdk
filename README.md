# LED Controller SDK

[![PHP](https://img.shields.io/badge/PHP-8.0%2B-blue)](https://php.net)
[![License](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)
[![CI](https://github.com/arturas88/led-controller-sdk/actions/workflows/ci.yml/badge.svg)](https://github.com/arturas88/led-controller-sdk/actions/workflows/ci.yml)
[![Latest Version](https://img.shields.io/github/v/release/arturas88/led-controller-sdk)](https://github.com/arturas88/led-controller-sdk/releases)

A modern PHP SDK for communicating with LED controllers, specifically designed for C-Power5200 and compatible LED display systems. This SDK provides a fluent, object-oriented interface for controlling LED displays via network (TCP/IP) or serial (RS232/RS485) communication.

## Features

- **Modern PHP 8.0+ Architecture**: Uses PHP 8.0+ features including enums, typed properties, and modern language constructs
- **Dual Communication Support**: Network (TCP/IP) and Serial (RS232/RS485) communication
- **Fluent Interface**: Chainable methods for easy and readable code
- **Comprehensive Management**: Templates, files, schedules, configuration, and more
- **Text Processing**: Advanced text processing with Unicode support and image conversion
- **Multi-window Support**: Display content across multiple windows simultaneously
- **Clock & Temperature**: Built-in clock and temperature sensor management
- **File Operations**: Upload, download, and manage files on the controller
- **Error Handling**: Comprehensive exception handling with detailed error messages
- **Logging**: Built-in logging system for debugging and monitoring
- **Type Safety**: Full PHP type hints and modern enum support

## Requirements

- PHP 8.0 or higher
- `ext-sockets` extension (for network communication)
- `ext-json` extension
- `ext-mbstring` extension (for Unicode text processing)

## Installation

### Via Composer

```bash
composer require arturas88/led-controller-sdk
```

### Manual Installation

1. Download the latest release from GitHub
2. Extract the package to your project directory
3. Include the autoloader:

```php
require_once 'vendor/autoload.php';
```

## Quick Start

### Basic Usage

```php
<?php

use LEDController\LEDController;
use LEDController\Enum\FontSize;
use LEDController\Enum\Color;
use LEDController\Enum\Effect;
use LEDController\Enum\Alignment;

// Create controller instance
$controller = new LEDController([
    'ip' => '192.168.1.222',
    'port' => 5200,
    'cardId' => 1
]);

// Connect and display text
$controller->connect()
    ->displayText('Hello World!', [
        'font' => FontSize::FONT_24,
        'color' => Color::RED,
        'effect' => Effect::SCROLL_LEFT,
        'align' => Alignment::CENTER
    ]);

// Cleanup
$controller->disconnect();
```

### Network Configuration

```php
$controller = new LEDController([
    'communicationType' => 'network',
    'ip' => '192.168.1.222',
    'port' => 5200,
    'cardId' => 1,
    'timeout' => 5000,
    'retries' => 3
]);
```

### Serial Configuration

```php
$controller = new LEDController([
    'communicationType' => 'serial',
    'serialPort' => '/dev/ttyUSB0', // or 'COM1' on Windows
    'baudRate' => 115200,
    'cardId' => 1
]);
```

## Core Components

### LEDController

The main class that provides the primary interface:

```php
$controller = new LEDController($config);
$controller->connect();
$controller->displayText('Hello World!');
$controller->setBrightness(25);
$controller->setTime();
$controller->clearDisplay();
```

### Managers

Access specialized functionality through managers:

```php
// Template management
$controller->template()->create('myTemplate');

// File management
$controller->files()->upload('display.bmp', '/path/to/image.bmp');

// Schedule management
$controller->schedule()->createPlan(1, $scheduleData);

// Configuration management
$controller->config()->getNetworkConfig();

// External calls
$controller->external()->splitScreen($windows);
```

### Enums

Type-safe constants for various options:

```php
use LEDController\Enum\FontSize;
use LEDController\Enum\Color;
use LEDController\Enum\Effect;
use LEDController\Enum\Alignment;
use LEDController\Enum\VerticalAlignment;

$controller->displayText('Sample Text', [
    'font' => FontSize::FONT_16,
    'color' => Color::GREEN,
    'effect' => Effect::OPEN_CENTER_H,
    'align' => Alignment::CENTER,
    'valign' => VerticalAlignment::MIDDLE
]);
```

## Advanced Features

### Multi-Window Display

```php
// Create split screen layout
$controller->external()->splitScreen([
    ['x' => 0, 'y' => 0, 'width' => 64, 'height' => 32],
    ['x' => 64, 'y' => 0, 'width' => 64, 'height' => 32]
]);

// Display content in different windows
$controller->displayText('Window 1', ['window' => 0]);
$controller->displayText('Window 2', ['window' => 1]);
```

### Table Layout Helper

The SDK provides a convenient helper for creating equally divided table layouts:

```php
// Get display dimensions from controller configuration
$dimensions = $controller->external()->getDisplayDimensions();
echo "Display size: {$dimensions['width']}x{$dimensions['height']} pixels";

// Create a 2x2 table layout (4 windows)
$windows = $controller->external()->createTableLayout(2, 2);

// Create a 4x2 table layout (8 windows) - like the original example
$windows = $controller->external()->createTableLayout(4, 2);

// Create with custom display dimensions
$windows = $controller->external()->createTableLayout(3, 3, [
    'width' => 192, 
    'height' => 64
]);

// Apply layout in one step (creates and applies split screen)
$windows = $controller->external()->applyTableLayout(2, 3);

// Display content in table cells
foreach ($windows as $index => $window) {
    $controller->external()->displayText($window['id'], "Cell $index", [
        'font' => FontSize::FONT_12,
        'color' => Color::GREEN,
        'align' => Alignment::CENTER
    ]);
}
```

**Features:**
- Automatic display dimension detection from controller configuration
- Automatic window sizing and positioning
- Handles edge cases (last column/row uses remaining space)
- Input validation (max 8 windows, minimum 8x8 pixels)
- Support for custom display dimensions
- Returns window array with 'id', 'x', 'y', 'width', 'height'

**Parameters:**
- `columns`: Number of columns (1-8)
- `rows`: Number of rows (1-8)  
- `dimensions`: Optional ['width' => int, 'height' => int] (auto-detected if not provided)

**Limitations:**
- Maximum total windows: 8 (columns × rows ≤ 8)
- Minimum window size: 8×8 pixels
- Common layouts: 2×2 (4 windows), 4×2 (8 windows), 3×2 (6 windows), 2×3 (6 windows)

### Clock Display

```php
$controller->clock()->display([
    'format' => ClockManager::FORMAT_24_HOUR,
    'showSeconds' => true,
    'font' => FontSize::FONT_16,
    'color' => Color::BLUE
]);
```

### Temperature Monitoring

```php
$temperature = $controller->temperature()->readTemperature();
echo "Temperature: {$temperature['celsius']}°C";
echo "Humidity: {$temperature['humidity']}%";
```

### Image Display

```php
// Display image from file
$controller->displayImage('/path/to/image.bmp', [
    'window' => 0,
    'mode' => ImageMode::CENTER,
    'effect' => Effect::FADE_IN
]);

// Display text as image (for complex Unicode text)
$controller->displayTextAsImage('Complex Unicode: 你好世界', [
    'font' => FontSize::FONT_24,
    'color' => Color::WHITE
]);
```

### File Operations

```php
// Upload file to controller
$controller->files()->upload('myfile.bmp', '/local/path/image.bmp');

// Download file from controller
$controller->files()->download('myfile.bmp', '/local/path/downloaded.bmp');

// List files on controller
$files = $controller->files()->list();
```

### Program Building

```php
$program = $controller->program()
    ->create(128, 32)
    ->setRepeatTimes(5)
    ->addTextWindow(0, 0, 128, 32, 'Hello World!')
    ->addImageWindow(0, 0, 64, 32, '/path/to/image.bmp')
    ->build();
```

## Configuration Options

### Default Configuration

```php
$defaultConfig = [
    'ip' => '192.168.1.222',
    'port' => 5200,
    'cardId' => 1,
    'networkIdCode' => 0xFFFFFFFF,
    'timeout' => 5000,
    'retries' => 3,
    'communicationType' => 'network',
    'serialPort' => 'COM1',
    'baudRate' => 115200,
];
```

### Available Options

| Option | Type | Default | Description |
|--------|------|---------|-------------|
| `ip` | string | `'192.168.1.222'` | Controller IP address |
| `port` | int | `5200` | Network port |
| `cardId` | int | `1` | Controller card ID (1-255) |
| `networkIdCode` | int | `0xFFFFFFFF` | Network identification code |
| `timeout` | int | `5000` | Communication timeout (ms) |
| `retries` | int | `3` | Connection retry attempts |
| `communicationType` | string | `'network'` | Communication type ('network' or 'serial') |
| `serialPort` | string | `'COM1'` | Serial port path |
| `baudRate` | int | `115200` | Serial communication speed |

## Error Handling

The SDK uses typed exceptions for different error conditions:

```php
use LEDController\Exception\ConnectionException;
use LEDController\Exception\CommunicationException;
use LEDController\Exception\ValidationException;
use LEDController\Exception\FileNotFoundException;

try {
    $controller->connect();
    $controller->displayText('Hello World!');
} catch (ConnectionException $e) {
    echo "Connection failed: " . $e->getMessage();
} catch (CommunicationException $e) {
    echo "Communication error: " . $e->getMessage();
} catch (ValidationException $e) {
    echo "Validation error: " . $e->getMessage();
}
```

## Testing

The SDK includes comprehensive tests:

```bash
# Run all tests
composer test

# Run tests with coverage
composer test:coverage

# Run static analysis
composer phpstan

# Run code style checks
composer cs:check

# Fix code style issues
composer cs:fix
```

## Examples

Check the `examples/` directory for complete working examples:

- `01_basic_usage.php` - Basic text display, effects, and unified displayText() method
- `02_multi_window_display.php` - Multi-window layouts
- `03_table.php` - Table display with formatting
- `04_clock_and_temperature.php` - Clock and temperature display
- `05_table_layout_helper.php` - Table layout helper demonstration

## API Documentation

For detailed API documentation, see the `docs/` directory:

- [Basic Protocol](docs/BasicProtocol.md)
- [Communication Protocol](docs/CommunicationProtocolForSetup.md)
- [External Calls Protocol](docs/ExternalCallsCommunicationProtocol.md)
- [LED Controller SDK](docs/LEDController_SDK.md)

## Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

Please ensure:
- All tests pass (`composer test`)
- Code follows PSR-12 standards (`composer cs:check`)
- Static analysis passes (`composer phpstan`)
- Add tests for new features

## Changelog

See [CHANGELOG.md](CHANGELOG.md) for details on version history.

## Security

If you discover any security-related issues, please email arturaz@gmail.com instead of using the issue tracker.

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## Support

- 📧 Email: arturaz@gmail.com
- 🐛 Issues: [GitHub Issues](https://github.com/arturas88/led-controller-sdk/issues)
- 📖 Documentation: [GitHub Wiki](https://github.com/arturas88/led-controller-sdk/wiki)
- 💰 Sponsor: [GitHub Sponsors](https://github.com/sponsors/arturas88)

## Acknowledgments

- Thanks to Shenzhen Lumen Electronics Co., Ltd. for the original C-Power5200 controller specifications
- PHP community for excellent testing and quality assurance tools
- All contributors who help improve this SDK

---

**Made with ❤️ by [arturas88](https://github.com/arturas88)**