------------------------------------------------------------------
                             HID Global  

            U.are.U 4500 Fingerprint Reader Driver (Legacy) With Installer

                      Driver Version 4.1.1.221
                 
                           September 18, 2025
------------------------------------------------------------------

             (c) HID Global, 2025. All rights reserved.


This document provides late-breaking or other information for the U.are.U 4500 Fingerprint Reader Driver (Legacy).

-------------------------
How to Use This Document
-------------------------

To view the Readme file on-screen in Windows Notepad, maximize the Notepad window. On the Format menu, click Word Wrap. To print the Readme file, open it in Notepad or another word processor, and then use the Print command on the File menu.

---------
CONTENTS
---------

1.   INSTALLATION

2.   COMPATIBILITY
    
3.   SYSTEM REQUIREMENTS

4.   RELEASE NOTES

5.   KNOWN ISSUES

6.   SUPPORT AND FEEDBACK

----------------
1. INSTALLATION
----------------

You must have administrator's right to install this software. 

To install driver, run setup-x64.msi (for 64-bit Windows) or setup-x86.msi (for 32-bit Windows).

To install driver silently without UI and user interaction, for 64-bit Windows run: 
  msiexec /i setup-x64.msi /qn  
  
To install driver silently without UI and user interaction, for 32-bit Windows run: 
  msiexec /i setup-x86.msi /qn 
  
-----------------
2. COMPATIBILITY
-----------------

This driver is not recognized and not supported by Windows Biometric Framework. This driver is intended to be used with applications that expect DigitalPersona Legacy driver interface and functionality.

This driver is compatible with the following DigitalPersona/Crossmatch/HID Global products:
    DP4500 Fingerprint Reader
    U.are.U 4000B Fingerprint Reader
    DP4500 Fingerprint Module
    U.are.U 4000B Fingerprint Module
    U.are.U Fingerprint Keyboard


-----------------------
3. SYSTEM REQUIREMENTS
-----------------------

Minimum system requirements:
  - Pentium-class processor
  - 45 MB disk space
  - USB port
  - Windows 10 (32-bit or 64-bit), or Windows 11
	

-----------------
4. RELEASE NOTES
-----------------

This driver reverts back to firmware 0x0119 to restore compatibility with Linux and Android applications.
This driver turns off watchdog timer to improve user experience on Windows 11 23H2 and later.


----------------
5. KNOWN ISSUES 
----------------

There are no issues known at this time.

------------------------
6. SUPPORT AND FEEDBACK 
------------------------
The latest version of support information is available at https://www.hidglobal.com/developer-center/digitalpersona-touchchip

