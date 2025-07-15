# The Communication Protocol for Setting the Controller ID, Baud Rate, and Network Parameters

## 1.1 General Agreement of Communication

Data packets are used to communicate between the PC and the controller to enhance the reliability of data and expand capabilities to deal with images and other data.

### Communication Process:

a) PC sends a data packet to the controller.
b) The controller receives the data packet, analyzes and processes the data packet, and then returns a data packet to the PC if necessary.
c) PC receives the data packets returned from the control card and analyzes the received data packets to determine whether communication is successful.

### Serial Setting:

- **Baud rate**: 115200, 57600, 38400, etc., selected by the selected tool.
- **Format string**: "115200, 8, N, 1", you can change the baud rate value 115200 according to what you set to the controller.

### Packet Data Checksum

The communication process uses the packet data checksum to check the correctness of data transmission. Checksum calculations should pay attention to: Data checksum is cumulative for each byte of data, using a 16-bit (2 bytes) unsigned number to represent. When the data validation is more than 0xFFFF, the checksum retains only the 16-bit value. For example, 0xFFA + 0x09 = 0x0003.

## 2 Data Packet Format

### 2.1 RS232/RS485 Data Packet Format

#### 2.1.1 The Data Package Constitutes

Sent packets and return packets have adopted the following packet format:

| Data | Value | Length (Byte) | Description |
|:-----|:------|:--------------|:------------|
| Start code | 0xa5 | 1 | The start of a packet |
| Packet type | 0x68 / 0xE8 | 1 | Recognition of this type of packet <br> Send packet: 0x68 <br> Return packet: 0xE8 |
| Card type | 0x32 | 1 | Fixed type code |
| Card ID | 0x01 ~ 0xFE, 0xFF | 1 | Control card ID, the screen No, valid values are as follows: <br> 1 ~ 254: the specified card ID <br> 0XFF: that group address, unconditionally receiving data |
| Command code (CMD) | See Command list | 1 | To perform the specified command |
| Additional information/ confirmation mark | 0 or 1 | 1 | The meaning of bytes in the packet is sent, "Additional Information", is a packet plus instructions, and now only use the lowest: <br> bit 0: whether to return a confirmation, 1 to return and 0 not to return <br> bit1 ~ bit7: reserved, set to 0 |
| Packet data | CC | Variable-length | Data |
| Packet data checksum | 0x0000 ~ 0xFFFF | 2 | Two bytes, checksum. Lower byte in the former. The sum of each byte from "Packet type" to "Packet data" content |
| End code | 0xae | 1 | The end of a packet (Package tail) |

#### 2.1.2 RS232/RS485 Packet Data Transcoding Description

The following process is done sending and receiving low-level functions. If you write your own PC side of the sending and receiving programs, you must implement as below conventions. Use the without transcoding code to calculate checksum.

**Send:**

Between start code and end code, if there is 0xA5, 0xAA, or 0xAE, it should be converted to two codes:
- 0xA5 → 0xAA 0x05. The purpose is to avoid the same with the start character 0xA5.
- 0xAE → 0xAA 0x0E. The purpose is to avoid the same with the end of the symbol 0xAE.
- 0xAA → 0xAA 0x0A. The purpose is to avoid the same with the escape character 0xAA.

**Receive:**

- Received symbol 0xA5, said that the beginning of a packet.
- Received symbol 0xAE, said that the end of a packet.
- When PC receives data from the controller, if there is 0xA5, 0xAA, or 0xAE, it should convert two codes to one code, specifically for:
  - 0xAA 0x05 → 0xA5
  - 0xAA 0x0E → 0xAE
  - 0xAA 0x0A → 0xAA

### 2.2 Network Data Packet Format

#### 2.2.1 The Data Packet Format of Network Sending

| Data | Value | Length (Byte) | Description |
|:-----|:------|:--------------|:------------|
| ID Code | 0x00000000 ~ 0xFFFFFFFF | 4 | Control network ID code, high byte in the former. Need to set to the same value on the card. |
| Network data length | 0x0000 ~ 0xFFFF | 2 | The byte length that from "Packet type" to "Packet data checksum". |
| Reservation | 0x0000 | 2 | Reservations. Fill in 0. |
| Packet type | 0x68 | 1 | Recognition of this type of packet. |
| Card type | 0x32 | 1 | Fixed Type Code. |
| Card ID | 0x01 ~ 0xFE, 0xFF | 1 | Control card ID, the screen No, valid values are as follows: 1 ~ 254: the specified card ID, 0xFF: that group address, unconditionally receiving data. |
| Command code (CMD) | | 1 | To perform the specified command. |
| Additional information/ confirmation mark | FF | 1 | The meaning of bytes in the packet is sent, "Additional Information", is a packet plus instructions, and now only use the lowest: bit 0: whether to return a confirmation, 1 to return and 0 not to return bit1 ~ bit7: reserved, set to 0. |
| Packet data | CC 000000 | Variable-length | Data |
| Packet data checksum | 0x0000 ~ 0xFFFF | 2 | Two bytes, checksum. Lower byte in the former. The sum of each byte from "Packet type" to "Packet data" content. |

Data within the network packet does not need to do the transcoding process.

#### 2.2.2 The Data Packet Format of the Control Card Returned to Network Sender

