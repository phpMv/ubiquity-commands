<?php
namespace Ubiquity\devtools\cmd;

use Ubiquity\utils\base\UIntrospection;
use Ubiquity\utils\base\UFileSystem;

/**
 * Define a command complete desciption.
 * Ubiquity\devtools\cmd$Command
 * This class is part of Ubiquity
 *
 * @author jc
 * @version 1.0.3
 *
 */
class Command {

	protected $name;

	protected $description;

	protected $value;

	protected $aliases;

	protected $parameters;

	protected $examples;

	protected $category;

	protected static $customCommands;

	protected static $customAliases;

	public function __construct(string $name = '', string $value = '', string $description = '', array $aliases = [], array $parameters = [], array $examples = [], string $category = 'custom') {
		$this->name = $name;
		$this->value = $value;
		$this->description = $description;
		$this->aliases = $aliases;
		$this->parameters = $parameters;
		$this->examples = $examples;
		$this->category = $category;
	}

	public function simpleString() {
		return "\t" . $this->name . " [" . $this->value . "]\t\t" . $this->description;
	}

	public function longString() {
		$dec = "\t";
		$result = "\n<b>■ " . $this->name . "</b> [" . ConsoleFormatter::colorize($this->value, ConsoleFormatter::YELLOW) . "] =>";
		$result .= "\n" . $dec . "· " . $this->description;
		if (\count($this->aliases) > 0) {
			$result .= "\n" . $dec . "· Aliases :";
			$aliases = $this->aliases;
			array_walk($aliases, function (&$alias) {
				$alias = "<b>" . $alias . "</b>";
			});
			$result .= " " . implode(",", $aliases);
		}
		if (\count($this->parameters) > 0) {
			$result .= "\n" . $dec . "· Parameters :";
			foreach ($this->parameters as $param => $content) {
				$result .= "\n" . $dec . "\t<b>-" . $param . "</b>";
				$result .= $content . "\n";
			}
		}
		if (\count($this->examples) > 0) {
			$result .= "\n" . $dec . "<b>× Samples :</b>";
			foreach ($this->examples as $desc => $sample) {
				if (is_string($desc)) {
					$result .= "\n" . $dec . "\t" . ConsoleFormatter::colorize($desc, ConsoleFormatter::LIGHT_GRAY);
				}
				$result .= "\n" . $dec . "\t  · " . ConsoleFormatter::colorize($sample, ConsoleFormatter::CYAN);
			}
		}
		return $result;
	}

	public static function getInfo($cmd) {
		$commands = self::getCommands();
		$result = [];
		if ($cmd != null) {
			foreach ($commands as $command) {
				if ($command->getName() == $cmd) {
					return [
						[
							"info" => "Command <b>{$cmd}</b> find by name",
							"cmd" => $command
						]
					];
				} elseif (\array_search($cmd, $command->getAliases()) !== false) {
					$result[] = [
						"info" => "Command <b>{$cmd}</b> find by alias",
						"cmd" => $command
					];
				} elseif (\stripos($command->getDescription(), $cmd) !== false) {
					$result[] = [
						"info" => "Command <b>{$cmd}</b> find in description",
						"cmd" => $command
					];
				} else {
					$parameters = $command->getParameters();
					foreach ($parameters as $parameter) {
						if ($cmd == $parameter->getName()) {
							$result[] = [
								"info" => "Command <b>{$cmd}</b> find by the name of a parameter",
								"cmd" => $command
							];
						}
						if (\stripos($parameter->getDescription(), $cmd) !== false) {
							$result[] = [
								"info" => "Command <b>{$cmd}</b> find in parameter description",
								"cmd" => $command
							];
						}
					}
				}
			}
		}
		return $result;
	}

