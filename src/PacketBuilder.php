<?php

namespace LEDController;

use LEDController\Enum\Effect;
use LEDController\Enum\Alignment;
use LEDController\Enum\VerticalAlignment;
use LEDController\Enum\FontSize;
use LEDController\Enum\Color;
use LEDController\Enum\ImageMode;
use LEDController\Enum\Protocol;

/**
 * Packet builder for creating protocol packets
 */
class PacketBuilder
{
    /**
     * Create hardware restart packet
     */
    public static function createHardwareRestartPacket(int $cardId): Packet
    {
        $packet = new Packet($cardId, 0x2D);
        $packet->setData(chr(0x00));
        return $packet;
    }

    /**
     * Create app restart packet
     */
    public static function createAppRestartPacket(int $cardId): Packet
    {
        $packet = new Packet($cardId, 0xFE);
        $packet->setData('APP!');
        return $packet;
    }

    /**
     * Create time query packet
     */
    public static function createTimeQueryPacket(int $cardId): Packet
    {
        $packet = new Packet($cardId, 0x47);
        $packet->setData(chr(0x01));
        return $packet;
    }

    /**
     * Create time set packet
     */
    public static function createTimeSetPacket(int $cardId, \DateTime $dateTime): Packet
    {
        $packet = new Packet($cardId, 0x47);

        $data = chr(0x00); // Set time command
        $data .= chr((int)$dateTime->format('s')); // Second
        $data .= chr((int)$dateTime->format('i')); // Minute
        $data .= chr((int)$dateTime->format('H')); // Hour
        $data .= chr((int)$dateTime->format('w')); // Weekday (0=Sunday)
        $data .= chr((int)$dateTime->format('j')); // Day
        $data .= chr((int)$dateTime->format('n')); // Month
        $data .= chr((int)$dateTime->format('y')); // Year (2-digit)

        $packet->setData($data);
        return $packet;
    }

    /**
     * Create brightness query packet
     */
    public static function createBrightnessQueryPacket(int $cardId): Packet
    {
        $packet = new Packet($cardId, 0x46);
        $packet->setData(chr(0x01));
        return $packet;
    }

    /**
     * Create brightness set packet
     */
    public static function createBrightnessSetPacket(int $cardId, int $brightness, int $hour = -1): Packet
    {
        $packet = new Packet($cardId, 0x46);

        $data = chr(0x00); // Set brightness command

        // Set brightness for all 24 hours
        for ($h = 0; $h < 24; $h++) {
            if ($hour === -1 || $hour === $h) {
                $data .= chr($brightness);
            } else {
                // Keep current value (we'd need to query first for partial updates)
                $data .= chr($brightness); // For simplicity, set all hours
            }
        }

        $packet->setData($data);
        return $packet;
    }

    /**
     * Create version query packet
     */
    public static function createVersionQueryPacket(int $cardId): Packet
    {
        $packet = new Packet($cardId, 0x2E);
        $packet->setData(chr(0x01)); // Should be 0x01 for query according to protocol docs
        return $packet;
    }

    /**
     * Create temperature query packet
     */
    public static function createTemperatureQueryPacket(int $cardId): Packet
    {
        $packet = new Packet($cardId, 0x75);
        $packet->setData(chr(0x03)); // Query both temperature and humidity
        return $packet;
    }

    /**
     * Create disk space query packet
     */
    public static function createDiskSpaceQueryPacket(int $cardId): Packet
    {
        $packet = new Packet($cardId, 0x29);
        $packet->setData(chr(0x01)); // User disk
        return $packet;
    }

    /**
     * Create file remove packet
     */
    public static function createFileRemovePacket(int $cardId, string $filename): Packet
    {
        $packet = new Packet($cardId, 0x2C);
        $packet->setData($filename . chr(0x00));
        return $packet;
    }

    /**
     * Create split screen packet
     */
    public static function createSplitScreenPacket(int $cardId, array $windows): Packet
    {
        $packet = new Packet($cardId, 0x7B);
        $packet->setSubCommand(0x01);

        $data = chr(count($windows)); // Number of windows

        foreach ($windows as $window) {
            $data .= pack('n', $window['x']); // Big-endian
            $data .= pack('n', $window['y']);
            $data .= pack('n', $window['width']);
            $data .= pack('n', $window['height']);
        }

        $packet->setData($data);
        return $packet;
    }

    /**
     * Create text display packet
     */
    public static function createTextPacket(int $cardId, int $windowNo, string $text, array $options = []): Packet
    {
        $packet = new Packet($cardId, 0x7B);
        $packet->setSubCommand(0x02);

        $data = chr($windowNo);
        $data .= chr(self::convertEffectToInt($options['effect'] ?? Effect::DRAW));
        $data .= chr(self::convertAlignToInt($options['align'] ?? Alignment::LEFT));
        $data .= chr($options['speed'] ?? 5);
        $data .= pack('n', $options['stay'] ?? 10); // Big-endian

        // Convert text to Rich3 format
        $fontSize = self::convertFontSizeToInt($options['font'] ?? FontSize::FONT_16);
        $color = self::convertColorToInt($options['color'] ?? Color::RED);

        $richText = self::textToRich3($text, $fontSize, $color);
        $data .= $richText;

        $packet->setData($data);
        return $packet;
    }

