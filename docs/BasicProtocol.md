# Basic Protocol LED Display Controller

## 1.1 Data Packet Format

The data packet format of RS232/RS485

| Data | Value | Length(Byte) | Description |
|:-----|:------|:-------------|:------------|
| Start code | 0xa5 | 1 | The start of a packet |
| Packet type | 0x68/0xE8 | 1 | 0x68: Send packet <br> 0xE8: Return packet |
| Card type | 0x32 | 1 | Fixed Type Code |
| Card ID | 0x01 ~ 0xFE, 0xFF | 1 | Control card ID, the screen No, valid values are as follows: <br> 1 ~ 254: the specified card ID <br> 0XFF: that group address, unconditionally receiving data |
| Protocol code | | 1 | Protocol identification. Detail in the following "Command list" |
| Additional information/ confirmation mark | 0 or 1 | 1 | The meaning of bytes in the packet is sent, "Additional Information", is a packet plus instructions, and now only use the lowest: <br> bit 0: whether to return a confirmation, 1 to return and 0 not to return <br> bit1 ~ bi7: reserved, set to 0 |
| Packet data | CC | | Packet data |
| Packet data checksum | 0x0000 ~ 0xFFFF | 2 | Two bytes checksum. Lower byte in the former. The sum of each byte from "Packet type" to "Packet data" content |
| End code | 0xae | 1 | The end of a packet (Package tail) |

### Command list

| Command | Code | Description |
|:--------|:-----|:------------|
| Restart hardware | 0x2d | |
| Restart APP | 0xfe | |
| Write file (Open) | 0x30 | |
| Write file (Write) | 0x32 | |
| Write file (Close) | 0x33 | |
| Quick write file (Open) | 0x50 | |
| Quick write file (Write) | 0x51 | |
| Quick write file (Close) | 0x52 | |
| Time query and set | 0x47 | |
| Brightness query and set | 0x46 | |
| Query version info | 0x2e | |
| Power ON/OFF info | 0x45 | |
| Power ON/OFF control | 0x76 | |
| Query temperature | 0x75 | |
| Remove file | 0x2c | |
| Query free disk space | 0x29 | |

### Transcoding description

The following process is done sending and receiving low-level functions. If you write your own PC side of the sending and receiving programs, you must implement as below conventions. Use the without transcoding code to calculate checksum.

#### Send:

Between start code and end code, if there is 0xA5, 0xAA, or 0xAE, it should be converted to two codes:
- 0xA5 → 0xAA 0x05. The purpose is to avoid the same with the start character 0xA5.
- 0xAE → 0xAA 0x0E. The purpose is to avoid the same with the end of the symbol 0xAE.
- 0xAA → 0xAA 0x0A. The purpose is to avoid the same with the escape character 0xAA.

#### Receive:

- Received symbol 0xA5, said that the beginning of a packet.
- Received symbol 0xAE, said that the end of a packet.
- When PC receives data from the controller, if there is 0xA5, 0xAA, or 0xAE, it should convert two codes to one code, specifically for:
  - 0xAA 0x05 → 0xA5
  - 0xAA 0x0E → 0xAE
  - 0xAA 0x0A → 0xAA

## 1.2 Packet Data

### 1.2.1 Restart hardware (CMD = 0x2d)

**Send Packet:**

| Data position | Data Items | Length(byte) | Description |
|:---------------|:------------|:-------------|:------------|
| 0x0000 | 0x00 | 1 | Filling a byte value of 0x00 |

**Return Packet:**

| Data position | Data Items | Length(byte) | Description |
|:---------------|:------------|:-------------|:------------|
| 0x0000 | Result | 1 | 0x00 Failed: 0x01 Success |

### 1.2.2 Restart APP (CMD = 0xfe)

**Send Packet:**

| Data position | Data Items | Length(byte) | Description |
|:---------------|:------------|:-------------|:------------|
| 0x0000 | Restart confirmation | 4 | "APP!" |

**Return Packet:**

| Data position | Data Items | Length(byte) | Description |
|:---------------|:------------|:-------------|:------------|
| 0x0000 | Result | 1 | 0x00 Failed: 0x01 Success |

### 1.2.3 Interactive upload file

#### 1.2.3.1 Open file (CMD = 0x30)

**Send Packet:**