	public static function project() {
		return new Command("project", "projectName", "Creates a new #ubiquity project.", [
			"new",
			"create_project"
		], [
			"b" => Parameter::create("dbName", "Sets the database name.", []),
			"s" => Parameter::create("serverName", "Defines the db server address.", [], "127.0.0.1"),
			"p" => Parameter::create("port", "Defines the db server port.", [], "3306"),
			"u" => Parameter::create("user", "Defines the db server user.", [], "root"),
			"w" => Parameter::create("password", "Defines the db server password.", [], ""),
			"h" => Parameter::create("themes", "Install themes.", [
				"semantic",
				"bootstrap",
				"foundation"
			], ""),
			"m" => Parameter::create("all-models", "Creates all models from database.", [], ""),
			"a" => Parameter::create("admin", "Adds UbiquityMyAdmin tool.", [
				"true",
				"false"
			], "false"),
			"i" => Parameter::create("siteUrl", "Sets the site base URL.", []),
			"e" => Parameter::create("rewriteBase", "Sets .htaccess file rewriteBase.", [])
		], [
			'Creates a new project' => 'Ubiquity new blog',
			'With admin interface' => 'Ubiquity new blog -a',
			'and models generation' => 'Ubiquity new blog -a -m -b=blogDB'
		], 'installation');
	}

	public static function controller() {
		return new Command("controller", "controllerName", "Creates a new controller.", [
			'create_controller',
			'create:controller',
			'create-controller',
			'createController'
		], [
			"v" => Parameter::create("views", "creates an associated view folder and index.html", [
				"true",
				"false"
			], 'false'),
			'o' => Parameter::create('domain', 'The domain in which to create the controller.', [], '')
		], [
			'Creates a controller' => 'Ubiquity controller UserController',
			'with its associated view' => 'Ubiquity controller UserController -v',
			'Creates a controller in the orga domain' => 'Ubiquity controller OrgaController -o=orga'
		], 'controllers');
	}

	public static function genModel() {
		return new Command("genModel", "tableName", "Generates a new model from an existing table.", [
			'gen_model',
			'gen:model',
			'gen-model',
			'genModel'
		], [
			'd' => Parameter::create('database', 'The database connection to use', [], 'default'),
			'a' => Parameter::create('access', 'The default access to the class members', [], 'private'),
			'o' => Parameter::create('domain', 'The domain in which to create the model.', [], '')
		], [
			'Ubiquity genModel User',
			'Ubiquity genModel Author -d=projects',
			'Ubiquity genModel Author -d=projects -a=protected'
		], 'models');
	}

	public static function model() {
		return new Command("model", "modelName", "Generates models from scratch.", [
			'create_model',
			'create:model',
			'create-model',
			'createModel',
			'new_model',
			'new:model',
			'new-model',
			'newModel'
		], [
			'd' => Parameter::create('database', 'The database connection to use', [], 'default'),
			'o' => Parameter::create('domain', 'The domain in which to create the model.', [], ''),
			'k' => Parameter::create('autoincPk', 'The default primary key defined as autoinc.', [], 'id')
		], [
			'Ubiquity model User',
			'Ubiquity model Author -d=projects',
			'Ubiquity model Group,User -o=orga'
		], 'models');
	}

	public static function routes() {
		return new Command("info-routes", "", "Display the cached routes.", [
			'info:r',
			'info_routes',
			'info:routes',
			'infoRoutes'
		], [
			"t" => Parameter::create("type", "Defines the type of routes to display.", [
				"all",
				"routes",
				"rest"
			]),
			"l" => Parameter::create("limit", " Specifies the number of routes to return.", []),
			"o" => Parameter::create("offset", "Specifies the number of routes to skip before starting to return.", []),
			"s" => Parameter::create("search", "Search routes corresponding to a path.", []),
			"m" => Parameter::create("method", "Allows to specify a method with search attribute.", [
				'get',
				'post',
				'put',
				'delete',
				'patch'
			])
		], [
			'All routes' => 'Ubiquity info:routes',
			'Rest routes' => 'Ubiquity info:routes -type=rest',
			'Only the routes with the method post' => 'Ubiquity info:routes -type=rest -m=-post'
		], 'router');
	}

	public static function version() {
		return new Command("version", "", "Return PHP, Framework and dev-tools versions.", [], [], [], 'system');
	}

