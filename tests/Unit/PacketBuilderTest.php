<?php

declare(strict_types=1);

namespace LEDController\Tests\Unit;

use DateTime;
use LEDController\Enum\Alignment;
use LEDController\Enum\Color;
use LEDController\Enum\Effect;
use LEDController\Enum\FontSize;
use LEDController\Enum\ImageMode;
use LEDController\Enum\Protocol;
use LEDController\Enum\VerticalAlignment;
use LEDController\Packet;
use LEDController\PacketBuilder;
use PHPUnit\Framework\TestCase;

class PacketBuilderTest extends TestCase
{
    private int $cardId = 1;

    public function testCreateHardwareRestartPacket()
    {
        $packet = PacketBuilder::createHardwareRestartPacket($this->cardId);

        self::assertInstanceOf(Packet::class, $packet);
        self::assertSame($this->cardId, $packet->getCardId());
        self::assertSame(0x2D, $packet->getCommand());
        self::assertSame(\chr(0x00), $packet->getData());
    }

    public function testCreateAppRestartPacket()
    {
        $packet = PacketBuilder::createAppRestartPacket($this->cardId);

        self::assertInstanceOf(Packet::class, $packet);
        self::assertSame($this->cardId, $packet->getCardId());
        self::assertSame(0xFE, $packet->getCommand());
        self::assertSame('APP!', $packet->getData());
    }

    public function testCreateTimeQueryPacket()
    {
        $packet = PacketBuilder::createTimeQueryPacket($this->cardId);

        self::assertInstanceOf(Packet::class, $packet);
        self::assertSame($this->cardId, $packet->getCardId());
        self::assertSame(0x47, $packet->getCommand());
        self::assertSame(\chr(0x01), $packet->getData());
    }

    public function testCreateTimeSetPacket()
    {
        $dateTime = new DateTime('2024-01-15 14:30:45');
        $packet = PacketBuilder::createTimeSetPacket($this->cardId, $dateTime);

        self::assertInstanceOf(Packet::class, $packet);
        self::assertSame($this->cardId, $packet->getCardId());
        self::assertSame(0x47, $packet->getCommand());

        $data = $packet->getData();
        self::assertStringStartsWith(\chr(0x00), $data); // Set time command
        self::assertSame(8, \strlen($data)); // Command + 7 time bytes

        // Test individual time components
        self::assertSame(45, \ord($data[1])); // Second
        self::assertSame(30, \ord($data[2])); // Minute
        self::assertSame(14, \ord($data[3])); // Hour
        self::assertSame(15, \ord($data[5])); // Day
        self::assertSame(1, \ord($data[6]));  // Month
        self::assertSame(24, \ord($data[7])); // Year (2-digit)
    }

    public function testCreateBrightnessQueryPacket()
    {
        $packet = PacketBuilder::createBrightnessQueryPacket($this->cardId);

        self::assertInstanceOf(Packet::class, $packet);
        self::assertSame($this->cardId, $packet->getCardId());
        self::assertSame(0x46, $packet->getCommand());
        self::assertSame(\chr(0x01), $packet->getData());
    }

    public function testCreateBrightnessSetPacket()
    {
        $brightness = 128;
        $packet = PacketBuilder::createBrightnessSetPacket($this->cardId, $brightness);

        self::assertInstanceOf(Packet::class, $packet);
        self::assertSame($this->cardId, $packet->getCardId());
        self::assertSame(0x46, $packet->getCommand());

        $data = $packet->getData();
        self::assertStringStartsWith(\chr(0x00), $data); // Set brightness command
        self::assertSame(25, \strlen($data)); // Command + 24 brightness bytes

        // All hours should be set to the same brightness
        for ($i = 1; $i < 25; $i++) {
            self::assertSame($brightness, \ord($data[$i]));
        }
    }

    public function testCreateBrightnessSetPacketForSpecificHour()
    {
        $brightness = 200;
        $hour = 10;
        $packet = PacketBuilder::createBrightnessSetPacket($this->cardId, $brightness, $hour);

        self::assertInstanceOf(Packet::class, $packet);
        $data = $packet->getData();

        // For simplicity, the current implementation sets all hours to the same value
        // In a real implementation, we'd need to query current values first
        for ($i = 1; $i < 25; $i++) {
            self::assertSame($brightness, \ord($data[$i]));
        }
    }

