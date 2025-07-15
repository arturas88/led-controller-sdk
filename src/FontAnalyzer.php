<?php

namespace LEDController;

/**
 * Font Analyzer for .fmd font files
 *
 * This class analyzes LED controller font files (.fmd format) to understand
 * their structure and potentially create custom fonts with extended character support.
 */
class FontAnalyzer
{
    /**
     * Analyze a .fmd font file
     */
    public static function analyzeFmdFile(string $filePath): array
    {
        if (!file_exists($filePath)) {
            throw new \Exception("Font file not found: $filePath");
        }

        $data = file_get_contents($filePath);
        $size = filesize($filePath);

        $analysis = [
            'file_path' => $filePath,
            'file_name' => basename($filePath),
            'file_size' => $size,
            'file_size_human' => self::formatBytes($size),
            'header' => [],
            'structure' => [],
            'character_data' => [],
            'suspected_format' => self::detectFormat($filePath, $data)
        ];

        // Analyze header (first 32 bytes)
        $analysis['header'] = self::analyzeHeader($data);

        // Try to detect character patterns
        $analysis['character_data'] = self::analyzeCharacterData($data);

        // Analyze structure based on file name
        $analysis['structure'] = self::analyzeStructure($filePath, $data);

        return $analysis;
    }

    /**
     * Detect .fmd format based on file name and content
     */
    private static function detectFormat(string $filePath, string $data): array
    {
        $fileName = basename($filePath, '.fmd');
        $format = [
            'type' => 'unknown',
            'size' => null,
            'encoding' => 'unknown',
            'confidence' => 0
        ];

        // Analyze file name patterns
        if (preg_match('/asc(\d+)?/', $fileName, $matches)) {
            $format['type'] = 'ascii';
            $format['encoding'] = 'ascii';
            $format['size'] = isset($matches[1]) ? (int)$matches[1] : null;
            $format['confidence'] = 0.8;
        } elseif (preg_match('/gb(\d+)?/', $fileName, $matches)) {
            $format['type'] = 'chinese';
            $format['encoding'] = 'gb2312';
            $format['size'] = isset($matches[1]) ? (int)$matches[1] : null;
            $format['confidence'] = 0.8;
        } elseif (preg_match('/big(\d+)?/', $fileName, $matches)) {
            $format['type'] = 'extended';
            $format['encoding'] = 'unknown';
            $format['size'] = isset($matches[1]) ? (int)$matches[1] : null;
            $format['confidence'] = 0.6;
        }

        // Analyze content patterns
        $contentAnalysis = self::analyzeContent($data);
        $format['content_analysis'] = $contentAnalysis;

        return $format;
    }

    /**
     * Analyze file header
     */
    private static function analyzeHeader(string $data): array
    {
        $header = [];

        if (strlen($data) < 32) {
            return ['error' => 'File too small for header analysis'];
        }

        // First 32 bytes as hex
        $headerBytes = substr($data, 0, 32);
        $header['hex'] = bin2hex($headerBytes);
        $header['bytes'] = array_values(unpack('C*', $headerBytes));

        // Look for magic numbers or patterns
        $header['magic_bytes'] = substr($data, 0, 4);
        $header['magic_hex'] = bin2hex($header['magic_bytes']);

        // Try to find width/height information
        $header['possible_dimensions'] = self::findDimensions($headerBytes);

        return $header;
    }

    /**
     * Analyze character data patterns
     */
    private static function analyzeCharacterData(string $data): array
    {
        $charData = [];

        // Skip potential header and analyze data patterns
        $dataStart = 32; // Assume 32-byte header
        if (strlen($data) <= $dataStart) {
            return ['error' => 'File too small for character data'];
        }

        $charBytes = substr($data, $dataStart);

        // Analyze byte patterns
        $charData['total_bytes'] = strlen($charBytes);
        $charData['byte_distribution'] = self::getByteDistribution($charBytes);
        $charData['patterns'] = self::findPatterns($charBytes);

        // Estimate character count based on common font sizes
        $charData['estimated_chars'] = self::estimateCharacterCount($charBytes);

        return $charData;
    }