	public static function allModels() {
		return new Command("all-models", "", "Generates all models from database.", [
			'create-all-models',
			'all_models',
			'all:models',
			'allModels'
		], [
			'd' => Parameter::create('database', 'The database connection to use (offset)', [], 'default'),
			'a' => Parameter::create('access', 'The default access to the class members', [], 'private'),
			'o' => Parameter::create('domain', 'The domain in which to create the models.', [], '')
		], [
			'Ubiquity all-models',
			'Ubiquity all-models -d=projects',
			'Ubiquity all-models -d=projects -a=protected'
		], 'models');
	}

	public static function clearCache() {
		return new Command("clear-cache", "", "Clear models cache.", [
			'clear_cache',
			'clear:cache',
			'clearCache'
		], [
			"t" => Parameter::create("type", "Defines the type of cache to reset.", [
				"all",
				"annotations",
				"controllers",
				"rest",
				"models",
				"queries",
				"views"
			], 'all')
		], [
			'Clear all caches' => 'Ubiquity clear-cache -t=all',
			'Clear models cache' => 'Ubiquity clear-cache -t=models'
		], 'cache');
	}

	public static function initCache() {
		return new Command("init-cache", "", "Init the cache for models, router, rest.", [
			'init_cache',
			'init:cache',
			'initCache'
		], [
			"t" => Parameter::create("type", "Defines the type of cache to create.", [
				"all",
				"controllers",
				"acls",
				"rest",
				"models"
			], 'all')
		], [
			'Init all caches' => 'Ubiquity init-cache',
			'Init models cache' => 'Ubiquity init-cache -t=models'
		], 'cache');
	}

	public static function serve() {
		return new Command("serve", "", "Start a web server.", [], [
			"h" => Parameter::create("host", "Sets the host ip address.", [], '127.0.0.1'),
			"p" => Parameter::create("port", "Sets the listen port number.", [], 8090),
			"n" => Parameter::create("nolr", "Starts without live-reload.", [], false),
			"l" => Parameter::create("lrport", "Sets the live-reload listen port number.", [], '35729'),
			"t" => Parameter::create("type", "Sets the server type.", [
				'php',
				'react',
				'swoole',
				'roadrunner'
			], 'php')
		], [
			'Starts a php server at 127.0.0.1:8090' => 'Ubiquity serve',
			'Starts a reactPHP server at 127.0.0.1:8080' => 'Ubiquity serve -t=react'
		], 'servers');
	}

	public static function liveReload() {
		return new Command("livereload", "path", "Start the live reload server.", [
			'live-reload',
			'live'
		], [
			"p" => Parameter::create("port", "Sets the listen port number.", [], 35729),
			"e" => Parameter::create("exts", "Specify extentions to observe .", [], 'php,html'),
			"x" => Parameter::create("exclusions", "Exclude file matching pattern .", [], 'cache/,logs/')
		], [
			'Starts the live-reload server at 127.0.0.1:35729' => 'Ubiquity live-reload',
			'Starts the live-reload server at 127.0.0.1:35800 excluding logs directory' => 'Ubiquity live-reload -p=35800 -x=logs/'
		], 'servers');
	}

	public static function selfUpdate() {
		return new Command("self-update", "", "Updates Ubiquity framework for the current project.", [], [], [], 'installation');
	}

	public static function admin() {
		return new Command("admin", "", "Add UbiquityMyAdmin webtools to the current project.", [], [], [], 'installation');
	}

	public static function help() {
		return new Command("help", "?", "Get some help about a dev-tools command.", [], [], [
			'Get some help about crud' => 'Ubiquity help crud'
		], 'system');
	}

	public static function crudController() {
		return new Command("crud", "crudControllerName", "Creates a new CRUD controller.", [
			'crud_controller',
			'crud:controller',
			'crud-controller',
			'crudController'
		], [
			"r" => Parameter::create("resource", "The model used", []),
			"d" => Parameter::create("datas", "The associated Datas class", [
				"true",
				"false"
			], "true"),
			"v" => Parameter::create("viewer", "The associated Viewer class", [
				"true",
				"false"
			], "true"),
			"e" => Parameter::create("events", "The associated Events class", [
				"true",
				"false"
			], "true"),
			"t" => Parameter::create("templates", "The templates to modify", [
				"index",
				"form",
				"display"
			], "index,form,display"),
			"p" => Parameter::create("path", "The associated route", []),
			'o' => Parameter::create('domain', 'The domain in which to create the controller.', [], '')
		], [
			'Creates a crud controller for the class models\User' => 'Ubiquity crud CrudUsers -r=User',
			'and associates a route to it' => 'Ubiquity crud CrudUsers -r=User -p=/users',
			'allows customization of index and form templates' => 'Ubiquity crud CrudUsers -r=User -t=index,form',
			'Creates a crud controller for the class models\projects\Author' => 'Ubiquity crud Authors -r=models\projects\Author'
		], 'controllers');
	}