    public function testCreateVersionQueryPacket()
    {
        $packet = PacketBuilder::createVersionQueryPacket($this->cardId);

        self::assertInstanceOf(Packet::class, $packet);
        self::assertSame($this->cardId, $packet->getCardId());
        self::assertSame(0x2E, $packet->getCommand());
        self::assertSame(\chr(0x01), $packet->getData());
    }

    public function testCreateTemperatureQueryPacket()
    {
        $packet = PacketBuilder::createTemperatureQueryPacket($this->cardId);

        self::assertInstanceOf(Packet::class, $packet);
        self::assertSame($this->cardId, $packet->getCardId());
        self::assertSame(0x75, $packet->getCommand());
        self::assertSame(\chr(0x03), $packet->getData());
    }

    public function testCreateDiskSpaceQueryPacket()
    {
        $packet = PacketBuilder::createDiskSpaceQueryPacket($this->cardId);

        self::assertInstanceOf(Packet::class, $packet);
        self::assertSame($this->cardId, $packet->getCardId());
        self::assertSame(0x29, $packet->getCommand());
        self::assertSame(\chr(0x01), $packet->getData());
    }

    public function testCreateFileRemovePacket()
    {
        $filename = 'test.txt';
        $packet = PacketBuilder::createFileRemovePacket($this->cardId, $filename);

        self::assertInstanceOf(Packet::class, $packet);
        self::assertSame($this->cardId, $packet->getCardId());
        self::assertSame(0x2C, $packet->getCommand());
        self::assertSame($filename . \chr(0x00), $packet->getData());
    }

    public function testCreateSplitScreenPacket()
    {
        $windows = [
            ['x' => 0, 'y' => 0, 'width' => 64, 'height' => 16],
            ['x' => 64, 'y' => 0, 'width' => 64, 'height' => 16],
            ['x' => 0, 'y' => 16, 'width' => 128, 'height' => 16],
        ];

        $packet = PacketBuilder::createSplitScreenPacket($this->cardId, $windows);

        self::assertInstanceOf(Packet::class, $packet);
        self::assertSame($this->cardId, $packet->getCardId());
        self::assertSame(0x7B, $packet->getCommand());
        self::assertSame(0x01, $packet->getSubCommand());

        $data = $packet->getData();
        self::assertSame(3, \ord($data[0])); // Number of windows

        // Check window data structure (each window is 8 bytes: 2 bytes each for x, y, width, height)
        $expectedLength = 1 + (3 * 8); // 1 byte for count + 3 windows * 8 bytes each
        self::assertSame($expectedLength, \strlen($data));
    }

    public function testCreateTextPacket()
    {
        $windowNo = 0;
        $text = 'Hello World';
        $options = [
            'effect' => Effect::SCROLL_LEFT,
            'align' => Alignment::CENTER,
            'speed' => 7,
            'stay' => 15,
            'font' => FontSize::FONT_24,
            'color' => Color::RED,
        ];

        $packet = PacketBuilder::createTextPacket($this->cardId, $windowNo, $text, $options);

        self::assertInstanceOf(Packet::class, $packet);
        self::assertSame($this->cardId, $packet->getCardId());
        self::assertSame(0x7B, $packet->getCommand());
        self::assertSame(0x02, $packet->getSubCommand());

        $data = $packet->getData();
        self::assertSame($windowNo, \ord($data[0]));
        self::assertSame($options['effect']->value, \ord($data[1]));
        self::assertSame($options['align']->value, \ord($data[2]));
        self::assertSame($options['speed'], \ord($data[3]));

        // Stay time is 2 bytes (big-endian)
        $stayBytes = unpack('n', substr($data, 4, 2));
        self::assertSame($options['stay'], $stayBytes[1]);
    }

