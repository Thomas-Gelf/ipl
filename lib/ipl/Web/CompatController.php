<?php

namespace ipl\Web;

use Icinga\Application\Benchmark;
use Icinga\Authentication\Auth;
use Icinga\Exception\IcingaException;
use Icinga\Exception\ProgrammingError;
use Icinga\Web\Controller as LegacyModuleController;
use Icinga\Web\Notification;
use Icinga\Web\UrlParams;
use Icinga\Web\Url as WebUrl;
use ipl\Web\Component\ActionBar;
use ipl\Web\Component\Content;
use ipl\Web\Component\Controls;
use ipl\Web\Component\Tabs;
use ipl\Zf1\SimpleViewRenderer;
use Zend_Controller_Request_Abstract as ZfRequest;
use Zend_Controller_Response_Abstract as ZfResponse;
use Zend_Controller_Action_HelperBroker as ZfHelperBroker;

class CompatController extends LegacyModuleController
{
    /** @var Controls */
    private $controls;

    // https://github.com/joshbuchea/HEAD

    /** @var Content */
    private $content;

    /** @var Url */
    private $url;

    /** @var Url */
    private $originalUrl;

    /** @var SimpleViewRenderer */
    private $viewRenderer;

    private $autorefreshInterval;

    private $reloadCss = false;

    private $rerenderLayout = false;

    private $xhrLayout = 'inline';

    /** @var UrlParams */
    protected $params;

    /**
     * The constructor starts benchmarking, loads the configuration and sets
     * other useful controller properties
     *
     * @param ZfRequest  $request
     * @param ZfResponse $response
     * @param array      $invokeArgs Any additional invocation arguments
     */
    public function __construct(
        ZfRequest $request,
        ZfResponse $response,
        array $invokeArgs = array()
    ) {
        /** @var \Icinga\Web\Request $request */
        /** @var \Icinga\Web\Response $response */
        $this->params = UrlParams::fromQueryString();

        $this->setRequest($request)
            ->setResponse($response)
            ->_setInvokeArgs($invokeArgs);

        $this->prepareViewRenderer();
        $this->_helper = new ZfHelperBroker($this);

        $this->handlerBrowserWindows();
        $moduleName = $this->getModuleName();
        $this->view->translationDomain = $moduleName !== 'default' ? $moduleName : 'icinga';
        // TODO: TranslationHelper::setTranslator(..);
        $layout = $this->_helper->layout();
        $layout->isIframe = $request->getUrl()->shift('isIframe');
        $layout->showFullscreen = $request->getUrl()->shift('showFullscreen');
        $layout->moduleName = $moduleName;

        $this->view->compact = $request->getParam('view') === 'compact';
        $url = $this->url();
        $this->params = $url->getParams();

        if ($url->shift('showCompact')) {
            $this->view->compact = true;
        }
        if ($this->rerenderLayout = $url->shift('renderLayout')) {
            $this->xhrLayout = $this->innerLayout;
        }
        if ($url->shift('_disableLayout')) {
            $layout->disableLayout();
        }

        // $auth->authenticate($request, $response, $this->requiresLogin());
        if ($this->requiresLogin()) {
            if (! $request->isXmlHttpRequest() && $request->isApiRequest()) {
                Auth::getInstance()->challengeHttp();
            }
            $this->redirectToLogin(Url::fromRequest());
        }

        $this->view->tabs = new Tabs();
        Benchmark::measure('Ready to initialize the controller');
        $this->prepareInit();
        $this->init();
    }

    public function init()
    {
        // Hint: we intentionally do not call our parent's init() method
    }

    public function getOriginalUrl()
    {
        if ($this->originalUrl === null) {
            $this->originalUrl = clone($this->getUrlFromRequest());
        }

        return clone($this->originalUrl);
    }

    protected function getUrlFromRequest()
    {
        $webUrl = $this->getRequest()->getUrl();

        return Url::fromPath(
            $webUrl->getPath()
        )->setParams($webUrl->getParams());
    }

    public function prepareViewRenderer()
    {
        $this->viewRenderer = new SimpleViewRenderer();
        $this->viewRenderer->replaceZendViewRenderer();
        $this->view = $this->viewRenderer->view;
    }

    /**
     * @return Url
     */
    public function url()
    {
        if ($this->url === null) {
            $this->url = $this->getOriginalUrl();
        }

        return $this->url;
    }

    /**
     * @param $title
     * @return $this
     */
    public function setTitle($title)
    {
        $title = $this->makeTitle(func_get_args());
        $this->view->title = $title;

        return $this;
    }