	public static function indexCrudController() {
		return new Command("crud-index", "crudControllerName", "Creates a new index-CRUD controller.", [
			'crud-index-controller',
			'crud_index',
			'crud:index',
			'crudIndex'
		], [
			"d" => Parameter::create("datas", "The associated Datas class", [
				"true",
				"false"
			], "true"),
			"v" => Parameter::create("viewer", "The associated Viewer class", [
				"true",
				"false"
			], "true"),
			"e" => Parameter::create("events", "The associated Events class", [
				"true",
				"false"
			], "true"),
			"t" => Parameter::create("templates", "The templates to modify", [
				"index",
				"form",
				"display",
				"item",
				"itemHome"
			], "index,form,display,home,itemHome"),
			"p" => Parameter::create("path", "The associated route", [], '{resource}'),
			'o' => Parameter::create('domain', 'The domain in which to create the controller.', [], '')
		], [
			'Creates an index crud controller' => 'Ubiquity crud-index MainCrud -p=crud/{resource}',
			'allows customization of index and form templates' => 'Ubiquity index-crud MainCrud -t=index,form'
		], 'controllers');
	}

	public static function restController() {
		return new Command("rest", "restControllerName", "Creates a new REST controller.", [
			'rest-controller',
			'rest:controller',
			'rest_controller',
			'restController'
		], [
			"r" => Parameter::create("resource", "The model used", []),
			"p" => Parameter::create("path", "The associated route", []),
			'o' => Parameter::create('domain', 'The domain in which to create the controller.', [], '')
		], [
			'Creates a REST controller for the class models\User' => 'Ubiquity rest RestUsers -r=User -p=/rest/users'
		], 'rest');
	}

	public static function restApiController() {
		return new Command("restapi", "restControllerName", "Creates a new REST API controller.", [
			'restapi-controller',
			'restapi:controller',
			'restapi_controller',
			'restapiController'
		], [
			"p" => Parameter::create("path", "The associated route", []),
			'o' => Parameter::create('domain', 'The domain in which to create the controller.', [], '')
		], [
			'Creates a REST API controller' => 'Ubiquity restapi -p=/rest'
		], 'rest');
	}

	public static function dao() {
		return new Command("dao", "command", "Executes a DAO command (getAll,getOne,count,uGetAll,uGetOne,uCount).", [
			"DAO"
		], [
			"r" => Parameter::create("resource", "The model used", []),
			"c" => Parameter::create("condition", "The where part of the query", []),
			"i" => Parameter::create("included", "The associated members to load (boolean or array: client.*,commands)", []),
			"p" => Parameter::create("parameters", "The parameters for a parameterized query", []),
			"f" => Parameter::create("fields", "The fields to display in the response", []),
			'o' => Parameter::create('domain', 'The domain in which the models are.', [], '')
		], [
			'Returns all instances of models\User' => 'Ubiquity dao getAll -r=User',
			'Returns all instances of models\User and includes their commands' => 'Ubiquity dao getAll -r=User -i=commands',
			'Returns the User with the id 5' => 'Ubiquity dao getOne -c="id=5"-r=User',
			'Returns the list of users belonging to the "Brittany" or "Normandy" regions' => 'Ubiquity uGetAll -r=User -c="region.name= ? or region.name= ?" -p=Brittany,Normandy'
		], 'models');
	}

