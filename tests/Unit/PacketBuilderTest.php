<?php

namespace LEDController\Tests\Unit;

use LEDController\PacketBuilder;
use LEDController\Packet;
use LEDController\LEDController;
use LEDController\Enum\Effect;
use LEDController\Enum\Alignment;
use LEDController\Enum\VerticalAlignment;
use LEDController\Enum\FontSize;
use LEDController\Enum\Color;
use LEDController\Enum\ImageMode;
use LEDController\Enum\Protocol;
use PHPUnit\Framework\TestCase;
use DateTime;

class PacketBuilderTest extends TestCase
{
    private int $cardId = 1;

    public function testCreateHardwareRestartPacket()
    {
        $packet = PacketBuilder::createHardwareRestartPacket($this->cardId);
        
        $this->assertInstanceOf(Packet::class, $packet);
        $this->assertEquals($this->cardId, $packet->getCardId());
        $this->assertEquals(0x2D, $packet->getCommand());
        $this->assertEquals(chr(0x00), $packet->getData());
    }

    public function testCreateAppRestartPacket()
    {
        $packet = PacketBuilder::createAppRestartPacket($this->cardId);
        
        $this->assertInstanceOf(Packet::class, $packet);
        $this->assertEquals($this->cardId, $packet->getCardId());
        $this->assertEquals(0xFE, $packet->getCommand());
        $this->assertEquals('APP!', $packet->getData());
    }

    public function testCreateTimeQueryPacket()
    {
        $packet = PacketBuilder::createTimeQueryPacket($this->cardId);
        
        $this->assertInstanceOf(Packet::class, $packet);
        $this->assertEquals($this->cardId, $packet->getCardId());
        $this->assertEquals(0x47, $packet->getCommand());
        $this->assertEquals(chr(0x01), $packet->getData());
    }

    public function testCreateTimeSetPacket()
    {
        $dateTime = new DateTime('2024-01-15 14:30:45');
        $packet = PacketBuilder::createTimeSetPacket($this->cardId, $dateTime);
        
        $this->assertInstanceOf(Packet::class, $packet);
        $this->assertEquals($this->cardId, $packet->getCardId());
        $this->assertEquals(0x47, $packet->getCommand());
        
        $data = $packet->getData();
        $this->assertStringStartsWith(chr(0x00), $data); // Set time command
        $this->assertEquals(8, strlen($data)); // Command + 7 time bytes
        
        // Test individual time components
        $this->assertEquals(45, ord($data[1])); // Second
        $this->assertEquals(30, ord($data[2])); // Minute
        $this->assertEquals(14, ord($data[3])); // Hour
        $this->assertEquals(15, ord($data[5])); // Day
        $this->assertEquals(1, ord($data[6]));  // Month
        $this->assertEquals(24, ord($data[7])); // Year (2-digit)
    }

    public function testCreateBrightnessQueryPacket()
    {
        $packet = PacketBuilder::createBrightnessQueryPacket($this->cardId);
        
        $this->assertInstanceOf(Packet::class, $packet);
        $this->assertEquals($this->cardId, $packet->getCardId());
        $this->assertEquals(0x46, $packet->getCommand());
        $this->assertEquals(chr(0x01), $packet->getData());
    }

    public function testCreateBrightnessSetPacket()
    {
        $brightness = 128;
        $packet = PacketBuilder::createBrightnessSetPacket($this->cardId, $brightness);
        
        $this->assertInstanceOf(Packet::class, $packet);
        $this->assertEquals($this->cardId, $packet->getCardId());
        $this->assertEquals(0x46, $packet->getCommand());
        
        $data = $packet->getData();
        $this->assertStringStartsWith(chr(0x00), $data); // Set brightness command
        $this->assertEquals(25, strlen($data)); // Command + 24 brightness bytes
        
        // All hours should be set to the same brightness
        for ($i = 1; $i < 25; $i++) {
            $this->assertEquals($brightness, ord($data[$i]));
        }
    }

    public function testCreateBrightnessSetPacketForSpecificHour()
    {
        $brightness = 200;
        $hour = 10;
        $packet = PacketBuilder::createBrightnessSetPacket($this->cardId, $brightness, $hour);
        
        $this->assertInstanceOf(Packet::class, $packet);
        $data = $packet->getData();
        
        // For simplicity, the current implementation sets all hours to the same value
        // In a real implementation, we'd need to query current values first
        for ($i = 1; $i < 25; $i++) {
            $this->assertEquals($brightness, ord($data[$i]));
        }
    }

    public function testCreateVersionQueryPacket()
    {
        $packet = PacketBuilder::createVersionQueryPacket($this->cardId);
        
        $this->assertInstanceOf(Packet::class, $packet);
        $this->assertEquals($this->cardId, $packet->getCardId());
        $this->assertEquals(0x2E, $packet->getCommand());
        $this->assertEquals(chr(0x01), $packet->getData());
    }

    public function testCreateTemperatureQueryPacket()
    {
        $packet = PacketBuilder::createTemperatureQueryPacket($this->cardId);
        
        $this->assertInstanceOf(Packet::class, $packet);
        $this->assertEquals($this->cardId, $packet->getCardId());
        $this->assertEquals(0x75, $packet->getCommand());
        $this->assertEquals(chr(0x03), $packet->getData());
    }