    /**
     * @param $title
     * @return $this
     */
    public function addTitle($title)
    {
        $title = $this->makeTitle(func_get_args());
        $this->view->title = $title;
        $this->controls()->addTitle($title);

        return $this;
    }

    private function makeTitle($args)
    {
        $title = array_shift($args);

        if (empty($args)) {
            return $title;
        } else {
            return vsprintf($title, $args);
        }
    }

    /**
     * @param $title
     * @param null $url
     * @param string $name
     * @return $this
     */
    public function addSingleTab($title, $url = null, $name = 'main')
    {
        if ($url === null) {
            $url = $this->url();
        }

        $this->tabs()->add($name, [
            'label' => $title,
            'url'   => $url,
        ])->activate($name);

        return $this;
    }

    /**
     * TODO: Not sure whether we need dedicated Content/Controls classes,
     *       a simple Container with a class name might suffice here
     *
     * @return Controls
     */
    public function controls()
    {
        if ($this->controls === null) {
            $this->controls = $this->view->controls = Controls::create();
        }

        return $this->controls;
    }

    /**
     * @return Tabs
     */
    public function tabs()
    {
        return $this->controls()->getTabs();
    }

    /**
     * @return ActionBar
     */
    public function actions()
    {
        return $this->controls()->getActionBar();
    }

    /**
     * @return Content
     */
    public function content()
    {
        if ($this->content === null) {
            $this->content = $this->view->content = Content::create();
        }

        return $this->content;
    }

    /**
     * @return SimpleViewRenderer
     */
    public function getViewRenderer()
    {
        return $this->viewRenderer;
    }

    public function setAutorefreshInterval($interval)
    {
        if (! is_int($interval) || $interval < 1) {
            throw new ProgrammingError(
                'Setting autorefresh interval smaller than 1 second is not allowed'
            );
        }
        $this->autorefreshInterval = $interval;
        $this->_helper->layout()->autorefreshInterval = $interval;
        return $this;
    }

    public function disableAutoRefresh()
    {
        $this->autorefreshInterval = null;
        $this->_helper->layout()->autorefreshInterval = null;
        return $this;
    }

    protected function redirectXhr($url)
    {
        if (! $url instanceof WebUrl) {
            $url = Url::fromPath($url);
        }

        if ($this->rerenderLayout) {
            $this->getResponse()->setHeader('X-Icinga-Rerender-Layout', 'yes');
        }
        if ($this->reloadCss) {
            $this->getResponse()->setHeader('X-Icinga-Reload-Css', 'now');
        }

        $this->shutdownSession();

        $this->getResponse()
            ->setHeader('X-Icinga-Redirect', rawurlencode($url->getAbsoluteUrl()))
            ->sendHeaders();

        exit;
    }

    public function postDispatchXhr()
    {
        $layout = $this->_helper->layout();
        $layout->setLayout($this->xhrLayout);
        $resp = $this->getResponse();

        $notifications = Notification::getInstance();
        if ($notifications->hasMessages()) {
            $notificationList = array();
            foreach ($notifications->popMessages() as $m) {
                $notificationList[] = rawurlencode($m->type . ' ' . $m->message);
            }
            $resp->setHeader('X-Icinga-Notification', implode('&', $notificationList), true);
        }

        if ($this->reloadCss) {
            $resp->setHeader('X-Icinga-CssReload', 'now', true);
        }

        if ($this->view->title) {
            if (preg_match('~[\r\n]~', $this->view->title)) {
                // TODO: Innocent exception and error log for hack attempts
                throw new IcingaException('No way, guy');
            }
            $resp->setHeader(
                'X-Icinga-Title',
                rawurlencode($this->view->title . ' :: Icinga Web'),
                true
            );
        } else {
            $resp->setHeader('X-Icinga-Title', rawurlencode('Icinga Web'), true);
        }

        if ($this->rerenderLayout) {
            $this->getResponse()->setHeader('X-Icinga-Container', 'layout', true);
        }

        if ($this->autorefreshInterval !== null) {
            $resp->setHeader('X-Icinga-Refresh', $this->autorefreshInterval, true);
        }

        if ($name = $this->getModuleName()) {
            $this->getResponse()->setHeader('X-Icinga-Module', $name, true);
        }
    }

    protected function rerenderLayout()
    {
        $this->rerenderLayout = true;
        $this->xhrLayout = 'layout';
        return $this;
    }

    protected function reloadCss()
    {
        $this->reloadCss = true;
        return $this;
    }
}
