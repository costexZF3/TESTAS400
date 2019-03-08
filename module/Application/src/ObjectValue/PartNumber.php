<?php

namespace Application\ObjectValue;


/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use Application\Service\QueryRecover;


/**
 * Description of PartNumber
 *
 * @author mojeda
 */
class PartNumber {
   /* properties  COMMOND */
    
   private $id = '';  //IMPTN
   private $description = ''; // IMDSC
   private $major = ''; // IMPC1
   private $minor = ''; // IMPC2
   private $unitCost = ''; // IMCST
   private $listPrice = 0.0; // IMPRC
   private $sellUnitMeasure = ''; // IMUMS
   private $countryOriginal = ''; // IMCNT
   private $model = ''; // IMMOD
   private $length = 0; // IMLENG ( CM )
   private $width = 0; // IMWIDT ( CM )
   private $deep = 0; // IMDPTH ( CM )
   private $volumen = 0; // IMVOLU ( CM )
   private $category = ''; // IMCATA
   private $subCategory = ''; // IMSBCA
   
   /* properties -> LOADING TO ANOTHER QUERIES */
   
   
 /* constructor
 *  - this receives the SERVICE for recovering data from AS400
 *    @var dataSet array
 */
    
   public function __construct( $dataSet ) {       
      $this->populate( $dataSet );
   }      
   
   /* setter PRIVATED */
   private function populate( $data ) {
      $this->id = $data['id'];
      $this->description = $data['description'];
      $this->major = $data['major'];
      $this->minor = $data['minor'];
      $this->unitCost = $data['unitCost'];
      $this->listPrice = $data['listPrice'];
      $this->sellUnitMeasure = $data['sellUnitMeasure'];
      $this->countryOriginal =  $data['countryOriginal'];
      $this->model = $data['model'];
      $this->length = $data['length'];
      $this->width = $data['width'];
      $this->deep = $data['deep'];
      $this->volumen = $data['volumen'];
      $this->category = $data['category'];
      $this->subCategory =  $data['subCategory'];  
   } //END: populate method

   /* getters */
   
   public function getId() {
      return $this->id;
   }

   public function getDescription() {
      return $this->description;
   }

   public function getMajor() {
      return $this->major;
   }

   public function getMinor() {
      return $this->minor;
   }

   public function getUnitCost() {
      return $this->unitCost;
   }

   public function getListPrice() {
      return $this->listPrice;
   }

   public function getSellUnitMeasure() {
      return $this->sellUnitMeasure;
   }

   public function getCountryOriginal() {
      return $this->countryOriginal;
   }

   public function getModel() {
      return $this->model;
   }

   public function getLength() {
      return $this->length;
   }

   public function getWidth() {
      return $this->width;
   }

   public function getDeep() {
      return $this->deep;
   }

   public function getVolumen() {
      return $this->volumen;
   }

   public function getCategory() {
      return $this->category;
   }

   public function getSubCategory() {
      return $this->subCategory;
   }


}
