# C-Power5200 SDK API Manual

**Version: V1.4**  
**Date: 2012.08.04**  
**Company: 深圳市流明电子有限公司 (Shenzhen Lumen Electronics Co., Ltd.)**

## Revision Log

| Date | Version | Changes | Executor |
|------|---------|---------|----------|
| 2009-8-11 | V1.0 | The first version | |
| 2010-1-28 | V1.1 | 1. Add multi-window protocol data packing API<br>2. Add multi-window protocol serial and network simple use API | |
| 2010-5-22 | V1.2 | Increase the following functions:<br>1. CP5200_Program_AddLafPict<br>2. CP5200_Program_AddLafVideo<br>3. CP5200_Program_AddVariable<br>4. CP5200_MakeGetTypeInfoData<br>5. CP5200_ParseGetTypeInfoRet<br>6. CP5200_MakeGetTempHumiData<br>7. CP5200_ParseGetTempHumiRet<br>8. CP5200_MakeReadConfigData<br>9. CP5200_ParseReadConfigRet<br>10. CP5200_MakeWriteConfigData<br>11. CP5200_ParseWriteConfigRet<br>12. CP5200_RS232_GetTemperature<br>13. CP5200_RS232_GetTypeInfo<br>14. CP5200_Net_GetTemperature<br>15. CP5200_Net_GetTypeInfo | |
| 2011-02-24 | V1.3 | Increase the following functions:<br>1. CP5200_MakeReadHWSettingData<br>2. CP5200_ParseReadHWSettingRet<br>3. CP5200_MakeWriteHWSettingData<br>4. CP5200_ParseWriteHWSettingRet<br>5. CP5200_RS232_ReadHWSetting<br>6. CP5200_RS232_WriteHWSetting<br>7. CP5200_Net_ReadHWSetting<br>8. CP5200_Net_WriteHWSetting | |
| 2012.08.04 | V1.4 | 1. Increase multiple new functions including communication, file operations, zone management, and schedule control functions<br>2. Perfect interface parameters | |

## 1. Basic Definitions

### 1.1 Data Types

| Name | Data Type | Definition |
|------|-----------|------------|
| Object Handle | HOBJECT | void* |

### 1.2 Classification of API Functions

- Creating program file API function
- Create playbill file API function  
- Communication data API function

### 1.3 Common Operating Steps

1. Create program file
2. Create playbill file
3. Use communication data API to generate command data, then send the data to controller and receive return data, also use communication data API to parse the return data and get the result

**Note:** Control card only searches program files "playbill.lpp" when it starts. If the generated file is saved as other file name, when the program single-file(".lpp") is sent to the card, you need to change the file name to "playbill.lpp".

### 1.4 Communication Protocol

C-Power5200 controller supports RS232/485 and network communication modes.

#### 1.4.1 RS232/485

Communication start code is `0xA5`, end code is `0xAE`. Between start code and end code, if there is `0xA5`, `0xAA` or `0xAE`, it should be converted to two codes.

**When PC sends data to controller, convert one code to two codes:**
- `0xA5` → `0xAA 0x05`
- `0xAA` → `0xAA 0x0A`
- `0xAE` → `0xAA 0x0E`

**When PC receives data from controller, convert two codes to one code:**
- `0xAA 0x05` → `0xA5`
- `0xAA 0x0A` → `0xAA`
- `0xAA 0x0E` → `0xAE`

#### 1.4.2 Network

Needs 4 bytes controller network ID code at the beginning of data to be sent to controller.

### 1.5 Special Effects for Text and Picture

