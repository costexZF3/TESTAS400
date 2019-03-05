<?php

namespace Purchasing\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

//use Zend\Db\Adapter\Adapter;

/* using Service: QueryRecover which let's take all data */
use Application\Service\QueryRecover as MyQuery;

//use Purchasing\Form\LostSaleForm;

class WishListController extends AbstractActionController
{
   /* DB2 connection */    
    private $conn; //it's the adapter
    
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
   
   /*
    * getting the logged user 
    */
   private function getUser(){
       $user = $this->currentUser();       
       //validating the user
       if ($user==null) {
           $this->getResponse()->setStatusCode(404);
           return;
       } 
       return $user;
   }//End: getUser()
                      
   /**
    *  The IndexAction show the main Menu about all concerning to the Purchasing Menus
    */
   public function indexAction() {
                
        $sqlStr = "SELECT * FROM PRDWL INNER JOIN INMSTA "
                . "ON TRIM(UCASE(PRDWL.PRWPTN)) = TRIM(UCASE(INMSTA.IMPTN))"
                . "LEFT JOIN INVPTYF ON TRIM(UCASE(INVPTYF.IPPART)) = TRIM(UCASE(PRDWL.PRWPTN)) "
                . "ORDER BY CRDATE DESC";       
              
       /* CALL THE WISHLIST CLASS */  
        $wishlist = $this->queryRecover->runSql( $sqlStr );
//        var_dump($wishlist); exit;
        echo "count item: ".$this->queryRecover->CountItems()."<br>";
        $fields = $this->queryRecover->getFields();
        
        print_r ( $fields );
        foreach ($wishlist as $item)
        {
            echo($item['PRWCOD']."--"); 
            echo($item['CRDATE']."--");
            echo($item['CRUSER']."--");
            echo($item['PRWPTN']."--");
            echo($item['IMDSC']);
             echo "<br>";
        }  
        
      
       /* this method retrives all items and return a resultSet or data as HTML tableGrid */   
//         $LostSale = new LostSale( $this->conn, $timesQuote, $vndAssignedOptionSelected );      
              
       $this->layout()->setTemplate('layout/layout_Grid');
       return new ViewModel([                          
                     'wishlist' => $wishlist
            ]);
    }//END: indexAction method
    
} //END: LostsaleController

