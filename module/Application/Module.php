<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application;

use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;

class Module
{
    public function onBootstrap(MvcEvent $e)
    {
        $eventManager        = $e->getApplication()->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);
        
        $eventManager->getSharedManager()->attach('*', MvcEvent::EVENT_DISPATCH_ERROR, array($this, 'onDispatchError'), -100);
        $eventManager->getSharedManager()->attach('*', MvcEvent::EVENT_RENDER_ERROR, array($this, 'onDispatchError'), - 100);
        
        $app = $e->getParam('application');
        $app->getEventManager()->attach('render', array($this, 'setLayoutTitle'));
    }

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }
    
    public function onDispatchError(MvcEvent $event)
    {
        $view = $event->getViewModel();
        $view->setTemplate('layout/dashboard');
    }
    
    /**
     * Set default page title
     * 
     * @param  \Zend\Mvc\MvcEvent $e The MvcEvent instance
     * @return void
     */
    public function setLayoutTitle($e)
    {
        $viewHelperManager = $e->getApplication()->getServiceManager()->get('viewHelperManager');
        $headTitleHelper = $viewHelperManager->get('headTitle');
        
        $title = $headTitleHelper->toString();
        if ($title === '<title></title>') {
            $headTitleHelper->set('SpreadPoint - Start Generating New Leads');
        }
    }
}