    /**
     * Analyze file structure
     */
    private static function analyzeStructure(string $filePath, string $data): array
    {
        $fileName = basename($filePath, '.fmd');
        $structure = [];

        // Guess structure based on file name
        if (strpos($fileName, 'asc') === 0) {
            $structure = self::analyzeAsciiStructure($data);
        } elseif (strpos($fileName, 'gb') === 0) {
            $structure = self::analyzeGbStructure($data);
        } else {
            $structure = self::analyzeGenericStructure($data);
        }

        return $structure;
    }

    /**
     * Analyze ASCII font structure
     */
    private static function analyzeAsciiStructure(string $data): array
    {
        return [
            'type' => 'ascii_font',
            'expected_chars' => 128, // Standard ASCII
            'char_range' => '0x00-0x7F',
            'estimated_char_size' => strlen($data) / 128,
            'notes' => 'Standard ASCII character set (128 characters)'
        ];
    }

    /**
     * Analyze GB (Chinese) font structure
     */
    private static function analyzeGbStructure(string $data): array
    {
        return [
            'type' => 'gb_font',
            'expected_chars' => 7445, // GB2312 character count
            'char_range' => 'GB2312',
            'estimated_char_size' => strlen($data) / 7445,
            'notes' => 'Chinese GB2312 character set'
        ];
    }

    /**
     * Analyze generic font structure
     */
    private static function analyzeGenericStructure(string $data): array
    {
        return [
            'type' => 'generic_font',
            'estimated_char_size' => self::estimateCharacterSize($data),
            'notes' => 'Unknown font structure'
        ];
    }

    /**
     * Find possible dimensions in header bytes
     */
    private static function findDimensions(string $headerBytes): array
    {
        $dimensions = [];
        $bytes = array_values(unpack('C*', $headerBytes));

        // Look for common font sizes (8, 12, 16, 24, 32, 40, 48, 56)
        $commonSizes = [8, 12, 16, 24, 32, 40, 48, 56];

        foreach ($bytes as $i => $byte) {
            if (in_array($byte, $commonSizes)) {
                $dimensions[] = [
                    'position' => $i,
                    'value' => $byte,
                    'type' => 'possible_font_size'
                ];
            }
        }

        return $dimensions;
    }

    /**
     * Get byte distribution for pattern analysis
     */
    private static function getByteDistribution(string $data): array
    {
        $distribution = array_fill(0, 256, 0);

        for ($i = 0; $i < strlen($data); $i++) {
            $byte = ord($data[$i]);
            $distribution[$byte]++;
        }

        // Find most common bytes
        arsort($distribution);
        $topBytes = array_slice($distribution, 0, 10, true);

        return [
            'most_common' => $topBytes,
            'zero_bytes' => $distribution[0],
            'ff_bytes' => $distribution[255]
        ];
    }

    /**
     * Find repeating patterns
     */
    private static function findPatterns(string $data): array
    {
        $patterns = [];

        // Look for repeating byte sequences
        for ($len = 2; $len <= 16; $len++) {
            $sequences = [];
            for ($i = 0; $i <= strlen($data) - $len; $i++) {
                $seq = substr($data, $i, $len);
                if (!isset($sequences[$seq])) {
                    $sequences[$seq] = 0;
                }
                $sequences[$seq]++;
            }

            // Find most common sequences
            arsort($sequences);
            $topSequences = array_slice($sequences, 0, 3, true);

            foreach ($topSequences as $seq => $count) {
                if ($count > 2) { // Only patterns that repeat
                    $patterns[] = [
                        'length' => $len,
                        'pattern' => bin2hex($seq),
                        'count' => $count
                    ];
                }
            }
        }

        return $patterns;
    }

