-- This file is used to copy web page design from one QR code to another.
-- This will copy just the id of images in the source QR code to the destination 
-- QR Code, this means if you are designing one of the copied QR Codes and you 
-- deleted an image there, this will delete the image in the source QR Code as well.

SET @src = 130015;  -- REPLACE WITH YOUR SOURCE QR CODE ID.

SET @dest = 130016; -- REPLACE WITH YOUR DESTINATION QR CODE ID.

DELETE FROM qrcode_webpage_designs 
    WHERE qrcode_id = @dest;

INSERT INTO qrcode_webpage_designs (qrcode_id, design) 
    SELECT qrcode_id, design 
    FROM qrcode_webpage_designs 
    WHERE qrcode_id = @src;

UPDATE qrcode_webpage_designs 
    SET qrcode_id = @dest 
    ORDER BY id DESC 
    LIMIT 1;