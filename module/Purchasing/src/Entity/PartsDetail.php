<?php

namespace Purchasing\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * THIS IS AN ENTITY CLASS WHICH MAPS THE THE FILE: PRDVLD
 * IN THE AS400.
 * - you can retrieve the details of a Development Project  gitven its code
 * 
 * @ORM\Entity()
 * @ORM\Table(name="QS36F.PRDVLD")
 */
class PartsDetail {
   // PROJECT STATUS CONSTANTS
   const STATUS_FINALIZED    = 'F'; // Finalized.
   const STATUS_INITIALIZED  = 'I'; //  Initialized.
        
  /**
   * @ORM\Id
   * @ORM\Column(name="PRHCOD")
   * 
   */      
   private $code;  //PRHCOD
   
   /** 
   * @ORM\Column(name="PRDPTN")  
   */
   private $partnumber; // PRDPTN
   
   /** 
   * @ORM\Column(name="PRDDAT")  
   */
   private $date;  //PRDDAT         Entered Date
   
   /** 
   * @ORM\Column(name="CRUSER")    creation User
   */
   private $creationUser;  //CRUSER
   
   /** 
   * @ORM\Column(name="CRDATE")  
   */
   private $creationDate; // CRDATE
   
   /** 
   * @ORM\Column(name="MOUSER")  
   */
   private $modifyUser; // MOUSER
   
   /** 
   * @ORM\Column(name="MODATE")  
   */
   private $modifyDate; // MODATE
   
   /** 
   * @ORM\Column(name="PRDCTP")  
   */
   private $ctpPartNumber; // PRDCTP
   
   /** 
   * @ORM\Column(name="PRDQTY")  
   */
   private $quantity; // PRDQTY
   
   /** 
   * @ORM\Column(name="PRDMFR")  
   */
   private $manufactureOEM; // PRDMFR
   
   /** 
   * @ORM\Column(name="PRDMFR#")  
   */
   private $manufactureOEMNum; // PRDMFR#
   
   /** 
   * @ORM\Column(name="PRDCOS")  
   */
   private $unitCostCurrentSupplier; // PRDCOS
   
   /** 
   * @ORM\Column(name="PRDCON")  
   */
   private $unitCostNewSupplier; // PRDCON
   
   /** 
   * @ORM\Column(name="PRDPO#")  
   */
   private $purchaseOrder; // PRDPO#
   
   /** 
   * @ORM\Column(name="PODATE")  
   */
   private $purchaseOrderDate; // PODATE
   
   /** 
   * @ORM\Column(name="PRDSTS")  
   */
   private $status; // PRDSTS
   
   
   // strings of 255 characthers
   /** 
   * @ORM\Column(name="PRDBEN")  
   */
   private $benefits; // PRDBEN
   
   /** 
   * @ORM\Column(name="PRDINF")  
   */
   private $info; // PRDINF
   
   /** 
   * @ORM\Column(name="PRDUSR")  
   */
   private $userInCharge; // PRDUSR
   
   /** 
   * @ORM\Column(name="PRDNEW")  
   */
   private $isNewPart; // PRDNEW
   
   /** 
   * @ORM\Column(name="PRDEDD")  
   */
   private $estDeliveryDate; // PRDEDD
   
   /** 
   * @ORM\Column(name="PRDSCO")  
   */
   private $oemSampleCost; // PRDSCO
   
   /** 
   * @ORM\Column(name="PRDTTC")  
   */
   private $techTestCost; // PRDTTC
   
   /** 
   * @ORM\Column(name="VMVNUM")  
   */
   private $vendor; // VMVNUM
   
   /** 
   * @ORM\Column(name="PRDPTS")  
   */
   private $partsToShow; // PRDPTS  1-CTP   2-Vendor
   
   /** 
   * @ORM\Column(name="PRDMPC")  
   */
   private $minorProdCode; // PRDMPC  Minor Product Code
   
   /** 
   * @ORM\Column(name="PRDTCO")  
   */
   private $toolingCost; // PRDTCO  Tooling Cost
   
   /** 
   * @ORM\Column(name="PRDERD")  
   */
   private $estimateRespDate; // PRDERD  Estimate Response Date
   
   /** 
   * @ORM\Column(name="PRDPDA")  
   */
   private $personInChargeDateAssigned; // PRDPDA  Date person in Charge assign
   
   /** 
   * @ORM\Column(name="PRDSQTY")  
   */
   private $sampleQty; // PRDSQTY  Sample Qty
   
   /** 
   * @ORM\Column(name="PRWLDA")  
   */
   private $wishListDate; // PRWLDA  Date Wish List
   
   /** 
   * @ORM\Column(name="PRWLFL")  
   */
   private $isWishList; // PRWLFL   Flag: it's in the WishList??
  
   /** 
   * @ORM\Column(name="PARTNO")  
   */
   private $partNumberLast; // PARTNO   ??? I need to know about it..
   
   
 /* constructor
  *  - this receives from the SERVICE PartNumberManager 
  *   a dataSET for recovering data from AS400
  *   @var dataSet array
 */
    
   public function __construct() {       
      //
   }      
    
     
    /**
     * Sets status.
     * @param int $status     
     */
    public function setStatus($status) 
    {
      $this->status = $status;
    }
    
    /**     
     * 
     * @param integer $code
     */
    public function setCode($code) 
    {
      $this->code = $code;
    }
    
    /**
     * 
     * @param string $name
     */
    public function setName( $name ) 
    {
      $this->name = $name;
    }
    
    /**
     * 
     * @param string $comment
     */
    public function setComment($comment) 
    {
      $this->comments = $comment;
    }
    
    /**
     * 
     * @param string $user
     */
    public function setCreationUser($user) 
    {
      $this->creationUser = $user;
    }
    