	public static function authController() {
		return new Command("auth", "authControllerName", "Creates a new controller for authentification.", [
			'auth-controller',
			'auth_controller',
			'auth:controller',
			'authController'
		], [
			"e" => Parameter::create("extends", "The base class of the controller (must derived from AuthController)", [], "Ubiquity\\controllers\\auth\\AuthController"),
			"t" => Parameter::create("templates", "The templates to modify", [
				"index",
				"info",
				"noAccess",
				"disconnected",
				"message",
				"baseTemplate"
			], 'index,info,noAccess,disconnected,message,baseTemplate'),
			"p" => Parameter::create("path", "The associated route", []),
			'o' => Parameter::create('domain', 'The domain in which to create the controller.', [], '')
		], [
			'Creates a new controller for authentification' => 'Ubiquity auth AdminAuthController',
			'and associates a route to it' => 'Ubiquity auth AdminAuthController -p=/admin/auth',
			'allows customization of index and info templates' => 'Ubiquity auth AdminAuthController -t=index,info'
		], 'controllers');
	}

	public static function newAction() {
		return new Command("action", "controller.action", "Creates a new action in a controller.", [
			'new-action',
			'new_action',
			'new:action',
			'newAction'
		], [
			"p" => Parameter::create("params", "The action parameters (or arguments)", []),
			"r" => Parameter::create("route", "The associated route path", []),
			"v" => Parameter::create("create-view", "Creates the associated view", [
				"true",
				"false"
			], "false"),
			'o' => Parameter::create('domain', 'The domain in which the controller is.', [], '')
		], [
			'Adds the action all in controller Users' => 'Ubiquity action Users.all',
			'Adds the action display in controller Users with a parameter' => 'Ubiquity action Users.display -p=idUser',
			'and associates a route to it' => 'Ubiquity action Users.display -p=idUser -r=/users/display/{idUser}',
			'with multiple parameters' => 'Ubiquity action Users.search -p=name,address',
			'and create the associated view' => 'Ubiquity action Users.search -p=name,address -v'
		], 'controllers');
	}

	public static function newDomain() {
		return new Command('domain', 'name', 'Creates a new domain (for a Domain Driven Design approach).', [
			'new-domain',
			'new_domain',
			'new:domain',
			'newDomain'
		], [
			"b" => Parameter::create("base", "The base folder for domains.", [], 'domains')
		], [
			'Creates a new domain users' => 'Ubiquity domain users'
		], 'controllers');
	}

	public static function infoModel() {
		return new Command("info-model", "?infoType", "Returns the model meta datas.", [
			'info_model',
			'info:model',
			'infoModel'
		], [
			"s" => Parameter::create("separate", "If true, returns each info in a separate table", [
				"true",
				"false"
			], "false"),
			"m" => Parameter::create("model", "The model on which the information is sought.", []),
			"f" => Parameter::create("fields", "The fields to display in the table.", []),
			'o' => Parameter::create('domain', 'The domain in which the models is.', [], '')
		], [
			'Gets metadatas for User class' => 'Ubiquity info:model -m=User'
		], 'models');
	}

	public static function infoModels() {
		return new Command('info-models', '', 'Returns the models meta datas.', [
			'info_models',
			'info:models',
			'infoModels'
		], [
			'd' => Parameter::create('database', 'The database connection to use (offset)', [], 'default'),
			"m" => Parameter::create("models", "The models on which the information is sought.", []),
			"f" => Parameter::create("fields", "The fields to display in the table.", []),
			'o' => Parameter::create('domain', 'The domain in which the models are.', [], '')
		], [
			'Gets metadatas for all models in default db' => 'Ubiquity info:models',
			'Gets metadatas for all models in messagerie db' => 'Ubiquity info:models -d=messagerie',
			'Gets metadatas for User and Group models' => 'Ubiquity info:models -m=User,Group',
			'Gets all primary keys for all models' => 'Ubiquity info:models -f=#primaryKeys'
		], 'models');
	}