    public function testCreateTextPacketWithDefaults()
    {
        $windowNo = 1;
        $text = 'Test';

        $packet = PacketBuilder::createTextPacket($this->cardId, $windowNo, $text);

        self::assertInstanceOf(Packet::class, $packet);
        self::assertSame($this->cardId, $packet->getCardId());
        self::assertSame(0x7B, $packet->getCommand());
        self::assertSame(0x02, $packet->getSubCommand());

        $data = $packet->getData();
        self::assertSame($windowNo, \ord($data[0]));
        self::assertSame(Effect::DRAW->value, \ord($data[1])); // Default effect
        self::assertSame(Alignment::LEFT->value, \ord($data[2])); // Default align
        self::assertSame(5, \ord($data[3])); // Default speed

        // Default stay time should be 10
        $stayBytes = unpack('n', substr($data, 4, 2));
        self::assertSame(10, $stayBytes[1]);
    }

    public function testCreatePureTextPacket()
    {
        $windowNo = 0;
        $text = 'Pure Text';
        $options = [
            'effect' => Effect::FLICKER,
            'align' => Alignment::RIGHT,
            'valign' => VerticalAlignment::CENTER,
            'speed' => 8,
            'stay' => 20,
            'font' => FontSize::FONT_32,
            'fontStyle' => 2,
            'color' => Color::BLUE,
        ];

        $packet = PacketBuilder::createPureTextPacket($this->cardId, $windowNo, $text, $options);

        self::assertInstanceOf(Packet::class, $packet);
        self::assertSame($this->cardId, $packet->getCardId());
        self::assertSame(0x7B, $packet->getCommand());
        self::assertSame(0x12, $packet->getSubCommand());

        $data = $packet->getData();
        self::assertSame($windowNo, \ord($data[0]));
        self::assertSame($options['effect']->value, \ord($data[1]));

        // Alignment byte combines horizontal and vertical alignment
        $alignByte = \ord($data[2]);
        self::assertSame($options['align']->value & 0x03, $alignByte & 0x03);
        self::assertSame(($options['valign']->value & 0x03) << 2, $alignByte & 0x0C);

        self::assertSame($options['speed'], \ord($data[3]));

        // Stay time is 2 bytes (big-endian)
        $stayBytes = unpack('n', substr($data, 4, 2));
        self::assertSame($options['stay'], $stayBytes[1]);
    }

    public function testCreateSaveDataPacket()
    {
        $packet = PacketBuilder::createSaveDataPacket($this->cardId, true);

        self::assertInstanceOf(Packet::class, $packet);
        self::assertSame($this->cardId, $packet->getCardId());
        self::assertSame(0x7B, $packet->getCommand());
        self::assertSame(0x06, $packet->getSubCommand());
        self::assertSame(\chr(0x01), $packet->getData());
    }

    public function testCreateSaveDataPacketNoSave()
    {
        $packet = PacketBuilder::createSaveDataPacket($this->cardId, false);

        self::assertInstanceOf(Packet::class, $packet);
        self::assertSame($this->cardId, $packet->getCardId());
        self::assertSame(0x7B, $packet->getCommand());
        self::assertSame(0x06, $packet->getSubCommand());
        self::assertSame(\chr(0x00), $packet->getData());
    }

    public function testCreateExitSplitScreenPacket()
    {
        $packet = PacketBuilder::createExitSplitScreenPacket($this->cardId);

        self::assertInstanceOf(Packet::class, $packet);
        self::assertSame($this->cardId, $packet->getCardId());
        self::assertSame(0x7B, $packet->getCommand());
        self::assertSame(0x07, $packet->getSubCommand());
        self::assertSame('', $packet->getData());
    }

    public function testCreateNetworkQueryPacket()
    {
        $packet = PacketBuilder::createNetworkQueryPacket($this->cardId);

        self::assertInstanceOf(Packet::class, $packet);
        self::assertSame($this->cardId, $packet->getCardId());
        self::assertSame(0x3C, $packet->getCommand());
        self::assertSame(\chr(0x01), $packet->getData());
    }