| Code | Effect |
|------|--------|
| 0 | Draw |
| 1 | Open from left |
| 2 | Open from right |
| 3 | Open from center (Horizontal) |
| 4 | Open from center (Vertical) |
| 5 | Shutter (vertical) |
| 6 | Move to left |
| 7 | Move to right |
| 8 | Move up |
| 9 | Move down |
| 10 | Scroll up |
| 11 | Scroll to left |
| 12 | Scroll to right |
| 13 | Flicker |
| 14 | Continuous scroll to left |
| 15 | Continuous scroll to right |
| 16 | Shutter (horizontal) |
| 17 | Clockwise open out |
| 18 | Anticlockwise open out |
| 19 | Windmill |
| 20 | Windmill (anticlockwise) |
| 21 | Rectangle forth |
| 22 | Rectangle entad |
| 23 | Quadrangle forth |
| 24 | Quadrangle entad |
| 25 | Circle forth |
| 26 | Circle entad |
| 27 | Open out from left up corner |
| 28 | Open out from right up corner |
| 29 | Open out from left bottom corner |
| 30 | Open out from right bottom corner |
| 31 | Bevel open out |
| 32 | AntiBevel open out |
| 33 | Enter into from left up corner |
| 34 | Enter into from right up corner |
| 35 | Enter into from left bottom corner |
| 36 | Enter into from lower right corner |
| 37 | Bevel enter into |
| 38 | AntiBevel enter into |
| 39 | Horizontal zebra crossing |
| 40 | Vertical zebra crossing |
| 41 | Mosaic (big) |
| 42 | Mosaic (small) |
| 43 | Radiation (up) |
| 44 | Radiation (downwards) |
| 45 | Amass |
| 46 | Drop |
| 47 | Combination (Horizontal) |
| 48 | Combination (Vertical) |
| 49 | Backout |
| 50 | Screwing in |
| 51 | Chessboard (horizontal) |
| 52 | Chessboard (vertical) |
| 53 | Continuous scroll up |
| 54 | Continuous scroll down |
| 55 | Reserved |
| 56 | Reserved |
| 57 | Gradual bigger (up) |
| 58 | Gradual smaller (down) |
| 59 | Reserved |
| 60 | Gradual bigger (vertical) |
| 61 | Flicker (horizontal) |
| 62 | Flicker (vertical) |
| 63 | Snow |
| 64 | Scroll down |
| 65 | Scroll from left to right |
| 66 | Open out from top to bottom |
| 67 | Sector expand |
| 68 | Reserved |
| 69 | Zebra crossing (horizontal) |
| 70 | Zebra crossing (Vertical) |
| 32768 | Random effect |

### 1.6 Text Extend Tags

The text containing extend tags may contain extend tags as below and all extend tags must be written in lowercase letters.

| Extend Tag | Description |
|------------|-------------|
| `<size>` | Designate the size of letters, must append attribute value, otherwise it will be ignored. If the attribute value is inoperative, it will be ignored also. Attribute value is the size of letter:<br>- `<size=8>`: 8 lattice letter<br>- `<size=16>`: 16 lattice letter<br>- `<size=24>`: 24 lattice letter<br>- `<size=32>`: 32 lattice letter |
| `<color>` | Designate the color of letters, must append attribute value, otherwise it will be ignored. Attribute value is the color of RGB hex value:<br>- `<color=#ff0000>`: Red<br>- `<color=#00ff00>`: Green<br>- `<color=#0000ff>`: Blue |
| `<p>` | Newline |
| `<align>` | The level of alignment, must append attribute value:<br>- `<align=left>`: left Alignment<br>- `<align=center>`: center Alignment<br>- `<align=right>`: right Alignment |
| `<font>` | Designate the font style of letters:<br>- `<font=0>`: Default font<br>- `<font=1>`: Font 1<br>- ...<br>- `<font=7>`: Font 7 |

### 1.7 Font Size Code and Font Style

#### 1.7.1 Font Size Code

| Code | Font Size (lattice) |
|------|---------------------|
| 0 | 8 |
| 1 | 12 |
| 2 | 16 |
| 3 | 24 |
| 4 | 32 |
| 5 | 40 |
| 6 | 48 |
| 7 | 56 |

#### 1.7.2 Font Style Code

