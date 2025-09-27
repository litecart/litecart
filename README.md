![LiteCart®](https://www.litecart.net/images/logotype.svg "LiteCart®")

LiteCart is a lightweight e-commerce platform for online merchants. Developed in PHP, HTML 5, and CSS 3.

LiteCart is a registered trademark, property of founder T. Almroth - [LiteCart AB](http://www.litecart.net/).


# How To Install

For an updated version of the upgrade documentation, visit https://www.litecart.net/wiki/how_to_install

What you need:

	* An Apache2 web server running PHP 5.6 or higher. Latest stable PHP release recommended for best performance.
	* A MySQL 5.7+ or MariaDB database.

## Installation Instructions

Please note running your own website requires some common sense of web knowledge. If this is not your area of expertise, ask a friend or collegue to assist you.

1. Connect to your web host via FTP using your favourite FTP software.

2. Transfer the contents of the folder public_html/ in this archive (yes the contents inside the folder - not the folder itself). Transfer it to your website root directory. Using subdirectories is supported but not recommended.

Examples:

	* /var/www/
	* /home/username/public_html/
	* C:\xampp\htdocs\

Paths are machine specific, ask your web hosting provider if you are uncertain where this folder is.

3. Point your web browser to the URL of your website followed by the subfolder install/ e.g. http://www.mysite.com/install/. If you placed LiteCart in a subfolder of the web root, the path should be something like http://www.mysite.com/litecart/install. The installation page should now load.

4. Carefully read the instructions on the page. Fill in your details for database, region, etc. Click the Install button when you are ready.

If everything went well LiteCart should be successfully installed.

For community written installation instructions see https://www.litecart.net/en/wiki/how_to_install.


# How To Get Started

To get your store up and running, see our [step list](https://www.litecart.net/en/wiki/get_started) for best practise.


# Folder Structure

```
litecart/
├── assets/                     - Public/client side  third party libraries e.g. javascripts, stylesheets, fonts
│   ├── litecore/               - LiteCore Javascript and Stylesheet framework
│   ├── jquery/                 - jQuery 4+ JavaScript DOM library
│   └── trumbowyg/              - WSIWYG Javascript library
├── backend/                    - Admin panel (called by BACKED_ALIAS defined in config.inc.php)
│   ├── apps/                   - Backend apps
│   ├── pages/                  - Backend page controllers
│   ├── routes/                 - Backend routes and URL rewriting
│   ├── template/               - Backend template
│   └── widgets/                - Backend dashboard widgets
├── frontend/                   - Store Front
│   ├── pages/                  - Full page content controllers
│   ├── partials/               - Partial content controllers
│   ├── routes/                 - Frontend routes and URL rewriting
│   └── templates/              - Frontend templates
│       └── default/
│           ├── css/            - Cascade Style Sheets (CSS)
│           ├── fonts/          - Fonts
│           ├── images/         - Images
│           ├── js/             - JavaScripts
│           ├── (less/)         - Leaner Style Sheets (LESS) source files
│           ├── (scss/)         - Sassy Cascading Style Sheets (SCSS) source files
│           ├── layouts/        - Visuals for content suroundings
│           ├── pages/          - Visuals for pages
│           ├── partials/       - Visuals for partials
│           └── config.inc.php  - Template settings
├── includes/
│   ├── abstracts/              - Class templates
│   ├── clients/                – Clients, Service Layers, and Wrappers
│   ├── entities/               - Entity objects
│   ├── functions/              - Helper functions, called via lib_func.inc.php using functions::name()
│   ├── nodes/                  – System nodes and event based hook events
│   ├── modules/                - Plug 'n play modules
│   │   ├── customer/
│   │   ├── order/
│   │   ├── order_total/
│   │   ├── shipping/
│   │   ├── payment/
│   │   └── jobs/
│   └── references/             - Read-only factory model reference objects
├── storage/
│   ├── cache/                  - Application cache
│   ├── downloads/              - Downloads storage
│   ├── images/                 - Image storage
│   ├── logs/                   - Application logs
│   ├── uploads/                - WYSIWYG uploads
│   ├── vmods/                  – Virtual Modifcations and Virtual File System
│   └── config.inc.php          - Application configuration
├── install/                    – Installation wizard
├── vendor/                     - Server-side third party libraries
│   └── composer/               - Composer for LiteCart (https://www.litecart.net/addons/255/composer-for-litecart)
└── index.php                   - Main application entry point
```


# Build On LiteCart

Make sure you have a good understanding of LiteCart's platform model.

* [Get Familiar With LiteCart's Components](introduction)


# How To Guides

* [Create a New Page](how_to_create_a_page)
* [Create a Box](how_to_create_a_box)
* [Create a Backend App](how_to_create_an_admin_app)
* [Create a Backend Widget](how_to_create_a_backend_widget)
* [Change the Look of Your Store](how_to_change_the_look_of_your_store)
* [Create a Template](how_to_create_a_template)
* [Create a Page](how_to_create_a_page)
* [Create a Regional Installation Package](regional_installation_packages)
* [Create a Customer Module](how_to_create_a_customer_module)
* [Create an Order Module](how_to_create_an_order_module)
* [Create an Order Total Module](how_to_create_an_order_total_module)
* [Create a Shipping Module](how_to_create_a_shipping_module)
* [Create a Payment Module](how_to_create_a_payment_module)
* [Create a Job Module](how_to_create_a_job_module)
* [Create a vMod™](how_to_create_a_vmod) (Virtual modification technology by LiteCart)
* [Create an Entity](how_to_create_an_entity)


# How To Change The Look Of Your Store

Navigate to the folder ~/frontend/templates/ and you will find all HTML content and CSS files to edit. If you chose LESS instead of CSS during install you will need edit the .less files instead of .css and use a LESS compiler to build new CSS versions. We recommend downloading our [Developer Kit](https://www.litecart.net/addons/163/developer-kit) that has a preconfigured LESS compiler and JavaScript minifyer.

See our wiki article [How To Create a Template](https://www.litecart.net/en/wiki/how_to_create_a_template).


## Contributing

See [CONTRIBUTING.md](CONTRIBUTING.md) for guidelines on contributing to the project.


## License

This project is licensed under the terms specified in [LICENSE.md](LICENSE.md).


# See Also

	- [Official Website](http://www.litecart.net)
	- [GitHub Repository](https://github.com/litecart/litecart)
	- [Issue Tracker](https://github.com/litecart/litecart/issues)
	- [Community Forums](http://www.litecart.net/forums/)
	- [Community Wiki](http://wiki.litecart.net/)