	public static function infoValidation() {
		return new Command("info-validation", "?memberName", "Returns the models validation info.", [
			'info_validation',
			'info:validation',
			'infoValidation',
			'info_validators',
			'info-validators',
			'info:validators',
			'infoValidators'
		], [
			"s" => Parameter::create("separate", "If true, returns each info in a separate table", [
				'true',
				'false'
			], 'false'),
			"m" => Parameter::create("model", "The model on which the information is sought.", []),
			'o' => Parameter::create('domain', 'The domain in which the models is.', [], '')
		], [
			'Gets validators for User class' => 'Ubiquity info:validation -m=User',
			'Gets validators for User class on member firstname' => 'Ubiquity info:validation firstname -m=User'
		], 'models');
	}

	public static function configInfo() {
		return new Command("config", "", "Returns the config informations from app/config/config.php.", [
			'info_config',
			'info-config',
			'info:config',
			'infoConfig'
		], [
			"f" => Parameter::create("fields", "The fields to display.", [])
		], [
			'Display all config vars' => 'Ubiquity config',
			'Display database config vars' => 'Ubiquity config -f=database'
		], 'system');
	}

	public static function configSet() {
		return new Command("config-set", "", "Modify/add variables and save them in app/config/config.php. Supports only long parameters with --.", [
			'set_config',
			'set-config',
			'set:config',
			'setConfig'
		], [], [
			'Assigns a new value to siteURL' => 'Ubiquity config:set --siteURL=http://127.0.0.1/quick-start/',
			'Change the database name and port' => 'Ubiquity config:set --database.dbName=blog --database.port=3307'
		], 'system');
	}

	public static function newTheme() {
		return new Command("create-theme", "themeName", "Creates a new theme or installs an existing one.", [
			'create_theme',
			'create:theme',
			'createTheme'
		], [
			"x" => Parameter::create("extend", "If specified, inherits from an existing theme (bootstrap,semantic or foundation).", [
				'bootstrap',
				'semantic',
				'foundation'
			]),
			'o' => Parameter::create('domain', 'The domain in which to create the theme.', [], '')
		], [
			'Creates a new theme custom' => 'Ubiquity create-theme custom',
			'Creates a new theme inheriting from Bootstrap' => 'Ubiquity theme myBootstrap -x=bootstrap'
		], 'gui');
	}

	public static function installTheme() {
		return new Command("theme", "themeName", "Installs an existing theme or creates a new one if the specified theme does not exists.", [
			'install_theme',
			'install-theme',
			'install:theme',
			'installTheme'
		], [
			'o' => Parameter::create('domain', 'The domain in which to install the theme.', [], '')
		], [
			'Creates a new theme custom' => 'Ubiquity theme custom',
			'Install bootstrap theme' => 'Ubiquity theme bootstrap'
		], 'gui');
	}

	public static function bootstrap() {
		return new Command("bootstrap", "command", "Executes a command created in app/config/_bootstrap.php file for bootstraping the app.", [
			"boot"
		], [], [
			'Bootstrap for dev mode' => 'Ubiquity bootstrap dev',
			'Bootstrap for prod mode' => 'Ubiquity bootstrap prod'
		], 'servers');
	}

	public static function composer() {
		return new Command("composer", "command", "Executes a composer command.", [
			"compo"
		], [], [
			'composer update' => 'Ubiquity composer update',
			'composer update with no-dev' => 'Ubiquity composer nodev',
			'composer optimization for production' => 'Ubiquity composer optimize'
		], 'system');
	}

	public static function mailer() {
		return new Command("mailer", "part", "Displays mailer classes, mailer queue or mailer dequeue.", [], [], [
			'Display mailer classes' => 'Ubiquity mailer classes',
			'Display mailer messages in queue(To send)' => 'Ubiquity mailer queue',
			'Display mailer messages in dequeue(sent)' => 'Ubiquity mailer dequeue'
		], 'mailer');
	}

	public static function sendMails() {
		return new Command("send-mail", "", "Send message(s) from queue.", [
			'send-mails',
			'send_mails',
			'send:mails',
			'sendMails'
		], [
			"n" => Parameter::create("num", "If specified, Send the mail at the position n in queue.", [])
		], [
			'Send all messages to send from queue' => 'Ubiquity semdMails',
			'Send the first message in queue' => 'Ubiquity sendMail 1'
		], 'mailer');
	}