| Code | Font Style |
|------|------------|
| 0 | Font 0 (default font) |
| 1 | Font 1 |
| 2 | Font 2 |
| 3 | Font 3 |
| 4 | Font 4 |
| 5 | Font 5 |
| 6 | Font 6 |
| 7 | Font 7 |

**Note:** If no special instructions, the parameter of the function in this document called "nFontSize" was defined in the following format:
- **Byte 0~1**: font size (lattice), such as 8, 12, 24, 32, 40, 48, 56
- **Byte 2**: 
  - Bit 0~2: font style code
  - Bit 3: Whether the specified font to use for each character (0 default font, 1 specify the font with each character)
  - Bit 4~7: Reserved
- **Byte 3**: Reserved

### 1.8 Font Color Code

**One-byte font color code:** Can express 8 kinds of color. Use each one bit to represent red, green, blue.
- The lowest stands for red
- The last but one stands for green  
- The antepenultimate stands for blue

**Three-byte font color code:** Can express all kinds of color. Use each one byte to represent red, green, blue.

### 1.9 Picture Effect Code

| Code | Picture Effect |
|------|----------------|
| 0 | Center |
| 1 | Zoom |
| 2 | Stretch |
| 3 | Tile |

### 1.10 Clock Format and Display Content

**Clock format:** Represented by one byte:
- bit 0: Signal timing (0: 12 signal timing; 1: 24 signal timing)
- bit 1: Year by bit (0: 4 bit; 1: 2 bit)
- bit 2: Line folding (0: single-row; 1: multi-row)
- bit 3~5: Reserved (set to 0)
- bit 6: Show time scale "Hour scale, Minute scale"
- bit 7: Reserved (set to 0)

**Clock display content:** Represented by one byte:
Ascertain the display content by bit:
- bit 7: pointer
- bit 6: week
- bit 5: second
- bit 4: minute
- bit 3: hour
- bit 2: day
- bit 1: month
- bit 0: year

### 1.11 Simple Picture Data Format

**Data composition:**
```
Data head | Red data (optional) | Green data (optional) | Blue data (optional)
```

**Data head description:**

| Offset | Field | Description |
|--------|-------|-------------|
| 0x00 | Identify | Set to "I1" |
| 0x02 | Width | The width of the picture, low byte previous (little endian) |
| 0x04 | Height | The height of the picture, low byte previous (little endian) |
| 0x06 | Property | The gray-scale and color of the picture |
| 0x07 | Reserved | Set 0 |

**Property field:**
- Bit0: Whether exist red data, exist when 1
- Bit1: Whether exist green data, exist when 1  
- Bit2: Whether exist blue data, exist when 1
- Bit3: Reserved, set to 0
- Bit4~7: Gray-scale, only support 0 and 7 now
  - 0: 2 levels gray, Each lattice data use 1 bit
  - 7: 256 levels gray, Each lattice data use 8 bit

Each line of the picture data is aligned by byte. For 2 levels gray picture, when the line data is not enough for 8 bit, add 0.

### 1.12 Global Zone Message Format

Each zone takes 16 bytes, the format as below:

| Offset | Field | Description |
|--------|-------|-------------|
| 0x00 | Type | 1: Show text<br>2: Show specify picture file<br>3: Clock<br>4: Temperature<br>5: Humidity<br>6: Hint text (After the '\n')<br>7: Time |
| 0x01 | Mode | Have different meanings according to the window mode |
| 0x02-0x03 | X | Start point X, high byte previous (big endian) |
| 0x04-0x05 | Y | Start point Y, high byte previous (big endian) |
| 0x06-0x07 | CX | Zone width, high byte previous (big endian) |
| 0x08-0x09 | CY | Zone height, high byte previous (big endian) |
| 0x0A-0x0F | ItemPropData | The property value of the zone, depends on the window mode |

### 1.13 Window Position and Property

| Offset | Field | Description |
|--------|-------|-------------|
| 0x00-0x01 | X | Window start point x, high byte previous (big endian) |
| 0x02-0x03 | Y | Window start point Y, high byte previous (big endian) |
| 0x04-0x05 | CX | Window width, high byte previous (big endian) |
| 0x06-0x07 | CY | Window height, high byte previous (big endian) |
| 0x08-0x0F | Window property | Window default type and parameters |

