INSERT INTO tItems VALUES('XXXXXXXX', 'YYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYY', 'YOUR_DESCRIPTION_HERE', 'YOUR_IMAGE_HERE');

/* statement to use for an insert with default empty description and image */
INSERT INTO tItems VALUES('XXXXXXXX', 'YYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYY', DEFAULT(description), DEFAULT(image));

/* use this if you want the SQL database to generate the key for you */
INSERT INTO tItems VALUES('XXXXXXXX', LEFT(CONCAT(HEX(FLOOR(RAND()*(10000000000000000-1000000000000000+1)+1000000000000000)), HEX(FLOOR(RAND()*(10000000000000000-1000000000000000+1)+1000000000000000)),HEX(FLOOR(RAND()*(10000000000000000-1000000000000000+1)+1000000000000000)),HEX(FLOOR(RAND()*(10000000000000000-1000000000000000+1)+1000000000000000)),HEX(FLOOR(RAND()*(10000000000000000-1000000000000000+1)+1000000000000000))),64), DEFAULT(description), DEFAULT(image))
