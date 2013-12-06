<?php

namespace Symfony\Cmf\Bundle\CreateBundle\Controller;

use FOS\RestBundle\View\ViewHandlerInterface,
    FOS\RestBundle\View\View;
use Symfony\Cmf\Bundle\MediaBundle\File\BrowserFileHelper;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\SecurityContextInterface;

/**
 * This controller includes the correct twig file to bootstrap the javascript
 * files of create.js and its dependencies if the current user has the rights
 * to use create.js.
 *
 * The security context is optional to not fail with an exception if the
 * controller is loaded in a context without a firewall.
 */
class JsloaderController
{
    /**
     * @var SecurityContextInterface
     */
    protected $securityContext;

    /**
     * @var ViewHandlerInterface
     */
    protected $viewHandler;

    /**
     * @var string the role name for the security check
     */
    protected $requiredRole;

    /**
     * @var string
     */
    private $stanbolUrl;

    /**
     * @var Boolean
     */
    private $fixedToolbar;

    /**
     * @var array
     */
    private $plainTextTypes;

    /**
     * @var string
     */
    private $editorBasePath;

    /**
     * @var Boolean
     */
    private $imageUploadEnabled;

    /**
     * @var BrowserFileHelper
     */
    private $browserFileHelper;


    /**
     * Create the Controller
     *
     * @param ViewHandlerInterface     $viewHandler
     * @param string                   $stanbolUrl         the url to use for
     *      the semantic enhancer stanbol.
     * @param Boolean                  $imageUploadEnabled used to determine
     *      whether image upload should be activated.
     * @param Boolean                  $fixedToolbar       whether the toolbar
     *      is fixed or floating. Hallo editor specific.
     * @param array                    $plainTextTypes     RDFa types to edit
     *      in raw text only.
     * @param string|boolean           $requiredRole       Role a user needs to
     *      be granted in order to see the the editor. If set to false, the
     *      editor is always loaded.
     * @param SecurityContextInterface $securityContext    The security
     *      context to use to check for the role.
     * @param string                   $editorBasePath     Configuration for
     *      ckeditor.
     * @param BrowserFileHelper        $browserFileHelper  Used to determine
     *      image editing for ckeditor.
     */
    public function __construct(
        ViewHandlerInterface $viewHandler,
        $stanbolUrl,
        $imageUploadEnabled = false,
        $fixedToolbar = true,
        $plainTextTypes = array(),
        $requiredRole = "IS_AUTHENTICATED_ANONYMOUSLY",
        SecurityContextInterface $securityContext = null,
        $editorBasePath = null,
        BrowserFileHelper $browserFileHelper = null
    ) {
        $this->viewHandler        = $viewHandler;
        $this->stanbolUrl         = $stanbolUrl;
        $this->imageUploadEnabled = $imageUploadEnabled;
        $this->fixedToolbar       = $fixedToolbar;
        $this->plainTextTypes     = $plainTextTypes;
        $this->editorBasePath     = $editorBasePath;
        $this->requiredRole       = $requiredRole;
        $this->securityContext    = $securityContext;
        $this->browserFileHelper  = $browserFileHelper;
    }

    /**
     * Render javascript HTML tags for create.js and dependencies and bootstrap
     * javscript code.
     *
     * This bundle comes with templates for ckeditor, hallo and to develop on
     * the hallo coffeescript files.
     *
     * To use a different editor simply create a template following the naming
     * below:
     *   CmfCreateBundle::includejsfiles-%editor%.html.twig
     * and pass the appropriate editor name.
     *
     * @param string $editor the name of the editor to load.
     */
    public function includeJSFilesAction($editor = 'ckeditor')
    {
        if (!$this->performSecurityChecks()) {
            return new Response('');
        }

        $view = new View();

        $view->setTemplate(sprintf('CmfCreateBundle::includejsfiles-%s.html.twig', $editor));

        if ($this->browserFileHelper) {
            $helper = $this->browserFileHelper->getEditorHelper($editor);
            $browseUrl = $helper ? $helper->getUrl() : false;
        } else {
            $browseUrl = false;
        }

        $view->setData(array(
                'cmfCreateEditor' => $editor,
                'cmfCreateStanbolUrl' => $this->stanbolUrl,
                'cmfCreateImageUploadEnabled' => (boolean) $this->imageUploadEnabled,
                'cmfCreateFixedToolbar' => (boolean) $this->fixedToolbar,
                'cmfCreatePlainTextTypes' => json_encode($this->plainTextTypes),
                'cmfCreateEditorBasePath' => $this->editorBasePath,
                'cmfCreateBrowseUrl' => $browseUrl,
            )
        );

        return $this->viewHandler->handle($view);
    }

    /**
     * Actions may be performed if the requiredRole is set to false (completely
     * disable security check) or if there is a securityContext and it grants
     * the required role.
     *
     * @return boolean Whether the current user is allowed to edit.
     */
    protected function performSecurityChecks()
    {
        if (false === $this->requiredRole) {
            // security check is disabled
            return true;
        }

        if ($this->securityContext
            && $this->securityContext->getToken()
            && $this->securityContext->isGranted($this->requiredRole)
        ) {
            return true;
        }

        return false;
    }
}
