<?php
/*
Pupilsight, Flexible & Open School System
 */

namespace Pupilsight\Services;

use Pupilsight\Core;
use Pupilsight\Domain\System\SettingGateway;
use Pupilsight\Locale;
use Pupilsight\Session;
use Pupilsight\View\View;
use Pupilsight\View\Page;
use Pupilsight\Comms\Mailer;
use Pupilsight\Comms\SMS;
use Pupilsight\Domain\System\Theme;
use Pupilsight\Domain\System\Module;
use Pupilsight\Contracts\Comms\Mailer as MailerInterface;
use Pupilsight\Contracts\Comms\SMS as SMSInterface;
use League\Container\ServiceProvider\AbstractServiceProvider;
use League\Container\ServiceProvider\BootableServiceProviderInterface;


/**
 * DI Container Services for the Core
 *
 * @version v17
 * @since   v17
 */
class CoreServiceProvider extends AbstractServiceProvider implements BootableServiceProviderInterface
{
    protected $absolutePath;

    public function __construct($absolutePath)
    {
        $this->absolutePath = $absolutePath;
    }

    /**
     * The provides array is a way to let the container know that a service
     * is provided by this service provider. Every service that is registered
     * via this service provider must have an alias added to this array or
     * it will be ignored.
     *
     * @var array
     */
    protected $provides = [
        'config',
        'session',
        'locale',
        'twig',
        'page',
        'module',
        'theme',
        MailerInterface::class,
        SMSInterface::class,
        'pupilsight_logger',
        'mysql_logger',
    ];

    /**
     * In much the same way, this method has access to the container
     * itself and can interact with it however you wish, the difference
     * is that the boot method is invoked as soon as you register
     * the service provider with the container meaning that everything
     * in this method is eagerly loaded.
     *
     * If you wish to apply inflectors or register further service providers
     * from this one, it must be from a bootable service provider like
     * this one, otherwise they will be ignored.
     */
    public function boot()
    {
        $container = $this->getContainer();

        $container->share('config', new Core($this->absolutePath));
        $container->share('session', new Session($container));
        $container->share('locale', new Locale($this->absolutePath, $container->get('session')));

        $container->share(\Pupilsight\Contracts\Services\Session::class, $container->get('session'));

        Format::setupFromSession($container->get('session'));
    }