    /**
     * Create pure text packet (0x12)
     */
    public static function createPureTextPacket(int $cardId, int $windowNo, string $text, array $options = []): Packet
    {
        $packet = new Packet($cardId, 0x7B);
        $packet->setSubCommand(0x12);

        $data = chr($windowNo);
        $data .= chr(self::convertEffectToInt($options['effect'] ?? Effect::DRAW));

        // Alignment (horizontal and vertical)
        $align = self::convertAlignToInt($options['align'] ?? Alignment::LEFT) & 0x03;
        $valign = (self::convertVerticalAlignToInt($options['valign'] ?? VerticalAlignment::TOP) & 0x03) << 2;
        $data .= chr($align | $valign);

        $data .= chr($options['speed'] ?? 5);
        $data .= pack('n', $options['stay'] ?? 10); // Big-endian

        // Font - According to documentation: 1 byte with bit0-3 for size, bit4-6 for style
        $fontSize = self::convertFontSizeToInt($options['font'] ?? FontSize::FONT_16);
        $fontStyle = ($options['fontStyle'] ?? 0) & 0x07; // 3 bits max
        $fontByte = ($fontSize & 0x0F) | (($fontStyle & 0x07) << 4);
        $data .= chr($fontByte);

        // Color - According to documentation: 3 separate bytes (R, G, B) - NO 0x77 marker
        // Universal color support: hex strings, RGB arrays, or color constants
        $color = $options['color'] ?? Color::RED;
        $rgbColor = Color::convert($color);

        // Handle both Color enum and RGB array
        if ($rgbColor instanceof Color) {
            $rgb = $rgbColor->toRgb();
        } else {
            $rgb = $rgbColor;
        }

        $data .= chr($rgb['r']);
        $data .= chr($rgb['g']);
        $data .= chr($rgb['b']);

        // Add text directly as-is
        $data .= $text;
        $data .= chr(0x00); // Null terminator

        $packet->setData($data);
        return $packet;
    }

    /**
     * Create image display packet
     */
    public static function createImagePacket(int $cardId, int $windowNo, string $imageData, array $options = []): Packet
    {
        $packet = new Packet($cardId, 0x7B);
        $packet->setSubCommand(0x03);

        $data = chr($windowNo);
        $data .= chr(self::convertEffectToInt($options['effect'] ?? Effect::DRAW));
        $data .= chr(self::convertImageModeToInt($options['mode'] ?? ImageMode::CENTER));
        $data .= chr($options['speed'] ?? 5);
        $data .= pack('n', $options['stay'] ?? 10); // Big-endian
        $data .= pack('n', $options['x'] ?? 0); // X position
        $data .= pack('n', $options['y'] ?? 0); // Y position

        // Add image data
        $data .= $imageData;

        $packet->setData($data);
        return $packet;
    }

    /**
     * Create clock display packet
     *
     * Based on External Calls Protocol CC=0x05 (Send Clock)
     * Format: Window NO | Stay time | Calendar | Format | Content | Font | Color R | Color G | Color B
     */
    public static function createClockPacket(int $cardId, int $windowNo, array $options = []): Packet
    {
        $packet = new Packet($cardId, 0x7B);
        $packet->setSubCommand(0x05);

        $data = chr($windowNo);

        // Stay time (2 bytes, big-endian)
        $stayTime = $options['stay'] ?? 10;
        $data .= pack('n', $stayTime);

        // Calendar type (1 byte)
        $calendar = $options['calendar'] ?? 0; // 0: Gregorian calendar
        $data .= chr($calendar);

        // Clock format (1 byte)
        $format = 0;
        if (($options['format'] ?? Protocol::CLOCK_24_HOUR) === Protocol::CLOCK_12_HOUR) {
            $format |= 0x01; // bit 0: 12-hour format
        }
        if (($options['yearFormat'] ?? Protocol::CLOCK_YEAR_4_DIGIT) === Protocol::CLOCK_YEAR_2_DIGIT) {
            $format |= 0x02; // bit 1: 2-digit year
        }
        if (($options['rowFormat'] ?? Protocol::CLOCK_SINGLE_ROW) === Protocol::CLOCK_MULTI_ROW) {
            $format |= 0x04; // bit 2: multi-row
        }
        if ($options['showTimeScale'] ?? false) {
            $format |= 0x40; // bit 6: show time scale
        }

        $data .= chr($format);

        // Content selection (1 byte)
        $content = $options['content'] ?? (Protocol::CLOCK_SHOW_HOUR | Protocol::CLOCK_SHOW_MINUTE);
        $data .= chr($content);

        // Font (1 byte)
        $fontSize = self::convertFontSizeToInt($options['font'] ?? FontSize::FONT_16);
        $data .= chr($fontSize);

        // Color - Universal color support: hex strings, RGB arrays, or color constants
        $color = $options['color'] ?? Color::RED;
        $rgbColor = Color::convert($color);

        // Handle both Color enum and RGB array
        if ($rgbColor instanceof Color) {
            $rgb = $rgbColor->toRgb();
        } else {
            $rgb = $rgbColor;
        }

        $data .= chr($rgb['r']);
        $data .= chr($rgb['g']);
        $data .= chr($rgb['b']);

        $packet->setData($data);
        return $packet;
    }

