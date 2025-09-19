![LiteCart®](https://www.litecart.net/images/logotype.svg "LiteCart®")

----------------------------------------------------------------------

LiteCart is a lightweight e-commerce platform for online merchants. Developed in PHP, HTML 5, and CSS 3.

LiteCart is a registered trademark, property of founder T. Almroth - [LiteCart AB](http://www.litecart.net/).


## Tech Stack

- **Application**: PHP, HTML5, CSS3, JavaScript
- **Server**: Apache2, Nginx, Caddy, IIS or compatible web server
- **Database**: MySQL or MariaDB
- **Build Tools**: Node.js (via package.json), Grunt (gruntfile.js)


## How To Install

For an updated version of the upgrade documentation, visit https://www.litecart.net/wiki/how_to_install

What you need:

  * An Apache2 web server running PHP 5.6 or higher. Latest stable PHP release recommended for best performance.
  * A MySQL 5.7+ or MariaDB database.


### Instructions

Please note: Running your own website requires some common sense and basic webmastering skills. If this is not your area of expertise, ask a collegue or friend to assist you.

1. Connect to your web host via FTP using your favourite FTP software.

2. Transfer the contents of the folder public_html/ in this archive (yes the contents inside the folder - not the folder itself). Transfer it to your website root directory. Using subdirectories is supported but not recommended.

    Example:

    /var/www/

    /home/username/public_html/

    C:\xampp\htdocs\

Paths are machine specific, so talk to your web host if you are uncertain where this folder is.

3. Point your web browser to the URL of your website followed by the subfolder install/ e.g. http://www.mysite.com/install/. If you placed LiteCart in a subfolder of the web root, the path should be something like http://www.mysite.com/litecart/install. The installation page should now load.

4. Carefully read the instructions on the page. Fill in your details for database, region, etc. Click the Install button when you are ready.

If everything went well LiteCart should be successfully installed.

For community written installation instructions see https://www.litecart.net/en/wiki/how_to_install.


## How To Get Started

To get your store up and running, see our [step list](https://www.litecart.net/en/wiki/how_to_install) for best practise.


## Folder Structure

```
/                   – Root
├── admin/          – Backend
│   ├── *.app/      – Admin apps
│   └── *.widget/   – Dashboard widgets
├── cache/          – Cache Directory
├── data/           – Data Storage
├── ext/            – Extensions/Extras/External/Vendors/Assets
│   ├── jquery/
│   └── ...
├── images/         – Graphics
├── includes/
│   ├── abstracts/  – Class templates
│   ├── boxes/      – Partials
│   ├── entities/   – Entity objects
│   ├── functions/  – Helper functions, called via lib_func.inc.php using functions::name()
│   ├── library/    – System nodes and events
│   ├── modules/    – Modules
│   ├── references/ – Read-only factory model reference objects
│   ├── routes/     – Route mapping
│   ├── templates/  – HTML and Output
│   └── wrappers/   – Wrappers, Service Layers, and Clients
├── install/        – Installation wizard
├── logs/           – Application logs
├── pages/          – Documents
└── vmods/          – Virtual Modifcations
```


## How To Change The Look Of Your Store

Navigate to the folder ~/includes/templates/default.catalog/ and you will find all HTML content and CSS files to edit. If you want to adapt your work with LESS instead of CSS you will need a LESS compiler. We recommend downloading our [Developer Kit](https://www.litecart.net/addons/163/developer-kit) that has a preconfigured LESS compiler and JavaScript minifyer.

See our wiki article [How To Create a Template](https://www.litecart.net/en/wiki/how_to_create_a_template).


## Contributing

See [CONTRIBUTING.md](CONTRIBUTING.md) for guidelines on contributing to the project.


## License

This project is licensed under the terms specified in [LICENSE.md](LICENSE.md).


## Links

  * [Official Website](http://www.litecart.net)
  * [GitHub Repository](https://github.com/litecart/litecart)
  * [Issue Tracker](https://github.com/litecart/litecart/issues)
  * [Community Forums](http://www.litecart.net/forums/)
  * [Community Wiki](http://wiki.litecart.net/)