    /**
     * 
     * @param string $user
     */
    public function setModifyUser($user) 
    {
      $this->modifyUser = $user;
    }
    
    /**
     * 
     * @param string $user
     */
    public function setUserInCharge($user) 
    {
      $this->userInCharge = $user;
    }
    
   public function setCtpPartNumber( $value ) {
     $this->ctpPartNumber = $value;
   }
   
   public function setQuantity( $value ) {
      $this->quantity= $value;
   }
   
   public function setManufactureOEM( $value ) {
      $this->manufactureOEM= $value;
   }
   
   public function setManufactureOEMNum( $value) {
      $this->manufactureOEMNum = $value;
   }


   public function setUnitCostCurrentSupplier( $value ) {
      $this->unitCostCurrentSupplier = $value;
   }
   
   public function setUnitCostNewSupplier($value) {
      $this->unitCostNewSupplier= $value;
   }
   
   public function setPurchasingOrder( $value ) {
      $this->purchaseOrder = $value;
   }
   
   public function setPurchasingOrderDate( $value ) {
      $this->purchaseOrderDate = $value;
   }
   
   public function setPartNumberStatus( $value ) {
      $this->partNumberStatus= $value;
   }
   
   public function setBenefits( $value ) {
      $this->benefits = $value;
   }
   
   public function setInfo( $value ) {
      $this->info = $value;
   }
   
   
   public function  setIsNewPart( $value ) {
      $this->isNewPart = $value;
   }
   
   public function setEstDeliveryDate( $value ) {
      $this->estDeliveryDate = $value;
   }

   public function setOEMSampleCost( $value ) {
      $this->oemSampleCost = $value;
   }
   
   public function setTechnicalTestCost( $value ) {
      $this->techTestCost = $value;
   }
   
   public function setVendor( $value ) { 
      $this->vendor = $value;
   }
   
   public function setPartsToShow( $value) {
      $this->partsToShow = $value;
   }
   
   public function setMinorProdCode( $value ) {
      $this->minorProdCode = $value;
   }
   
   public function setToolingCost( $value ) {
      $this->toolingCost = $value;
   }
   
   public function setEstimateRespDate( $value) {
      $this->estimateRespDate = $value;
   }
   
   public function setPersonInChargeDateAssigned( $value ) {
      $this->personInChargeDateAssigned = $value;
   }
   
   public function setSampleQty( $value = 0 ) {
     $this->sampleQty = $value;     
   }
   
   public function setWishListDate( $value ) {
      $this->wishListDate = $value;
   }
   
   // return true if the field has value 1 
   public function setIsWishList( $value = 0) {
      $this->isWishList = $value; 
   }
   
   //return the last PartNumber  field
   public function  setPartNumberLast( $value ) {
      $this->partNumberLast = $value;
   }
    
    
        
   /*********************************************** getters ********************************************/  
   
   public function getParts() {
      return $this->parts;
   }
   
   public function getCode() {
      return $this->code;
   }
   
   public function getDate() {
      return $this->date;
   }
   public function getComment() {
      return $this->comments;
   }
   public function getName() {
      return $this->name;
   }
   public function getStatus() {
      return trim($this->status);
   }
   
   public function getCreationUser() {
      return $this->creationUser;
   }
   public function getCreationDate() {
      return $this->creationDate;
   }
   
   public function getModifyDate() {
      return $this->modifyDate;
   }

   public function getModifyUser() {
      return $this->modifyUser;
   }

   public function getUserInCharge() {
      return $this->userInCharge;
   }
 
   public function getCtpPartNumber() {
      return $this->ctpPartNumber;
   }
   
   public function getQuantity() {
      return $this->quantity;
   }
   
   public function getManufactureOEM() {
      return $this->manufactureOEM;
   }
   
   public function getManufactureOEMNum() {
      return $this->manufactureOEMNum;
   }


   public function getUnitCostCurrentSupplier() {
      return $this->unitCostCurrentSupplier;
   }
   
   public function getUnitCostNewSupplier() {
      return $this->unitCostNewSupplier;
   }
   
   public function getPurchasingOrder() {
      return $this->purchaseOrder;
   }
   
   public function getPurchasingOrderDate() {
      return $this->purchaseOrderDate;
   }
   
   public function getPartNumber() {
      return $this->partnumber;
   }
   
   public function getPartNumberStatus() {
      return $this->partNumberStatus;
   }
   
   public function getBenefits() {
      return $this->benefits;
   }
   
   public function getInfo() {
      return $this->info;
   }
   
   public function  getIsNewPart() {
      return $this->isNewPart;
   }
   
   public function getEstDeliveryDate() {
      return $this->estDeliveryDate;
   }

   public function getOEMSampleCost() {
      return $this->oemSampleCost;
   }
   
   public function getTechnicalTestCost() {
      return $this->techTestCost;
   }
   
   public function getVendor() {
      return $this->vendor;
   }
   
   public function getPartsToShow() {
      return $this->partsToShow;
   }
   
   public function getMinorProdCode() {
      return $this->minorProdCode;
   }
   
   public function getToolingCost() {
      return $this->toolingCost;
   }
   
   public function getEstimateRespDate() {
      return $this->estimateRespDate;
   }
   
   public function getPersonInChargeDateAssigned() {
      return $this->personInChargeDateAssigned;
   }
   
   public function getSampleQty() {
     return $this->sampleQty;     
   }
   
   public function getWishListDate() {
      return $this->wishListDate;
   }
   
   // return true if the field has value 1 
   public function getIsWishList() {
      return $this->isWishList === 1; 
   }
   
   //return the last PartNumber  field
   public function  getPartNumberLast() {
      return $this->partNumberLast;
   }
}
