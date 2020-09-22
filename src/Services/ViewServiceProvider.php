<?php
/*
Pupilsight, Flexible & Open School System
 */

namespace Pupilsight\Services;

use Pupilsight\Forms\Form;
use Pupilsight\Forms\FormFactory;
use Pupilsight\Forms\FormFactoryInterface;
use Pupilsight\Forms\View\FormView;
use Pupilsight\Forms\View\FormRendererInterface;
use Pupilsight\Tables\DataTable;
use Pupilsight\Tables\View\DataTableView;
use Pupilsight\Tables\View\PaginatedView;
use League\Container\ServiceProvider\AbstractServiceProvider;
use Twig_Environment;

/**
 * DI Container Services for rendering Views
 *
 * @version v18
 * @since   v18
 */
class ViewServiceProvider extends AbstractServiceProvider
{
    /**
     * The provides array is a way to let the container know that a service
     * is provided by this service provider. Every service that is registered
     * via this service provider must have an alias added to this array or
     * it will be ignored.
     *
     * @var array
     */
    protected $provides = [
        Form::class,
        FormRendererInterface::class,
        FormFactoryInterface::class,
        DataTable::class,
        DataTableView::class,
        PaginatedView::class,
        Twig_Environment::class,
    ];

    /**
     * This is where the magic happens, within the method you can
     * access the container and register or retrieve anything
     * that you need to, but remember, every alias registered
     * within this method must be declared in the `$provides` array.
     */
    public function register()
    {
        $container = $this->getContainer();
        
        $container->add(Form::class, function () {
            $factory = new FormFactory();
            $renderer = new FormView($this->getContainer()->get('twig'));

            return (new Form($factory, $renderer))->setClass('w-full smallIntBorder standardForm');
        });

        $container->add(FormRendererInterface::class, function () {
            return new FormView($this->getContainer()->get('twig'));
        });

        $container->add(FormFactoryInterface::class, function () {
            return new FormFactory();
        });
        
        $container->add(DataTable::class, function () use ($container) {
            $renderer = new DataTableView($container->get('twig'));

            return new DataTable($renderer);
        });

        $container->add(DataTableView::class, function () use ($container) {
            return new DataTableView($container->get('twig'));
        });

        $container->add(PaginatedView::class, function () use ($container) {
            return new PaginatedView($container->get('twig'));
        });

        $container->share(\Twig_Environment::class, function () {
            return $this->getContainer()->get('twig');
        });
    }
}
