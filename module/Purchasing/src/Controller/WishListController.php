<?php

namespace Purchasing\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

/* using Service: QueryRecover which let's take all data */
use Application\Service\QueryRecover as MyQuery;
use Purchasing\Entity\WishList;

//use Purchasing\Form\LostSaleForm;

class WishListController extends AbstractActionController
{
     /*
     * Service QueryRecover
     */
    private $queryRecover;
     
    /**------------- Class Methods -----------------*/ 
    
   /* constructor for WishList. It will be injected 
    * with the queryRecover SERVICE for execute any queryString 
    * by dependency injection 
    */
   public function __construct( MyQuery $queryRecover ){   
       $this->queryRecover = $queryRecover;      
   }   
  
   /**
    *  The IndexAction show the main Menu about all concerning to the Purchasing Menus
    */
   public function indexAction() {              
       /* CALL THE WISHLIST CLASS */  
//        $wishlist = $this->queryRecover->runSql( $sqlStr );
//
//        echo "count item: ".$this->queryRecover->CountItems()."<br>";
        
        $MyWishList = new WishList( $this->queryRecover );
              
//        var_dump($MyWishList); exit();
//        echo $MyWishList->CountItems()."<br>";
//        $MyWishList->TableAsHtml();
//        print_r ($MyWishList->getRows()); exit();
//        echo $MyWishList->TableAsHtml(); exit();

       
      
       /* this method retrives all items and return a resultSet or data as HTML tableGrid */   
//         $LostSale = new LostSale( $this->conn, $timesQuote, $vndAssignedOptionSelected );      
              
       $this->layout()->setTemplate('layout/layout_Grid');
       return new ViewModel([                          
                     'wishlist' => $MyWishList,
            ]);
    }//END: indexAction method
    
} //END: LostsaleController

