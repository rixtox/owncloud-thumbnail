files_thumbnail
==================

ownCloud thumbnail generator application. Used as a public GET/REST API to gather file thumbnails from clients.
Able to specify thumbnail size or use default size values to generate thumbnails.

## Usage
*Get thumbnail in default sizes:
> http://domain/owncloud/?app=files_thumbnail&path=/subdir/imagefile.jpg&size={xs, s, m, l, xl}
*Specify size for thumbnail:
> http://domain/owncloud/?app=files_thumbnail&path=/subdir/imagefile.jpg&width=125&height=125
