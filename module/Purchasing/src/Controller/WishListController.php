<?php

namespace Purchasing\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

/* SERVICES TO BE USED*/
use Application\Service\QueryRecover as queryManager;
use Application\Service\PartNumberManager;

use Purchasing\Service\WishListManager;


class WishListController extends AbstractActionController {
     /*
     * Service QueryRecover
     */
    private $queryManager;
    
    private $partNumberManager;
     
    /**------------- Class Methods -----------------*/ 
    
   /* 
    * @var queryRecover queryManager
    * @var $partNumberManager PartNumberManager
    */
   public function __construct( $queryRecover, $partNumberManager ) {   
       $this->queryManager= $queryRecover;      
       $this->partNumberManager = $partNumberManager;
   }   
  
   /**
    *  The IndexAction show the main Menu about all concerning to the Purchasing Menus
    */
   public function indexAction() {              
       /* CALL THE WISHLIST CLASS */  
//        $wishlist = $this->queryRecover->runSql( $sqlStr );

        $MyWishList = new WishListManager( $this->queryManager, $this->partNumberManager );
        
      
       /* this method retrives all items and return a resultSet or data as HTML tableGrid */   
//         $LostSale = new LostSale( $this->conn, $timesQuote, $vndAssignedOptionSelected );      
              
       $this->layout()->setTemplate('layout/layout_Grid');
       return new ViewModel([                          
                     'wishlist' => $MyWishList->TableAsHtml(),
            ]);
    }//END: indexAction method
    
} //END: LostsaleController