| Data | Value | Length (Byte) | Description |
|:-----|:------|:--------------|:------------|
| ID Code | 0x00000000 ~ 0xFFFFFFFF | 4 | Control network ID code, high byte in the former. Need to set to the same value on the card. |
| Network data length | 0x0000 ~ 0xFFFF | 2 | The byte length that from "Packet type" to "Packet data checksum". |
| Reservation | 0x0000 | 2 | Reservations. Fill in 0. |
| Packet type | 0xE8 | 1 | Recognition of this type of packet. 0xE8 = (0x68 | 0x80), for the app 3.2 or below return 0x68, app 3.3 or above return 0xE8. Same as other protocol (such as "set time" protocol), so you can ignore the highest bit (0x80), then it works for all app version. |
| Card type | 0x32 | 1 | Fixed Type Code. |
| Card ID | 0x01 ~ 0xFE, 0xFF | 1 | Control card ID, the screen No, valid values are as follows: 1 ~ 254: the specified card ID, 0xFF: that group address, unconditionally receiving data. |
| Command code (CMD) | | 1 | With the packets sent. |
| Return value | RR | 1 | RR = 0x00: that successful; RR = 0x01 ~ 0xFF: that the failure error code. In addition, a certain period of time does not receive the returned data packet, said communication failures. |
| Packet data | CC | Variable-length | Data |
| Packet data checksum | 0x0000 ~ 0xFFFF | 2 | Two bytes, checksum. Lower byte in the former. The sum of each byte from "Packet type" to "Packet data" content. |

The network packet data does not need to do transcoding processing.

## 2.3 Command List

| Command | Command sub-code | Description |
|:--------|:------------------|:------------|
| Query and set network parameter | 0x3C | |
| Query and set ID, baud rate | 0x3E | |

## 2.4 Data Format

### 2.4.1 Query and Set Network Parameter (CMD = 0x3C)

#### 2.4.1.1 Query Network Parameter

**Send Packet:**

| Data Position | Data Items | Length (Byte) | Description |
|:---------------|:------------|:--------------|:------------|
| 0x0000 | 1 | 1 | 1: Query network parameter |

**Return Packet:**

| Data Position | Data Items | Length (Byte) | Description |
|:---------------|:------------|:--------------|:------------|
| 0x0000 | Confirmation message | 1 | 0 Failed: 1 Successful. |
| 0x0001 | IP Address | 4 | IP Address |
| 0x0005 | Gateway | 4 | Gateway |
| 0x0009 | Subnet mask | 4 | Subnet mask |
| 0x000D | IP port number | 2 | IP port number |
| 0x000F | Network ID code | 4 | Network ID code |

#### 2.4.1.2 Set Network Parameter

**Send Packet:**

| Data Position | Data Items | Length (Byte) | Description |
|:---------------|:------------|:--------------|:------------|
| 0x0000 | 0 | 1 | 0: Set network parameter |
| 0x0001 | IP Address | 4 | IP Address |
| 0x0005 | Gateway | 4 | Gateway |
| 0x0009 | Subnet mask | 4 | Subnet mask |
| 0x000D | IP port number | 2 | IP port number |
| 0x000F | Network ID code | 4 | Network ID code |

**Return Packet:**

| Data Position | Data Items | Length (Byte) | Description |
|:---------------|:------------|:--------------|:------------|
| 0x0000 | Confirmation message | 1 | 0 Failed: 1 Successful. |

Serial data example: (IP=192.168.1.222)
A5 68 32 01 3C 01 00 C0 A8 01 DE C0 A8 01 01 FF FF FF 00 14 50 FF FF FF FF E6 0B AE
A5 E8 32 01 3C 01 01 59 01 AE

### 2.4.2 Query and Set ID and Baud Rate (CMD = 0x3E)

#### 2.4.2.1 Query Controller ID and Baud Rate

**Send Packet:**

| Data Position | Data Items | Length (Byte) | Description |
|:---------------|:------------|:--------------|:------------|
| 0x0000 | 1 | 1 | 1: Query controller ID and baud rate |
| 0x0001 | 0 | 1 | Reservation |
| 0x0002 | 0 | 1 | Reservation |

**Return Packet:**

| Data Position | Data Items | Length (Byte) | Description |
|:---------------|:------------|:--------------|:------------|
| 0x0000 | Confirmation message | 1 | 0 Failed: 1 Successful. |
| 0x0001 | ID number | 1 | ID number |
| 0x0002 | Baud rate number | 1 | 0: 115200 <br> 1: 57600 <br> 2: 38400 <br> 3: 19200 <br> 4: 9600 <br> 5: 4800 <br> 6: 2400 |

#### 2.4.2.2 Set Controller ID and Baud Rate

**Send Packet:**

| Data Position | Data Items | Length (Byte) | Description |
|:---------------|:------------|:--------------|:------------|
| 0x0000 | 0 | 1 | 0: Set controller ID and baud rate |
| 0x0001 | ID number | 1 | 1-254 |
| 0x0002 | Baud rate number | 1 | 0: 115200 <br> 1: 57600 <br> 2: 38400 <br> 3: 19200 <br> 4: 9600 <br> 5: 4800 <br> 6: 2400 |

**Return Packet:**

| Data Position | Data Items | Length (Byte) | Description |
|:---------------|:------------|:--------------|:------------|
| 0x0000 | Confirmation message | 1 | 0 Failed: 1 Successful. |