**Window property** is represented by 8 bytes:

| Offset | Field | Description |
|--------|-------|-------------|
| 0x00 | Mode | Window mode value |
| 0x01-0x07 | Parameter | Parameters based on mode |

**Window mode definitions:**

| Mode Value | Description |
|------------|-------------|
| 0 | Blank (Show nothing) |
| 1 | Text |
| 2 | Clock, calendar |
| 3 | Temperature, Humidity |
| 4 | Picture, Reference of the picture |
| Other | Reserved |

### 1.14 The Meaning of Each Byte of the Scan Parameters

A total of 16 bytes of scan parameters, set the scanning parameters and read the scan parameters to be used.

| Byte | Meaning | CPower3200/2200/1200 | LEDController/4200 |
|------|---------|----------------------|------------------|
| 0x00 | Column order | 0:Positive(+),1:Negative(-) | 0:Positive(+),1:Negative(-) |
| 0x01 | Data polarity | 0:Positive(+),1:Negative(-) | 0:Positive(+),1:Negative(-) |
| 0x02 | OE polarity | CPower3200/2200: Does not exist, set to 0<br>CPower1200: 0:Positive(+),1:Negative(-) | 0:Positive(+),1:Negative(-) |
| 0x03 | Line adjust | 0:-1,1:0,2:1,3:2 | 0:0,1:1,2:2,3:-1 |
| 0x04 | Hide scan | 0:No，1：Yes | 0:No hide, 1:Hide front, 2:Hide back, 3:Hide both |
| 0x05 | Color order | 0：Red-Green，1：Green-Red | 0:Red-Green-Blue, 1:Red-Blue-Green, 2:Green-Red-Blue, 3:Green-Blue-Red, 4:Blue-Red-Green, 5:Blue-Green-Red |
| 0x06 | Color mode | 0：6Mhz，1：12Mhz | 0~15：Mode 1~Mode 16 |
| 0x07 | Timing trimming | Does not exist, set to 0 | 0：1,1：2,2：3,3：4 |
| 0x08 | Pulse trimming | Does not exist, set to 0 | 0：1,1：2,2：3,3：4 |
| 0x09 | Scan mode | 0：1/16，1：1/8，2：1/4，3：1/2，4：Static | 0：1/16，1：1/8，2：1/4，3：1/2，4：Static |
| 0x0A | Module size | 0：16-Line，1：8-Line，2：4-Line，3：2-Line，4：1-Line | 0：16-Line，1：8-Line，2：4-Line，3：2-Line，4：1-Line |
| 0x0B | Line change space | 0：Every 4，1：Every 8，2：Every 16，3：Every 32 | 0：Every 8，1：Every 4，2：Every 16，3：Every 32 |
| 0x0C | Line change direction | 0:Positive(+),1:Negative(-) | 0:Positive(+),1:Negative(-) |
| 0x0D | Signal reverse | 0:None, 1:Odd line reverse, 2:Even line reverse, 3:All | 0：None，1：Reverse 8-pixel，2：Reverse 4-pixel，3：Reverse 16-pixel，4：Reverse 32-pixel |
| 0x0E | Output board | 0:Normal，1:Extend | 0：Type 1,1：Type 2, 2：Type 3,3：Type 4 |
| 0x0F | Line reverse | Does not exist, set to 0 | 0：None,2：Even line reverse,3: Odd line reverse |

## 2. API Function for Creating Program File

### 2.1 Overview of Program Creating API Functions

