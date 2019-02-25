<?php
namespace Application\View\Helper;

use Zend\View\Helper\AbstractHelper;

/**
 * This view helper class displays breadcrumbs.
 */
class Breadcrumbs extends AbstractHelper 
{
    /**
     * Array of items.
     * @var array 
     */
    private $items = [];
    
    /**
     * Constructor.
     * @param array $items Array of items (optional).
     */
    public function __construct($items=[]) 
    {                
        $this->items = $items;
    }
    
    /**
     * Sets the items.
     * @param array $items Items.
     */
    public function setItems($items) 
    {
        $this->items = $items;
    }
    
    /**
     * Renders the breadcrumbs.
     * @return string HTML code of the breadcrumbs.
     */
    public function render() 
    {
        if (count($this->items)==0) {
            return ''; // Do nothing if there are no items.
        }    
        
        // Resulting HTML code will be stored in this var
        $result = '<section class="">';
        $result.= '<div class="container">';
        $result.= '<div class="row">';
        $result.= '<div class="col-lg-12">';
        $result.= '<div class="bread-crumb-inner">';
                
        // Get item count
        $itemCount = count($this->items); 
        
        $itemNum = 1; // item counter
               
        $result.= '<div class="page-list">';
                
        // Walk through items
        foreach ($this->items as $label=>$link) {
            if ($itemNum == 1) {
                 $result.= '<h1 class="title">'.$label.'</h1>';
            }
            // Make the last item inactive
            $isActive = ($itemNum==$itemCount?true:false);
                        
            // Render current item
            $result .= $this->renderItem($label, $link, $isActive);
                        
            // Increment item counter
            $itemNum++;
        }
        
        $result .= '</div>'; //closing: page-list div
        $result .= '</div>'; //closing: breadcrumb div
        $result .= '</div>'; //closing: col-lg-12  div
        $result .= '</div>'; //closing: row  div
        $result .= '</div>'; //closing: container  div
        $result .= '</section>'; //closing: section
        
        return $result;
        
    }
    
    /**
     * Renders an item.
     * @param string $label
     * @param string $link
     * @param boolean $isActive
     * @return string HTML code of the item.
     */
    protected function renderItem($label, $link, $isActive) 
    {
        $escapeHtml = $this->getView()->plugin('escapeHtml');        
               
        if (!$isActive) {
            $result = '<a href="'.$escapeHtml($link).'">'.$escapeHtml($label).'</a>';
            $result.= ' - ';
            
        } else {
            $result= '<span class="">'.$escapeHtml($label).'</span>';
//            $result= '<span class="current-item">'.$escapeHtml($label).'</span>';
        }           
                    
       
    
        return $result;
    }
}
