#TorrentFinder
=============
TorrentFinder it's a web application with a PHP back-end that allows torrents search and downloads via an installed transmission daemon. A google-like web interface let you search for torrents, aggregating results from different torrent search engines and add torrents to your transmission queue with a single click.

Torrent search is performed by parsing different websites or by using their external API. The architecture of the system has been designed to make the addition of different search engines very easy.

Some screenshots: https://www.dropbox.com/sh/muwrxgz2l9z81jf/om6QZmGx0X/TorrentFinder#/

##Requisites 
* A web server (like Lighttpd or Apache) with the PHP module installed

##Installation
* Copy the whole 'torrentFinder' directory in the root directory of your web server (e.g. /var/www/).
* Set the transmission-daemon address and port in the file 'requestDownload.php'. For example mine is:
```php
$url = 'http://raspberrypi:5555/transmission/rpc';
```
* Go to http://raspberrypi/torrentFinder with your browser or to whatever directory/web-alias you've used in the installation and you're ready to go!.
