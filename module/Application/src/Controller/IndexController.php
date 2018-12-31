<?php
namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use User\Entity\User;
use Zend\Mvc\MvcEvent;

use Zend\Db\Adapter\Adapter;
use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Sql\Sql;


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
    private $db;
    
    /**
     * Constructor. Its purpose is to inject dependencies into the controller.
     */
    public function __construct($entityManager, Adapter $adapter) 
    {
       $this->entityManager = $entityManager;
       
       $this->conn = $adapter; // by dependency injection
       $this->db = new Sql($this->conn);
       
    }
    
    protected function create_TableWay( $tableName, $adapter )
    {
       return new TableGateway($tableName, $adapter);
    }
   
    
    /**
     * This is the default "index" action of the controller. It displays the 
     * Home page.
     */
    public function indexAction() 
    {
        //return new ViewModel();
        
        //testing Creating SQL statement 
        $result1 = "with z as  (SELECT WRKPTN,qt,t1+t2+t3+t4+t5+t6+t7+t8+t9+t10+t11+t12+t13 TQ from 
                    (SELECT WRKPTN,(WRK001+ WRK002+ WRK003+ WRK004+ WRK005+ WRK006+WRK007+WRK008+ WRK009+ WRK010+ WRK011+ WRK012+ WRK013) QT,
                    CASE WRK001 WHEN 0 THEN 0 ELSE 1 END T1,CASE WRK002 WHEN 0 THEN 0 ELSE 1 END T2,
                    CASE WRK003 WHEN 0 THEN 0 ELSE 1 END T3,CASE WRK004 WHEN 0 THEN 0 ELSE 1 END T4,
                    CASE WRK005 WHEN 0 THEN 0 ELSE 1 END T5,CASE WRK006 WHEN 0 THEN 0 ELSE 1 END T6,
                    CASE WRK007 WHEN 0 THEN 0 ELSE 1 END T7,CASE WRK008 WHEN 0 THEN 0 ELSE 1 END T8,
                    CASE WRK009 WHEN 0 THEN 0 ELSE 1 END T9,CASE WRK010 WHEN 0 THEN 0 ELSE 1 END T10,
                    CASE WRK011 WHEN 0 THEN 0 ELSE 1 END T11,CASE WRK012 WHEN 0 THEN 0 ELSE 1 END T12,
                    CASE WRK013 WHEN 0 THEN 0 ELSE 1 END T13 FROM ddtwrk) a where t1+t2+t3+t4+t5+t6+t7+t8+t9+t10+t11+t12+t13 > 5) 
                    select imptn, imdsc, imds2, imds3, (select zoned(sum(odrsq),10,0) from horddt 
                      where odrcd = 1 and SUBSTR(DIGITS(ODDATE),5 ,2) || 
                      SUBSTR(DIGITS(ODDATE),1 ,2) || SUBSTR(DIGITS(ODDATE),3 ,2) >= 171022 and odptn = imptn and 
                      odcu# not in (4384,4385) and odlcn in ('01', '04', '05', '07', '02') group by odptn) qtysold, x.onhand, x.onorder, 
                      coalesce(x.vendor, '') vendor,impc2, (INMSTA.IMQTE+INMSTA.IMQT01+INMSTA.IMQT02+INMSTA.IMQT03+INMSTA.IMQT04+INMSTA.IMQT05+INMSTA.IMQT06+INMSTA.IMQT07+INMSTA.IMQT08+INMSTA.IMQT09+INMSTA.IMQT10+INMSTA.IMQT11+INMSTA.IMQT12) TQUOTE,
                      imprc,invptyf.iptqte Timesq, 
                      coalesce((select 'X' from dvinva where dvlocn='20' and dvpart=imptn and dvonh#>0),' ') F20,
                      (case when x.vendor = '261339' or x.vendor='060106' or x.vendor='262369' or x.vendor = '262673' 
                      or x.vendor='261903' or x.vendor='150064' then 'X' else '' end) Foem, 
                      zoned(coalesce((select count(distinct qdcuno) from qtedtld 
                      where qdptno=imptn and qdyear||qdmth||qdday >= '171022'),0),5,0) Ncus, impc1, imcata, 
                       (select mindes from mincodes where mincod = inmsta.impc2) mindsc from inmsta left join 
                       (select dvpart, sum(dvonh#) onhand, sum(dvono#) onorder, max(dvprmg) vendor from dvinva 
                          where dvlocn in ('01', '05', '07') and trim(dvprmg) <> '' and dvonh# <= 0 and dvono# <= 0 group by dvpart)
                          x on inmsta.imptn = x.dvpart inner join invptyf on inmsta.imptn = invptyf.ippart where 
                          substr(ucase(trim(imdsc)),1,3) <> 'USE' and imsts <> 'D' and impc1 = '01' and 
                          (INMSTA.IMQTE+INMSTA.IMQT01+INMSTA.IMQT02+INMSTA.IMQT03+INMSTA.IMQT04+INMSTA.IMQT05+INMSTA.IMQT06+INMSTA.IMQT07+INMSTA.IMQT08+INMSTA.IMQT09+INMSTA.IMQT10+INMSTA.IMQT11+INMSTA.IMQT12) > 0 
                          and imptn not in (select dvpart from dvinva 
                          where dvlocn in ('01', '05', '07') and (dvprmg = '' or dvonh# > 0 or dvono# > 0)) and invptyf.iptqte >= 100 
                           union 
                          select z.wrkptn imptn, coalesce(catdsc,coalesce(kodesc,'N/A')) imdsc, 
                          coalesce(imds2, 'N/A') imds2, coalesce(imds3, 'N/A') imds3, 0 qtysold, 0 onhand, 0 onorder, '' vendor, impc2, qt tquote,
                          coalesce(catprc,coalesce(kopric,0)) imprc, z.TQ Timesq, '' F20, '' Foem, 0 Ncus, impc1, imcata, '' mindsc from z  
                          left join cater on z.wrkptn = catptn left join inmsta on z.wrkptn = inmsta.imptn left join komat on z.wrkptn = koptno 
                          where imsts <> 'D' and z.wrkptn not in (select dvpart from dvinva where dvlocn in ('01', '05', '07'))";
        
        //$result = $this->conn->query('SELECT * FROM CTPUSER', Adapter::QUERY_MODE_EXECUTE);
        $result = $this->conn->query($result1, Adapter::QUERY_MODE_EXECUTE);        
       
        $count= $result->count();
        
//      objSpreadsheet.Cells(iRow, 1).Value = "Part No."    
//	objSpreadsheet.Cells(iRow, 2).Value = "Part Description"
//	objSpreadsheet.Cells(iRow, 3).Value = "Part Description 2"
//	objSpreadsheet.Cells(iRow, 4).Value = "Part Description 3"
//	objSpreadsheet.Cells(iRow, 5).Value = "Qty Quote"
//	objSpreadsheet.Cells(iRow, 6).Value = "Times Quote"
//	objSpreadsheet.Cells(iRow, 7).Value = "Custs. Quote"
//	objSpreadsheet.Cells(iRow, 8).Value = "Sales Last 12"
//	objSpreadsheet.Cells(iRow, 9).Value = "Vendor No."
//	objSpreadsheet.Cells(iRow, 10).Value = "Vendor Name"
//	objSpreadsheet.Cells(iRow, 11).Value = "Pur. Agent Name"
//	objSpreadsheet.Cells(iRow, 12).Value = "Price List Caterpillar"
//	objSpreadsheet.Cells(iRow, 13).Value = "Wish List"
//	objSpreadsheet.Cells(iRow, 14).Value = "Dev. Proj."
//	objSpreadsheet.Cells(iRow, 15).Value = "Dev. Sts."
//	objSpreadsheet.Cells(iRow, 16).Value = "Loc. 20"
//	objSpreadsheet.Cells(iRow, 17).Value = "OEM Vnd."
//	objSpreadsheet.Cells(iRow, 18).Value = "Major"
//	objSpreadsheet.Cells(iRow, 19).Value = "Category"
//	objSpreadsheet.Cells(iRow, 20).Value = "Minor"
//	objSpreadsheet.Cells(iRow, 21).Value = "Minor Description"
        
        return new ViewModel([ 'parts' => $result,
                               'count' => $count,
                    ]);         
        
    }

    /**
     * This is the "about" action. It is used to display the "About" page.
     */
    public function aboutAction() 
    {                      
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
    public function settingsAction()
    {
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
        
        //$this->layout()->setTemplate('layout/layout.phtml');
        
        $viewModel = new ViewModel([
            'user' => $user
        ]);       
        
        return  $viewModel;
    }
}

