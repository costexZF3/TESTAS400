<?php
namespace Application\View\Helper;

use Zend\View\Helper\AbstractHelper;

/**
 * This view helper class displays a menu bar.
 */
class Menu extends AbstractHelper 
{
    /**
     * Menu items array.
     * @var array 
     */
    protected $items = [];
    
    /**
     * Active item's ID.
     * @var string  
     */
    protected $activeItemId = '';
    
    /**
     * Constructor.
     * @param array $items Menu items.
     */
    public function __construct($items=[]) 
    {
        $this->items = $items;
    }
    
    /**
     * Sets menu items.
     * @param array $items Menu items.
     */
    public function setItems($items) 
    {
        $this->items = $items;
    }
    
    /**
     * Sets ID of the active items.
     * @param string $activeItemId
     */
    public function setActiveItemId($activeItemId) 
    {
        $this->activeItemId = $activeItemId;
    }
    
    /**
     * Renders the menu.
     * @return string HTML code of the menu.
     */
    public function render() 
    {
        if (count($this->items)==0){
            return ''; // Do nothing if there are no items.
        }                       
                
        $result = '<ul class="navbar-nav" id="primary-menu">';
         //navbar- nav       
        // Render items
        foreach ($this->items as $item) {
            if(!isset($item['float']) || $item['float']=='left') {
                $result .= $this->renderItem($item);
                
            }
        }
        
      //  $result .= '</ul>';
        //$result .= '<ul class="nav-item navbar-right">';
        
        // Render items
        foreach ($this->items as $item) {
            if(isset($item['float']) && $item['float']=='right'){
                $result .= $this->renderItem($item);
            }
        }
        
        $result .= '</ul>';    
        
        return $result;        
    }
    
    /**
     * Renders an item.
     * @param array $item The menu item info.
     * @return string HTML code of the item.
     */
    protected function renderItem($item) 
    {        
        $id = $item['id']?? '';
        $isActive = ($id==$this->activeItemId);
        $label = $item['label'] ?? '';
            
        $result = ''; 
     
        $escapeHtml = $this->getView()->plugin('escapeHtml');
        
        // checking the menu options
        if (isset($item['dropdown'])) {
            
            $dropdownItems = $item['dropdown'];
            
            //creating Menu TAG (HEADER )
            $result .= '<li class="nav-item dropdown ' . ($isActive ? 'active' : '') . '">';
            $result .= '<a href="#" class="nav-link dropdown-toggle" role="button" data-toggle="dropdown" aria-haspopup="true"';
            $result .= ' aria-expanded="false">';
            $result .= $escapeHtml($label);
            $result .= '</a>';
           
           $result .= '<div class="dropdown-menu">';
            foreach ($dropdownItems as $item) {                
                // adding a divider if it's defined 
                $link = $item['link'] ?? '';
                $label = $item['label'] ?? '';                   
                $result .= '<a  class="dropdown-item" href="'.$escapeHtml($link).'">'.$escapeHtml($label).'</a>';                   
              }            
            $result .= '</div>';
            $result.='</li>';
        } 
      else {        
        $link = $item['link']??'#';
        //Some Menu==[] and it does not need be rendered
        if ($link!='#') {    
            $result.='<li class="nav-item">';
            $result .= '<a class="nav-link" href="'.$escapeHtml($link).'">'.$escapeHtml($label).'</a>';   
            $result.='</li>';
        }
      }    
      return $result;
    } //END: function renderItem($item)
}