| Data position | Data Items | Length(byte) | Description |
|:---------------|:------------|:-------------|:------------|
| 0x0000 | Read/Write mode | 1 | 'r': Read <br> 'w': Write |
| 0x0001 | Read/Write type | 1 | 'b': binary <br> 't': Text mode (Now don't support) |
| 0x0002 | Hour | 1 | File time "Hour" |
| 0x0003 | Minute | 1 | File time "Minute" |
| 0x0004 | Second | 1 | File time "Second" |
| 0x0005 | Year | 1 | File time "Year", 2-digit number. For example 0x09 means 2009 |
| 0x0006 | Month | 1 | File time "Month" |
| 0x0007 | Day | 1 | File time "Day" |
| 0x0008 | Reserved | 1 | Fill 0 |
| 0x0009 | File size | 4 | File size. Low byte first. |
| 0x000d | File name and file extensions | Variable-length | For example: "abc.txt" |
| Variable position | File name end flag | 1 | File name end flag, value is 0x00. |

Send package "return tag" should be set to 0x01. Receive and process the return packet.

**Return Packet:**

| Data position | Data Items | Length(byte) | Description |
|:---------------|:------------|:-------------|:------------|
| 0x0000 | Result | 1 | 0x00 Failed: 0x01 Success |

#### 1.2.3.2 Write file data (CMD = 0x32)

**Send Packet:**

| Data position | Data Items | Length(byte) | Description |
|:---------------|:------------|:-------------|:------------|
| 0x0000 | Data length | 2 | The length of the write data (bytes). Low byte first |
| 0x0002 | File data | Variable-length | The data to be written |

Note: Each write data don't over 512 bytes, if the file is larger than 512 bytes, according to the order in multiple writing file data.

Send package "return tag" should be set to 0x01. Receive and process the return packet.

**Return Packet:**

| Data position | Data Items | Length(byte) | Description |
|:---------------|:------------|:-------------|:------------|
| 0x0000 | Result | 1 | 0x00 Failed: 0x01 Success |

#### 1.2.3.3 Close file (CMD = 0x33)

**Send Packet:**

| Data position | Data Items | Length(byte) | Description |
|:---------------|:------------|:-------------|:------------|
| 0x0000 | File checksum | 2 | The entire file data checksum. Low byte first |

Send package "return tag" should be set to 0x01. Receive and process the return packet.

**Return Packet:**

| Data position | Data Items | Length(byte) | Description |
|:---------------|:------------|:-------------|:------------|
| 0x0000 | Result | 1 | 0x00 Failed: 0x01 Success |

### 1.2.4 Quick upload file or program

The program files are BIOS file and APP file. When you update program, you must first confirm that the source of the documents safe, reliable, and formatted correctly, and version is expected version. If there is no clear place, do not perform update operation procedures.

#### 1.2.4.1 Open file (CMD = 0x50)

**Send Packet:**

| Data position | Data Items | Length(byte) | Description |
|:---------------|:------------|:-------------|:------------|
| 0x0000 | Read/Write mode | 1 | 'r': Read <br> 'w': Write |
| 0x0001 | Read/Write type | 1 | 'b': binary <br> 't': Text mode (Now don't support) |
| 0x0002 | Hour | 1 | File time "Hour" |
| 0x0003 | Minute | 1 | File time "Minute" |
| 0x0004 | Second | 1 | File time "Second" |
| 0x0005 | Year | 1 | File time "Year", 2-digit number. For example 0x09 means 2009 |
| 0x0006 | Month | 1 | File time "Month" |
| 0x0007 | Day | 1 | File time "Day" |
| 0x0008 | Reserved | 1 | Fill 0 |
| 0x0009 | File size | 4 | File size. Low byte first. |
| 0x000d | File name and file extensions | Variable-length | For example: "abc.txt" |
| Variable position | File name end flag | 1 | File name end flag, value is 0x00. |

Send package "return tag" should be set to 0x01. Receive and process the return packet.

**Return Packet:**

| Data position | Data Items | Length(byte) | Description |
|:---------------|:------------|:-------------|:------------|
| 0x0000 | Result | 1 | 0x00 Failed: 0x01 Success |

#### 1.2.4.2 Write file data (CMD = 0x51)

**Send Packet:**

| Data position | Data Items | Length(byte) | Description |
|:---------------|:------------|:-------------|:------------|
| 0x0000 | Data length | 2 | The length of the write data (bytes). Low byte first |
| 0x0002 | Data block sequence number | 2 | Numbered starting from 0. Low byte first. |
| 0x0004 | Data block length | 2 | The data block length (in bytes). Low byte first. Each time a file is updated, according to a fixed length data block, and then the block to send. For all packets, the data block length value should be the same. |
| 0x0006 | File data | Variable-length | The data to be written |

Note: Each write data don't over 512 bytes, if the file is larger than 512 bytes, according to the order in multiple writing file data.

Send package "return tag" can be set to 0x00 or 0x01. When it be set to 0x00, no return packet. You can continue to send the next packet.

**Return Packet (According to the "return tag" to determine):**

| Data position | Data Items | Length(byte) | Description |
|:---------------|:------------|:-------------|:------------|
| 0x0000 | Result | 1 | 0x00 Failed: 0x01 Success |

#### 1.2.4.3 Close file (CMD = 0x52)

**Send Packet:**

| Data position | Data Items | Length(byte) | Description |
|:---------------|:------------|:-------------|:------------|
| 0x0000 | Total number of data blocks | 2 | The entire file into a number of data blocks. Low byte first. |

Send package "return tag" identification file type, set according to the following table:
- 0x01: General file.
- 0x02: Reserved
- 0x03: BIOS program file
- 0x04: APP program file

Send package "return tag" should be set to 0x01. Receive and process the return packet.

**Return Packet:**

| Data position | Data Items | Length(byte) | Description |
|:---------------|:------------|:-------------|:------------|
| 0x0000 | Number of error block | 1 | 0: No error. >0 and <=36: The number of error packets. The number of erroneous data returned does not mean that all of the error data segment, You can be re-issued this known error data segment, and then receive the new results, to know there is no error so far. |
| 0x0001 | Error block sequence number | Variable-length | Each error packet sequence number 2 bytes, Low byte first. The data length is: the number of error packets * 2 |

### 1.2.5 Time query and set (CMD = 0x47)

#### 1.2.5.1 Query time

**Send Packet:**

| Data position | Data Items | Length(byte) | Description |
|:---------------|:------------|:-------------|:------------|
| 0x0000 | 0x01 | >=1 | 0x01: Query time. |

**Return Packet:**

| Data position | Data Items | Length(byte) | Description |
|:---------------|:------------|:-------------|:------------|
| 0x0000 | 0x01 | 1 | 0x01: Query time. |
| 0x0001 | Time info | >=7 | Byte 1: Second <br> Byte 2: Minute <br> Byte 3: Hour <br> Byte 4: Week (0: Sunday, 1: Monday,...) <br> Byte 5: Day <br> Byte 6: Month <br> Byte 7: Year (2-digit number, for example: 0x09 means 2009) |

#### 1.2.5.2 Set time

**Send Packet:**

| Data position | Data Items | Length(byte) | Description |
|:---------------|:------------|:-------------|:------------|
| 0x0000 | 0x00 | 1 | 0x00: Set time |
| 0x0001 | Time info | >=7 | Byte 1: Second <br> Byte 2: Minute <br> Byte 3: Hour <br> Byte 4: Week (0: Sunday, 1: Monday,...) <br> Byte 5: Day <br> Byte 6: Month <br> Byte 7: Year (2-digit number, for example: 0x09 means 2009) |

**Return Packet:**

| Data position | Data Items | Length(byte) | Description |
|:---------------|:------------|:-------------|:------------|
| 0x0000 | 0 or 1 | >=1 | Byte 1: 0 Failed; 1 Success. <br> Other: Ignore |

### 1.2.6 Brightness Control (CMD = 0x46)

#### 1.2.6.1 Query Brightness (CMD = 0x46)

**Send data:**

| Data position | Data Items | Length(byte) | Description |
|:---------------|:------------|:-------------|:------------|
| 0x0000 | 0x01 | 1 | 0x01: Query the brightness |

**Return data:**

| Data position | Data Items | Length(byte) | Description |
|:---------------|:------------|:-------------|:------------|
| 0x0000 | 1 | 1 | 0x01: Query the brightness |
| 0x0001 | Brightness value | 24 | 24 bytes for 24 hours brightness value. Invalid value is 0 ~ 31, the value large than 31 means auto detect and control by sensor <br> Byte 1: value of 0:00 ~ 0:59 <br> Byte 2: value of 1:00 ~ 1:59 <br> ... <br> Byte 1: value of 23:00 ~ 23:59 |

#### 1.2.6.2 Set Brightness (CMD = 0x46)

**Send data:**

| Data position | Data Items | Length(byte) | Description |
|:---------------|:------------|:-------------|:------------|
| 0x0000 | 0 | 1 | 0x00: Set brightness |
| 0x0001 | Brightness value | 24 | 24 bytes for 24 hours brightness value. Invalid value is 0 ~ 31, the value large than 31 means auto detect and control by sensor <br> Byte 1: value of 0:00 ~ 0:59 <br> Byte 2: value of 1:00 ~ 1:59 <br> ... <br> Byte 1: value of 23:00 ~ 23:59 |

**Return data:**

| Data position | Data Items | Length(byte) | Description |
|:---------------|:------------|:-------------|:------------|
| 0x0000 | 0 or 1 | >=1 | Byte 1: 0 failed, 1 success <br> Other: Ignore |

### 1.2.7 Query version info (CMD = 0x2e)

**Send Packet:**

| Data position | Data Items | Length(byte) | Description |
|:---------------|:------------|:-------------|:------------|
| 0x0000 | 0x00 | 1 | Reserved |

**Return Packet:**

| Data position | Data Items | Length(byte) | Description |
|:---------------|:------------|:-------------|:------------|
| 0x0000 | 0x00 | 1 | Reserved |
| 0x0001 | Card type code | 1 | Controller type code |
| 0x0002 | Logic version | 1 | Bit0-3: The minor version. <br> Bit4-7: The major version. |
| 0x0003 | Bios version | 1 | Bit0-3: The minor version. <br> Bit4-7: The major version. |
| 0x0004 | Reserved | 1 | |
| 0x0005 | Reserved | 1 | |
| 0x0006 | Reserved | 1 | |
| 0x0007 | APP version | 1 | Bit0-3: The minor version. <br> Bit4-7: The major version. |
| 0x0008 | Reserved | Variable-length | |

### 1.2.8 Power ON/OFF info (CMD = 0x45)

#### 1.2.8.1 Query power ON/OFF info (CMD = 0x45)

**Send Packet:**

| Data position | Data Items | Length(byte) | Description |
|:---------------|:------------|:-------------|:------------|
| 0x0000 | 0x01 | 1 | 0x01: Query power ON/OFF info. |

**Return Packet:**

| Data position | Data Items | Length(byte) | Description |
|:---------------|:------------|:-------------|:------------|
| 0x0000 | 1 | 1 | 0x01: Query power ON/OFF info. |
| 0x0001 | Time value | 4 | Byte 1-2: The hour and minute of "ON" time. <br> Byte 3-4: The hour and minute of "OFF" time. <br> When "On", "off" time is the same that has been "On"; if hour > 23 or minutes > 59 indicates that the time is invalid. |
| 0x0005 | Reserved | 2 | Set to 0 |

#### 1.2.8.2 Set power ON/OFF info (CMD = 0x45)

**Send Packet:**

| Data position | Data Items | Length(byte) | Description |
|:---------------|:------------|:-------------|:------------|
| 0x0000 | 0 | 1 | 0x00: Set power ON/OFF info. |
| 0x0001 | Time value | 4 | Byte 1-2: The hour and minute of "ON" time. <br> Byte 3-4: The hour and minute of "OFF" time. <br> When "On", "off" time is the same that has been "On"; if hour > 23 or minutes > 59 indicates that the time is invalid. |
| 0x0005 | Reserved | 2 | Set to 0 |

**Return Packet:**

| Data position | Data Items | Length(byte) | Description |
|:---------------|:------------|:-------------|:------------|
| 0x0000 | 0 or 1 | >=1 | Byte 1: 0 failed, 1 success. <br> Other: Ignore |

### 1.2.9 Power ON/OFF control (CMD = 0x76)

#### 1.2.9.1 Query power ON/OFF info (CMD = 0x76)

**Send Packet:**

| Data position | Data Items | Length(byte) | Description |
|:---------------|:------------|:-------------|:------------|
| 0x0000 | 0x01 | 1 | 0x01: Query software power ON/OFF info. |

For example: a5 68 32 01 76 01 01 13 01 ae

**Return Packet:**

| Data position | Data Items | Length(byte) | Description |
|:---------------|:------------|:-------------|:------------|
| 0x0000 | 1 | 1 | 0x01: Query software power ON/OFF info. |
| 0x0001 | Power status | 1 | |
| 0x0002 | Value | 8 | Byte 1-2: The hour and minute of "ON" time <br> Byte 3-4: The hour and minute of "OFF" time <br> Byte 5-8: Reserved |

#### 1.2.9.2 Set power ON/OFF (CMD = 0x76)

**Send Packet:**

| Data position | Data Items | Length(byte) | Description |
|:---------------|:------------|:-------------|:------------|
| 0x0000 | 0 | 1 | 0x00: Set software power ON/OFF |
| 0x0001 | Switch command | 1 | 0: Immediately turn off the screen. <br> 1: Immediately open the screen. |
| 0x0002 | Value | 8 | Fill 0. |

Immediately turn off the screen instance: a5 68 32 01 76 01 00 00 00 00 00 00 00 00 00 12 01 ae
Immediately open the screen instance: a5 68 32 01 76 01 00 01 00 00 00 00 00 00 00 13 01 ae

**Return Packet:**

| Data position | Data Items | Length(byte) | Description |
|:---------------|:------------|:-------------|:------------|
| 0x0000 | 0 | 1 | Set software power ON/OFF |
| 0x0001 | Status | >=1 | Byte 1: 0: Immediately turn off the screen. <br> 1: Immediately open the screen. <br> Other: Ignore |

### 1.2.10 Query temperature/humidity (CMD = 0x75)

#### 1.2.10.1 Query temperature, humidity

This protocol requires the support of a new APP version.

**Send Packet:**

| Data position | Data Items | Length(byte) | Description |
|:---------------|:------------|:-------------|:------------|
| 0x0000 | Query flag | >=1 | Byte 1: <br> Bit0: Do you want to query temperature? (0 No, 1 Yes) <br> Bit1: Do you want to query humidity? (0 No, 1 Yes) <br> Other: Reserved |

**Return Packet:**

There are two returned data formats. When the returned data length is 8 bytes, the following meanings:

| Data position | Data Items | Length(byte) | Description |
|:---------------|:------------|:-------------|:------------|
| 0x0000 | Query flag | 1 | Meaning with sent packets |
| 0x0001 | Degrees Celsius | 2 | Temperature value (Degrees Celsius): <br> Byte 1: <br> Bit7: Numerical symbols. 1 negative, 0 positive. <br> Bit6-0: The high 7 bits in the integer part of the temperature absolute value. <br> Byte 2: <br> Bit7-4: The low 4 bits in the integer part of the temperature absolute value. <br> Bit3-0: The fractional part. Its unit is 1/16 (0.0625). |
| 0x0003 | Fahrenheit | 2 | Temperature value (Fahrenheit): |
| 0x0009 | Temperature adjusted value | 1 | Bit7: 1 Fahrenheit, 0 Degrees Celsius <br> Bit6: 1 negative, 1 positive <br> Bit5-0: The absolute value of the temperature adjustment. |
| 0x000a | Humidity values | 1 | Humidity value, Valid value: 0-100 |
| 0x000d | Humidity adjustment value | 1 | Bit7: Reserved <br> Bit6: 1 negative, 1 positive <br> Bit5-0: The absolute value of the humidity adjustment. |

When the returned data length is 14 bytes, the following meanings:

| Data position | Data Items | Length(byte) | Description |
|:---------------|:------------|:-------------|:------------|
| 0x0000 | Query flag | 1 | Meaning with sent packets |
| 0x0001 | Degrees Celsius | 4 | Temperature value (Degrees Celsius) string. For example: "23", "-3" |
| 0x0005 | Fahrenheit | 4 | Temperature value (Fahrenheit) string. For example: "96" |
| 0x0009 | Temperature adjusted value | 1 | Bit7: 1 Fahrenheit, 0 Degrees Celsius <br> Bit6: 1 negative, 1 positive <br> Bit5-0: The absolute value of the temperature adjustment. |
| 0x000a | Humidity values | 3 | Humidity value string. For example: "80" |
| 0x000d | Humidity adjustment value | 1 | Bit7: Reserved <br> Bit6: 1 negative, 1 positive <br> Bit5-0: The absolute value of the humidity adjustment. |

### 1.2.11 Remove file (CMD = 0x2c)

**Send Packet:**

| Data position | Data Items | Length(byte) | Description |
|:---------------|:------------|:-------------|:------------|
| 0x0000 | File name and file extensions | Variable-length | For example: "abc.txt" |
| Variable position | File name end flag | 1 | Fill name end flag. Value is 0x00 |

**Return Packet:**

| Data position | Data Items | Length(byte) | Description |
|:---------------|:------------|:-------------|:------------|
| 0x0000 | Result | 1 | 0x01 Success; Others Failed |

### 1.2.12 Query free disk space (CMD = 0x29)

**Send Packet:**

| Data position | Data Items | Length(byte) | Description |
|:---------------|:------------|:-------------|:------------|
| 0x0000 | 1 or 0 | 1 | 1: Disk "User" <br> 0: Disk "System" |

**Return Packet:**

| Data position | Data Items | Length(byte) | Description |
|:---------------|:------------|:-------------|:------------|
| 0x0000 | Return value | 1 | >0 Success; Others failed. |
| 0x0001 | The size of free disk space | 4 | Low byte first. |
