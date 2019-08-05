<?php

namespace Purchasing\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Session\Container as SM;

/* services injected from the FACTORY: ProductDevControllerFactory */
use Application\Service\QueryManager;
use Purchasing\Service\ProductDevManager;
use Purchasing\Service\WishListManager;
use Purchasing\Entity\Project;
use Purchasing\Entity\Detail;


/* MODEL: forms classes */
//use Purchasing\Form\FormAddItemWL as FormAddItemWL;
//use Purchasing\Form\FormUpload; 

class ProductdevController extends AbstractActionController 
{    
   /**
    * VARIABLE QueryManager is an instance of the service
    * @var ProductDevManager 
    */
   private $pdManager = null; 

   /**
    * @var queryManager
    */
    private $queryManager; 

    /**
     *
     * @var Zend\Session\Container
     */
    private $session;

    /**
     * 
     * 
     * @param WishListManager $wlManager
     * @param ProductDevManager $pdManager
     * @param QueryManager $queryManager
     * @param SM $sessionManager
     */
    public function __construct( $entityManager,
                                 WishListManager     $wlManager,
                                 ProductDevManager   $pdManager,    
                                 QueryManager        $queryManager,
                                 SM                  $sessionManager ) 
    {          
       $this->entityManager = $entityManager;
       $this->wlManager = $wlManager;   
       $this->pdManager = $pdManager;
       $this->queryManager = $queryManager;//
       $this->session = $sessionManager;
       
    } //END: CONSTRUCTOR  

   /*
   * getting the logged user 
   */
   private function getUser($short = true)
   {
       $user = $this->currentUser();
       
       //validating the user
       if ($user == null) {
           $this->getResponse()->setStatusCode( 404 );
           return;
       } 
      
       return ($short) ? str_replace('@costex.com', '', $user->getEmail()) : $user;
   }//End: getUser()  

   /************************************ for updating items in WL ******************************  

   /**
   *  This method save on the session variable an list of data
   * 
   * @param array() $data
   */
   private function saveIntoSession( $data )
   {
      $this->session->data = $data;
   }

   /**
   *  This method compares $data, with data in the session and returns if was some 
   *  differences between the new data and the save them into the session
   * 
   * @param array() $data
   * @return type
   */    
   private function changedData( $data )
   {                
      $result = array_diff( $data, $this->session->data );
      return  $result;
   }
  
   /**
    * Index Action: it's the main action in the controller
    * - this show all the projects
    * @return ViewModel
    */
   public function indexAction() {
      var_dump($this->session->data);  
      echo "indexAction() in ProductDevController";  exit;
      return new ViewModel([''=>'']);
   }//END indexAction()
   
   /**
    * This method receives as parameter the Id of the project that will be 
    * recover
    *  
    * @return ViewModel
    */
   public function viewprojectAction() {
      $projectId = $this->params()->fromRoute('id', -1);
      
      //if you want to know whether the part is coming from the wishlist...
      $isFromWl = $this->session->project['fromwl'] ? true : false;
      
      if ( $projectId < 0 ) {
         $this->getResponse()->setStatusCode(404);
         return;
      }      
      
      // using the ORM (DOCTRINE) FOR RECOVERING THE Project ENTITY         
      $projectObj = $this->entityManager->getRepository(Project::class)
                         ->find( $projectId );
      
          
      if ($projectObj == null) {
         $this->getResponse()->setStatusCode(404);
         return;
      }
      $this->flashMessenger()->addInfoMessage("The Project with code: [$projectId] was created successfully. The STATUS HAS CHANGED TO CLOSE_BY_DEVELOPMENT ");
      //rendering the View
      return new ViewModel([         
         'projectObj' => $projectObj,         
      ]);
      
   }//END: viewprojectAction()
  
   /**
    *  This method() is an Action that receive data for being inserted as a 
    *  New Development Project 
    */
   public function addprojectAction() 
   {    
     //checking if the newproject is comming from WL
      $data = $this->session->newproddev ?? null;
      
      //updating FROMWL ?  
      $data['fromwl'] = $data != null; 
      
      $validData = null != $tmp = (($data['projectname']) ?? null);
      
      if ( !$validData ) {
        $this->flashMessenger()->addErrorMessage("Invalid data");         
         $this->getResponse()->setStatusCode(404);
         return;
      }
      
      $data['currentuser'] = strtoupper($this->getUser()); //shorter user by default 
      
      //it's generated automatically by the SERVICE PdManager()
      $data['projectcode'] = $this->pdManager->nextIndex(); 
      
    
      //inserting data into the new project
      if ($this->pdManager->insert( $data )) {
         $this->flashMessenger()->addInfoMessage("The Project with code: [".$data['projectcode']."] was created successfully.");         
         $this->flashMessenger()->addInfoMessage("The Status of the Part has been changed to CLOSE_BY_DEVELOPMENT");         
      }
 
      //saving the data from the current project into the session
      $this->session->project = $data;
      
      // redictecting to viewproject Action checking where the request is from. 
      
      $this->redirect()->toRoute('productdev', ['action'=>'viewproject', 'id'=> $data['projectcode'] ]);
  }
  
    
} //END: LostsaleController