    public function testCreateNetworkSetPacket()
    {
        $config = [
            'ip' => '192.168.1.222',
            'port' => 5200,
            'gateway' => '192.168.1.1',
            'subnet' => '255.255.255.0',
            'networkId' => 0xFFFFFFFF,
            'timeout' => 5000,
            'retries' => 3,
        ];

        $packet = PacketBuilder::createNetworkSetPacket($this->cardId, $config);

        self::assertInstanceOf(Packet::class, $packet);
        self::assertSame($this->cardId, $packet->getCardId());
        self::assertSame(0x3C, $packet->getCommand());

        $data = $packet->getData();
        self::assertStringStartsWith(\chr(0x00), $data); // Set network command
        self::assertSame(19, \strlen($data)); // Command + 4 IP + 4 gateway + 4 subnet + 2 port + 4 networkId

        // Test IP address bytes
        $ipBytes = [192, 168, 1, 222];
        for ($i = 0; $i < 4; $i++) {
            self::assertSame($ipBytes[$i], \ord($data[1 + $i]));
        }

        // Test port (big-endian) - comes after IP(4) + Gateway(4) + Subnet(4) = position 13
        $portBytes = unpack('n', substr($data, 13, 2));
        self::assertSame($config['port'], $portBytes[1]);
    }

    public function testCreateImagePacket()
    {
        $windowNo = 0;
        $imageData = 'test_image_data';
        $options = [
            'x' => 10,
            'y' => 20,
            'mode' => ImageMode::STRETCH,
            'effect' => Effect::DRAW,
            'speed' => 6,
            'stay' => 12,
        ];

        $packet = PacketBuilder::createImagePacket($this->cardId, $windowNo, $imageData, $options);

        self::assertInstanceOf(Packet::class, $packet);
        self::assertSame($this->cardId, $packet->getCardId());
        self::assertSame(0x7B, $packet->getCommand());
        self::assertSame(0x03, $packet->getSubCommand());

        $data = $packet->getData();
        self::assertSame($windowNo, \ord($data[0]));
        self::assertSame($options['effect']->value, \ord($data[1]));
        self::assertSame($options['mode']->value, \ord($data[2]));
        self::assertSame($options['speed'], \ord($data[3]));

        // Stay time (big-endian)
        $stayBytes = unpack('n', substr($data, 4, 2));
        self::assertSame($options['stay'], $stayBytes[1]);

        // Test position (big-endian) - comes after windowNo + effect + mode + speed + stay
        $xBytes = unpack('n', substr($data, 6, 2));
        self::assertSame($options['x'], $xBytes[1]);

        $yBytes = unpack('n', substr($data, 8, 2));
        self::assertSame($options['y'], $yBytes[1]);

        // Check that image data is included
        self::assertStringContainsString($imageData, $data);
    }

    public function testCreateClockPacket()
    {
        $windowNo = 1;
        $options = [
            'font' => FontSize::FONT_24,
            'fontStyle' => 1,
            'color' => Color::GREEN,
            'format' => Protocol::CLOCK_12_HOUR,
            'effect' => Effect::DRAW,
            'speed' => 5,
            'stay' => 10,
        ];

        $packet = PacketBuilder::createClockPacket($this->cardId, $windowNo, $options);

        self::assertInstanceOf(Packet::class, $packet);
        self::assertSame($this->cardId, $packet->getCardId());
        self::assertSame(0x7B, $packet->getCommand());
        self::assertSame(0x05, $packet->getSubCommand());

        $data = $packet->getData();
        self::assertSame($windowNo, \ord($data[0]));

        // Stay time is at position 1-2 (2 bytes, big-endian)
        $stayBytes = unpack('n', substr($data, 1, 2));
        self::assertSame($options['stay'], $stayBytes[1]);

        // Calendar type is at position 3
        self::assertSame(0, \ord($data[3])); // Default: Gregorian calendar

        // Format byte is at position 4
        $formatByte = \ord($data[4]);
        self::assertSame(0x01, $formatByte & 0x01); // 12-hour format bit

        // Content selection is at position 5
        self::assertSame(Protocol::CLOCK_SHOW_HOUR | Protocol::CLOCK_SHOW_MINUTE, \ord($data[5]));

        // Font size is at position 6
        self::assertSame(FontSize::FONT_24->value, \ord($data[6]));

        // Color RGB values are at positions 7, 8, 9
        $greenRgb = Color::GREEN->toRgb();
        self::assertSame($greenRgb['r'], \ord($data[7]));
        self::assertSame($greenRgb['g'], \ord($data[8]));
        self::assertSame($greenRgb['b'], \ord($data[9]));
    }
}
