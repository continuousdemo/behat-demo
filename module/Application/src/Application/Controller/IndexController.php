<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class IndexController extends AbstractActionController
{
    public function indexAction()
    {
        $username = $this->params()->fromPost('username');
        $email    = $this->params()->fromPost('email');

        /** @var \Application\Mapper\User $mapperUser */
        $mapperUser = $this->getServiceLocator()->get('mapper.user');

        /** @var \Application\Entity\User $user */
        $user = $this->getServiceLocator()->get('entity.user');
        $user->setEmail($email)
             ->setUsername($username);

        $mapperUser->store($user)
                   ->flush();

        return new ViewModel();
    }
}
