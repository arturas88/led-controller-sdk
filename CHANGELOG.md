# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
- Initial release preparation
- Package metadata for GitHub publishing

## [1.0.0] - 2025-07-15

### Added
- Modern PHP 8.0+ LED Controller SDK
- Network (TCP/IP) and Serial (RS232/RS485) communication support
- Fluent interface for easy API usage
- Comprehensive manager system for different functionality areas
- Type-safe enums for all constants and options
- Multi-window display support
- Clock and temperature sensor management
- File upload/download operations
- Advanced text processing with Unicode support
- Template and program building system
- Schedule management system
- External calls for advanced controller operations
- Comprehensive exception handling
- Built-in logging system
- Extensive test coverage
- Code quality tools integration (PHPStan, PHP CS Fixer, CodeSniffer)
- GitHub Actions CI/CD pipeline
- Comprehensive documentation and examples

### Features
- **LEDController**: Main controller class with fluent interface
- **Manager System**: Specialized managers for different operations
  - TemplateManager: Template creation and management
  - FileManager: File operations with progress tracking
  - ScheduleManager: Program scheduling and timing
  - ConfigManager: Configuration management
  - SetupManager: Network and serial setup
  - ExternalCallsManager: Advanced controller operations
  - ClockManager: Clock display with various formats
  - TemperatureManager: Temperature sensor reading
- **Communication**: Dual protocol support
  - NetworkCommunication: TCP/IP communication
  - SerialCommunication: RS232/RS485 communication
- **Text Processing**: Advanced text handling
  - Unicode support with automatic transliteration
  - Text-to-image conversion for complex characters
  - Multiple text processing modes
- **Builders**: Fluent builders for complex operations
  - PacketBuilder: Low-level packet construction
  - ProgramBuilder: Program creation with multiple elements
  - ScheduleBuilder: Schedule planning and management
- **Enums**: Type-safe constants
  - FontSize, Color, Effect, Alignment
  - Protocol, Command, ReturnCode
  - WindowType, ImageMode, TextProcessorMode
- **Exception Handling**: Comprehensive error handling
  - ConnectionException, CommunicationException
  - ValidationException, FileNotFoundException
  - ConfigException, ScheduleException
  - And more specific exceptions

### Documentation
- Comprehensive README with usage examples
- API documentation in docs/ directory
- Working examples in examples/ directory
- Protocol documentation from original SDK

### Development
- PHP 8.0+ modern codebase
- PSR-4 autoloading
- PSR-12 coding standards
- PHPUnit test suite with coverage reporting
- PHPStan static analysis
- PHP CS Fixer code formatting
- GitHub Actions workflow for CI/CD
- Automated documentation generation

[Unreleased]: https://github.com/arturas88/led-controller-sdk/compare/v1.0.0...HEAD
[1.0.0]: https://github.com/arturas88/led-controller-sdk/releases/tag/v1.0.0 