	public static function newMail() {
		return new Command("new-mail", "name", "Creates a new mailer class.", [
			'new_mail',
			'new:mail',
			'newMail'
		], [
			"p" => Parameter::create("parent", "The class parent.", [], '\\Ubiquity\\mailer\\AbstractMail'),
			"v" => Parameter::create("view", "Add the associated view.", [], false)
		], [
			'Creates a new mailer class' => 'Ubiquity newMail InformationMail'
		], 'mailer');
	}

	public static function newClass() {
		return new Command('new-class', "name", "Creates a new class.", [
			'new_class',
			'new:class',
			'newClass',
			'class'
		], [
			"p" => Parameter::create("parent", "The class parent.", [])
		], [
			'Creates a new class' => 'Ubiquity class services.OrgaRepository'
		], 'controllers');
	}

	public static function createCommand() {
		return new Command("create-command", "commandName", "Creates a new custom command for the devtools.", [
			'create_command',
			'create:command',
			'createCommand'
		], [
			"v" => Parameter::create("value", "The command value (first parameter).", []),
			"p" => Parameter::create("parameters", "The command parameters (comma separated).", []),
			"d" => Parameter::create("description", "The command description.", []),
			"a" => Parameter::create("aliases", "The command aliases (comma separated).", [])
		], [
			'Creates a new custom command' => 'Ubiquity create-command custom'
		], 'system');
	}

	public static function initAcls() {
		return new Command('acl-init', '', 'Initialize Acls defined with annotations in controllers.', [
			'acl_init',
			'acl:init',
			'aclInit'
		], [
			"m" => Parameter::create("models", "Generates ACL models", []),
			"p" => Parameter::create("providers", "The providers to use (comma separated).", ['dao'], 'dao'),
			"d" => Parameter::create("database", "The database offset.", [], 'default'),
		], [
			'Initialize Acls' => 'Ubiquity aclInit',
			'Initialize Acls and create tables for AclDAOProvider' => 'Ubiquity aclInit -p=dao',
			'Initialize Acls, create tables for acls db offset and models for AclDAOProvider' => 'Ubiquity aclInit -p=dao -m -d=acls',
		], 'security');
	}

	public static function displayAcls() {
		return new Command('acl-display', '', 'Display Acls defined with annotations in controllers.', [
			'acl_display',
			'acl:display',
			'aclDisplay'
		], [
			"v" => Parameter::create("value", "The ACL part to display.", [
				'all',
				'role',
				'resource',
				'permission',
				'map',
				'acl'
			], 'acl')
		], [
			'Display all defined roles with ACL annotations' => 'Ubiquity aclDisplay role'
		], 'security');
	}

	public static function newEncryptionKey() {
		return new Command('new-key', 'cypher', 'Generate a new encryption key using a cipher.', [
			'new_key',
			'new:key',
			'newKey'
		], [], [
			'Generate a key for AES-128' => 'Ubiquity new-key 128'
		], 'security');
	}

	public static function infoMigrations() {
		return new Command("info-migrations", "", "Returns the migration infos.", [
			'info_migrations',
			'info:migrations',
			'infoMigrations'
		], [
			"d" => Parameter::create("database", "The database offset.", [], 'default'),
			'o' => Parameter::create('domain', 'The domain in which the database models are.', [], '')
		], [
			'Display all migrations for the default database' => 'Ubiquity info:migrations'
		], 'models');
	}

	public static function migrations() {
		return new Command("migrations", "", "Display and execute the database migrations.", [
			'migrations',
			'migrate'
		], [
			"d" => Parameter::create("database", "The database offset.", [], 'default'),
			'o' => Parameter::create('domain', 'The domain in which the database models are.', [], '')
		], [
			'Display and execute all migrations for the default database' => 'Ubiquity migrations'
		], 'models');
	}

	protected static function getCustomCommandInfos() {
		$result = [];
		$commands = self::getCustomCommands();
		if (is_array($commands)) {
			foreach ($commands as $o) {
				$result[] = $o->getCommand();
			}
		}
		return $result;
	}

