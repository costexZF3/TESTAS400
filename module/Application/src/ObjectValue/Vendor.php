<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\ObjectValue;

use Application\Service\QueryManager; 

/**
 * Description of Vendor
 *
 * @author mojeda
 */
class Vendor {
    private $name;
    private $number;    
    private $productSpecialist;
    private $purchasingAgent;
    private $type;
    private $address = [];   //address 1, 2, 3
    private $yearSales;
    private $lastYear;
    private $createdDate;
        
    private $query;
    
    
    public function __construct( $data ) {       
      $this->populate( $data );
    }
    
    /**
     * This method populates the Vendor Object
     * 
     * @param array() $data
     */
    private function populate( $data ) 
    {        
        $this->setName( $data['name'] );
        $this->setNumber( $data['number'] );
        $this->setProductSpecialist( $data['ps'] );
        $this->setPurchasingAgent( $data['pa'] );
        $this->setType( $data['type'] );
//        $this->setAddress( $data['address'] );
        $this->setYearSales( $data['yearsales'] );
        $this->setLastYear( $data['lastyear'] );              
    }


    /*** GETTERS ***/
    public function getName() {
        return $this->name;
    }

    public function getNumber() {
        return $this->number;
    }

    public function getProductSpecialist() {
        return $this->productSpecialist;
    }

    public function getPurchasingAgent() {
        return $this->purchasingAgent;
    }

    public function getType() {
        return $this->type;
    }

    public function getAddress() {
        return $this->address;
    }

    public function getYearSales() {
        return $this->yearSales;
    }

    public function getLastYear() {
        return $this->lastYear;
    }

    public function getCreatedDate() {
        return $this->createdDate;
    }

    public function getQuery() {
        return $this->query;
    }

    public function getData() {
        return $this->data;
    }

    /*** SETTERS *********/
    private function setName($name) {
        $this->name = $name;
    }

    private function setNumber($number) {
        $this->number = $number;
    }

    private function setProductSpecialist($productSpecialist) {
        $this->productSpecialist = $productSpecialist;
    }

    private function setPurchasingAgent($purchasingAgent) {
        $this->purchasingAgent = $purchasingAgent;
    }

    private function setType($type) {
        $this->type = $type;
    }

    private function setAddress($address) {
        $this->address = $address;
    }

    private function setYearSales($yearSales) {
        $this->yearSales = $yearSales;
    }

    private function setLastYear($lastYear) {
        $this->lastYear = $lastYear;
    }

    private function setCreatedDate($createdDate) {
        $this->createdDate = $createdDate;
    }

    private function setQuery($query) {
        $this->query = $query;
    }

    private function setData($data) {
        $this->data = $data;
    }
}//end class
