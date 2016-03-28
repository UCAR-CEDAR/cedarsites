<?php
# Alert the user that this is not a valid access point to MediaWiki if they
# try to access the special pages file directly.
if ( !defined( 'MEDIAWIKI' ) ) {
        echo <<<EOT
To install my extension, put the following line in LocalSettings.php:
require_once( "\$IP/extensions/cedarsites/cedarsites.php" );
EOT;
        exit( 1 );
}
 
$wgExtensionCredits[ 'specialpage' ][] = array(
        'path' => __FILE__,
        'name' => 'CedarSites',
        'author' => 'Patrick West',
        'url' => 'http://cedarweb.hao.ucar.edu/cedaradmin/index.php/Extensions:cedarsites',
        'descriptionmsg' => 'cedarsites-desc',
        'version' => '1.0.1',
);
 
$wgAutoloadClasses[ 'CedarSites' ] = __DIR__ .  '/CedarSites_body.php'; # Location of the SpecialMyExtension class (Tell MediaWiki to load this file)
$wgExtensionMessagesFiles[ 'CedarSites' ] = __DIR__ .  '/CedarSites.i18n.php'; # Location of a messages file (Tell MediaWiki to load this file)
$wgSpecialPages[ 'CedarSites' ] = 'CedarSites'; # Tell MediaWiki about the new special page and its class name
$wgGroupPermissions['sysop']['cedar_admin'] = true;

