# Upgrade

## Automatic Upgrade

  This LiteCart release can upgrade from any previous master release version. The automated upgrade procedure will sort out and apply all necessary patches.

  Note: Add-ons are version specific and might cause your upgraded platform to malfunction. Make sure all your add-ons are up to date.
  
### Instructions

  1. Backup your files AND database!! Do not underestimate the damage that can be caused by a failed upgrade process.
  
  2. Note your current platform version. This is seen in the admin panel footer or in the file ~/includes/app_header.inc.php.
  
  3. Upload the contents of the folder public_html/* to the corresponding path of your installation replacing the old files. Any modified files will be overwritten!
  
  4. Point your browser to http://www.yoursite.com/install/upgrade.php and follow the instructions on the page.
  
  5. Make sure everything went fine and delete the install/ folder.

     If there are complications, try switching to the default template and disable any vQmods.
  
  If you need help, turn to our forums at http://forums.litecart.net.
  
## Performing a Manual Upgrade
  
  This chapter is no longer provided as it is recommended to always use the automatic upgrade script.
  
  Doing a manual upgrade you must note any changes stated in the upgrade patches. See ~/install/upgrade_patches/*.
  