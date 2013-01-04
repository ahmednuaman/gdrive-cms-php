# A Google Drive CMS
## PHP Version (>=5.3.2)
This is a simple CMS proof-of-concept that uses Google Drive as the CMS part. This means that you don't need to bother with testing different WYSIWYG editors, dealing with authentication, passwords and sessions.

## How does it work?
A user will authenticate themselves using OAuth 2 and grant read permissions to the app. They then select a folder that contains the site's content (only documents at the moment) and then they'd select a document within this folder to define as the home page.

The folder can have many folders and documents within it, the CMS builds the multi dimention menu for you.

Once the user has selected the folder and the home page, the app scans through the documents copying over the `text/html` versions of the content into a MySQL database ready to be served.

## How can I use it?
1. Clone the app: `git clone git@github.com:ahmednuaman/gdrive-cms-php.git` or [download the zip](https://github.com/ahmednuaman/gdrive-cms-php/archive/master.zip).
2. Upload it to your server and rename the `config.php.example` to `config.php`.
3. Follow the instructions within the `config.php` (such as setting the keys, paths and what not).
4. Set up the DB table.
5. Visit the admin area, it'll be at [http://yourserver/path/admin/](http://yourserver/path/admin/), log in and follow the instructions.
6. When it's all done visit [http://yourserver/path/](http://yourserver/path/) and voila!

## Why did you do this?
I work on a lot of projects where my clients use Google Drive to update copy leaving me to then either update a CMS or some flat JSON/XML/YAML files. I _could_ risk allowing my clients to update flat files but if I had £1 for every time a JSON/XML/YAML file didn't validate, well, I'd have about £38.

I think that for the future this is quite a nice idea where I can allow my clients to specify a document in an environment that they're used to (a word processor or spreadsheet) to be used as the copy master for any site or project we work on.
