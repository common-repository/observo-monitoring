=== Observo Monitoring ===
Contributors: maG66
Donate link: https://www.observo-monitoring.com
Tags: monitoring,server
Requires at least: 4.4 
Tested up to: 6.0.0
Requires PHP: 5.4
Stable tag: 1.0.6
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
 
Monitor your Server Memory, Disk and CPU from external server

== Description ==
Install the plugin and check the Status URL Link. You can submit it to observo-monitoring.com to monitor it 24/7.
- shell_exec must be available for cpu and memory
- The plugin will install an open rest api
- External servers can access this api to get performance stats
- Services like observo-monitoring.com can give you an added value

== Frequently Asked Questions ==
= Is there a free version of observo-monitoring.com to scan my site? =
Yes, you can try Observo Monitoring for free with 60 start credits
= Why does the plugin need write access to the plugin directory? =
The plugin installs an opensource script named "sysinfo". To make sure that no one simply accesses the script without your knowing that, the directory is renamed individually during installation.
= What is the copyright of sysinfo? =
Michael Cramer <BigMichi1@users.sourceforge.net>, 2009 phpSysInfo, http://opensource.org/licenses/gpl-2.0.php GNU General Public License version 2, or (at your option) any later version

== Changelog ==
= 1.0.6 =
* Update Error Bugfix
= 1.0.5 =
* Update Error Bugfix
= 1.0.4 =
* Update Error Bugfix
= 1.0.3 =
* Update Error Bugfix
= 1.0.2 =
* Added ?code=XY to open rest api url
= 1.0.1 =
* Sysinfo added for more precise cpu load information
= 1.0.0 =
* First Release

== Upgrade Notice ==
= 1.0.0 =
First Release

== Installation ==
1. Upload the entire folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Check Status URL Link (It's next to the plugin deactivate button, if you missed the notice)

== Screenshots ==

1. Example json Output

== Credits ==
[Observo Monitoring](https://www.observo-monitoring.com "Observo Monitoring")