| No. | Function Name | Description |
|-----|---------------|-------------|
| 1 | CP5200_Program_Create | Create program object |
| 2 | CP5200_Program_Destroy | Destroy program object |
| 3 | CP5200_Program_SetProperty | Set the attribute value of program object |
| 4 | CP5200_Program_SetBackgndImage | Set the background image of program object |
| 5 | CP5200_Program_AddPlayWindow | Add play window to program |
| 6 | CP5200_Program_SetWindowProperty | Set window property |
| 7 | CP5200_Program_SetItemProperty | Set play item property |
| 8 | CP5200_Program_AddText<br>CP5200_Program_AddText1 | Add text item to play window |
| 9 | CP5200_Program_AddTagText<br>CP5200_Program_AddTagText1 | Add text item of contain extend tag to play window |
| 10 | CP5200_Program_AddPicture | Add picture item to play window |
| 11 | CP5200_Program_AddImage | Add image item to play window |
| 12 | CP5200_Program_AddLafPict | Add Laf picture item to play window |
| 13 | CP5200_Program_AddLafVideo | Add Laf animator item to play window |
| 14 | CP5200_Program_AddAnimator | Add animator item to play window |
| 15 | CP5200_Program_AddClock | Add clock item to play window |
| 16 | CP5200_Program_AddTemperature | Add temperature item to play window |
| 17 | CP5200_Program_AddVariable | Add custom variable data to play window |
| 18 | CP5200_Program_AddTimeCounter | Add time counter data to play window |
| 19 | CP5200_Program_SaveToFile | Save program to file |

**Usage:**
1. Create program object
2. Add play window
3. Add play item to play window
4. Save program to file
5. Destroy program object

### 2.2 Detail of Creating Program File API Functions

#### CP5200_Program_Create

```c
HOBJECT CP5200_Program_Create(WORD width, WORD height, BYTE color)
```

**Description:** Create program object

**Parameters:**
- `width`: Width of the screen, unit is pixel
- `height`: Height of the screen, unit is pixel  
- `color`: Color and gray-scale
  - Bit0~2: 1 red color, 3 red & green color, 7 red, green and blue color
  - Bit4~6: gray scale. 0 (white or black), 7(256 grayscale)
  - Example: 0x01(red color no gray), 0x77 full color, 256 gray scale

**Return:** Handle of program object, all these kind of API functions use this handle. Return NULL if fail.

**Note:** When an application no longer requires a given object, it should be destroyed to free the resource.

#### CP5200_Program_Destroy

```c
int CP5200_Program_Destroy(HOBJECT hObj)
```

**Description:** Destroy program object

**Parameters:**
- `hObj`: Handle of program object to be destroyed

**Return:**
- 0: No error
- -1: Invalid program object handle

#### CP5200_Program_SetProperty

```c
int CP5200_Program_SetProperty(HOBJECT hObj, int nPropertyValue, DWORD nPropertyID)
```

**Description:** Set the attribute value of program object

**Parameters:**
- `hObj`: Handle of program object
- `nPropertyValue`: Attribute value, depends on parameter "nPropertyID"
  - Program repetition play times range is 1~65535
  - Program play time unit is second and range is 1~65535
- `nPropertyID`: Attribute identify, must be one of below:
  - 1: program repetition play times
  - 2: program play time

**Return:**
- -1: Wrong handle of program object
- 0: Unacquainted Attribute identify
- >0: Setting success

**Note:** "Program repetition play times" and "program play time", only one is virtuous and the lastly setting is virtuous.

#### CP5200_Program_AddPlayWindow

```c
int CP5200_Program_AddPlayWindow(HOBJECT hObj, WORD x, WORD y, WORD cx, WORD cy)
```

**Description:** Add play window to program

**Parameters:**
- `hObj`: Handle of program object
- `x`: Start X of the play window
- `y`: Start Y of the play window
- `cx`: Width of play window
- `cy`: Height of play window

**Return:**
- >=0: Number of play window
- -1: Invalid program object handle
- -3: Argument error

#### CP5200_Program_AddText / CP5200_Program_AddText1

```c
int CP5200_Program_AddText(HOBJECT hObj, int nWinNo, const char* pText, int nFontSize, COLORREF crColor, int nEffect, int nSpeed, int nStay)
```

**Description:** Add text item to play window

