Pamphlet Radio Player

Installation instructions

For usage inside Pamphlet:

Place the 'radio.php' file and 'radio' folder inside the plugins folder
Load up Pamphlet, go to the Radio tab and click Configure
That's it!

For usage outside Pamphlet:

The folder must go in a specific location to ensure all file links resolve correctly

1. Create a folder called "plugins" in the same directory as the PHP file which will be calling this function
2. Place the 'radio' folder inside this new folder
3. Include the following code in your page

include("plugins/radio/player.php");
echo radioPlayer::build($url,$cover,$nowplaying);

The following parameters can be passed to the player
$url: Stream URL
$cover: URL of a photo to be displayed in the background of the player
$nowplaying: URL of a page containing the station's currently playing song