	public static function getCustomCommands() {
		if (\class_exists(\Ubiquity\utils\base\UIntrospection::class)) {
			if (! isset(self::$customCommands)) {
				$classes = UIntrospection::getChildClasses('\\Ubiquity\\devtools\\cmd\\commands\\AbstractCustomCommand');
				foreach ($classes as $class) {
					$o = new $class();
					$cmd = $o->getCommand();
					self::$customCommands[$cmd->getName()] = $o;
					$aliases = $cmd->getAliases();
					if (is_array($aliases)) {
						foreach ($aliases as $alias) {
							self::$customAliases[$alias] = $o;
						}
					}
				}
			}
		}
		return self::$customCommands;
	}

	public static function reloadCustomCommands(array $config = []) {
		if (\class_exists(\Ubiquity\utils\base\UIntrospection::class)) {
			self::$customCommands = null;
			self::$customAliases = [];
			self::preloadCustomCommands($config);
			self::getCustomCommands();
		}
	}

	public static function getCustomAliases() {
		return self::$customAliases;
	}

	public static function preloadCustomCommands(array $config = []) {
		if (\class_exists(\Ubiquity\utils\base\UIntrospection::class)) {
			$config['cmd-pattern'] ??= 'commands' . \DS . '*.cmd.php';
			$files = UFileSystem::glob_recursive($config['cmd-pattern']);
			foreach ($files as $file) {
				include_once $file;
			}
		}
	}

	public static function getCommandNames(array $excludedCategories = [
		'installation' => false,
		'servers' => false
	], $excludedCommands = []) {
		$result = [];
		$commands = self::getCommands();
		foreach ($commands as $command) {
			$cat = $command->getCategory();
			$commandName = $command->getName();
			if (! isset($excludedCommands[$commandName]) && ! isset($excludedCategories[$cat])) {
				$result[] = $commandName;
			}
		}
		return $result;
	}

	public static function getCommands() {
		return [
			self::initCache(),
			self::clearCache(),
			self::controller(),
			self::newAction(),
			self::authController(),
			self::indexCrudController(),
			self::crudController(),
			self::newClass(),
			self::newTheme(),
			self::installTheme(),

			self::project(),
			self::serve(),
			self::liveReload(),
			self::bootstrap(),
			self::help(),
			self::version(),
			self::model(),
			self::genModel(),
			self::allModels(),
			self::infoMigrations(),
			self::migrations(),
			self::dao(),
			self::selfUpdate(),
			self::composer(),
			self::admin(),
			self::restController(),
			self::restApiController(),
			self::routes(),
			self::infoModel(),
			self::infoModels(),
			self::infoValidation(),
			self::configInfo(),
			self::configSet(),
			self::mailer(),
			self::newMail(),
			self::sendMails(),
			self::createCommand(),
			self::initAcls(),
			self::displayAcls(),
			self::newEncryptionKey(),
			self::newDomain(),
			...self::getCustomCommandInfos()
		];
	}

	/**
	 *
	 * @return mixed
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 *
	 * @return mixed
	 */
	public function getDescription() {
		return $this->description;
	}

	/**
	 *
	 * @return mixed
	 */
	public function getValue() {
		if ($this->value != null) {
			return \ltrim($this->value, '?');
		}
		return $this->value;
	}

	public function hasRequiredValue() {
		return $this->value != null && \substr($this->value, 0, 1) !== '?';
	}

	/**
	 *
	 * @return mixed
	 */
	public function getAliases() {
		return $this->aliases;
	}

	/**
	 *
	 * @return mixed
	 */
	public function getParameters() {
		return $this->parameters;
	}

	/**
	 *
	 * @return mixed
	 */
	public function getExamples() {
		return $this->examples;
	}

	/**
	 *
	 * @return mixed
	 */
	public function getCategory() {
		return $this->category;
	}

	/**
	 *
	 * @param mixed $category
	 */
	public function setCategory($category) {
		$this->category = $category;
	}

	public function hasParameters() {
		return \count($this->parameters) > 0;
	}

	public function hasValue() {
		return $this->value != null;
	}

	public function isImmediate() {
		return ! $this->hasParameters() && ! $this->hasValue();
	}

	public function __toString() {
		return $this->name;
	}
}
