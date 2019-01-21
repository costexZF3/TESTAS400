<?php

namespace Purchasing\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

use Zend\Db\Adapter\Adapter;
use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Sql\Sql;



class LostsaleController extends AbstractActionController
{
   /**------------- Class Attributes -----------------*/ 
   /**
     * Entity manager.
     * @var Doctrine\ORM\EntityManager
     */
    private $entityManager;
    
    /* DB2 connection */
    
     private $conn; //it's the adapter
    private $db;
    
    /**------------- Class Methods -----------------*/ 
    
   /* constructor for claimsController. It will be injected 
    * with the entityManager with all Entities mapped 
    */
   public function __construct( $entityManager, Adapter $adapter ){
       
       $this->entityManager = $entityManager;
       
       $this->conn = $adapter; // by dependency injection
       $this->db = new Sql($this->conn);
   }   
   
   //getting the logged user 
   private function getUser(){
       $user = $this->currentUser();       
       //validating the user
       if ($user==null) {
           $this->getResponse()->setStatusCode(404);
           return;
       } 
       return $user;
   }//End: getUser()
      
   private function testPermission( string $permission){
       $user = self::getUser();       
        /*------ inherited --------  */  
       $accessT = $this->access( $permission, ['user'=> $user]);
       //var_dump($accessT); echo "";
       return $accessT;
   }
                   