**Parameters:**
- `hObj`: Handle of program object
- `nWinNo`: Number of play window, base on 0
- `pText`: Text to be added
- `nFontSize`: Font size and style, see 1.7. Font size code and font style
- `crColor`: Text color
- `nEffect`: Show effect
- `nSpeed`: Effect speed
- `nStay`: Stay time in second

**Return:**
- >=0: Play item no
- -1: Invalid program object handle
- -3: Invalid play window number
- -4: Memory not enough

**Note:** CP5200_Program_AddText1 is for single byte characters, ASCII and extended ASCII.

#### CP5200_Program_SaveToFile

```c
int CP5200_Program_SaveToFile(HOBJECT hObj, const char* pFilename)
```

**Description:** Save program to file

**Parameters:**
- `hObj`: Handle of program object
- `pFilename`: Path and file name

**Return:**
- 0: No error
- -1: Invalid program object handle
- -3: File create error

## 3. API Function for Creating Playbill File

### 3.1 Overview of Playbill Creating API Function

| No. | Function Name | Description |
|-----|---------------|-------------|
| 1 | CP5200_Playbill_Create | Create playbill object |
| 2 | CP5200_Playbill_Destroy | Destroy playbill object |
| 3 | CP5200_Playbill_AddFile | Add program file to playbill |
| 4 | CP5200_Playbill_DelFile | Delete program file from playbill |
| 5 | CP5200_Playbill_SaveToFile | Save playbill to file |

**Usage:**
1. Create playbill object
2. Add program file to playbill
3. Save playbill to file
4. Destroy playbill object

## 4. API Function for Data Communication

### 4.1 Overview of Data Communication API Function

| No. | Function Name | Description |
|-----|---------------|-------------|
| 1 | CP5200_CommData_Create | Create communication data object |
| 2 | CP5200_CommData_Destroy | Destroy communication data object |
| 3 | CP5200_CommData_SetParam | Set data packet parameter |
| 4 | CP5200_MakeCreateFileData | Make create file command data |
| 5 | CP5200_ParseCreateFileRet | Parse return data of create file |
| ... | ... | ... |

**Usage:**
1. Create data object
2. Make communication data, include RS232/485's code convert (0xa5 => 0xaa 0x05, ...), or network ID code
3. Send communication data to the controller
4. Receive data from controller, and process code convert (0xaa 0x05 => 0xa5, ...)
5. Parse the return data and get the result
6. Destroy data object

### 4.2 Detail of Data Communication API Functions

#### CP5200_CommData_Create

```c
HOBJECT CP5200_CommData_Create(int nCommType, BYTE byCardID, DWORD dwIDCode)
```

**Description:** Create communication data object

**Parameters:**
- `nCommType`: RS232/485 or network communication type
  - 0: RS232/485
  - 1: Network
- `byCardID`: Controller ID
- `dwIDCode`: Network ID code of the controller. RS232 ignore it.

**Return:** Handle of communication data object, all these kind of API functions use this handle. Return NULL if fail.

**Note:** When an application no longer requires a given object, it should be destroyed to free the resource.

## 5. API Function for Multi-window Protocol Data Communication

### 5.1 Overview of Data Communication API Function

| No. | Function Name | Description |
|-----|---------------|-------------|
| 1 | CP5200_CmmPacker_Create | Create multi-window communication data object |
| 2 | CP5200_CmmPacker_Destroy | Destroy multi-window communication data object |
| 3 | CP5200_CmmPacket_SetParam | Set communication data packet parameter |
| 4 | CP5200_CmmPacker_Count | Get the number of packets in the object |
| 5 | CP5200_CmmPacker_Data | Get the data of packet in the object |
| 6 | CP5200_MakeSplitScreenData | Make split window command data |
| 7 | CP5200_ParseSplitScreenRet | Parse return data of split window command |
| ... | ... | ... |

## 6. Template Data Communication API Function

Template data communication API functions provide advanced features for program template management, including setting program templates, managing schedules, and controlling program execution.

