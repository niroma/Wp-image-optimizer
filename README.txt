=== WP Image Optimizer ===
Contributors: zulugrid, nir0ma
Tags: image, images, attachments, attachment, optimization, compress, littleutils, opt-jpg, opt-gig, opt-png, compression, lossy, lossless
Donate link: https://www.niroma.net
Requires at least: 3.0.1
Tested up to: 4.9.6
Requires PHP: 5.6.0
Stable tag: 1.1.4
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Reduce image file sizes and improve website performance using Linux littleutils image optimizers within WordPress.

== Description ==
**New features compared to CW Image Optimizer?**

1. New plugin structure with many code rewrites and performance improvments
1. Bulk Optimization now uses ajax to optimize images and can handle huge amount of images. Bulk optimization tests were performed successfully on a 200k+ media library ... yes it tooked a while to run the test, but it worked :) whereas all image optimization plugins I had been testing were failing on a 15k+ media library !
1. Optimize function can now fix images meta if structure is not correct
1. Bulk Optimization can now be performed on All images or only the ones that haven’t been optimized yet
1. **Save even more disk space** with Lossy Jpeg Compression (Fallback to lossless compression if fails)

The WP Image Optimizer is a WordPress plugin that will automatically and losslessly optimize your images as you upload them to your blog. It can also optimize the images that you have already uploaded in the past.

Because WP Image Optimizer uses lossless optimization techniques, your image quality will be exactly the same before and after the optimization. The only thing that will change is your file size.

