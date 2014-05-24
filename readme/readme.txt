PDF Catalog v2.8
=========================================
Description:
----------------------------------------------------------------------------------
This module enables to generate PDF Catalog.
----------------------------------------------------------------------------------
Requirements:
----------------------------------------------------------------------------------
1. TCPDF version 5.9.002 or above

----------------------------------------------------------------------------------
Update on 2.7.2
----------------------------------------------------------------------------------
1. added option not to display description

-----------------------------------------------------------------------
Update on 2.7
----------------------------------------------------------------------------------
1. reworked for opencart 1.5.4.1
2. Add configuration for PDF - pdf file information
----------------------------------------------------------------------------------
----------------------------------------------------------------------------------
Update on 2.6
----------------------------------------------------------------------------------
1. reworked for opencart 1.5.3.1

----------------------------------------------------------------------------------
----------------------------------------------------------------------------------
Update on 2.5
----------------------------------------------------------------------------------
1. Add configuration for PDF - Display categories
2. Display 6 products to prevent the image displaying on next page

----------------------------------------------------------------------------------
Update on 2.0
----------------------------------------------------------------------------------
1. Add page for introduction
2. Add Table of Contents 
3. Fixed product image displaying next page

----------------------------------------------------------------------------------
Features:
----------------------------------------------------------------------------------
1. Export catalog pdf file for all products
2. Export catalog pdf file for each category.
3. Able to set introduction of PDF page

=========================================
Installation instruction
=========================================
Step 1:
Copy the contents of the "upload" folder to your store's top level directory, preserving the directory structure.
The contents will not overwrite anything due to new files.

Step 2:
Download TCPDF - PHP Class for PDF from http://sourceforge.net/projects/tcpdf/files/
Extract downloaded file and copy 'tcpdf' folder to  '/upload/system/helper' folder.

Step 3:
3-1. Login to your OpenCart Admin
3-2. Select "Admin->Extensions->Modules"
3-3. Install "PDF Catalog"
3-4. Edit "PDF Catalog" and select "Position", "Status" and enter "Sort Order"

Step 4:
Open "Store Front" and check the function.

=========================================
FAQ
----------------------------------------------------------------------------------
Q. When I open 'PDF Catalog' module, I saw an error 'Error: Could not load helper tcpdf/tcpdf!'.
   What should I do?
A. You have not installed TCPDF properly.
   Download TCPDF - PHP Class for PDF from http://sourceforge.net/projects/tcpdf/files/
   Extract downloaded file and copy 'tcpdf' folder to  '/upload/system/helper' folder.
=========================================
Compatibilities
----------------------------------------------------------------------------------
- opencart v1.4.7 		Test: PASS
- opencart v1.4.8 		Test: PASS
- opencart v1.4.8b 		Test: PASS 
- opencart v1.4.9 		Test: PASS
- opencart v1.4.9.1 	Test: PASS
