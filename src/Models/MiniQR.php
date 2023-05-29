<?php

namespace ShuGlobal\SlipVerification\Models;

use Crc16\Crc16;

class MiniQR
{
    public string|null $apiID = null;
    public string|null $backID = null;
    public string|null $transactionRef = null;
    public string|null $countryCode = null;
    public string|null $crc = null;

    public function __construct(string $miniQR)
    {
        $length = strlen($miniQR);
        $crcLength = 4; // 2 bytes for CRC value

        $payload = substr($miniQR, 0, $length - $crcLength);
        $crc = substr($miniQR, $length - $crcLength, $length);

        // Checksum and output CRC 2 bytes
        $calculateCRC = substr('0000' . strtoupper(dechex(Crc16::CCITT_FALSE($payload))), -4);

        // Checksum before read data each ID
        if ($calculateCRC == $crc) {
            $startID = substr($miniQR, 0, 2);
            if ($startID == "00") {
                $cursor = 4; // Start cursor without header

                // Recursive to read data from ID
                while ($cursor < strlen($miniQR)) {
                    $length = 2;

                    $ID = substr($miniQR, $cursor, $length);
                    $cursor += $length; // Move cursor
                    $dataLength = intval(substr($miniQR, $cursor, $length));
                    $cursor += $length; // Move cursor
                    $value = substr($miniQR, $cursor, $dataLength);
                    $cursor += $dataLength; // Move cursor with data length

                    // Store value to variable
                    switch ($ID) {
                        case "00":
                            $this->apiID = $value;
                            break;
                        case "01":
                            $this->backID = $value;
                            break;
                        case "02":
                            $this->transactionRef = $value;
                            break;
                        case "51":
                            $this->countryCode = $value;
                            break;
                        case "91":
                            $this->crc = $value;
                            break;
                        default:
                            break;
                    }
                }
            }
        }
    }
}