## 7. Communication Base API Function

### 7.1 Overview of RS232 Communication Base API Functions

| No. | Function Name | Description |
|-----|---------------|-------------|
| 1 | CP5200_RS232_Init | Initialize serial port parameters |
| 2 | CP5200_RS232_InitEx | Initialize serial port parameters and set timeout |
| 3 | CP5200_RS232_Open | Open serial port |
| 4 | CP5200_RS232_OpenEx | Open serial port, assigned reading and writing timeout |
| 5 | CP5200_RS232_Close | Close serial port |
| 6 | CP5200_RS232_IsOpened | Test whether the serial port has been opened |
| 7 | CP5200_RS232_Write | Write data to serial port |
| 8 | CP5200_RS232_Read | Read data from serial port |
| 9 | CP5200_RS232_WriteEx | Write data to serial port, and processing for transcoding |
| 10 | CP5200_RS232_ReadEx | Read data from serial port, and processing for transcoding |

### 7.2 Detail of RS232 Communication Base API Functions

#### CP5200_RS232_Init

```c
int CP5200_RS232_Init(const char *fName, int nBaudrate)
```

**Description:** Initialize serial port parameters

**Parameters:**
- `fName`: RS232 serial port name, for example: "COM1", "COM2", ...
- `nBaudrate`: Baud rate, for example: 115200, 57600, ...

**Return:**
- 1: Success
- 0: Fail

**Note:** Other serial port parameters are fixed:
- Parity: No parity
- Data bits: 8
- Stop bits: 1
- Flow Control: None

### 7.3 Overview of Network Communication Base API Functions

| No. | Function Name | Description |
|-----|---------------|-------------|
| 1 | CP5200_Net_Init | Initialize network parameters |
| 2 | CP5200_Net_SetBindParam | Bind client IP and port |
| 3 | CP5200_Net_Connect | Open network connections |
| 4 | CP5200_Net_IsConnected | Test whether the network has been connected |
| 5 | CP5200_Net_Disconnect | Close network connections |
| 6 | CP5200_Net_Write | Write data to network |
| 7 | CP5200_Net_Read | Read data from network |

## 8. Running Plan API Function

C-Power5200 controller controls running program by date and week. Running plan is saved as file in the controller, the file name is "playbill.rsf" and it can't be changed.

### 8.1 Overview of Running Plan API Functions

| No. | Function Name | Description |
|-----|---------------|-------------|
| 1 | CP5200_Runsch_Create | Create running plan object |
| 2 | CP5200_Runsch_Destroy | Destroy running plan object |
| 3 | CP5200_Runsch_AddItem | Add running plan item |
| 4 | CP5200_Runsch_SaveToFile | Save running plan to file |

## 9. Time-limited Play Information by Week

C-Power5200 controller supports play by period of time. Time-limited information is saved as file and its name is "playbill.lpt".

### 9.1 Detail of File Head

| Offset | Field | Size | Description |
|--------|-------|------|-------------|
| 0x00 | File ID | 2 | Fixed for the "LT" |
| 0x02 | Format version number | 2 | 0x0100 (first byte is 0x00, second byte is 0x01) |
| 0x04 | Record number | 2 | Number of time-limited players recorded information, low byte first |

### 9.2 Detail Definition of Time-limited Play Information by Week

| Offset | Field | Size | Description |
|--------|-------|------|-------------|
| 0x00 | Program number | 2 | Program number, started from 0 |
| 0x02 | Week | 1 | Limited by week, use 7 bits |
| 0x03 | Begin minute | 1 | Begin play time: minute (0~59) |
| 0x04 | Begin hour | 1 | Begin play time: hour (0~23) |
| 0x05 | End minute | 1 | End play time: minute (0~59) |
| 0x06 | End hour | 1 | End play time: hour (0~23) |

## 10. Multi-window Control API Function

### 10.1 Overview of RS232 Multi-window Control API Function

