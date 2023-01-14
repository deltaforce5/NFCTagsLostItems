# NFCTagsLostItems
This is a simple NFCTags lost items website solution, consisting of a simple website and the instructions on how to setup the whole system in order to be able to attach NFC tags to any relevant asset, so, if it is being lost, who recovers it can contact the owner and arrange the return. 

## Requirements
- A place to host a public website (eg. 000webhost) with:
-- Apache/ISS
-- PHP
-- a PHP compatible database like MySQL, PostgreSQL or MariaDB
- Ndef capable NFC Tags with sufficient memory to store the final URL (site type + public host + relative path + pagename + tag-id + key-id) 
- NFC Reader/Writer (An android smartphone does the job for 13.56MHz tags)

## How it works
By scanning the tag, pre-programmed with a public website address and two parameters: tag-id (the tag 8 byte address) and tag-key (a 64byte user chosen sequence); the user is directed to the website containing the image of the item associated to the tag, a description of the item and a contact form.

The url will be in the following form: <code> https://website.domain.tld/user-relative-path/pagename.php?t=tagid&k=tagkey </code>

By filling and submitting the form, the user sends an email to the owner of the tag, with the user phone number, email, the tag-id and a message. 

The image and the description of the items are stored in a SQL database, together with the tag-id and a tag-key, to prevent any visitor from accessing to all the available tags and images stored in the website. The image is stored as a BLOB object MIME encoded, ready to be served in the HTTP response. 

## Setup
### The website
The website is written in PHP with a single and simple main page containing a contact form to protect your email address from spam. This requires other two PHP files with the necessary code to retrieve some information from the SQL server and to send the email. A fourth PHP file holds sensitive data.

Just place all the files from the <code>WebSite</code> subfolder into the public folder of your host or web server and customize the <code>reserved.php</code> with the proper email address where you want to receive contacts from the form in the page and the configuration settings for the SQL database

### NFC Tags
Once the website is ready and available to the public, you can start programming the NFC Tags. I used an android phone with the following Apps
- Mifare Classic Tool (only for mifare classic tags)
- TagWriter by NXP (only for any tag from NXP)
- NFC Tools PRO (this one is not free)

Scan all the tags you are going to use, collecting their IDs (the serial number), we will identify them as <code>XXXXXXXX</code>. Now, for each one, generate a 64bytes long key using only number and characters in both upeercase and lowercase, to be HTTP compatible without recurring to encoding, we will identify them as <code>YYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYY</code>. You can shorten the key if the whole URL won't fit in your tags memory, just remember to change the table definition in database, reducing the keyid field size, when you are going to setup the SQL database.

You are ready to write the tags! for each one store a "Link" or "URL/URI" type field with the following data, assuming you are going to use secure server only:
- url type: https://
- url: mywebsite.dom.tld/userpath/i.php?t=XXXXXXXX&k=YYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYY

replacing 
- mywebsite.dom.tld : with the public address given by your provider or the fixed/dynamic DNS of your server (don't use IP addresses, use DNS, even fixed addresses may change over time)
- userpath/ : with the relative path to the folder where you placed the files of the <code>WebSite</code> subfolder. Remove if you are going to use the root path
- i.php : with the new name if you may have renamed it
- XXXXXXXX : with the id of the tag you are writing to
- YYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYY : with the key associated to the tag id

Once programmed, you should be able to scan it and navigate to the website, even if an error should pop up at the beginning of the form, because the SQL database has not been configured yet.

You may want to configure the database before going on programming all the tags.

### The SQL database
The database is used to provide the picture and the description of the tag. Create a new database and the associated username with the info recorded in the <code>reserved.php</code> file, then customize the <code>database.sql</code> in the <code>SQL</code> subfolder and execute it with your database (eg. for mysql: <code> sudo mysql dbname < database.sql </code> ) 

If everything went fine, you are ready to store the info of your tags.

Start taking a picture of the item the tag will be applied to, then edit the picture and scale it down to something around 320px for each side, then save it as jpg. Keep it small, there is no need for a FullHD picture, otherwise the website will take longer to render.
Now convert the image to base64, if you don't know how to do it, just go to [https://www.base64-image.de/], drag the saved jpg over their webpage and wait for the encoding to complete. Once ready, click <code></> show code</code> button and <code>copy to clipboard</code> the first of the two long texts.

Open the <code>add_tag_data.sql</code> in the <code>SQL</code> folder and paste the copied text in place of <code>YOUR_IMAGE_HERE</code>, then change <code>YOUR_DESCRIPTION_HERE</code> with the description of the item in the picture, <code>YYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYY</code> with the tag-key of the tag attached to the item and <code>XXXXXXXX</code> with the tag-id.

Save the file and execute the statement with your database, you should be able to scan the tag and see the form with the picture and its description just above it.


Everything is all set!


You should repeat the process of programming the tag, taking the picture and record everything into the database, for each tag you want to use. If you do one step at time you can use more <code>INSERT INTO</code> statement at once in the <code>add_tag_data.sql</code> file.

If you need to update the image, the description or the tag-key of a tag, use the statements from the <code>update_tag_data.sql</code> file.


### Locking the tags
This is a task you may have to complete after or while programming the tags, because if you don't lock the tags, they will can be erased and rewitten by anyone. This process is not the same for every NFC Tag and the protection they may offer is different, they can be password protected or just become read-only. If it the latter, please test your website carefully before locking the tag!!!

I used some Mifare Classic that cannot be locked but can be password protected using the Mifare Classic Tool. The drawback was that I had to dump each tag, change the last twelve bytes of each sector (Sector 0 excluded as was not replaceable) with an hexadecimal key of my choice and the access bytes of each sector (again sector 0 excluded) to 787780 instead of 7F0788, which was the default, and then write the modified dump back into the tag memory. This makes the tag freely readable but it is password protected against write.