The WP Image Optimizer plugin is based on the WP Smush.it plugin. Unlike the WP Smush.it plugin, your files won’t be uploaded to a third party when using WP Image Optimizer. Your files are optimized using the Linux [littleutils](http://sourceforge.net/projects/littleutils/) and [jpeg-recompress](https://github.com/danielgtaylor/jpeg-archive) image optimization tools (available for free). You don’t need to worry about the Smush.it privacy policy or terms of service because your images never leave your server.

**Why use WP Image Optimizer?**

1. **Your pages will load faster.** Smaller image sizes means faster page loads. This will make your visitors happy, and can increase ad revenue.
1. **Faster backups.** Smaller image sizes also means faster backups.
1. **Less bandwidth usage.** Optimizing your images can save you hundreds of KB per image, which means significantly less bandwidth usage.
1. **Super fast.** Because it runs on your own server, you don’t have to wait for a third party service to receive, process, and return your images. You can optimize hundreds of images in just a few minutes.
1. **Robust** I needed a bulletproof plugin, able to handle thousands of files and working using cron-task with wp-cli. Most of the optimization plugins I had been testing failed, so I decided to build a new version of cw image otpimizer to suit my neeeds.

== Installation ==
1. Install littleutils on your Linux server (step-by-step instructions are below).
1. Upload the \'wp-image-optimizer\' plugin to your \'/wp-content/plugins/\' directory.
1. Activate the plugin through the \'Plugins\' menu in WordPress.
1. Navigate to the settings page (Media >> WP Image Optimizer) to optimize your files
1. Done!

### Installing littleutils: Ubuntu 16.04 LTS (64-bit)

These instructions were tested with littleutils 1.0.27 and Ubuntu 16.04 LTS (64-bit).

Please note : If you install littleutils 1.0.37 without installing 1.0.27 before, images optimization process may encounter issues.

1. sudo apt-get update
1. Download littleutils 1.0.27 : sudo wget http://downloads.sourceforge.net/project/littleutils/littleutils-source/1.0.27/littleutils-1.0.27.tar.bz2
1. Install dependencies : sudo apt-get install gifsicle pngcrush lzip libpng12-0 libpng12-dev libjpeg-progs p7zip-full
1. Uncompress littleutils : sudo tar jxvf littleutils-1.0.27.tar.bz2 && cd littleutils-1.0.27
1. Configure and install littleutils : sudo ./configure --prefix=/usr && sudo make && sudo make install && sudo make install-extra
1. Then you can upgrade littleutiles to 1.0.37 : sudo wget http://downloads.sourceforge.net/project/littleutils/littleutils-source/1.0.37/littleutils-1.0.37.tar.bz2
1. Uncompress littleutils : sudo tar jxvf littleutils-1.0.37.tar.bz2 && cd littleutils-1.0.37
1. Configure and install littleutils : sudo ./configure --prefix=/usr && sudo make && sudo make install && sudo make install-extra

### Installing jpeg-recompress: Ubuntu 16.04 LTS (64-bit) (Needed for lossy optimization)

Install mozjpeg dependencies first

1. sudo apt-get update
1. sudo apt-get install build-essential autoconf pkg-config nasm libtool git gettext libjpeg-dev -y

Build [mozjpeg](https://github.com/mozilla/mozjpeg), the latest tar.gz can be found [here](https://github.com/mozilla/mozjpeg/releases) which you can replace below in the wget line.

1. cd /tmp
1. wget https://github.com/mozilla/mozjpeg/archive/v3.3.1.tar.gz -O mozjpeg.tar.gz
1. tar -xf mozjpeg.tar.gz
1. cd mozjpeg
1. autoreconf -fiv
1. ./configure --with-jpeg8 --prefix=/usr
1. make
1. sudo make install

Install jpeg-recompress with these commands, make sure you have the bzip2 package.

1. sudo apt-get install bzip2
1. cd /tmp
1. wget https://github.com/danielgtaylor/jpeg-archive/releases/download/2.1.1/jpeg-archive-2.1.1-linux.tar.bz2 -O jpeg-archive.tar.bz2
1. tar -xf jpeg-archive.tar.bz2
1. sudo cp jpeg-recompress /usr/bin/jpeg-recompress
1. sudo chmod 755 /usr/bin/jpeg-recompress

(re)Install jpegtran

1. sudo apt-get install libjpeg-turbo-progs

##  Troubleshooting

**littleutils is installed, but the plugin says it isn't.** If you are confident that it is installed properly, then go to the plugin configuration page and disable the installation check.

It is also possible that your binaries aren't accessible to your web server user (especially while using a cron task or wp-cli to create posts). You can link these binaries using the following commands:

ln -s /usr/local/bin/opt-jpg /usr/bin/opt-jpg
ln -s /usr/local/bin/opt-png /usr/bin/opt-png
ln -s /usr/local/bin/opt-gif /usr/bin/opt-gif
ln -s /usr/local/bin/tempname /usr/bin/tempname
ln -s /usr/local/bin/imagsize /usr/bin/imagsize
ln -s /usr/local/bin/gifsicle /usr/bin/gifsicle
ln -s /usr/local/bin/pngcrush /usr/bin/pngcrush
ln -s /usr/local/bin/pngrecolor /usr/bin/pngrecolor
ln -s /usr/local/bin/pngstrip /usr/bin/pngstrip

== Frequently Asked Questions ==

= Can I use WP Image Optimizer with a Windows server? =

No, WP Image Optimizer only supports Linux.

= Do I have to have littleutils? =

Yes, WP Image Optimizer will not work if littleutils isn\'t installed. This plugin expects *opt-jpg*, *opt-png*, and *opt-gif* to be in the PATH.

= Do I have to have jpeg-recompress? =

Jpeg-Recompress is not mandatory but lossy jpeg optimization won\'t work if it isn\'t installed

== Screenshots ==
1. Additional optimize column added to media listing. You can see your savings, or manually optimize individual images.
2. Bulk optimization page. You can optimize all your images at once. This is very useful for existing blogs that have lots of images.

== Changelog ==

= 1.1.4 =
* Conditionnal script loading
* Plugin is now translatable
* Various fixes

= 1.1.3 =
* Bug fix

= 1.1.2 =
* UI improvments
* Queries optimizations

= 1.1.1 =
* Added cron task option to resize 250 unoptimized images / hour

= 1.1.0 =
* Lossy Compression for jpeg files
* Optimized ajax and php for bulk resize, now supports bulk optimization of 200k + medias at once

= 1.0.1 =
* Removed unnecessary frontend files
* Readme updated

= 1.0.0 =
* First edition

== Contact and Credits ==

Wp Image optimizer Plugin Icon by [Freepik](http://www.freepik.com)
Aliens by [OpenClipart](https://openclipart.org)
Jpeg Recompress installation instructions by [WP Bullet](https://guides.wp-bullet.com/batch-optimize-jpg-lossy-linux-command-line-with-jpeg-recompress/)
Wp Image optimizer is based on the famous CW Image Optimizer by [Jacob Allred](http://www.jacoballred.com/).
