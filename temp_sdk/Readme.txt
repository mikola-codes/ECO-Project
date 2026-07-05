-------------------------------------------------------------------

                           DigitalPersona
                       U.are.U SDK for Windows

                           Version 3.0.0 
                          February 24, 2017
				
      (c) 1996-2017 DigitalPersona, Inc. All Rights Reserved. 
-------------------------------------------------------------------

This document provides late-breaking or other information that supplements the DigitalPersona U.are.U SDK for Windows documentation.


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

You must have local administrator rights to install this product on supported Windows systems.

U.are.U SDK for Windows 3.0.0 
1- Open/load the U.are.U SDK for Windows product package
2- Run SDK\x86\setup.exe OR SDK\x64\setup.exe located in the SDK folder
3- Follow the installation instructions
4- Connect the U.are.U Fingerprint Reader

-----------------
2. COMPATIBILITY
-----------------

DigitalPersona U.are.U SDK for Windows v3.0.0 is compatible with the following DigitalPersona products:

- DigitalPersona Altus 2.x.x

Fingerprint templates produced by the U.are.U SDK for Windows are also compatible with templates from the following DigitalPersona SDKs:

- Gold SDK
- Gold CE SDK
- One Touch for Windows SDK, all previous versions
- One Touch for Linux SDK, all distributions

Platinum SDK registration templates must be converted to a compatible format to work with U.are.U SDK for Windows v3.0.0 See the Developer Guide for instructions on performing this conversion. 

U.are.U SDK for Windows v3.0.0 is not compatible with any other DigitalPersona product.


-----------------------
3. SYSTEM REQUIREMENTS
-----------------------

- Microsoft Windows 7 (32/64-bit) or Microsoft Windows 8.1 (32/64-bit), Windows 10 (32/64-bit)
- JRE or JDK (1.6 or 1.7) - To run samples and completed applications if developing in Java
- .NET Framework v4.5 or newer 
- Browser Compatibility: IE, Microsoft Edge, Firefox, Chrome
- USB port on the computer where the fingerprint reader is to be connected


-----------------
4. RELEASE NOTES
-----------------

4.1 This release supports the following APIs:

- C++
- .NET
- ActiveX  
- Java
- Javascript/Web

4.2 This release adds Javascript Sample Application.

4.3 This release merges TouchChip components into main istallation of U.are.U SDK 3.0.0 for Windows.

4.3 IE has been tested on IE10, IE11. Chrome has been tested on Chrome49, Chrome50, Chrome56 . Firefox has been tested on Firefox45 and Firefox51.

4.4 SDK and RTE cannot be installed on the same computer.

4.5 Only U.are.U 5xxx devices will support streaming mode


------------------
5.   KNOWN ISSUES
------------------

5.1 On systems where both the DigitalPersona Altus Workstation\Kiosk client software and the U.are.U SDK are installed:Uninstallation of the U.are.U SDK for Windows (or accompanying Runtime) on a system may disable some features of the DigitalPersona Altus Workstation\Kiosk.  In these situations the DigitalPersona Altus Workstation\Kiosk installation may need to be reinstalled or repaired. Uninstallation of the DigitalPersona Altus Workstation\Kiosk on a system may disable some features of the U.are.U SDK.  In these situations the U.are.U SDK\RTE installation may need to be reinstalled or repaired.

5.2 Mozilla Firefox - If installed after the SDK, reboot before running the JavaScript sample application or any applications you develop using JavaScript and this SDK.

5.3 tabclt32.ocx needs to be manually copied and registered to run the OPOS sample with RTE installation.

5.4 Path of jpos.properties needs to be set to run the JavaPos sample with RTE installation.

5.5 WSQ and NFIQ have been introduced into .NET wrappers. CSharp samples from previous release need to be rebuilt against new assemblies.

5.6 Effective with the v2.3.1 release of U.are.U SDK for Windows, only U.are.U 5xxx devices support streaming mode. Streaming mode is disabled for the U.are.U 45xx devices.

5.7 Upgrading from a prior version of U.are.U SDK/RTE for Windows to a service-less install of U.are.U SDK/RTE for Windows v3.0.0 is not supported. An error message will popup for such upgrade.


------------------------
6. SUPPORT AND FEEDBACK
------------------------

The latest version of documentation is available at http://www.crossmatch.com/Support/Reference-Material/
Free technical support is available through the Crossmatch Developer WebPortal at http://devportal.digitalpersona.com.
You can also purchase a Developer Support package at our web store: http://www.crossmatch.com