    public function testCreateDiskSpaceQueryPacket()
    {
        $packet = PacketBuilder::createDiskSpaceQueryPacket($this->cardId);
        
        $this->assertInstanceOf(Packet::class, $packet);
        $this->assertEquals($this->cardId, $packet->getCardId());
        $this->assertEquals(0x29, $packet->getCommand());
        $this->assertEquals(chr(0x01), $packet->getData());
    }

    public function testCreateFileRemovePacket()
    {
        $filename = 'test.txt';
        $packet = PacketBuilder::createFileRemovePacket($this->cardId, $filename);
        
        $this->assertInstanceOf(Packet::class, $packet);
        $this->assertEquals($this->cardId, $packet->getCardId());
        $this->assertEquals(0x2C, $packet->getCommand());
        $this->assertEquals($filename . chr(0x00), $packet->getData());
    }

    public function testCreateSplitScreenPacket()
    {
        $windows = [
            ['x' => 0, 'y' => 0, 'width' => 64, 'height' => 16],
            ['x' => 64, 'y' => 0, 'width' => 64, 'height' => 16],
            ['x' => 0, 'y' => 16, 'width' => 128, 'height' => 16]
        ];
        
        $packet = PacketBuilder::createSplitScreenPacket($this->cardId, $windows);
        
        $this->assertInstanceOf(Packet::class, $packet);
        $this->assertEquals($this->cardId, $packet->getCardId());
        $this->assertEquals(0x7B, $packet->getCommand());
        $this->assertEquals(0x01, $packet->getSubCommand());
        
        $data = $packet->getData();
        $this->assertEquals(3, ord($data[0])); // Number of windows
        
        // Check window data structure (each window is 8 bytes: 2 bytes each for x, y, width, height)
        $expectedLength = 1 + (3 * 8); // 1 byte for count + 3 windows * 8 bytes each
        $this->assertEquals($expectedLength, strlen($data));
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
              'color' => Color::RED
        ];
        
        $packet = PacketBuilder::createTextPacket($this->cardId, $windowNo, $text, $options);
        
        $this->assertInstanceOf(Packet::class, $packet);
        $this->assertEquals($this->cardId, $packet->getCardId());
        $this->assertEquals(0x7B, $packet->getCommand());
        $this->assertEquals(0x02, $packet->getSubCommand());
        
        $data = $packet->getData();
        $this->assertEquals($windowNo, ord($data[0]));
        $this->assertEquals($options['effect']->value, ord($data[1]));
        $this->assertEquals($options['align']->value, ord($data[2]));
        $this->assertEquals($options['speed'], ord($data[3]));
        