    /**
     * This is where the magic happens, within the method you can
     * access the container and register or retrieve anything
     * that you need to, but remember, every alias registered
     * within this method must be declared in the `$provides` array.
     */
    public function register()
    {
        $container = $this->getContainer();
        $absolutePath = $this->absolutePath;
        $session = $container->get('session');

        // Logging removed until properly setup & tested
        
        // $container->share('pupilsight_logger', function () use ($container) {
        //     $factory = new LoggerFactory($container->get(SettingGateway::class));
        //     return $factory->getLogger('pupilsight');
        // });

        // $container->share('mysql_logger', function () use ($container) {
        //     $factory = new LoggerFactory($container->get(SettingGateway::class));
        //     return $factory->getLogger('mysql');
        // });

        // $pdo->setLogger($container->get('mysql_logger'));

        $container->share('twig', function () use ($absolutePath, $session) {
            $loader = new \Twig_Loader_Filesystem($absolutePath.'/resources/templates');

            // Add the theme templates folder so it can override core templates
            $themeName = $session->get('pupilsightThemeName');
            if (is_dir($absolutePath.'/themes/'.$themeName.'/templates')) {
                $loader->prependPath($absolutePath.'/themes/'.$themeName.'/templates');
            }

            $enableDebug = $session->get('installType') == 'Development';
            // Override caching on systems during upgrades, when the system version is higher than database version
            if (version_compare($this->getContainer()->get('config')->getVersion(), $session->get('version'), '>')) {
                $enableDebug = true;
            }

            // Add module templates
            $moduleName = $session->get('module');
            if (is_dir($absolutePath.'/modules/'.$moduleName.'/templates')) {
                $loader->prependPath($absolutePath.'/modules/'.$moduleName.'/templates');
            }

            $twig = new \Twig_Environment($loader, array(
                'cache' => $absolutePath.'/uploads/cache',
                'debug' => $enableDebug,
            ));

            $twig->addGlobal('absolutePath', $session->get('absolutePath'));
            $twig->addGlobal('absoluteURL', $session->has('absoluteURL') ? $session->get('absoluteURL') : '.');
            $twig->addGlobal('pupilsightThemeName', $themeName);


            $twig->addFunction(new \Twig_Function('__', function ($string, $domain = null) {
                return __($string, $domain);
            }));

            $twig->addFunction(new \Twig_Function('__n', function ($singular, $plural, $n, $params = [], $options = []) {
                return __n($singular, $plural, $n, $params, $options);
            }));

            $twig->addFunction(new \Twig_Function('formatUsing', function ($method, ...$args) {
                return Format::$method(...$args);
            }));

            return $twig;
        });

        $container->share('action', function () use ($session) {
            $data = [
                'actionName'   => '%'.$session->get('action').'%',
                'moduleName'   => $session->get('module'),
                'pupilsightRoleID' => $session->get('pupilsightRoleIDCurrent'),
            ];
            $sql = "SELECT pupilsightAction.* 
                    FROM pupilsightAction
                    JOIN pupilsightModule ON (pupilsightModule.pupilsightModuleID=pupilsightAction.pupilsightModuleID)
                    LEFT JOIN pupilsightPermission ON (pupilsightPermission.pupilsightActionID=pupilsightAction.pupilsightActionID AND pupilsightPermission.pupilsightRoleID=:pupilsightRoleID)
                    LEFT JOIN pupilsightRole ON (pupilsightRole.pupilsightRoleID=pupilsightPermission.pupilsightRoleID)
                    WHERE pupilsightAction.URLList LIKE :actionName 
                    AND pupilsightModule.name=:moduleName";

            $actionData = $this->getContainer()->get('db')->selectOne($sql, $data);

            return $actionData ? $actionData : null;
        });

        $container->share('module', function () use ($session) {
            $data = ['moduleName' => $session->get('module')];
            $sql = "SELECT * FROM pupilsightModule WHERE name=:moduleName AND active='Y'";
            $moduleData = $this->getContainer()->get('db')->selectOne($sql, $data);

            return $moduleData ? new Module($moduleData) : null;
        });

        $container->share('theme', function () use ($session) {
            if ($session->has('pupilsightThemeIDPersonal')) {
                $data = ['pupilsightThemeID' => $session->get('pupilsightThemeIDPersonal')];
                $sql = "SELECT * FROM pupilsightTheme WHERE pupilsightThemeID=:pupilsightThemeID";
            } else {
                $data = [];
                $sql = "SELECT * FROM pupilsightTheme WHERE active='Y'";
            }

            $themeData = $this->getContainer()->get('db')->selectOne($sql, $data);

            $session->set('pupilsightThemeID', $themeData['pupilsightThemeID'] ?? 001);
            $session->set('pupilsightThemeName', $themeData['name'] ?? 'Default');

            return $themeData ? new Theme($themeData) : null;
        });

        $container->share('page', function () use ($session, $container) {
            $pageTitle = $session->get('organisationNameShort').' - '.$session->get('systemName');
            if ($session->has('module')) {
                $pageTitle .= ' - '.__($session->get('module'));
            }

            $page = new Page($container->get('twig'), [
                'title'   => $pageTitle,
                'address' => $session->get('address'),
                'action'  => $container->get('action'),
                'module'  => $container->get('module'),
                'theme'   => $container->get('theme'),
            ]);

            $container->add('errorHandler', new ErrorHandler($session->get('installType'), $page));

            return $page;
        });

        $container->add(MailerInterface::class, function () use ($container) {
            $view = new View($container->get('twig'));
            return (new Mailer($container->get('session')))->setView($view);
        });

        $container->add(SMSInterface::class, function () use ($session, $container) {
            $connection2 = $container->get('db')->getConnection();
            $smsGateway = getSettingByScope($connection2, 'Messenger', 'smsGateway');

            return new SMS([
                'smsGateway'   => $smsGateway,
                'smsSenderID'  => getSettingByScope($connection2, 'Messenger', 'smsSenderID'),
                'smsURL'       => getSettingByScope($connection2, 'Messenger', 'smsURL'),
                'smsURLCredit' => getSettingByScope($connection2, 'Messenger', 'smsURLCredit'),
                'smsUsername'  => getSettingByScope($connection2, 'Messenger', 'smsUsername'),
                'smsPassword'  => getSettingByScope($connection2, 'Messenger', 'smsPassword'),
                'smsMailer'    => $smsGateway == 'Mail to SMS' ? $container->get(MailerInterface::class) : '',
            ]);
        });
    }
}