   /**
    *  The IndexAction show the main Menu about all concerning to the Purchasing Menus
    */
   public function indexAction(){              
       //getting the loggin user object
       $user = self::getUser();  
              
       //-- checking if the logged user has SPECIAL ACCESS: he/she would be an Prod. Specialist
       $especial = ($this->access('special.access'))?'TRUE':'FALSE';
       
       //Initicial Value for TimesQuote 
       $timesQuote = "10";
              
       $result1 = "with z as (SELECT WRKPTN,qt,t1+t2+t3+t4+t5+t6+t7+t8+t9+t10+t11+t12+t13 TQ from "
                . "(SELECT WRKPTN,(WRK001+ WRK002+ WRK003+ WRK004+ WRK005+ WRK006+WRK007+WRK008+ WRK009+ "
                . "WRK010+ WRK011+ WRK012+ WRK013) QT,"
                . "CASE WRK001 WHEN 0 THEN 0 ELSE 1 END T1,"
                . "CASE WRK002 WHEN 0 THEN 0 ELSE 1 END T2,"
                . "CASE WRK003 WHEN 0 THEN 0 ELSE 1 END T3,"
                . "CASE WRK004 WHEN 0 THEN 0 ELSE 1 END T4,"
                . "CASE WRK005 WHEN 0 THEN 0 ELSE 1 END T5,"
                . "CASE WRK006 WHEN 0 THEN 0 ELSE 1 END T6,"
                . "CASE WRK007 WHEN 0 THEN 0 ELSE 1 END T7,"
                . "CASE WRK008 WHEN 0 THEN 0 ELSE 1 END T8,"
                . "CASE WRK009 WHEN 0 THEN 0 ELSE 1 END T9,"
                . "CASE WRK010 WHEN 0 THEN 0 ELSE 1 END T10,"
                . "CASE WRK011 WHEN 0 THEN 0 ELSE 1 END T11,"
                . "CASE WRK012 WHEN 0 THEN 0 ELSE 1 END T12,"
                . "CASE WRK013 WHEN 0 THEN 0 ELSE 1 END T13 FROM ddtwrk) a where "
                . "t1+t2+t3+t4+t5+t6+t7+t8+t9+t10+t11+t12+t13 >=".$timesQuote.") "
                . "select imptn, imdsc, imds2, imds3, (select zoned(sum(odrsq),10,0) from horddt where odrcd = 1 and"
                . " SUBSTR(DIGITS(ODDATE),5 ,2) || SUBSTR(DIGITS(ODDATE),1 ,2) || SUBSTR(DIGITS(ODDATE),3 ,2) >= 180121 and"
                . " odptn = imptn and odcu# not in (4384,4385, 4381) and"
                . " odlcn in ('01', '07', '05', '02') group by odptn) qtysold, x.onhand, x.onorder, coalesce(x.vendor, '') "
                . "vendor,impc2, (INMSTA.IMQTE+INMSTA.IMQT01+INMSTA.IMQT02+INMSTA.IMQT03+INMSTA.IMQT04+INMSTA.IMQT05+"
                . "INMSTA.IMQT06+INMSTA.IMQT07+INMSTA.IMQT08+INMSTA.IMQT09+INMSTA.IMQT10+INMSTA.IMQT11+INMSTA.IMQT12) TQUOTE,"
                . "imprc,invptyf.iptqte Timesq,coalesce((select 'X' from dvinva where dvlocn='20' and"
                . " dvpart=imptn and dvonh#>0),' ') F20,(case when x.vendor = '261339' or x.vendor='060106' or"
                . " x.vendor='262369' or x.vendor = '262673' or x.vendor='261903' or x.vendor='150064'"
                . " then 'X' else '' end) Foem, zoned(coalesce((select count(distinct qdcuno) from qtedtld "
                . "where qdptno=imptn and qdyear||qdmth||qdday >= '180121'),0),5,0) Ncus, impc1, imcata, "
               . "(select mindes from mincodes where mincod = inmsta.impc2) mindsc from inmsta left join "
               . "(select dvpart, sum(dvonh#) onhand, sum(dvono#) onorder, max(dvprmg) vendor from dvinva "
               . "where dvlocn in ('01', '05', '07') and trim(dvprmg) <> '' and dvonh# <= 0 and dvono# <= 0 "
               . "group by dvpart) x on inmsta.imptn = x.dvpart inner join invptyf on inmsta.imptn = invptyf.ippart "
               . "where substr(ucase(trim(imdsc)),1,3) <> 'USE' and imsts <> 'D' and impc1 = '01' and"
               . " (INMSTA.IMQTE+INMSTA.IMQT01+INMSTA.IMQT02+INMSTA.IMQT03+INMSTA.IMQT04+INMSTA.IMQT05+INMSTA.IMQT06+"
               . "INMSTA.IMQT07+INMSTA.IMQT08+INMSTA.IMQT09+INMSTA.IMQT10+INMSTA.IMQT11+INMSTA.IMQT12) > 0 and"
               . " imptn not in (select dvpart from dvinva where dvlocn in ('01', '05', '07') and"
               . " (dvprmg = '' or dvonh# > 0 or dvono# > 0)) and invptyf.iptqte >= ".$timesQuote." union "
               . "select z.wrkptn imptn, coalesce(catdsc,coalesce(kodesc,'N/A')) imdsc, coalesce(imds2, 'N/A') imds2, "
               . "coalesce(imds3, 'N/A') imds3, 0 qtysold, 0 onhand, 0 onorder, '' vendor, impc2, qt tquote, "
               . "coalesce(catprc,coalesce(kopric,0)) imprc, z.TQ Timesq, '' F20, '' Foem, 0 Ncus, impc1, imcata, "
               . "'TEST' mindsc from z left join cater on z.wrkptn = catptn left join inmsta on z.wrkptn = inmsta.imptn"
               . " left join komat on z.wrkptn = koptno where imsts <> 'D' and z.wrkptn not in "
               . "(select dvpart from dvinva where dvlocn in ('01', '05', '07'))";
        
        $result = $this->conn->query( $result1, Adapter::QUERY_MODE_EXECUTE );          
       
        $count = 20;
        
        $this->layout()->setTemplate('layout/layoutLostSale');
      //  $this->layout()->setTemplate('layout/layout');
        return new ViewModel([
                    'lostsalelist' => $result,                 
                           'count' => $count,
                           'user'  => $user,
                   'specialAccess' => $especial,
            ]);
    }//END: indexAction method
} //END: LostsaleController
