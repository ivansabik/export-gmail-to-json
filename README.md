Gmail to JSON
=====
 
Command line PHP tool for exporting all Inbox in Gmail to JSON text files

Requirements:

- Command line interface access to PHP cli
- IMAP configured in PHP cli 
- Access to ```system('stty echo')``` since it is used to hide login password
- MongoDB driver for PHP cli (if exporting to mongo)

Ubuntu / Debian setup instructions

```
sudo apt-get install php5
sudo apt-get install php5-imap
sudo gedit /etc/php5/cli/php.ini
```

On php.ini add extension ```imap.so```, if you are using MongoDB ```mongo.so``` should be somewhere in there also.

Usage

- ```php gmail-json.php```
- Write login credentials
- Follow the wizard
- Wait for the fun

If you export to MongoDB you may like to perform indexing to do some querying or operations with the text

```
db.gmail.ensureIndex( { from: "text" } )
db.gmail.ensureIndex( { subject: "text" } )
db.gmail.ensureIndex( { message: "text" } )
```


[![Bitdeli Badge](https://d2weczhvl823v0.cloudfront.net/ivansabik/export-gmail-to-json/trend.png)](https://bitdeli.com/free "Bitdeli Badge")

