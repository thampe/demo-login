<?php

namespace Hampe\DemoLogin\Controller\Adminhtml\Demo;

use Hampe\DemoLogin\Helper\Config;
use Magento\Backend\App\AbstractAction;
use Magento\Backend\App\Action;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\State\UserLockedException;
use Magento\User\Model\User;
use Magento\User\Model\UserFactory;

class Login extends AbstractAction implements HttpGetActionInterface, HttpPostActionInterface
{

    protected $_publicActions = ['login'];

    /**
     * @var \Magento\Backend\Model\Auth
     */
    protected $auth;

    /**
     * @var Config
     */
    protected $configHelper;
    /**
     * @var UserFactory
     */
    private $userFactory;
    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    private $eventManager;
    /**
     * @var \Magento\Security\Model\Plugin\Auth
     */
    private $authPlugin;

    public function __construct(
        \Magento\Security\Model\Plugin\Auth $authPlugin,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        UserFactory $userFactory,
        Config $configHelper,
        \Magento\Backend\Model\Auth $auth,
        Action\Context $context
    ) {
        parent::__construct($context);
        $this->auth = $auth;
        $this->configHelper = $configHelper;
        $this->userFactory = $userFactory;
        $this->eventManager = $eventManager;
        $this->authPlugin = $authPlugin;
    }

    protected function _isAllowed()
    {
        return true;
    }

    public function dispatch(\Magento\Framework\App\RequestInterface $request)
    {
        if (!$this->configHelper->isActive()) {
            return parent::dispatch($request);
        }

        $request->setForwarded(false)
            ->setDispatched(true);
        return parent::dispatch($request);
    }

    public function execute()
    {
        $redirect = $this->resultRedirectFactory->create();
        if (!$this->configHelper->isActive()) {
            $redirect->setPath('admin/auth/login');
            return $redirect;
        }

        /** @var \Magento\Backend\Model\Auth\Session $authStorage */
        $authStorage = $this->auth->getAuthStorage();

        $userId = $this->configHelper->getUserId();
        $user = $this->userFactory->create();
        try {
            $user->load($userId);
            if (!$user->getId()) {
                throw new LocalizedException(__('User with ID %1 not found', $userId));
            }

            $this->eventManager->dispatch('admin_user_authenticate_before', [
                'username' => $user->getUserName(),
                'user' => $user,
            ]);

            // check whether user is disabled
            if (!$user->getIsActive()) {
                throw new AuthenticationException(__('You did not sign in correctly or your account is temporarily disabled.'));
            }

            // check whether user is locked
            $lockExpires = $user->getData('lock_expires');
            if ($lockExpires) {
                $lockExpires = new \DateTime($lockExpires);
                if ($lockExpires > new \DateTime()) {
                    throw new UserLockedException(__('You did not sign in correctly or your account is temporarily disabled.'));
                }
            }

            $this->eventManager->dispatch('admin_user_authenticate_after', [
                'username' => $user->getUserName(),
                'password' => null,
                'user' => $user,
                'result' => true,
            ]);

            // Handle login

            /** @var \Magento\User\Model\ResourceModel\User $resource */
            $resource = $user->getResource();
            $resource->recordLogin($user);
            $authStorage->setUser($user);
            $authStorage->processLogin();
            $this->eventManager->dispatch('backend_auth_user_login_success', ['user' => $user]);
            $this->authPlugin->afterLogin($this->auth);
            $authStorage->refreshAcl();

        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $redirect->setPath('admin/auth/login');
            return $redirect;
        }
        $redirect->setPath('admin/dashboard/');
        return $redirect;
    }
}
