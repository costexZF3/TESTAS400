<?php
namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use User\Entity\User;
use Zend\Mvc\MvcEvent;

use Zend\Db\Adapter\Adapter;

/**
 * This is the main controller class of the User Demo application. It contains
 * site-wide actions such as Home or About.
 */
class IndexController extends AbstractActionController 
{
    /**
     * Entity manager.
     * @var Doctrine\ORM\EntityManager
     */
    private $entityManager;
    
    private $conn; //it's the adapter
   
    
    /**
     * Constructor. Its purpose is to inject dependencies into the controller.
     */
    public function __construct(  Adapter $adapter) 
    {
      $this->conn = $adapter; // by dependency injection
      
    }
    
    protected function create_TableWay( $tableName, $adapter )
    {
       return new TableGateway($tableName, $adapter);
    }
   
    
    /**
     * This is the default "index" action of the controller. It displays the 
     * Home page.
     */
    public function indexAction() {  
        $this->layout()->setTemplate('layout/layoutHome');
        return new ViewModel();   
    }

    /**
     * This is the "about" action. It is used to display the "About" page.
     */
    public function aboutAction() {    
//       $this->layout()->setTemplate('layout/layoutCommond'); 
       return new ViewModel();
    }  
    
    /**
     *  pagebuilding: It getting use to using when you want to display  a temporary page 
     *  at meantime.E.g: if the Site is out of service, you can use it
     *  to inform users about the website maintancement. 
     */
    public function pagebuildingAction()
    {
        return new ViewModel();
    }
    
    /**
     * The "settings" action displays the info about currently logged in user.
     */
    public function settingsAction() {
        $id = $this->params()->fromRoute('id');
        
        if ($id!=null) {
            $user = $this->entityManager->getRepository(User::class)
                    ->find($id);
         } 
        else {
            $user = $this->currentUser();
         }
      
        if ($user==null) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        //checking the logged user's permissions, if he/she has no access to the route requested, 
        //the response will be  NOT-AUTHORIZED page.
        if (!$this->access('profile.any.view') && 
            !$this->access('profile.own.view', ['user'=>$user])) {
            return $this->redirect()->toRoute('not-authorized');
        }
        
        
        
        $viewModel = new ViewModel([
            'user' => $user
        ]);       
        
//        $this->layout()->setTemplate('layout/layout_Grid');
        return  $viewModel;
    }
}

