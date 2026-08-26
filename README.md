# How To Install

LiteCart is a lightweight e-commerce platform for online merchants. Developed in PHP, HTML 5, and CSS 3.

LiteCart is a registered trademark, property of founder T. Almroth - [LiteCart AB](https://www.litecart.net/).

For an updated version of the upgrade documentation, visit [How To Upgrade](https://www.litecart.net/wiki/how_to_upgrade).

What you need:

* An Apache2 web server running PHP 8.0 or higher. Latest stable PHP release recommended for best performance.
* A MySQL 8.0+ or MariaDB 10.4+ database.

## Installation Instructions

Please note running your own website requires some common sense of web knowledge. If this is not your area of expertise, ask a friend or colleague to assist you.

1. Connect to your web host via FTP using your favourite FTP software.

2. Transfer the contents of the folder src/ in this archive (yes the contents inside the folder - not the folder itself). Transfer it to your website root directory. Using subdirectories is supported but not recommended.

```text
Examples:

* /var/www/
* /home/username/src/
* C:\xampp\htdocs\
```

Paths are machine specific, ask your web hosting provider if you are uncertain where this folder is.

3. Point your web browser to the URL of your website followed by the subfolder `install/` e.g. `http://www.mysite.com/install/`. If you placed LiteCart in a subfolder of the web root, the path should be something like `http://www.mysite.com/litecart/install`. The installation page should now load.

4. Carefully read the instructions on the page. Fill in your details for database, region, etc. Click the Install button when you are ready.

If everything went well LiteCart should be successfully installed.

For community written installation instructions see [How To Install](https://www.litecart.net/en/wiki/how_to_install).

## How To Get Started

To get your store up and running, see our [step list](https://www.litecart.net/en/wiki/get_started) for best practice.

## Folder Structure

All paths below are relative to `src/` (the document root).

```text
src/
├── assets/                     - Client-side third party libraries (javascripts, stylesheets, fonts)
│   ├── litecore/               - LiteCore JavaScript and stylesheet framework
│   ├── jquery/                 - jQuery 4+ JavaScript DOM library
│   ├── trumbowyg/              - WYSIWYG JavaScript editor library
│   └── chartist/               - Chart library
├── backend/                    - Admin panel (path controlled by BACKEND_ALIAS in config.inc.php)
│   ├── apps/                   - Backend applications (catalog, orders, customers, settings, modules, etc.)
│   ├── mcp/                    - Backend MCP tools (JSON-RPC server)
│   ├── pages/                  - Backend page controllers (login, about, search)
│   ├── partials/               - Backend UI components
│   ├── routes/                 - Backend URL routing
│   ├── template/               - Backend HTML/CSS/JS/SCSS template
│   └── widgets/                - Dashboard widgets (orders, stats, graphs, addons, discussions)
├── frontend/                   - Storefront
│   ├── mcp/                    - Frontend MCP tools (JSON-RPC server)
│   ├── pages/                  - Frontend page controllers (product, category, checkout, account, etc.)
│   ├── partials/               - Frontend UI components (site_header, site_footer, navigation, boxes)
│   ├── routes/                 - Frontend URL routing
│   └── templates/              - Frontend template themes
│       └── default/            - Default theme
│           ├── css/            - Compiled stylesheets (CSS)
│           ├── fonts/          - Fonts
│           ├── images/         - Theme images
│           ├── js/             - JavaScripts
│           ├── emails/         - Transactional email templates
│           ├── layouts/        - Visuals for content surroundings
│           ├── pages/          - Visuals for pages
│           ├── scss/           - Syntactically Awesome Style Sheets (SCSS) source files
│           └── partials/       - Visuals for partials
├── shared/                     - Core application logic
│   ├── abstracts/              - Base classes (abs_module, abs_modules, abs_reference_entity)
│   ├── clients/                - Service clients (HTTP, SMTP)
│   ├── entities/               - Data entities (product, order, customer, etc.)
│   ├── functions/              - Helper functions, called via f::name()
│   ├── modules/                - Plug 'n play modules
│   │   ├── checkout/
│   │   ├── customer/
│   │   ├── order/
│   │   ├── order_total/
│   │   ├── shipping/
│   │   ├── payment/
│   │   └── jobs/
│   ├── nodes/                  - Static service nodes (database, session, cache, settings, events, etc.)
│   ├── references/             - Read-only factory model reference objects
│   └── streams/                - Stream wrappers (app://, storage://)
├── storage/
│   ├── cache/                  - Application cache
│   ├── downloads/              - Downloads storage
│   ├── images/                 - Image storage
│   ├── logs/                   - Application logs
│   ├── uploads/                - WYSIWYG uploads
│   ├── vmods/                  - Virtual modifications and virtual file system
│   └── config.inc.php          - Application configuration
├── install/                    - Installation wizard, migrations, and country configs
├── tests/                      - Platform tests (entity CRUD and integration tests)
├── vendor/                     - Server-side third party libraries (Composer)
└── index.php                   - Main application entry point
```

## Connect an AI agent to LiteCart's MCP Tools

LiteCart has built in MCP server allowing an AI agent to access your MCP Tools.

To configure use via CLI, edit your agent's MCP settings (mcp.json):

```json
{
	"servers": {
		...
		"litecart": {
			"command": "php",
			"args": ["/path/to/index.php", "mcp"],
			"env": {
				"PHP_AUTH_USER": "your-username-here",
				"PHP_AUTH_PW": "your-password-here"
			}
		}
	}
}
```

To configure remote use via Web:

```json
{
	"servers": {
		...
		"litecart-web": {
			"url": "https://litecart.tld/admin/mcp",
			"type": "http",
			"headers": {
				"Authorization": "Basic YWRtaW46MTIzNA=="
			}
		}
	}
}
```

## Build On LiteCart

Make sure you have a good understanding of LiteCart's platform model.

* [Get Familiar With LiteCart's Components](https://www.litecart.net/en/wiki/introduction)

## How To Guides

* [Create a New Page](https://www.litecart.net/en/wiki/how_to_create_a_page)
* [Create a Box](https://www.litecart.net/en/wiki/how_to_create_a_box)
* [Create a Backend App](https://www.litecart.net/en/wiki/how_to_create_an_admin_app)
* [Create a Backend Widget](https://www.litecart.net/en/wiki/how_to_create_a_backend_widget)
* [Change the Look of Your Store](https://www.litecart.net/en/wiki/how_to_change_the_look_of_your_store)
* [Create a Template](https://www.litecart.net/en/wiki/how_to_create_a_template)
* [Create a Regional Installation Package](https://www.litecart.net/en/wiki/regional_installation_packages)
* [Create a Customer Module](https://www.litecart.net/en/wiki/how_to_create_a_customer_module)
* [Create an Order Module](https://www.litecart.net/en/wiki/how_to_create_an_order_module)
* [Create an Order Total Module](https://www.litecart.net/en/wiki/how_to_create_an_order_total_module)
* [Create a Shipping Module](https://www.litecart.net/en/wiki/how_to_create_a_shipping_module)
* [Create a Payment Module](https://www.litecart.net/en/wiki/how_to_create_a_payment_module)
* [Create a Job Module](https://www.litecart.net/en/wiki/how_to_create_a_job_module)
* [Create a vMod™](https://www.litecart.net/en/wiki/how_to_create_a_vmod) (Virtual modification technology by LiteCart)
* [Create an Entity](https://www.litecart.net/en/wiki/how_to_create_an_entity)
* [Premium API](https://www.litecart.net/en/wiki/premium-api)
* [How to Create an A/B Test](https://www.litecart.net/en/wiki/how_to_create_a_b_testing)

## How To Change The Look Of Your Store

Navigate to the folder ~/frontend/templates/ and you will find all HTML content and CSS files to edit. If you chose SCSS instead of CSS during install you will need edit the .scss files instead of .css and use an SCSS compiler to build new CSS versions. We recommend downloading our [Developer Kit](https://www.litecart.net/addons/163/developer-kit) that has a preconfigured SCSS compiler and JavaScript minifier.

See our wiki article [How To Create a Template](https://www.litecart.net/en/wiki/how_to_create_a_template).

## Contributing

See [CONTRIBUTING.md](CONTRIBUTING.md) for guidelines on contributing to the project.

## License

This project is licensed under the terms specified in [LICENSE.md](LICENSE.md).

## See Also

* [Official Website](https://www.litecart.net)
* [GitHub Repository](https://github.com/litecart/litecart)
* [Issue Tracker](https://github.com/litecart/litecart/issues)
* [Community Forums](https://www.litecart.net/forums/)
* [Community Wiki](https://wiki.litecart.net/)