| No. | Function Name | Description |
|-----|---------------|-------------|
| 1 | CP5200_RS232_SplitScreen | Send split window command |
| 2 | CP5200_RS232_SendText<br>CP5200_RS232_SendText1 | Send text to special window |
| 3 | CP5200_RS232_SendTagText<br>CP5200_RS232_SendTagText1 | Send tag text to special window |
| 4 | CP5200_RS232_SendPicture | Send picture to special window |
| 5 | CP5200_RS232_SendStatic | Send static text to special window |
| 6 | CP5200_RS232_SendClock | Send clock to special window |
| 7 | CP5200_RS232_ExitSplitScreen | Exit split window command |
| 8 | CP5200_RS232_SaveClearWndData | Save or clear split window message |
| ... | ... | ... |

## 11. Program Template API Function

Program template API functions provide comprehensive template-based program management capabilities for advanced LED display control applications.

## 12. Simple Use API Function

### 12.1 Overview of RS232 Simple Use API Function

| No. | Function Name | Description |
|-----|---------------|-------------|
| 1 | CP5200_RS232_UploadFile | Upload file to controller |
| 2 | CP5200_RS232_DownloadFile | Download file from controller |
| 3 | CP5200_RS232_RemoveFile | Delete controller file |
| 4 | CP5200_RS232_TestController | Test whether controller has connected to PC |
| 5 | CP5200_RS232_TestCommunication | Test whether controller communication is normal |
| 6 | CP5200_RS232_GetTime | Get controller time |
| 7 | CP5200_RS232_SetTime | Set controller time |
| 8 | CP5200_RS232_GetTempHumi | Get controller temperature and humidity |
| 9 | CP5200_RS232_RestartApp | Restart controller app |
| 10 | CP5200_RS232_RestartSys | Restart controller system |
| 11 | CP5200_RS232_GetTypeInfo | Get controller type information |
| 12 | CP5200_RS232_SendInstantMessage<br>CP5200_RS232_SendInstantMessage1 | Send Instant Message |
| 13 | CP5200_RS232_ReadHWSetting | Read scan param |
| 14 | CP5200_RS232_WriteHWSetting | Write scan param |

### 12.2 Detail of RS232 Simple Use API Function

#### CP5200_RS232_UploadFile

```c
int CP5200_RS232_UploadFile(int nCardID, const char* pSourceFilename, const char *pTargetFilename);
```

**Description:** Upload file to controller

**Parameters:**
- `nCardID`: Controller ID
- `pSourceFilename`: Source file name
- `pTargetFilename`: Target file name

**Return:**
- 0: Success
- -1: Error reading source file
- -2: Cannot generate the command data
- ... (other error codes)

## 13. Other API

### 13.1 Overview of Other API

| No. | Function Name | Description |
|-----|---------------|-------------|
| 1 | CP5200_CalcImageDataSize | Image data size calculation |
| 2 | CP5200_MakeImageDataFromFile | Image data obtained from the image file |

### 13.2 Detail of Other API

#### CP5200_CalcImageDataSize

```c
int CP5200_CalcImageDataSize(WORD imgw, WORD imgh, BYTE color)
```

**Description:** Image data size calculation

**Parameters:**
- `imgw`: Image width
- `imgh`: Image height  
- `color`: Image color

**Return:** >=0: Image data size

#### CP5200_MakeImageDataFromFile

```c
int CP5200_MakeImageDataFromFile(WORD imgw, WORD imgh, BYTE color, BYTE *pDatBuf, int nBufSize, const char* pFilename, int nMode)
```

**Description:** Image data obtained from the image file

**Parameters:**
- `imgw`: Image width
- `imgh`: Image height
- `color`: Image color
- `pDatBuf`: Image data buffer
- `nBufSize`: Image data buffer size
- `pFilename`: Picture file path name
- `nMode`: Picture mode, see 1.9. Picture effect code

**Return:**
- >=0: Image data size
- -1: Image file not found or load failed
- -2: Image conversion failed
- -3: Picture mode is wrong
- -4: Image data buffer length is not enough

---