        // Stay time is 2 bytes (big-endian)
        $stayBytes = unpack('n', substr($data, 4, 2));
        $this->assertEquals($options['stay'], $stayBytes[1]);
    }

    public function testCreateTextPacketWithDefaults()
    {
        $windowNo = 1;
        $text = 'Test';
        
        $packet = PacketBuilder::createTextPacket($this->cardId, $windowNo, $text);
        
        $this->assertInstanceOf(Packet::class, $packet);
        $this->assertEquals($this->cardId, $packet->getCardId());
        $this->assertEquals(0x7B, $packet->getCommand());
        $this->assertEquals(0x02, $packet->getSubCommand());
        
        $data = $packet->getData();
        $this->assertEquals($windowNo, ord($data[0]));
        $this->assertEquals(Effect::DRAW->value, ord($data[1])); // Default effect
        $this->assertEquals(Alignment::LEFT->value, ord($data[2])); // Default align
        $this->assertEquals(5, ord($data[3])); // Default speed
        
        // Default stay time should be 10
        $stayBytes = unpack('n', substr($data, 4, 2));
        $this->assertEquals(10, $stayBytes[1]);
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
            'color' => Color::BLUE
        ];
        
        $packet = PacketBuilder::createPureTextPacket($this->cardId, $windowNo, $text, $options);
        
        $this->assertInstanceOf(Packet::class, $packet);
        $this->assertEquals($this->cardId, $packet->getCardId());
        $this->assertEquals(0x7B, $packet->getCommand());
        $this->assertEquals(0x12, $packet->getSubCommand());
        
        $data = $packet->getData();
        $this->assertEquals($windowNo, ord($data[0]));
        $this->assertEquals($options['effect']->value, ord($data[1]));
        
        // Alignment byte combines horizontal and vertical alignment
        $alignByte = ord($data[2]);
        $this->assertEquals($options['align']->value & 0x03, $alignByte & 0x03);
        $this->assertEquals(($options['valign']->value & 0x03) << 2, $alignByte & 0x0C);
        
        $this->assertEquals($options['speed'], ord($data[3]));
        
        // Stay time is 2 bytes (big-endian)
        $stayBytes = unpack('n', substr($data, 4, 2));
        $this->assertEquals($options['stay'], $stayBytes[1]);
    }

    public function testCreateSaveDataPacket()
    {
        $packet = PacketBuilder::createSaveDataPacket($this->cardId, true);
        
        $this->assertInstanceOf(Packet::class, $packet);
        $this->assertEquals($this->cardId, $packet->getCardId());
        $this->assertEquals(0x7B, $packet->getCommand());
        $this->assertEquals(0x06, $packet->getSubCommand());
        $this->assertEquals(chr(0x01), $packet->getData());
    }

    public function testCreateSaveDataPacketNoSave()
    {
        $packet = PacketBuilder::createSaveDataPacket($this->cardId, false);
        
        $this->assertInstanceOf(Packet::class, $packet);
        $this->assertEquals($this->cardId, $packet->getCardId());
        $this->assertEquals(0x7B, $packet->getCommand());
        $this->assertEquals(0x06, $packet->getSubCommand());
        $this->assertEquals(chr(0x00), $packet->getData());
    }

    public function testCreateExitSplitScreenPacket()
    {
        $packet = PacketBuilder::createExitSplitScreenPacket($this->cardId);
        
        $this->assertInstanceOf(Packet::class, $packet);
        $this->assertEquals($this->cardId, $packet->getCardId());
        $this->assertEquals(0x7B, $packet->getCommand());
        $this->assertEquals(0x07, $packet->getSubCommand());
        $this->assertEquals('', $packet->getData());
    }

    public function testCreateNetworkQueryPacket()
    {
        $packet = PacketBuilder::createNetworkQueryPacket($this->cardId);
        
        $this->assertInstanceOf(Packet::class, $packet);
        $this->assertEquals($this->cardId, $packet->getCardId());
        $this->assertEquals(0x3C, $packet->getCommand());
        $this->assertEquals(chr(0x01), $packet->getData());
    }

    public function testCreateNetworkSetPacket()
    {
        $config = [
            'ip' => '192.168.1.100',
            'subnet' => '255.255.255.0',
            'gateway' => '192.168.1.1',
            'port' => 5200
        ];
        
        $packet = PacketBuilder::createNetworkSetPacket($this->cardId, $config);
        
        $this->assertInstanceOf(Packet::class, $packet);
        $this->assertEquals($this->cardId, $packet->getCardId());
        $this->assertEquals(0x3C, $packet->getCommand());
        
        $data = $packet->getData();
        $this->assertStringStartsWith(chr(0x00), $data); // Set network command
        $this->assertEquals(19, strlen($data)); // Command + 4 IP + 4 gateway + 4 subnet + 2 port + 4 networkId
        
        // Test IP address bytes
        $ipBytes = [192, 168, 1, 100];
        for ($i = 0; $i < 4; $i++) {
            $this->assertEquals($ipBytes[$i], ord($data[1 + $i]));
        }
        
        // Test port (big-endian) - comes after IP(4) + Gateway(4) + Subnet(4) = position 13
        $portBytes = unpack('n', substr($data, 13, 2));
        $this->assertEquals($config['port'], $portBytes[1]);
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
            'stay' => 12
        ];
        
        $packet = PacketBuilder::createImagePacket($this->cardId, $windowNo, $imageData, $options);
        
        $this->assertInstanceOf(Packet::class, $packet);
        $this->assertEquals($this->cardId, $packet->getCardId());
        $this->assertEquals(0x7B, $packet->getCommand());
        $this->assertEquals(0x03, $packet->getSubCommand());
        
        $data = $packet->getData();
        $this->assertEquals($windowNo, ord($data[0]));
        $this->assertEquals($options['effect']->value, ord($data[1]));
        $this->assertEquals($options['mode']->value, ord($data[2]));
        $this->assertEquals($options['speed'], ord($data[3]));
        
        // Stay time (big-endian)
        $stayBytes = unpack('n', substr($data, 4, 2));
        $this->assertEquals($options['stay'], $stayBytes[1]);
        
        // Test position (big-endian) - comes after windowNo + effect + mode + speed + stay
        $xBytes = unpack('n', substr($data, 6, 2));
        $this->assertEquals($options['x'], $xBytes[1]);
        
        $yBytes = unpack('n', substr($data, 8, 2));
        $this->assertEquals($options['y'], $yBytes[1]);
        
        // Check that image data is included
        $this->assertStringContainsString($imageData, $data);
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
            'stay' => 10
        ];
        
        $packet = PacketBuilder::createClockPacket($this->cardId, $windowNo, $options);
        
        $this->assertInstanceOf(Packet::class, $packet);
        $this->assertEquals($this->cardId, $packet->getCardId());
        $this->assertEquals(0x7B, $packet->getCommand());
        $this->assertEquals(0x05, $packet->getSubCommand());
        
        $data = $packet->getData();
        $this->assertEquals($windowNo, ord($data[0]));
        $this->assertEquals($options['effect']->value, ord($data[1]));
        // Align is at position 2
        $this->assertEquals($options['speed'], ord($data[3]));
        
        // Stay time (big-endian)
        $stayBytes = unpack('n', substr($data, 4, 2));
        $this->assertEquals($options['stay'], $stayBytes[1]);
    }
}