    /**
     * Estimate character count
     */
    private static function estimateCharacterCount(string $data): array
    {
        $estimates = [];

        // Common character counts for different font types
        $commonCounts = [128, 256, 512, 1024, 7445]; // ASCII, Extended ASCII, etc.

        foreach ($commonCounts as $count) {
            $charSize = strlen($data) / $count;
            $estimates[$count] = [
                'char_size' => $charSize,
                'probability' => self::calculateProbability($charSize)
            ];
        }

        return $estimates;
    }

    /**
     * Estimate character size
     */
    private static function estimateCharacterSize(string $data): float
    {
        // Assume ASCII and calculate
        return strlen($data) / 128;
    }

    /**
     * Calculate probability of character size being correct
     */
    private static function calculateProbability(float $charSize): float
    {
        // Character sizes should be reasonable (multiples of 8, 16, etc.)
        $reasonableSizes = [8, 16, 32, 64, 128, 256];

        foreach ($reasonableSizes as $size) {
            if (abs($charSize - $size) < 1) {
                return 0.9;
            }
        }

        return 0.1;
    }

    /**
     * Analyze content for patterns
     */
    private static function analyzeContent(string $data): array
    {
        return [
            'entropy' => self::calculateEntropy($data),
            'null_percentage' => (substr_count($data, "\x00") / strlen($data)) * 100,
            'binary_patterns' => self::detectBinaryPatterns($data)
        ];
    }

    /**
     * Calculate data entropy
     */
    private static function calculateEntropy(string $data): float
    {
        $frequencies = array_count_values(str_split($data));
        $length = strlen($data);
        $entropy = 0;

        foreach ($frequencies as $frequency) {
            $probability = $frequency / $length;
            $entropy -= $probability * log($probability, 2);
        }

        return $entropy;
    }

    /**
     * Detect binary patterns that suggest font data
     */
    private static function detectBinaryPatterns(string $data): array
    {
        return [
            'has_bitmap_patterns' => self::hasBitmapPatterns($data),
            'regular_spacing' => self::hasRegularSpacing($data),
            'character_boundaries' => self::detectCharacterBoundaries($data)
        ];
    }

    /**
     * Check for bitmap patterns
     */
    private static function hasBitmapPatterns(string $data): bool
    {
        // Look for patterns that suggest bitmap font data
        $nullCount = substr_count($data, "\x00");
        $ffCount = substr_count($data, "\xFF");

        // Bitmap fonts often have many null bytes and some full bytes
        return ($nullCount > strlen($data) * 0.1 && $ffCount > 0);
    }

    /**
     * Check for regular spacing
     */
    private static function hasRegularSpacing(string $data): bool
    {
        // Check if data has regular patterns that suggest character boundaries
        return true; // Placeholder
    }

    /**
     * Detect character boundaries
     */
    private static function detectCharacterBoundaries(string $data): array
    {
        return []; // Placeholder
    }

    /**
     * Format bytes for human reading
     */
    private static function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $power = $bytes > 0 ? floor(log($bytes, 1024)) : 0;
        return number_format($bytes / pow(1024, $power), 2) . ' ' . $units[$power];
    }

    /**
     * Create a custom font with Lithuanian/Russian characters
     */
    public static function createCustomFont(string $baseFontPath, array $customChars): string
    {
        // This would be the ultimate solution - creating custom .fmd files
        // with extended character support

        if (!file_exists($baseFontPath)) {
            throw new \Exception("Base font file not found: $baseFontPath");
        }

        // Load base font
        $baseFontData = file_get_contents($baseFontPath);
        $analysis = self::analyzeFmdFile($baseFontPath);

        // Create new font data with extended characters
        $newFontData = self::extendFontData($baseFontData, $analysis, $customChars);

        // Save custom font
        $customFontPath = dirname($baseFontPath) . '/custom_' . basename($baseFontPath);
        file_put_contents($customFontPath, $newFontData);

        return $customFontPath;
    }

    /**
     * Extend font data with custom characters
     */
    private static function extendFontData(string $baseFontData, array $analysis, array $customChars): string
    {
        // This is where we would implement the actual font extension logic
        // Based on the analyzed structure, we would add new character bitmaps

        // For now, return the base data (placeholder)
        return $baseFontData;
    }
}
