------------------------------------------------------------------
                           HID Global  

            U.are.U 4500 Fingerprint Reader Driver (Legacy) With Installer

                      Driver Version 4.1.0.217
                 
                           December 20, 2023
------------------------------------------------------------------

             (c) HID Global, 2023. All rights reserved.


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

-----------------
2. COMPATIBILITY
-----------------

This driver is not recognized and not supported by Windows Biometric Framework. This driver is intended to be used with applications that expect DigitalPersona Legacy driver interface and functionality.

This driver is compatible with the following DigitalPersona/Crossmatch/HID Global products:
    U.are.U 4500 Fingerprint Reader
    U.are.U 4000B Fingerprint Reader
    U.are.U 4500 Fingerprint Module
    U.are.U 4000B Fingerprint Module
    U.are.U Fingerprint Keyboard


-----------------------
3. SYSTEM REQUIREMENTS
-----------------------

Minimum system requirements:
  - Pentium-class processor
  - 45 MB disk space
  - USB port
  - Windows 10 (32-bit or 64-bit)
  - Windows 11 (64-bit)
	

-----------------
4. RELEASE NOTES
-----------------

This driver updates VM code with a workaround for the bug in the Prolific chip, that causes finger detection to malfunction.
This driver disables Disconnect Watchdog Timer.


----------------
5. KNOWN ISSUES 
----------------

5.1 When upgrading from an earlier version of the driver, the actual driver binaries will be updated, but the entry corresponding to the older driver installer will not be removed from "Programs and Features". This entry can be removed later and does not interfere with driver functionality.

------------------------
6. SUPPORT AND FEEDBACK 
------------------------
The latest version of support information is available at https://www.hidglobal.com/developer-center/digitalpersona-touchchip