    /**
     * Create save data packet
     */
    public static function createSaveDataPacket(int $cardId, bool $save = true): Packet
    {
        $packet = new Packet($cardId, 0x7B);
        $packet->setSubCommand(0x06);

        $data = chr($save ? 0x01 : 0x00);
        $packet->setData($data);
        return $packet;
    }

    /**
     * Create exit split screen packet
     */
    public static function createExitSplitScreenPacket(int $cardId): Packet
    {
        $packet = new Packet($cardId, 0x7B);
        $packet->setSubCommand(0x07);

        $packet->setData('');
        return $packet;
    }

    /**
     * Create network query packet
     */
    public static function createNetworkQueryPacket(int $cardId): Packet
    {
        $packet = new Packet($cardId, 0x3C);
        $packet->setData(chr(0x01));
        return $packet;
    }

    /**
     * Create network set packet
     */
    public static function createNetworkSetPacket(int $cardId, array $config): Packet
    {
        $packet = new Packet($cardId, 0x3C);

        $data = chr(0x00); // Set command

        // IP address
        $ipParts = explode('.', $config['ip']);
        for ($i = 0; $i < 4; $i++) {
            $data .= chr(intval($ipParts[$i] ?? 0));
        }

        // Gateway
        $gatewayParts = explode('.', $config['gateway']);
        for ($i = 0; $i < 4; $i++) {
            $data .= chr(intval($gatewayParts[$i] ?? 0));
        }

        // Subnet mask
        $subnetParts = explode('.', $config['subnet']);
        for ($i = 0; $i < 4; $i++) {
            $data .= chr(intval($subnetParts[$i] ?? 0));
        }

        // Port
        $data .= pack('n', $config['port'] ?? 5200); // Big-endian

        // Network ID
        $data .= pack('N', $config['networkId'] ?? 0xFFFFFFFF); // Big-endian

        $packet->setData($data);
        return $packet;
    }

    /**
     * Convert text to Rich3 format
     */
    private static function textToRich3(string $text, int $fontSize, int $color): string
    {
        $rich3 = '';

        // Color and size byte
        $colorAndSize = ($color << 4) | ($fontSize & 0x0F);

        // Convert text to Rich3 format
        $length = mb_strlen($text, 'UTF-8');
        for ($i = 0; $i < $length; $i++) {
            $char = mb_substr($text, $i, 1, 'UTF-8');

            // Handle newlines
            if ($char === "\n") {
                $char = "\r";
            }

            $rich3 .= chr($colorAndSize);

            // For ASCII characters
            if (ord($char) < 128) {
                $rich3 .= chr(0x00); // High byte
                $rich3 .= $char;     // Low byte (ASCII)
            } else {
                // For multi-byte characters that passed text processing
                $bytes = mb_convert_encoding($char, 'GBK', 'UTF-8');
                if (strlen($bytes) === 2) {
                    $rich3 .= $bytes[0]; // High byte
                    $rich3 .= $bytes[1]; // Low byte
                } else {
                    // This should be rare after text processing
                    $rich3 .= chr(0x00);
                    $rich3 .= '?';
                }
            }
        }

        return $rich3;
    }

    /**
     * Convert effect value to integer
     */
    private static function convertEffectToInt(mixed $effect): int
    {
        if ($effect instanceof Effect) {
            return $effect->value;
        }

        return (int) $effect;
    }

    /**
     * Convert alignment value to integer
     */
    private static function convertAlignToInt(mixed $align): int
    {
        if ($align instanceof Alignment) {
            return $align->value;
        }

        return (int) $align;
    }

    /**
     * Convert vertical alignment value to integer
     */
    private static function convertVerticalAlignToInt(mixed $valign): int
    {
        if ($valign instanceof VerticalAlignment) {
            return $valign->value;
        }

        return (int) $valign;
    }

    /**
     * Convert font size value to integer
     */
    private static function convertFontSizeToInt(mixed $fontSize): int
    {
        if ($fontSize instanceof FontSize) {
            return $fontSize->value;
        }

        return (int) $fontSize;
    }

    /**
     * Convert color value to integer
     */
    private static function convertColorToInt(mixed $color): int
    {
        if ($color instanceof Color) {
            return $color->value;
        }

        return (int) $color;
    }

    /**
     * Convert image mode value to integer
     */
    private static function convertImageModeToInt(mixed $imageMode): int
    {
        if ($imageMode instanceof ImageMode) {
            return $imageMode->value;
        }

        return (int) $imageMode;
    }
}
